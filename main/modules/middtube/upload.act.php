<?php
/**
 * @since 11/13/08
 * @package middtube
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
 * @package middtube
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
		$extension_whitelist = array("mp4", "flv", "mp3");	// Allowed file extensions
		$valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';				// Characters allowed in the file name (in a Regular Expression format)
		$upload_name = "Filedata";
		$MAX_FILENAME_LENGTH = 260;
		
		
		$dir = $this->getDirectory();
		
		
		if (!isset($_FILES[$upload_name]))
			$this->error('No file uploaded');
		
		if ($_FILES[$upload_name]['error'])
			$this->error('An error occurred with the file upload: '.$uploadErrors[$_FILES[$upload_name]['error']]);
			
		if (!$_FILES[$upload_name]['size'])
			$this->error('Uploaded file is empty');
		
		if ($_FILES[$upload_name]['size'] > ($dir->getBytesAvailable()))
			$this->error('File upload exceeds quota.');
		
		// Validate file name (for our purposes we'll just remove invalid characters)
		$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
		if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
			$this->error("Invalid file name");
		}
		
		if (!MiddTube_File::nameValid($file_name)) {
			$this->error("Invalid file name");
		}
		
		// Validate file extension
		$path_info = pathinfo($_FILES[$upload_name]['name']);
		$file_extension = $path_info["extension"];
		$is_valid_extension = false;
		foreach ($extension_whitelist as $extension) {
			if (strcasecmp($file_extension, $extension) == 0) {
				$is_valid_extension = true;
				break;
			}
		}
		if (!$is_valid_extension) {
			$this->error("Invalid file extension");
		}
		
		// Validate that the file doesn't already exist.
		if ($dir->fileExists($file_name))
			$this->error("File '$file_name' already exists.");
		
		try {
			$file = $dir->createFile($file_name);
// 			$file->putContents(file_get_contents($_FILES[$upload_name]["tmp_name"]));
			$file->moveInUploadedFile($_FILES[$upload_name]["tmp_name"]);
		} catch (Exception $e) {
			$this->error("File could not be saved to '".$dir->getBaseName().'/'.$file_name."'. ".$e->getMessage());
		}
				
		// Return output to the browser (only supported by SWFUpload for Flash Player 9)
		header("HTTP/1.1 200 OK");
		print '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
		print "\n\t\t<file
				name=\"".str_replace('&', '&amp;', $file->getBaseName())."\"
				directory=\"".$dir->getBaseName()."\"
				http_url=\"".$file->getHttpUrl()."\"
				rtmp_url=\"".$file->getRtmpUrl()."\"
				mime_type=\"".$file->getMimeType()."\"
				size=\"".$file->getSize()."\"
				modification_date=\"".$file->getModificationDate()->asLocal()->asString()."\"";
		
		try {
			print "\n\t\t\tcreator_name=\"".$file->getCreator()->getDisplayName()."\"";
		} catch (OperationFailedException $e) {
		} catch (UnimplementedException $e) {
		}
		
		// As an example, lets include the content of text-files.
// 		if ($file->getMimeType() == 'text/plain') {
// 			print "><![CDATA[";
// 			print $file->getContents();
// 			print "]]></file>";
// 		} else {
			print "/>";
// 		}
		
		// Log the success
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("MiddTube");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Error events.");
			
			$item = new AgentNodeEntryItem("Upload Success", "File '".$dir->getFsPath().'/'.$file_name."' uploaded.");
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		exit;
	}
	
	/**
	 * Answer the target directory object
	 * 
	 * @return object MiddTube_Directory
	 * @access protected
	 * @since 11/19/08
	 */
	protected function getDirectory () {
		if (!isset($this->directory)) {
			$manager = MiddTubeManager::forCurrentUser();
			$this->directory = $manager->getDirectory(RequestContext::value('directory'));
		}
		
		return $this->directory;
	}
	
	/**
	 * @var object MiddTube_Directory $directory;  
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
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("MiddTube");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Error events.");
			
			$item = new AgentNodeEntryItem("Upload Failed", "File upload failed with message: ".$errorString);
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		header("HTTP/1.1 500 Internal Server Error");
		echo $errorString;
		exit;
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