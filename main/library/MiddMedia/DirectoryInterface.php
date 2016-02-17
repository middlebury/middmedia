<?php
/**
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(HARMONI.'utilities/Filing/Directory.interface.php');

/**
 * This is a basic interface for directory access, used to allow methods
 * to return a single object that represents a directory.
 *
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_DirectoryInterface
	extends Harmoni_Filing_DirectoryInterface
{
	/*********************************************************
	 * Static methods
	 *********************************************************/

	/**
	 * Answer the directory if it exists. Throw an UnknownIdException if it doesn't.
	 *
	 * @param object MiddMedia_Manager $manager
	 * @param string $name
	 * @return object MiddMedia_DirectoryInterface
	 */
	public static function getIfExists (MiddMedia_Manager $manager, $name);

	/**
	 * Answer the directory, creating if needed.
	 *
	 * @param object MiddMedia_Manager $manager
	 * @param string $name
	 * @return ovject MiddMedia_DirectoryInterface
	 */
	public static function getAlways (MiddMedia_Manager $manager, $name);

	/*********************************************************
	 * Instance methods
	 *********************************************************/

	/**
	 * Answer the full http path (URI) of this directory
	 *
	 * @return string
	 */
	public function getHttpUrl ();

	/**
	 * Answer the full RMTP path (URI) of this directory
	 *
	 * @return string
	 */
	public function getRtmpUrl ();

	/**
	 * Answer the number of bytes used.
	 *
	 * @return int
	 */
	public function getBytesUsed ();

	/**
	 * Answer the number of bytes availible before a quota is reached.
	 *
	 * @return int
	 */
	public function getBytesAvailable ();

	/**
	 * Answer the quota size in bytes
	 *
	 * @return int
	 */
	public function getQuota ();

	/**
	 * Answer true if this directory has a custom quota
	 *
	 * @return boolean
	 */
	public function hasCustomQuota ();

	/**
	 * Set the quota of this directory in bytes.
	 *
	 * @param int $quota
	 * @return void
	 */
	public function setCustomQuota ($quota);

	/**
	 * Remove the custom quota
	 *
	 * @return void
	 */
	public function removeCustomQuota ();

	/**
	 * Answer the default quota for this directory
	 *
	 * @return int
	 */
	public function getDefaultQuota ();

	/**
	 * Create a file with content (and handle any conversion if necessary).
	 *
	 * @param string $name
	 * @param string $content
	 * @return object MiddMedia_File_Media The new file
	 */
	public function createFileFromData ($name, $content);

	/**
	 * Create a file in this directory from an upload. Similar to move_uploaded_file().
	 *
	 * @param array $fileArray The element of the $_FILES superglobal for this file.
	 * @return object MiddMedia_File_Media The new file
	 */
	public function createFileFromUpload (array $fileArray);
}
