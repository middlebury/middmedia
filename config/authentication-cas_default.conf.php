<?php

/**
 * Set up the the LDAP authentication method.
 *
 * To add a second LDAP Authentication Method:
 * 		1. copy this file to a new name such as 'authentication-ldap2.conf.php' 
 *		2. Use a unique type for the new authentication method such as:
 *			$type = new Type ("Authentication", "edu.example", "Secondary LDAP");
 *		3. Update the authentications_sources.conf.php to add this new configuration:
 *			$authenticationSources = array(
 *				"db",
 *			 	"ldap",
 *				"ldap2",
 *				"visitors"
 *			);
 *
 * USAGE: Copy this file to authentication_manager.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNTokens.class.php");	
require_once(HARMONI."/oki2/authentication/CasTokenCollector.class.php");	
 		
/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new CASAuthNMethod;
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('CAS_DEBUG_PATH', '/tmp/harmoni_cas.out');
	$configuration->addProperty("CAS_HOST", "login.middlebury.edu");
	$configuration->addProperty("CAS_PORT", "443");
	$configuration->addProperty("CAS_PATH", "/cas/");
	$configuration->addProperty("CAS_CERT", "/etc/pki/tls/certs/ca-bundle.crt");
	$configuration->addProperty("CALLBACK_URL", "https://chisel.middlebury.edu/~afranco/directory_client_test/storePGT.php");
	$configuration->addProperty("CASDIRECTORY_BASE_URL", "http://chisel.middlebury.edu/~afranco/directory/");
	$configuration->addProperty("CASDIRECTORY_ADMIN_ACCESS", "sdfj239ug2jasdgae01jLKJ");
	$configuration->addProperty("DISPLAY_NAME_FORMAT", "[[FirstName]] [[LastName]]");
	
	$rootGroups = array(
// 		'OU=Groups,DC=middlebury,DC=edu',
		'OU=web data,DC=middlebury,DC=edu',
	);
	$configuration->addProperty("ROOT_GROUPS", $rootGroups);
	
	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");
	
	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new CasTokenCollector();

