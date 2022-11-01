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

require_once(dirname(__FILE__).'/../vendor/autoload.php');

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNTokens.class.php");
require_once(HARMONI."/oki2/authentication/CasTokenCollector.class.php");

/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new CASAuthNMethod;

	$configuration = new ConfigurationProperties;
	// use Monolog\Handler\StreamHandler;
	// use Monolog\Handler\ErrorLogHandler;
	// use Monolog\Logger;
	// $logger = new Logger('phpcas');
	//$logger->pushHandler(new ErrorLogHandler());
	//$logger->pushHandler(new StreamHandler('/tmp/harmoni_cas.out'));
	// $configuration->addProperty('CAS_LOGGER', $logger);
	$configuration->addProperty("CAS_HOST", "login.middlebury.edu");
	$configuration->addProperty("CAS_PORT", "443");
	$configuration->addProperty("CAS_PATH", "/cas/");
	$configuration->addProperty("CAS_CERT", "/etc/pki/tls/certs/ca-bundle.crt");
	$configuration->addProperty("CAS_SERVICE_BASE_URL", "https://chisel.middlebury.edu");
	// $configuration->addProperty("CALLBACK_URL", "https://chisel.middlebury.edu/~afranco/directory_client_test/storePGT.php");
	// $configuration->addProperty("CASDIRECTORY_BASE_URL", "http://chisel.middlebury.edu/~afranco/directory/");
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

/*********************************************************
 * Replace the username/password form in the head with a CAS link
 *********************************************************/
define('LOGIN_FORM_CALLBACK', 'getCasLoginLink');
function getCasLoginLink () {
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");
	$harmoni = Harmoni::instance();
	$harmoni->request->startNamespace("polyphony");
	$url = "<a href='".$harmoni->request->quickURL('auth', 'login_type', array('type' => $type->asString()))."'>Log In</a>";
	$harmoni->request->endNamespace();

    $harmoni->history->markReturnURL("polyphony/login", $harmoni->request->mkURL());

	return $url;
}


/*********************************************************
 * Uncomment and customize to enable a mapping update script
 * to associate LDAP logins with CAS logins
 *********************************************************/
// global $update001Types, $update001CasType;
//
// $update001Types[] = array(
// 	'type' => new Type ("Authentication", "edu.middlebury.harmoni", "LDAP"),
// 	'cas_id_property' => 'middleburycollegeuid'
// );
//
// $update001CasType = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");
