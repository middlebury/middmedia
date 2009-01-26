<?php

define('MIDDMEDIA_FS_BASE_DIR', '/home/afranco/public_html/middmedia_data');
define('MIDDMEDIA_HTTP_BASE_URL', 'http://termite.middlebury.edu/~afranco/middmedia_data');
define('MIDDMEDIA_RTMP_BASE_URL', 'rtmp://termite.middlebury.edu/fms');

define('MIDDMEDIA_GROUP_DIRNAME_PROPERTY', 'mail nickname');

define('MIDDMEDIA_ALLOWED_FILE_TYPES', 'mp4, flv, mp3');

MiddMediaManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
MiddMediaManager::addPersonalDirectoryGroup('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');

// MiddMediaManager::addTrustedServiceKey('blogs', '1234567890abcdefghijkl');	// Key for a remote service
// MiddMediaManager::addTrustedServiceKey('wiki', 'abcdefghijklmnop');	// Key for a second remote service