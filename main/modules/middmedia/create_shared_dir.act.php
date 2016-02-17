<?php
/**
 * @since 12/11/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * <##>
 *
 * @since 12/11/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class create_shared_dirAction
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
		$authZMgr = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');

		return $authZMgr->isUserAuthorized(
				$idMgr->getId('edu.middlebury.authorization.modify'),
				$idMgr->getId('edu.middlebury.authorization.root'));
	}

	/**
	 * Exectute
	 *
	 * @return void
	 * @access public
	 * @since 12/11/08
	 */
	public function buildContent () {
		$harmoni = Harmoni::instance();

		try {
			$manager = MiddMedia_Manager_Admin::forCurrentUser();
			$dir = $manager->createSharedDirectory(RequestContext::value('group'));

			RequestContext::sendTo($harmoni->request->quickURL('middmedia', 'admin'));
		} catch (Exception $e) {
			throw $e;
		}
	}

}
