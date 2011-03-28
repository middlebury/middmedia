<?php

abstract class EmbedPlugin_Abstract {
	
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