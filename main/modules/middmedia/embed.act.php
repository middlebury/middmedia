<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * Provide embed code
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class embedAction
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
		
		$dir = $this->getDirectory();
		
		$file = $dir->getFile(RequestContext::value('file'));
		
		$httpUrl = $file->getHttpUrl();
		$rtmpUrl = $file->getRtmpUrl();
		$splashImage = $file->getSplashImage();
		
		$plugins = EmbedPlugins::instance();
		
		foreach ($plugins->getPlugins() as $embed) {
			print '<h3>'.$embed->GetTitle().'</h3>';
			print $embed->GetDesc($file);
			print $embed->GetMarkup($file);	
		}
		
		exit;

	}
	
	/**
	 * Answer the target directory object
	 * 
	 * @return object MiddMedia_Directory
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
	 * @return MiddMediaManager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMediaManager::forCurrentUser();
	}
	
	/**
	 * @var object MiddMedia_Directory $directory;  
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