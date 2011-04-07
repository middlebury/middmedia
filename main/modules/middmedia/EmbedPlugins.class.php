<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * This is a singleton class for holding instances of the
 * embed codes for the video files on Middmedia 
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class EmbedPlugins {
	
	private static $instance;
	private $plugins;
	
 /**
 	* Make only one instance of the class
 	* 
 	* @return EmbedPlugins
 	*/	
	public static function instance() {
		
		//ensures that a new one is not made if one
		//already exists.
		if (!isset(self::$instance)) {
			self::$instance = new EmbedPlugins();
		}
		return self::$instance;
	}
	
 /**
 	* Constructor
 	* 
 	* @return void
 	*/	
	private function __construct() {
		$this->plugins = array();
	}
	
 /**
 	* Add embed plugins to $plugins
 	* 
 	* @param EmbedPlugin $p
 	* @return void
 	*/	
	public function AddPlugin(EmbedPlugin $p) {
		$this->plugins[] = $p;
	}
	
 /**
 	* Gets the array of plugins
 	* 
 	* @return array
 	*/	
	public function getPlugins() {
		return $this->plugins;
	}
	
}