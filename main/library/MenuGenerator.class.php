<?php

/**
 * The MenuGenerator class is a static class used for the generation of Menus.
 * @package example_application.display
 * @author Adam Franco
 * @access public
 * @version $Id$
 */

class MenuGenerator {

	/**
	 * Generates a menu layout based on the current action.
	 * @param string $actionString A dotted-pair action string of the form
	 *		"module.action" .
	 * @return object MenuLayout
	 */
	static function generateMainMenu($harmoni) {
		
		$harmoni = Harmoni::instance();
		
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		
		$mainMenu = new Menu(new YLayout(), 1);

	// :: Home ::
		$mainMenu->add(
			 new MenuItemLink(
				"<span style='font-size: large'>"._("Home")."</span>", 
				$harmoni->request->quickURL("example", "home"), 
				($module == "example" && $action == "home")?TRUE:FALSE, 1), 
			"100%", null, LEFT, CENTER);
		
		
	// :: Docs ::
		$mainMenu->add(
			new MenuItemLink(
				"<span style='font-size: large'>"._("Documentation")."</span>", 
				$harmoni->request->quickURL("docs","index"),
				($module == "docs" && $action == "index")?TRUE:FALSE, 1),
			"100%", null, LEFT, CENTER);
		
		//Documentation links to show if we are in the documentation module
		if ($module == "docs") {
			
			// :: Manual :: 
			$mainMenu->add(
				new MenuItemLink(
					"<span style='font-size: smaller'>"._("Harmoni Manual")."</span>", 
					"http://harmoni.sourceforge.net/wiki/index.php/Main_Page", 
					FALSE, 1),
				"100%", null, LEFT, CENTER);
			
			// :: PHPDoc :: 
			$mainMenu->add(
				new MenuItemLink(
					"<span style='font-size: smaller'>"._("Harmoni PHPDoc")."</span>", 
					"http://harmoni.sourceforge.net/harmoniDoc/phpdoc/", 
					FALSE, 1),
				"100%", null, LEFT, CENTER);
		}
	
	// :: No_theme ::
		$mainMenu->add( 
			new MenuItemLink(
				"<span style='font-size: large'>"._("No Theme")."</span>", 
				$harmoni->request->quickURL("example","no_theme"), 
				($module == "example" && $action == "no_theme")?TRUE:FALSE, 1),
			"100%", null, LEFT, CENTER);
		
	
		return $mainMenu;
	}
}

?>