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
		$dir = $directory->getFsPath().'/'.$subdirectory;
		if (!file_exists($dir)) {
			if (!is_writable($directory->getFsPath()))
				throw new ConfigurationErrorException($directory->getBaseName()." is not writable.");
			mkdir($dir);
		}
		
		$pathInfo = pathinfo($mediaFile->getBaseName());
		$name = $pathInfo['filename'].'.'.$extension;
		touch($dir.'/'.$name);
		
		if (!file_exists($dir.'/'.$name))
			throw new OperationFailedException("Could not create ".$directory->getBaseName()."/".$subdirectory."/".$name);
	}
}

?>