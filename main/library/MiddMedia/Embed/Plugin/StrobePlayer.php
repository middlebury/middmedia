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
class MiddMedia_Embed_Plugin_StrobePlayer
	implements MiddMedia_Embed_Plugin 
{
	private $strobePlayerUrl;
	
	/**
	 * Constructor
	 * 
	 * @param string $strobePlayerUrl The Url to the strobePlayer directory.
	 * @return void
	 */
	public function __construct ($strobePlayerUrl) {
		if (empty($strobePlayerUrl))
			throw new InvalidArgumentException('$strobePlayerUrl must be specified.');
		
		// If the path to the swf was given, use its directory.
		$info = pathinfo($strobePlayerUrl);
		if (!empty($info['extension']))
			$strobePlayerUrl = $info['dirname'];
		
		// Delete any trailing slashes.
		$strobePlayerUrl = rtrim($strobePlayerUrl, '/');
		
		$this->strobePlayerUrl = $strobePlayerUrl;
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
		return "\n<p>The following code can be pasted into web sites to display this video in-line. Please note that some services may not allow the embedding of audio/videos.</p>";
	}
	
	/**
	 * Gets the embed code markup
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return string
	 */
	function getMarkup(MiddMedia_File_MediaInterface $file) {
		$mp4 = $file->getFormat('mp4');
		if ($mp4->supportsRtmp()) {
			$mediaUrl = $mp4->getRtmpUrl();
		} else
			$mediaUrl = $mp4->getHttpUrl();
		
		$mp4_httpUrl = $mp4->getHttpUrl();
		
		$webm = $file->getFormat('webm');
		$webmUrl = $webm->getHttpUrl();
		
		$fileId = rawurlencode($file->getFormat('mp4')->getBaseName());
		$splash = rawurlencode($file->getFormat('splash')->getHttpUrl());
		 
		return '<video width="400" height="300" poster="'. $splash .'" controls>
<source src="'.$mp4_httpUrl.'" type="video/mp4" />
<source src="'.$webmUrl.'" type="video/webm" />
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="400" height="300">
<param name="movie" value="'.$this->strobePlayerUrl. '/StrobeMediaPlayback.swf' .'"></param>
<param name="FlashVars" value="src='.$mediaUrl.'&poster='. $splash .'"></param>
<param name="allowFullScreen" value="true"></param>
<param name="allowscriptaccess" value="always"></param>
<embed src="'.$this->strobePlayerUrl. '/StrobeMediaPlayback.swf' .'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="400" height="300" FlashVars="src='.$mediaUrl.'&poster='. $splash .'">
</embed>
</object>
</video>';
	}

	/**
	 * Checks to see if the file is supported
	 * by the particular embed code
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return boolean
	 */
	function isSupported(MiddMedia_File_MediaInterface $file) {
		return $file->hasFormat('mp4');
	}
	
}