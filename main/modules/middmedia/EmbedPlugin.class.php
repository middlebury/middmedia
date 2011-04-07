<?php

/******************
*
* This is an interface for the embed plugins
* that hold the embed code for the video files on Middmedia. 
* They have the following methods.
*
*******************/

interface EmbedPlugin {
	
	function GetTitle();
	function GetDesc($file);
	function GetMarkup($file);
	
}