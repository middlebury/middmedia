<?php

/**
 * Run some post-configuration setup.
 *
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!isset($_SESSION['post_config_setup_complete'])) {


	// Maybe here you could add some default application data


	$_SESSION['post_config_setup_complete'] = TRUE;
}
