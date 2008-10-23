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

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNTokens.class.php");	
 		
/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new LDAPAuthNMethod;
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('tokens_class', 'LDAPAuthNTokens');
	$configuration->addProperty("LDAPHost", "ad.middlebury.edu");
	$configuration->addProperty("UserBaseDN", "cn=users,dc=middlebury,dc=edu");
	$configuration->addProperty("ClassesBaseDN", "ou=classes,ou=groups,dc=middlebury,dc=edu");
	$configuration->addProperty("GroupBaseDN", "ou=groups,dc=middlebury,dc=edu");
	$configuration->addProperty("bindDN", "readonly_username");
	$configuration->addProperty("bindDNPassword", "password");
	$propertiesFields = array (
		'username' => 'samaccountname',
		'name' =>  'displayname',
		'first name' =>  'givenname',
		'last name' =>  'sn',
		'department' =>  'department',
		'email' =>  'mail',
	);
	$configuration->addProperty('properties_fields', $propertiesFields);
	$loginFields = array (
		'samaccountname', 
		'mail',
		'cn',
	);
	$configuration->addProperty('login_fields', $loginFields);
	$configuration->addProperty("display_name_property", "name");
	
	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "LDAP");
	
	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new FormActionNamePassTokenCollector(
		$harmoni->request->quickURL("auth","username_password_form"));

