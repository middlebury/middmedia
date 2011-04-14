<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/../FileInterface.php');

/**
 * An interface for all middmedia files.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_File_MediaInterface
	extends MiddMedia_File_FileInterface
{
	
	/*********************************************************
	 * Class Methods
	 *********************************************************/
	
	/**
	 * Answer true if the file name is valid, false otherwise
	 * 
	 * @param string $name
	 * @return boolean
	 * @static
	 */
	public static function nameValid ($name);
	
	/**
	 * Answer an array of allowed extensions
	 * 
	 * @return array
	 * @static
	 */
	public static function getAllowedVideoTypes ();
	
	/**
	 * Check the queue for items to process and start processing if needed.
	 * 
	 * @param object MiddMedia_Manager $manager
	 * @return void
	 * @static
	 */
	public static function checkQueue (MiddMedia_Manager $manager);
	
	/*********************************************************
	 * Instance Creation Methods
	 *********************************************************/
	
	/**
	 * Create a new empty file in this directory. Similar to touch().
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_DirectoryInterface $directory
	 * @param string $name
	 * @return object MiddMedia_File_MediaInterface The new file
	 */
	public static function create (MiddMedia_DirectoryInterface $directory, $name);
	
	/*********************************************************
	 * Instance Methods
	 *********************************************************/

	/**
	 * Answer the Agent that created this file.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If no creator is listed or can be returned.
	 *		UnimplementedException 		- If this method is not available yet.
	 * 
	 * @return object Agent
	 */
	public function getCreator ();
	
	/**
	 * Answer the username of the creator
	 * 
	 * @return string
	 */
	public function getCreatorUsername ();
	
	/**
	 * Set the creator of the file.
	 * 
	 * @param object Agent $creator
	 * @return void
	 */
	public function setCreator (Agent $creator);
	
	/**
	 * Answer our directory.
	 * 
	 * @return MiddMedia_DirectoryInterface
	 */
	public function getDirectory ();
	
	/**
	 * Answer a format of this media file
	 * 
	 * @param string $format
	 * @return MiddMedia_File_FormatInterface
	 */
	public function getFormat ($format);
	
	/**
	 * Answer the primary format of this media file
	 * 
	 * @return MiddMedia_File_FormatInterface
	 */
	public function getPrimaryFormat ();
	
	/**
	 * Answer embed code that can be used for this file. 
	 * This is an example, other players will work as well.
	 * 
	 * @return string
	 */
	public function getEmbedCode ();
	
}

?>