<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

/**
 * This class is a simple directory-access wrapper.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Directory 
	implements MiddMedia_DirectoryInterface
{
	/*********************************************************
	 * Static methods
	 *********************************************************/

	/**
	 * Answer the directory if it exists. Throw an UnknownIdException if it doesn't.
	 * 
	 * @param object MiddMedia_Manager $manager
	 * @param string $name
	 * @return object MiddMedia_DirectoryInterface
	 */
	public static function getIfExists (MiddMedia_Manager $manager, $name) {
		$dir = new MiddMedia_Directory($manager, $name);
		
		if (!file_exists($dir->getPath())) {
			throw new UnknownIdException("Directory does not exist");
		}
		
		return $dir;
	}

	/**
	 * Answer the directory, creating if needed.
	 * 
	 * @param object MiddMedia_Manager $manager
	 * @param string $name
	 * @return ovject MiddMedia_Directory
	 * @static
	 */
	public static function getAlways (MiddMedia_Manager $manager, $name) {
		$dir = new MiddMedia_Directory($manager, $name);
		
		if (!file_exists($dir->getPath())) {
			if (!is_writable(MIDDMEDIA_FS_BASE_DIR))
				throw new ConfigurationErrorException("MIDDMEDIA_FS_BASE_DIR is not writable.");
			mkdir($dir->getPath());
		}
		
		return $dir;
	}
		
	/**
	 * Constructor
	 * 
	 * @param object MiddMedia_Manager $manager
	 * @param string $name
	 * @return void
	 * @access protected
	 * @since 10/24/08
	 */
	protected function __construct (MiddMedia_Manager $manager, $name) {
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
	 * @var object MiddMedia_Manager $manager;  
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
	 */
	public function getBaseName () {
		return $this->name;
	}
	
	/**
	 * [Re]Set the base name for the directory
	 * 
	 * @param string $baseName
	 * @return null
	 */
	public function setBaseName ($baseName) {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer the full file-system path of this directory
	 * 
	 * @return string
	 */
	public function getPath () {
		return MIDDMEDIA_FS_BASE_DIR.'/'.$this->name;
	}
	
	/**
	 * [Re]Set a full path to the directory, including the directory name.
	 * 
	 * @param string $path
	 * @return null
	 */
	public function setPath ($path) {
		throw new UnimplementedException();
	}
	
	/**
	 * Delete the directory.
	 * 
	 * @param boolean $recursive
	 * @return null
	 */
	public function delete ($recursive) {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer the modification date/time
	 * 
	 * @return object DateAndTime
	 */
	public function getModificationDate () {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 */
	public function getHttpUrl () {
		return MIDDMEDIA_HTTP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 */
	public function getRtmpUrl () {
		return MIDDMEDIA_RTMP_BASE_URL.'/'.$this->name;
	}
	
	/**
	 * Answer an array of the files in this directory.
	 * 
	 * @return array of MiddMedia_File_Media objects
	 */
	public function getFiles () {
		$files = array();
		foreach (scandir($this->getPath()) as $fname) {
			if (!is_dir($this->getPath().'/'.$fname)) {
				// Skip FLV files, they are just in place to support legacy URLs.
				$info = pathinfo($fname);
				if ($info['extension'] != 'flv')
					$files[] = new MiddMedia_File_Media($this, $fname);
			}
		}
		return $files;
	}
	
	/**
	 * Answer a single file by name
	 * 
	 * @param string $name
	 * @return object MiddMedia_File_Media
	 */
	public function getFile ($name) {
		if (!$this->fileExists($name))
			throw new UnknownIdException("File '$name' does not exist.");
		return new MiddMedia_File_Media($this, $name);
	}
	
	/**
	 * Answer true if the filename passed exists in this directory
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function fileExists ($name) {
		if (!MiddMedia_File_Media::nameValid($name))
			throw new InvalidArgumentException('Invalid file name \''.$name.'\'');
		return file_exists($this->getPath().'/'.$name);
	}
	
	/**
	 * Answer the number of bytes used.
	 * 
	 * @return int
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
	 */
	public function getBytesAvailable () {
		return max(0, $this->getQuota() - $this->getBytesUsed());
	}
	
	/**
	 * Answer the quota size in bytes
	 * 
	 * @return int
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
	 */
	public function addFile (Harmoni_Filing_FileInterface $file) {
		if (file_exists($this->getPath().'/'.$file->getBaseName()))
			throw new OperationFailedException("File already exists.");
		
		$newFile = $this->createFile($file->getBaseName());
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
	 * @return object Harmoni_Filing_FileInterface The new file
	 */
	public function createFile ($name) {
		if ($this->fileExists($name))
			throw new OperationFailedException("File already exists.");
		  
		$media = MiddMedia_File_Media::create($this, $name);
		//set quality
		$media->setQuality($this->getQuality());
		return $media;
	}
	
	/**
	 * Create a file with content (and handle any conversion if necessary).
	 * 
	 * @param string $name
	 * @param string $content
	 * @return object MiddMedia_File_Media The new file
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
		$size = strlen($content);
		unset($content);
		
		if (!strlen($name)) 
			throw new InvalidArgumentException('Invalid file upload.');
		if (!$size) 
			throw new InvalidArgumentException('Uploaded file is empty.');
		if ($size > ($this->getBytesAvailable()))
			throw new InvalidArgumentException('File upload exceeds quota.');
		
		$mediaFile = $this->createFile($name);
		$mediaFile->moveInFile($tmpfile);
		
		return $mediaFile;
	}
	
	
	/**
	 * Create a file in this directory from an upload. Similar to move_uploaded_file().
	 * 
	 * @param array $fileArray The element of the $_FILES superglobal for this file.
	 * @return object MiddMedia_File_Media The new file
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
		
		$mediaFile = $this->createFile($fileArray['name']);
		$mediaFile->moveInUploadedFile($fileArray['tmp_name']);
		
		return $mediaFile;
	}
	
	/**
	 * Answer true if the file is readable
	 * 
	 * @return boolean
	 */
	public function isReadable () {
		return is_readable($this->getPath());
	}
	
	/**
	 * Answer true if the file is writable
	 * 
	 * @return boolean
	 */
	public function isWritable () {
		return is_writeable($this->getPath());
	}
	
	/**
	 * Answer true if the file is executable
	 * 
	 * @return boolean
	 */
	public function isExecutable () {
		return is_executable($this->getPath());
	}
	
	/**
	 * Answer the current Manager.
	 * 
	 * WARNING: This method should only be used by the File object in this package.
	 * 
	 * @return MiddMedia_Manager $manager
	 */
	public function getManager () {
		return $this->manager;
	}
	
	/**
	 * Video quality per directory.
	 * 
	 */
	private $quality;
	
	/**
	 * Set video quality for the directory in the session
	 * 
	 * @return void
	 */
	public function setQuality ($quality) {
		$valid_qualities = MiddMedia_File_Media::getQualities();
	  if (in_array($quality, $valid_qualities)) {
	    $this->quality = $quality;
	  }
	  else {
	    throw new OperationFailedException("Quality is not on the list of valid qualities.");
	  }
	}
	
	/**
	 * Get video quality for the directory in the session
	 * 
	 * @return string
	 */
	public function getQuality () {
		$valid_qualities = MiddMedia_File_Media::getQualities();
	  if (isset($this->quality)) {
	      $quality = $this->quality; 
	  }
	  else {
	      $quality = MiddMedia_File_Media::getDefaultQuality();
	  }
	  
	  if (in_array($quality, $valid_qualities)) {  
	    return $quality;
	  }
	  else {
	    throw new OperationFailedException("Quality is not on the list of valid qualities.");
	  }
	}
	
}

?>