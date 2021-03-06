<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * Class for the short embed code used on the Middlebury and MIIS Drupal sites.
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_Drupal
	implements MiddMedia_Embed_Plugin
{

	/**
	 * Gets the title of the embed code
	 *
	 * @return string
	 */
	function getTitle() {
		return 'Drupal Page Embed Code';
	}
	
	/**
	 * Gets the description for the embed code
	 *
	 * @param MiddMedia_File $file
	 * @return string
	 */
	function getDesc(MiddMedia_File_MediaInterface $file) {
		return "\n<p>The syntax for inserting videos is:[video:URL width:value height:value align:value autoplay:value autorewind:value loop:value image:URL]. The video URL is the address of the site you found the video on. Accepted values for width and height are numbers. Accepted values for align are left and right. Accepted values for autoplay, autorewind and loop are 0 (false) and 1 (true). The image URL is used to change the 'splash image' or the image show in the player when the video is not playing. Other than the video URL, all attributes are optional.</p>";
	}

	/**
	 * Gets the embed code markup
	 *
	 * @param MiddMedia_File $file
	 * @return string
	 */
	function getMarkup(MiddMedia_File_MediaInterface $file) {
		return "[video:" . $file->getPrimaryFormat()->getHttpUrl() . "]";
	}

	function isSupported(MiddMedia_File_MediaInterface $file) {
		return true;
	}

}
