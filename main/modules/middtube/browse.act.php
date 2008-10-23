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
class browseAction 
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
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Browse Your Videos");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		
		$actionRows = $this->getActionRows();
		ob_start();
		
		print "\n<p>";
		print _("to do.....");
		print "</p>";
		
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}	
}

?>