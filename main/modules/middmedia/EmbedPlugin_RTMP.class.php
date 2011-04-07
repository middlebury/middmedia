<?php

/******************
*
* Class for the embed code used for
* showing the RTMP link to the video. 
*
*******************/

class EmbedPlugin_RTMP implements EmbedPlugin {
	
	private $title;
	private $desc;
	private $markup;
	
	function __construct() {
		$this->title = 'RTMP (Streaming) URL';
		$this->desc = '<p>The following URL may be used in custom Flash video players to stream this video.</p>';
	}
	
	function GetTitle() {
		return $this->title;
	}
	
	function GetDesc($file) {
		return $this->desc;
	}
	
	function GetMarkup($file) {
		$this->markup = '<input type="text" size="110" value="'.$file->getRtmpUrl() . '" />';
		return $this->markup;
	}
	
}