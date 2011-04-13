<?php
/**
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');
require_once(dirname(__FILE__).'/Media.interface.php');
require_once(dirname(__FILE__).'/Format/Video/Source.class.php');
require_once(dirname(__FILE__).'/Format/Video/Mp4.class.php');
require_once(dirname(__FILE__).'/Format/Audio/Mp3.class.php');
require_once(dirname(__FILE__).'/Format/Image/FullFrame.class.php');
require_once(dirname(__FILE__).'/Format/Image/Thumbnail.class.php');
require_once(dirname(__FILE__).'/Format/Image/Splash.class.php');

if (version_compare(PHP_VERSION, '5.2.0', '<'))
	throw new Exception('MiddMedia Requires PHP >= 5.2.0');

/**
 * A Media file is a link to the default version of an audio or video file as well
 * as a collection for accessing all versions of the the media.
 * 
 * @package middmedia
 * 
 * @copyright Copyright &copy; 2010, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class MiddMedia_File_Media
	extends Harmoni_Filing_FileSystemFile
	implements MiddMedia_File_MediaInterface
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
		return (preg_match('/^[a-z0-9_+=,.?#@%^!~\'&\[\]{}()<>\s-]+$/i', $name) && strlen($name) < 260);
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
		
		if (!preg_match('/Stream #[^:]+: Video: ([^,]+), (?:([^,]+), )?([0-9]+)x([0-9]+)[^,]*, ([0-9\.]+) (?:tbr|kb\/s),/', $output, $matches))
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
	 * @param object MiddMedia_Manager $manager
	 * @return void
	 * @access public
	 * @since 9/25/09
	 * @static
	 */
	public static function checkQueue (MiddMedia_Manager $manager) {
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
			if ($startTStamp->isLessThan(DateAndTime::now()->minus(Duration::withHours(3)))) {
				$dir = $manager->getDirectory($row['directory']);
				$file = $dir->getFile($row['file']);
				
				// Ensure that the file is in an error state.
				$file->getFormat('mp4')->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.mp4'));
				$file->getFormat('thumb')->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.jpg'));
				$file->getFormat('full_frame')->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.jpg'));
				$file->getFormat('splash')->putContents(file_get_contents(MYDIR.'/images/VideoConversionFailed.jpg'));
				
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
				} catch (Exception $e) {
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
	
	/*********************************************************
	 * Instance Creation Methods
	 *********************************************************/
	
	/**
	 * Create a new empty file in this directory. Similar to touch().
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file already exists.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_DirectoryInterface $directory
	 * @param string $name
	 * @return object MiddMedia_File_MediaInterface The new file
	 */
	public static function create (MiddMedia_DirectoryInterface $directory, $name) {
		if (!self::nameValid($name))
			throw new InvalidArgumentException("Invalid file name '$name'.");
		
		$pathInfo = pathinfo($name);
		
		$extension = strtolower($pathInfo['extension']);
		$noExtension =  $pathInfo['filename'];
		
		if ($extension == 'mp3')
			$basename = $noExtension.'.mp3';
		else
			$basename = $noExtension.'.mp4';
		
		if ($directory->fileExists($basename))
			throw new OperationFailedException("File already exists.");
		
		// Create a placeholder file and set metadata
		touch($directory->getPath().'/'.$basename);
		$media = new MiddMedia_File_Media($directory, $basename);
		$media->setCreator($directory->getManager()->getAgent());
		
		return $media;
	}
	
	/**
	 * Get an existing file in a directory.
	 * 
	 * This method throws the following exceptions:
	 *		InvalidArgumentException 	- If incorrect parameters are supplied
	 *		OperationFailedException 	- If the file doesn't exist.
	 *		PermissionDeniedException 	- If the user is unauthorized to manage media here.
	 * 
	 * @param MiddMedia_DirectoryInterface $directory
	 * @param string $name
	 * @return object MiddMedia_File_MediaInterface The new file
	 */
	public static function get (MiddMedia_DirectoryInterface $directory, $name) {
		return new MiddMedia_File_Media($directory, $name);
	}
	
	/**
	 * Constructor.
	 * 
	 * @param object MiddMedia_DirectoryInterface $directory
	 * @param string $basename
	 * @return void
	 */
	public function __construct (MiddMedia_DirectoryInterface $directory, $basename) {
		$this->directory = $directory;
		if (!self::nameValid($basename))
			throw new InvalidArgumentException('Invalid file name \''.$basename.'\'');
		
		parent::__construct($directory->getPath().'/'.$basename);
	}
	
	/**
	 * Move an uploaded file into our file and hand any conversion if needed.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 9/24/09
	 */
	public function moveInFile ($tempName) {
		// MP3 audio only has a single version, so just store it.
		if ($this->getExtension() == 'mp3') {
			$mp3Format = $this->setPrimaryFormat(MiddMedia_File_Format_Audio_Mp3::create($this));
			$mp3Format->moveInFile($tempName);
			return;
		}
		
		// Store the temporary file in a source format, then queue for processing.
		$sourceFormat = MiddMedia_File_Format_Video_Source::create($this);
		$sourceFormat->moveInFile($tempName);
		
		$this->queueForProcessing();
		
		$this->logAction('upload');
	}
	
	/**
	 * Move an uploaded file into our file and hand any conversion if needed.
	 * 
	 * @param string $tempName
	 * @return void
	 * @access public
	 * @since 9/24/09
	 */
	public function moveInUploadedFile ($tempName) {
		// MP3 audio only has a single version, so just store it.
		if ($this->getExtension() == 'mp3') {
			$mp3Format = $this->setPrimaryFormat(MiddMedia_File_Format_Audio_Mp3::create($this));
			$mp3Format->moveInUploadedFile($tempName);
			return;
		}
		
		// Store the temporary file in a source format, then queue for processing.
		$sourceFormat = MiddMedia_File_Format_Video_Source::create($this);
		$sourceFormat->moveInUploadedFile($tempName);
		
		$this->queueForProcessing();
		
		$this->logAction('upload');
	}
	
	/**
	 * Add a new format for this media.
	 * 
	 * @param MiddMedia_File $formatFile
	 * @return MiddMedia_File The new format file
	 */
	protected function setPrimaryFormat (MiddMedia_File_FormatInterface $formatFile) {
		unlink($this->getPath());
		symlink($formatFile->getPath(), $this->getPath());
		return $formatFile;
	}
	
	/**
	 * Queue a file for conversion to mp4.
	 * 
	 * @param string $tempName
	 * @return void
	 */
	protected function queueForProcessing () {	
		$format = MiddMedia_File_Format_Video_Mp4::create($this);
		$format->putContents(file_get_contents(MYDIR.'/images/ConvertingVideo.mp4'));
		$this->setPrimaryFormat($format);
		
		$format = MiddMedia_File_Format_Image_Thumbnail::create($this);
		$format->putContents(file_get_contents(MYDIR.'/images/ConvertingVideo.jpg'));
		
		$format = MiddMedia_File_Format_Image_FullFrame::create($this);
		$format->putContents(file_get_contents(MYDIR.'/images/ConvertingVideo.jpg'));
		
		$format = MiddMedia_File_Format_Image_Splash::create($this);
		$format->putContents(file_get_contents(MYDIR.'/images/ConvertingVideo.jpg'));
		
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
	 * @since 9/25/09
	 */
	protected function removeFromQueue () {
		$dbMgr = Services::getService("DatabaseManager");
		
		// Delete any temporary files
		foreach ($this->getFormats() as $format) {
			$format->cleanup();
// 			$format->makeError();
		}
		// Delete the source file
		try {
			$this->getFormat('source')->delete();
		} catch (InvalidArgumentException $e) {
			// Ignore if the file was already deleted (78345)
			if ($e->getCode() != 78345)
				throw $e;
		}
		
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
	 */
	protected function process () {
		$source = $this->getFormat('source');
		
		
		// Convert our video formats from the source format
		$mp4 = $this->getFormat('mp4');
		$mp4->process($source);
		
		// $this->getFormat('webm')->process($source);
		
		
		// Generate our image formats from the mp4
		$fullFrame = $this->getFormat('full_frame');
		$fullFrame->process($mp4);
		
		$this->getFormat('thumb')->process($fullFrame);
		$this->getFormat('splash')->process($fullFrame);
		
		
		// Clean up
		$source->delete();
		$this->logAction('processed');
	}
	
	/**
	 * Delete the file.
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function delete () {
		foreach ($this->getFormats() as $format) {
			$format->delete();
		}
		$this->removeFromQueue();
		parent::delete();
		
		$query = new DeleteQuery;
		$query->setTable('middmedia_metadata');
		$query->addWhereEqual('directory', $this->directory->getBaseName());
		$query->addWhereEqual('file', $this->getBaseName());
		
		$dbMgr = Services::getService("DatabaseManager");
		$dbMgr->query($query, HARMONI_DB_INDEX);
		
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
	 * Answer our directory.
	 * 
	 * @return MiddMedia_DirectoryInterface
	 */
	public function getDirectory () {
		return $this->directory;
	}
	
	/**
	 * Answer a format of this media file
	 * 
	 * @param string $format
	 * @return MiddMedia_File_FormatInterface
	 */
	public function getFormat ($format) {
		switch ($format) {
			case 'source':
				return new MiddMedia_File_Format_Video_Source($this);
			case 'mp4':
				return new MiddMedia_File_Format_Video_Mp4($this);
			case 'webm':
				return new MiddMedia_File_Format_Video_WebM($this);
			case 'mp3':
				return new MiddMedia_File_Format_Audio_Mp3($this);
			case 'thumb':
				return new MiddMedia_File_Format_Image_Thumbnail($this);
			case 'splash':
				return new MiddMedia_File_Format_Image_Splash($this);
			case 'full_frame':
				return new MiddMedia_File_Format_Image_FullFrame($this);
			default:
				throw new InvalidArgumentException("Unsupported format '$format'.");			
		}
	}
	
	/**
	 * Answer all of our formats.
	 * 
	 * @return array of MiddMedia_File_FormatInterface
	 */
	protected function getFormats () {
		$formatIds = array('source', 'mp4', 'mp3', 'thumb', 'splash', 'full_frame');
		$formats = array();
		foreach ($formatIds as $id) {
			try {
				$formats[] = $this->getFormat($id);
			} catch (InvalidArgumentException $e) {
			}
		}
		return $formats;
	}
	
	/**
	 * Answer the primary format of this media file
	 * 
	 * @return MiddMedia_File_FormatInterface
	 */
	public function getPrimaryFormat () {
		if (!is_link($this->getPath()))
			throw new Exception("Expecting a link at ".$this->getDirectory()->getBaseName().'/'.$this->getBaseName());
		
		$target = readlink($this->getPath());
		$format = basename(dirname($target));
		return $this->getFormat($format);
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
			$splashUrl = $this->getFormat('splash')->getHttpUrl();
		} catch (Exception $e) {
			$splashUrl = '';
		}
		
		$primaryFormat = $this->getPrimaryFormat();
		
		$code = str_replace('###ID###', $myId, $code);
		$code = str_replace('###HTML_ID###', 'media_'.preg_replace('/[^a-z0-9_-]/i', '', $myId), $code);-
		$code = str_replace('###HTTP_URL###', $primaryFormat->getHttpUrl(), $code);
		if ($primaryFormat->supportsRtmp())
			$code = str_replace('###RTMP_URL###', $primaryFormat->getRtmpUrl(), $code);
		else
			$code = str_replace('###RTMP_URL###', '', $code);
		$code = str_replace('###SPLASH_URL###', $splashUrl, $code);
		
		return $code;
	}
}

?>