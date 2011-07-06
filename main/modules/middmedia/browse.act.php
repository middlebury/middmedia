<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */ 

/*********************************************************
 * Add to MiddTube
 *********************************************************/
// Add the IXR class for XML-RPC with Wordpress
require_once(dirname(__FILE__).'/includes/class-IXR.php');

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */
class browseAction 
	extends MiddMedia_Action_Abstract
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Browse Your Videos");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		
		$actionRows = $this->getActionRows();
		
		$this->addToHead("\n\t\t<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'></script>");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload/swfupload.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/handlers.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/fileprogress.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/swfupload.queue.js'></script> ");
		$this->addToHead("\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/SWFUpload_Samples/progress.css'/> ");
		
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/sorttable.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/md5.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/Panel.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script> ");
		
		$this->addToHead("
		<script type='text/javascript'>
		
		// Create embed code for MiddTube
		$(document).ready(function() {
			// Bind this function to all inputs
			$(\"input\").bind('click', function(){
			  var inputs = $('input');
				var embeds = [ ];
				for (var i = 0; i < inputs.length; i++) {
					// Only proceed if we're dealing with a 'media file' checkbox
					if (inputs[i].name == 'media_files' && inputs[i].checked) {
						var file_name = inputs[i].value;
						// Create the embed code. We make some characters around
						//file_name to make it easier to access later.
						embeds[i] = '[middmedia ##user## `!~' + file_name + '~!` width:400 height:300]';
					}
				}
				// Add this to the Add to MiddTube form
				$('input.checked_files_middtube_embed').attr('value', embeds);
		  });
		  
		  // Validation for first step of form
		  $('#add_to_middtube').bind('click', function(){
			  var inputs = $('input');
			  var something_checked = false;
				for (var i = 0; i < inputs.length; i++) {
					if (inputs[i].name == 'media_files' && inputs[i].checked) {
						something_checked = true;
					}
				}
				if (!something_checked) {
					return false;
				}
		  });
		  
		}); //end (document).ready(function() {
		
		// <![CDATA[
		
		function toggle (dirId) {
				$('.collapsible.' + dirId).slideToggle();
		}
		
		$(document).ready(function() {
			$('.collapse_all').click( function() {
				$('.collapsible').slideToggle();
			});
		});
		
		function deleteFile (directory, file, row) {
// 			if (!confirm(\"Are you sure you want to delete this file?\\n\\n\" + file))
// 				return;
			var url = Harmoni.quickUrl('middmedia', 'delete', {
				'directory': directory,
				'file': file
			});
			
			var req = Harmoni.createRequest();
			if (!req) {
				alert('Your browser does not support AJAX, please upgrade.');
				return;
			}
			
			var size = 0;
			for (var i = 0; i < row.childNodes.length; i++) {
				if (row.childNodes[i].className == 'size') {
					size = new Number(row.childNodes[i].getAttribute('sorttable_customkey'));
					size = 0 - size;
					break;
				}
			}
						
			req.onreadystatechange = function () {
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						row.parentNode.removeChild(row);
						addToQuota(directory, size);
					} else {
						alert(req.responseText);
					}
				}
			}
			
			req.open('GET', url, true);
			req.send(null);
		}
		
		
		function middmediaUploadSuccess (file, serverData) {
			// run the default handler to close up the progress bar
			uploadSuccess.apply(this, [file, serverData]);
			
			// Add a row to the listing.
// 			console.log(file);
// 			console.log(serverData);
			
			var fileDoc = createDocumentFromString(serverData);
			if (!fileDoc) {
				alert('Could not load new data. Please refresh the page after files have finished uploading.');
				return;
			}
			
			var file = fileDoc.getElementsByTagName('file').item(0);
			if (!file) {
				alert('Could not load file data. Please refresh the page after files have finished uploading.');
				return;
			}
			
			var tbody = document.get_element_by_id(this.customSettings.fileListingId);
			var row = tbody.insertBefore(document.createElement('tr'), tbody.firstChild);
			row.id = 'row-' + hex_md5(file.getAttribute('directory') + '/' + file.getAttribute('name'));
			
			var td = row.appendChild(document.createElement('td'));
			var box = td.appendChild(document.createElement('input'));
			box.type = 'checkbox';
			box.name = 'media_files';
			box.value = file.getAttribute('name');
			
			var pathInfo = file.getAttribute('name').match(/(.+)\.([a-zA-Z0-9]+)/);
			var type = null;
			var extension = pathInfo[2].toLowerCase();
			switch(extension) {
				case 'flv':
					type = 'video';
					var myId = file.getAttribute('directory') + '/' + pathInfo[1];
					break;
				case 'mp3':
					type = 'audio';
				default:
					if (!type)
						type = 'video';
					var myId = extension + ':' + file.getAttribute('directory') + '/' + file.getAttribute('name');
			}
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'name';
			var link = td.appendChild(document.createElement('a'));
			link.innerHTML = file.getAttribute('name');
			link.href = '#';
			link.onclick = function () {
				displayMedia(this, type, myId, file.getAttribute('http_url'), file.getAttribute('rtmp_url'), file.getAttribute('splash_url')); 
				return false;
			}
			if (file.getAttribute('thumb_url')) {
				link.appendChild(document.createElement('br'));
				var thumb = link.appendChild(document.createElement('img'));
				thumb.className = 'media_thumbnail';
				thumb.src = file.getAttribute('thumb_url');
			}
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'type';
			td.innerHTML = file.getAttribute('mime_type');
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'size';
			var size = new Number(file.getAttribute('size'));
			td.setAttribute('sorttable_customkey', size);
			td.innerHTML = size.asByteSizeString();
			
			addToQuota(file.getAttribute('directory'), size);
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'date';
			var mod = Date.fromISO8601(file.getAttribute('modification_date'));
			td.innerHTML = mod.toFormatedString('NNN d, yyyy') + '<br/>' + mod.toFormatedString('h:m a');
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'creator';
			td.innerHTML = file.getAttribute('creator_name');

			var td = row.appendChild(document.createElement('td'));
			td.className = 'access';
			
			var link = td.appendChild(document.createElement('a'));
			link.innerHTML = 'Embed Code &amp; URLs';
			link.href = '#';
			link.onclick = function() {
				displayEmbedCode(this, type, file.getAttribute('http_url'), file.getAttribute('rtmp_url'), file.getAttribute('splash_url')); 
				return false;
			}
		}
		
		/**
		 * Create a new XML document in a cross-browser way.
		 * 
		 * @param string xmlString
		 * @return Document
		 * @access public
		 * @since 11/19/08
		 */
		function createDocumentFromString (xmlString) {
			try { 	//Internet Explorer
				xmlDoc=new ActiveXObject('Microsoft.XMLDOM');
			} catch(e) {
				try {	//Firefox, Mozilla, Opera, etc.
					parser = new DOMParser();
					return parser.parseFromString(xmlString,'text/xml');
				} catch(e) {
					alert(e.message);
					return;
				}
			}
			try {
				xmlDoc.async=false;
				xmlDoc.loadXML(xmlString);
				return doc;
			} catch(e) {
				alert(e.message);
			}
		}
		
		/**
		 * Delete all check files in the directory specified
		 * 
		 * @param string dirName		The directory which contains the files.
		 * @return void
		 * @access public
		 * @since 11/20/08
		 */
		function deleteChecked (dirName) {
			var fileList = document.get_element_by_id('listing-' + hex_md5(dirName));
			
			var toDelete = [];
			var inputs = fileList.getElementsByTagName('input');
			for (var i = 0; i < inputs.length; i++) {
				if (inputs[i].name == 'media_files' && inputs[i].checked) {
					toDelete.push(inputs[i].value);
				}
			}
			
			if (!toDelete.length) {
				alert('No files selected in this directory.');
				return;
			}
			
			if (confirm(\"Are you sure you wish to delete the following files?\\n\\n\\t\" + toDelete.join(\"\\n\\t\") + \"\\n\")) {
				for (var i = 0; i < toDelete.length; i++) {
					deleteFile(dirName, toDelete[i], document.get_element_by_id('row-' + hex_md5(dirName + '/' + toDelete[i])));
				}
			}
		}
		
		/**
		 * Set all files in a directory checked or unchecked
		 * 
		 * @param string dirName
		 * @param DOMElement checkAllBox
		 * @return void
		 * @access public
		 * @since 11/20/08
		 */
		function setChecked (dirName, checkAllBox) {
			var fileList = document.get_element_by_id('listing-' + hex_md5(dirName));
			var inputs = fileList.getElementsByTagName('input');
			for (var i = 0; i < inputs.length; i++) {
				if (inputs[i].name == 'media_files') {
					 inputs[i].checked = checkAllBox.checked;
				}
			}
		}
		
		/**
		 * Add an amount (positive or negative) from the quota display
		 * 
		 * @param string dirName
		 * @param int bytes
		 * @return void
		 * @access public
		 * @since 11/20/08
		 */
		function addToQuota (dirName, bytes) {
			var dirId = hex_md5(dirName);
			var quotaBar = document.get_element_by_id('quota-' + dirId);
			var quotaAmmountElem = document.get_element_by_id('quota_ammount-' + dirId);
			var quotaUsedElem = document.get_element_by_id('quota_ammount_used-' + dirId);
			
			var quota = new Number(quotaAmmountElem.innerHTML);
			var used = new Number(quotaUsedElem.innerHTML);
			
			used = Math.max(0, used + bytes);
			
			quotaUsedElem.innerHTML = used;
			
			
			var pcUsed = Math.ceil(100 * (used / quota));
			var pcFree = Math.floor(100 * ((quota - used) / quota));
			
			// used bar and label
			var elem = document.get_element_by_id('quota_used-' + dirId);
			elem.style.width = pcUsed + '%';
			var elem = document.get_element_by_id('quota_used_label-' + dirId);
			elem.innerHTML = used.asByteSizeString();
			
			// free bar and label
			var elem = document.get_element_by_id('quota_free-' + dirId);
			elem.style.width = pcFree + '%';
			var elem = document.get_element_by_id('quota_free_label-' + dirId);
			elem.innerHTML = (quota - used).asByteSizeString();
			
		}
		
		function displayPreview(link, dir, file) {
			if (link.panel) {
				link.panel.open();
			} else {
				var panel = new CenteredPanel('Viewing Media', 400, 500, link);
				panel.contentElement.style.textAlign = 'center';
				
				var req = Harmoni.createRequest();
				if (!req) {
					alert('Your browser does not support AJAX, please upgrade.');
					return;
				}
				
				req.onreadystatechange = function () {
				
				
				
					// only if req shows 'loaded'
					if (req.readyState == 4) {
					
					
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseText) {
							
							
							
							var desc = panel.contentElement.appendChild(document.createElement('p'));
							
							if (navigator.appName == 'Microsoft Internet Explorer') {
							desc.outerHTML = req.responseText;
						} else {
							desc.innerHTML = req.responseText;
						}
							
							
						} else {
							alert(req.responseText);
						}
					}
				}
				
				req.open('GET', Harmoni.quickUrl('middmedia', 'preview', {'directory':dir,'file':file}), true);
				req.send(null);
			}
		}
		
		function displayEmbedCode(link, type, dir, file, splashUrl) {
			if (link.panel) {
				link.panel.open();
			} else {
			
			var panel = new CenteredPanel('Embed Code and URLs', 400, 700, link);
				
			var req = Harmoni.createRequest();
			if (!req) {
				alert('Your browser does not support AJAX, please upgrade.');
				return;
			}
			
			req.onreadystatechange = function () {
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseText) {
						
						var desc = panel.contentElement.appendChild(document.createElement('p'));
						if (navigator.appName == 'Microsoft Internet Explorer') {
							desc.outerHTML = req.responseText;
						} else {
							desc.innerHTML = req.responseText;
						}
						
					} else {
						alert(req.responseText);
					}
				}
			}
			
			req.open('GET', Harmoni.quickUrl('middmedia', 'embed', {'directory':dir,'file':file}), true);
			req.send(null);
				
			}
			
		}
		
		// ]]>
		</script> ");
		
		$manager = $this->getManager();
		
		// Get the personal directory
		try {
			$dir = $manager->getPersonalDirectory();
			$actionRows->add(
				new Heading($dir->getBaseName()." ("._("Personal").")", 2), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
			$actionRows->add(
				new Block($this->getDirectoryMarkup($dir), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
		} catch (PermissionDeniedException $e) {
			$actionRows->add(
				new Block(_("You are not authorized to upload personal videos."), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
		}
			
		// Get the shared directories
		foreach ($manager->getSharedDirectories() as $dir) {
			$actionRows->add(
				new Heading($dir->getBaseName()." ("._("Shared").")", 2), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
			$actionRows->add(
				new Block($this->getDirectoryMarkup($dir), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
		}
	}
	
	/**
	 * Answer the manager to use for this action.
	 * 
	 * @return MiddMediaMangager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMedia_Manager::forCurrentUser();
	}
	
	/**
	 * Add to the document head
	 * 
	 * @param string $string
	 * @return void
	 * @access protected
	 * @since 11/13/08
	 */
	protected function addToHead ($string) {
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$string);
	}
	
	/**
	 * Answer a block of HTML to represent the directory
	 * 
	 * @param object MiddMedia_Directory $dir
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getDirectoryMarkup (MiddMedia_DirectoryInterface $dir) {
		ob_start();
		$harmoni = Harmoni::instance();
		
		$dirId = md5($dir->getBaseName());
		
		print "\n
								<a href='#' class='collapse ".$dirId."' onclick='toggle(\"".$dirId."\")'>[Show/Hide Folder]</a>
								<a href='#' class='collapse_all ".$dirId."'>[Show/Hide All]</a><br /><br />
						";
		print "\n<div class='collapsible ".$dirId."'>";
		
		/*********************************************************
		 * Quota bar
		 *********************************************************/
		print $this->getQuotaBar($dir);
		
		/*********************************************************
		 * Upload Form
		 *********************************************************/
		$mediaTypes = MiddMedia_File_Media::getAllowedVideoTypes();
		foreach ($mediaTypes as $key => $type) {
			$mediaTypes[$key] = '*.'.$type;
		}
		$mediaTypes = implode(';', $mediaTypes);
		
		$this->addToHead(
			"
		<script type='text/javascript'>
		// <![CDATA[
		
		
		window.addOnLoad(function() {
		
		var selected_quality = $('select#quality-".$dirId." option:selected').val();

			var swfu = new SWFUpload({
					upload_url : '".str_replace('&amp;', '&', $harmoni->request->quickURL('middmedia', 'upload', array('directory' => $dir->getBaseName())))."', 
					flash_url : '".MYPATH."/javascript/SWFUpload/swfupload.swf',
					post_params: {'".session_name()."' : '".session_id()."', 'quality' : selected_quality},
					file_size_limit : '".ByteSize::withValue($this->getSystemUploadLimit())->asMBString()."',
					file_types : '".$mediaTypes."',
					file_types_description : 'Flash Video, H264 Video, and MP3 Audio',
					file_upload_limit : 100,
					file_queue_limit : 0,
					debug: false,
					custom_settings : {
						progressTarget : 'uploadProgress-".$dirId."',
						cancelButtonId : 'cancel-".$dirId."',
						fileListingId : 'listing-".$dirId."'
					},
					
					// Button settings
					button_image_url: '".MYPATH."/javascript/SWFUpload_Samples/images/UploadBackground-100x22.png',	// Relative to the Flash file
					button_width: '90',
					button_height: '22',
					button_placeholder_id: 'upload-".$dirId."',
					button_text: '<span class=\"theFont\">Upload Files</span>',
					button_text_style: '.theFont { font-family: Vedana,Arial,Helvetica,sans-serif; font-size: 13; }',
					button_text_left_padding: 3,
					button_text_top_padding: 2,
					
					// The event handler functions are defined in handlers.js
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
					upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : middmediaUploadSuccess,
					upload_complete_handler : uploadComplete
					
				}); 
			document.get_element_by_id('cancel-".$dirId."').onclick = function () {
				swfu.cancelQueue();
					
			};
			$('select#quality-".$dirId."').change(function () {
				swfu.addPostParam('quality' , this.value);
				
			});
			
			
		});
		
		// ]]>
		</script>"
		);
		
		print "\n<div class='middmedia_upload'>";
		print "\n\t<button class='btnUpload' id='upload-".$dirId."'></button>";
		print "\n\t<input  class='btnCancel' type='button' id='cancel-".$dirId."' value='Cancel All Uploads' disabled='disabled' />";
		print "\n\t<br />File quality:<select name='quality-".$dirId."' id='quality-".$dirId."'>";
		if ($dir->getQuality() == 'original') {
		  print "\n\t<option selected value='original'>same as original</option>";
		}
		else {
		  print "\n\t<option value='original'>same as original</option>";
		}
		if ($dir->getQuality() == '360p') {
		  print "\n\t<option selected value='360p'>360p</option>";
		}
		else {
		  print "\n\t<option value='360p'>360p</option>";
		}
		if ($dir->getQuality() == '480p') {
		  print "\n\t<option selected value='480p'>480p (default)</option>";
		}
		else {
		  print "\n\t<option value='480p'>480p (default)</option>";
		}
		if ($dir->getQuality() == '720p') {
		  print "\n\t<option selected value='720p'>720p</option>";
		}
		else {
		  print "\n\t<option value='720p'>720p</option>";
		}
		if ($dir->getQuality() == '1080p') {
		  print "\n\t<option selected value='1080p'>1080p</option>";
		}
		else {
		  print "\n\t<option value='1080p'>1080p</option>";
		}
		print "\n\t</select>";
		print "\n<fieldset class='progress' id='uploadProgress-".$dirId."'>";
		print "\n\t<legend>"._("Upload Queue")."</legend>";
		print "\n</fieldset>";
// 		print "\n<div class='status' id='status-".$dirId."'>"._("0 Files Uploaded")."</div>";
		print $this->getUploadHelp();
		print "<p><a href='".$harmoni->request->quickUrl('middmedia', 'upload_form', array('directory' => $dir->getBaseName()))."'>Alternate (non-Flash) upload form</a></p>";
		print "\n</div>";
		
		/*********************************************************
		 * Add to MiddTube
		 *********************************************************/
		if (defined('MIDDTUBE_URL') && MIDDTUBE_URL != '') {
			// Establish a connection to MiddTube
			$client = new IXR_Client(MIDDTUBE_URL."/xmlrpc.php");
		}
		// Do the following on normal page load
		if (!isset($_POST['middtubeclicked']) && defined('MIDDTUBE_URL') && MIDDTUBE_URL != ''){
		
		/*********************************************************
		* Delete Controls (We only want this when we're not processing an addition to Middtube)
		*********************************************************/
		print "\n<div class='middmedia_delete'>";
		print "\n\t<input type='button' onclick=\"deleteChecked('".$dir->getBaseName()."');\" value='Delete Checked Files'/>";
		print "\n</div>";	
		
		?>
		<!-- Add a form for Add to MiddTube --> 
		<form action='<?php $harmoni->request->quickURL('middmedia', 'browse'); ?>' method='post'>
			<!-- Lets us know that Add to MiddTube has been clicked on submission -->
			<input name='middtubeclicked' type='hidden' value='TRUE' />
			<!-- This is where the embed code the JS got is placed -->
			<input class='checked_files_middtube_embed' name='checked_files_middtube_embed' type='hidden' />
			<input type='submit' id='add_to_middtube' name='add_to_middtube' value='Add Checked Files to Middtube'/> What is <a href="http://blogs.middlebury.edu/middtube/">Middtube</a>?
		</form>
		<?php
		// If Add to MiddTube has been clicked we want to show a different form
		} elseif (defined('MIDDTUBE_URL') && MIDDTUBE_URL != '') {
		?>
		<form action='<?php $harmoni->request->quickURL('middmedia', 'browse'); ?>' method='post'>
			<!-- Pass on the embed code the JS got last time. We'll need that -->
			<input name='checked_files_middtube_embed' type='hidden' value='<?php print $_POST['checked_files_middtube_embed']; ?>'/>
			<!-- This is a flag for this stage of the form -->
			<input name='filenamesset' type='hidden' value='TRUE' />
			<p id="Titles_for_Posts_on_MiddTube_explanation">Choose the category and add a title for each file you are adding to <a href="http://blogs.middlebury.edu/middtube/">MiddTube</a>.</p>
		<?php
			// Finally we use those embed codes, split them into an array
			$embeds = explode(',', $_POST['checked_files_middtube_embed']);
			$i = 0;
			// For each embed code
			foreach($embeds as $middtube_embed) {
				// We only care about the actual embed codes
				if ($middtube_embed != '') {
					// Get the name of the file from the embed code
					preg_match('/`!~.*~!`/',$middtube_embed, $title);
					$title = str_replace('`!~','',$title);
					$title = str_replace('~!`','',$title);
					// Make a select list for the categories
					print '<select name="categories'.$i.'">';
					// Make sure we can connect and get the categories
					if (!$client->query('wp.getCategories','', WP_USER, WP_PASS)) {
						die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
					}
					$response = $client->getResponse();
					// Make the select list options (the categories from MiddTube we just got)
					foreach($response as $category) {
						print "<option value='".$category['categoryName']."'>".$category['categoryName']."</option>";
					}
					print '</select>';
					// Show the name of the file so we know what file we're choosing a
					//category for and giving a name to. 
					print "<input class='post_title_input' name='title".$i."' type='text' /> ".$title[0]."<br />";
					$i++;
				} // end if ($middtube_embed != '') {
			} // end foreach($embeds as $middtube_embed) {
			?>
			<input id='submit_to_middtube' type='submit' value='Submit to Middtube!'/>
		</form>
		<?php
		} //end else {
		// Now we do this when the second part of the form has been completed
		// (The categories have been selected and the names entered). Here we
		// want to actually add the posts to MiddTube
		if (isset($_POST['filenamesset']) && defined('MIDDTUBE_URL') && MIDDTUBE_URL != '') {
			// We need those embed codes again
			$embeds = explode(',', $_POST['checked_files_middtube_embed']);
			$i = 0;
			// For each embed code
			foreach($embeds as $middtube_embed) {
				// Only if there is a code
				if ($middtube_embed != '') {
					// Get the name of the file from the embed code
					preg_match('/`!~.*~!`/',$middtube_embed, $filename);
					$embed_filename = str_replace(' ','%20',$filename); 
					$middtube_embed = str_replace($filename,$embed_filename,$middtube_embed);
					$filename = str_replace('`!~','',$filename);
					$filename = str_replace('~!`','',$filename);
					// Now swap in the real user name for the placeholder
					$middtube_embed = str_replace('##user##', $dir->getBaseName() . ' ' .$dir->getBaseName() ,$middtube_embed);
					// Also replace the wrapper around the file name.
					// We don't need that in the actual embed code
					$middtube_embed = str_replace('`!~', '',$middtube_embed);
					$middtube_embed = str_replace('~!`', '',$middtube_embed);
					// We use these to increment the $_POST values we're passing to Wordpress
					$title = 'title' . $i;
					$categories = 'categories' . $i;
					// Here is the actual content	we're passing
					$content['title'] = strip_tags($_POST[$title]);
					$content['categories'] = array($_POST[$categories]);
					$content['description'] = $middtube_embed;
					// Make the post!
					if (!$client->query('metaWeblog.newPost','', WP_USER, WP_PASS, $content, true)) {
  					die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
					}
					// Also get the most recent post. We want the post ID so we can use
					// it to make some nice links to these new post(s) we just made.
					if (!$client->query('metaWeblog.getRecentPosts','', WP_USER, WP_PASS, 1)) {
  					die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
					}
					$response = $client->getResponse();
					// This is UGLY but it's just a line that tells us the name of the
					// post we made, the file that was posted, and an "edit" and "view" link.
					print "<p id='posted_to_middtube_success_message'><span class='b'><a target='_blank' href='".MIDDTUBE_URL."/?p=".($response[0]['postid']+$i)."'>".strip_tags($_POST[$title])."</a> (".$filename[0].") posted to <a href='http://blogs.middlebury.edu/middtube/'>MiddTube</a> Successfully! <a target='_blank' href='".MIDDTUBE_URL."/wp-admin/post.php?post=".(htmlentities($response[0]['postid'])+$i)."&action=edit'>Edit</a> or <a target='_blank' href='".MIDDTUBE_URL."/?p=".htmlentities(($response[0]['postid'])+$i)."'>View</a> this post.</p>";
					$i++;
				}
			}
			// Clear $_POST
			unset($_POST);
		}
		
		/*********************************************************
		 * File Listing
		 *********************************************************/
		print "\n<table class='file_listing_table sortable'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th class='sorttable_nosort'>";
		print "\n\t\t\t\t<input type='checkbox' onchange='setChecked(\'".$dir->getBaseName()."\', this);'/>";
		print "</th>";
		print "\n\t\t\t<th>"._("Name")."</th>";
		print "\n\t\t\t<th>"._("Type")."</th>";
		print "\n\t\t\t<th>"._("Size")."</th>";
		print "\n\t\t\t<th>"._("Date")."</th>";
		print "\n\t\t\t<th>"._("Creator")."</th>";
		print "\n\t\t\t<th class='sorttable_nosort'>"._("Access")."</th>";
// 		print "\n\t\t\t<th class='sorttable_nosort'>"._("Operations")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody id='listing-".$dirId."'>";
		
		foreach ($dir->getFiles() as $file) {
			$fileId = md5($dir->getBaseName().'/'.$file->getBaseName());
			
			
			// Get the type and Id for use by JS functions
			$parts = pathinfo($file->getBasename());
			unset($type);
			switch (strtolower($parts['extension'])) {
				case 'flv':
					$type = 'video';
					$myId = $dir->getBaseName().'/'.$parts['filename'];
					break;
				case 'mp3':
					$type = 'audio';
				default:
					if (!isset($type))
						$type = 'video';
					$myId = strtolower($parts['extension']).':'.$dir->getBaseName().'/'.$parts['filename'].'.'.$parts['extension'];
			}
			
			try {
				$splashUrl = $file->getFormat('splash')->getHttpUrl();
			} catch (InvalidArgumentException $e) {
				// Only ignore if reporting that the file doesn't exist.
				if ($e->getCode() != 78345)
					throw $e;
				else
					$splashUrl = '';
			}
			
			
			
			print "\n\t\t<tr id=\"row-".$fileId."\">";
			
			print "\n\t\t\t<td>";
			print "\n\t\t\t\t<input type='checkbox' name='media_files' value=\"".$file->getBaseName()."\"/>";
			print "</td>";
			
			print "\n\t\t\t<td class='name'>";
			$primaryFormat = $file->getPrimaryFormat();
			if ($primaryFormat->supportsHttp())
				$httpUrl = $primaryFormat->getHttpUrl();
			else
				$httpUrl = '';
			if ($primaryFormat->supportsRtmp())
				$rtmpUrl = $primaryFormat->getRtmpUrl();
			else
				$rtmpUrl = '';
			
			print "\n\t\t\t\t<a href='#' onclick=\"displayPreview(this, '".$file->directory->getBaseName()."', '".$file->getBaseName()."'); return false;\">";
			print $file->getBaseName();
			try {
				$thumbUrl = $file->getFormat('thumb')->getHttpUrl();
				print "\n\t\t\t\t<br/>\n\t\t\t\t";
				print "<img src=\"".$thumbUrl."\" class='media_thumbnail'/>";
			} catch (InvalidArgumentException $e) {
				// Only ignore if reporting that the file doesn't exist.
				if ($e->getCode() != 78345)
					throw $e;
			}
			print "\n\t\t\t\t</a>";
			
			// Processing feedback.
			if ($info = $file->getQueueInfo()) {
				print "\n\t\t\t\t<div class='queue_info'>";
				if ($info['processing']) {
					print "Converting for ".$info['time_processing']->asString();
				} else {
					if ($info['num_ahead']) {
						print $info['num_ahead'];
						if ($info['num_ahead'] == 1)
							print " video ";
						else
							print " videos ";
						print 'in line ahead';
					} else {
						print "Next up for conversion";
					}
				}
				print "\n\t\t\t\t</div>";
			}
			
			print "\n\t\t\t</td>";
			
			print "\n\t\t\t<td class='type'>".$file->getMimeType()."</td>";
			
			print "\n\t\t\t<td class='size' sorttable_customkey='".$file->getSize()."'>";
			$size = ByteSize::withValue($file->getSize());
// 			print $file->getSize();
			print $size->asString();
			print "</td>";
			
			$mod = $file->getModificationDate()->asLocal();
			print "\n\t\t\t<td class='date' sorttable_customkey='".$mod->asUTC()->asString()."'>";
			print $mod->format('M d, Y');
			print "<br/>";
			print $mod->format('g:i a');
			print "</td>";
			
			print "\n\t\t\t<td class='creator'>";
			try {
				print $file->getCreator()->getDisplayName();
			} catch (OperationFailedException $e) {
				print _("Unknown");
			} catch (UnimplementedException $e) {
			}
			print "</td>";
			
			print "\n\t\t\t<td class='access'>";
			
			print "<br/><a href='#' onclick=\"displayEmbedCode(this, '".$type."', '".$file->directory->getBaseName()."', '".$file->getBaseName()."', '".$splashUrl."'); return false;\">Embed Code &amp; URLs</a>";
			
			print "</td>";			
			print "\n\t\t</tr>";
		}
		
		print "\n\t</tbody>";
		print "\n\t</table>";
		print "\n\t</div>";
		return ob_get_clean();
	}
}

?>