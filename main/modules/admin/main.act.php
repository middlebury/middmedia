<?php 
/**
 * @package concerto.modules.admin
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Block.class.php");


// Our
$yLayout = new YLayout();
$actionRows = new Container($yLayout,OTHER,1);

// Intro
$introHeader = new Heading(_("Administrative Tools"), 1);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);


// setup return points
$harmoni->history->markReturnURL("polyphony/agents/group_membership");
$harmoni->history->markReturnURL("polyphony/authorization/browse_authorizations");
$harmoni->history->markReturnURL("polyphony/authorization/choose_agent");
$harmoni->history->markReturnURL("polyphony/agents/create_agent");

$actionRows->add(new Heading(_("Agents &amp; Groups"), 2));

ob_start();
print "\n<ul>";
print "\n\t<li><a href='".$harmoni->request->quickURL("agents","group_membership")."'>";
print _("Edit Group Membership");
print "</a></li>";
print "\n\t<li><a href='".$harmoni->request->quickURL("agents","create_agent")."'>";
print _("Create User");
print "</a></li>";
print "\n\t<li><a href='".$harmoni->request->quickURL("authorization","choose_agent")."'>";
print _("Edit Agent Authorizations &amp; Details");
print "</a></li>";
print "\n</ul>";

$introText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();

$actionRows->add(new Heading(_("Authorizations") , 2));

ob_start();
print "\n<ul>";
print "\n\t<li><a href='".$harmoni->request->quickURL("authorization","browse_authorizations")."'>";
print _("Browse Authorizations");
print "</a></li>";
print "\n\t<li><a href='".$harmoni->request->quickURL("authorization","choose_agent")."'>";
print _("Edit Agent Authorizations &amp; Details");
print "</a></li>";
// print "\n\t<li><a href='".$harmoni->request->quickURL("agents","create_agent")."'>";
// print _("Create User");
// print "</a></li>";
print "\n</ul>";

$introText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();

// return the main layout.
return $actionRows;