<?php

/**
 * Set up the AuthorizationManager
 *
 * USAGE: Copy this file to authorization.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: Set up the Authorization System ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	$configuration->addProperty('database_name', HARMONI_DB_NAME);
	Services::startManagerAsService("AuthorizationManager", $context, $configuration);
