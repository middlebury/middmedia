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
 * Answer a list of groups matching the search parameters.
 * 
 * @since 11/13/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class get_groupsAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		$authZMgr = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		return $authZMgr->isUserAuthorized(
				$idMgr->getId('edu.middlebury.authorization.modify'),
				$idMgr->getId('edu.middlebury.authorization.root'));
	}
	
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/13/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute()) {
			print "Permission denied.";
			exit;
		}
		
		
		print "<ul>";
		$manager = MiddMedia_Manager_Admin::forCurrentUser();
		
		foreach ($manager->getGroupNamesBySearch(RequestContext::value('group')) as $group) {
			print "\n\t<li>".$group."</li>";
		}
		
		print "</ul>";
		
		
		exit;
	}
}

?>