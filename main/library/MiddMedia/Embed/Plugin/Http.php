<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * Class for the embed code used for
 * showing the HTTP link to the video.  
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_Http 
	implements MiddMedia_Embed_Plugin 
{
  
 /**
   * Gets the title of the embed code
   * 
   * @return string
   */
  function getTitle() {
    return 'HTTP (Streaming) URL';
  }
  
 /**
   * Gets the description for the embed code
   * 
   * @param MiddMedia_File_MediaInterface $file
   * @return string
   */
  function getDesc(MiddMedia_File_MediaInterface $file) {
    return "\n<p><a href='" . $file->getPrimaryFormat()->getHttpUrl() . "'>Click here to download this file.</a></p>
    \n<p>Make a link to the following URL to allow downloads of this file.</p>";
  }
  
 /**
   * Gets the embed code markup
   * 
   * @param MiddMedia_File_MediaInterface $file
   * @return string
   */
  function getMarkup(MiddMedia_File_MediaInterface $file) {
    return "\n<input type='text' size='110' value='". $file->getPrimaryFormat()->getHttpUrl() . "' />";
  }
  
  /**
   * Checks to see if the file is supported
   * by the particular embed code
   * 
   * @param MiddMedia_File_MediaInterface $file
   * @return boolean
   */
  function isSupported(MiddMedia_File_MediaInterface $file) {
    return true;
  }
  
}