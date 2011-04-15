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

define("WSDL", MYPATH."/middmedia.wsdl.php");
	
function setup() {
	require_once(dirname(__FILE__)."/main/include/libraries.inc.php");
	require_once(dirname(__FILE__)."/main/include/setup.inc.php");
}

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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
		return doGetTypes($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetTypes($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
	}
}

/**
 * Return a list of allowed file type extensions.
 *
 * @access	public
 * @param	MiddMedia_Manager	$manager	The manager to use in this request.
 * @return	array				A list of allowed file type extensions.
 * @since	Jan 09
 */
function doGetTypes($manager) {
	return MiddMedia_File_Media::getAllowedVideoTypes();
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
		return doGetDirs($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetDirs($manager);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
	}
}

/**
 * Return a list of directories the user has access to through the manger.
 * 
 * @param 	MiddMedia_Manager	$manager		The manager to use in this request.
 * @return	array					List of directories
 * @access	public
 * @since	Dec 08
 */
function doGetDirs (MiddMedia_Manager $manager) {
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
		return doGetVideos($manager, $directory);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetVideos($manager, $directory);
	} catch(Exception $ex) {
		return new SoapFault($ex->getMessage(), $ex->getCode());
	}
}

/**
 * Return a list of video information in the user or group directory.
 *
 * @access	public
 * @param	MiddMedia_Manager	$manager	The manager to use in this request.
 * @param	string		$directory	User or Group name.
 * @return	array				List of video information.
 * @since	Dec 08
 */
function doGetVideos(MiddMedia_Manager $manager, $directory) {
	$videos = array();
	
	foreach($manager->getDirectory($directory)->getFiles() as $file) {
		$video = array();
		
		$primaryFormat = $file->getPrimaryFormat();
		if ($primaryFormat->supportsHttp())
			$httpUrl = $primaryFormat->getHttpUrl();
		else
			$httpUrl = '';
		if ($primaryFormat->supportsRtmp())
			$rtmpUrl = $primaryFormat->getRtmpUrl();
		else
			$rtmpUrl = '';
		
		$video["name"] = $file->getBaseName();
		$video["httpurl"] = $httpUrl;
		$video["rtmpurl"] = $rtmpUrl;
		$video["mimetype"] = $primaryFormat->getMimeType();
		$video["size"] = $primaryFormat->getSize();
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
			$video["fullframeurl"] = $file->getFormat('full_frame')->getHttpUrl();
			$video["thumburl"] = $file->getFormat('thumb')->getHttpUrl();
			$video["splashurl"] = $file->getFormat('splash')->getHttpUrl();
		} catch (Exception $e) {
			$video["fullframeurl"] = null;
			$video["thumburl"] = null;
			$video["splashurl"] = null;
		}
		
		$plugins = MiddMedia_Embed_Plugins::instance();
		foreach ($plugins->getPlugins() as $embed) {
			if ($embed->isSupported($file)) {
				$video["embedcode"] = $embed->getMarkup($file);
				break;
			}
		}
		
		$videos[] = $video;
	}
	
	return $videos;
}

/**
 * Return information about a specific video anonymously
 *
 * @access	public
 * @param	string	$directory	User or Group name.
 * @param	string	$file		Name of the video file.
 * @return	array			Video information.
 * @since	Dec 08
 */
function getVideoAnon($directory, $file) {
	// Load from cache if possible
	if (function_exists('apc_fetch')) {
		$video = apc_fetch('getVideoAnon-'.$directory.'/'.$file);
		if ($video !== FALSE) {
			return $video;
		}
	}
	
	setup();
	try {
		$manager = UnauthenticatedMiddMedia_Manager::instance();
		$video = doGetVideo($manager, $directory, $file);
		
		// Try to save in the cache if possible
		if (function_exists('apc_store')) {
			apc_store('getVideoAnon-'.$directory.'/'.$file, $video, 21600);
		}
		
		return $video;
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doGetVideo($manager, $directory, $file);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Return information about a specific video in the user or group directory.
 *
 * @access	public
 * @param 	MiddMedia_Manager	$manager	The manager to use in this request.
 * @param	string		$directory	User or Group name.
 * @param	string		$file		Name of the video file.
 * @return	array				Video information.
 * @since	Dec 08
 */
function doGetVideo(MiddMedia_Manager $manager, $directory, $file) {
	$video = array();
	
	$file = $manager->getDirectory($directory)->getFile($file);
	
	$video["name"] = $file->getBaseName();
	$video["httpurl"] = $file->getPrimaryFormat()->getHttpUrl();
	if ($file->getPrimaryFormat()->supportsRtmp())
		$video["rtmpurl"] = $file->getPrimaryFormat()->getRtmpUrl();
	else
		$video["rtmpurl"] = null;
	$video["mimetype"] = $file->getPrimaryFormat()->getMimeType();
	$video["size"] = $file->getPrimaryFormat()->getSize();
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
		$video["fullframeurl"] = $file->getFormat('full_frame')->getHttpUrl();
		$video["thumburl"] = $file->getFormat('thumb')->getHttpUrl();
		$video["splashurl"] = $file->getFormat('splash')->getHttpUrl();
	} catch (Exception $e) {
		$video["fullframeurl"] = null;
		$video["thumburl"] = null;
		$video["splashurl"] = null;
	}
	
	$plugins = MiddMedia_Embed_Plugins::instance();
	foreach ($plugins->getPlugins() as $embed) {
		if ($embed->isSupported($file)) {
			$video["embedcode"] = $embed->getMarkup($file);
			break;
		}
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
 * @since	Dec 08
 */
function addVideo($username, $password, $directory, $file, $filename, $filetype, $filesize) {
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
		return doAddVideo($manager, $directory, $file, $filename, $filetype, $filesize);
	} catch(Exception $ex) {
		return new SoapFault("server", $ex->getMessage());
	}
}

/**
 * Add a new video to the user or group directory.
 *
 * @access	public
 * @param 	MiddMedia_Manager	$manager	The manager to use in this request.
 * @param	string		$directoryName	User or Group name.
 * @param	string		$file		base64string of file data.
 * @param	string		$filename	Name of the video.
 * @param	string		$filetype	MIME type of the video.
 * @param	string		$filesize	Byte size of the video.
 * @return	array				Video information.
 * @since	Dec 08
 */
function doAddVideo(MiddMedia_Manager $manager, $directoryName, $file, $filename, $filetype, $filesize) {
	$video = array();

	$directory = MiddMedia_Directory::getIfExists($manager, $directoryName);
	$newfile = $directory->createFileFromData($filename, base64_decode($file));
	
	return doGetVideo($manager, $directoryName, $filename);
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernamePassword($username, $password);
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
	setup();
	try {
		$manager = MiddMedia_Manager::forUsernameServiceKey($username, $serviceId, $serviceKey);
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
function doDelVideo(MiddMedia_Manager $manager, $directory, $filename) {
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
		"getVideoAnon",
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

