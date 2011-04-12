<?php
/**
 * @since 7/24/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../Directory/Unauthenticated.class.php');

/**
 * This manager provides unauthenticated direct access to files. Because it is unauthenticated,
 * no browsing is permitted, only getDirectory() and the directory's getFile() methods are available.
 * The results given by this manager are cachable.
 * 
 * @since 7/24/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Manager_Unauthenticated
	extends MiddMedia_Manager
{
		
	/*********************************************************
	 * Instance Creation Methods
	 *********************************************************/
	/**
	 * Create a new manager for a username/password pair. 
	 * The username can be a samaccountname, email, or DN.
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If authentication fails for the parameters
	 *		PermissionDeniedException 	- If authentication succeeds, but the user is 
	 *									  unauthorized to manage media.
	 * 
	 * @param string $username
	 * @param string $password
	 * @return object MiddMedia_Manager
	 * @access public
	 * @since 10/24/08
	 * @static
	 */
	public static function forUsernamePassword ($username, $password) {
		return self::instance();
	}
	
	/**
	 * Create a new manager for a username and shared key. 
	 * The username can be a samaccountname, email, or DN.
	 * The shared key is a secret known to both MiddMedia and other systems it trusts
	 * to authenticate users.
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If authentication fails for the parameters
	 *		PermissionDeniedException 	- If authentication succeeds, but the user is 
	 *									  unauthorized to manage media.
	 * 
	 * @param string $username
	 * @param string $serviceId
	 * @param string $serviceKey
	 * @return object MiddMedia_Manager
	 * @access public
	 * @since 12/10/08
	 * @static
	 */
	public static function forUsernameServiceKey ($username, $serviceId, $serviceKey) {
		return self::instance();
	}
	
	/**
	 * Create a new manager for the currently authenticated user.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If there is no user authenticated.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media.
	 * 
	 * @return object MiddMedia_Manager
	 * @access public
	 * @since 10/24/08
	 */
	public static function forCurrentUser () {
		return self::instance();
	}
	/**
	 * Create a new Manager
	 * 
	 * @return object MiddMedia_Manager
	 * @access public
	 * @since 7/24/09
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance))
			self::$instance = new MiddMedia_Manager_Unauthenticated;
		
		return self::$instance;
	}
	private static $instance;
	
	/*********************************************************
	 * End Instance Creation Methods
	 *********************************************************/
	
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access protected
	 * @since 7/24/09
	 */
	protected function __construct () {
		parent::__construct(new AnonymousAgent);
	}
	
	/**
	 * Answer the personal directory for the user
	 * 
	 * This method throws the following exceptions:
	 *		PermissionDeniedException 	- If the user is unauthorized to have a personal directory.
	 *
	 * @return object MiddMedia_Directory
	 * @access public
	 * @since 10/24/08
	 */
	public function getPersonalDirectory () {
		throw new PermissionDeniedException("You are not authorized to have a personal directory.");
	}
	
	/**
	 * Answer an array of all shared directories the user can access.
	 * 
	 * @return array of MiddMedia_Directory objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getSharedDirectories () {
		return array();
	}
	
	/**
	 * Answer a particular directory by name. 
	 *
	 * This method throws the following exceptions:
	 *		PermissionDeniedException 	- If the user is unauthorized to access the directory.
	 * 
	 * @param string $name
	 * @return object MiddMedia_Directory
	 * @access public
	 * @since 11/13/08
	 */
	public function getDirectory ($name) {
		return MiddMedia_Directory_Unauthenticated::getIfExists($this, $name);
	}
}

?>