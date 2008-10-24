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

require_once(dirname(__FILE__).'/File.class.php');

/**
 * This class is a simple directory-access wrapper.
 * 
 * @since 10/24/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddTube_Directory {
		
	/**
	 * Constructor
	 * 
	 * @param string $name
	 * @return void
	 * @access public
	 * @since 10/24/08
	 */
	public function __construct ($name) {
		ArgumentValidator::validate($name, RegexValidatorRule::getRule('^[a-zA-Z0-9_-]+$'));
		
		if (!file_exists(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR does not exist.");
		
		if (!is_dir(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not a directory.");
		
		if (!is_executable(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not listable.");
		
		$this->name = $name;
		
		// @todo check Authorization
		
		if (!file_exists($this->getFSPath())) {
			if (!is_writable(MIDDTUBE_FS_BASE_DIR))
				throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not writable.");
			mkdir($this->getFSPath());
		}
	}
	
	/**
	 * Answer the name of the directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getBaseName () {
		return $this->name;
	}
	
	/**
	 * Answer the full file-system path of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getFsPath () {
		return MIDDTUBE_FS_BASE_DIR.'/'.$this->name;
	}
	
	/**
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return MIDDTUBE_HTTP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		return MIDDTUBE_RTMP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer an array of the files in this directory.
	 * 
	 * @return array of MiddTubeFile objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getFiles () {
		$files = array();
		foreach (scandir($this->getFsPath()) as $fname) {
			if ($fname != '.' && $fname != '..')
				$files[] = new MiddTube_File($this, $fname);
		}
		return $files;
	}
	
	/**
	 * Answer the number of bytes used.
	 * 
	 * @return int
	 * @access public
	 * @since 10/24/08
	 */
	public function getBytesUsed () {
		$used = 0;
		foreach ($this->getFiles() as $file)
			$used = $used + $file->getSize();
		return $used;
	}
	
	/**
	 * Answer the number of bytes availible before a quota is reached.
	 * 
	 * @return int
	 * @access public
	 * @since 10/24/08
	 */
	public function getBytesAvailable () {
		return $this->getQuota() - $this->getBytesUsed();
	}
	
	/**
	 * Answer the quota size in bytes
	 * 
	 * @return int
	 * @access public
	 * @since 10/24/08
	 */
	public function getQuota () {
		// @todo Implement per-directory quotas.
		return 500 * 1024 * 1024;
	}
	
	/**
	 * Add a file to this directory
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param object Harmoni_Filing_FileInterface $file
	 * @return object MiddTubeFile The new file
	 * @access public
	 * @since 10/24/08
	 */
	public function addFile (Harmoni_Filing_FileInterface $file) {
		if (file_exists($this->getFsPath().'/'.$file->getBaseName()))
			throw new OperationFailedException("File already exists.");
		
		$newFile = new MiddTube_File($this, $file->getBaseName());
		$newFile->putContents($file->getContents());
		
		return $newFile;
	}
}

?>