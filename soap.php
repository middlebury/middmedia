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

define("WSDL", MYPATH."/middtube.wsdl");

require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
require_once(dirname(__FILE__)."/main/include/setup.inc.php");


/**
 * Return a list of directories the user or group has access to view.
 *
 * @access	public
 * @param 	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @return	array				List of directories.
 * @since						0.2
 */
function getDirs($username, $password) {
	
	$manager = MiddTubeManager::forUsernamePassword($username, $password);
	$directories = array();
	try {
		$directories[] = $manager->getPersonalDirectory()->getBaseName();
	} catch(Exception $ex) {
		// user does not have a personal directory
		
		// no need to handle this here, we simply return a blank array
	}
	
	foreach($manager->getSharedDirectories() as $directory) {
		$directories[] = $directory->getBaseName();
	}
	
	return $directories;
}

/**
 * Return a list of video information in the user or group directory.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @param	string	$directory	User or Group name.
 * @return	array			List of video information.
 * @since				0.1
 */
function getVideos($username, $password, $directory) {
	
	try {
		$manager = MiddTubeManager::forUsernamePassword($username, $password);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
	
	$videos = array();
	
	foreach($manager->getDirectory($directory)->getFiles() as $file) {
		$video = array();
		
		$video["name"] = $file->getBaseName();
		$video["httpurl"] = $file->getHttpUrl();
		$video["rtmpurl"] = $file->getRtmpUrl();
		$video["mimetype"] = $file->getMimeType();
		$video["size"] = $file->getSize();
		
		try {
			$moddate = $newfile->getModificationDate();
			$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
		} catch(Exception $ex) {
			return new SoapFault("Server", $ex->getMessage());
		}
		
		$videos[] = $video;
	}
	
	return $videos;
}

/**
 * Return information about a specific video in the user or group directory.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @param	string	$directory	User or Group name.
 * @param	string	$file		Name of the video file.
 * @return	array			Video information.
 * @since				0.1
 */
function getVideo($username, $password, $directory, $file) {
	
	try {
		$manager = MiddTubeManager::forUsernamePassword($username, $password);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
	
	$video = array();
	
	$file = $manager->getDirectory($directory)->getFile($file);
	
	$video["name"] = $file->getBaseName();
	$video["httpurl"] = $file->getHttpUrl();
	$video["rtmpurl"] = $file->getRtmpUrl();
	$video["mimetype"] = $file->getMimeType();
	$video["size"] = $file->getSize();
	
	try {
		$moddate = $newfile->getModificationDate();
		$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}
	
	return $video;
}

/**
 * Add a new video to the user or group directory.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @param	string	$directory	User or Group name.
 * @param	string	$file		base64string of file data.
 * @param	string	$filename	Name of the video.
 * @param	string	$filetype	MIME type of the video.
 * @param	string	$filesize	Byte size of the video.
 * @return	array			Video information.
 * @since				0.1
 */
function addVideo($username, $password, $directory, $file, $filename, $filetype, $filesize) {
	
	$video = array();
	
	try {
		$manager = MiddTubeManager::forUsernamePassword($username, $password);
		$directory = MiddTube_Directory::getIfExists($manager, $directory);
		$newfile = $directory->createFile($filename);
		$newfile->putContents(base64_decode($file));
		
		$video["name"] = $newfile->getBaseName();
		$video["httpurl"] = $newfile->getHttpUrl();
		$video["rtmpurl"] = $newfile->getRtmpUrl();
		$video["mimetype"] = $newfile->getMimeType();
		$video["size"] = $newfile->getSize();
		$moddate = $newfile->getModificationDate();
		$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}

	return $video;
}

/**
 * Remove a video from the user or group directory.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @param	string	$directory	User or Group name.
 * @param	string	$filename	Name of the video.
 * @since				0.1
 */
function delVideo($username, $password, $directory, $filename) {
	
	try {
		$manager = MiddTubeManager::forUsernamePassword($username, $password);
		$directory = MiddTube_Directory::getIfExists($manager, $directory);
		$file = $directory->getFile($filename);
		$file->delete();
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}
	
}
 

/********************************************************
 * SOAP Server Initialization.
 ********************************************************/
$server = new SoapServer(WSDL);

$server->addFunction(
	array(
		"getDirs",
		"getVideos",
		"getVideo",
		"addVideo",
		"delVideo"
	)
);

$server->handle();

