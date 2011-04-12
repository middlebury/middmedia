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
	extends MiddMedia_AbstractAction
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
	function buildContent () {
		$actionRows = $this->getActionRows();
		
		ob_start();
		$harmoni = Harmoni::instance();
		
		$dir = $this->getDirectory();
		
		$actionRows->add(
				new Heading('Upload to: '.$dir->getBaseName(), 2), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
		
		print $this->getQuotaBar($dir);
		
		print "\n<div class='note'>";
		print "Maximum upload size: ";
		print ByteSize::withValue($this->getDirectoryUploadLimit($dir))->asString();
		print " (".$this->getDirectoryUploadLimit($dir)." bytes)";
		print "\n</div>";

		print "\n<div class=' upload_form_help'>";
		print $this->getUploadHelp();
		print "\n</div>";

		print "\n<form action='".$harmoni->request->quickURL('middmedia', 'upload_form_result', array('directory' => $dir->getBaseName()))."' method='post' enctype='multipart/form-data'>";
		
		print "\n\t<input type='hidden' name='MAX_FILE_SIZE' value='".$this->getDirectoryUploadLimit($dir)."'/>";
		print "\n\t<input type='file' name='Filedata' size='40'/>";
		print "\n\t<br/><input type='submit' value='upload'/>";
		
		print "\n</form>";
		
		
		print "\n<p><a href='".$harmoni->request->quickURL('middmedia', 'browse')."'>&laquo; Return to browsing</a></p>";
		
		$actionRows->add(
				new Block(ob_get_clean(), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
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
	 * @var object MiddMedia_Directory $directory;  
	 * @access private
	 * @since 11/19/08
	 */
	private $directory;
	
}

?>