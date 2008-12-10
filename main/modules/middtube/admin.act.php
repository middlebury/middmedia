<?php
/**
 * @since 12/10/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/admin_browse.act.php');

/**
 * Browse all media as an admin
 * 
 * @since 12/10/08
 * @package middtube
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class adminAction
	extends admin_browseAction
{
		
	
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Administer MiddMedia");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows = $this->getActionRows();
		
		$this->addToHead("\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/sorttable.js'></script> ");
		
		$this->addToHead("
		<script type='text/javascript'>
		// <![CDATA[
		
		/**
		 * Edit the quota ammount
		 * 
		 * @param DOMElement container
		 * @param string dirName
		 * @param boolean isDefault
		 * @return void
		 * @access public
		 * @since 11/20/08
		 */
		function editQuota (container, dirName, isDefault) {
			var currentQuota = new Number(container.getAttribute('sorttable_customkey'));
			var currentContents = container.innerHTML;
			
			container.innerHTML = 'Enter quota ammount or leave blank for default';
			container.appendChild(document.createElement('br'));
			
			var quota = container.appendChild(document.createElement('input'));
			quota.type = 'text';
			quota.size = 10;
			if (!isDefault)
				quota.value = currentQuota.asByteSizeString();
			
			var submit = container.appendChild(document.createElement('input'));
			submit.type = 'button';
			submit.value = 'Update';
			submit.onclick = function () {
				updateQuota(container, dirName, quota.value);
			}
			
			var cancel = container.appendChild(document.createElement('input'));
			cancel.type = 'button';
			cancel.value = 'Cancel';
			cancel.onclick = function () {
				container.innerHTML = currentContents;
			}
		}
		
		/**
		 * Update a quota value and write the new value to the container.
		 * 
		 * @param DOMElement container
		 * @param string dirName
		 * @param string newQuota
		 * @return void
		 * @access public
		 * @since 12/10/08
		 */
		function updateQuota (container, dirName, newQuota) {
			var url = Harmoni.quickUrl('middtube', 'update_quota', {
				'directory': dirName,
				'quota': newQuota
			});
			
			var req = Harmoni.createRequest();
			if (!req) {
				alert('Your browser does not support AJAX, please upgrade.');
				return;
			}
			
			req.onreadystatechange = function () {
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseXML) {
						
						var dir = req.responseXML.getElementsByTagName('directory').item(0);
						if (!dir.getAttribute('custom_quota')) {
							var quota = new Number(dir.getAttribute('default_quota'));
							var isDefault = true;
						} else {
							var quota = new Number(dir.getAttribute('custom_quota'));
							var isDefault = false;
						}
						
						container.innerHTML = '';
						container.setAttribute('sorttable_customkey', quota);
						
						if (isDefault) {
							container.innerHTML = quota.asByteSizeString() + ' ("._('Default').")' ;
						} else {
							container.innerHTML = quota.asByteSizeString();
						}
						
						var link = container.insertBefore(document.createElement('a'), container.firstChild);
						link.href = '#';
						link.className = 'middtube_quota_edit';
						link.innerHTML = '"._('edit')."';
						link.onclick = function () {
							editQuota(container, dirName, isDefault);
						}
						
					} else {
						alert(req.responseText);
					}
				}
			}
			
			req.open('GET', url, true);
			req.send(null);
		}
		
		
		// ]]>
		</script> ");
		
		ob_start();
		
		$manager = $this->getManager();
		
		print "\n<table width='100%' border='1' class='sortable'>";
		print "\n<thead>";
		print "\n\t<tr>";
		print "\n\t\t<th>"._("Directory")."</th>";
		print "\n\t\t<th>"._("Quota")."</th>";
		print "\n\t\t<th>"._("Space Used")."</th>";
		print "\n\t\t<th>"._("Space Available")."</th>";
		print "\n\t\t<th>"._("Num Files")."</th>";
		print "\n\t</tr>";
		print "\n</thead>";
		print "\n<tbody>";
		foreach ($manager->getSharedDirectories() as $dir) {
			print "\n\t<tr>";
			print "\n\t\t<td>".$dir->getBasename()."</td>";
			
			$quota = ByteSize::withValue($dir->getQuota());
			print "\n\t\t<td sorttable_customkey='".$quota->value()."'>";
			print " <a href='#' class='middtube_quota_edit' onclick=\"editQuota(this.parentNode, '".$dir->getBasename()."', ".(($dir->hasCustomQuota())?'false':'true')."); return false;\">"._("edit")."</a>";
			print $quota->asString();
			if (!$dir->hasCustomQuota())
				print ' ('._('Default').')';
			print "</td>";
			
			$bytes = ByteSize::withValue($dir->getBytesUsed());
			print "\n\t\t<td sorttable_customkey='".$bytes->value()."'>".$bytes->asString()."</td>";
			
			$bytes = ByteSize::withValue($dir->getBytesAvailable());
			print "\n\t\t<td sorttable_customkey='".$bytes->value()."'>".$bytes->asString()."</td>";
			
			print"\n\t\t<td>".count($dir->getFiles())."</td>";
			
			print "\n\t</tr>";
		}
		print "\n</tbody>";
		print "\n</table>";
		
		$actionRows->add(
				new Block(ob_get_clean(), STANDARD_BLOCK), 
				"100%", 
				null, 
				CENTER, 
				CENTER);
	}
	
}

?>