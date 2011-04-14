<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * Interface for the embed plugins that hold the
 * embed code for the video files on Middmedia.
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface EmbedPlugin {
  
 /**
   * Gets the title of the embed code
   * 
   * @return string
   */
  function getTitle();
  
 /**
   * Gets the description for the embed code
   * 
   * @param MiddMedia_File $file
   * @return string
   */
  function getDesc(MiddMedia_File $file);
  
 /**
   * Gets the embed code markup
   * 
   * @param MiddMedia_File $file
   * @return string
   */
  function getMarkup(MiddMedia_File $file);
  
/**
  * Checks to see if the file is supported
  * by the particular embed code
  * 
  * @param MiddMedia_File $file
  * @return boolean
  */
  function isSupported(MiddMedia_File $file);
  
}