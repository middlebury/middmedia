<?php

define('FFMPEG_PATH', '/usr/local/bin/ffmpeg');
define('IMAGE_MAGICK_COMPOSITE_PATH', '/usr/local/bin/composite');
define('IMAGE_MAGICK_CONVERT_PATH', '/usr/local/bin/convert');
define('MIDDMEDIA_DEFAULT_FRAME_PATH', MYDIR.'/images/Black.jpg');
define('MIDDMEDIA_SPLASH_OVERLAY', MYDIR.'/images/splash-overlay.png');

define('MIDDMEDIA_FS_BASE_DIR', '/home/afranco/public_html/middmedia_data');
define('MIDDMEDIA_HTTP_BASE_URL', 'http://chisel.middlebury.edu/~afranco/middmedia_data');
#define('MIDDMEDIA_RTMP_BASE_URL', 'rtmp://chisel.middlebury.edu/fms');
define('MIDDTUBE_URL', 'http://blogs.middlebury.edu/middtube/');
define('WP_USER', 'enter_wordpress_username_here');
define('WP_PASS', 'enter_wordpress_password_here');


define('UPLOAD_DISABLED_MESSAGE', "Uploads to MiddMedia have been disabled. Please migrate your videos to other services such as <a href='http://go.middlebury.edu/panoptohelp'>Panopto</a>.");

/*********************************************************
 * If a single valued property can be used for the directory name, specify it here.
 *********************************************************/
// define('MIDDMEDIA_GROUP_DIRNAME_PROPERTY', 'mail nickname');

/*********************************************************
 * Alternatively, specify a callback function which takes a group.
 *********************************************************/
define('MIDDMEDIA_GROUP_DIRNAME_CALLBACK', 'getGroupDirnames');
function getGroupDirnames(Group $group) {
	$names = array();
	$names[] = getGroupIdAsDirname($group->getId());
	$propertiesIterator = $group->getProperties();
	while ($propertiesIterator->hasNext()) {
		$properties = $propertiesIterator->next();
		if ($properties->getProperty('EMail')) {
			$names[] = substr($properties->getProperty('EMail'), 0, strpos($properties->getProperty('EMail'), '@'));
		}
	}
	return array_reverse($names);
}
function getGroupIdAsDirname (Id $id) {
	$name = preg_replace('/(CN|OU|DC)=/i', '', $id->getIdString());
	$name = preg_replace('/,/i', '-', $name);
	$name = preg_replace('/[^a-z0-9-]+/i', '_', $name);
	$name = trim($name, '_-');
	return $name;
}


define('MIDDMEDIA_ALLOWED_FILE_TYPES', 'mp3, mp4, flv, avi, asf, dv, m4v, mj2, mjp, mjpg, mkv, mov, mpeg, mpg, ogv, qt, rv, swf, wm, wmv, webm, m4a');
define('MIDDMEDIA_CONVERT_MAX_HEIGHT', 1080);
define('MIDDMEDIA_CONVERT_MAX_WIDTH', 1920);
define('MIDDMEDIA_TMP_DIR', '/tmp');
define('MIDDMEDIA_ENABLE_WEBM', FALSE);

/**********************************************************************************
 * Add or remove embed code plugins that will populate the embed code listing
 * available for each video or audio file.
 *
 * The first plugin listed that supports a media file will be used to preview it.
 **********************************************************************************/
$plugins = MiddMedia_Embed_Plugins::instance();                                                                                                                               $plugins->addPlugin(new MiddMedia_Embed_Plugin_StrobePlayer('http://middmedia.middlebury.edu/strobe_mp'));
$plugins->addPlugin(new MiddMedia_Embed_Plugin_AudioPlayer('http://middmedia.middlebury.edu/AudioPlayer'));
$plugins->addPlugin(new MiddMedia_Embed_Plugin_AudioPlayerM4a('http://middmedia.middlebury.edu/AudioPlayer'));
$plugins->addPlugin(new MiddMedia_Embed_Plugin_Rtmp());
$plugins->addPlugin(new MiddMedia_Embed_Plugin_Http());

MiddMedia_Manager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
MiddMedia_Manager::addPersonalDirectoryGroup('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');

// MiddMedia_Manager::addTrustedServiceKey('blogs', '1234567890abcdefghijkl');	// Key for a remote service
// MiddMedia_Manager::addTrustedServiceKey('wiki', 'abcdefghijklmnop');	// Key for a second remote service
