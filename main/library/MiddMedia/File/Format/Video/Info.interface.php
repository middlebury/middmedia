<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../Image/Info.interface.php');
require_once(dirname(__FILE__).'/../Audio/Info.interface.php');

/**
 * An interface for all middmedia files.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_File_Format_Video_InfoInterface 
	extends MiddMedia_File_Format_Image_InfoInterface, MiddMedia_File_Format_Audio_InfoInterface
{
	
	/**
	 * Answer the video codec used.
	 * 
	 * @return string
	 */
	public function getVideoCodec ();
	
	/**
	 * Answer the frame rate of the video.
	 * 
	 * @return float
	 */
	public function getVideoFrameRate ();
	
}
