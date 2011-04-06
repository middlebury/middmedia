<?php

class EmbedPlugin_HTTP implements EmbedPlugin {
	
	private $title;
	private $desc;
	private $markup;
	
	function __construct() {
		$this->title = 'HTTP (Streaming) URL';
		$this->desc = '<p>Make a link to the following URL to allow downloads of this file.</p>';
	}
	
	function GetTitle() {
		return $this->title;
	}
	
	function GetDesc($file) {
		return '<p><a href="' . $file->getHttpUrl() . '">Click here to download this file.</a></p>' . $this->desc;
	}
	
	function GetMarkup($file) {
		$this->markup = '<input type="text" size="110" value="'. $file->getHttpUrl() . '" />';
		return $this->markup;
	}
	
}