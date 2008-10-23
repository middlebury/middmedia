<?php

/**
 * Set up the ActionHandler. The ActionHandler provides a logical navigation-control system.
 *
 * USAGE: Copy this file to action.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
 	// First we need to assign a RequestHandler to Harmoni for generating URLs, 
 	// getting request data (ie, $_REQUEST variables) from the browser, and getting
 	// the requested module/action pair.
 
 	// the GETMethodRequestHandler uses standard GET query strings for information
 	// transfer.
 	require_once(HARMONI."/architecture/request/GETMethodRequestHandler.class.php");
	$harmoni->request->assignRequestHandler( new GETMethodRequestHandler() );
	
	// Next we need to tell the ActionHandler where to find our modules/actions
	// and what type those are (FlatFile-Actions, ClassMethod-Actions, or
	// Class-Actions.
	$harmoni->ActionHandler->addActionSource(	
				new FlatFileActionSource(realpath(MYDIR."/main/modules"), 
										 ".act.php", "Action"));
	// Our Authentication and Language-switching actions are already written
	// and reside in the Polyphony package:
	$harmoni->ActionHandler->addActionSource(	
				new ClassesActionSource(realpath(POLYPHONY."/main/modules"), 
										 ".act.php", "Action"));