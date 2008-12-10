<?php
/**
 * @since 12/10/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This is an admin-view manager that provides access to all directories.
 * 
 * @since 12/10/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AdminMiddTubeManager
	extends MiddTubeManager
{
		
	/**
	 * Create a new manager for the currently authenticated user.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If there is no user authenticated.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media.
	 * 
	 * @return object MiddTubeManager
	 * @access public
	 * @since 10/24/08
	 */
	public static function forCurrentUser () {
		$authN = Services::getService('AuthN');
		$agentMgr = Services::getService('Agent');
		
		if (!$authN->isUserAuthenticatedWithAnyType())
			throw new OperationFailedException("No user authenticated");
		
		$authZMgr = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		if (!$authZMgr->isUserAuthorized(
				$idMgr->getId('edu.middlebury.authorization.modify'),
				$idMgr->getId('edu.middlebury.authorization.root')))
			throw new PermissionDeniedException('Unauthorized to manage this system.');
		
		return new AdminMiddTubeManager($agentMgr->getAgent($authN->getFirstUserId()));
	}
	
	/**
	 * Answer an array of all directories
	 * 
	 * @return array of MiddTube_Directory objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getSharedDirectories () {
		$sharedDirs = array();
		
		foreach (scandir(MIDDTUBE_FS_BASE_DIR) as $dirname) {
			try {
				$sharedDirs[] = MiddTube_Directory::getIfExists($this, $dirname);
			} catch(UnknownIdException $e) {
			} catch(InvalidArgumentException $e) {
			}
		}
		
		return $sharedDirs;
	}
}

?>