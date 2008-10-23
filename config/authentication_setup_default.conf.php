<?php

/**
 * This file controls authentication setup. You probably should not customize this
 * file unless you wish to use a custom Authentication OSID implementation.
 *
 * For customizing authentication sources please create custom versions of the
 * following files as needed:
 *		authentication_sources.conf.php
 *		authentication-db.conf.php
 *		authentication-ldap.conf.php
 *		authentication-visitors.conf.php
 *
 *
 * USAGE: Copy this file to authentication_setup.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/*********************************************************
 * Create the empty configuration for the AuthenticationMethodManager
 *********************************************************/
	$authenticationMethodManagerConfiguration = new ConfigurationProperties;

/*********************************************************
 * Create an array to hold token collector assignments.
 *********************************************************/
	$tokenCollectors = array();

/*********************************************************
 * Load the list of authentication sources
 *********************************************************/
if (file_exists(dirname(__FILE__).'/authentication_sources.conf.php'))
	require_once (dirname(__FILE__).'/authentication_sources.conf.php');
else
	require_once (dirname(__FILE__).'/authentication_sources_default.conf.php');

/*********************************************************
 * Load each authentication source configuration files
 *********************************************************/
foreach ($authenticationSources as $source) {
	if (file_exists(MYDIR.'/config/authentication-'.$source.'.conf.php'))
		require_once (MYDIR.'/config/authentication-'.$source.'.conf.php');
	else
		require_once (MYDIR.'/config/authentication-'.$source.'_default.conf.php');
}


/*********************************************************
 * Start the AuthenticationMethodManager
 *********************************************************/
	Services::startManagerAsService("AuthNMethodManager", $context, $authenticationMethodManagerConfiguration);

/*********************************************************
 * Agent-Token Mapping Manager
 *********************************************************/
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_id', HARMONI_DB_INDEX);
	$configuration->addProperty('harmoni_db_name', 'segue_db');
	Services::startManagerAsService("AgentTokenMappingManager", $context, $configuration);

/*********************************************************
 * Start the AuthenticationManager OSID Impl.
 *********************************************************/
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('token_collectors', $tokenCollectors);
	Services::startManagerAsService("AuthenticationManager", $context, $configuration);