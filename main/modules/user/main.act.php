<?php
/**
 * @package segue.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: main.act.php,v 1.6 2007/12/18 20:22:15 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Container.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");

/**
 * 
 * 
 * @package segue.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: main.act.php,v 1.6 2007/12/18 20:22:15 adamfranco Exp $
 */
class mainAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/24/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/05
	 */
	function getHeadingText () {
		return _("User Tools");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function buildContent () {
		$actionRows = $this->getActionRows();
		$harmoni = Harmoni::instance();
		
		if (RequestContext::value('login_failed')) {
			$actionRows->add(new Heading("<span style='color: red;'>"._("Error: Login Failed. Either your username or password was invalid.")."</span>", 2));
		}

		$actionRows->add(new Heading(_("Authentication"), 2));
		
		// Current AuthN Table
		ob_start();
		$authNManager = Services::getService("AuthN");
		$agentManager = Services::getService("Agent");
		$authTypes = $authNManager->getAuthenticationTypes();
		print "\n<table border='2' align='left'>";
		print "\n\t<tr><th colspan='3'><center>";
		print _("Current Authentications: ");
		print "</center>\n\t</th></tr>";
		
		while($authTypes->hasNext()) {
			$authType = $authTypes->next();
			$typeString = HarmoniType::typeToString($authType);
			print "\n\t<tr>";
			print "\n\t\t<td><small>";
			print "<a href='#' title='$typeString' onclick='alert(\"$typeString\")'>";
			print $authType->getKeyword();
			print "</a>";
			print "\n\t\t</small></td>";
			print "\n\t\t<td><small>";
			$userId = $authNManager->getUserId($authType);
			$userAgent = $agentManager->getAgent($userId);
			print '<a title=\''._("Agent Id").': '.$userId->getIdString().'\' onclick=\'Javascript:alert("'._("Agent Id").':\n\t'.$userId->getIdString().'");\'>';
			print $userAgent->getDisplayName();
			print "</a>";
			print "\n\t\t</small></td>";
			print "\n\t\t<td><small>";
			
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
			
			print "\n\t\t</small></td>";
			print "\n\t</tr>";
		}
		print "\n</table>";

		$statusBar = new Block(ob_get_contents(),2);
		$actionRows->add($statusBar,null,null,RIGHT,TOP);
		ob_end_clean();

		// Visitor Registration Link
		$authTypes = $authNManager->getAuthenticationTypes();
		$hasVisitorType = false;
		$visitorType = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
		while($authTypes->hasNext()) {
			$authType = $authTypes->next();
			if ($visitorType->isEqual($authType)) {
				$hasVisitorType = true;
				break;
			}
		}
		if ($hasVisitorType && !$authNManager->isUserAuthenticatedWithAnyType()) {
			ob_start();
			print "\n<ul>".
				"\n\t<li><a href='".
				$harmoni->request->quickURL("user", "visitor_reg")."'>".
				_("Visitor Registration").
				"</a></li>".
				"\n</ul>";
				
			$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, CENTER, CENTER);
		}
		
		// Change Password
		ob_start();
		$authTypes = $authNManager->getAuthenticationTypes();
		while($authTypes->hasNext()) {
			$authType = $authTypes->next();
			if ($authNManager->isUserAuthenticated($authType)) {
				$methodMgr = Services::getService("AuthNMethodManager");
				try {
					$method = $methodMgr->getAuthNMethodForType($authType);
					if ($method->supportsTokenUpdates()) {
						
						print "\n\t<li><a href='".
							$harmoni->request->quickURL("user", "change_password")."'>";
						$keyword = $authType->getKeyword();
						print str_replace('%1', $keyword, dgettext("polyphony", "Change '%1' Password"));
						print "</a></li>";	
					}
				} catch (Exception $e) {
				}
			}
		}
		$passLinks = ob_get_clean();
		if (strlen($passLinks)) {
			$actionRows->add(new Block("\n<ul>".$passLinks."\n</ul>", STANDARD_BLOCK), "100%", null, CENTER, CENTER);
		}
	}
}
