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
class MiddMedia_File_Format_Image_FullFrame
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
		$dir = $directory->getFsPath().'/full_frame';
		if (!file_exists($dir)) {
			if (!is_writable($directory->getFsPath()))
				throw new ConfigurationErrorException($directory->getBaseName()." is not writable.");
			mkdir($dir);
		}
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$extension = 'jpg';
		$name = $pathInfo['filename'].'.'.$extension;
		
		touch($dir.'/'.$name);
		return new MiddMedia_File_Format_Image_FullFrame($mediaFile);
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
		return new MiddMedia_File_Format_Image_FullFrame($mediaFile);
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
		
		parent::__construct($mediaFile->getDirectory()->getFSPath().'/full_frame/'.$this->basename);
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
		return $this->mediaFile->getDirectory()->getHttpUrl().'/full_frame/'.$this->getBaseName();
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
		$timecodes = array(5, 2);
		
		// Try several time-codes and see if we can get an image out.
		while (!isset($fullFrame) && current($timecodes)) {
			$seconds = current($timecodes);
			try {
				$this->createFullFrame($source, $seconds);
				return;
			} catch (OperationFailedException $e) {
				next($timecodes);
			}
		}
		
		// if we still don't have an image, copy in our default one.
		if (!defined('MIDDMEDIA_DEFAULT_FRAME_PATH'))
			throw new ConfigurationErrorException('MIDDMEDIA_DEFAULT_FRAME_PATH is not defined');
		if (!copy(MIDDMEDIA_DEFAULT_FRAME_PATH, $this->getPath()))
			throw new OperationFailedException('Could not copy default full-frame image');
	}
	
	
	/**
	 * Create a full-frame image from the video file at the time-code specified.
	 *
	 * Throws:
	 *		InvalidArgumentException on invalid time-code
	 *		PermissionDeniedException on read/write failure.
	 *		ConfigurationErrorException on invalid configuration
	 *		OperationFailedException on image extraction failure.
	 * 
	 * @param Harmoni_Filing_FileInterface $source
	 * @param optional float $seconds Time-offset at which to grab the frame.
	 * @return MiddMedia_ImageFile the full-frame image file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createFullFrame (Harmoni_Filing_FileInterface $source, $seconds = 5) {
		$seconds = floatval($seconds);
		if ($seconds <= 0)
			throw new InvalidArgumentException('$seconds must be a float greater than zero. '.$seconds.' is invalid.');
		
		if (!$source->isReadable())
			throw new PermissionDeniedException('Video file is not readable: '.$this->mediaFile->getDirectory()->getBaseName().'/'.basename(dirname($source->getPath())).'/'.$source->getBaseName());
		
		$fullFramesDir = $this->mediaFile->getDirectory()->getFsPath().'/full_frame';
		
		if (!file_exists($fullFramesDir)) {
			if (!mkdir($fullFramesDir, 0775))
				throw new PermissionDeniedException('Could not create full-frames dir: '.$this->mediaFile->getDirectory()->getBaseName().'/full_frame');
		}
		
		if (!is_writable($fullFramesDir))
			throw new PermissionDeniedException('Full-Frames dir is not writable: '.$this->mediaFile->getDirectory()->getBaseName().'/full_frame');
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		
		// Try to create the full-frame
		$destImage = dirname($this->getPath()).'/tmp-'.basename($this->getPath());
		$command = FFMPEG_PATH.' -vframes 1 -ss '.$seconds.' -i '.escapeshellarg($source->getPath()).'  -vcodec mjpeg '.escapeshellarg($destImage).'  2>&1';
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Full-frame generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperationFailedException('Full-frame was not generated: '.$this->mediaFile->getDirectory()->getBaseName().'/full_frame/'.basename($destImage));
		
		$this->moveInUploadedFile($destImage);
		$this->cleanup();
	}

	/**
	 * Clean up our temporary files.
	 * 
	 * @return void
	 */
	public function cleanup () {
		$outFile = dirname($this->getPath()).'/tmp-'.basename($this->getPath());
		if (file_exists($outFile))
			unlink($outFile);
		
		if (file_exists($outFile))
			throw new OperationFailedException("Could not delete $outFile");
	}
}

?>