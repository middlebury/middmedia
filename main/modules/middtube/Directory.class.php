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
	 * Answer the directory if it exists. Throw an UnknownIdException if it doesn't.
	 * 
	 * @param object MiddTubeManagerMiddTubeManager $manager
	 * @param string $name
	 * @return object MiddTube_Directory
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public static function getIfExists (MiddTubeManager $manager, $name) {
		$dir = new MiddTube_Directory($manager, $name);
		
		if (!file_exists($dir->getFSPath())) {
			throw new UnknownIdException("Directory does not exist");
		}
		
		return $dir;
	}

	/**
	 * Answer the directory, creating if needed.
	 * 
	 * @param object MiddTubeManagerMiddTubeManager $manager
	 * @param string $name
	 * @return ovject MiddTube_Directory
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public static function getAlways (MiddTubeManager $manager, $name) {
		$dir = new MiddTube_Directory($manager, $name);
		
		if (!file_exists($dir->getFSPath())) {
			if (!is_writable(MIDDTUBE_FS_BASE_DIR))
				throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not writable.");
			mkdir($dir->getFSPath());
		}
		
		return $dir;
	}
		
	/**
	 * Constructor
	 * 
	 * @param object MiddTubeManagerMiddTubeManager $manager
	 * @param string $name
	 * @return void
	 * @access public
	 * @since 10/24/08
	 */
	private function __construct (MiddTubeManager $manager, $name) {
		ArgumentValidator::validate($name, RegexValidatorRule::getRule('^[a-zA-Z0-9_&-]+$'));
		
		if (!file_exists(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR does not exist.");
		
		if (!is_dir(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not a directory.");
		
		if (!is_executable(MIDDTUBE_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDTUBE_FS_BASE_DIR is not listable.");
		
		$this->manager = $manager;
		$this->name = $name;
	}
	
	/**
	 * @var object MiddTubeManager $manager;  
	 * @access private
	 * @since 11/21/08
	 */
	private $manager;
	
	/**
	 * @var string $name;  
	 * @access private
	 * @since 11/21/08
	 */
	private $name;
	
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
	 * Answer a single file by name
	 * 
	 * @param string $name
	 * @return object Middtube_File
	 * @access public
	 * @since 11/13/08
	 */
	public function getFile ($name) {
		if (!$this->fileExists($name))
			throw new UnknownIdException("File '$name' does not exist.");
		return new MiddTube_File($this, $name);
	}
	
	/**
	 * Answer true if the filename passed exists in this directory
	 * 
	 * @param string $name
	 * @return boolean
	 * @access public
	 * @since 11/13/08
	 */
	public function fileExists ($name) {
		if (!MiddTube_File::nameValid($name))
			throw new InvalidArgumentException('Invalid file name '.$name);
		return file_exists($this->getFsPath().'/'.$name);
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
		return $this->manager->getDefaultQuota();
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
		$newFile->setCreator($this->manager->getAgent());
		
		return $newFile;
	}
	
	/**
	 * Create a new empty file in this directory. Similar to touch().
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param string $name
	 * @return object MiddTubeFile The new file
	 * @access public
	 * @since 11/21/08
	 */
	public function createFile ($name) {
		if (!MiddTube_File::nameValid($name))
			throw new InvalidArgumentException("Invalid file name");
		if ($this->fileExists($name))
			throw new OperationFailedException("File already exists.");
		
		touch($this->getFsPath().'/'.$name);
		$newFile = new MiddTube_File($this, $name);
		$newFile->setCreator($this->manager->getAgent());
		
		return $newFile;
	}
	
	/**
	 * Answer true if the file is readable
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/19/08
	 */
	public function isReadable () {
		return is_readable($this->getFsPath());
	}
	
	/**
	 * Answer true if the file is writable
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/19/08
	 */
	public function isWritable () {
		return is_writeable($this->getFsPath());
	}
	
	/**
	 * Answer true if the file is executable
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/19/08
	 */
	public function isExecutable () {
		return is_executable($this->getFsPath());
	}
}

?>