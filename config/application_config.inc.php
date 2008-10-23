<?php

/******************************************************************************
 * Set up another database connection for our application tables
 ******************************************************************************/
	$dbHandler = Services::getService("DBHandler");
	$dbIndex = $dbHandler->addDatabase( 
						new MySQLDatabase(				// The database type
								"localhost", 			// The database hostname
								"example_application", 	// The database name
								"test", 				// The database username
								"test") 				// The database password
				);
	$dbHandler->pConnect($dbIndex);
	unset($dbHandler); // done with that for now
	
	// Define
	define("APPLICATION_DB_INDEX", $dbIndex);
