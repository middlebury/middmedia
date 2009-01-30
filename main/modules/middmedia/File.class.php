<?php
/**
 * @since 10/24/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');
require_once(dirname(__FILE__).'/ImageFile.class.php');

/**
 * This class is a basic wrapper around a file
 * 
 * @since 10/24/08
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MiddMedia_File
	extends Harmoni_Filing_FileSystemFile
{
	
	/**
	 * Answer true if the file name is valid, false otherwise
	 * 
	 * @param string $name
	 * @return boolean
	 * @access public
	 * @since 11/19/08
	 * @static
	 */
	public static function nameValid ($name) {
		return preg_match('/^[a-z0-9_+=,.?#@%^!~\'&\[\]{}()<>\s-]+$/i', $name);
	}
	
	/**
	 * Constructor.
	 * 
	 * @param object MiddMedia_Directory $directory
	 * @param string $basename
	 * @return void
	 * @access public
	 * @since 10/24/08
	 */
	public function __construct (MiddMedia_Directory $directory, $basename) {
		$this->directory = $directory;
		if (!self::nameValid($basename))
			throw new InvalidArgumentException('Invalid file name \''.$basename.'\'');

		parent::__construct($directory->getFSPath().'/'.$basename);
	}
	
	/**
	 * Answer the full file-system path of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getFsPath () {
		return $this->getPath();
	}
	
	/**
	 * Answer the full http path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getHttpUrl () {
		return $this->directory->getHttpUrl().'/'.rawurlencode($this->getBaseName());
	}
	
	/**
	 * Answer the full RMTP path (URI) of this directory
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/08
	 */
	public function getRtmpUrl () {
		$base = rtrim(MIDDMEDIA_RTMP_BASE_URL, '/').'/';
		$parts = pathinfo($this->getBaseName());
		switch (strtolower($parts['extension'])) {
			case 'mp4':
				$base .= 'mp4:';
				break;
			case 'mp3':
				$base .= 'mp3:';
				break;
		}
		return $base.$this->directory->getBaseName().'/'.rawurlencode($this->getBaseName());
	}
	
	/**
	 * Move an uploaded file into this file.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function moveInUploadedFile ($tempName) {
		move_uploaded_file($tempName, $this->getFsPath());
	}
	
	/**
	 * Delete the file.
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function delete () {
		parent::delete();
		
		$query = new DeleteQuery;
		$query->setTable('middmedia_metadata');
		$query->addWhereEqual('directory', $this->directory->getBaseName());
		$query->addWhereEqual('file', $this->getBaseName());
		
		$dbMgr = Services::getService("DatabaseManager");
		$dbMgr->query($query, HARMONI_DB_INDEX);
	}
	
	/**
	 * Answer the Agent that created this file.
	 *
	 * This method throws the following exceptions:
	 *		OperationFailedException 	- If no creator is listed or can be returned.
	 *		UnimplementedException 		- If this method is not available yet.
	 * 
	 * @return object Agent
	 * @access public
	 * @since 10/24/08
	 */
	public function getCreator () {
		if (!isset($this->creator)) {
			$query = new SelectQuery;
			$query->addTable('middmedia_metadata');
			$query->addColumn('creator');
			$query->addWhereEqual('directory', $this->directory->getBaseName());
			$query->addWhereEqual('file', $this->getBaseName());
			
			$dbMgr = Services::getService("DatabaseManager");
			$result = $dbMgr->query($query, HARMONI_DB_INDEX);
			
			if (!$result->getNumberOfRows())
				throw new OperationFailedException("No creator listed.");
			
			$agentMgr = Services::getService('Agent');
			$this->creator = $agentMgr->getAgent(new HarmoniId($result->field('creator')));
			$result->free();
		}
		return $this->creator;
	}
	
	/**
	 * Answer the username of the creator
	 * 
	 * @return string
	 * @access public
	 * @since 1/14/09
	 */
	public function getCreatorUsername () {
		$creator = $this->getCreator();
		$propertiesCollections = $creator->getProperties();
		while($propertiesCollections->hasNext()) {
			$properties = $propertiesCollections->next();
			$username = $properties->getProperty('username');
			if (!is_null($username))
				return $username;
		}
		throw new OperationFailedException ("No creator username available.");
	}
	
	/**
	 * Set the creator of the file.
	 * 
	 * @param object Agent $creator
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function setCreator (Agent $creator) {
		$query = new InsertQuery;
		$query->setTable('middmedia_metadata');
		$query->addValue('directory', $this->directory->getBaseName());
		$query->addValue('file', $this->getBaseName());
		$query->addValue('creator', $creator->getId()->getIdString());
		
		$dbMgr = Services::getService("DatabaseManager");
		$dbMgr->query($query, HARMONI_DB_INDEX);
	}
	
	/**
	 * Answer the thumbnail image, throws an OperationFailedException if not available.
	 * 
	 * @return object MiddMedia_ImageFile
	 * @access public
	 * @since 1/29/09
	 */
	public function getThumbnailImage () {
		if (!file_exists($this->getThumbImagePath())) {
			try {
				$this->createImages();
			} catch (InvalidArgumentException $e) {
				if ($e->getCode() == 4321)
					throw new OperationFailedException("Thumbnail image does not exist", 897345);
				else
					throw $e;
			}
		}
		return new MiddMedia_ImageFile($this->directory, $this, 'thumb');
	}
	
	/**
	 * Answer the splash image, throws an OperationFailedException if not available.
	 * 
	 * @return object MiddMedia_ImageFile
	 * @access public
	 * @since 1/29/09
	 */
	public function getSplashImage () {
		if (!file_exists($this->getSplashImagePath())) {
			try {
				$this->createImages();
			} catch (InvalidArgumentException $e) {
				if ($e->getCode() == 4321)
					throw new OperationFailedException("Splash image does not exist", 897345);
				else
					throw $e;
			}
		}
		return new MiddMedia_ImageFile($this->directory, $this, 'splash');
	}
	
	/**
	 * Create a set of thumbnail images from the video file at the time-code specified.
	 * - If the time-code is out of range, alternate time-codes will be tried.
	 * - If no thumbnail images can be generated, default images will be used.
	 * 
	 * @param optional float $seconds Time-offset at which to grab the frame.
	 * @return void
	 * @access public
	 * @since 1/29/09
	 */
	public function createImages ($seconds = 5) {
		if (!preg_match('/^video\//', $this->getMimeType()))
			throw new InvalidArgumentException("Cannot generate thumbnails for non-video files.", 4321);
		
		$timecodes = array($seconds);
		if ($seconds > 5)
			$timecodes[] = 5;
		if ($seconds > 2)
			$timecodes[] = 2;
		
		// Try several time-codes and see if we can get an image out.
		while (!isset($thumbnail) && current($timecodes)) {
			try {
				$thumbnail = $this->createThumbnail($seconds);
			} catch (OperationFailedException $e) {
				next($timecodes);
			}
		}
		
		// if we still don't have an image, copy in our default one.
		if (!isset($thumbnail)) {
			if (!defined('MIDDMEDIA_DEFAULT_THUMB_PATH'))
				throw new ConfigurationErrorException('MIDDMEDIA_DEFAULT_THUMB_PATH is not defined');
			if (!copy(MIDDMEDIA_DEFAULT_THUMB_PATH, $this->getThumbImagePath()))
				throw new OperationFailedException('Could not copy default thumbnail image');
			
			$thumbnail = new MiddMedia_ImageFile($this->directory, $this, 'thumb');
		}
			
		// Generate the splash image from the thumbnail
		$splashImage = $this->createSplashImage($thumbnail);
	}
	
	/**
	 * Create a set of thumbnail images from the video file at the time-code specified.
	 *
	 * Throws:
	 *		InvalidArgumentException on invalid time-code
	 *		PermissionDeniedException on read/write failure.
	 *		ConfigurationErrorException on invalid configuration
	 *		OperationFailedException on image extraction failure.
	 * 
	 * @param optional float $seconds Time-offset at which to grab the frame.
	 * @return Harmoni_Filing_File the thumbnail file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createThumbnail ($seconds = 5) {
		$seconds = floatval($seconds);
		if ($seconds <= 0)
			throw new InvalidArgumentException('$seconds must be a float greater than zero. '.$seconds.' is invalid.');
		
		if (!$this->isReadable())
			throw new PermissionDeniedException('Video file is not readable: '.$this->directory->getBaseName().'/'.$this->getBaseName());
		
		$thumbsDir = dirname($this->getThumbImagePath());
		
		if (!file_exists($thumbsDir)) {
			if (!mkdir($thumbsDir, 0775))
				throw new PermissionDeniedException('Could not create thumbs dir: '.$this->directory->getBaseName().'/thumb');
		}
		
		if (!is_writable($thumbsDir))
			throw new PermissionDeniedException('Thumbs dir is not writable: '.$this->directory->getBaseName().'/thumb');
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		
		// Try to create the thumbnail
		$destImage = $this->getThumbImagePath();
		$command = FFMPEG_PATH.' -vframes 1 -ss '.$seconds.' -i '.escapeshellarg($this->getFsPath()).'  -vcodec mjpeg '.escapeshellarg($destImage);
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Thumbnail generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperaionFailedException('Thumbnail was not generated: '.$this->directory->getBaseName().'/thumb/'.$parts['filename'].'.jpg');
		
		return new MiddMedia_ImageFile($this->directory, $this, 'thumb');
	}
	
	/**
	 * Create a splash image from a thumbnail image file
	 * 
	 * @param Harmoni_Filing_File $thumbnail
	 * @return Harmoni_Filing_FileInterface The splash image file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createSplashImage (Harmoni_Filing_FileInterface $thumbnail) {
		if (!$thumbnail->isReadable())
			throw new PermissionDeniedException('Thumbnail file is not readable: '.$this->directory->getBaseName().'/thumb/'.$thumbnail->getBaseName());
		
		// Set up the Splash Image directory
		$splashDir = dirname($this->getSplashImagePath());
		
		if (!file_exists($splashDir)) {
			if (!mkdir($splashDir, 0775))
				throw new PermissionDeniedException('Could not create splash dir: '.$this->directory->getBaseName().'/splash');
		}
		
		if (!is_writable($splashDir))
			throw new PermissionDeniedException('Splash dir is not writable: '.$this->directory->getBaseName().'/splash');
		
		if (!defined('IMAGE_MAGICK_COMPOSITE_PATH'))
			throw new ConfigurationErrorException('IMAGE_MAGICK_COMPOSITE_PATH is not defined');
		
		if (!defined('MIDDMEDIA_SPLASH_OVERLAY'))
			throw new ConfigurationErrorException('MIDDMEDIA_SPLASH_OVERLAY is not defined');
		
		if (!is_readable(MIDDMEDIA_SPLASH_OVERLAY))
			throw new PermissionDeniedException('MIDDMEDIA_SPLASH_OVERLAY is not readable');
		
		$destImage = $this->getSplashImagePath();
		$command = IMAGE_MAGICK_COMPOSITE_PATH.' -gravity center '.escapeshellarg(MIDDMEDIA_SPLASH_OVERLAY).' '.escapeshellarg($thumbnail->getFsPath()).' '.escapeshellarg($destImage);
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Splash-Image generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperaionFailedException('Splash-Image was not generated: '.$this->directory->getBaseName().'/splash/'.$parts['filename'].'.jpg');
		
		return new MiddMedia_ImageFile($this->directory, $this, 'splash');
	}
	
	/**
	 * Answer the file path that the thumbnail image should have
	 * 
	 * @return string
	 * @access private
	 * @since 1/29/09
	 */
	private function getThumbImagePath () {
		return MiddMedia_ImageFile::getFsPathForImage($this->directory, $this, 'thumb');
	}
	
	/**
	 * Answer the file path that the splash image should have
	 * 
	 * @return string
	 * @access private
	 * @since 1/29/09
	 */
	private function getSplashImagePath () {
		return MiddMedia_ImageFile::getFsPathForImage($this->directory, $this, 'splash');
	}
}

?>