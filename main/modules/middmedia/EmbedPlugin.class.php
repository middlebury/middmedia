<?php

interface EmbedPlugin {
	
	function GetTitle();
	function GetDesc($file);
	function GetMarkup($file);
	
}