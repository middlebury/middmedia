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
	 * Answer true if the file name is valid, false otherwise
	 * 
	 * @param string $name
	 * @return boolean
	 * @access public
	 * @since 11/19/08
	 * @static
	 */
	public static function nameValid ($name) {
		return preg_match('/^[a-z0-9_+=,.?#@%^!~\'"&\[\]{}()<> -]+$/i', $name);
	}
	
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
		if (!self::nameValid($basename))
			throw new InvalidArgumentException('Invalid file name '.$basename);

		parent::__construct($directory->getFSPath().'/'.$basename);
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
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return $this->directory->getHttpUrl().'/'.rawurlencode($this->getBaseName());
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		return $this->directory->getRtmpUrl().'/'.rawurlencode($this->getBaseName());
	}
	
	/**
	 * Answer the Agent that created this file.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If no creator is listed or can be returned.
	 *		UnimplementedException 		- If this method is not available yet.
	 * 
	 * @return object Agent
	 * @access public
	 * @since 10/24/08
	 */
	public function getCreator () {
		throw new UnimplementedException();
	}
}

?>