<?php
/**
 * @since 10/24/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');

/**
 * This class is a basic wrapper around a file
 * 
 * @since 10/24/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddTube_File
	extends Harmoni_Filing_FileSystemFile
{
	
	/**
	 * Constructor.
	 * 
	 * @param object MiddTube_Directory $directory
	 * @param string $basename
	 * @return void
	 * @access public
	 * @since 10/24/08
	 */
	public function __construct (MiddTube_Directory $directory, $basename) {
		$this->directory = $directory;
		parent::__construct($directory->getFSPath().'/'.$basename);
	}
	
	/**
	 * Answer the full file-system path of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getFSPath () {
		return $this->getPath();
	}
	
	/**
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHTTPPath () {
		return $this->directory->getHTTPPath().'/'.$this->getBaseName();
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRTMPPath () {
		return $this->directory->getRTMPPath().'/'.$this->getBaseName();
	}
	
}

?>