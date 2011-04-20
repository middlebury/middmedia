<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * Class for the embed code used for
 * showing the RTMP link to the video. 
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_Rtmp
	implements MiddMedia_Embed_Plugin 
{

	/**
	 * Gets the title of the embed code
	 * 
	 * @return string
	 */
	function getTitle() {
		return "RTMP (Streaming) URL";
	}

	/**
	 * Gets the description for the embed code
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return string
	 */
	function getDesc(MiddMedia_File_MediaInterface $file) {
		return "\n<p>The following URL may be used in custom Flash video players to stream this video.</p>";
	}

	/**
	 * Gets the embed code markup
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return string
	 */
	function getMarkup(MiddMedia_File_MediaInterface $file) {
		foreach ($file->getFormats() as $format) {
			if ($format->supportsRtmp())
				return $format->getRtmpUrl();
		}
		throw new InvalidArgumentException("Unsuported format.");
	}
	
	/**
	 * Checks to see if the file is supported
	 * by the particular embed code
	 * 
	 * @param MiddMedia_File_MediaInterface $file
	 * @return boolean
	 */
	function isSupported(MiddMedia_File_MediaInterface $file) {
		foreach ($file->getFormats() as $format) {
			if ($format->supportsRtmp())
				return true;
		}
		return false;
	}
	
}