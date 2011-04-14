<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * Class for the embed code used for the files
 * that stream from the Flash Media Server. 
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_Embed_Plugin_Flash
	implements MiddMedia_Embed_Plugin 
{
  
 /**
   * Gets the title of the embed code
   * 
   * @return string
   */
  function getTitle() {
    return 'Embed Code';
  }
  
 /**
   * Gets the description for the embed code
   * 
   * @param MiddMedia_File $file
   * @return string
   */
  function getDesc(MiddMedia_File $file) {
    return "\n<p>The following code can be pasted into web sites to display this video in-line. Please note that some services may not allow the embedding of videos.</p>";
  }
  
 /**
   * Gets the embed code markup
   * 
   * @param MiddMedia_File $file
   * @return string
   */
  function getMarkup(MiddMedia_File $file) {
    
    $image = $file->getSplashImage();
    $splash = $image->getHttpUrl();
    $fileID = $_GET['id'];
    
    return "<textarea rows='6' cols='83'><embed src='http://middmedia.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddmedia.middlebury.edu%2Fvod%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27'.$fileID.'%27%2CsplashImageFile%3A%27'. $splash .'%27%7D' width='400' height='300' scale='fit' bgcolor='#111111' type='application/x-shockwave-flash' allowFullScreen='true' allowNetworking='all' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed><br />
    \n<div style='width:400px;text-align:center;'><a style='margin:auto;' href='" . $file->getHttpUrl() . "'>Download Video</a></div></textarea>";
  }

  /**
   * Checks to see if the file is supported
   * by the particular embed code
   * 
   * @param MiddMedia_File $file
   * @return boolean
   */
  function isSupported(MiddMedia_File $file) {
    return true;
  }
  
}