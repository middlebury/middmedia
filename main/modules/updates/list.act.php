<?php
/**
 * @since 3/5/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.2 2008/04/21 17:44:27 adamfranco Exp $
 */ 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This action builds a list of updates
 * 
 * @since 3/5/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.2 2008/04/21 17:44:27 adamfranco Exp $
 */
class listAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
	 * Build the content of this action
	 * 
	 * @return void
	 * @access public
	 * @since 3/5/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$centerPane =$this->getActionRows();
		$updatesToRun = array();
		$updatesInPlace = array();
		
		$this->loadUpdates();
		
		ob_start();
		
		print "\n<table border='1' width='100%'>";
		print "\n\t<tr>";
		print "\n\t\t<th>"._('Date')."</th>";
		print "\n\t\t<th>"._('Title')."</th>";
		print "\n\t\t<th>"._('Description')."</th>";
		print "\n\t\t<th>"._('State')."</th>";
		print "\n\t</tr>";
		
		$this->printUpdateRows();
		
		print "\n</table>";
		
		$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, CENTER, TOP);
	}
	
	/**
	 * Print out the update Rows.
	 * 
	 * @return void
	 * @access protected
	 * @since 6/12/08
	 */
	protected function printUpdateRows () {
		foreach($this->updateClasses as $name) {
			$this->printRow($name);
		}
	}
	
	/**
	 * Print out an update row
	 * 
	 * @param string $name
	 * @return void
	 * @access protected
	 * @since 6/12/08
	 */
	protected function printRow ($name) {
		$harmoni = Harmoni::instance();
		$className = $name.'Action';
		$update = new $className;
		print "\n\t<tr>";
		$date = $update->getDateIntroduced();
		print "\n\t\t<td style='white-space: nowrap;'>".$date->asString()."</td>";
		print "\n\t\t<td style='white-space: nowrap;'>".$update->getTitle()."</td>";
		print "\n\t\t<td>".$update->getDescription()."</td>";
		print "\n\t\t<td style='white-space: nowrap;'>";
		if ($this->shouldCheckSeparate() && isset($update->checkSeparate) && $update->checkSeparate) {
			print "<a href='";
			print $harmoni->request->quickURL('updates', 'check', array('update' => $name));
			print "' title='"._('Check the status of this Update')."'>";
			print ("Check...");
			print "</a>";
		} else if ($update->isInPlace()) {
			print _("In Place");
		} else {
			print "<a href='";
			print $harmoni->request->quickURL('updates', $name);
			print "' title='"._('Run this Update')."'>";
			print ("Run");
			print "</a>";
		}
		print "</td>";
		print "\n\t</tr>";
	}
	
	/**
	 * Answer true if we should print "check..." links
	 * 
	 * @return boolean
	 * @access protected
	 * @since 6/12/08
	 */
	public function shouldCheckSeparate () {
		return true;
	}
	
	/**
	 * Load the update classes
	 * 
	 * @return void
	 * @access public
	 * @since 3/6/07
	 */
	function loadUpdates () {
		// Include all updaters
		$this->updateClasses = array();
		$dir = dirname(__FILE__);
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false) {
			if ($file != __FILE__
				&& $file != 'check.act.php'
				&& filetype($dir .'/'. $file) == 'file' 
				&& preg_match('/^([a-z0-9_]+)\.act\.php$/i', $file, $matches))
			{
				if ($matches[1] != 'list') {
					require_once($dir .'/'. $file);
					$this->updateClasses[] = $matches[1];
				}
			}
		}
		closedir($handle); 
		
		sort($this->updateClasses);
	}
	
}

?>