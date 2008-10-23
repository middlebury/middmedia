<?php

require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");

// Our Rows for placing data
$actionRows = new Container(new YLayout(),OTHER,1);

// Intro
$introHeader = new Heading(_("Welcome to Example Application"), 1);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);

ob_start();

print "<p>";
print _("This <strong>Example Application</strong> is a template application that is designed to serve as a starting point for Harmoni-based applications.");
print "</p>\n<p>";
print _("You can either get this example skeleton as a package or from CVS. Packaged releases will include versions of Harmoni, Polyphony, and the Harmoni Manual/PHPDoc that are current as of the package release. If you check out this application from CVS you will also need to check out the Harmoni and Polyphony packages as well and set your config to point to their locations.");
print "</p>";

$introText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();

// return the main layout.
return $actionRows;