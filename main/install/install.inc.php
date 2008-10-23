<?php
/**
 * @package concerto.install
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/*********************************************************
 * Please note that this install script should be included
 * by the config
 * 		- After the HierarchyManager has been started
 * 		- Before the RepositoryManager is started
 * This script will print out a series of values that you 
 * will then need to copy to your config.
 *********************************************************/

if (!isset($_SESSION['table_setup_complete'])) {
	
	/*********************************************************
	 * Check for existing data in the database
	 *********************************************************/
	$dbHandler = Services::getService("DatabaseManager");
	$tables = $dbHandler->getTableList(HARMONI_DB_INDEX);

	if (count ($tables)) {
		$_SESSION['table_setup_complete'] = TRUE;
// 		RequestContext::locationHeader($_SERVER['REQUEST_URI']);
// 		
// 		print "<h2>Tables exist in the database. Not creating tables.</h2>";
// 		print "<h2>If you have just run the installer, comment out it's line in the config to start using the system.</h2>";
// 		exit;
	} else {
	
		print "<h1>Creating tables and default data set.</h1>";
		
		/*********************************************************
		 * Create the needed database tables
		 *********************************************************/
		$dbHandler->beginTransaction(HARMONI_DB_INDEX);
		switch ($dbHandler->getDatabaseType(HARMONI_DB_INDEX)) {
			case MYSQL:
				SQLUtils::runSQLdir(HARMONI_BASE."/SQL/MySQL", HARMONI_DB_INDEX);
				break;
			case POSTGRESQL:
				try {
					SQLUtils::runSQLdir(HARMONI_BASE."/SQL/PostgreSQL", HARMONI_DB_INDEX);
				} catch (QueryDatabaseException $e) {
					if (preg_match('/function .+ does not exist/i', $e->getMessage()))
						throw new Exception ($e->getMessage().". You must first run the Harmoni SQL file, <a href='main/harmoni/SQL/PostgreSQL/000_DigitalRepository.sql.run_manually'>example_skeleton/main/harmoni/SQL/PostgreSQL/000_DigitalRepository.sql.run_manually</a> on your database as a superuser to install plpgsql and the necessary trigger functions.");
					else
						throw $e;
				}
				break;
			case ORACLE:
				SQLUtils::runSQLdir(HARMONI_BASE."/SQL/PostgreSQL", HARMONI_DB_INDEX);
				break;
			default:
				throw new Exception("Database schemas are not defined for specified database type.");
		}
		
		
		/*********************************************************
		 * Script for setting up the Authorization Hierarchy
		 *********************************************************/
				$hierarchyManager = Services::getService("HierarchyManager");
				$idManager = Services::getService("IdManager");
				
				// Create the Hierarchy
				$nodeTypes = array();
				$authorizationHierarchyId =$idManager->getId("edu.middlebury.authorization.hierarchy");
				$authorizationHierarchy =$hierarchyManager->createHierarchy(
					"Qualifier Hierarchy", 
					$nodeTypes,
					"A Hierarchy to hold all Qualifiers known.",
					TRUE,
					FALSE,
					$authorizationHierarchyId);
		
				// Create nodes for Qualifiers
				$allOfId =$idManager->getId("edu.middlebury.authorization.root");
				$authorizationHierarchy->createRootNode($allOfId, new DefaultQualifierType, "Root", "The top level of the system.");
				
		
		
		/*********************************************************
		 * Script for setting up the RepositoryManager Hierarchy
		 *********************************************************/	
				// Create nodes for Qualifiers
				$collectionsId =$idManager->getId("edu.middlebury.repositories_root");
				$authorizationHierarchy->createNode($collectionsId, $allOfId, new DefaultQualifierType, "Repositories", "All Repositories in the system.");
		
		/*********************************************************
		 * Script for setting up the AgentManager Hierarchy
		 *********************************************************/	
				// Create nodes for Qualifiers
				$systemAgentType = new Type ("Agents", "edu.middlebury.harmoni", "System", "Agents/Groups required by the Agent system.");
				
				$everyoneId =$idManager->getId("edu.middlebury.agents.everyone");
				$authorizationHierarchy->createNode($everyoneId, $allOfId, $systemAgentType, "Everyone", "All Agents and Groups in the system.");
				
				$allGroupsId =$idManager->getId("edu.middlebury.agents.all_groups");
				$authorizationHierarchy->createNode($allGroupsId, $everyoneId, $systemAgentType, "All Groups", "All Groups in the system.");
				
				$allAgentsId =$idManager->getId("edu.middlebury.agents.all_agents");;
				$authorizationHierarchy->createNode($allAgentsId, $everyoneId, $systemAgentType, "All Agents", "All Agents in the system.");
				
				
				if (file_exists(MYDIR.'/config/agent.conf.php'))
					require_once (MYDIR.'/config/agent.conf.php');
				else
					require_once (MYDIR.'/config/agent_default.conf.php');
			
				// The anonymous Agent
				$agentManager = Services::getService("AgentManager");
				$anonymousId =$idManager->getId("edu.middlebury.agents.anonymous");
				require_once(HARMONI."oki2/shared/NonReferenceProperties.class.php");
				$agentProperties = new NonReferenceProperties($systemAgentType);
				$agentManager->createAgent("Anonymous", $systemAgentType, $agentProperties, $anonymousId);
		
		/*********************************************************
		 * Script for setting up some default Groups and Users
		 *********************************************************/
				$agentManager = Services::getService("AgentManager");
				$idManager = Services::getService("IdManager");			
		
				$groupType = new Type ("System", "edu.middlebury.harmoni", "SystemGroups", "Groups for administrators and others with special privileges.");
				$nullType = new Type ("System", "edu.middlebury.harmoni", "NULL");
				$properties = new HarmoniProperties($nullType);
				$adminGroup =$agentManager->createGroup("Administrators", $groupType, "Users that have access to every function in the system.", $properties);
				$auditorGroup =$agentManager->createGroup("Auditors", $groupType, "Users that can view all content in the system but not modify it.", $properties);
				
				
				// default administrator account
				$authNMethodManager = Services::getService("AuthNMethodManager");
				$dbAuthType = new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB");
				$dbAuthMethod =$authNMethodManager->getAuthNMethodForType($dbAuthType);
				// Create the representation
				
				$tokensArray = array("username" => "jadministrator",
								"password" => "password");
					
				$adminAuthNTokens =$dbAuthMethod->createTokens($tokensArray);
				// Add it to the system
				$dbAuthMethod->addTokens($adminAuthNTokens);
				
				// Create an agent
				$agentType = new Type ("System", "edu.middlebury.harmoni", "Default Agents", "Default agents created for install and setup. They should be removed on production systems.");
				require_once(HARMONI."oki2/shared/NonReferenceProperties.class.php");
				$agentProperties = new NonReferenceProperties($agentType);
				$agentProperties->addProperty("name", "Administrator, John");
				$agentProperties->addProperty("first_name", "John");
				$agentProperties->addProperty("last_name", "Administrator");
				$agentProperties->addProperty("email", "jadministrator@xxxxxxxxx.edu");
				$agentProperties->addProperty("status", "Not a real person.");
				$adminAgent =$agentManager->createAgent("John Administrator", $agentType, $agentProperties);
				
	
				// map the agent to the tokens
				$agentTokenMappingManager = Services::getService("AgentTokenMappingManager");
				$agentTokenMappingManager->createMapping($adminAgent->getId(), $adminAuthNTokens, $dbAuthType);
				
				// Add the agent to the Administrators group.
				$adminGroup->add($adminAgent);
				
				if (defined('ENABLE_DWARVES') && ENABLE_DWARVES) {
					$dwarfGroup =$agentManager->createGroup("Dwarves", $groupType,
						"Test users with varying privileges", $properties);
						
					$arrayOfTokens = array();
					$arrayOfTokens[] = array("username" => "sleepy",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "doc",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "bashful",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "dopey",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "sneezy",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "grumpy",
						"password" => "disney");
					$arrayOfTokens[] = array("username" => "happy",
						"password" => "disney");
						
					$dwarfAuthNTokens = array();
					foreach ($arrayOfTokens as $key => $tokenArray) {
						$dwarfAuthNTokens[$key] =$dbAuthMethod->createTokens($tokenArray);
					// Add it to the system
						$dbAuthMethod->addTokens($dwarfAuthNTokens[$key]);
					}
				
					// the last 3 steps for the dwarves
					foreach ($arrayOfTokens as $key => $tokens) {
						$dwarfProperties = new NonReferenceProperties($agentType);
						$dwarfProperties->addProperty("name",
							$tokens['username']);
						$dwarfProperties->addProperty("first_name",
							$tokens['username']);
						$dwarfProperties->addProperty("email",
							$tokens['username']."@xxxxxxxxx.edu");
						$dwarfProperties->addProperty("status",
							"Not a real Dwarf");
						$dAgent =$agentManager->createAgent(
							$tokens['username'], $agentType, $dwarfProperties);
						$agentTokenMappingManager->createMapping($dAgent->getId(),
							$dwarfAuthNTokens[$key], $dbAuthType);
						$dwarfGroup->add($dAgent);
					}
				}
		
		/*********************************************************
		 * Script for setting up the AuthorizationFunctions that  will use
		 *********************************************************/
		 
				 if (file_exists(MYDIR.'/config/authorization.conf.php'))
					require_once (MYDIR.'/config/authorization.conf.php');
				else
					require_once (MYDIR.'/config/authorization_default.conf.php');
					
					
				$authZManager = Services::getService("AuthorizationManager");
				$idManager = Services::getService("IdManager");
				$qualifierHierarchyId =$authorizationHierarchyId; // Id from above
				
				
			// View/Use Functions
				$type = new Type ("Authorization", "edu.middlebury.harmoni", "View/Use", "Functions for viewing and using.");
			
				$id =$idManager->getId("edu.middlebury.authorization.access");
				$function =$authZManager->createFunction($id, "Access", "Access a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.view");
				$function =$authZManager->createFunction($id, "View", "View a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.comment");
				$function =$authZManager->createFunction($id, "Comment", "Comment on a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				
			// Editing Functions
				$type = new Type ("Authorization", "edu.middlebury.harmoni", "Editing", "Functions for editing.");
			
				$id =$idManager->getId("edu.middlebury.authorization.modify");
				$function =$authZManager->createFunction($id, "Modify", "Modify a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.delete");
				$function =$authZManager->createFunction($id, "Delete", "Delete a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.add_children");
				$function =$authZManager->createFunction($id, "Add Children", "Add children to this qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.remove_children");
				$function =$authZManager->createFunction($id, "Remove Children", "Remove children from this qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				
			// Administration Functions
				$type = new Type ("Authorization", "edu.middlebury.harmoni", "Administration", "Functions for administering.");
			
				$id =$idManager->getId("edu.middlebury.authorization.view_authorizations");
				$function =$authZManager->createFunction($id, "View Authorizations", "View Authorizations at a qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
				
				$id =$idManager->getId("edu.middlebury.authorization.modify_authorizations");
				$function =$authZManager->createFunction($id, "Modify Authorizations", "Modify Authorizations at qualifier.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
			// Administration Functions
				$type = new Type ("Authorization", "edu.middlebury.harmoni", "User Administration", "Functions for administering users.");
	
				$id =$idManager->getId("edu.middlebury.authorization.create_agent");
				$function =$authZManager->createFunction($id, "Create Agents", "Add Agents to the system.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
				$id =$idManager->getId("edu.middlebury.authorization.delete_agent");
				$function =$authZManager->createFunction($id, "Delete Agents", "Remove Agents from the system.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
				$id =$idManager->getId("edu.middlebury.authorization.modify_agent");
				$function =$authZManager->createFunction($id, "Modify Agents", "Modify Agent properties.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
			// Administration Functions
				$type = new Type ("Authorization", "edu.middlebury.harmoni", "Group Administration", "Functions for administering groups.");
	
				$id =$idManager->getId("edu.middlebury.authorization.create_group");
				$function =$authZManager->createFunction($id, "Create Groups", "Add Groups to the system.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
				$id =$idManager->getId("edu.middlebury.authorization.delete_group");
				$function =$authZManager->createFunction($id, "Delete Groups", "Remove Groups from the system.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);
	
				$id =$idManager->getId("edu.middlebury.authorization.modify_group_membership");
				$function =$authZManager->createFunction($id, "Modify Group Membership", "Modify Group membership.", $type, $qualifierHierarchyId);
				$authZManager->createAuthorization($adminGroup->getId(), $function->getId(), $allOfId);	
		$dbHandler->commitTransaction(HARMONI_DB_INDEX);
	
	// 	print "\n<br> ...done";
		$_SESSION['table_setup_complete'] = TRUE;
		
		RequestContext::locationHeader($_SERVER['REQUEST_URI']);
	}
}
?>