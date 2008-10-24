<?php
/**
 * This is a soap endpoint for Middtube
 *
 * @package middtube
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Setup stuff.
 *********************************************************/

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF'])));
define("MYURL", MYPATH."/index.php");

require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
require_once(dirname(__FILE__)."/main/include/setup.inc.php");



/*********************************************************
 * Below here is just example stuff. Change to be implementation.
 *********************************************************/
header('Content-Type: text/xml');
print "<response>";

// The following params would set by SOAP request values
$user = 'jadministrator'; // the default user
$pass = 'password';

try {
	// Create a new manager for a username/password combo (username/shared key not yet implemented)
	$manager = MiddTubeManager::forUsernamePassword($user, $pass);
	
	// Get the personal directory
	$dir = $manager->getPersonalDirectory();
	print "\n\t<directory 
				name=\"".$dir->getBaseName()."\"
				http_path=\"".$dir->getHTTPPath()."\"
				rtmp_path=\"".$dir->getRTMPPath()."\"
				type=\"personal\">";
	
	foreach ($dir->getFiles() as $file) {
		 print "\n\t\t<file
					name=\"".$file->getBaseName()."\"
					http_path=\"".$file->getHTTPPath()."\"
					rtmp_path=\"".$file->getRTMPPath()."\"
					mime_type=\"".$file->getMimeType()."\"
					size=\"".$file->getSize()."\"
					modification_date=\"".$file->getModificationDate()->asString()."\"
					/>";
	}
	
	print "\n\t</directory>";
	
	// Get the shared directories
	foreach ($manager->getSharedDirectories() as $dir) {
		print "\n\t<directory 
				name=\"".$dir->getBaseName()."\"
				http_path=\"".$dir->getHTTPPath()."\"
				rtmp_path=\"".$dir->getRTMPPath()."\"
				type=\"shared\">";
		
		foreach ($dir->getFiles() as $file) {
			 print "\n\t\t<file
					name=\"".$file->getBaseName()."\"
					http_path=\"".$file->getHTTPPath()."\"
					rtmp_path=\"".$file->getRTMPPath()."\"
					mime_type=\"".$file->getMimeType()."\"
					size=\"".$file->getSize()."\"
					modification_date=\"".$file->getModificationDate()->asString()."\"
					/>";
		}
		
		print "\n\t</directory>";
	}
} catch (Exception $e) {
	print "\n\t<error type='".get_class($e)."'><![CDATA[".$e->getMessage()."]]></error>";
}

print "\n</response>";