<?php
/**
 * @since 10/24/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/Directory.class.php');

/**
 * This manager provides access to all of the functions needed by the upload and
 * video management interfaces and webservices, hiding the details of Harmoni
 * 
 * @since 10/24/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddTubeManager {
	
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
	 * @return object MiddTubeManager
	 * @access public
	 * @since 10/24/08
	 * @static
	 */
	public static function forUsernamePassword ($username, $password) {
		ArgumentValidator::validate($username, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($password, NonzeroLengthStringValidatorRule::getRule());
		
		$authN = Services::getService('AuthN');
		$authNMethodMgr = Services::getService('AuthNMethods');
		
		$tokens = array('username' => $username, 'password' => $password);
		
		$authTypes = $authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			
			// Try authenticating with this type
			try {
				$method = $authNMethodMgr->getAuthNMethodForType($authType);
			} catch (Exception $e) {
				continue;
			}
			$tokenObj = $method->createTokens($tokens);
			$authN->_authenticateTokensWithType($tokenObj, $authType);
			
			// If they are authenticated, continue
			if ($authN->isUserAuthenticated($authType)) {
				$agentMgr = Services::getService('Agent');
				return new MiddTubeManager($agentMgr->getAgent($authN->getUserId($authType)));
			}
		}
		
		throw new OperationFailedException("Could not authenticate with the credentials given.");
	}
	
	/**
	 * Create a new manager for a username and shared key. 
	 * The username can be a samaccountname, email, or DN.
	 * The shared key is a secret known to both MiddTube and other systems it trusts
	 * to authenticate users.
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If authentication fails for the parameters
	 *		PermissionDeniedException 	- If authentication succeeds, but the user is 
	 *									  unauthorized to manage media.
	 * 
	 * @param string $username
	 * @param string $sharedKey
	 * @return object MiddTubeManager
	 * @access public
	 * @since 10/24/08
	 * @static
	 */
	public static function forUsernameSharedKey ($username, $sharedKey) {
		ArgumentValidator::validate($username, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($sharedKey, NonzeroLengthStringValidatorRule::getRule());
		
		throw new UnimplementedException(); // @todo
		
		throw new OperationFailedException("Could not authenticate with the credentials given.");
	}
	
	/*********************************************************
	 * Management methods
	 *********************************************************/
	
	/**
	 * Answer the personal directory for the user
	 * 
	 * This method throws the following exceptions:
	 *		PermissionDeniedException 	- If the user is unauthorized to have a personal directory.
	 *
	 * @return object MiddTube_Directory
	 * @access public
	 * @since 10/24/08
	 */
	public function getPersonalDirectory () {
		// @todo look at the test user, check authorizations, and return (creating if needed)
		// a directory based on their email address
		// For starters, we'll just use a static directory
		return new MiddTube_Directory('testuser');
	}
	
	/**
	 * Answer an array of all shared directories the user can access.
	 * 
	 * @return array of MiddTube_Directory objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getSharedDirectories () {
		// @todo look at the test user, check authorizations, and return the shared directories
		// the user is authorized to upload to.
		// For starters, we'll just use some static directories for testing
		return array (
			new MiddTube_Directory('testgroup-a'),
			new MiddTube_Directory('testgroup-b'));
	}
	
	/*********************************************************
	 * Private Methods
	 *********************************************************/
	
		
	/**
	 * Constructor
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access private
	 * @since 10/24/08
	 */
	private function __construct (Agent $agent) {
		$this->_agent = $agent;
	}
	
	/**
	 * @var object Agent $agent 
	 * @access private
	 * @since 10/24/08
	 */
	private $agent;
	
}

?>