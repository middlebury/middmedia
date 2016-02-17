<?php

/**
 * Argument Validation configuration.
 * - Disable argument validation to speed execution.
 * - Enable it for development and debugging.
 *
 * It may be possible to further speed execution by commenting out the check
 * for "DISABLE_VALIDATION" in
 *		harmoni/core/utilities/ArgumentValidator.class.php
 * so that the validate() method simply returns true. Do this at your own risk.
 *
 * USAGE: Copy this file to validation.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Argument Validation configuration.
 * - Disable argument validation to speed execution.
 * - Enable it for development and debugging.
 *
 * It may be possible to further speed execution by commenting out the check
 * for "DISABLE_VALIDATION" in
 *		harmoni/core/utilities/ArgumentValidator.class.php
 * so that the validate() method simply returns true. Do this at your own risk.
 *********************************************************/
define('DISABLE_VALIDATION', false);
