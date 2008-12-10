<?php
/**
 * @since 11/19/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/upload.act.php');

/**
 * Update a directory quota
 * 
 * @since 11/19/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class update_quotaAction
	extends UploadAction
{
		
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
		
		try {
			$dir = $this->getDirectory();
			
			if (RequestContext::value('quota') == '')
				$dir->removeCustomQuota();
			else {
				$quota = ByteSize::fromString(RequestContext::value('quota'));
				$dir->setCustomQuota($quota->value());
			}
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
		
		// Return output to the browser (only supported by SWFUpload for Flash Player 9)
		header("HTTP/1.1 200 OK");
		header("Content-Type: text/xml");
		print '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
		print "\n\t\t<directory name=\"".str_replace('&', '&amp;', $dir->getBaseName())."\" ";
		print "custom_quota='".(($dir->hasCustomQuota())?$quota->value():'')."' ";
		print "default_quota='".$dir->getDefaultQuota()."' />";
		
		// Log the success
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("MiddTube");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Admin",
							"Admin events.");
			
			$item = new AgentNodeEntryItem("Quota Changed", "Quota for '".$dir->getFsPath()."' changed to '".((isset($quota))?$quota->asString():'Default')."'.");
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		exit;
	}
	
	/**
	 * Answer and error name
	 * 
	 * @return string
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getErrorName () {
		return "Quota Change Failed";
	}
	
	/**
	 * Answer and error prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getErrorPrefix () {
		return "Quota Change failed with message: ";
	}
	
	/**
	 * Answer the manager to use
	 * 
	 * @return MiddTubeManager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return AdminMiddTubeManager::forCurrentUser();
	}
	
}

?>