<?php
/**
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

require_once(dirname(__FILE__).'/browse.act.php');

/**
 * Encode missing formats.
 *
 * @package middmedia
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class encode_missingAction
	extends MainWindowAction
{

	/**
	 * Check Authorizations
	 *
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}

	/**
	 * Exectute
	 *
	 * @return void
	 * @access public
	 * @since 12/11/08
	 */
	public function buildContent () {
		try {
			$manager = MiddMedia_Manager_Admin::forSystemUser();
			foreach ($manager->getSharedDirectories() as $dir) {
				print $dir->getBaseName()."/\n";
				foreach ($dir->getFiles() as $media) {
					$baseFormat = $media->getPrimaryFormat();
					print "\t".$baseFormat->getBaseName()."\n";

					// Skip audio, just do video and Skip videos that are queued for processing.
					if (strpos($media->getMimeType(), 'video/') === 0 && $media->getQueueInfo() === FALSE) {
						// Ensure that we have an mp4 format (in case of a flash source);
						if (!$media->hasFormat('mp4')) {
							try {
								$format = MiddMedia_File_Format_Video_Mp4::create($media);
								print "\t\t=> ".$format->getBaseName()."\n";
								$this->flush();
								$format->process($baseFormat, '480p');
							} catch (OperationFailedException $e) {
								$format->delete();
								print $e->getMessage();
							}
						}

						// Ensure that we have a webm format.
						if (!$media->hasFormat('webm')) {
							try {
								$format = MiddMedia_File_Format_Video_WebM::create($media);
								print "\t\t=> ".$format->getBaseName()."\n";
								$this->flush();
								$format->process($baseFormat, 'original');
							} catch (OperationFailedException $e) {
								$format->delete();
								print $e->getMessage();
							}
						}
					}
				}
			}
			print "Done\n";
			exit;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Flush all buffers.
	 *
	 * @return void
	 */
	private function flush () {
		while (ob_get_level())
			ob_end_flush();
		flush();
	}

}
