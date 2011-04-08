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
class EmbedPlugin_RTMP implements EmbedPlugin {

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
   * @param MiddMedia_File $file
   * @return string
   */
  function getDesc(MiddMedia_File $file) {
    return "\n<p>The following URL may be used in custom Flash video players to stream this video.</p>";
  }

 /**
   * Gets the embed code markup
   * 
   * @param MiddMedia_File $file
   * @return string
   */
  function getMarkup(MiddMedia_File $file) {
    return "\n<input type='text' size='110' value='" . $file->getRtmpUrl() . "' />";
  }
  
  /**
   * Checks to see if the file is supported
   * by the particular embed code
   * 
   * @param MiddMedia_File $file
   * @return boolean
   */
  function isSupported(MiddMedia_File $file) {
    return true;
  }
  
}