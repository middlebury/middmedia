<?php

define('MIDDTUBE_FS_BASE_DIR', '/home/afranco/public_html/middtube_data');
define('MIDDTUBE_HTTP_BASE_URL', 'http://termite.middlebury.edu/~afranco/middtube_data');
define('MIDDTUBE_RTMP_BASE_URL', 'rtmp://termite.middlebury.edu/fms');

define('MIDDTUBE_GROUP_DIRNAME_PROPERTY', 'mail nickname');

MiddTubeManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
MiddTubeManager::addPersonalDirectoryGroup('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');

// MiddTubeManager::addTrustedServiceKey('blogs', '1234567890abcdefghijkl');	// Key for a remote service
// MiddTubeManager::addTrustedServiceKey('wiki', 'abcdefghijklmnop');	// Key for a second remote service