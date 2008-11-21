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
		
		return new MiddTubeManager($agentMgr->getAgent($authN->getFirstUserId()));
	}
	
	/*********************************************************
	 * Configuration Methods
	 *********************************************************/
	/**
	 * Add a new group id string that is authorized to have personal directories.
	 *
	 * ex: MiddTubeManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
	 * 
	 * @param string $groupIdString
	 * @return void
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public function addPersonalDirectoryGroup ($groupIdString) {
		self::$personalDirectoryGroups[] = new HarmoniId($groupIdString);
	}
	
	/**
	 * @var array $personalDirectoryGroups;  
	 * @access private
	 * @since 11/13/08
	 */
	private static $personalDirectoryGroups = array();
	
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
		if (!$this->hasPersonal())
			throw new PermissionDeniedException("You are not authorized to have a personal directory.");
		
		return MiddTube_Directory::getAlways($this, $this->getPersonalShortname());
	}
	
	/**
	 * Answer an array of all shared directories the user can access.
	 * 
	 * @return array of MiddTube_Directory objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getSharedDirectories () {
		// Go through all groups the agent is a member of and add those for whom
		// directories exist.
		$sharedDirs = array();
		$agentManager = Services::getService("Agent");
		$ancestorSearchType = new HarmoniType("Agent & Group Search",
													"edu.middlebury.harmoni","AncestorGroups");
		$containingGroups = $agentManager->getGroupsBySearch(
								$this->_agent->getId(), $ancestorSearchType);
		
		while ($containingGroups->hasNext()) {
			$group = $containingGroups->next();
			try {
				$propertiesIterator = $group->getProperties();
				$dirname = null;
				while (!$dirname && $propertiesIterator->hasNext()) {
					$properties = $propertiesIterator->next();
					try {
						$dirname = $properties->getProperty(MIDDTUBE_GROUP_DIRNAME_PROPERTY);
					} catch(UnknownIdException $e) {
					}
				}
				if ($dirname) {
					try {
						$sharedDirs[] = MiddTube_Directory::getIfExists($this, $dirname);
					} catch(UnknownIdException $e) {
					}
				}
			} catch (OperationFailedException $e) {
// 				printpre($e->getMessage());
			}
		}
		
		return $sharedDirs;
	}
	
	/**
	 * Answer a particular directory by name. 
	 *
	 * This method throws the following exceptions:
	 *		PermissionDeniedException 	- If the user is unauthorized to access the directory.
	 * 
	 * @param string $name
	 * @return object MiddTube_Directory
	 * @access public
	 * @since 11/13/08
	 */
	public function getDirectory ($name) {
		// see if this is our personal directory
		try {
			$dir = $this->getPersonalDirectory();
			if ($dir->getBaseName() == $name)
				return $dir;
		} catch (PermissionDeniedException $e) {
		}
		
		// See if this is one of our group directories
		foreach ($this->getSharedDirectories() as $dir) {
			if ($dir->getBaseName() == $name)
				return $dir;
		}
		
		throw new PermissionDeniedException("You are not authorized to access '$name'.");
	}
	
	/**
	 * Answer the Agent associated with this manager
	 * 
	 * @return object Agent
	 * @access public
	 * @since 11/21/08
	 */
	public function getAgent () {
		return $this->_agent;
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
	
	/**
	 * Answer true if the user has access to a personal directory
	 * 
	 * @return boolean
	 * @access private
	 * @since 8/23/07
	 */
	private function hasPersonal () {
		$authN = Services::getService("AuthN");
		$idManager = Services::getService("Id");
		$agentManager = Services::getService("Agent");
		
		$userId = $authN->getFirstUserId();
		
		if (!$userId->isEqual($idManager->getId("edu.middlebury.agents.anonymous"))) {
			// Match the groups the user is in against our configuration of
			// groups whose members should have personal sites.
			$ancestorSearchType = new HarmoniType("Agent & Group Search",
													"edu.middlebury.harmoni","AncestorGroups");
			$containingGroups = $agentManager->getGroupsBySearch(
							$userId, $ancestorSearchType);
			
			while ($containingGroups->hasNext()) {
				$group = $containingGroups->next();
				foreach (self::$personalDirectoryGroups as $validGroupId) {
					if ($validGroupId->isEqual($group->getId())) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Answer the user-shortname for an agentId
	 * 
	 * @param object Id $agentId
	 * @return string
	 * @access private
	 * @since 8/22/07
	 */
	private function getPersonalShortname () {
		$properties = $this->_agent->getProperties();		
		$email = null;
		while ($properties->hasNext() && !$email) {
			$email = $properties->next()->getProperty("email");
		}
		
		if (!$email)
			throw new OperationFailedException("No email found for agentId, '$agentId'.");
		
		return substr($email, 0, strpos($email, '@'));
	}
	
}

?>