<?php
/**
 * This script is a command-line entry point for the concerto OAI-updater, 
 * allowing updates to be run nightly via cron (for instance).
 *
 * 
 * 
 * @since 10/30/07
 * @package concerto.oai
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!defined('HELP_TEXT')) 
	define("HELP_TEXT", 
"This is a command line script that will update the library structure to the new format.
It takes no arguments or parameters.
");

$_SERVER['argv'][] = '--module=middmedia';
$_SERVER['argv'][] = '--action=update_library_structure';

error_reporting(E_ERROR);

require(dirname(__FILE__)."/index_cli.php");
?>