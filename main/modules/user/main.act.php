<?php
/**
 * @package concerto.modules.user
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
$introHeader = new Heading(_("User Tools"), 1);
$actionRows->add($introHeader, "100%" ,null, LEFT, CENTER);
$harmoni = Harmoni::instance();

$actionRows->add(new Heading(_("Authentication"), 2));

// Current AuthN Table
ob_start();
$authNManager = Services::getService("AuthN");
$agentManager = Services::getService("Agent");
$authTypes =$authNManager->getAuthenticationTypes();
print "\n<table border='2' align='left' class='login'>";
print "\n\t<tr><th colspan='3'><center>";
print _("Current Authentications: ");
print "</center>\n\t</th></tr>";

while($authTypes->hasNext()) {
	$authType =$authTypes->next();
	$typeString = HarmoniType::typeToString($authType);
	print "\n\t<tr>";
	print "\n\t\t<td>";
	print "<a href='#' title='$typeString' onclick='alert(\"$typeString\")'>";
	print $authType->getKeyword();
	print "</a>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	$userId =$authNManager->getUserId($authType);
	$userAgent =$agentManager->getAgent($userId);
	print '<a title=\''._("Agent Id").': '.$userId->getIdString().'\' onclick=\'Javascript:alert("'._("Agent Id").':\n\t'.$userId->getIdString().'");\'>';
	print $userAgent->getDisplayName();
	print "</a>";
	print "\n\t\t</td>";
	print "\n\t\t<td>";
	
	$harmoni->request->startNamespace("polyphony");
	// set where we are before login 
	$harmoni->history->markReturnURL("polyphony/login");
		
	if ($authNManager->isUserAuthenticated($authType)) {
		$url = $harmoni->request->quickURL(
			"auth", "logout_type",
			array("type"=>urlencode($typeString))
		);
		print "<a href='".$url."'>Log Out</a>";
	} else {
		$url = $harmoni->request->quickURL(
			"auth", "login_type",
			array("type"=>urlencode($typeString))
		);
		print "<a href='".$url."'>Log In</a>";
	}
	$harmoni->request->endNamespace();
	
	print "\n\t\t</td>";
	print "\n\t</tr>";
}
print "\n</table>";

$statusBar = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($statusBar,null,null,RIGHT,TOP);
ob_end_clean();



ob_start();
print "\n<ul>".
	"\n\t<li><a href='".
	$harmoni->request->quickURL("user", "change_password")."'>".
	_("Change 'Harmoni DB' Password").
	"</li>";
	
$introText = new Block(ob_get_contents(), STANDARD_BLOCK);
$actionRows->add($introText, "100%", null, CENTER, CENTER);
ob_end_clean();
// end of authN links

return $actionRows;
?>
