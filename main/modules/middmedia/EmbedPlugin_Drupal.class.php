<?php

class EmbedPlugin_Drupal extends EmbedPlugin_Abstract implements EmbedPlugin {
	
	private $title;
	private $markup;
	
	function __construct() {
		$this->title = 'Drupal Embed Code';
		$this->markup = 'Here is code or something.';
	}
	
	function GetTitle() {
		return $this->title;
	}
	
	function GetMarkup() {
		return $this->markup;
	}
	
	public function make_js_obj () {
		
      $classname = get_class($this);
      $string = "\nfunction $classname() " . "{\n";
      $cvars = get_object_vars($this);
      foreach ($cvars as $k=>$v)
      {
       $string = $string . "\tthis.$k = '$v';\n";
      }
      $string = $string . "}\n\n";
      return $string;
  }
	
}