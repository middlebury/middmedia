<?php

/**
 * Set up the HierarchyManager
 *
 * USAGE: Copy this file to hierarchy.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: Set up the Hierarchy Manager ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	$configuration->addProperty('database_name', HARMONI_DB_NAME);
	Services::startManagerAsService("HierarchyManager", $context, $configuration);
