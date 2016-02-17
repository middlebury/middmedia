<?php
/**
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * Provide A preview of the media.
 *
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class previewAction
	extends Action
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
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException();

		$dir = $this->getDirectory();

		$file = $dir->getFile(RequestContext::value('file'));

		$plugins = MiddMedia_Embed_Plugins::instance();
		foreach ($plugins->getPlugins() as $embed) {
			if ($embed->isSupported($file)) {
				try {
					print "\n".$embed->getMarkup($file);
				} catch (Exception $e) {
					print "\n<p class='error'>Error: ".$e->getMessage()."</p>";
				}

				print "\n<p>";
				print "\n\t<a href='#' onclick=\"displayEmbedCode(this, null, '".$dir->getBaseName()."', '".$file->getBaseName()."', null); return false;\">Embed Code &amp; URLs</a>";
				print "\n</p>";

				exit;
			}
		}

		print "Error: No Embed plugins added with the useForPreview flag.";
		print "<br/>Add these in your middmedia/conf/middmedia.conf.php";
		exit;
	}

	/**
	 * Answer the target directory object
	 *
	 * @return object MiddMedia_Directory
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
	 * Answer the manager to use
	 *
	 * @return MiddMediaManager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMedia_Manager::forCurrentUser();
	}

}
