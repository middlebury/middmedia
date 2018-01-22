<?php
/**
 * @since 11/13/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * HTML form for file upload
 *
 * @since 11/13/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class upload_formAction
	extends MiddMedia_Action_Abstract
{

	/**
	 * Check Authorizations
	 *
	 * @return boolean
	 * @access public
	 * @since 11/13/08
	 */
	public function isAuthorizedToExecute () {
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		if (!$authN->isUserAuthenticatedWithAnyType())
			return false;
		try {
			$dir = $this->getDirectory();
		} catch (PermissionDeniedException $e) {
			return false;
		}
		return true;
	}

	/**
	 * Execute this action
	 *
	 * @return void
	 * @access public
	 * @since 11/13/08
	 */
	function buildContent () {
		// Disable uploads.
		$actionRows = $this->getActionRows();
		ob_start();
		print "<div class='warning'>".UPLOAD_DISABLED_MESSAGE."</div>";
		$actionRows->add(
				new Block(ob_get_clean(), STANDARD_BLOCK),
				"100%",
				null,
				CENTER,
				CENTER);
	}

	/**
	 * Answer the target directory object
	 *
	 * @return object MiddMedia_DirectoryInterface
	 * @access protected
	 * @since 11/19/08
	 */
	protected function getDirectory () {
		if (!isset($this->directory)) {
			$manager = $this->getManager();
			$this->directory = $manager->getDirectory(RequestContext::value('directory'));
		}

		return $this->directory;
	}

	/**
	 * @var object MiddMedia_DirectoryInterface $directory;
	 * @access private
	 * @since 11/19/08
	 */
	private $directory;

}
