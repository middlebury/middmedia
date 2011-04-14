<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../Abstract.php');
require_once(dirname(__FILE__).'/../Image/InfoInterface.php');
require_once(dirname(__FILE__).'/InfoInterface.php');
require_once(dirname(__FILE__).'/../Audio/InfoInterface.php');


/**
 * Support for video info.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
abstract class MiddMedia_File_Format_Video_Abstract
	extends MiddMedia_File_Format_Abstract
	implements MiddMedia_File_Format_Video_InfoInterface
{
	
	private $videoCodec;
	private $colorspace;
	private $width;
	private $height;
	private $frameRate;
	private $audioCodec;
	private $audioSampleRate;
	private $audioChannels;
	private $populated = false;
	
	
	/**
	 * Populate our video info.
	 * 
	 * @return void
	 */
	private function populateInfo () {
		if ($this->populated)
			return;
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		
		$command = FFMPEG_PATH.' -i '.escapeshellarg($this->getPath()).' 2>&1';
		$lastLine = exec($command, $output, $return_var);
		$output = implode("\n", $output);
		
		if (!preg_match('/Stream #[^:]+: Video: ([^,]+), (?:([^,]+), )?([0-9]+)x([0-9]+)[^,]*, ([0-9\.]+) (?:tbr|kb\/s),/', $output, $matches))
			throw new OperationFailedException("Could not determine video properties from: <pre>\n$output\n</pre>\n");
		$this->videoCodec = $matches[1];
		$this->colorspace = $matches[2];
		$this->width = intval($matches[3]);
		$this->height = intval($matches[4]);
		$this->framerate = floatval($matches[5]);
		
		if (preg_match('/Stream #[^:]+: Audio: ([^,]+), ([0-9]+) Hz, ([0-9]+) channels/', $output, $matches)) {
			$this->audioCodec = $matches[1];
			$this->audioSampleRate = intval($matches[2]);
			$this->audioChannels = intval($matches[3]);
		} else {
			$this->audioCodec = null;
			$this->audioSampleRate = null;
			$this->audioChannels = null;
		}
		
		$this->populated = true;
	}
	
	/*********************************************************
	 * MiddMedia_File_Image_InfoInterface
	 *********************************************************/
	
	/**
	 * Answer the width of the image in pixels.
	 * 
	 * @return int
	 */
	public function getWidth () {
		$this->populateInfo();
		return $this->width;
	}
	
	/**
	 * Answer the height of the image in pixels.
	 * 
	 * @return int
	 */
	public function getHeight () {
		$this->populateInfo();
		return $this->height;
	}
	
	/*********************************************************
	 * MiddMedia_File_Format_Video_InfoInterface
	 *********************************************************/
		
	/**
	 * Answer the video codec used.
	 * 
	 * @return string
	 */
	public function getVideoCodec () {
		$this->populateInfo();
		return $this->videoCodec;
	}
	
	/**
	 * Answer the frame rate of the video.
	 * 
	 * @return float
	 */
	public function getVideoFrameRate () {
		$this->populateInfo();
		return $this->frameRate;
	}
	
	/*********************************************************
	 * MiddMedia_File_Format_Image_InfoInterface
	 *********************************************************/
	
	/**
	 * Answer the audio codec used.
	 * 
	 * @return string
	 */
	public function getAudioCodec () {
		$this->populateInfo();
		return $this->audioCodec;
	}
	
	/**
	 * Answer the sample rate of the audio.
	 * 
	 * @return int
	 */
	public function getAudioSampleRate () {
		$this->populateInfo();
		return $this->audioSampleRate;
	}
	
	/**
	 * Answer the number of channels in the audio.
	 * 
	 * @return int
	 */
	public function getAudioChannels () {
		$this->populateInfo();
		return $this->audioChannels;
	}
	
}
