<?php

/**
 * Set up the AgentManager
 *
 * USAGE: Copy this file to agent.conf.php to set custom values.
 *
 * @package segue.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */

// :: Set up the AgentManager ::
	$configuration = new ConfigurationProperties;
	// default agent Flavor is one that can be editted
	$agentFlavor="HarmoniEditableAgent";
	$agentHierarchyId = "edu.middlebury.authorization.hierarchy";
	$configuration->addProperty('hierarchy_id', $agentHierarchyId);
	$configuration->addProperty('defaultAgentFlavor', $agentFlavor);
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	$configuration->addProperty('database_name', HARMONI_DB_NAME);

	// IP ranges can be specified as a dotted quarted with each unit being
	// either an integer (e.g. 128), a wildcard (e.g. *), or an integer range
	// (e.g. 62-134).
	$configuration->addProperty('group_ip_ranges', array(
// 		'edu.middlebury.institute'	=>	'140.233.*.*'

	));

	Services::startManagerAsService("AgentManager", $context, $configuration);

// :: Set up PropertyManager ::
	//the property manager operates in the same context as the AgentManager and is more or less an adjunct to it
	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
	$configuration->addProperty('database_name', HARMONI_DB_NAME);
	Services::startManagerAsService("PropertyManager", $context, $configuration);
