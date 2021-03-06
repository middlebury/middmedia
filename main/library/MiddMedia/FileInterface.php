<?php
/**
 * @package middmedia
 *
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * An interface for all middmedia files.
 *
 * @package middmedia
 *
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_FileInterface
	extends Harmoni_Filing_FileInterface
{

	/**
	 * Move an uploaded file into our path.
	 *
	 * @param string $sourcePath
	 * @return void
	 */
	public function moveInUploadedFile ($sourcePath);

	/**
	 * Move a file into our path.
	 *
	 * @param string $sourcePath
	 * @return void
	 */
	public function moveInFile ($sourcePath);

	/**
	 * Copy a file into our path.
	 *
	 * @param string $sourcePath
	 * @return void
	 */
	public function copyInFile ($sourcePath);
}
