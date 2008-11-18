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
		
		$manager = MiddTubeManager::forCurrentUser();
		
		// Get the personal directory
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
		
		/*********************************************************
		 * Quota bar
		 *********************************************************/
		
		print "\n<div class='quota_bar'>";
		
		$percent = ceil(100 * ($dir->getBytesUsed() / $dir->getQuota()));
		print "\n\t<div class='used' style='width: ".$percent."%;'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesUsed());
		print "\n\t<div class='used_label'>"._("Used: ").$size->asString()."</div>";
		
		$percent = floor(100 * ($dir->getBytesAvailable() / $dir->getQuota()));
		print "\n\t<div class='free' style='width: ".$percent."%;'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesAvailable());
		print "\n\t<div class='free_label'>"._("Free: ").$size->asString()."</div>";
		
		print "\n</div>";
		
		/*********************************************************
		 * Upload Form
		 *********************************************************/
		$harmoni = Harmoni::instance();
		$this->addToHead(
			"
		<script type='text/javascript'>
		// <![CDATA[
		
		window.addOnLoad(function() {
			var swfu = new SWFUpload({
					upload_url : '".str_replace('&amp;', '&', $harmoni->request->quickURL('middtube', 'upload', array('directory' => $dir->getBaseName())))."', 
					flash_url : '".MYPATH."/javascript/SWFUpload/Flash9/swfupload_f9.swf', 
					post_params: {'".session_name()."' : '".session_id()."'},
					file_size_limit : '".ByteSize::withValue($this->getFileSizeLimit())->asString()."',
					file_types : '*.flv;*.mp4;*.mp3',
					file_types_description : 'Flash Video, H264 Video, and MP3 Audio',
					file_upload_limit : 100,
					file_queue_limit : 0,
					debug: true,
					custom_settings : {
						progressTarget : 'uploadProgress-".$dir->getBaseName()."',
						cancelButtonId : 'cancel-".$dir->getBaseName()."'
					},
					
					// The event handler functions are defined in handlers.js
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
					upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,
					queue_complete_handler : queueComplete	// Queue plugin event
					
				}); 
			document.get_element_by_id('upload-".$dir->getBaseName()."').onclick = function () {
				swfu.selectFiles();
			};
		});
		
		// ]]>
		</script>"
		);
		print "\n<form class='middtube_upload' action='".$harmoni->request->quickURL('middtube', 'upload', array('directory' => $dir->getBaseName()))."' method='post' enctype='multipart/form-data'>";
		print "\n\t<input type='button' id='upload-".$dir->getBaseName()."' value='Upload Files'/>";
		print "\n\t<input type='button' id='cancel-".$dir->getBaseName()."' value='Cancel All Uploads' disabled='disabled' />";
		print "\n</form>";
		print "\n<fieldset class='progress' id='uploadProgress-".$dir->getBaseName()."'>";
		print "\n\t<legend>"._("Upload Queue")."</legend>";
		print "\n</fieldset>";
		print "\n<div class='status' id='status-".$dir->getBaseName()."'>"._("0 Files Uploaded")."</div>";
		
		/*********************************************************
		 * File Listing
		 *********************************************************/
		print "\n<table class='file_listing_table'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"._("Name")."</th>";
		print "\n\t\t\t<th>"._("Type")."</th>";
		print "\n\t\t\t<th>"._("Size")."</th>";
		print "\n\t\t\t<th>"._("Date")."</th>";
		print "\n\t\t\t<th>"._("Creator")."</th>";
		print "\n\t\t\t<th>"._("Access")."</th>";
		print "\n\t\t\t<th>"._("Operations")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		foreach ($dir->getFiles() as $file) {
			print "\n\t\t<tr>";
			
			print "\n\t\t\t<td>".$file->getBaseName()."</td>";
			
			print "\n\t\t\t<td>".$file->getMimeType()."</td>";
			
			print "\n\t\t\t<td>";
			$size = ByteSize::withValue($file->getSize());
			print $file->getSize();
			print " (".$size->asString().")";
			print "</td>";
			
			print "\n\t\t\t<td>".$file->getModificationDate()->asLocal()->asString()."</td>";
			
			print "\n\t\t\t<td>";
			try {
				print $file->getCreator()->getDisplayName();
			} catch (OperationFailedException $e) {
			} catch (UnimplementedException $e) {
			}
			print "</td>";
			
			print "\n\t\t\t<td>";
			print "<a href='".$file->getHttpUrl()."'>HTTP (Download)</a>";
			print "<br/><a href='".$file->getRtmpUrl()."'>RTMP (Streaming)</a>";
			print "<br/><a href='#' onclick=\"alert('Unimplemented'); return false;\">Embed Code</a>";
			print "</td>";
			
			print "\n\t\t\t<td>";
			print _("Delete");
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