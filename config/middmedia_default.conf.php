<?php

define('FFMPEG_PATH', '/usr/local/bin/ffmpeg');
define('IMAGE_MAGICK_COMPOSITE_PATH', '/usr/local/bin/composite');
define('MIDDMEDIA_SPLASH_OVERLAY', MYDIR.'/images/splash-overlay.png');

define('MIDDMEDIA_VIDEO_EMBED_CODE', '<embed src="http://middmedia.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddmedia.middlebury.edu%2Fvod%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27###ID###%27%7D" width="400" height="200" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>');

define('MIDDMEDIA_AUDIO_EMBED_CODE', '<script type="text/javascript" src="http://middmedia.middlebury.edu/AudioPlayer/audio-player.js"></\' + \'script><object width="290" height="24" id="###HTML_ID###" data="http://middmedia.middlebury.edu/AudioPlayer/player.swf" type="application/x-shockwave-flash"><param value="http://middmedia.middlebury.edu/AudioPlayer/player.swf" name="movie" /><param value="high" name="quality" /><param value="false" name="menu" /><param value="transparent" name="wmode" /><param value="soundFile=###HTTP_URL###" name="FlashVars" /></object>');

define('MIDDMEDIA_FS_BASE_DIR', '/home/afranco/public_html/middmedia_data');
define('MIDDMEDIA_HTTP_BASE_URL', 'http://termite.middlebury.edu/~afranco/middmedia_data');
define('MIDDMEDIA_RTMP_BASE_URL', 'rtmp://termite.middlebury.edu/fms');

define('MIDDMEDIA_GROUP_DIRNAME_PROPERTY', 'mail nickname');

define('MIDDMEDIA_ALLOWED_FILE_TYPES', 'mp4, flv, mp3');

MiddMediaManager::addPersonalDirectoryGroup('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
MiddMediaManager::addPersonalDirectoryGroup('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');

// MiddMediaManager::addTrustedServiceKey('blogs', '1234567890abcdefghijkl');	// Key for a remote service
// MiddMediaManager::addTrustedServiceKey('wiki', 'abcdefghijklmnop');	// Key for a second remote service