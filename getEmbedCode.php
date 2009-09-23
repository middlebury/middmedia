<?php
/**
 * This is a soap endpoint for MiddMedia
 *
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Setup stuff.
 *********************************************************/
// Setup
define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
$protocol = 'https';
else
$protocol = 'http';

define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
                                            "\\", "/", 
                                            dirname($_SERVER['PHP_SELF'])));
define("MYURL", MYPATH."/index.php");

try {
                 
    if (!isset($_GET['directory']) || !$_GET['directory']) {
        require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
        throw new NullArgumentException("The 'directory' parameter must be specified.");
    }  
    if (!isset($_GET['file']) || !$_GET['file']) {
        require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
        throw new NullArgumentException("The 'file' parameter must be specified.");
    }

    // Load from cache if possible
    if (function_exists('apc_fetch')) {
        $embedCode = apc_fetch('embed-'.$_GET['directory'].'/'.$_GET['file']);
        if ($embedCode !== FALSE) {
            print $embedCode;
            exit;
        }
    }
    
        
    require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
    require_once(dirname(__FILE__)."/main/include/setup.inc.php");
	
	
	// Execution
	$manager = UnauthenticatedMiddMediaManager::instance();
	$directory = $manager->getDirectory($_GET['directory']);
	$file = $directory->getFile($_GET['file']);
	$embedCode = $file->getEmbedCode();
	print $embedCode;
	
	
	// Save to cache if possible
	if (function_exists('apc_store')) {
		apc_store('embed-'.$_GET['directory'].'/'.$_GET['file'], $embedCode, 21600);
	}
	
	
// Handle certain types of uncaught exceptions specially. In particular,
// Send back HTTP Headers indicating that an error has ocurred to help prevent
// crawlers from continuing to pound invalid urls.
} catch (UnknownActionException $e) {
	header('HTTP/1.1 404 Not Found');
	print "<h1>404 NOT FOUND</h1>";
	print "<p>".$e->getMessage()."</p>";
} catch (NullArgumentException $e) {
	header('HTTP/1.1 400 Bad Request');
	print "<h1>400 Bad Request</h1>";
	print "<p>".$e->getMessage()."</p>";
} catch (PermissionDeniedException $e) {
	header('HTTP/1.1 403 Forbidden');
	print "<h1>403 Forbidden</h1>";
	print "<p>".$e->getMessage()."</p>";	
} catch (UnknownIdException $e) {
	header('HTTP/1.1 404 Not Found');
	print "<h1>404 NOT FOUND</h1>";
	print "<p>".$e->getMessage()."</p>";
}
// Default 
catch (Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');
	print "<h1>500 Internal Server Error</h1>";
	print "<p>".$e->getMessage()."</p>";
}
