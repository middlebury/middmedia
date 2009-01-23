<?php

/**
 * URL configuration file.
 *
 * USAGE: Copy this file to path.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: url_default.conf.php,v 1.1 2008/02/19 16:57:34 adamfranco Exp $
 */

/*********************************************************
 * The MYURL value is the url of the index.php script.
 * If the url is to be re-written with mod_rewrite or other
 * techniques, change the value of MYURL to match.
 *********************************************************/
define("MYURL", trim(MYPATH, '/')."/index.php");
