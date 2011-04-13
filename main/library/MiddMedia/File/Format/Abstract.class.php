<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../Format.interface.php');

/**
 * Source video files are of arbitrary video type.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
abstract class MiddMedia_File_Format_Abstract
	extends Harmoni_Filing_FileSystemFile
	implements MiddMedia_File_FormatInterface
{
		
	/*********************************************************
	 * Instance creation methods.
	 *********************************************************/
	
	/**
	 * Create a new empty file.
	 *
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_File_MediaInterface $mediaFile
	 * @param string $subdirectory
	 * @param string $extension
	 * @return void
	 */
	protected static function touch (MiddMedia_File_MediaInterface $mediaFile, $subdirectory, $extension) {
		$directory = $mediaFile->getDirectory();
		$dir = $directory->getPath().'/'.$subdirectory;
		if (!file_exists($dir)) {
			if (!is_writable($directory->getPath()))
				throw new ConfigurationErrorException($directory->getBaseName()." is not writable.");
			mkdir($dir);
		}
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$name = $pathInfo['filename'].'.'.$extension;
		touch($dir.'/'.$name);
		
		if (!file_exists($dir.'/'.$name))
			throw new OperationFailedException("Could not create ".$directory->getBaseName()."/".$subdirectory."/".$name);
	}
	
	/*********************************************************
	 * Abstract methods
	 *********************************************************/
	
	/**
	 * Answer the name of the subdirectory this format uses.
	 *
	 * @return string
	 */
	abstract protected function getTargetSubdir ();
	
	/**
	 * Answer the extension to use for this format.
	 *
	 * @return string
	 */
	abstract protected function getTargetExtension ();
	
	/*********************************************************
	 * Instance methods
	 *********************************************************/
	 
	/**
	 * @var MiddMedia_File_MediaInterface $mediaFile;  
	 */
	protected $mediaFile;
	
	/**
	 * Constructor.
	 * 
	 * @param MiddMedia_File_MediaInterface $mediaFile
	 * @param string $basename
	 * @return void
	 */
	public function __construct (MiddMedia_File_MediaInterface $mediaFile) {
		$this->mediaFile = $mediaFile;
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$this->basename = $pathInfo['filename'].'.'.$this->getTargetExtension();
		
		parent::__construct($mediaFile->getDirectory()->getPath().'/'.$this->getTargetSubdir().'/'.$this->basename);
	}
	
	/**
	 * Answer the full http path (URI) of this file.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		if (!$this->supportsHttp())
			throw new OperationFailedException('supportsHttp() is false');
		
		return $this->mediaFile->getDirectory()->getHttpUrl().'/'.$this->getTargetSubdir().'/'.$this->getBaseName();
	}
	
	/**
	 * Answer the full RMTP path (URI) of this file
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		if (!$this->supportsRtmp())
			throw new OperationFailedException('supportsRtmp() is false');
		
		return $this->mediaFile->getDirectory()->getRtmpUrl().'/'.$this->getTargetSubdir().'/'.$this->getBaseName();
	}
	
	/**
	 * Move an uploaded file into our path.
	 * 
	 * @param string $sourcePath
	 * @return void
	 */
	public function moveInUploadedFile ($sourcePath) {
		if(!move_uploaded_file($sourcePath, $this->getPath()))
			throw new OperationFailedException("Could not move uploaded file $sourcePath to ".$this->getPath());
	}
	
	/**
	 * Move a file into our path.
	 * 
	 * @param string $sourcePath
	 * @return void
	 */
	public function moveInFile ($sourcePath) {
		if(!rename($sourcePath, $this->getPath()))
			throw new OperationFailedException("Could not move $sourcePath to ".$this->getPath());
	}
	
	/**
	 * Copy a file into our path.
	 * 
	 * @param string $sourcePath
	 * @return void
	 */
	public function copyInFile ($sourcePath) {
		if(!copy($sourcePath, $this->getPath()))
			throw new OperationFailedException("Could not copy $sourcePath to ".$this->getPath());
	}
}

?>