<?php


class EntityController extends AdminController {
	
	
	
	public function run(User $user){
		
		$this->user = $user;
		
		$this->action = Tools::getSuperglobal('action');
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "form" )
				$this->displayForm( (int)Tools::getSuperglobal('id_entity_model') );
			
			if( $this->action == "submitEntity")
				$this->addEntity();
			
			if( $this->action == "deleteEntity")
				$this->deleteEntity(Tools::getSuperglobal('id_entity'));
			
		}else{
			
			$model = new EntityModel(Tools::getSuperglobal('id_entity_model'));
			$fields = array_slice($model->getFieldsList($this->cookie->id_lang_admin), 0, 5);
			
			$parents = $model->getHierarchicTree(0, $this->cookie->id_lang_admin);
			$parent_model = new EntityModel( $model->id_parent );
			
			$id_parent = ( Tools::getValue('id_parent') !== false && Tools::getValue('id_parent')>=0 ? (int)Tools::getValue('id_parent') : '' );
			if( $id_parent !== '' )
				$this->smarty->assign('id_parent', $id_parent);
			else
				$id_parent = false;
			
			$this->smarty->assign(array(
				'parents' => $parents,
				'parent_name' => $parent_model->lang[$this->cookie->id_lang_admin]['name'],
				'hierarchic' => $model->hierarchic,
				'modelname' => $model->lang[$this->cookie->id_lang_admin]['name'],
				'id_entity_model' => (int)Tools::getSuperglobal('id_entity_model')
			));
			
			$nbentities = 0;
			
			$user = false;
			$all_access = $this->user->userHasPermission( $model->id_entity_model, 0 ); 
			if( !$all_access && !$this->user->is_admin )
				$user = $this->user;
			else $user = NULL;
			
			if( $model->hierarchic ){
				
				$entities = Entity::getHierarchicEntitiesList(
					Tools::getSuperglobal('id_entity_model'), 
					$this->cookie->id_lang_admin,
					$id_parent,
					Tools::getValue('sort'), 
					Tools::getValue('page'), 
					100,
					true,
					$user
				);
				
				$nbentities = count($entities);
				
			}else{
				$n=10;
				if( $id_parent !== '' )
					$n = 9999;
				$entities = Entity::getEntitiesList(
					Tools::getSuperglobal('id_entity_model'), 
					$this->cookie->id_lang_admin,
					$id_parent,
					Tools::getValue('sort'), 
					Tools::getValue('page'), 
					$n,
					true,
					$user
				);
				
				$nbentities = Entity::countEntities(
					Tools::getSuperglobal('id_entity_model'), 
					$this->cookie->id_lang_admin,
					$id_parent,
					Tools::getValue('sort'),
					true,
					$user
				);
				
				foreach( $entities as &$entity ){
					$e = new Entity( $entity['id_entity']);
					$id_default_parent = $e->getDefaultParent();
					$entity['parent_name'] = Entity::getDisplayName($id_default_parent, $this->cookie->id_lang_admin);
				}
			}

			$this->smarty->assign(array(
				'nbentities' => $nbentities,
				'item_per_page' => 10,
				'entities' => $entities,
				'model' => $model
			));
			
			$this->smarty->display('entity.html');
		
		}	
		
	}
	
	
	
	public function displayForm($id_entity_model=0){
		
		$model = new EntityModel($id_entity_model);
		
		if( $model ){
			
			$this->smarty->assign(array(
				'modelname' => $model->lang[$this->cookie->id_lang_admin]['name'],
				'model' => $model,
				'id_entity_model' => (int)$id_entity_model
			));
			
			if( !$model->hierarchic && $model->id_parent ){
				$parents = $model->getHierarchicTree(0, $this->cookie->id_lang_admin);
			}elseif( $model->hierarchic && !$model->id_parent ){
				$parents = $model->getHierarchicTree(0, $this->cookie->id_lang_admin);
			}
			
			$fields = $model->getFieldsList($this->cookie->id_lang_admin);
			
			foreach( $fields as &$field ){
				
				if( $field['type'] == 'select' ){
					$field['attributes'] = Attribute::getValues($field['params'][0]['value'], $this->cookie->id_lang_admin);	
				}
				
				if( $field['type'] == "linkedEntities" ){
					$field['entities'] = Entity::getEntitiesList($field['params'][0]['value'], $this->cookie->id_lang_admin, NULL, 'meta_title', 0, 10000, true);
				}
				
			}
			
			$this->initRichtext();
			
			$entity = NULL;
			
			if( Tools::getSuperGlobal('id_entity') > 0 ){
				$entity = new Entity( Tools::getSuperGlobal('id_entity') );
	
				if( $entity->id_entity <= 0 )
					return false;
				
				foreach( $fields as &$field ){
					$field['values'] = EntityField::getFieldValues( $field['id_field_model'], Tools::getSuperGlobal('id_entity') );
					$field['raw_value'] = EntityField::getRawValue( $field['id_field_model'], Tools::getSuperGlobal('id_entity') );
					
					
					if( $field['type'] == "linkedEntities")
					{
						$tmp = explode(",", $field['raw_value']);
						if(count($tmp)>0)
						{
							$field['tab_value'] = $tmp;
							
							if(count($field['entities'])>0)
							{
								foreach($field['entities'] as $num => $entitytmp)
								{
									if(in_array($entitytmp["id_entity"], $field['tab_value']))
									{
										$field['entities'][$num]["selected"] = 1;
									}
								}
							}
							
						}
					}
					
					
					
					if( $field['type'] == "checkbox")
					{
						$tmp = explode(",", $field['raw_value']);
						if(count($tmp)>0)
						{
							$field['tab_value'] = $tmp;
							
							if(count($field['attributes'])>0)
							{
								foreach($field['attributes'] as $num => $attributtmp)
								{
									if(in_array($attributtmp["id_attribute_value"], $field['tab_value']))
									{
										$field['attributes'][$num]["checked"] = 1;
									}
								}
							}
							
						}
					}
					
					
				}
				
			}
			
			$this->smarty->assign(array(
				'entity' => $entity,
				'fields' => $fields,
				'parents' => $parents
			));
			
			$all_access = $this->user->userHasPermission( $model->id_entity_model, 0 ); 
			if( !$all_access && !$this->user->is_admin ){
				$access = $this->user->userHasPermission( $model->id_entity_model, $entity->id_entity );
				if( !$access ) Tools::redirect('/admin/index.php');
			}			
			
			$this->smarty->display('entity_form.html');
		
		}
		
	}
	
	public function initRichtext(){
		// 1. get elements
		$elements = array();
		$dir = "js/richtext/elements";
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			
			if( is_dir($dir.'/'.$filename) ){
				$js = ( file_exists($dir.'/'.$filename.'/'.$filename.'.js') ? $dir.'/'.$filename.'/'.$filename.'.js' : false );
				$icon = ( file_exists($dir.'/'.$filename.'/'.$filename.'.png') ? $dir.'/'.$filename.'/'.$filename.'.png' : false );
				
				$lang_file = $dir.'/'.Lang::getLangCode($this->cookie->id_lang_admin).'.php';
				$lang = ( file_exists($lang_file) ? require_once($lang_file) : false );;
				
				if( $js ) {
					$elements[] = array(
						'js' => $js,
						'icon' => $icon,
						'name' => $filename,
					);
				}
				
			}
		}
		
		$richtext_current_id_lang = (int)$this->cookie->id_lang_admin;
		$richtext_current_lang = Lang::getLangCode($this->cookie->id_lang_admin);
		
		if( Tools::getValue('richtext_id_lang') ){
			$lang_code = Lang::getLangCode(Tools::getValue('richtext_id_lang'));
			if( $lang_code ){
				$richtext_current_id_lang = (int)Tools::getValue('richtext_id_lang');
				$richtext_current_lang = $lang_code;
			}
		}
		
		$this->smarty->assign(array(
			'elements' => $elements,
			'richtext_current_id_lang' => $richtext_current_id_lang,
			'richtext_current_lang' => $richtext_current_lang
		));		
	}
	
	
	public function deleteEntity($id_entity){
		$entity = new Entity($id_entity);
		$entity->delete();
		
		Tools::redirect('/admin/index.php?p=entity&id_entity_model='.Tools::getSuperGlobal('id_entity_model'));
	}
	
	
	public function preprocess(){ }

}