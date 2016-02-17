<?php

/**
 * The main configuration file.
 *
 * USAGE: Copy this file to harmoni.conf.php to set custom values.
 *
 * @package concerto.config
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

// :: set up the $harmoni object ::
	$harmoni->config->set("defaultModule","home");
	$harmoni->config->set("defaultAction","welcome");
	$harmoni->config->set("programTitle","MiddMedia");
	$harmoni->config->set("sessionName","MIDDMEDIA_SESSID");
	$harmoni->config->set("sessionUseCookies",true);
	// In order to prevent User's from including their SESSION IDs in urls that they
	// copy/paste for others, we will force the usage of cookies. This will prevent
	// inadvertant session fixation problems.
	$harmoni->config->set("sessionUseOnlyCookies",true);
	$harmoni->config->set("sessionCookiePath","/");
	$harmoni->config->set("sessionCookieDomain","");

	// An array of actions for which the SESSION ID *can* be passed in the url
	$harmoni->config->set("sessionInUrlActions", array(
		'middmedia.upload'));

	// tell harmoni to post-process all actions with this specified action.
	// the action takes the result from previous actions and builds a display
	// screen from it.

	// until polyphony has been updated to use this functionality, we must ignore
	// it.
	$postProcessIgnoreList = array(
									"language.*",
									"repository.*",
									"help.*",
									"middmedia.upload",

									// and our special no-theme action
									"example.no_theme"
								);

	$harmoni->setPostProcessAction("window.display", $postProcessIgnoreList);

	$context = new OsidContext;
	$context->assignContext('harmoni', $harmoni);

/*********************************************************
 * Set the default timezone if not set.
 *********************************************************/
if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'America/New_York');
}
