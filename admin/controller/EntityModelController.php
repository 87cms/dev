<?php


class EntityModelController extends AdminController {
	
	
	
	public function run(){
		
		$this->action = Tools::getSuperglobal('action');
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "form" )
				$this->displayForm( (int)Tools::getSuperglobal('id_entity_model') );
			
			if( $this->action == "delete" ){
				$entity_model = new EntityModel( (int)Tools::getSuperglobal('id_entity_model') );
				$entity_model->delete();
				Tools::redirect('/admin/index.php?p=entityModel');
			}
			
		}else{
			
			$models = EntityModel::getModels($this->cookie->id_lang);
			$this->smarty->assign('models', $models);
			$this->smarty->display('entity_model.html');
		}	
		
	}
	
	public function displayForm($id=0){
		$parents = EntityModel::getModels($this->cookie->id_lang,1);
		
		$modelData = '';
		$attributes = '';
		if( $id > 0 ){
			$modelData = EntityModel::getModelData($id);
			$model = new EntityModel($id);
			$attributes = Attribute::getAttributesList($this->cookie->id_lang);
		}
		
		$this->smarty->assign(array(
			'parents' => $parents,
			'model' => $modelData,
			'modelobject' => $model,
			'attributes' => $attributes,
			'models' => EntityModel::getModels(),
			'id_entity_model' => intval($id)
		));
		$this->smarty->display('entity_model_form.html');
		
	}

	
	public function preprocess(){ }
	
}