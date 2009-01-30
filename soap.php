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

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($_SERVER['PHP_SELF'])));
define("MYURL", MYPATH."/index.php");

define("WSDL", MYPATH."/middmedia.wsdl");

require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
require_once(dirname(__FILE__)."/main/include/setup.inc.php");

/**
 * Return a list of allowed file type extensions.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @return	array			List of file types.
 * @since	Jan 09
 */
function getTypes($username, $password) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doGetTypes($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of allowed file type extensions.
 *
 * @access	public
 * @param	string	$username	The user already authenticated.
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @return	array			List of file types.
 * @since	Jan 09
 */
function serviceGetTypes($username, $serviceId, $serviceKey) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetTypes($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of allowed file type extensions.
 *
 * @access	public
 * @param	MiddMediaManager	$manager	The manager to use in this request.
 * @return	array				A list of allowed file type extensions.
 * @since	Jan 09
 */
function doGetTypes($manager) {
	return explode(", ", MIDDMEDIA_ALLOWED_FILE_TYPES);
}

/**
 * Return a list of directories the user or group has access to view.
 *
 * @access	public
 * @param 	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @return	array				List of directories.
 * @since	Dec 08
 */
function getDirs($username, $password) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doGetDirs($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of directories the user or group has access to view.
 *
 * @access	public
 * @param 	string	$username	The user already authenticated
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @return	array				List of directories.
 * @since	Dec 08
 */
function serviceGetDirs($username, $serviceId, $serviceKey) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetDirs($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of directories the user has access to through the manger.
 * 
 * @param 	MiddMediaManager	$manager		The manager to use in this request.
 * @return	array					List of directories
 * @access	public
 * @since	Dec 08
 */
function doGetDirs (MiddMediaManager $manager) {
	$directories = array();
	try {
		$dir = $manager->getPersonalDirectory();
		$directory = array();
		$directory['name'] = $dir->getBaseName();
		$directory['bytesused'] = $dir->getBytesUsed();
		$directory['bytesavailable'] = $dir->getBytesAvailable();
	
		$directories[] = $directory;
	} catch(Exception $ex) {
		// user does not have a personal directory
		
		// no need to handle this here, we simply return a blank array
	}
	
	foreach($manager->getSharedDirectories() as $dir) {
		$directory = array();
		$directory['name'] = $dir->getBaseName();
		$directory['bytesused'] = $dir->getBytesUsed();
		$directory['bytesavailable'] = $dir->getBytesAvailable();
	
		$directories[] = $directory;
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
 * @since	Dec 08
 */
function getVideos($username, $password, $directory) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doGetVideos($manager, $directory);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of video information in the user or group directory.
 *
 * @access	public
 * @param 	string	$username	The user already authenticated
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @param	string	$directory	User or Group name.
 * @return	array				List of directories.
 * @since	Dec 08
 */
function serviceGetVideos($username, $serviceId, $serviceKey, $directory) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetVideos($manager, $directory);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage());
	}
}

/**
 * Return a list of video information in the user or group directory.
 *
 * @access	public
 * @param	MiddMediaManager	$manager	The manager to use in this request.
 * @param	string		$directory	User or Group name.
 * @return	array				List of video information.
 * @since	Dec 08
 */
function doGetVideos(MiddMediaManager $manager, $directory) {
	$videos = array();
	
	foreach($manager->getDirectory($directory)->getFiles() as $file) {
		$video = array();
		
		$video["name"] = $file->getBaseName();
		$video["httpurl"] = $file->getHttpUrl();
		$video["rtmpurl"] = $file->getRtmpUrl();
		$video["mimetype"] = $file->getMimeType();
		$video["size"] = $file->getSize();
		try {
			$video["creator"] = $file->getCreatorUsername();
		} catch (OperationFailedException $e) {
			$video["creator"] = null;
		}
		
		try {
			$moddate = $file->getModificationDate();
			$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
		} catch(Exception $ex) {
			return new SoapFault("Server", $ex->getMessage());
		}
		
		try {
			$video["fullframeurl"] = $file->getFullFrameImage()->getUrl();
			$video["thumburl"] = $file->getThumbnailImage()->getUrl();
			$video["splashurl"] = $file->getSplashImage()->getUrl();
		} catch (OperationFailedException $e) {
			$video["fullframeurl"] = null;
			$video["thumburl"] = null;
			$video["splashurl"] = null;
		}
		
		$video["embedcode"] = $file->getEmbedCode();
		
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
 * @since	Dec 08
 */
function getVideo($username, $password, $directory, $file) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doGetVideo($manager, $directory, $file);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Return information about a specific video in the user or group directory.
 *
 * @access	public
 * @param 	string	$username	The user already authenticated
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @param	string	$directory	User or Group name.
 * @param	string	$file		Name of the video file.
 * @return	array			Video information.
 * @since	Dec 08
 */
function serviceGetVideo($username, $serviceId, $serviceKey, $directory, $file) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetVideo($manager, $directory, $file);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Return information about a specific video in the user or group directory.
 *
 * @access	public
 * @param 	MiddMediaManager	$manager	The manager to use in this request.
 * @param	string		$directory	User or Group name.
 * @param	string		$file		Name of the video file.
 * @return	array				Video information.
 * @since	Dec 08
 */
function doGetVideo(MiddMediaManager $manager, $directory, $file) {
	$video = array();
	
	$file = $manager->getDirectory($directory)->getFile($file);
	
	$video["name"] = $file->getBaseName();
	$video["httpurl"] = $file->getHttpUrl();
	$video["rtmpurl"] = $file->getRtmpUrl();
	$video["mimetype"] = $file->getMimeType();
	$video["size"] = $file->getSize();
	try {
		$video["creator"] = $file->getCreatorUsername();
	} catch (OperationFailedException $e) {
		$video["creator"] = null;
	}
	
	try {
		$moddate = $file->getModificationDate();
		$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}
	
	try {
		$video["fullframeurl"] = $file->getFullFrameImage()->getUrl();
		$video["thumburl"] = $file->getThumbnailImage()->getUrl();
		$video["splashurl"] = $file->getSplashImage()->getUrl();
	} catch (OperationFailedException $e) {
		$video["fullframeurl"] = null;
		$video["thumburl"] = null;
		$video["splashurl"] = null;
	}
	
	$video["embedcode"] = $file->getEmbedCode();
	
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
 * @since	Dec 08
 */
function addVideo($username, $password, $directory, $file, $filename, $filetype, $filesize) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doAddVideo($manager, $directory, $file, $filename, $filetype, $filesize);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Add a new video to the user or group directory.
 *
 * @access	public
 * @param 	string	$username	The user already authenticated
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @param	string	$directory	User or Group name.
 * @param	string	$file		base64string of file data.
 * @param	string	$filename	Name of the video.
 * @param	string	$filetype	MIME type of the video.
 * @param	string	$filesize	Byte size of the video.
 * @return	array			Video information.
 * @since	Dec 08
 */
function serviceAddVideo($username, $serviceId, $serviceKey, $directory, $file, $filename, $filetype, $filesize) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doAddVideo($manager, $directory, $file, $filename, $filetype, $filesize);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Add a new video to the user or group directory.
 *
 * @access	public
 * @param 	MiddMediaManager	$manager	The manager to use in this request.
 * @param	string		$directory	User or Group name.
 * @param	string		$file		base64string of file data.
 * @param	string		$filename	Name of the video.
 * @param	string		$filetype	MIME type of the video.
 * @param	string		$filesize	Byte size of the video.
 * @return	array				Video information.
 * @since	Dec 08
 */
function doAddVideo(MiddMediaManager $manager, $directory, $file, $filename, $filetype, $filesize) {
	$video = array();

	$directory = MiddMedia_Directory::getIfExists($manager, $directory);
	$newfile = $directory->createFile($filename);
	$newfile->putContents(base64_decode($file));
	
	$video["name"] = $newfile->getBaseName();
	$video["httpurl"] = $newfile->getHttpUrl();
	$video["rtmpurl"] = $newfile->getRtmpUrl();
	$video["mimetype"] = $newfile->getMimeType();
	$video["size"] = $newfile->getSize();
	$moddate = $newfile->getModificationDate();
	$video["date"] = $moddate->ymdString() . " " . $moddate->hmsString();
	try {
		$video["creator"] = $newfile->getCreatorUsername();
	} catch (OperationFailedException $e) {
		$video["creator"] = null;
	}
	
	try {
		$video["fullframeurl"] = $file->getFullFrameImage()->getUrl();
		$video["thumburl"] = $file->getThumbnailImage()->getUrl();
		$video["splashurl"] = $file->getSplashImage()->getUrl();
	} catch (OperationFailedException $e) {
		$video["fullframeurl"] = null;
		$video["thumburl"] = null;
		$video["splashurl"] = null;
	}
	
	$video["embedcode"] = $file->getEmbedCode();

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
 * @since	Dec 08
 */
function delVideo($username, $password, $directory, $filename) {
	try {
		$manager = MiddMediaManager::forUsernamePassword($username, $password);
		return doDelVideo($manager, $directory, $filename);
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}
}

/**
 * Remove a video from the user or group directory.
 *
 * @access	public
 * @param 	string	$username	The user already authenticated
 * @param	string	$serviceId	The service who is acting as an authentication proxy.
 * @param	string	$serviceKey	The key of the service who is acting as an authentication proxy.
 * @param	string	$directory	User or Group name.
 * @param	string	$filename	Name of the video.
 * @since	Dec 08
 */
function serviceDelVideo($username, $serviceId, $serviceKey, $directory, $filename) {
	try {
		$manager = MiddMediaManager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doDelVideo($manager, $directory, $filename);
	} catch(Exception $ex) {
		return new SoapFault("Server", $ex->getMessage());
	}
}

/**
 * Remove a video from the user or group directory.
 *
 * @access	public
 * @param	string	$username	Username for authentication.
 * @param	string	$password	Password for authentication.
 * @param	string	$directory	User or Group name.
 * @param	string	$filename	Name of the video.
 * @since	Dec 08
 */
function doDelVideo(MiddMediaManager $manager, $directory, $filename) {
	$directory = MiddMedia_Directory::getIfExists($manager, $directory);
	$file = $directory->getFile($filename);
	$file->delete();
} 

/********************************************************
 * SOAP Server Initialization.
 ********************************************************/
$server = new SoapServer(WSDL);

$server->addFunction(
	array(
		"getTypes",
		"getDirs",
		"getVideos",
		"getVideo",
		"addVideo",
		"delVideo",
		"serviceGetTypes",
		"serviceGetDirs",
		"serviceGetVideos",
		"serviceGetVideo",
		"serviceAddVideo",
		"serviceDelVideo"
	)
);

$server->handle();

