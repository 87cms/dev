<?php

class Mhsuggestions extends Module implements ModuleInterface {
	
	public $name = "Mhsuggestions";
	public $description = "Gestion des suggestions sur le site";
	public $menu = "Gestion des suggestions";
	public $slug = "hsuggestions";
	
	public function start(){
			
	}	
	
	public function displayAdmin(){
		parent::displayAdmin();	
		
		
		if( Tools::getValue('saveForm') ){
			
			foreach( $_POST as $key => $value ){
				if( is_int($key) ){
					
					$raw_value = '';
					foreach( $_POST[ $key ] as $id_entity )
						$raw_value .= $id_entity.',';
					
					Db::getInstance()->UpdateDB(
						_DB_PREFIX_.'entity_field', 
						array( 'raw_value' => $raw_value ), 
						array(
							'id_entity' => $key,
							'id_field_model' => 31
						)
					);
					
				}
			}
			
		}
		
		
		$domaines = Entity::getEntitiesList(1, $this->cookie->id_lang_admin, NULL, 'ORDER BY L.meta_title ASC');
		
		foreach( $domaines as &$domaine ){
			
			$d = new Entity( $domaine['id_entity'] );
			$domaine['vins'] = 	$d->getChildren($this->cookie->id_lang_admin, 0, 0, 'ORDER BY L.meta_title ASC', 2);
			
			foreach( $domaine['vins'] as &$vin ){
				$raw_value = EntityField::getRawValue(31, $vin['id_entity']);
				$raw_value = rtrim($raw_value, ',');	
				$vin['linkedEntities'] = explode(',', $raw_value);
			}
			
		}
		
		$vins = Entity::getEntitiesList(2, $this->cookie->id_lang_admin, NULL, 'meta_title');
		foreach( $vins as &$vin ){
			$tmpE = new Entity($vin['id_entity']);
			$vin['fields']	= $tmpE->getData($this->cookie->id_lang_admin);
			$tmpE = new Entity($vin['id_default_parent']);
			$tmpE->fields = $tmpE->getData($this->cookie->id_lang_admin);
			$vin['domaine'] = $tmpE->fields['nom_domaine'];			
		}
		
		$this->smarty->assign(array(
			'vins' => $vins,
			'domaines' => $domaines
		));
			
		$this->smarty->display('../modules/mhsuggestions/admin.html');
	}
	
	public function installModule(){
		
	}
	
	public function uninstallModule(){
		
	}
	
}




