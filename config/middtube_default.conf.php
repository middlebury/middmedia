<?php

define('MIDDTUBE_EMBED_CODE', '<embed src="http://middtube.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddtube%2Emiddlebury%2Eedu%3A1935%2F###DIR###%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27###ID###%27###SPLASH_IMAGE_URL###%7D" width="400" height="200" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>');

define('MIDDTUBE_FS_BASE_DIR', '/home/afranco/public_html/middtube_data');
define('MIDDTUBE_HTTP_BASE_URL', 'http://termite.middlebury.edu/~afranco/middtube_data');
define('MIDDTUBE_RTMP_BASE_URL', 'rtmp://termite.middlebury.edu/fms');

define('MIDDTUBE_GROUP_DIRNAME_PROPERTY', 'mail nickname');

define('MIDDTUBE_ALLOWED_FILE_TYPES', 'mp4, flv, mp3');

MiddTubeManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
MiddTubeManager::addPersonalDirectoryGroup('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');

// MiddTubeManager::addTrustedServiceKey('blogs', '1234567890abcdefghijkl');	// Key for a remote service
// MiddTubeManager::addTrustedServiceKey('wiki', 'abcdefghijklmnop');	// Key for a second remote service