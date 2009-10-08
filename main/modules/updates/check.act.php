<?php
/**
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/list.act.php');

/**
 * Check the status of a single update
 * 
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class checkAction
	extends ListAction
{
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 6/12/08
	 */
	public function buildContent () {
		$centerPane = $this->getActionRows();
		$harmoni = Harmoni::instance();
		ob_start();
		print "<a href='".$harmoni->request->quickURL('updates', 'list')."'>";
		print _("Back to full list");
		print "</a>";
		$centerPane->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, CENTER, TOP);
		parent::buildContent();
	}
	
	/**
	 * Print out the update Rows.
	 * 
	 * @return void
	 * @access protected
	 * @since 6/12/08
	 */
	protected function printUpdateRows () {
		$name = RequestContext::value("update");
		if (!in_array($name, $this->updateClasses))
			throw new UnknownIdException("No update with name '$name'.");
		
		$this->printRow($name);
	}
	
	/**
	 * Answer true if we should print "check..." links
	 * 
	 * @return boolean
	 * @access protected
	 * @since 6/12/08
	 */
	public function shouldCheckSeparate () {
		return false;
	}
}

?>