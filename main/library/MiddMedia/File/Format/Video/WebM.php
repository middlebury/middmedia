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
class MiddMedia_File_Format_Video_WebM
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
		self::touch($mediaFile, 'webm', 'webm');
		return new MiddMedia_File_Format_Video_WebM($mediaFile);
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
		return 'webm';
	}
	
	/**
	 * Answer the extension to use for this format.
	 *
	 * @return string
	 */
	protected function getTargetExtension () {
		return 'webm';
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
		if (!$source instanceof MiddMedia_File_Format_Video_InfoInterface)
			throw new InvalidArgumentException('$source must implement MiddMedia_File_Format_Video_InfoInterface');
		
		// If our source is a webm file, just copy it in without transcoding.
		if ($source->getVideoCodec() == 'libvpx') {
			$this->copyInFile($source->getPath());
			return;
		}
		
		// Transcode the file.
		$outFile = $this->getPath().'-tmp.webm';
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		if (!defined('MIDDMEDIA_CONVERT_MAX_WIDTH'))
			throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_WIDTH is not defined');
		if (!defined('MIDDMEDIA_CONVERT_MAX_HEIGHT'))
			throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_HEIGHT is not defined');
			
		// Determine the output size base on our maximums/quality.
		$dimensions = $this->getTargetDimensions($source->getWidth(), $source->getHeight(), $quality);

		// Determine the output video bitrate based on our quality 
		$video_bitrate = $this->getVideoBitrate($source->getHeight(), $quality);
		
		// Some audio sample rates die, so force to the closest of 44100, 22050, 11025
		$sampleRate = $this->getTargetSampleRate($source->getAudioSampleRate());
		
		// Convert the video
		$command = FFMPEG_PATH
			.' -i '.escapeshellarg($source->getPath())
			.' -s '.$dimensions.' -y -f webm -vcodec libvpx'
			// Bitrate parameters - variable bitrate averaging 500k
			.' -b '. $video_bitrate
			.' -passlogfile '.escapeshellarg($this->getPath());
		
		$pass1Command = $command.' -pass 1'
			// no audio for first pass.
			.' -an'
			.' '.escapeshellarg($outFile).' 2>&1';
		$pass2Command = $command.' -pass 2 '
			// Audio parameters
			.' -acodec libvorbis -ar '.$sampleRate.' -ab 100k -ac 2'
			.' '.escapeshellarg($outFile).' 2>&1';
		
		$lastLine = exec($pass1Command, $output, $return_var);
		$output = implode("\n", $output);
		
// 		print "\n".$pass1Command."\n".$pass2Command."\n";
		
		if ($return_var) {
			$this->cleanup();
			$this->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.webm'));
			throw new OperationFailedException("Video encoding failed with error $return_var and output: \n<pre>\n$output\n</pre>\n");
		}
		
		$lastLine = exec($pass2Command, $output, $return_var);
		$output = implode("\n", $output);
		
		if ($return_var) {
			$this->cleanup();
			$this->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.webm'));
			throw new OperationFailedException("Video encoding failed with error $return_var and output: \n<pre>\n$output\n</pre>\n");
		}
		
		// Move into position
		$this->moveInFile($outFile);
		$this->cleanup();
	}

	/**
	 * Clean up our temporary files.
	 * 
	 * @return void
	 */
	public function cleanup () {
		$outFile = $this->getPath().'-tmp.webm';
		if (file_exists($outFile))
			unlink($outFile);
		
		if (file_exists($outFile))
			throw new OperationFailedException("Could not delete $outFile");
		
		// Delete the log files.
		foreach (scandir(dirname($this->getPath())) as $file) {
			if (preg_match('/'.$this->getBasename().'-[0-9]+\.log/', $file))
				unlink(dirname($this->getPath()).'/'.$file);
		}
	}
}

?>