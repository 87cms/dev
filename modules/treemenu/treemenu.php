<?php

class Treemenu extends Module implements ModuleInterface {
	
	public $name = "Tree menu";
	public $description = "Display the tree of a hierarchic entity.";
	
	public $hook_name = "HOOK_TREE_MENU";
	public $method_name = "displayTreeMenu";
	
	public function start(){
			
	}	
	
	public function displayTreeMenu(){
		
		$this->initController();
		
		$entitiesList = array();
		$entitiesList = Entity::getHierarchicEntitiesList(Config::getConfig('MODULE_TREEMENU_MODEL'), $this->cookie->id_lang, 0, 0, 0, 0);
		
		$this->smarty->assign('entitiesList', $entitiesList);
		$this->smarty->display(_ABSOLUTE_PATH_.'/modules/treemenu/treemenu.html');
		
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
		 
		 $this->smarty->display(_ABSOLUTE_PATH_.'/modules/treemenu/admin.html');
		
	}
	
	
}




