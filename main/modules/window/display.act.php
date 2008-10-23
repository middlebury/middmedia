<?php
/**
 * @package example.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
require_once(MYDIR."/main/library/MenuGenerator.class.php");
 
require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(HARMONI."GUIManager/Components/Header.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");
require_once(HARMONI."GUIManager/Container.class.php");

require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");

require_once(HARMONI."GUIManager/StyleProperties/FloatSP.class.php");

// Set a default title
$harmoni = Harmoni::instance();
$outputHandler =$harmoni->getOutputHandler();
$outputHandler->setHead($outputHandler->getHead()."\n\t\t<title>"._("Example Skeleton")."</title>");

$xLayout = new XLayout();
$yLayout = new YLayout();


$mainScreen = new Container($yLayout, BLOCK, 1);

// :: Top Row ::
	// The top row for the logo and status bar.
		$headRow = new Container($xLayout, HEADER, 1);

	// The logo
	$logo = new UnstyledBlock("\n<a href='".MYPATH."/'> <img src='".MYPATH."/main/modules/window/logo.gif' 
						style='border: 0px;' alt='"._("Example Logo'"). "/> </a>",1);
	$headRow->add($logo, null, null, LEFT, TOP);

	// Language Bar
	$harmoni->history->markReturnURL("polyphony/language/change");
	$languageText = "\n<form action='".$harmoni->request->quickURL("language", "change")."' method='post'>";
	$harmoni->request->startNamespace("polyphony");
	$languageText .= "\n\t<div style='text-align: center'>\n\t<select name='".$harmoni->request->getName("language")."'>";
	$harmoni->request->endNamespace();
	$langLoc = Services::getService('Lang');
	$currentCode = $langLoc->getLanguage();
	$languages = $langLoc->getLanguages();
	ksort($languages);
	foreach($languages as $code => $language) {
		$languageText .= "\n\t\t<option value='".$code."'".
						(($code == $currentCode)?" selected='selected'":"").">";
		$languageText .= $language."</option>";
	}
	$languageText .= "\n\t</select>";
	$languageText .= "\n\t<input type='submit' />";
	$languageText .= "\n\t</div>\n</form>";
	
	$languageBar = new Component($languageText, BLANK, 1);
	$headRow->add($languageBar, null, null, LEFT,TOP);
	
		// Pretty Login Box
		$loginRow = new Container($yLayout, OTHER, 1);
		$headRow->add($loginRow, null, null, RIGHT, TOP);
		
		ob_start();
		$authN = Services::getService("AuthN");
		$agentM = Services::getService("Agent");
		$idM = Services::getService("Id");
		$authTypes =$authN->getAuthenticationTypes();
		$users = '';
		while ($authTypes->hasNext()) {
			$authType =$authTypes->next();
			$id =$authN->getUserId($authType);
			if (!$id->isEqual($idM->getId('edu.middlebury.agents.anonymous'))) {
				$agent =$agentM->getAgent($id);
				$exists = false;
				foreach (explode("+", $users) as $user) {
					if ($agent->getDisplayName() == $user)
						$exists = true;
				}
				if (!$exists) {
					if ($users == '')
						$users .= $agent->getDisplayName();
					else
						$users .= " + ".$agent->getDisplayName();
				}
			}
		}
		if ($users != '') {
			print "\n<div class='login'>";
			if (count(explode("+", $users)) == 1)
				print _("User: ").$users."\t";
			else 
				print _("Users: ").$users."\t";
			
			print "<a href='".$harmoni->request->quickURL("auth",
				"logout")."'>"._("Log Out")."</a></div>";
		} else {
			// set bookmarks for success and failure
			$harmoni->history->markReturnURL("polyphony/display_login");
			$harmoni->history->markReturnURL("polyphony/login_fail",
				$harmoni->request->quickURL("user", "main"));

			$harmoni->request->startNamespace("harmoni-authentication");
			$usernameField = $harmoni->request->getName("username");
			$passwordField = $harmoni->request->getName("password");
			$harmoni->request->endNamespace();
			$harmoni->request->startNamespace("polyphony");
			print  "\n<div class='login'>".
				"\n<form action='".
				$harmoni->request->quickURL("auth", "login").
				"' method='post'>".
				"\n\t"._("Username:")." <input type='text' size='8' 
					name='$usernameField'/>".
				"\n\t"._("Password:")." <input type='password' size ='8' 
					name='$passwordField'/>".
				"\n\t <input type='submit' value='Log In' />".
				"\n</form></div>\n";
			$harmoni->request->endNamespace();
		}	
		$loginRow->add(new Component(ob_get_clean(), BLANK, 2), null, null, RIGHT, TOP);
		
		
		// User tools
		ob_start();
		print "<div style='font-size: small; margin-top: 8px;'>";
		print "<a href='".$harmoni->request->quickURL("user", "main")."'>";
		print _("User Tools");
		print "</a>";
		print " | ";
		print "<a href='".$harmoni->request->quickURL("admin", "main")."'>";
		print _("Admin Tools");
		print "</a>";
		print "</div>";

		$loginRow->add(new Component(ob_get_clean(), BLANK, 2), null, null, RIGHT, BOTTOM);
		
		
	//Add the headerRow to the mainScreen
	$mainScreen->add($headRow, "100%", null, LEFT, TOP);

// :: Center Pane ::
		$centerPane =$mainScreen->add(new Container($xLayout, OTHER, 1), "100%", null, LEFT, TOP);		
	// Main menu
	$mainMenu = MenuGenerator::generateMainMenu($harmoni);
	$centerPane->add($mainMenu,"300px",null, LEFT, TOP);

	// use the result from previous actions
		if ($harmoni->printedResult) {
			$contentDestination = new Container($yLayout, OTHER, 1);
			$centerPane->add($contentDestination, null, null, LEFT, TOP);
			$contentDestination->add(new Block($harmoni->printedResult, 1), null, null, TOP, CENTER);
			$harmoni->printedResult = '';
		} else {
			$contentDestination =$centerPane;
		}
		
		// use the result from previous actions
		if (is_object($harmoni->result))
			$contentDestination->add($harmoni->result, null, null, CENTER, TOP);
		else if (is_string($harmoni->result))
			$contentDestination->add(new Block($harmoni->result, STANDARD_BLOCK), null, null, CENTER, TOP);
	
	

// :: Footer ::
	$footer = new Container (new XLayout, FOOTER, 1);
	
	$helpText = "<a target='_blank' href='";
	$helpText .= $harmoni->request->quickURL("help", "browse_help");
	$helpText .= "'>"._("Help")."</a>";
	$footer->add(new UnstyledBlock($helpText), "50%", null, LEFT, BOTTOM);
	
	$footerText = "<a href='doc/changelog.html'>Example v.0.5.2</a> ";
	$footerText .= "&copy;2007 Middlebury College: <a href='http://harmoni.sourceforge.net'>";
	$footerText .= _("about");
$footerText .= "</a>";
	$footer->add(new UnstyledBlock($footerText), "50%", null, RIGHT, BOTTOM);
	
	$mainScreen->add($footer, "100%", null, RIGHT, BOTTOM);

	return $mainScreen;

?>