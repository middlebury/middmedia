MiddMedia
=========

MiddMedia is a audio/video management system that runs along-side an Adobe Flash 
Media Server (FMS) or another Flash video server. FMS provides the streaming of 
the video data while MiddMedia handles user authentication, file management, and 
the generation of appropriate embed-code.

MiddMedia is not a video browsing and sharing service in the vein of YouTube. To 
share a video with others you must embed it in a website visible to your 
intended audience. This website might be a Segue site, a WordPress blog, a 
MediaWiki wiki, or another site on campus or elsewhere on the internet.


MiddMedia Plugins
-----------------
Plugins are available for CMS applications that allow them to integrate with
MiddMedia. At the simplest, these plugins will convert some shortcode in the CMS
to the proper embed code to stream media off of the MiddMedia host. More 
advanced plugins will provide a browsing interface within the CMS that can allow
users ofthe CMS to browse and upload media to MiddMedia directly from within the
CMS.

* [WordPress Plugin](https://github.com/adamfranco/middmedia-wordpressplugin)
* [MediaWiki Plugin](https://github.com/adamfranco/middmedia-mediawikiplugin)


Documentation
-------------
MiddMedia includes contextual help for users. Additional documentation can be found
online at:

https://mediawiki.middlebury.edu/wiki/LIS/MiddMedia


Getting MiddMedia
-----------------
We are no longer regularly packaging tarballs for download. To get the latest
version of MiddMedia, please clone our Git repository:

    git clone git://github.com/middlebury/middmedia.git
    cd middmedia
    git submodule update --init --recursive


System Requirements
-------------------
* PHP 5.2 or later with the following options:
    --enable-mbstring
    --with-xml
    --enable-soap
    --with-mysql
    --with-curl
    --with-xsl

* MySQL database (version 4 or later)

In addition to the requirements above you will likely want to install Adobe Flash Media Server (FMS) or an alternative server for streaming Flash video.


Installation
------------
1. Download the MiddMedia source via Git.

	git clone git://github.com/middlebury/middmedia.git
	cd middmedia
  	git submodule update --init --recursive

2. Create custom configuration files for those you need to change by renaming 
config/xxxxxx_default.conf.php to config/xxxxxx.conf.php. You will likely need 
to make custom settings in the following configs:

    database.conf.php
    middmedia.conf.php
    authentication_sources.conf.php


3. Create a database for MiddMedia to store user and quota information.

4. Add the database connection parameters to the config/database.conf.php you 
created in Step 2.

5. Point your browser at the directory in which you installed MiddMedia. The 
required tables will be created the first time the application is accessed.

6. Log in with username/password: jadministrator/password


Issue Tracker
---------------------
https://github.com/middlebury/middmedia/issues
