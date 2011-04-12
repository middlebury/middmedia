<?php
/**
 * @since 11/19/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * An abstract class to capture some of the common needs of middmedia actions.
 * 
 * @since 11/19/09
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2009, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
abstract class MiddMedia_Action_Abstract
	extends MainWindowAction
{
	
	/**
	 * Answer the manager to use
	 * 
	 * @return MiddMedia_Manager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		return MiddMedia_Manager::forCurrentUser();
	}
	
	/**
	 * Answer the system-limits on file-size
	 * 
	 * @return int
	 * @access protected
	 * @since 11/19/09
	 */
	protected function getSystemUploadLimit () {
		return min(
					ByteSize::fromString(ini_get('post_max_size'))->value(),
					ByteSize::fromString(ini_get('upload_max_filesize'))->value(),
					ByteSize::fromString(ini_get('memory_limit'))->value()
				);
	}
	
	/**
	 * Answer the upload limit for a particular directory. This will take into account
	 * directory quotas as well as system limits.
	 * 
	 * @param MiddMedia_Directory $directory
	 * @return int
	 * @access protected
	 * @since 11/19/09
	 */
	protected function getDirectoryUploadLimit (MiddMedia_Directory $directory) {
		return min($this->getSystemUploadLimit(), $directory->getBytesAvailable());
					
	}
	
	/**
	 * Answer quota-bar html for a directory
	 * 
	 * @param MiddMedia_Directory $dir
	 * @return string
	 * @access protected
	 * @since 11/19/09
	 */
	protected function getQuotaBar (MiddMedia_Directory $dir) {
		ob_start();
		$dirId = md5($dir->getBaseName());
		
		print "\n<div class='quota_bar' id='quota_bar-".$dirId."'>";
		print "\n\t<div class='quota_ammount' id='quota_ammount-".$dirId."'>".$dir->getQuota()."</div>";
		print "\n\t<div class='quota_ammount_used' id='quota_ammount_used-".$dirId."'>".$dir->getBytesUsed()."</div>";
		
		$percent = ceil(100 * ($dir->getBytesUsed() / $dir->getQuota()));
		print "\n\t<div class='used' style='width: ".$percent."%;' id='quota_used-".$dirId."'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesUsed());
		print "\n\t<div class='used_label' id='quota_used_label-".$dirId."'>"._("Used: ").$size->asString()."</div>";
		
		$percent = floor(100 * ($dir->getBytesAvailable() / $dir->getQuota()));
		print "\n\t<div class='free' style='width: ".$percent."%;' id='quota_free-".$dirId."'>&nbsp;</div>";
		$size = ByteSize::withValue($dir->getBytesAvailable());
		print "\n\t<div class='free_label' id='quota_free_label-".$dirId."'>"._("Free: ").$size->asString()."</div>";
		
		print "\n</div>";
		
		return ob_get_clean();
	}
	
	/**
	 * Answer help html
	 * 
	 * @return string
	 * @access protected
	 * @since 11/19/09
	 */
	protected function getUploadHelp () {
		ob_start();
		$harmoni = Harmoni::instance();
		print "\n<div class='upload_help'>";
		print "\n\tThe follow media types are allowed:";
		$mimeMgr = Services::getService("MIME");
		foreach(explode(',', MIDDMEDIA_ALLOWED_FILE_TYPES) as $type) {
			print "<br/>&nbsp;&nbsp;&nbsp;&nbsp;.".trim($type)." (".$mimeMgr->getMIMETypeForExtension(trim($type)).")";
		}
		print "<p>See <a href='https://mediawiki.middlebury.edu/wiki/LIS/MiddMedia' target='_blank'>MiddMedia Help</a> for more information.</p>";
		print "\n</div>";
		return ob_get_clean();
	}
	
}

?>