<?php
/**
 * This is a command-line entry point to concerto that allows execution of actions
 *
 * @package concerto
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Define a Constant reference to this application directory.
 *********************************************************/

if (!defined('MYDIR')) 
	define("MYDIR",realpath(dirname(__FILE__)."/../"));

if (!defined('MYPATH')) 
	define("MYPATH", MYDIR);

if (!defined('MYURL')) 
	define("MYURL", MYPATH."/bin/index_cli.php");

if (!defined('LOAD_GUI')) 
	define("LOAD_GUI", true);

if (!defined('HELP_TEXT')) 
	define("HELP_TEXT", "
This is a command line entry point to Concerto. You must specify a module and
action. Additional parameters can be specified using the following format:
	--<parameter_name>='<parameter_value>'
    
Usage:

	".$_SERVER['argv'][0]." --module=<module_name> --action=<action_name> [parameters]

");

/*********************************************************
 * Include our libraries
 *********************************************************/
require_once(MYDIR."/main/include/libraries.inc.php");

/*********************************************************
 * Include our configuration and setup scripts
 *********************************************************/
require_once(MYDIR."/main/include/setup.inc.php");

/*********************************************************
 * Execute our actions
 *********************************************************/
if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
	require_once(HARMONI."/utilities/Timer.class.php");
	$execTimer = new Timer;
	$execTimer->start();
}

require_once(HARMONI."architecture/output/CommandLineOutputHandler.class.php");
$harmoni->attachOutputHandler(new CommandLineOutputHandler);

require_once(HARMONI."architecture/request/CommandLineRequestHandler.class.php");
$harmoni->request->assignRequestHandler(new CommandLineRequestHandler($_SERVER['argv']));

try {
	$harmoni->execute();
} catch (UnknownActionException $e) {
	print HELP_TEXT;
} catch (HelpRequestedException $e) {
	print $e->getMessage();
}

if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
	$execTimer->end();
	print "\n<table>\n<tr><th align='right'>Execution Time:</th>\n<td align='right'><pre>";
	printf("%1.6f", $execTimer->printTime());
	print "</pre></td></tr>\n</table>";
	
	$dbhandler = Services::getService("DBHandler");
	printpre("NumQueries: ".$dbhandler->getTotalNumberOfQueries());
	
// 	printpreArrayExcept($_SESSION, array('__temporarySets'));
	// debug::output(session_id());
	// Debug::printAll();
}
?>