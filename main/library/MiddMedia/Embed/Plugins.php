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
class MiddMedia_Embed_Plugins {
	
	private static $instance;
	private $plugins;
	
	/**
	 * Make only one instance of the class
	 * 
	 * @return MiddMedia_Embed_Plugins
	 */
	public static function instance() {
		
		//ensures that a new one is not made if one
		//already exists.
		if (!isset(self::$instance)) {
			self::$instance = new MiddMedia_Embed_Plugins();
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
	 * @param MiddMedia_Embed_Plugin $p
	 * @return void
	 */
	public function addPlugin(MiddMedia_Embed_Plugin $p) {
		$this->plugins[] = $p;
	}
	
	/**
	 * Gets the array of plugins
	 * 
	 * @return array of MiddMedia_Embed_Plugin objects
	 */
	public function getPlugins() {
		return $this->plugins;
	}
	
}