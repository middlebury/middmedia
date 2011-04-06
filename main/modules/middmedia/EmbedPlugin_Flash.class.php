<?php

class EmbedPlugin_Flash implements EmbedPlugin {
	
	private $title;
	private $desc;
	private $markup;
	
	function __construct() {
		$this->title = 'Embed Code';
		$this->desc = '<p>The following code can be pasted into web sites to display this video in-line. Please note that some services may not allow the embedding of videos.</p>';
	}
	
	function GetTitle() {
		return $this->title;
	}
	
	function GetDesc($file) {
		return $this->desc;
	}
	
	function GetMarkup($file) {
		$image = $file->getSplashImage();
		$splash = $image->getHttpUrl();
		$fileID = $_GET['id'];
		
		$this->markup = '<textarea rows="6" cols="83"><embed src="http://middmedia.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddmedia.middlebury.edu%2Fvod%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27'.$fileID.'%27%2CsplashImageFile%3A%27'. $splash .'%27%7D" width="400" height="300" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></br /><div style="width:400px;text-align:center;"><a style="margin:auto;" href="' . $file->getHttpUrl() . '">Download Video</a></div></textarea>';
		return $this->markup;
	}
	
}