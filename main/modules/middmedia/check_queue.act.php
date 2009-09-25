<?php
/**
 * @since 12/10/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/browse.act.php');

/**
 * Browse all media as an admin
 * 
 * @since 12/10/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class check_queueAction
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
		return true;
	}
	
	/**
	 * Exectute
	 * 
	 * @return void
	 * @access public
	 * @since 12/11/08
	 */
	public function buildContent () {
		try {
			$manager = AdminMiddMediaManager::forSystemUser();
			MiddMedia_File::checkQueue($manager);
			print "Done\n";
			exit;
		} catch (Exception $e) {
			throw $e;
		}
	}
	
}

?>