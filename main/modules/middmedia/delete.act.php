<?php
/**
 * @since 11/19/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/upload.act.php');

/**
 * Delete a file
 * 
 * @since 11/19/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class deleteAction
	extends UploadAction
{
		
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/13/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->error("Permission denied");		
		
		$dir = $this->getDirectory();
		$file_name = RequestContext::value('file');
		if (!$dir->fileExists($file_name))
			$this->error("File '".$dir->getBaseName().'/'.$file_name."' does not exist.");
		
		$file = $dir->getFile($file_name);
		
		if (!$file->isWritable())
			$this->error("File '".$dir->getBaseName().'/'.$file->getBaseName()."'  is not writable.");
		
		$file->delete();
		
		// Return output to the browser (only supported by SWFUpload for Flash Player 9)
		header("HTTP/1.1 200 OK");
		echo "File Deleted";
		exit;
	}
	
}

?>