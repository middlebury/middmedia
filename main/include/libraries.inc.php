<?php
/**
 * Include the libraries and define constants for our application
 *
 * @package concerto
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
/*********************************************************
 * Load a config with our library locations
 *********************************************************/
if (file_exists(MYDIR.'/config/libraries.conf.php'))
	require_once (MYDIR.'/config/libraries.conf.php');
else
	require_once (MYDIR.'/config/libraries_default.conf.php');

/******************************************************************************
 * Include Harmoni - required
 ******************************************************************************/
if (!file_exists(HARMONI_DIR)) {
	print "<h2>Harmoni was not found in the specified location, '";
	print HARMONI_DIR;
	print "'. Please install Harmoni there or change the location specifed.</h2>";
	print "<h3>Harmoni is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once (HARMONI_DIR);

/******************************************************************************
 * Include Polyphony
 ******************************************************************************/
if (!file_exists(POLYPHONY_DIR."/polyphony.inc.php")) {
	print "<h2>Polyphony was not found in the specified location, '";
	print POLYPHONY_DIR;
	print "'. Please install Polyphony there or change the location specifed.</h2>";
	print "<h3>Polyphony is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once (POLYPHONY_DIR."/polyphony.inc.php");

/******************************************************************************
 * Include our libraries
 ******************************************************************************/
require_once(MYDIR."/main/library/MenuGenerator.class.php");
