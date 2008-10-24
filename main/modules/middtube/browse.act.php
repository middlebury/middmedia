<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 adamfranco Exp $
 */
class browseAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Ensure that the user is logged in.
		// Authorization checks will be done on a per-directory basis when printing.
		$authN = Services::getService("AuthN");
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Browse Your Videos");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows = $this->getActionRows();
		
		$manager = MiddTubeManager::forCurrentUser();
		
		// Get the personal directory
		$dir = $manager->getPersonalDirectory();
		$actionRows->add(
			new Heading($dir->getBaseName()." ("._("Personal").")", 2), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
		$actionRows->add(
			new Block($this->getDirectoryMarkup($dir), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
			
		// Get the shared directories
		foreach ($manager->getSharedDirectories() as $dir) {
			$actionRows->add(
				new Heading($dir->getBaseName()." ("._("Shared").")", 2), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
			$actionRows->add(
				new Block($this->getDirectoryMarkup($dir), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
		}
	}
	
	/**
	 * Answer a block of HTML to represent the directory
	 * 
	 * @param object MiddTube_Directory $dir
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getDirectoryMarkup (MiddTube_Directory $dir) {
		ob_start();
		
		/*********************************************************
		 * Quota bar
		 *********************************************************/
		
		print "\n<div class='quota_bar'>";
		
		$percent = ceil(100 * ($dir->getBytesUsed() / $dir->getQuota()));
		print "\n\t<div class='used' style='width: ".$percent."%;'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesUsed());
		print "\n\t<div class='used_label'>"._("Used: ").$size->asString()."</div>";
		
		$percent = floor(100 * ($dir->getBytesAvailable() / $dir->getQuota()));
		print "\n\t<div class='free' style='width: ".$percent."%;'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesAvailable());
		print "\n\t<div class='free_label'>"._("Free: ").$size->asString()."</div>";
		
		print "\n</div>";
		
		/*********************************************************
		 * Upload Form
		 *********************************************************/
		print "\n<div class='middtube_upload'>";
		print "\n\t<button onclick=\"alert('Unimplemented');\">Upload New File</button>";
		print "\n</div>";
		
		/*********************************************************
		 * File Listing
		 *********************************************************/
		print "\n<table class='file_listing_table'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"._("Name")."</th>";
		print "\n\t\t\t<th>"._("Type")."</th>";
		print "\n\t\t\t<th>"._("Size")."</th>";
		print "\n\t\t\t<th>"._("Date")."</th>";
		print "\n\t\t\t<th>"._("Creator")."</th>";
		print "\n\t\t\t<th>"._("Access")."</th>";
		print "\n\t\t\t<th>"._("Operations")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		foreach ($dir->getFiles() as $file) {
			print "\n\t\t<tr>";
			
			print "\n\t\t\t<td>".$file->getBaseName()."</td>";
			
			print "\n\t\t\t<td>".$file->getMimeType()."</td>";
			
			print "\n\t\t\t<td>";
			$size = ByteSize::withValue($file->getSize());
			print $file->getSize();
			print " (".$size->asString().")";
			print "</td>";
			
			print "\n\t\t\t<td>".$file->getModificationDate()->asLocal()->asString()."</td>";
			
			print "\n\t\t\t<td>";
			try {
				print $file->getCreator()->getDisplayName();
			} catch (OperationFailedException $e) {
			} catch (UnimplementedException $e) {
			}
			print "</td>";
			
			print "\n\t\t\t<td>";
			print "<a href='".$file->getHttpUrl()."'>HTTP (Download)</a>";
			print "<br/><a href='".$file->getRtmpUrl()."'>RTMP (Streaming)</a>";
			print "<br/><a href='#' onclick=\"alert('Unimplemented'); return false;\">Embed Code</a>";
			print "</td>";
			
			print "\n\t\t\t<td>";
			print _("Delete");
			print "</td>";
			
			print "\n\t\t</tr>";
		}
		
		print "\n\t</tbody>";
		print "\n\t</table>";
		return ob_get_clean();
	}
}

?>