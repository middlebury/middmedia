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
    
    $plugins = MiddMedia_Embed_Plugins::instance();
    
    foreach ($plugins->getPlugins() as $embed) {
      if ($embed->isSupported($file)) {
        print "\n<h3>".$embed->getTitle()."</h3>";
        print $embed->getDesc($file);
        $markup = $embed->getMarkup($file);
        if (strlen($markup) > 150)
        	print "<textarea rows='6' cols='83'>".htmlspecialchars($markup)."</textarea>";
        else
        	print "<input type='text' size='95' value=\"".htmlspecialchars($markup)."\"/>";
        
      }
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
    return MiddMedia_Manager::forCurrentUser();
  }
  
  /**
   * Send an error header and string.
   *
   * @param string $errorString
   * @return void
   * @access protected
   * @since 11/12/15
   */
  protected function error ($errorString) {
    $this->logError($errorString);

    header("HTTP/1.1 403 Forbidden");
    echo $errorString;
    exit;
  }

  /**
   * Log an error
   *
   * @param string $errorString
   * @return void
   * @access public
   * @since 11/12/15
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
}

?>