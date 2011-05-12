<?php
/**
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 
 
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to middmedia's queue table
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Update002Action 
	extends Update
{
// 	/**
// 	 * @var boolean $checkSeparate;  Tell the list to check speparately since that takes a while.
// 	 * @access public
// 	 * @since 6/12/08
// 	 */
// 	public $checkSeparate = true;
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/9/07
	 */
	function getDateIntroduced () {
		return Date::withYearMonthDay(2011, 05, 12);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getTitle () {
		return _("Quality field");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getDescription () {
		return _("This update add a 'quality' field to the encoding queue.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		
		$tables = $dbc->getTableList();
		if (!in_array('middmedia_queue', $tables))
			return false;
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE middmedia_queue");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field(0) == "quality") {
				$exists = true;
				break;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return $exists;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function runUpdate () {
		if ($this->isInPlace())
			return true;
		
		$dbc = Services::getService('DatabaseManager');
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `middmedia_queue` ADD `quality` VARCHAR( 20 ) NULL DEFAULT NULL");
		try {
			$dbc->query($query);
		} catch (DatabaseException $e) {
		}		
		
		return $this->isInPlace();

	}
}

?>