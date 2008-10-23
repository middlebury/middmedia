<?php

/**
 * Set up the GUIManager
 *
 * USAGE: Copy this file to gui.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

 require_once(HARMONI.'Gui2/GuiManager.class.php');
 
 
 // :: GUIManager setup ::
	define("LOGO_URL", MYPATH."/images/logo.gif");
 	
 	$configuration = new ConfigurationProperties;
 	$configuration->addProperty('database_index', HARMONI_DB_INDEX);
 	$configuration->addProperty('database_name', HARMONI_DB_NAME);
 	$configuration->addProperty('default_theme', 'RoundedCorners');
 	$configuration->addProperty('character_set', 'utf-8');
 	$configuration->addProperty('document_type', 'text/html');
 	$configuration->addProperty('document_type_definition', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	$configuration->addProperty('xmlns', 'http://www.w3.org/1999/xhtml');
	
	// Enable linked theme CSS
	$configuration->addProperty('css_url', $harmoni->request->mkURL('gui2', 'theme_css'));
	$configuration->addProperty('css_url_theme_property', 'theme');
	
	// Theme sources
	$sources = array();
	
	// Read-only themes
	$sources[] = array(	'type' => 'directory',
						'path' => MYDIR.'/themes-dist');
	$sources[] = array(	'type' => 'directory',
						'path' => MYDIR.'/themes-local');
	
	$configuration->addProperty('sources', $sources);
	
	$guiMgr = new Harmoni_Gui2_GuiManager;
	$guiMgr->assignConfiguration($configuration);
	$guiMgr->assignOsidContext($context);
	Services::registerObjectAsService("GUIManager", $guiMgr);
	
	$guiMgr->setHead($guiMgr->getHead()."
		
		<link rel='stylesheet' type='text/css' href='".MYPATH."/images/Common.css' id='SegueCommon'/>
");