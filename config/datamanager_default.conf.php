<?php

/**
 * Set up the DataManager
 *
 * USAGE: Copy this file to datamanager.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the DataManager ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	Services::startManagerAsService("DataManager", $context, $configuration);
	