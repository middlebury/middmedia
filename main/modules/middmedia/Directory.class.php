<?php
/**
 * @since 10/24/08
 * @package middmedia
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
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddMedia_Directory {

	/**
	 * Answer the directory if it exists. Throw an UnknownIdException if it doesn't.
	 * 
	 * @param object MiddMediaManagerMiddMediaManager $manager
	 * @param string $name
	 * @return object MiddMedia_Directory
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public static function getIfExists (MiddMediaManager $manager, $name) {
		$dir = new MiddMedia_Directory($manager, $name);
		
		if (!file_exists($dir->getFSPath())) {
			throw new UnknownIdException("Directory does not exist");
		}
		
		return $dir;
	}

	/**
	 * Answer the directory, creating if needed.
	 * 
	 * @param object MiddMediaManagerMiddMediaManager $manager
	 * @param string $name
	 * @return ovject MiddMedia_Directory
	 * @access public
	 * @since 11/13/08
	 * @static
	 */
	public static function getAlways (MiddMediaManager $manager, $name) {
		$dir = new MiddMedia_Directory($manager, $name);
		
		if (!file_exists($dir->getFSPath())) {
			if (!is_writable(MIDDMEDIA_FS_BASE_DIR))
				throw new ConfigurationErrorException("MIDDMEDIA_FS_BASE_DIR is not writable.");
			mkdir($dir->getFSPath());
		}
		
		return $dir;
	}
		
	/**
	 * Constructor
	 * 
	 * @param object MiddMediaManagerMiddMediaManager $manager
	 * @param string $name
	 * @return void
	 * @access protected
	 * @since 10/24/08
	 */
	protected function __construct (MiddMediaManager $manager, $name) {
		ArgumentValidator::validate($name, RegexValidatorRule::getRule('/^[a-zA-Z0-9_&-]+[a-zA-Z0-9_\.&-]*$/'));
		
		if (!file_exists(MIDDMEDIA_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDMEDIA_FS_BASE_DIR does not exist.");
		
		if (!is_dir(MIDDMEDIA_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDMEDIA_FS_BASE_DIR is not a directory.");
		
		if (!is_executable(MIDDMEDIA_FS_BASE_DIR))
			throw new ConfigurationErrorException("MIDDMEDIA_FS_BASE_DIR is not listable.");
		
		$this->manager = $manager;
		$this->name = $name;
	}
	
	/**
	 * @var object MiddMediaManager $manager;  
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
		return MIDDMEDIA_FS_BASE_DIR.'/'.$this->name;
	}
	
	/**
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return MIDDMEDIA_HTTP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		return MIDDMEDIA_RTMP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer an array of the files in this directory.
	 * 
	 * @return array of MiddMediaFile objects
	 * @access public
	 * @since 10/24/08
	 */
	public function getFiles () {
		$files = array();
		foreach (scandir($this->getFsPath()) as $fname) {
			if (!is_dir($this->getFsPath().'/'.$fname))
				$files[] = new MiddMedia_File($this, $fname);
		}
		return $files;
	}
	
	/**
	 * Answer a single file by name
	 * 
	 * @param string $name
	 * @return object MiddMedia_File
	 * @access public
	 * @since 11/13/08
	 */
	public function getFile ($name) {
		if (!$this->fileExists($name))
			throw new UnknownIdException("File '$name' does not exist.");
		return new MiddMedia_File($this, $name);
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
		if (!MiddMedia_File::nameValid($name))
			throw new InvalidArgumentException('Invalid file name \''.$name.'\'');
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
		return max(0, $this->getQuota() - $this->getBytesUsed());
	}
	
	/**
	 * Answer the quota size in bytes
	 * 
	 * @return int
	 * @access public
	 * @since 10/24/08
	 */
	public function getQuota () {
		if (!isset($this->quota)) {
			$dbMgr = Services::getService("DatabaseManager");
		
			$query = new SelectQuery;
			$query->addColumn('quota');
			$query->addTable('middmedia_quotas');
			$query->addWhereEqual('directory', $this->getBaseName());
			$result = $dbMgr->query($query, HARMONI_DB_INDEX);
			if ($result->getNumberOfRows()) {
				$quota = round(floatval($result->field('quota')));
				if ($quota > 0)
					$this->quota = $quota;
				else
					$this->quota = null;
			} else {
				$this->quota = null;
			}	
		}
		if (is_null($this->quota))
			return $this->getDefaultQuota();
		else
			return $this->quota;
	}
	
	/**
	 * Answer true if this directory has a custom quota
	 * 
	 * @return boolean
	 * @access public
	 * @since 12/10/08
	 */
	public function hasCustomQuota () {
		$this->getQuota();
		return !is_null($this->quota);
	}
	
	/**
	 * Set the quota of this directory in bytes.
	 * 
	 * @param int $quota
	 * @return void
	 * @access public
	 * @since 12/10/08
	 */
	public function setCustomQuota ($quota) {
		ArgumentValidator::validate($quota, IntegerValidatorRule::getRule());
		if ($quota < 1)
			throw new InvalidArgumentException("Invalid quota value $quota");
		
		$this->quota = $quota;
		
		$dbMgr = Services::getService("DatabaseManager");
		try {
			$query = new InsertQuery;
			$query->setTable('middmedia_quotas');
			$query->addValue('directory', $this->getBaseName());
			$query->addValue('quota', strval($quota));
			$dbMgr->query($query, HARMONI_DB_INDEX);
		} catch (DuplicateKeyDatabaseException $e) {
			$query = new UpdateQuery;
			$query->setTable('middmedia_quotas');
			$query->addWhereEqual('directory', $this->getBaseName());
			$query->addValue('quota', strval($quota));
			$dbMgr->query($query, HARMONI_DB_INDEX);
		}
	}
	
	/**
	 * Remove the custom quota
	 * 
	 * @return void
	 * @access public
	 * @since 12/10/08
	 */
	public function removeCustomQuota () {
		$this->quota = null;
		
		$dbMgr = Services::getService("DatabaseManager");
		
		$query = new DeleteQuery;
		$query->setTable('middmedia_quotas');
		$query->addWhereEqual('directory', $this->getBaseName());
		$dbMgr->query($query, HARMONI_DB_INDEX);
	}
	
	/**
	 * Answer the default quota for this directory
	 * 
	 * @return int
	 * @access public
	 * @since 12/10/08
	 */
	public function getDefaultQuota () {
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
	 * @return object MiddMediaFile The new file
	 * @access public
	 * @since 10/24/08
	 */
	public function addFile (Harmoni_Filing_FileInterface $file) {
		if (file_exists($this->getFsPath().'/'.$file->getBaseName()))
			throw new OperationFailedException("File already exists.");
		
		$newFile = new MiddMedia_File($this, $file->getBaseName());
		$newFile->setCreator($this->manager->getAgent());
		$newFile->putContents($file->getContents());
		
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
	 * @return object MiddMediaFile The new file
	 * @access public
	 * @since 11/21/08
	 */
	public function createFile ($name) {
		if (!MiddMedia_File::nameValid($name))
			throw new InvalidArgumentException("Invalid file name '$name'.");
		if ($this->fileExists($name))
			throw new OperationFailedException("File already exists.");
		
		touch($this->getFsPath().'/'.$name);
		$newFile = new MiddMedia_File($this, $name);
		$newFile->setCreator($this->manager->getAgent());
		
		return $newFile;
	}
	
	/**
	 * Create a file with content (and handle any conversion if necessary).
	 * 
	 * @param string $name
	 * @param string $content
	 * @return object MiddMediaFile The new file
	 * @access public
	 * @since 9/28/09
	 */
	public function createFileFromData ($name, $content) {
		if (!defined('MIDDMEDIA_TMP_DIR'))
			throw new ConfigurationErrorException('MIDDMEDIA_TMP_DIR is not defined');
		if (!is_dir(MIDDMEDIA_TMP_DIR))
			throw new ConfigurationErrorException('MIDDMEDIA_TMP_DIR does not exist.');
		if (!is_writable(MIDDMEDIA_TMP_DIR))
			throw new ConfigurationErrorException('MIDDMEDIA_TMP_DIR is not writable.');
			
		$tmpfile = tempnam(MIDDMEDIA_TMP_DIR, 'middmedia_soap_');
		file_put_contents($tmpfile, $content);
		
		$fileArray = array(
			'tmp_name'	=> $tmpfile,
			'name'		=> $name,
			'size'		=> filesize($tmpfile),
			'error'		=> 0,
		);
		return $this->createFileFromUpload($fileArray);
	}
	
	
	/**
	 * Create a file in this directory from an upload. Similar to move_uploaded_file().
	 * 
	 * @param array $fileArray The element of the $_FILES superglobal for this file.
	 * @return object MiddMediaFile The new file
	 * @access public
	 * @since 9/24/09
	 */
	public function createFileFromUpload (array $fileArray) {
		$uploadErrors = array(
			0=>"There is no error, the file uploaded with success",
			1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
			2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
			3=>"The uploaded file was only partially uploaded",
			4=>"No file was uploaded",
			6=>"Missing a temporary folder"
		);
		
		if (!isset($fileArray['tmp_name']) || !strlen($fileArray['tmp_name'])) 
			throw new InvalidArgumentException('Invalid file upload.');
		if (!isset($fileArray['name']) || !strlen($fileArray['name'])) 
			throw new InvalidArgumentException('Invalid file upload.');
		if (!isset($fileArray['size']) || !$fileArray['size']) 
			throw new InvalidArgumentException('Uploaded file is empty.');
		if ($fileArray['size'] > ($this->getBytesAvailable()))
			throw new InvalidArgumentException('File upload exceeds quota.');
		if (!isset($fileArray['error']) || $fileArray['error']) 
			throw new InvalidArgumentException('An error occurred with the file upload: '.$uploadErrors[$fileArray['error']]);
		
		$pathInfo = pathinfo($fileArray['name']);
		// PHP < 5.2.0 doesn't have 'filename'
		if (!isset($pathInfo['filename'])) {
			preg_match('/(.+)\.[a-z0-9]+/i', basename($fileArray['name']), $matches);
			$pathInfo['filename'] = $matches[1];
		}
		
		$valid_chars_regex = '.a-zA-Z0-9_ !@#$%^&()+={}\[\]\',~`-';	// Characters allowed in the file name (in a Regular Expression format)
		$basename = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", $pathInfo['filename']);
		$extension = strtolower(preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', "", $pathInfo['extension']));
		$targetExtension = MiddMedia_File::getTargetExtension($extension);
		
		$MAX_FILENAME_LENGTH = 260;
		if (strlen($basename) == 0 || strlen($basename) > $MAX_FILENAME_LENGTH) {
			throw new InvalidArgumentException("Invalid file name, '".$basename."'");
		}
		
		$file = $this->createFile($basename.'.'.$targetExtension);
		$file->moveInUploadedFileForProcessing($fileArray['tmp_name']);
		
		return $file;
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
	
	/**
	 * Answer the current Manager.
	 * 
	 * WARNING: This method should only be used by the File object in this package.
	 * 
	 * @return MiddMediaManager $manager
	 * @access public
	 * @since 2/2/09
	 */
	public function getManager () {
		return $this->manager;
	}
}

?>