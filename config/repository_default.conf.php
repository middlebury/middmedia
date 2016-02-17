<?php

/**
 * Set up the RepositoryManager
 *
 * USAGE: Copy this file to repository.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: Set up the RepositoryManager ::
	$repositoryHierarchyId = "edu.middlebury.authorization.hierarchy";
	$defaultParentId = "edu.middlebury.concerto.collections_root";
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	$configuration->addProperty('hierarchy_id', $repositoryHierarchyId);
	$configuration->addProperty('default_parent_id', $defaultParentId);
	$configuration->addProperty('version_control_all', TRUE);
	$configuration->addProperty('use_filesystem_for_files', TRUE);
// 	$configuration->addProperty('file_data_path', MYPATH."/../concerto_data");
	Services::startManagerAsService("RepositoryManager", $context, $configuration);
