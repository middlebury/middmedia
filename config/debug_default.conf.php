<?php

/**
 * Debugging and testing options.
 *
 * USAGE: Copy this file to debug.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Set to true to enable functionality of resetting the Concerto database to
 * a fresh install. Useful for data-corrupting testing/development.
 * Enabling this will allow all of your data to be deleted with one click.
 *********************************************************/
define ("ENABLE_RESET", false);

/*********************************************************
 * Enable the creation of a set of testing users (dwarves)
 * for the purpose of testing user/group functionality.
 *********************************************************/
define ("ENABLE_DWARVES", false);


/*********************************************************
 * Enable the display of timers and query-counters.
 * (Useful for debugging/testing).
 *********************************************************/
define ("ENABLE_TIMERS", false);
 
 

/*********************************************************
 * Set the HarmoniErrorHandler as the default exception Handler.
 *********************************************************/
set_exception_handler(array('HarmoniErrorHandler', 'handleException'));


/*********************************************************
 * Un-comment the following line to use the Harmoni error
 * handling and logging method. This has the advantage that
 * Logging entries are stored whenever errors are encountered.
 *
 * A disadvantage (if software is not developed in E_STRICT mode)
 * is that every E_STRICT runtime notice will trigger a call
 * to the error handling function even if they are subsequently
 * ignored, resulting in performance degredation.
 *
 * Concerto 2.5.0 has been developed in E_STRICT reporting mode,
 * so no notices or runtime notices should occur during normal
 * operation.
 *********************************************************/
set_error_handler(array('HarmoniErrorHandler', 'handleError'));

/*********************************************************
 * PHP error reporting setting. uncomment to enable override
 * of default environment.
 * 
 * If the HarmoniErrorHandler is used (above), it will respect
 * the error_reporting level and will ignore any errors that
 * are not within the reporting level.
 *********************************************************/
error_reporting(E_ALL | E_STRICT);
// error_reporting(E_ALL);

/*********************************************************
 * If you wish to display errors and uncaught exceptions on
 * the screen, uncomment the following line. This should
 * not be used in production environments, but is VERY useful
 * in development.
 *
 * If display_errors if Off, then any errors matching the current
 * error_reporting level and all uncaught Exceptions will 
 * be logged, but not displayed on the screen.
 *********************************************************/
// ini_set('display_errors', 'On');

/*********************************************************
 * If log_errors is turned on, then reported errors and
 * Exceptions will be logged to the default system log 
 * as well as the Logging OSID (if used).
 *********************************************************/
// ini_set('log_errors', '1');

/*********************************************************
 * Un-comment the following lines to change the level of errors
 * at which execution is halted. By default, the level is 
 * 		E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR
 * meaning that notices and warnings will not halt execution.
 *
 * The syntax for this method is the same as for the 
 * error_reporting() function. 
 *
 * This should not be used in production environments, but
 * may be useful in development.
 *********************************************************/
// $handler = HarmoniErrorHandler::instance();
// $handler->fatalErrors(E_ALL | E_STRICT);
