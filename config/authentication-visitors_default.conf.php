<?php

/**
 * Set up the Visitors DB authentication method. 
 *
 * You probably don't need to modify this configuration file, just 
 * add your reCAPTCHA API keys to segue/config/recaptcha.conf.php
 * 
 * Visitor Registration requires that you sign up for reCAPTCHA API key. 
 * These reCAPTCHA keys are defined in recaptcha.conf.php
 *
 *
 *
 * USAGE: Copy this file to authentication_manager.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

if (!defined('RECAPTCHA_PUBLIC_KEY') || !defined('RECAPTCHA_PRIVATE_KEY'))
	throw new ConfigurationErrorException("You must configure reCAPTCHA API keys in segue/config/recaptcha.conf.php to enable visitor registration.");


require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/VisitorSQLDatabaseAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseMD5UsernamePasswordAuthNTokens.class.php");

/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new VisitorSQLDatabaseAuthNMethod;
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('tokens_class', 'SQLDatabaseMD5UsernamePasswordAuthNTokens');
	$configuration->addProperty('database_id', HARMONI_DB_INDEX);
	
	// These values can be changed if needed.
	$configuration->addProperty('email_from_name', 'Segue Administrator');
	$configuration->addProperty('email_from_address', $_SERVER['SERVER_ADMIN']);
	
	// Visitors with emails from these domains will not be able  to register.
	$configuration->addProperty('domain_blacklist', array(
		'example.com', 
		'example.org', 
		'example.net', 
		'example.edu'
	));
	
	// If specified, only visitors with emails from these domains will be allowed
	// to register. Blacklist and existance checks will still apply.
// 	$configuration->addProperty('domain_whitelist', array(
// 		'example.edu'
// 	));
	
	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
	
	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new FormActionNamePassTokenCollector(
		$harmoni->request->quickURL("auth","username_password_form"));
	
