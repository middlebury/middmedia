<?php
/**
 * @since 11/13/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * Handle a file-upload
 * 
 * @since 11/13/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class uploadAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/13/08
	 */
	public function isAuthorizedToExecute () {
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		if (!$authN->isUserAuthenticatedWithAnyType())
			return false;
		try {
			$dir = $this->getDirectory();
		} catch (PermissionDeniedException $e) {
			return false;
		}
		return true;
	}
	
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/13/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->error("Permission denied");
		
		$uploadErrors = array(
			0=>"There is no error, the file uploaded with success",
			1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
			2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
			3=>"The uploaded file was only partially uploaded",
			4=>"No file was uploaded",
			6=>"Missing a temporary folder"
		);
		
		$upload_name = "Filedata";
				
		
		$dir = $this->getDirectory();
		
		
		if (!isset($_FILES[$upload_name]))
			$this->error('No file uploaded');
		
		try {
			$file = $dir->createFileFromUpload($_FILES[$upload_name]);
			return $this->success($dir, $file);
		} catch (Exception $e) {
			return $this->error("File could not be saved to '".$dir->getBaseName().'/'.$_FILES[$upload_name]['name']."'. ".$e->getMessage());
		}
	}
	
	/**
	 * Answer a success message and file info if needed
	 * 
	 * @param MiddMedia_DirectoryInterface $dir
	 * @param MiddMedia_File $file
	 * @return mixed
	 * @access protected
	 * @since 11/19/09
	 */
	protected function success (MiddMedia_DirectoryInterface $dir, MiddMedia_File_Media $file) {
		// Return output to the browser (only supported by SWFUpload for Flash Player 9)
		header("HTTP/1.1 200 OK");
		header("Content-type: text/xml");
		print '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
		$primaryFormat = $file->getPrimaryFormat();
		if ($primaryFormat->supportsHttp())
			$httpUrl = $primaryFormat->getHttpUrl();
		else
			$httpUrl = '';
		if ($primaryFormat->supportsRtmp())
			$rtmpUrl = $primaryFormat->getRtmpUrl();
		else
			$rtmpUrl = '';
		print "\n\t\t<file
				name=\"".str_replace('&', '&amp;', $file->getBaseName())."\"
				directory=\"".$dir->getBaseName()."\"
				http_url=\"".$httpUrl."\"
				rtmp_url=\"".$rtmpUrl."\"
				mime_type=\"".$file->getMimeType()."\"
				size=\"".$file->getSize()."\"
				modification_date=\"".$file->getModificationDate()->asLocal()->asString()."\"";
		
		try {
			print "\n\t\t\tcreator_name=\"".$file->getCreator()->getDisplayName()."\"";
		} catch (OperationFailedException $e) {
		} catch (UnimplementedException $e) {
		}
		
		try {
			$format = $file->getFormat('thumb');
			print "\n\t\t\tthumb_url=\"".$format->getHttpUrl()."\"";
		} catch (Exception $e) {
			print "\n\t\t\tthumb_url=\"\"";
		}
		
		try {
			$format = $file->getFormat('splash');
			print "\n\t\t\tsplash_url=\"".$format->getHttpUrl()."\"";
		} catch (Exception $e) {
			print "\n\t\t\tsplash_url=\"\"";
		}
		
		// As an example, lets include the content of text-files.
// 		if ($file->getMimeType() == 'text/plain') {
// 			print "><![CDATA[";
// 			print $file->getContents();
// 			print "]]></file>";
// 		} else {
			print "/>";
// 		}

		exit;
	}
	
	/**
	 * Answer the target directory object
	 * 
	 * @return object MiddMedia_DirectoryInterface
	 * @access protected
	 * @since 11/19/08
	 */
	protected function getDirectory () {
		if (!isset($this->directory)) {
			$manager = $this->getManager();
			$this->directory = $manager->getDirectory(RequestContext::value('directory'));
		}
		
		return $this->directory;
	}
	
	/**
	 * Answer the manager to use
	 * 
	 * @return MiddMedia_Manager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMedia_Manager::forCurrentUser();
	}
	
	/**
	 * @var object MiddMedia_DirectoryInterface $directory;  
	 * @access private
	 * @since 11/19/08
	 */
	private $directory;
	
	/**
	 * Send an error header and string.
	 * 
	 * @param string $errorString
	 * @return void
	 * @access protected
	 * @since 11/13/08
	 */
	protected function error ($errorString) {
		$this->logError($errorString);
		
		header("HTTP/1.1 500 Internal Server Error");
		echo $errorString;
		exit;
	}
	
	/**
	 * Log an error
	 * 
	 * @param string $errorString
	 * @return void
	 * @access public
	 * @since 11/19/09
	 */
	public function logError ($errorString) {
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("MiddMedia");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Error events.");
			
			$item = new AgentNodeEntryItem($this->getErrorName(), $this->getErrorPrefix().$errorString);
			
			$idManager = Services::getService("Id");
							
			$item->addNodeId($idManager->getId('middmedia:'.$this->getDirectory()->getBaseName().'/'));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
	}
	
	/**
	 * Answer and error name
	 * 
	 * @return string
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getErrorName () {
		return "Upload Failed";
	}
	
	/**
	 * Answer and error prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getErrorPrefix () {
		return "File upload failed with message: ";
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