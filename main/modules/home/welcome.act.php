<?php
/**
 * @package segue.modules.home
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 *
 *
 * @package segue.modules.home
 *
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */
class welcomeAction
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 *
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}

	/**
	 * Return the heading text for this action, or an empty string.
	 *
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Welcome to MiddMedia");
	}

	/**
	 * Build the content for this action
	 *
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$authN = Services::getService("AuthN");
		if ($authN->isUserAuthenticatedWithAnyType()) {
			$harmoni = Harmoni::instance();
			RequestContext::sendTo($harmoni->request->quickURL('middmedia', 'browse'));
		}

		$actionRows = $this->getActionRows();
		ob_start();

		print "\n<p>";
		print _("Welcome to the <strong>MiddMedia</strong> video management system.");
		print "</p>";

		print "\n<p>";
		print _("Please log in above to manage your videos.");
		print "</p>";

		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK),
			"100%",
			null,
			CENTER,
			CENTER);
	}
}
