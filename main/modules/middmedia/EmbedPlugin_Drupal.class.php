<?php

/******************
*
* Class for the short embed code used on the Drupal site. 
*
*******************/

class EmbedPlugin_Drupal implements EmbedPlugin {
	
	private $title;
	private $desc;
	private $markup;
	
	function __construct() {
		$this->title = 'Drupal Page Embed Code';
		$this->desc = '<p>The syntax for inserting videos is:[video:URL width:value height:value align:value autoplay:value autorewind:value loop:value image:URL]. The video URL is the address of the site you found the video on. Accepted values for width and height are numbers. Accepted values for align are left and right. Accepted values for autoplay, autorewind and loop are 0 (false) and 1 (true). The image URL is used to change the \"splash image\" or the image show in the player when the video is not playing. Other than the video URL, all attributes are optional.</p>';
	}
	
	function GetTitle() {
		return $this->title;
	}
	
	function GetDesc($file) {
		return $this->desc;
	}
	
	function GetMarkup($file) {
		$this->markup = '<input type="text" size="110" value="[video:' . $file->getHttpUrl() . ']" />';
		return $this->markup;
	}
	
}