<?php

/**
 * Set up the DatabaseHandler
 *
 * USAGE: Copy this file to database.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/******************************************************************************
 * Set up the database connection
 *
 * This example application uses a database for Authentication, so we need
 * to set up this connection. If you don't wish to use Authentication or other
 * database services, you can remove this and all later sections.
 ******************************************************************************/
 	$configuration = new ConfigurationProperties;
	$connectionInfoString = _("Please create a database for the MiddMedia application to use. If you need to change configuration properties, please copy <br/>&nbsp;&nbsp;&nbsp;config/database_default.conf.php <br/>to<br/>&nbsp;&nbsp;&nbsp;config/database.conf.php<br/>and edit the new file's values.");
	$configuration->addProperty('connectionInfo', $connectionInfoString);
	Services::startManagerAsService("DatabaseManager", $context, $configuration);

	// Get the database service
	$databaseManager = Services::getService("DatabaseManager");

	// Add our database
	$harmoni_db_name = "middmedia";
	$harmoni_db_index = $databaseManager->addDatabase(
		new MySQLDatabase(			// The database type MySQLDatabase or PostgreSQLDatabase
				"localhost", 		// The database hostname
				$harmoni_db_name, 	// The database name
				"test_user", 			// The database username
				"test_password") 			// The database password
		);

	// connect to our database
	$databaseManager->pConnect($harmoni_db_index);

	// Define some constants
	define("HARMONI_DB_INDEX", $harmoni_db_index);
	define("HARMONI_DB_NAME", $harmoni_db_name);


	// The type will be used to choose which table creation script to run.
	// Valid types: "MySQL", "Postgre", "Oracle"
	define("HARMONI_DB_TYPE", $databaseManager->getStringName($harmoni_db_index));
