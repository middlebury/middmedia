<?php
/**
 * @since 3/5/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update.abstract.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * Abstract class that defines common methods for update actions to have
 * 
 * @since 3/5/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update.abstract.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */
class Update 
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/08/05
	 */
	function isAuthorizedToExecute () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
 	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to run this update.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 6/08/05
	 */
	function getHeadingText () {
		return $this->getVersionIntroduced()." ".$this->getTitle();
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 3/5/07
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException();
		
		$harmoni = Harmoni::instance();
		ob_start();
		
		if ($this->isInPlace()) {
			print _("Update is already in place.");
		} else {
			if ($this->runUpdate())
				print "<br/><br/>"._('Update succeeded.');
			else
				print _('Update failed.');
		}
		
		print "\n<br/><a href='".$harmoni->request->quickURL('updates', 'list')."'>&lt;--"._('Return to list')."</a>";
		
		$block = new Block(ob_get_clean(), STANDARD_BLOCK);
		return $block;
	}
	
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/5/07
	 */
	function getDateIntroduced () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getTitle () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/5/07
	 */
	function getDescription () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/5/07
	 */
	function isInPlace () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
	/**
	 * Run the update.
	 * 
	 * @return void
	 * @access public
	 * @since 3/5/07
	 */
	function runUpdate () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridden in child classes."));
	}
	
}

?>