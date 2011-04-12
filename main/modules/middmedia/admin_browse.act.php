<?php
/**
 * @since 12/10/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/browse.act.php');

/**
 * Browse all media as an admin
 * 
 * @since 12/10/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class admin_browseAction
	extends browseAction
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
	 * Answer the manager to use for this action.
	 * 
	 * @return MiddMediaMangager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMedia_Manager_Admin::forCurrentUser();
	}
	
}

?>