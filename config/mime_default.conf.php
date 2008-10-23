<?php

/**
 * Set up the MIME service for sniffing mime types
 *
 * USAGE: Copy this file to mime.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
// :: Set up the MIME service for sniffing mime types ::
	$configuration = new ConfigurationProperties;
	Services::startManagerAsService("MIMEManager", $context, $configuration);