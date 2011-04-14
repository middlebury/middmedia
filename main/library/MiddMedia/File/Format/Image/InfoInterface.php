<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * An interface for all middmedia files.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_File_Format_Image_InfoInterface {
	
	/**
	 * Answer the width of the image in pixels.
	 * 
	 * @return int
	 */
	public function getWidth ();
	
	/**
	 * Answer the height of the image in pixels.
	 * 
	 * @return int
	 */
	public function getHeight ();
	
}
