<?php

/**
 * Set up the basic Harmoni DB authentication method.
 *
 * To add a second DB Authentication Method:
 * 		1. copy this file to a new name such as 'authentication-db2.conf.php'
 *		2. Use a unique type for the new authentication method such as:
 *			$type = new Type ("Authentication", "edu.example", "Secondary DB");
 *		3. Update the authentications_sources.conf.php to add this new configuration:
 *			$authenticationSources = array(
 *				"db",
 *				"db2",
 *			// 	"ldap",
 *				"visitors"
 *			);
 *
 * USAGE: Copy this file to authentication-db.conf.php to set custom values.
 *
 * @package segue.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseAuthNMethod.class.php");		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseMD5UsernamePasswordAuthNTokens.class.php");

/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new SQLDatabaseAuthNMethod;

	$configuration = new ConfigurationProperties;
	$configuration->addProperty('tokens_class', 'SQLDatabaseMD5UsernamePasswordAuthNTokens');
	$configuration->addProperty('database_id', HARMONI_DB_INDEX);
	$configuration->addProperty('authentication_table', 'auth_db_user');
	$configuration->addProperty('username_field', 'username');
	$configuration->addProperty('password_field', 'password');
	$propertiesFields = array(
		'username' => 'username',
	);
	$configuration->addProperty('properties_fields', $propertiesFields);

	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB");

	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new FormActionNamePassTokenCollector(
		$harmoni->request->quickURL("auth","username_password_form"));
