<?php

class Treemenu extends Module implements ModuleInterface {
	
	public $name = "Tree menu";
	public $description = "Display the tree of a hierarchic entity.";
	
	public function start(){
			
	}	
	
	public function displayHookTreeMenu(){
		
		global $smarty, $cookie;
		
		$entitiesList = Entity::getHierarchicEntitiesList(Config::getConfig('MODULE_TREEMENU_MODEL'), $cookie->id_lang, 0, 0, 0, 0);
		
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
		$smarty->display('modules/treemenu/treemenu.html');
		
	}

	
	public function displayAdmin(){
		 
		 parent::displayAdmin();	
		 
		 /** Your code below **/
		 
		 if( Tools::getValue('submitConfiguration') )
			Config::setConfig('MODULE_TREEMENU_MODEL', (int)Tools::getValue('id_model') ); 
		 
		 
		 $models = EntityModel::getModels($this->cookie->id_lang, 1);
		 $this->smarty->assign(array(
			 'models' => $models,
			 'id_tree_model' => Config::getConfig('MODULE_TREEMENU_MODEL')
		 ));
		 
		 $this->smarty->display('../modules/treemenu/admin.html');
		
	}
	
	
}




