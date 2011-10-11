<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.7 2008/02/19 17:25:28 mattlafrance Exp $
 */ 

class viewAction 
	extends MiddMedia_Action_Abstract
{
	
	/**
	 * File object property
	 */
	protected $file;
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// This is publicly accessable to user do not need to be logged in.
		// Just return true always.
		return true;
	}
	
	/**
   * Answer the target file object
   * 
   * @return object MiddMedia_File_Media_Unauthenticated
   * @access protected
   * @since 11/19/08
   */
	protected function getFile () {
    if (!isset($this->file)) {
      $manager = $this->getManager();
      $directory = $manager->getDirectory(RequestContext::value('dir'));
      $this->file = $directory->getFile(RequestContext::value('file'));
    }
    return $this->file;
  }
	
	/**
	 * Return the heading text for this action, or cause an error.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return $this->getFile()->getBaseName();
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
		$actionRows->add(
			new Block($this->getFileMarkup(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER
		);
	}
	
	/**
	 * Answer the manager to use for this action.
	 * 
	 * @return MiddMediaMangager
	 * @access protected
	 * @since 12/10/08
	 */
	protected function getManager () {
		//return MiddMedia_Manager::forCurrentUser();
		return MiddMedia_Manager_Unauthenticated::instance();
	}
	
	/**
	 * Add to the document head
	 * 
	 * @param string $string
	 * @return void
	 * @access protected
	 * @since 11/13/08
	 */
	protected function addToHead ($string) {
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().$string);
	}
	
	/**
	 * Answer a block of HTML to represent the file
	 * 
	 * @param object MiddMedia_Directory $dir
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getFileMarkup () {
		ob_start();

		$media = $this->getFile();
		
		if($media->hasFormat('mp4')) {
			$target_plugin = 'MiddMedia_Embed_Plugin_StrobePlayer';
		} elseif($media->hasFormat('mp3')) {
			$target_plugin = 'MiddMedia_Embed_Plugin_AudioPlayer';
		} elseif($media->hasFormat('m4a')) {
			$target_plugin = 'MiddMedia_Embed_Plugin_AudioPlayerM4a';
		} else {
			throw new InvalidArgumentException("No target plugin");
		}
		
		$plugins = MiddMedia_Embed_Plugins::instance();
		$plugins = $plugins->getPlugins();
		
		foreach($plugins as $plugin) {
			if (is_a($plugin, $target_plugin)) {
				$obj = $plugin;
			}
		}
		// Get the embed code for the file and print
		$markup = $obj->getMarkup($media);
		print $markup;
		
		if($media->hasFormat('mp4')) {
			$formats[] = 'mp4';
		}
		if($media->hasFormat('webm')) {
			$formats[] = 'webm';
		}
		if($media->hasFormat('flv')) {
			$formats[] = 'flv';
		}
		if($media->hasFormat('mp3')) {
			$formats[] = 'mp3';
		}
		if($media->hasFormat('m4a')) {
			$formats[] = 'm4a';
		}
		
		// Now print a link for each of the file formats
		foreach ($formats as $format) {
			print "\n<p><a href='" . $media->getFormat($format)->getHttpUrl() . "'>Click here to download ".$format." version of file. You may need to right click and choose \"save as\".</a></p>\n";
		}
		
		return ob_get_clean();
	}
}

?>