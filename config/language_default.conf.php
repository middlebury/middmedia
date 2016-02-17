<?php

/**
 * Set up the LanguageLocalization system
 *
 * USAGE: Copy this file to language.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: Set up language directories ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('default_language', 'en_US');
	$configuration->addProperty('applications', array (
		'concerto' => MYDIR.'/main/languages',
		'polyphony'=> POLYPHONY.'/main/languages'
	));
	Services::startManagerAsService("LanguageManager", $context, $configuration);
