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
	 * Answer a target file-extension given an input extension.
	 * 
	 * Exceptions:
	 *		InvalidArgumentException - No extension argument supplied.
	 *		OperationFailedException - No mapping supported.
	 * 
	 * @param string $extension 'mp4', 'mov', etc
	 * @return string 
	 * @access public
	 * @since 9/24/09
	 * @static
	 */
	public static function getTargetExtension ($extension) {
		if (!preg_match('/^[a-z0-9]{2,4}$/i', $extension))
			throw new InvalidArgumentException("Invalid extension '$extension'.");
		
		$extension = strtolower($extension);
		$nonCoverting = array('mp4', 'flv', 'mp3');
		if (in_array($extension, $nonCoverting))
			return $extension;
		else
			return 'mp4';
	}
	
	/**
	 * Answer an array of allowed extensions
	 * 
	 * @return array
	 * @access public
	 * @since 9/24/09
	 * @static
	 */
	public static function getAllowedVideoTypes () {
		$types = explode(",", MIDDMEDIA_ALLOWED_FILE_TYPES);
		array_walk($types, 'trim');
		array_walk($types, 'strtolower');
		return $types;
	}
	
	/**
	 * Answer video information
	 * 
	 * @param string $filePath
	 * @return array
	 * @access public
	 * @since 9/24/09
	 * @static
	 */
	public static function getVideoInfo ($filePath) {
		if (!file_exists($filePath))
			throw new OperationFailedException("File doesn't exist.");
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		
		$command = FFMPEG_PATH.' -i '.escapeshellarg($filePath).' 2>&1';
		$lastLine = exec($command, $output, $return_var);
		$output = implode("\n", $output);
		
		if (!preg_match('/Stream #[^:]+: Video: ([^,]+), (?:([^,]+), )?([0-9]+)x([0-9]+), ([0-9\.]+) (?:tbr|kb\/s),/', $output, $matches))
			throw new OperationFailedException("Could not determine video properties from: <pre>\n$output\n</pre>\n");
		$info['codec'] = $matches[1];
		$info['colorspace'] = $matches[2];
		$info['width'] = intval($matches[3]);
		$info['height'] = intval($matches[4]);
		$info['framerate'] = floatval($matches[5]);
		
		if (preg_match('/Stream #[^:]+: Audio: ([^,]+), ([0-9]+) Hz, ([0-9]+) channels/', $output, $matches)) {
			$info['audio_codec'] = $matches[1];
			$info['audio_samplerate'] = intval($matches[2]);
			$info['audio_channels'] = intval($matches[3]);
		}
		return $info;
	}
	
	/**
	 * Check the queue for items to process and start processing if needed.
	 * 
	 * @param object MiddMediaManagerMiddMediaManager $manager
	 * @return void
	 * @access public
	 * @since 9/25/09
	 * @static
	 */
	public static function checkQueue (MiddMediaManager $manager) {
		$dbMgr = Services::getService("DatabaseManager");
		
		// Check to see if there are any items currently processing. If so, don't process more.
		$query = new SelectQuery;
		$query->addTable('middmedia_queue');
		$query->addColumn('*');
		$query->addWhereEqual('processing', '1');
		$results = $dbMgr->query($query, HARMONI_DB_INDEX);
		
		while ($results->hasNext()) {
			$row = $results->next();
			
			// Clean out any really old jobs
			$startTStamp = $dbMgr->fromDBDate($row['processing_start'], HARMONI_DB_INDEX);
			if ($startTStamp < (time() - (3 * 60 * 60))) {
				$dir = $manager->getDirectory($row['directory']);
				$file = $dir->getFile($row['file']);
				
				// Ensure that the file is in an error state.
				$file->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.mp4'));
				$file->deleteTempFiles();
				$file->removeFromQueue();
			} 
			// We have a current job
			else {
				$results->free();
				return;
			}
		}
		$results->free();
		
		// Look for new jobs
		$query = new SelectQuery;
		$query->addTable('middmedia_queue');
		$query->addColumn('*');
		$query->addOrderBy('upload_time', ASCENDING);
		$query->limitNumberOfRows(1);
		$results = $dbMgr->query($query, HARMONI_DB_INDEX);
		
		// Start a new job if we have one.
		if ($results->hasNext()) {
			$row = $results->next();
			$results->free();
			
			try {
				$dir = $manager->getDirectory($row['directory']);
				$file = $dir->getFile($row['file']);
				
				// Update the DB to indicate a job start.
				$query = new UpdateQuery;
				$query->setTable('middmedia_queue');
				$query->addValue('processing', '1');
				$query->addRawValue('processing_start', 'NOW()');
				$query->addWhereEqual('directory', $file->directory->getBaseName());
				$query->addWhereEqual('file', $file->getBaseName());
				$dbMgr->query($query, HARMONI_DB_INDEX);
				
				try {
					$file->process();
				} catch (OperationFailedException $e) {
					$file->removeFromQueue();
					throw $e;
				}
				$file->removeFromQueue();
			} catch (UnknownIdException $e) {
				$file->removeFromQueue();
				throw $e;
			}
		}
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
	 * Answer the size (bytes) of the file
	 * 
	 * @return int
	 * @access public
	 * @since 5/6/08
	 */
	public function getSize () {
		// Add any temporary file size so that users can queue up more files than
		// they have space for.
		$tmpFile = $this->directory->getFsPath().'/queue/'.$this->getBaseName().'-tmp';
		if (file_exists($tmpFile))
			return filesize($tmpFile) + parent::getSize();
		else
			return parent::getSize();
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
	 * Set the contents of the file
	 * 
	 * @param string $contents
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function setContents ($contents) {
		parent::setContents($contents);
		
		try {
			$this->deleteImages();
			$this->createImages();
		} catch (InvalidArgumentException $e) {
			// Only ignore if reporting that the we can't generate images for the file-type.
			if ($e->getCode() != 4321)
				throw $e;
		}
		
		$this->logAction('upload');
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
		
		try {
			$this->deleteImages();
			$this->createImages();
		} catch (InvalidArgumentException $e) {
			// Only ignore if reporting that the we can't generate images for the file-type.
			if ($e->getCode() != 4321)
				throw $e;
		}
		
		$this->logAction('upload');
	}
	
	/**
	 * Move a file into this file.
	 * 
	 * @param string $sourcePath
	 * @return void
	 * @access public
	 * @since 11/21/08
	 */
	public function moveInFile ($sourcePath) {
		rename($sourcePath, $this->getFsPath());
		
		try {
			$this->deleteImages();
			$this->createImages();
		} catch (InvalidArgumentException $e) {
			// Only ignore if reporting that the we can't generate images for the file-type.
			if ($e->getCode() != 4321)
				throw $e;
		}
		
		$this->logAction('changed');
	}
	
	/**
	 * Move an uploaded file into our file and hand any conversion if needed.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 9/24/09
	 */
	public function moveInUploadedFileForProcessing ($tempName) {
		if ($this->getExtension() == 'flv' || $this->getExtension() == 'mp3') {
			$this->moveInUploadedFile($tempName);
			return;
		}
			
		// If conversion is needed, put in our placeholder video and queue for conversion.
		if ($this->needsConversion($tempName)) {
			$this->putContents(file_get_contents(MYDIR.'/images/ConvertingVideo.mp4'));
			$this->queueForProcessing($tempName);
		}
		// Otherwise move the video to its final location.
		else {
			$this->moveInUploadedFile($tempName);
		}
		
		$this->logAction('upload');
	}
	
	/**
	 * Answer true if a file needs conversion to mp4.
	 * 
	 * @param string $tempName
	 * @return boolean
	 * @access public
	 * @since 9/24/09
	 */
	public function needsConversion ($tempName) {
		$info = self::getVideoInfo($tempName);
		return (strtolower($info['codec']) != 'h264');
	}
	
	/**
	 * Queue a file for conversion to mp4.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 9/24/09
	 */
	public function queueForProcessing ($tempName) {
		$queueDir = $this->directory->getFsPath().'/queue';
		if (!file_exists($queueDir)) {
			if (!mkdir($queueDir, 0775))
				throw new PermissionDeniedException('Could not create queue dir: '.$queueDir);
		}
		
		// Move the file out of the uploads directory to a temporary hold place.
		move_uploaded_file($tempName, $queueDir.'/'.$this->getBaseName().'-tmp');
		
		// Add an entry to our encoding queue.
		$query = new InsertQuery;
		$query->setTable('middmedia_queue');
		$query->addValue('directory', $this->directory->getBaseName());
		$query->addValue('file', $this->getBaseName());
		
		$dbMgr = Services::getService("DatabaseManager");
		try {
			$dbMgr->query($query, HARMONI_DB_INDEX);
		} catch (DuplicateKeyDatabaseException $e) {
			// If the file was re-uploaded, update the the timestamp.
			$query = new UpdateQuery;
			$query->setTable('middmedia_queue');
			$query->addRawValue('upload_time', 'NOW()');
			$query->addWhereEqual('directory', $this->directory->getBaseName());
			$query->addWhereEqual('file', $this->getBaseName());
			$dbMgr->query($query, HARMONI_DB_INDEX);
		}
	}
	
	/**
	 * Remove this file from the processing queue
	 * 
	 * @return void
	 * @access public
	 * @since 9/25/09
	 */
	public function removeFromQueue () {
		$dbMgr = Services::getService("DatabaseManager");
		
		// Remove from the queue
		$query = new DeleteQuery;
		$query->setTable('middmedia_queue');
		$query->addWhereEqual('directory', $this->directory->getBaseName());
		$query->addWhereEqual('file', $this->getBaseName());
		$dbMgr->query($query, HARMONI_DB_INDEX);
	}
	
	/**
	 * Process any uploaded versions of this file.
	 * This method does no locking. Clients must handle locking to prevent multiple
	 * processing threads from clobbering each other's results
	 * 
	 * Exceptions:
	 *		OperationFailedException - Processing has failed.
	 *		ConfigurationErrorException - FFMPEG_PATH is not defined.
	 * 
	 * @return void
	 * @access public
	 * @since 9/25/09
	 */
	public function process () {
		$tmpFile = $this->directory->getFsPath().'/queue/'.$this->getBaseName().'-tmp';
		$outFile = $this->directory->getFsPath().'/queue/'.$this->getBaseName();
		if (file_exists($tmpFile)) {
			if (!defined('FFMPEG_PATH'))
				throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
			if (!defined('MIDDMEDIA_CONVERT_MAX_WIDTH'))
				throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_WIDTH is not defined');
			if (!defined('MIDDMEDIA_CONVERT_MAX_HEIGHT'))
				throw new ConfigurationErrorException('MIDDMEDIA_CONVERT_MAX_HEIGHT is not defined');
			
			// Determine the output size base on our maximums.
			$info = self::getVideoInfo($tmpFile);
			$width = $info['width'];
			$height = $info['height'];
			if ($width > MIDDMEDIA_CONVERT_MAX_WIDTH) {
				$ratio = MIDDMEDIA_CONVERT_MAX_WIDTH / $width;
				$width = MIDDMEDIA_CONVERT_MAX_WIDTH;
				$height = round($ratio * $height);
			}
			if ($height > MIDDMEDIA_CONVERT_MAX_HEIGHT) {
				$ratio = MIDDMEDIA_CONVERT_MAX_HEIGHT / $height;
				$width = round($ratio * $width);
				$height = MIDDMEDIA_CONVERT_MAX_HEIGHT;
			}
			// Round to the nearest multiple of 2 as this is required for frame sizes.
			$width = round($width/2) * 2;
			$height = round($height/2) * 2;
			
			// Some audio sample rates die, so force to the closest of 44100, 22050, 11025
			$sampleRate = $info['audio_samplerate'];
			if (!in_array($sampleRate, array(44100, 22050, 11025))) {
				if ($sampleRate < 16538)
					$sampleRate = 11025;
				else if ($sampleRate < 33075)
					$sampleRate = 22050;
				else
					$sampleRate = 44100;
			}
			
			// Convert the video
			$command = FFMPEG_PATH
				.' -i '
				.escapeshellarg($tmpFile)
				.' -vcodec libx264 -vpre normal -b 500k -bt 500k '
				.' -ar '.$sampleRate.' '
				.' -s '.$width.'x'.$height.' '
				.escapeshellarg($outFile).' 2>&1';
			$lastLine = exec($command, $output, $return_var);
			$output = implode("\n", $output);
			
			if ($return_var) {
				$this->deleteTempFiles();
				$this->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.mp4'));
				throw new OperationFailedException("Video encoding failed with error $return_var and output: \n<pre>\n$output\n</pre>\n");
			}
			
			// Move into position
			$this->moveInFile($outFile);
			$this->deleteTempFiles();
		}
		
		$this->logAction('processed');
	}
	
	/**
	 * Delete any temporary or working files
	 * 
	 * @return void
	 * @access public
	 * @since 9/25/09
	 */
	public function deleteTempFiles () {
		$tmpFile = $this->directory->getFsPath().'/queue/'.$this->getBaseName().'-tmp';
		$outFile = $this->directory->getFsPath().'/queue/'.$this->getBaseName();
		
		if (file_exists($tmpFile))
			unlink($tmpFile);
		if (file_exists($outFile))
			unlink($outFile);
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
		
		$this->deleteImages();
		$this->deleteTempFiles();
		$this->removeFromQueue();
		
		$this->logAction('delete');
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
	 * Answer the image of a video frame, throws an OperationFailedException if not available.
	 * 
	 * @return object MiddMedia_ImageFile
	 * @access public
	 * @since 1/29/09
	 */
	public function getFullFrameImage () {
		if (!file_exists($this->getFullFrameImagePath())) {
			try {
				$this->createImages();
			} catch (InvalidArgumentException $e) {
				if ($e->getCode() == 4321)
					throw new OperationFailedException("Full-frame image does not exist", 897345);
				else
					throw $e;
			}
		}
		return new MiddMedia_ImageFile($this->directory, $this, 'full_frame');
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
	 * Answer embed code that can be used for this file. 
	 * This is an example, other players will work as well.
	 * 
	 * @return string
	 * @access public
	 * @since 1/30/09
	 */
	public function getEmbedCode () {
		$parts = pathinfo($this->getBasename());
		// PHP < 5.2.0 doesn't have 'filename'
		if (!isset($parts['filename'])) {
			preg_match('/(.+)\.[a-z0-9]+/i', $this->getBasename(), $matches);
			$parts['filename'] = $matches[1];
		}
		
		switch (strtolower($parts['extension'])) {
			case 'flv':
				$code = MIDDMEDIA_VIDEO_EMBED_CODE;
				$myId = $this->directory->getBaseName().'/'.$parts['filename'];
				break;
			case 'mp3':
				$code = MIDDMEDIA_AUDIO_EMBED_CODE;
			default:
				if (!isset($code))
					$code = MIDDMEDIA_VIDEO_EMBED_CODE;
				$myId = strtolower($parts['extension']).':'.$this->directory->getBaseName().'/'.$parts['filename'].'.'.$parts['extension'];
		}
		
		try {
			$splashUrl = $this->getSplashImage()->getUrl();
		} catch (Exception $e) {
			$splashUrl = '';
		}
		
		$code = str_replace('###ID###', $myId, $code);
		$code = str_replace('###HTML_ID###', 'media_'.preg_replace('/[^a-z0-9_-]/i', '', $myId), $code);
		$code = str_replace('###HTTP_URL###', $this->getHttpUrl(), $code);
		$code = str_replace('###RTMP_URL###', $this->getRtmpUrl(), $code);
		$code = str_replace('###SPLASH_URL###', $splashUrl, $code);
		
		return $code;
	}
	
	/**
	 * Create a set of thumbnail images from the video file at the time-code specified.
	 * - If the time-code is out of range, alternate time-codes will be tried.
	 * - If no thumbnail images can be generated, default images will be used.
	 * 
	 * @param optional float $seconds Time-offset at which to grab the frame.
	 * @return void
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createImages ($seconds = 5) {
		if (!preg_match('/^video\//', $this->getMimeType()))
			throw new InvalidArgumentException("Cannot generate thumbnails for non-video files.", 4321);
		
		$timecodes = array($seconds);
		if ($seconds > 5)
			$timecodes[] = 5;
		if ($seconds > 2)
			$timecodes[] = 2;
		
		// Try several time-codes and see if we can get an image out.
		while (!isset($fullFrame) && current($timecodes)) {
			$seconds = current($timecodes);
			try {
				$fullFrame = $this->createFullFrame($seconds);
			} catch (OperationFailedException $e) {
				next($timecodes);
			}
		}
		
		// if we still don't have an image, copy in our default one.
		if (!isset($fullFrame)) {
			if (!defined('MIDDMEDIA_DEFAULT_FRAME_PATH'))
				throw new ConfigurationErrorException('MIDDMEDIA_DEFAULT_FRAME_PATH is not defined');
			if (!copy(MIDDMEDIA_DEFAULT_FRAME_PATH, $this->getFullFrameImagePath()))
				throw new OperationFailedException('Could not copy default full-frame image');
			
			$fullFrame = new MiddMedia_ImageFile($this->directory, $this, 'full_frame');
		}
			
		// Generate the splash image from the fullFrame
		$splashImage = $this->createSplashImage($fullFrame);
		
		// Generate the thumbnail from the full-frame
		$thumbnail = $this->createThumbnailImage($fullFrame);
	}
	
	/**
	 * Delete our image files
	 * 
	 * @return void
	 * @access protected
	 * @since 1/30/09
	 */
	protected function deleteImages () {
		$types = array('full_frame', 'thumb', 'splash');
		
		foreach ($types as $type) {
			try {
				$image = new MiddMedia_ImageFile($this->directory, $this, $type);
				$image->delete();
			} catch (InvalidArgumentException $e) {
				if ($e->getCode() != 78345)
					throw $e;
			}
		}
	}
	
	/**
	 * Create a full-frame image from the video file at the time-code specified.
	 *
	 * Throws:
	 *		InvalidArgumentException on invalid time-code
	 *		PermissionDeniedException on read/write failure.
	 *		ConfigurationErrorException on invalid configuration
	 *		OperationFailedException on image extraction failure.
	 * 
	 * @param optional float $seconds Time-offset at which to grab the frame.
	 * @return MiddMedia_ImageFile the full-frame image file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createFullFrame ($seconds = 5) {
		$seconds = floatval($seconds);
		if ($seconds <= 0)
			throw new InvalidArgumentException('$seconds must be a float greater than zero. '.$seconds.' is invalid.');
		
		if (!$this->isReadable())
			throw new PermissionDeniedException('Video file is not readable: '.$this->directory->getBaseName().'/'.$this->getBaseName());
		
		$fullFramesDir = dirname($this->getFullFrameImagePath());
		
		if (!file_exists($fullFramesDir)) {
			if (!mkdir($fullFramesDir, 0775))
				throw new PermissionDeniedException('Could not create full-frames dir: '.$this->directory->getBaseName().'/full_frame');
		}
		
		if (!is_writable($fullFramesDir))
			throw new PermissionDeniedException('Full-Frames dir is not writable: '.$this->directory->getBaseName().'/full_frame');
		
		if (!defined('FFMPEG_PATH'))
			throw new ConfigurationErrorException('FFMPEG_PATH is not defined');
		
		// Try to create the full-frame
		$destImage = $this->getFullFrameImagePath();
		$command = FFMPEG_PATH.' -vframes 1 -ss '.$seconds.' -i '.escapeshellarg($this->getFsPath()).'  -vcodec mjpeg '.escapeshellarg($destImage).'  2>&1';
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Full-frame generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperationFailedException('Full-frame was not generated: '.$this->directory->getBaseName().'/full_frame/'.basename($destImage));
		
		return new MiddMedia_ImageFile($this->directory, $this, 'full_frame');
	}
		
	/**
	 * Create a thumbnail image from a full-frame image file
	 * 
	 * @param Harmoni_Filing_File $fullFrame
	 * @return Harmoni_Filing_FileInterface The splash image file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createThumbnailImage (Harmoni_Filing_FileInterface $fullFrame) {
		if (!$fullFrame->isReadable())
			throw new PermissionDeniedException('Full-frame file is not readable: '.$this->directory->getBaseName().'/full_frame/'.$fullFrame->getBaseName());
		
		// Set up the Thumbnail Image directory
		$thumbDir = dirname($this->getThumbImagePath());
		
		if (!file_exists($thumbDir)) {
			if (!mkdir($thumbDir, 0775))
				throw new PermissionDeniedException('Could not create thumb dir: '.$this->directory->getBaseName().'/thumb');
		}
		
		if (!is_writable($thumbDir))
			throw new PermissionDeniedException('Thumb dir is not writable: '.$this->directory->getBaseName().'/thumb');
		
		if (!defined('IMAGE_MAGICK_CONVERT_PATH'))
			throw new ConfigurationErrorException('IMAGE_MAGICK_CONVERT_PATH is not defined');
		
		
		$destImage = $this->getThumbImagePath();
		$command = IMAGE_MAGICK_CONVERT_PATH.' '.escapeshellarg($fullFrame->getFsPath()).' -resize 200x200 '.escapeshellarg($destImage);
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Thumbnail-Image generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperaionFailedException('Thumbnail-Image was not generated: '.$this->directory->getBaseName().'/thumb/'.$parts['filename'].'.jpg');
		
		return new MiddMedia_ImageFile($this->directory, $this, 'thumb');
	}
	
	/**
	 * Create a splash image from a full-frame image file
	 * 
	 * @param Harmoni_Filing_File $fullFrame
	 * @return Harmoni_Filing_FileInterface The splash image file
	 * @access protected
	 * @since 1/29/09
	 */
	protected function createSplashImage (Harmoni_Filing_FileInterface $fullFrame) {
		if (!$fullFrame->isReadable())
			throw new PermissionDeniedException('Full-frame file is not readable: '.$this->directory->getBaseName().'/full_frame/'.$fullFrame->getBaseName());
		
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
		$command = IMAGE_MAGICK_COMPOSITE_PATH.' -gravity center '.escapeshellarg(MIDDMEDIA_SPLASH_OVERLAY).' '.escapeshellarg($fullFrame->getFsPath()).' '.escapeshellarg($destImage);
		$lastLine = exec($command, $output, $return_var);
		if ($return_var) {
			throw new OperationFailedException("Splash-Image generation failed with code $return_var: $lastLine");
		}
		
		if (!file_exists($destImage))
			throw new OperaionFailedException('Splash-Image was not generated: '.$this->directory->getBaseName().'/splash/'.$parts['filename'].'.jpg');
		
		return new MiddMedia_ImageFile($this->directory, $this, 'splash');
	}
	
	/**
	 * Answer the file path that the full-frame image should have
	 * 
	 * @return string
	 * @access private
	 * @since 1/29/09
	 */
	private function getFullFrameImagePath () {
		return MiddMedia_ImageFile::getFsPathForImage($this->directory, $this, 'full_frame');
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
	
	/**
	 * Log actions about this file
	 * 
	 * @param string $category
	 * @return void
	 * @access private
	 * @since 2/2/09
	 */
	private function logAction ($category) {
		switch ($category) {
			case 'upload':
				$category = 'Upload';
				$description = "File uploaded: ".$this->directory->getBaseName()."/".$this->getBaseName();
				$type = 'Event_Notice';
				break;
			case 'delete':
				$category = 'Delete';
				$description = "File deleted: ".$this->directory->getBaseName()."/".$this->getBaseName();
				$type = 'Event_Notice';
				break;
			case 'processed':
				$category = 'Video Processed';
				$description = "Video converted to mp4: ".$this->directory->getBaseName()."/".$this->getBaseName();
				$type = 'Event_Notice';
				break;
			case 'changed':
				$category = 'Contents Changed';
				$description = "File contents changed: ".$this->directory->getBaseName()."/".$this->getBaseName();
				$type = 'Event_Notice';
				break;
			default:
				throw new InvalidArgumentException("Unknown category: $category");
		}
		
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("MiddMedia");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", $type,
							"Normal events.");
			
			$item = new AgentNodeEntryItem($category, $description);
			$item->addAgentId($this->directory->getManager()->getAgent()->getId());
			
			
			$idManager = Services::getService("Id");
			
			$item->addNodeId($idManager->getId('middmedia:'.$this->directory->getBaseName().'/'));
			$item->addNodeId($idManager->getId('middmedia:'.$this->directory->getBaseName().'/'.$this->getBaseName()));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
	}
}

?>