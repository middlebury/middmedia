<?php
/**
 * @since 11/19/09
 * @package middmedia
 *
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(dirname(__FILE__).'/upload.act.php');

/**
 * An upload action with output apropriate for human consumption
 *
 * @since 11/19/09
 * @package middmedia
 *
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class upload_form_resultAction
	extends uploadAction
{

	/**
	 * Send an error header and string.
	 *
	 * @param string $errorString
	 * @return void
	 * @access protected
	 * @since 11/13/08
	 */
	protected function error ($errorString) {
		$this->logError($errorString);

		header("HTTP/1.1 500 Internal Server Error");
		return new Block($errorString.$this->getReturnLink(), STANDARD_BLOCK);
	}

	/**
	 * Answer a return link.
	 *
	 * @return string
	 * @access protected
	 * @since 11/19/09
	 */
	protected function getReturnLink () {
		$harmoni = Harmoni::instance();
		return "\n<p><a href='".$harmoni->request->quickURL('middmedia', 'upload_form', array('directory' => $this->getDirectory()->getBaseName()))."'>&laquo; Back</a></p>";
	}

	/**
	 * Answer a success message and file info if needed
	 *
	 * @param MiddMedia_DirectoryInterface $dir
	 * @param MiddMedia_File $file
	 * @return mixed
	 * @access protected
	 * @since 11/19/09
	 */
	protected function success (MiddMedia_DirectoryInterface $dir, MiddMedia_File_Media $file) {
		ob_start();

		print $file->getBaseName().' successfully uploaded to '.$dir->getBaseName();

		return new Block(ob_get_clean().$this->getReturnLink(), STANDARD_BLOCK);
	}

}
