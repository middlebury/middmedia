<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * Class for the embed code used for the files
 * that stream from the Flash Media Server. 
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_AudioPlayer
	implements MiddMedia_Embed_Plugin 
{
	private $audioPlayerUrl;
	
	/**
	 * Constructor
	 * 
	 * @param string $audioPlayerUrl The Url to the AudioPlayer directory.
	 * @return void
	 */
	public function __construct ($audioPlayerUrl) {
		if (empty($audioPlayerUrl))
			throw new InvalidArgumentException('$audioPlayerUrl must be specified.');
		
		// If the path to the swf was given, use its directory.
		$info = pathinfo($audioPlayerUrl);
		if (!empty($info['extension']))
			$audioPlayerUrl = $info['dirname'];
		
		// Delete any trailing slashes.
		$audioPlayerUrl = rtrim($audioPlayerUrl, '/');
		
		$this->audioPlayerUrl = $audioPlayerUrl;
	}
	
	/**
	 * Gets the title of the embed code
	 * 
	 * @return string
	 */
	function getTitle() {
		return 'Embed Code';
	}
	
	/**
	 * Gets the description for the embed code
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return string
	 */
	function getDesc(MiddMedia_File_MediaInterface $file) {
		return "\n<p>The following code can be pasted into web sites to display this audio in-line. Please note that some services may not allow the embedding of audio/videos.</p>";
	}
	
	/**
	 * Gets the embed code markup
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return string
	 */
	function getMarkup(MiddMedia_File_MediaInterface $file) {
		$httpUrl = $file->getFormat('mp3')->getHttpUrl();
		$fileId = rawurlencode($file->getFormat('mp3')->getBaseName());
		
		return '<script type="text/javascript" src="'.$this->audioPlayerUrl.'/audio-player.js"></script><object width="290" height="24" id="'.$fileId.'" data="'.$this->audioPlayerUrl.'/player.swf" type="application/x-shockwave-flash"><param value="'.$this->audioPlayerUrl.'/player.swf" name="movie" /><param value="high" name="quality" /><param value="false" name="menu" /><param value="transparent" name="wmode" /><param value="soundFile='.$httpUrl.'" name="FlashVars" /></object>';
	}

	/**
	 * Checks to see if the file is supported
	 * by the particular embed code
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return boolean
	 */
	function isSupported(MiddMedia_File_MediaInterface $file) {
		return $file->hasFormat('mp3');
	}
	
}