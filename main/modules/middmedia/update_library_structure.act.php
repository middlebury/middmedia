<?php
/**
 * @since 12/10/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

require_once(dirname(__FILE__).'/browse.act.php');

/**
 * Browse all media as an admin
 *
 * @since 12/10/08
 * @package middmedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class update_library_structureAction
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
		// Only allow running from the command line.
		if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
			return true;
		} else {
			return false;
		}
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
			$directories = $manager->getSharedDirectories();
			foreach ($directories as $directory) {
				print "\nChecking ".$directory->getBaseName();

				foreach ($this->getFiles($directory) as $file) {
					if (!is_link($file->getPath())) {
						$origPath = $file->getPath();

						// Move Mp4 files into place and replace with symbolic links.
						if ($file->getExtension() == 'mp4') {
							print "\n\tUpdating ".$origPath;
							$dest = MiddMedia_File_Format_Video_Mp4::create($file);
							$dest->moveInFile($file->getPath());
							symlink($dest->getPath(), $origPath);
						}

						// Move Mp3 files into place and replace with symbolic links.
						if ($file->getExtension() == 'mp3') {
							print "\n\tUpdating ".$origPath;
							$dest = MiddMedia_File_Format_Audio_Mp3::create($file);
							$dest->moveInFile($file->getPath());
							symlink($dest->getPath(), $origPath);
						}

						// Move FLV files into their new sub-directory.
						// Then, add a copy of the flv files as a source file and
						// queue for conversion to mp4.
						if ($file->getExtension() == 'flv') {
							print "\n\tUpdating ".$origPath;

							// Create a copy of our FLV in the flv subdirectory.
							$flvDest = MiddMedia_File_Format_Video_Flv::create($file);
							$flvDest->copyInFile($file->getPath());

							// Move in the current FLV file as the source of a new mp4 file and queue for conversion.
							try {
								$newMediaFile = MiddMedia_File_Media::create($directory, $file->getBaseName());
								$newMediaFile->moveInFile($origPath);
							}
							// If we have a filename-colision with an existing mp4 file, just assume
							// that they are equivalent
							catch (OperationFailedException $e) {
								print "\n\t\tmp4 file of the same name as the flv exists. Assuming they are equivalent.";

								// delete our original flv path so that we can make a symlink to it.
								unlink($origPath);
							}

							// Make a sym-link to the new FLV file so that old flowplayer embeds work.
							symlink($flvDest->getPath(), $origPath);
						}
					}
					while (ob_get_level())
						ob_end_flush();
					flush();
				}
			}

			print "\n";
			exit;
		} catch (Exception $e) {
			throw $e;
		}
	}


	/**
	 * Answer an array of the files in this directory.
	 *
	 * We need to re-implement this method, because the directory class is now filtering
	 * out FLV files.
	 *
	 * @return array of MiddMedia_File_Media objects
	 */
	public function getFiles ($directory) {
		$files = array();
		foreach (scandir($directory->getPath()) as $fname) {
			if (!is_dir($directory->getPath().'/'.$fname))
				$files[] = new MiddMedia_File_Media($directory, $fname);
		}
		return $files;
	}
}
