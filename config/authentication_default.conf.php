<?php

/**
 * Set up the AuthenticationManager and associated Authentication modules
 *
 * USAGE: Copy this file to authentication.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Start the AuthenticationManager OSID Impl.
	$configuration = new ConfigurationProperties;
	$tokenCollectors = array(
		serialize(new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB")) 
			=> new FormActionNamePassTokenCollector($harmoni->request->quickURL("auth","username_password_form")),
// 		serialize(new Type ("Authentication", "edu.middlebury.harmoni", "Middlebury LDAP")) 
// 			=> new FormActionNamePassTokenCollector($harmoni->request->quickURL("auth","username_password_form")),
	);
	$configuration->addProperty('token_collectors', $tokenCollectors);
	Services::startManagerAsService("AuthenticationManager", $context, $configuration);


// :: Start and configure the AuthenticationMethodManager
	$configuration = new ConfigurationProperties;
	
		// set up a Database Authentication Method
		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseAuthNMethod.class.php");
		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseMD5UsernamePasswordAuthNTokens.class.php");
		$dbAuthType = new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB");
		$dbMethodConfiguration = new ConfigurationProperties;
		$dbMethodConfiguration->addProperty('tokens_class', 'SQLDatabaseMD5UsernamePasswordAuthNTokens');
		$dbMethodConfiguration->addProperty('database_id', HARMONI_DB_INDEX);
		$dbMethodConfiguration->addProperty('authentication_table', 'auth_db_user');
		$dbMethodConfiguration->addProperty('username_field', 'username');
		$dbMethodConfiguration->addProperty('password_field', 'password');
		$propertiesFields = array(
			'username' => 'username',
//			'name'=> 'display_name',
		);
		$dbMethodConfiguration->addProperty('properties_fields', $propertiesFields);
		
		$dbAuthNMethod = new SQLDatabaseAuthNMethod;
		$dbAuthNMethod->assignConfiguration($dbMethodConfiguration);
		
	$configuration->addProperty($dbAuthType, $dbAuthNMethod);
	
	$GLOBALS["NewUserAuthNType"] =$dbAuthType;
		
		// set up LDAPAuthentication Method
// 		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNMethod.class.php");
// 		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNTokens.class.php");	
// 		$ldapAuthType = new Type ("Authentication", "edu.middlebury.harmoni", "Middlebury LDAP");
// 		$ldapConfiguration = new ConfigurationProperties;
// 		$ldapConfiguration->addProperty('tokens_class', 'LDAPAuthNTokens');
// 		$ldapConfiguration->addProperty("LDAPHost", "ad.middlebury.edu");
// 		$ldapConfiguration->addProperty("UserBaseDN", "cn=users,dc=middlebury,dc=edu");
// 		$ldapConfiguration->addProperty("GroupBaseDN", "ou=groups,dc=middlebury,dc=edu");
// 		$ldapConfiguration->addProperty("bindDN", "juser");
// 		$ldapConfiguration->addProperty("bindDNPassword", "password");
// 		$propertiesFields = array (
// 			'username' => 'samaccountname',
// 			'name' =>  'displayname',
// 			'first name' =>  'givenname',
// 			'last name' =>  'sn',
// 			'department' =>  'department',
// 			'email' =>  'mail',
// 		);
// 		$ldapConfiguration->addProperty('properties_fields', $propertiesFields);
// 		$loginFields = array (
// 			'samaccountname', 
// 			'mail',
// 			'cn',
// 		);
// 		$ldapConfiguration->addProperty('login_fields', $loginFields);
// 		$ldapConfiguration->addProperty("display_name_property", "name");
// 
// 		$ldapAuthNMethod = new LDAPAuthNMethod;
// 		$ldapAuthNMethod->assignConfiguration($ldapConfiguration);
// 		
// 	$configuration->addProperty($ldapAuthType, $ldapAuthNMethod);
	
	Services::startManagerAsService("AuthNMethodManager", $context, $configuration);
	
	
// :: Agent-Token Mapping Manager ::	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_id', HARMONI_DB_INDEX);
	Services::startManagerAsService("AgentTokenMappingManager", $context, $configuration);