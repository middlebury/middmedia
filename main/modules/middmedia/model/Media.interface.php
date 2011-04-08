<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * An interface for all middmedia files.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
interface MiddMedia_File_MediaInterface
	extends Harmoni_Filing_FileInterface
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
	public static function nameValid ($name);
	
	/**
	 * Answer an array of allowed extensions
	 * 
	 * @return array
	 * @access public
	 * @since 9/24/09
	 * @static
	 */
	public static function getAllowedVideoTypes ();
	
	/**
	 * Answer video information
	 * 
	 * @param string $filePath
	 * @return array
	 * @access public
	 * @since 9/24/09
	 * @static
	 */
	public static function getVideoInfo ($filePath);
	
	/**
	 * Check the queue for items to process and start processing if needed.
	 * 
	 * @param object MiddMediaManagerMiddMediaManager $manager
	 * @return void
	 * @access public
	 * @since 9/25/09
	 * @static
	 */
	public static function checkQueue (MiddMediaManager $manager);
	
	/*********************************************************
	 * Instance creation methods.
	 *********************************************************/
	
	/**
	 * Create a new empty file in this directory. Similar to touch().
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param string $name
	 * @return object MiddMedia_File_MediaInterface The new file
	 */
	public static function create (MiddMedia_Directory $directory, $name);
	
	/**
	 * Get an existing file in a directory.
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file doesn't exist.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_Directory $directory
	 * @param string $name
	 * @return object MiddMedia_File_MediaInterface The new file
	 */
	public static function get (MiddMedia_Directory $directory, $name);
	
	/*********************************************************
	 * Instance Methods
	 *********************************************************/
	
	/**
	 * Move an uploaded file into our file and hand any conversion if needed.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 9/24/09
	 */
	public function moveInUploadedFile ($tempName);
	
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
	public function getCreator ();
	
	/**
	 * Answer the username of the creator
	 * 
	 * @return string
	 * @access public
	 * @since 1/14/09
	 */
	public function getCreatorUsername ();
	
	/**
	 * Set the creator of the file.
	 * 
	 * @param object Agent $creator
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function setCreator (Agent $creator);
	
	/**
	 * Answer our directory.
	 * 
	 * @return MiddMedia_Directory
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
	
}

?>