<?php

class Menu extends Module implements ModuleInterface {
	
	public $name = "Menu";
	public $description = "Add a menu on your site";
	
	public function start(){
			
	}	
	
	/*public function displaySidebar(){
		
		global $smarty, $cookie;
		
		$id_current_entity = '';
		
		$entitiesList = Entity::getHierarchicEntitiesList(1, $cookie->id_lang, 0, 0, 0, 0, name);
		$smarty->assign('entitiesList', $entitiesList);
		$smarty->display('modules/menu/menu.html');
		
		return $out;
		
	}*/

	public function displayHookCategories(){
		
		global $smarty, $cookie;
		
		//$entitiesList = Entity::getHierarchicEntitiesList(3, $cookie->id_lang, 0, 0, 0, 0, "name");
		$entitiesList = Entity::getHierarchicEntitiesList(3, $cookie->id_lang, 0, 0, 0, 0);
		$tab = array();

		if(count($entitiesList) > 0){
			foreach($entitiesList as $key => $value) {
				$tab[$key]["name"] = $value["title"];
				$tab[$key]["url"] = $value["link_rewrite"];
				$tab[$key]["id_entity"] = $value["id_entity"];
				$tab[$key]["id_entity_model"] = $value["id_entity_model"];
			}
		}

		$smarty->assign('entitiesList', $tab);
		$smarty->display('modules/menu/menuCategories.html');
		
		return $out;
		
	}

	
	public function displayAdmin(){
		 $smarty->display('../modules/menu/admin.html');
		
	}
	
	public function installModule(){
		
	}
	
	public function uninstallModule(){
		
	}
	
}




