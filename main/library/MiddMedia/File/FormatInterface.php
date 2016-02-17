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
interface MiddMedia_File_FormatInterface
	extends MiddMedia_FileInterface
{

	/*********************************************************
	 * Instance creation methods.
	 *********************************************************/

	/**
	 * Create a new empty format file in a subdirectory of the media file. Similar to touch().
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 *
	 * @param MiddMedia_File_MediaInterface $mediaFile
	 * @return object MiddMedia_File_FormatInterface The new file
	 */
	public static function create (MiddMedia_File_MediaInterface $mediaFile);

	/*********************************************************
	 * Instance Methods
	 *********************************************************/

	/**
	 * Answer true if this file is accessible via HTTP.
	 *
	 * @return boolean
	 */
	public function supportsHttp ();

	/**
	 * Answer the full http path (URI) of this file.
	 *
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl ();

	/**
	 * Answer true if this file is accessible via RTMP.
	 *
	 * @return boolean
	 */
	public function supportsRtmp ();

	/**
	 * Answer the full RMTP path (URI) of this file
	 *
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl ();

	/**
	 * Convert the source file into our format and make our content the result.
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied or the source passed is unsupported.
	 *		OperationFailedException 	- If the file doesn't exist.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 *
	 * @param Harmoni_Filing_FileInterface $source
	 * @return void
	 */
	public function process (Harmoni_Filing_FileInterface $source, $quality = NULL);

	/**
	 * Clean up our temporary files.
	 *
	 * @return void
	 */
	public function cleanup ();

}
