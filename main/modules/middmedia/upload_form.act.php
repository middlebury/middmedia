<?php
/**
 * @since 11/13/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * HTML form for file upload
 * 
 * @since 11/13/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class upload_formAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/13/08
	 */
	public function isAuthorizedToExecute () {
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		if (!$authN->isUserAuthenticatedWithAnyType())
			return false;
		try {
			$dir = $this->getDirectory();
		} catch (PermissionDeniedException $e) {
			return false;
		}
		return true;
	}
	
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/13/08
	 */
	public function execute () {		
		if (!$this->isAuthorizedToExecute())
			return "Permission denied";
		
		ob_start();
		$harmoni = Harmoni::instance();
		
		$dir = $this->getDirectory();
		
		print "\n<form action='".$harmoni->request->quickURL('middmedia', 'upload', array('directory' => $dir->getBaseName()))."' method='post' enctype='multipart/form-data'>";
		
		print "\n\t<input type='file' name='Filedata' size='40'/>";
		print "\n\t<br/><input type='submit' value='upload'/>";
		
		print "\n</form>";
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the target directory object
	 * 
	 * @return object MiddMedia_Directory
	 * @access protected
	 * @since 11/19/08
	 */
	protected function getDirectory () {
		if (!isset($this->directory)) {
			$manager = $this->getManager();
			$this->directory = $manager->getDirectory(RequestContext::value('directory'));
		}
		
		return $this->directory;
	}
	
	/**
	 * Answer the manager to use
	 * 
	 * @return MiddMediaManager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMediaManager::forCurrentUser();
	}
	
	/**
	 * @var object MiddMedia_Directory $directory;  
	 * @access private
	 * @since 11/19/08
	 */
	private $directory;
	
	/**
	 * Answer the file size limit
	 * 
	 * @return int
	 * @access protected
	 * @since 11/13/08
	 */
	protected function getFileSizeLimit () {
		return min(
					ByteSize::fromString(ini_get('post_max_size'))->value(),
					ByteSize::fromString(ini_get('upload_max_filesize'))->value(),
					ByteSize::fromString(ini_get('memory_limit'))->value()
				);
	}
	
}

?>