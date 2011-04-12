<?php
/**
 * @since 10/24/08
 * @package middmedia
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
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddMediaManager {
	
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
	 * @return object MiddMediaManager
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
				return new MiddMediaManager($agentMgr->getAgent($authN->getUserId($authType)));
			}
		}
		
		throw new OperationFailedException("Could not authenticate with the credentials given.");
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
	 * @return object MiddMediaManager
	 * @access public
	 * @since 12/10/08
	 * @static
	 */
	public static function forUsernameServiceKey ($username, $serviceId, $serviceKey) {
		ArgumentValidator::validate($username, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($serviceId, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($serviceKey, NonzeroLengthStringValidatorRule::getRule());
		
		if (!isset(self::$serviceKeys[$serviceId]) || self::$serviceKeys[$serviceId] != $serviceKey)
			throw new OperationFailedException("Invalid Service ID or Service Key.");
		
		$authN = Services::getService('AuthN');
		$authNMethodMgr = Services::getService('AuthNMethods');
		
		$tokens = array('username' => $username, 'password' => '');
		
		$authTypes = $authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			
			// Try authenticating with this type
			try {
				$method = $authNMethodMgr->getAuthNMethodForType($authType);
			} catch (Exception $e) {
				continue;
			}
			
			$tokensObj = $method->createTokens($tokens);
			
			// if the the username exists allow them in.
			if ($method->tokensExist($tokensObj)) {				
				$agentMgr = Services::getService('Agent');
				return new MiddMediaManager($agentMgr->getAgent(
						$authN->_getAgentIdForAuthNTokens($tokensObj, $authType)));
			}
		}
		
		
		throw new OperationFailedException("Could not authenticate with the credentials given.");
	}
	
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
		
		return new MiddMediaManager($agentMgr->getAgent($authN->getFirstUserId()));
	}
	
	/*********************************************************
	 * Configuration Methods
	 *********************************************************/
	/**
	 * Add a new group id string that is authorized to have personal directories.
	 *
	 * ex: MiddMediaManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
	 * 
	 * @param string $groupIdString
	 * @return void
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public static function addPersonalDirectoryGroup ($groupIdString) {
		self::$personalDirectoryGroups[] = new HarmoniId($groupIdString);
	}
	
	/**
	 * Add a new service key for a service who's authentication we trust to be valid. 
	 * The usernames provided by this service will be validated, but no password 
	 * check will be done for the user as we are trusting that has already happened 
	 * at the remote service.
	 * 
	 * @param string $serviceId	An identifier for the service.
	 * @param string $key		A passphrase/key to use when connecting as this service.
	 * @return void
	 * @access public
	 * @since 12/10/08
	 */
	public static function addTrustedServiceKey ($serviceId, $key) {
		if (strlen($serviceId) < 1)
			throw new InvalidArgumentException("Service Id must be at least 1 character");
		if (strlen($key) < 8)
			throw new InvalidArgumentException("Key must be at least 8 characters");
		if (in_array($key, self::$serviceKeys))
			throw new InvalidArgumentException("Key already in use for the '".array_search($key, self::$serviceKeys)."' service");
		if (isset(self::$serviceKeys[$serviceId]))
			throw new InvalidArgumentException("Service $serviceId is already configured.");
			
		self::$serviceKeys[$serviceId] = $key;
	}
	
	/**
	 * Set the default quota for directories.
	 * 
	 * @param int $quota
	 * @return void
	 * @access public
	 * @since 12/10/08
	 */
	public function setDefaultQuota ($quota) {
		ArgumentValidator::validate($quota, IntegerValidatorRule::getRule());
		
		self::$defaultQuota = $quota;
	}
	
	/**
	 * @var int $defaultQuota;  
	 * @access private
	 * @since 12/10/08
	 */
	private static $defaultQuota = 524288000;	// 500MB
	
	/**
	 * @var array $serviceKeys;  
	 * @access private
	 * @since 12/10/08
	 */
	private static $serviceKeys = array();
	
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
	 * @return object MiddMedia_Directory
	 * @access public
	 * @since 10/24/08
	 */
	public function getPersonalDirectory () {
		if (!$this->hasPersonal())
			throw new PermissionDeniedException("You are not authorized to have a personal directory.");
		
		return MiddMedia_Directory::getAlways($this, $this->getPersonalShortname());
	}
	
	/**
	 * Answer an array of all shared directories the user can access.
	 * 
	 * @return array of MiddMedia_Directory objects
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
		
		$sharedNames = array();
		while ($containingGroups->hasNext()) {
			$group = $containingGroups->next();
			try {
				$sharedNames = array_merge($sharedNames, $this->getGroupDirectoryNames($group));
			} catch (OperationFailedException $e) {
// 				printpre($e->getMessage());
			}
		}
		
		foreach ($sharedNames as $name) {
			try {
				$sharedDirs[] = MiddMedia_Directory::getIfExists($this, $name);
			} catch(UnknownIdException $e) {
			} catch(InvalidArgumentException $e) {
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
	 * @return object MiddMedia_Directory
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
	
	/**
	 * Answer the default quota (in bytes) associated with this manager
	 * 
	 * @return int
	 * @access public
	 * @since 12/10/08
	 */
	public function getDefaultQuota () {
		return self::$defaultQuota;
	}
	
	/**
	 * Answer an array of group names by search
	 * 
	 * @param string $searchTerm
	 * @return array
	 * @access public
	 * @since 12/11/08
	 */
	public function getGroupNamesBySearch ($searchTerm) {
		$results = array();
		
		// Return an empty array if there is no search term.
		if (!strlen($searchTerm))
			return $results;
			
		$agentManager = Services::getService("Agent");
		$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
		$string = "*".$searchTerm."*";
		
		$groups = $agentManager->getGroupsBySearch($string, $searchType);
		
		while ($groups->hasNext()) {
			try {
				$results = array_merge($results, $this->getGroupDirectoryNames($groups->next()));
			} catch (Exception $e) {
			}
		}
		
		return $results;
	}
	
	/**
	 * Answer an array of possible directory name strings for a group
	 * 
	 * @param Group $group
	 * @return array of strings
	 * @access protected
	 * @since 10/6/09
	 */
	protected function getGroupDirectoryNames (Group $group) {
		// Pull out the directory name property
		if (defined('MIDDMEDIA_GROUP_DIRNAME_PROPERTY')) {
			$propertiesIterator = $group->getProperties();
			while ($propertiesIterator->hasNext()) {
				$properties = $propertiesIterator->next();
				try {
					if ($properties->getProperty(MIDDMEDIA_GROUP_DIRNAME_PROPERTY)) {
						return array($properties->getProperty(MIDDMEDIA_GROUP_DIRNAME_PROPERTY));
					}
				} catch (Exception $e) {
				}
			}
			throw new OperationFailedException('Could not find group dirname property '.MIDDMEDIA_GROUP_DIRNAME_PROPERTY.' for group '.$group->getId()->getIdString());
		} 
		// Use the callback to extract a directory name
		else if (defined('MIDDMEDIA_GROUP_DIRNAME_CALLBACK')) {
			if (!function_exists(MIDDMEDIA_GROUP_DIRNAME_CALLBACK))
				throw new ConfigurationErrorException('Funcion '.MIDDMEDIA_GROUP_DIRNAME_CALLBACK.' is not defined.');
			return call_user_func(MIDDMEDIA_GROUP_DIRNAME_CALLBACK, $group);
		} else {
			throw new ConfigurationErrorException('MIDDMEDIA_GROUP_DIRNAME_PROPERTY or MIDDMEDIA_GROUP_DIRNAME_CALLBACK must be configured.');
		}
	}
	
	/**
	 * Answer true if the name specified is a valid group name
	 * 
	 * @param string $groupName
	 * @return boolean
	 * @access public
	 * @since 12/11/08
	 */
	public function isValidGroupName ($groupName) {
		foreach ($this->getGroupNamesBySearch($groupName) as $match) {
			if ($match == $groupName)
				return true;
		}
		return false;
	}
	
	/*********************************************************
	 * Private Methods
	 *********************************************************/
	
		
	/**
	 * Constructor
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access protected
	 * @since 10/24/08
	 */
	protected function __construct (Agent $agent) {
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
		$idManager = Services::getService("Id");
		$agentManager = Services::getService("Agent");
		
		$userId = $this->_agent->getId();
		
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
		$allProperties = $this->_agent->getProperties();		
		$email = null;
		while ($allProperties->hasNext() && !$email) {
			$properties = $allProperties->next();
			$email = $properties->getProperty("email");
			if (!$email)
				$email = $properties->getProperty("EMail");
		}
		
		if (!$email)
			throw new OperationFailedException("No email found for agentId, '".$this->_agent->getId()->getIdString()."'.");
		
		return substr($email, 0, strpos($email, '@'));
	}
	
}

?>