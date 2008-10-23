<?php

require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");

// Our Rows for placing data
$actionRows = new Container(new YLayout(),OTHER,1);

// Intro
$introHeader = new Heading(_("Documentation"), 1);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

ob_start();
print "<p>";
print _("The links to the left take you to the Harmoni Manual and the Harmoni PHPDoc,
The main sources of information about how to use Harmoni. The manual, in particular the 
'Getting Started' chapter, provides a reference for using Harmoni. Some of the ways that
Harmoni can speed your developement are listed below:");
print "<ul>";
	print "<li>";
	print _("Authentication and Authorization systems are already written and in place, just configure for your server.");
	print "</li>";
	print "<li>";
	print _("A module/action control architecture -- tied in to Authentication -- is available for rapid interface development.");
	print "</li>";
	print "<li>";
	print _("Advanced database wrapper abstracts queries to run on multiple database platforms.");
	print "</li>";
	print "<li>";
	print _("Theme/Layout GUI engine is provided.");
	print "</li>";
	print "<li>";
	print _("Implementations of OKI high-level services for managing Hierarchies, Digital Repositories, Users, and Classes.");
	print "</li>";
	print "<li>";
	print _("Much more, see the manual.");
	print "</li>";
print "</ul>";
print "</p>";
$introText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();

// Using
$introHeader = new Heading(_("Using this Skeleton"), 2);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

ob_start();
print "<p>";
print _("The easiest way to get started is to:");
print "<ol>";
	print "<li>";
	print _("Edit the <em>config/database.conf.php</em> file to fit your database (Though you have probably already done this).");
	print "</li>";
	print "<li>";
	print _("Create modules/actions for your application in the <em>main/modules/</em> directory.");
	print "</li>";
	print "<li>";
	print _("Edit the <em>config/action_default.conf.php</em> file to point to your new default action.");
	print "</li>";
	print "<li>";
	print _("Modify/re-create/get-rid-of the MenuGenerator class in <em>main/library/</em> so as to fit your application.");
	print "</li>";
print "</ol>";
print _("Other considerations:");
print "<ul>";
	print "<li>";
	print _("Start and use high-level services such as Hierarchy or DigitalRepository.");
	print "</li>";
	print "<li>";
	print _("Changing the settings of the Theme/creating new themes.");
	print "</li>";
	print "<li>";
	print _("Internationalization.");
	print "</li>";
	print "<li>";
	print _("Configuration of external Authentication systems such as LDAP.");
	print "</li>";
	print "<li>";
	print _("See the \"Help\" link at the bottom left for information on integrating contextual help into your application.");
	print "</li>";
print "</ul>";
print "</p>";
$exampleText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($exampleText, "100%", null, CENTER, CENTER);
ob_end_clean();

// return the main layout.
return $actionRows;