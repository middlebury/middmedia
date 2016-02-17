<?php
/**
 * @package middmedia.modules.window
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: display.act.php,v 1.29 2008/04/09 21:12:03 adamfranco Exp $
 */

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

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

/**
 * build the frame of the window
 *
 * @package middmedia.modules.window
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: display.act.php,v 1.29 2008/04/09 21:12:03 adamfranco Exp $
 */
class displayAction
	extends Action
{
	/**
	 * AuthZ
	 *
	 * @return boolean
	 * @access public
	 * @since 10/24/07
	 */
	public function isAuthorizedToExecute () {
		return true;
	}

	/**
	 * Execute the Action
	 *
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$harmoni = Harmoni::instance();

		$xLayout = new XLayout();
		$yLayout = new YLayout();


		$mainScreen = new Container($yLayout, BLANK, 1);

		// :: login, links and commands
		$this->headRow = new Container($xLayout, BLANK, 1);

		// Admin Links
		ob_start();

		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.modify'), $idMgr->getId('edu.middlebury.authorization.root'))) {
			print "<a href='".$harmoni->request->quickURL('admin', 'main')."'>";
			print _("Admin Tools");
			print "</a> | ";

		}

		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.change_user'), $idMgr->getId('edu.middlebury.authorization.root'))) {
			print "<a href='".$harmoni->request->quickURL('user', 'main')."'>";
			print _("User Tools");
			print "</a> ";
		}

		$this->headRow->add(new UnstyledBlock(ob_get_clean(), 1),
				null, null, LEFT, TOP);
		// END - Admin Links


		$rightHeadColumn = $this->headRow->add(
			new Container($yLayout, BLANK, 1),
			null, null, CENTER, TOP);


		$rightHeadColumn->add($this->getLoginComponent(),
				null, null, RIGHT, TOP);

		// The middlebury logo
		$middlogo = new Component("\n<a class='midd_logo' href=\"http://www.middlebury.edu\"></a>", BLANK, 1);
		$mainScreen->add($middlogo, '100%', null, CENTER, TOP);


	// BACKGROUND
		$backgroundContainer = $mainScreen->add(new Container($yLayout, BLOCK, BACKGROUND_BLOCK));

		// Add the previously created headRow to background
		$backgroundContainer->add($this->headRow, '100%', null, CENTER, TOP);

	// :: Top Row ::
		// The top row for the logo and status bar.
		$headRow = new Container($xLayout, HEADER, 1);

		// The logo
		$logo = new Component("\n<a href='".MYPATH."/'> <img src='".LOGO_URL."'
							style='border: 0px;' class='program_logo' alt='"._("MiddMedia Logo'"). "/> </a>", BLANK, 1);
		$headRow->add($logo, null, null, LEFT, TOP);

		// Language Bar
		/*$harmoni->history->markReturnURL("polyphony/language/change");
		$languageText = "\n<form action='".$harmoni->request->quickURL("language", "change")."' method='post'>";

		$harmoni->request->startNamespace("polyphony");
		$languageText .= "\n\t<div style='text-align: right'>\n\t<select style='font-size: 10px' name='".$harmoni->request->getName("language")."'>";
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


		$languageText .= "\n\t<input class='button small' value='Set language' type='submit' />&nbsp;";
		$languageText .= "\n\t</div>\n</form>";

		$languageBar = new Component($languageText, BLANK, 1);
		$headRow->add($languageBar, null, null, LEFT,BOTTOM);*/

		// Pretty Login Box
// 		$loginRow = new Container($yLayout, OTHER, 1);
// 		$headRow->add($loginRow, null, null, RIGHT, TOP);
// 		$loginRow->add($this->getLoginComponent(), null, null, RIGHT, TOP);

		//Add the headerRow to the backgroundContainer
		$backgroundContainer->add($headRow, "100%", null, LEFT, TOP);

	// :: Center Pane ::
		$centerPane = new Container($xLayout, BLANK, 1);
		$backgroundContainer->add($centerPane,"100%",null, LEFT, TOP);

		// Main menu
// 		$mainMenu = SegueMenuGenerator::generateMainMenu($harmoni->getCurrentAction());


		// use the result from previous actions
		if ($harmoni->printedResult) {
			$contentDestination = new Container($yLayout, OTHER, 1);
			$centerPane->add($contentDestination, null, null, LEFT, TOP);
			$contentDestination->add(new Block($harmoni->printedResult, 1), null, null, TOP, CENTER);
			$harmoni->printedResult = '';
		} else {
			$contentDestination = $centerPane;
		}

		// use the result from previous actions
		if (is_object($harmoni->result))
			$contentDestination->add($harmoni->result, null, null, CENTER, TOP);
		else if (is_string($harmoni->result))
			$contentDestination->add(new Block($harmoni->result, STANDARD_BLOCK), null, null, CENTER, TOP);

// 		$centerPane->add($mainMenu,"140px",null, LEFT, TOP);

		// Right Column
// 		$rightColumn = $centerPane->add(new Container($yLayout, OTHER, 1), "140px", null, LEFT, TOP);
		// Basket
// 		$basket = Basket::instance();
// 		$rightColumn->add($basket->getSmallBasketBlock(), "100%", null, LEFT, TOP);
// 		if (ereg("^(collection|asset)\.browse$", $harmoni->getCurrentAction()))
// 			$rightColumn->add(AssetPrinter::getMultiEditOptionsBlock(), "100%", null, LEFT, TOP);

	// :: Footer ::
		$footer = new Container (new XLayout, BLANK, 1);

		// The middlebury logo
		$footerwrapper = new Container (new XLayout, BLANK, 1);

		$footerwrapper->add(new UnstyledBlock("<a href='https://mediawiki.middlebury.edu/wiki/LIS/MiddMedia' target='_blank'>"._("Help")."</a>"), "50%", null, LEFT, BOTTOM);

		$footerwrapper->add(new UnstyledBlock(self::getVersionText()), "50%", null, RIGHT, BOTTOM);

		$footer->add($footerwrapper, "100%", null, RIGHT, BOTTOM);

		$mainScreen->add($footer, "100%", null, RIGHT, BOTTOM);

		return $mainScreen;
	}

	/**
	 * Answer the version and copyright text
	 *
	 * @return string
	 * @access public
	 * @since 9/25/07
	 */
	public static function getVersionText () {
		$harmoni = Harmoni::instance();
		ob_start();
		print "<div class='seguefooter_right'>";
		print "<a href='".$harmoni->request->quickURL('window', 'changelog')."' target='_blank'>MiddMedia v.".self::getSegueVersion()."</a>";
// 		print "&nbsp; &nbsp; &nbsp; ";
// 		print "&copy;".self::getSegueCopyrightYear()." Middlebury College";
// 		print "&nbsp; &nbsp; &nbsp; <a href='http://segue.sourceforge.net'>";
// 		print _("about Segue");
// 		print "</a>";
		print "</div>";

		return ob_get_clean();
	}

	/**
	 * Answer the segue version string
	 *
	 * @return string
	 * @access public
	 * @since 1/18/08
	 * @static
	 */
	public static function getSegueVersion () {
		if (!isset($_SESSION['SegueVersion'])) {
			$document = new DOMDocument();
			// attempt to load (parse) the xml file
			if ($document->load(MYDIR."/doc/raw/changelog/changelog.xml")) {
				$versionElems = $document->getElementsByTagName("version");
				$latest = $versionElems->item(0);
				$_SESSION['SegueVersion'] = $latest->getAttribute('number');
				if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $latest->getAttribute('date'), $matches))
					$_SESSION['SegueCopyrightYear'] = $matches[1];
				else
					$_SESSION['SegueCopyrightYear'] = $latest->getAttribute('date');
			} else {
				$_SESSION['SegueVersion'] = "2.x.x";
				$_SESSION['SegueCopyrightYear'] = "2007";
			}
		}

		return $_SESSION['SegueVersion'];
	}

	/**
	 * Answer the segue version string
	 *
	 * @return string
	 * @access public
	 * @since 1/18/08
	 * @static
	 */
	public static function getSegueCopyrightYear () {
		self::getSegueVersion();
		return $_SESSION['SegueCopyrightYear'];
	}

	/**
	 * Answer the component containing the login/logout form.
	 *
	 * @return object Component
	 * @access public
	 * @since 3/13/06
	 */
	function getLoginComponent () {
		ob_start();
		$harmoni = Harmoni::instance();
		$authN = Services::getService("AuthN");
		$agentM = Services::getService("Agent");
		$idM = Services::getService("Id");
		$authTypes = $authN->getAuthenticationTypes();
		$users = '';
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			$id = $authN->getUserId($authType);
			if (!$id->isEqual($idM->getId('edu.middlebury.agents.anonymous'))) {
				$agent = $agentM->getAgent($id);
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
		print "\n<div class='login'>";
		// set bookmarks for success and failure
		$current = $harmoni->request->mkURLWithPassthrough();
		$current->setValue('login_failed', null);
		$harmoni->history->markReturnURL("polyphony/display_login", $current);
		if ($users != '') {

			if (count(explode("+", $users)) == 1)
				print $users."\t";
			else
				print _("Users: ").$users."\t";

			print " | <a href='".$harmoni->request->quickURL("auth",
				"logout")."'>"._("Log Out")."</a>";
		} else {
			if (defined('LOGIN_FORM_CALLBACK')) {
				print call_user_func(LOGIN_FORM_CALLBACK);
			} else {
				$harmoni->history->markReturnURL("polyphony/login_fail",
					$harmoni->request->quickURL("user", "main", array('login_failed' => 'true')));

				$harmoni->request->startNamespace("harmoni-authentication");
				$usernameField = $harmoni->request->getName("username");
				$passwordField = $harmoni->request->getName("password");
				$harmoni->request->endNamespace();
				$harmoni->request->startNamespace("polyphony");
				print "\n<form action='".
					$harmoni->request->quickURL("auth", "login").
					"' style='text-align: right' method='post'><small>".
					"\n\t"._("Username/email:")." <input class='small' type='text' size='8'
						name='$usernameField'/>".
					"\n\t"._("Password:")." <input class='small' type='password' size ='8'
						name='$passwordField'/>".
					"\n\t <input class='button small' type='submit' value='Log in' />".
					"\n</small></form>";
				$harmoni->request->endNamespace();
			}
		}

		// Visitor Registration Link
		$authTypes = $authN->getAuthenticationTypes();
		$hasVisitorType = false;
		$visitorType = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
		while($authTypes->hasNext()) {
			$authType = $authTypes->next();
			if ($visitorType->isEqual($authType)) {
				$hasVisitorType = true;
				break;
			}
		}
		if ($hasVisitorType && !$authN->isUserAuthenticatedWithAnyType()) {
			$url = $harmoni->request->mkURL("user", "visitor_reg");

			// Add return info to the visitor registration url
			$visitorReturnModules = array('view', 'ui1', 'ui2', 'versioning');
			if (in_array($harmoni->request->getRequestedModule(), $visitorReturnModules)) {
				$url->setValue('returnModule', $harmoni->request->getRequestedModule());
				$url->setValue('returnAction', $harmoni->request->getRequestedAction());
				$url->setValue('returnKey', 'node');
				$url->setValue('returnValue', SiteDispatcher::getCurrentNodeId());
			}

			print "\n<div class='visitor_reg_link'>".
				"\n\t<a href='".$url->write()."'>".
				_("Visitor Registration").
				"</a>".
				"\n</div>";
		}

		print "\n</div>";
		$loginForm = new Component(ob_get_clean(), BLANK, 2);

		return $loginForm;
	}

	/**
	 * Answer the current UI module
	 *
	 * @return string
	 * @access public
	 * @since 7/27/07
	 */
	function getUiModule () {
		$module = UserData::instance()->getPreference('segue_ui_module');
		$allowed = array('ui1', 'ui2');
		if (in_array($module, $allowed))
			return $module;
		else
			return 'ui2';
	}


	/**
	 * Set the UI module
	 *
	 * @param string $module
	 * @return void
	 * @access public
	 * @since 7/27/07
	 */
	function setUiModule ($module) {
		$allowed = array('ui1', 'ui2');

		if (in_array($module, $allowed))
			UserData::instance()->setPreference('segue_ui_module', $module);
	}


	/**
	 * Get the form for switching to a different UI mode.
	 *
	 * @return string
	 * @access public
	 * @since 9/6/07
	 */
	public function getUiSwitchForm () {
		$harmoni = Harmoni::instance();
		ob_start();
		print "\n\t<form action='";
		print $harmoni->request->quickURL('view', 'change_ui');
		print "' method='post' ";
		print "style='display: inline;'>";
		$returnUrl = $harmoni->request->mkURLWithPassthrough();

		print "\n\t\t<input type='hidden' name='".RequestContext::name('returnUrl')."' value='".rawurlencode($returnUrl->write())."'/>";

		print "\n\t\t<select style='font-size: 10px' name='".RequestContext::name('user_interface')."' ";
		print "onchange=\"this.form.submit();\"";
		print ">";
		$options = array ('ui1' => _("Classic Mode"), 'ui2' => _("New Mode"));
		foreach ($options as $key => $val) {
			print "\n\t\t\t<option value='$key'";
			print (($this->getUiModule() == $key)?" selected='selected'":"");
			print ">$val</option>";
		}
		print "\n\t\t</select>";
		print "\n\t</form>";
		return ob_get_clean();
	}
}
