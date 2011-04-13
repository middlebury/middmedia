<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../Abstract.class.php');

/**
 * Source video files are of arbitrary video type.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_File_Format_Video_Mp4
	extends MiddMedia_File_Format_Abstract
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
		self::touch($mediaFile, 'mp4', 'mp4');
		return new MiddMedia_File_Format_Video_Mp4($mediaFile);
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
		return 'mp4';
	}
	
	/**
	 * Answer the extension to use for this format.
	 *
	 * @return string
	 */
	protected function getTargetExtension () {
		return 'mp4';
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
		return $this->mediaFile->getDirectory()->getHttpUrl().'/mp4/'.$this->getBaseName();
	}

	/**
	 * Answer true if this file is accessible via RTMP.
	 * 
	 * @return boolean
	 */
	public function supportsRtmp () {
		return true;
	}
	
	/**
	 * Answer the full RMTP path (URI) of this file
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		return $this->mediaFile->getDirectory()->getRtmpUrl().'/mp4/'.$this->getBaseName();
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
		$outFile = $this->getPath().'-tmp.mp4';
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		if (!defined('MIDDMEDIA_CONVERT_MAX_WIDTH'))
			throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_WIDTH is not defined');
		if (!defined('MIDDMEDIA_CONVERT_MAX_HEIGHT'))
			throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_HEIGHT is not defined');
			
		// Determine the output size base on our maximums.
		$info = MiddMedia_File_Media::getVideoInfo($source->getPath());
		$width = $info['width'];
		$height = $info['height'];
		if ($width > MIDDMEDIA_CONVERT_MAX_WIDTH) {
			$ratio = MIDDMEDIA_CONVERT_MAX_WIDTH / $width;
			$width = MIDDMEDIA_CONVERT_MAX_WIDTH;
			$height = round($ratio * $height);
		}
		if ($height > MIDDMEDIA_CONVERT_MAX_HEIGHT) {
			$ratio = MIDDMEDIA_CONVERT_MAX_HEIGHT / $height;
			$width = round($ratio * $width);
			$height = MIDDMEDIA_CONVERT_MAX_HEIGHT;
		}
		// Round to the nearest multiple of 2 as this is required for frame sizes.
		$width = round($width/2) * 2;
		$height = round($height/2) * 2;
		
		// Some audio sample rates die, so force to the closest of 44100, 22050, 11025
		$sampleRate = $info['audio_samplerate'];
		if (!in_array($sampleRate, array(44100, 22050, 11025))) {
			if ($sampleRate < 16538)
				$sampleRate = 11025;
			else if ($sampleRate < 33075)
				$sampleRate = 22050;
			else
				$sampleRate = 44100;
		}
		
		// Convert the video
		$command = FFMPEG_PATH
			.' -i '
			.escapeshellarg($source->getPath())
			.' -vcodec libx264 -vpre normal -b 500k -bt 500k '
			.' -ar '.$sampleRate.' '
			.' -s '.$width.'x'.$height.' '
			.escapeshellarg($outFile).' 2>&1';
		$lastLine = exec($command, $output, $return_var);
		$output = implode("\n", $output);
		
		if ($return_var) {
			$this->cleanup();
			$this->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.mp4'));
			throw new OperationFailedException("Video encoding failed with error $return_var and output: \n<pre>\n$output\n</pre>\n");
		}
		
		// Move into position
		$this->moveInUploadedFile($outFile);
		$this->cleanup();
	}

	/**
	 * Clean up our temporary files.
	 * 
	 * @return void
	 */
	public function cleanup () {
		$outFile = $this->getPath().'-tmp.mp4';
		if (file_exists($outFile))
			unlink($outFile);
		
		if (file_exists($outFile))
			throw new OperationFailedException("Could not delete $outFile");
	}
}

?>