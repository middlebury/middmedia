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

/**
 * This is an admin-view manager that provides access to all directories.
 * 
 * @since 12/10/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AdminMiddMediaManager
	extends MiddMediaManager
{
		
	/**
	 * Create a new manager for the currently authenticated user.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If there is no user authenticated.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media.
	 * 
	 * @return object MiddMediaManager
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
		
		return new AdminMiddMediaManager($agentMgr->getAgent($authN->getFirstUserId()));
	}
	
	/**
	 * Create a new manager for the system user. (for use in cron jobs)
	 * 
	 * @return object MiddMediaManager
	 * @access public
	 * @since 9/25/09
	 */
	public static function forSystemUser () {
		return new AdminMiddMediaManager(new AnonymousAgent);
	}
	
	/**
	 * Answer an array of all directories
	 * 
	 * @return array of MiddMedia_Directory objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getSharedDirectories () {
		$sharedDirs = array();
		
		foreach (scandir(MIDDMEDIA_FS_BASE_DIR) as $dirname) {
			try {
				$sharedDirs[] = MiddMedia_Directory::getIfExists($this, $dirname);
			} catch(UnknownIdException $e) {
			} catch(InvalidArgumentException $e) {
			}
		}
		
		return $sharedDirs;
	}
	
	/**
	 * Create a new shared directory.
	 * 
	 * @param string $name
	 * @return object MiddMedia_Directory
	 * @access public
	 * @since 12/11/08
	 */
	public function createSharedDirectory ($name) {
		// Check that the name has only the allowed chars
		ArgumentValidator::validate($name, RegexValidatorRule::getRule('^[a-zA-Z0-9_\.&-]+$'));
		
		// Verify that the name specified doesn't already exist
		try {
			MiddMedia_Directory::getIfExists($this, $name);
			throw new OperationFailedException("Directory '$name' already exists.");
		} catch (UnknownIdException $e) {
		}
		
		// Verify that the name specified is a valid group name.
		if (!$this->isValidGroupName($name))
			throw new InvalidArgumentException("'$name' is not a valid group name.");
		
		// Create the directory.
		return MiddMedia_Directory::getAlways($this, $name);
	}
}

?>