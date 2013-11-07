<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class VinModelController extends EntityModelController {
	
	public $entity_model;
	
	public function run($id){
		$this->entity_model = new EntityModel($id);
		
		// 1. Récupérer la liste des Régions
		$appellations = Attribute::getValues(3, $this->cookie->id_lang, true);
		
		$this->smarty->assign( array(
			'appellations' => $appellations
		));
		
		$this->getSEO();
		$this->display();
	}
	
	public function display(){
		$active_entity = array();
		array_push($active_entity, array(
			'id_entity_model' => $this->entity_model->id_entity_model
		));
		$this->smarty->assign(array(
			'active_entity' => $active_entity
		));
		
		$tpls = $this->entity_model->getTemplates();
		$this->sendTemplatesToSmarty( $tpls );		
	}
	
}