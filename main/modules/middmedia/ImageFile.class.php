<?php
/**
 * @since 1/30/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');

/**
 * An image file for thumbnails and other images.
 * 
 * @since 1/30/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddMedia_ImageFile
	extends Harmoni_Filing_FileSystemFile
{
	
	/**
	 * Answer the file-system path for an image that matches the directory, file, and type
	 * 
	 * @param MiddMedia_Directory $directory
	 * @param MiddMedia_File $file
	 * @param string $type
	 * @return string
	 * @access public
	 * @since 1/30/09
	 * @static
	 */
	public static function getFsPathForImage (MiddMedia_Directory $directory, MiddMedia_File $file, $type) {
		if ($type != 'thumb' && $type != 'splash' && $type != 'full_frame')
			throw new InvalidArgumentException("Unknown image type, $type");
		$parts = pathinfo($file->getBaseName());
		return $directory->getFSPath().'/'.$type.'/'.$parts['filename'].'.jpg';
	}
	
	/**
	 * Constructor.
	 * 
	 * @param MiddMedia_Directory $directory
	 * @param MiddMedia_File $file
	 * @param string $type
	 * @return void
	 * @access public
	 * @since 10/24/08
	 */
	public function __construct (MiddMedia_Directory $directory, MiddMedia_File $file, $type) {
		$this->directory = $directory;
		$this->file = $file;
		$this->type = $type;

		parent::__construct(self::getFsPathForImage($directory, $file, $type));
	}
	
	/**
	 * Answer the full file-system path of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getFsPath () {
		return $this->getPath();
	}
	
	/**
	 * Answer the full http path (URI) of this file
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return $this->directory->getHttpUrl().'/'.$this->type.'/'.rawurlencode($this->getBaseName());
	}
	
	/**
	 * Answer the full http path (URI) of this file
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getUrl () {
		return $this->getHttpUrl();
	}
	
}

?>