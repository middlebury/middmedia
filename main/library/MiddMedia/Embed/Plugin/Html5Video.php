<?php
/**
 * @copyright Copyright &copy; 2017, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * Class for the embed code used for the files via HTML5 Video elements.
 *
 * @copyright Copyright &copy; 2017, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_Html5Video
	implements MiddMedia_Embed_Plugin
{

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct () {
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
		$mp4_httpUrl = $mp4->getHttpUrl();
		$webm = $file->getFormat('webm');
		$webmUrl = $webm->getHttpUrl();

		$fileId = rawurlencode($file->getFormat('mp4')->getBaseName());
		$splash = rawurlencode($file->getFormat('splash')->getHttpUrl());

		return '<video width="400" height="300" poster="'. $splash .'" controls>
<source src="'.$mp4_httpUrl.'" type="video/mp4" />
<source src="'.$webmUrl.'" type="video/webm" />
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
