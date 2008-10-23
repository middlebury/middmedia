<?php

/**
 * Specify which authentication configurations are enabled.
 *
 * Authentication configuration files must be named in the
 * following pattern:
 *		authentication-XXXXX_default.conf.php
 *		authentication-XXXXX.conf.php
 * where XXXXX is the name of the configuration source.
 * 
 * You may add additional sources (e.g. "ldap2") if you wish to have additional
 * authentication methods configured.
 *
 * USAGE: Copy this file to authentication_sources.conf.php to set custom values.
 *
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

$authenticationSources = array(
	"db",
// 	"ldap",
// 	"visitors"
);