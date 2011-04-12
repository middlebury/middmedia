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
class MiddMedia_File_Format_Image_Thumbnail
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
		$dir = $directory->getFsPath().'/thumb';
		if (!file_exists($dir)) {
			if (!is_writable($directory->getFsPath()))
				throw new ConfigurationErrorException($directory->getBaseName()." is not writable.");
			mkdir($dir);
		}
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$extension = 'jpg';
		$name = $pathInfo['filename'].'.'.$extension;
		
		touch($dir.'/'.$name);
		return new MiddMedia_File_Format_Image_Thumbnail($mediaFile);
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
		return new MiddMedia_File_Format_Image_Thumbnail($mediaFile);
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
		$extension = 'jpg';
		$this->basename = $pathInfo['filename'].'.'.$extension;
		
		parent::__construct($mediaFile->getDirectory()->getFSPath().'/thumb/'.$this->basename);
	}
	
	/**
	 * Answer true if this file is accessible via HTTP.
	 * 
	 * @return boolean
	 */
	public function supportsHttp () {
		return true;
	}
	
	/**
	 * Answer the full http path (URI) of this file.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return $this->mediaFile->getDirectory()->getHttpUrl().'/thumb/'.$this->getBaseName();
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
	public function process (Harmoni_Filing_FileInterface $fullFrame) {
		if (!preg_match('/^image\/.+$/', $fullFrame->getMimeType()))
			throw new InvalidArgumentException("Unsupported image type, ".$fullFrame->getMimeType());
		
		if (!$fullFrame->isReadable())
			throw new PermissionDeniedException('Full-frame file is not readable: '.$this->media->getDirectory()->getBaseName().'/'.basename(dirname($fullFrame->getPath())).'/'.$fullFrame->getBaseName());
		
		// Set up the Thumbnail Image directory
		$thumbDir = basename($this->getPath());
		
		if (!file_exists($thumbDir)) {
			if (!mkdir($thumbDir, 0775))
				throw new PermissionDeniedException('Could not create thumb dir: '.$this->media->getDirectory()->getBaseName().'/thumb');
		}
		
		if (!is_writable($thumbDir))
			throw new PermissionDeniedException('Thumb dir is not writable: '.$this->media->getDirectory()->getBaseName().'/thumb');
		
		if (!defined('IMAGE_MAGICK_CONVERT_PATH'))
			throw new ConfigurationErrorException('IMAGE_MAGICK_CONVERT_PATH is not defined');
		
		
		$destImage = $this->getPath().'-tmp';
		$command = IMAGE_MAGICK_CONVERT_PATH.' '.escapeshellarg($fullFrame->getPath()).' -resize 200x200 '.escapeshellarg($destImage);
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Thumbnail-Image generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperaionFailedException('Thumbnail-Image was not generated: '.$this->media->getDirectory()->getBaseName().'/thumb/'.$parts['filename'].'.jpg');
		
		$this->moveInUploadedFile($destImage);
		$this->cleanup();
	}

	/**
	 * Clean up our temporary files.
	 * 
	 * @return void
	 */
	public function cleanup () {
		$outFile = $this->getPath().'-tmp';
		if (file_exists($outFile))
			unlink($outFile);
		
		if (file_exists($outFile))
			throw new OperationFailedException("Could not delete $outFile");
	}
}
