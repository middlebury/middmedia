<?php
/**
 * @package middmedia
 *
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * Source video files are of arbitrary video type.
 *
 * @package middmedia
 *
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_File_Format_Video_Source
	extends MiddMedia_File_Format_Video_Abstract
	implements MiddMedia_File_FormatInterface, MiddMedia_File_Format_Video_InfoInterface
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
	public static function create (MiddMedia_File_MediaInterface $mediaFile) {
		self::touch($mediaFile, 'source', 'source');
		return new MiddMedia_File_Format_Video_Source($mediaFile);
	}

	/*********************************************************
	 * Instance Methods
	 *********************************************************/

	/**
	 * Answer the name of the subdirectory this format uses.
	 *
	 * @return string
	 */
	protected function getTargetSubdir () {
		return 'source';
	}

	/**
	 * Answer the extension to use for this format.
	 *
	 * @return string
	 */
	protected function getTargetExtension () {
		return 'source';
	}

	/**
	 * Answer true if this file is accessible via HTTP.
	 *
	 * @return boolean
	 */
	public function supportsHttp () {
		return false;
	}

	/**
	 * Answer true if this file is accessible via RTMP.
	 *
	 * @return boolean
	 */
	public function supportsRtmp () {
		return false;
	}

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
	public function process (Harmoni_Filing_FileInterface $source, $quality = NULL) {
		throw new OperationFailedException("Source files can't process other inputs");
	}

	/**
	 * Clean up our temporary files.
	 *
	 * @return void
	 */
	public function cleanup () {
		// Do nothing since we don't process anything.
	}
}
