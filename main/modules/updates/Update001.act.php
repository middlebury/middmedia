<?php
/**
 * @since 7/9/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to segues's segue_slot table for a location_category
 * 
 * @since 7/9/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */
class Update001Action 
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
		return Date::withYearMonthDay(2009, 10, 7);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getTitle () {
		return _("Map LDAP IDs to CAS IDs");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getDescription () {
		return _("This update will map existing LDAP IDs to CAS IDs if both the LDAP and CAS authentication methods are enabled. It should only be used in transitioning from LDAP to CAS authentication. To use this update, define a global array (named \$update001Types) of authentication types and the attributes to map to the CAS id. As well, you need to specify the CAS type via a global \$update001CasType variable. For example: 
<pre>global \$update001Types, \$update001CasType;

\$update001Types[] = array(
	'type' => new Type (\"Authentication\", \"edu.middlebury.harmoni\", \"LDAP\"),
	'cas_id_property' => 'UserID'
);
\$update001Types[] = array(
	'type' => new Type (\"Authentication\", \"edu.middlebury.harmoni\", \"Alternate LDAP\"),
	'cas_id_property' => 'UserID'
);

\$update001CasType = new Type (\"Authentication\", \"edu.middlebury.harmoni\", \"CAS\");
</pre>
");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function isInPlace () {
		return (!$this->isConfigValid());
	}
	
	/**
	 * Answer true if the configuration is good
	 * 
	 * @return boolean
	 * @access protected
	 * @since 10/8/09
	 */
	protected function isConfigValid () {
		global $update001Types, $update001CasType;
		$canRun = true;
		$authNMethodManager = Services::getService("AuthNMethodManager");
		
		// Check the source types
		if (is_array($update001Types)) {
			if (count($update001Types)) {
				foreach ($update001Types as $num => $source) {
					// Verify the type
					if (!isset($source['type'])) {
						print "\$update001Types[$num]['type'] is not set. Can't run.<br/>\n";
						$canRun = false;
					} else if ($source['type'] instanceof Type) {
						try {
							$authNMethodManager->getAuthNMethodForType($source['type']);
						} catch (UnknownTypeException $e) {
							print "\$update001Types[$num]['type'] (".$source['type']->asString().") is not an enabled type. Can't run.<br/>\n";
							$canRun = false;
						}
					} else {
						print "\$update001Types[$num]['type'] is not a Type object. Can't run.<br/>\n";
						$canRun = false;
					}
					
					// Verify the source property
					if (!isset($source['cas_id_property']) || !strlen($source['cas_id_property'])) {
						print "\$update001Types[$num]['cas_id_property'] is not set. Can't run.<br/>\n";
						$canRun = false;
					}
				}
			} else {
				print "\$update001Types has no sources. Can't run.<br/>\n";
				$canRun = false;
			}
		} else {
			print "\$update001Types is not set. Can't run.<br/>\n";
			$canRun = false;
		}
		
		// Check the destination type
		if (is_object($update001CasType)) {
			if ($update001CasType instanceof Type) {
				try {
					$authNMethodManager->getAuthNMethodForType($update001CasType);
				} catch (UnknownTypeException $e) {
					print "\$update001CasType (".$update001CasType->asString().") is not an enabled type. Can't run.<br/>\n";
					$canRun = false;
				}
			} else {
				print "\$update001CasType is not a Type object. Can't run.<br/>\n";
				$canRun = false;
			}
		} else {
			print "\$update001CasType is not set. Can't run.<br/>\n";
			$canRun = false;
		}
		
		return $canRun;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function runUpdate () {
		if (!$this->isConfigValid())
			return false;

		global $update001Types, $update001CasType;
		$mappingManager = Services::getService("AgentTokenMapping");
		$authNMethodManager = Services::getService("AuthNMethodManager");
		$casAuthNMethod = $authNMethodManager->getAuthNMethodForType($update001CasType);
		
		$existing = array();
		
		foreach ($update001Types as $num => $source) {
			$authNMethod = $authNMethodManager->getAuthNMethodForType($source['type']);
			$mappings = $mappingManager->getMappingsByType($source['type']);
			foreach ($mappings as $mapping) {
				try {
					$properties = $authNMethod->getPropertiesForTokens($mapping->getTokens());
					$casId = $properties->getProperty($source['cas_id_property']);
					$agentId = $mapping->getAgentId();
										
					if (is_null($casId)) {
						print "No ".$source['cas_id_property']." found for ".$mapping->getTokens()->getIdentifier()." (Agent = ".$mapping->getAgentId()->getIdString().") <br/>\n";
					} else {
						$casTokens = $casAuthNMethod->createTokensForIdentifier($casId);
						if ($mappingManager->mappingExists($agentId, $casTokens, $update001CasType)) {
							$existing[] = "Mapping already exists for ".$casId." (Agent = ".$mapping->getAgentId()->getIdString().") <br/>\n";
						} else {
							$mappingManager->createMapping($agentId, $casTokens, $update001CasType);
							print "Mapping created for ".$casId." (Agent = ".$mapping->getAgentId()->getIdString().") <br/>\n";
						}
					}
					
				} catch (LDAPException $e) {
					print "No user found in source for ".$mapping->getTokens()->getIdentifier()." (Agent = ".$mapping->getAgentId()->getIdString().") <br/>\n";
				}
			}
		}
		
		print "\n<hr/>\n".implode($existing);
		
		return true;
	}
}

?>