<?php
/**
 * @since 7/24/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * This is an unauthenticated file that allows only read-only operation.
 * 
 * @since 7/24/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_File_UnauthenticatedMedia
	extends MiddMedia_File_Media
{
	/**
	 * Create a new empty file in this directory. Similar to touch().
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param string $name
	 * @return object MiddMedia_MediaFile The new file
	 */
	public static function create (MiddMedia_DirectoryInterface $directory, $name) {
		throw new PermissionDeniedException("The UnauthenticatedFiles cannot be changed.");
	}
		
	/**
	 * Set the contents of the file
	 * 
	 * @param string $contents
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function setContents ($contents) {
		throw new PermissionDeniedException("The UnauthenticatedFiles cannot be changed.");
	}
	
	/**
	 * Move an uploaded file into this file.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function moveInUploadedFile ($tempName) {
		throw new PermissionDeniedException("The UnauthenticatedFiles cannot be changed.");
	}
	
	/**
	 * Delete the file.
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function delete () {
		throw new PermissionDeniedException("The UnauthenticatedFiles cannot be changed.");
	}
	
	/**
	 * Set the creator of the file.
	 * 
	 * @param object Agent $creator
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function setCreator (Agent $creator) {
		throw new PermissionDeniedException("The UnauthenticatedFiles cannot be changed.");
	}
	
}

?>