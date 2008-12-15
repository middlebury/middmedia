<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

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
	extends MainWindowAction
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
		
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload/swfupload.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/handlers.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/fileprogress.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SWFUpload_Samples/swfupload.queue.js'></script> ");
		$this->addToHead("\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/SWFUpload_Samples/progress.css'/> ");
		
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/sorttable.js'></script> ");
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/md5.js'></script> ");
		
		$this->addToHead("
		<script type='text/javascript'>
		// <![CDATA[
		
		function deleteFile (directory, file, row) {
// 			if (!confirm(\"Are you sure you want to delete this file?\\n\\n\" + file))
// 				return;
			var url = Harmoni.quickUrl('middtube', 'delete', {
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
		
		
		function middtubeUploadSuccess (file, serverData) {
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
			
			var td = row.appendChild(document.createElement('td'));
			td.className = 'name';
			td.innerHTML = file.getAttribute('name');
			
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
			link.innerHTML = 'HTTP (Download)';
			link.href = file.getAttribute('http_url');
			td.appendChild(document.createElement('br'));
			
			var link = td.appendChild(document.createElement('a'));
			link.innerHTML = 'RTMP (Streaming)';
			link.href = file.getAttribute('rtmp_url');
			td.appendChild(document.createElement('br'));
			
			var link = td.appendChild(document.createElement('a'));
			link.innerHTML = 'Embed Code';
			link.href = '#';
			link.onclick = function() {
				alert('unimplemented');
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
	 * @return MiddTubeMangager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddTubeManager::forCurrentUser();
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
	 * @param object MiddTube_Directory $dir
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getDirectoryMarkup (MiddTube_Directory $dir) {
		ob_start();
		$harmoni = Harmoni::instance();
		
		$dirId = md5($dir->getBaseName());
		
		/*********************************************************
		 * Quota bar
		 *********************************************************/
		
		print "\n<div class='quota_bar' id='quota_bar-".$dirId."'>";
		print "\n\t<div class='quota_ammount' id='quota_ammount-".$dirId."'>".$dir->getQuota()."</div>";
		print "\n\t<div class='quota_ammount_used' id='quota_ammount_used-".$dirId."'>".$dir->getBytesUsed()."</div>";
		
		$percent = ceil(100 * ($dir->getBytesUsed() / $dir->getQuota()));
		print "\n\t<div class='used' style='width: ".$percent."%;' id='quota_used-".$dirId."'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesUsed());
		print "\n\t<div class='used_label' id='quota_used_label-".$dirId."'>"._("Used: ").$size->asString()."</div>";
		
		$percent = floor(100 * ($dir->getBytesAvailable() / $dir->getQuota()));
		print "\n\t<div class='free' style='width: ".$percent."%;' id='quota_free-".$dirId."'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesAvailable());
		print "\n\t<div class='free_label' id='quota_free_label-".$dirId."'>"._("Free: ").$size->asString()."</div>";
		
		print "\n</div>";
		
		/*********************************************************
		 * Upload Form
		 *********************************************************/
		
		$this->addToHead(
			"
		<script type='text/javascript'>
		// <![CDATA[
		
		window.addOnLoad(function() {
			var swfu = new SWFUpload({
					upload_url : '".str_replace('&amp;', '&', $harmoni->request->quickURL('middtube', 'upload', array('directory' => $dir->getBaseName())))."', 
					flash_url : '".MYPATH."/javascript/SWFUpload/swfupload.swf', 
					post_params: {'".session_name()."' : '".session_id()."'},
					file_size_limit : '".ByteSize::withValue($this->getFileSizeLimit())->asString()."',
					file_types : '*.flv;*.mp4;*.mp3',
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
					upload_success_handler : middtubeUploadSuccess,
					upload_complete_handler : uploadComplete,
// 					queue_complete_handler : queueComplete	// Queue plugin event
					
				}); 
			document.get_element_by_id('cancel-".$dirId."').onclick = function () {
				swfu.cancelQueue();
			};
		});
		
		// ]]>
		</script>"
		);
		
		print "\n<div class='middtube_upload'>";
		print "\n\t<button class='btnUpload' id='upload-".$dirId."'></button>";
		print "\n\t<input  class='btnCancel' type='button' id='cancel-".$dirId."' value='Cancel All Uploads' disabled='disabled' />";
		
		print "\n<fieldset class='progress' id='uploadProgress-".$dirId."'>";
		print "\n\t<legend>"._("Upload Queue")."</legend>";
		print "\n</fieldset>";
// 		print "\n<div class='status' id='status-".$dirId."'>"._("0 Files Uploaded")."</div>";
		print "\n</div>";
		
		/*********************************************************
		 * Delete Controls
		 *********************************************************/
		print "\n<div class='middtube_delete'>";
		print "\n\t<input type='button' onclick=\"deleteChecked('".$dir->getBaseName()."');\" value='Delete Checked Files'/>";
		print "\n</div>";
		
		/*********************************************************
		 * File Listing
		 *********************************************************/
		print "\n<table class='file_listing_table sortable'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th class='sorttable_nosort'>";
		print "\n\t\t\t\t<input type='checkbox' onchange='setChecked(\"".$dir->getBaseName()."\", this);'/>";
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
			
			print "\n\t\t<tr id=\"row-".$fileId."\">";
			
			print "\n\t\t\t<td>";
			print "\n\t\t\t\t<input type='checkbox' name='media_files' value=\"".$file->getBaseName()."\"/>";
			print "</td>";
			
			print "\n\t\t\t<td class='name'>".$file->getBaseName()."</td>";
			
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
			print "<a href='".$file->getHttpUrl()."'>HTTP (Download)</a>";
			print "<br/><a href='".$file->getRtmpUrl()."'>RTMP (Streaming)</a>";
			print "<br/><a href='#' onclick=\"alert('Unimplemented'); return false;\">Embed Code</a>";
			print "</td>";
			
			print "\n\t\t</tr>";
		}
		
		print "\n\t</tbody>";
		print "\n\t</table>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the file size limit
	 * 
	 * @return int
	 * @access protected
	 * @since 11/13/08
	 */
	protected function getFileSizeLimit () {
		return min(
					ByteSize::fromString(ini_get('post_max_size'))->value(),
					ByteSize::fromString(ini_get('upload_max_filesize'))->value(),
					ByteSize::fromString(ini_get('memory_limit'))->value()
				);
	}
}

?>