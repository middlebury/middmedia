
MiddMedia v. 0.6.0 (2009-11-18)
=================================

What is MiddMedia?
------------------
MiddMedia is a audio/video upload and management tool that works in parallel with a
Flash Media Server (FMS) to deliver user-created videos.


Current Version Notes
---------------------
This release changes support for group-directory naming to allow multiple directory
definitions per group. This change will allow directories to be created based on
multiple criteria, not just a single property.


Downloads
---------------------
For the latest and archived versions, please download from ______.

http://www.assembla.com/wiki/show/MiddMedia/


Documentation
---------------------
MiddMedia includes contextual help for users. Additional documentation can be found
online at:

https://mediawiki.middlebury.edu/wiki/LIS/MiddMedia


Installation
---------------------
See the INSTALL.txt file in the root directory for installation instructions or read
on the web at:

http://www.assembla.com/wiki/show/MiddMedia/


Bug Tracker
---------------------
http://www.assembla.com/spaces/MiddMedia/tickets/







===================================================================
| Prior MiddMedia Release Notes
| (See the MiddMedia change log for more details)
===================================================================


v. 0.6.0 (2009-11-18)
----------------------------------------------------
This release changes support for group-directory naming to allow multiple directory
definitions per group. This change will allow directories to be created based on
multiple criteria, not just a single property.



v. 0.5.0 (2009-10-09)
----------------------------------------------------
This release adds support for CAS authentication.

If going to CAS authentication from LDAP, enable both authentication methods, then
run the updater under Admin Tools --> MiddMedia Updates to map LDAP ids to CAS ids.

From there both authentication methods can be left enabled or the LDAP method can
be disabled.

Authentication configuration has changed to support the reworked admin-act-as-user
authentication method. 

----

 This release of MiddMedia uses Harmoni 1.10.0 and Polyphony 1.5.3.



v. 0.4.1 (2009-09-28)
----------------------------------------------------
This update adds support for server-side transcoding from uploads made via the SOAP
API. 



v. 0.4.0 (2009-09-25)
----------------------------------------------------
This release adds support for server-side transcoding.

A new requirement is that ffmpeg be installed with support for the libx264 codec.
As well, a cron job to run middmedia/cli/checkQueue.php is needed to initiate
conversion. 



v. 0.3.0 (2009-07-24)
----------------------------------------------------
This release adds two new web-service methods that allow for accessing embed code
for files anonymously. These methods support APC caching to provide very
high-performance for this light-weight read-only usage, allowing client services to
rely on these methods for render-time access to embed-code.



v. 0.2.5 (2009-05-05)
----------------------------------------------------
This release fixes a minor bug in group naming restrictions.



v. 0.2.4 (2009-03-30)
----------------------------------------------------
This release fixes a few bugs that were affecting Internet Explorer. 

----

 This release of MiddMedia uses Harmoni 1.9.3 and Polyphony 1.4.11.



v. 0.2.3 (2009-02-02)
----------------------------------------------------
This release fixes a bug in the WSDL path. 

----

 This release of MiddMedia uses Harmoni 1.9.3 and Polyphony 1.4.11.



v. 0.2.2 (2009-02-02)
----------------------------------------------------
This release fixes a few bugs and adds links to help documentation. 

----

 This release of MiddMedia uses Harmoni 1.9.3 and Polyphony 1.4.10.



v. 0.2.1 (2009-01-30)
----------------------------------------------------




v. 0.2.0 (2009-01-30)
----------------------------------------------------
This release adds support for the extraction of frames from video and the generation
of thumbnail and splash images from those frames.

Every video file will have a matching image with the '.jpg' extension in each of
the three subdirectories below that in which the video is located: full_frame/,
thumb/, splash/. The full-frame is the same dimensions as the video file. The
splash-image is the full-frame image with a 'play' icon overlayed. The thumbnail is
a maximum of 200x200 pixels.

This release also fixes a few other bugs. See the change-log for details. 

----

 This release of MiddMedia uses Harmoni 1.9.3 and Polyphony 1.4.10.



v. 0.1.3 (2009-01-26)
----------------------------------------------------
This release now supports audio embed code. 

----

 This release of MiddMedia uses Harmoni 1.9.1 and Polyphony 1.4.9.



v. 0.1.2 (2009-01-26)
----------------------------------------------------
This release updates to the quota support and adds the display of embed code and
URLs to media 

----

 This release of MiddMedia uses Harmoni 1.9.1 and Polyphony 1.4.9.



v. 0.1.1 (2009-01-23)
----------------------------------------------------
This release includes updates to Harmoni and the MiddMedia code-bases needed to get
this system running under PHP 5.1 

----

 This release of MiddMedia uses Harmoni 1.9.0 and Polyphony 1.4.9.



v. 0.1.0 (2009-01-21)
----------------------------------------------------
This is the first release of the MiddMedia system to be put into testing.



