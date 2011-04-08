<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../../Format.interface.php');

/**
 * Source video files are of arbitrary video type.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_File_Format_Video_Source
	extends Harmoni_Filing_FileSystemFile
	implements MiddMedia_File_FormatInterface
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
		$directory = $mediaFile->getDirectory();
		$dir = $directory->getFsPath().'/source';
		if (!file_exists($dir)) {
			if (!is_writable($directory->getFsPath()))
				throw new ConfigurationErrorException($directory->getBaseName()." is not writable.");
			mkdir($dir);
		}
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$extension = 'source';
		$name = $pathInfo['filename'].'.'.$extension;
		
		touch($dir.'/'.$name);
		return new MiddMedia_File_Format_Video_Source($mediaFile);
	}
	
	/**
	 * Get an existing file in a subdirectory of the media file.
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file doesn't exist.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_File_MediaInterface $mediaFile
	 * @param string $name
	 * @return object MiddMedia_File_FormatInterface The new file
	 */
	public static function get (MiddMedia_File_MediaInterface $mediaFile) {
		return new MiddMedia_File_Format_Video_Source($mediaFile);
	}
	
	/*********************************************************
	 * Instance Methods
	 *********************************************************/
	
	/**
	 * Constructor.
	 * 
	 * @param MiddMedia_File_MediaInterface $mediaFile
	 * @param string $basename
	 * @return void
	 */
	public function __construct (MiddMedia_File_MediaInterface $mediaFile) {
		$this->mediaFile = $mediaFile;
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$extension = 'source';
		$this->basename = $pathInfo['filename'].'.'.$extension;
		
		parent::__construct($mediaFile->getDirectory()->getFSPath().'/source/'.$this->basename);
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
	 * Answer the full http path (URI) of this file.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		throw new OperationFailedException('supportsHttp() is false');
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
	 * Answer the full RMTP path (URI) of this file
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		throw new OperationFailedException('getRtmpUrl() is false');
	}
	
	/**
	 * Move an uploaded file into our file.
	 * 
	 * @param string $tempName
	 * @return void
	 */
	public function moveInUploadedFile ($tempName) {
		rename($tempName, $this->getPath());
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
	public function process (Harmoni_Filing_FileInterface $source) {
		throw new OperationFailedException("Source files can't process other inputs");
	}

	
}

?>