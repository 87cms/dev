<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class DomaineModelController extends EntityModelController {
	
	public $entity_model;
	
	public function run($id){
		$this->entity_model = new EntityModel($id);
		
		// 1. Récupérer la liste des Régions, trié du Sud au Nord
		$regions = array();
		$regions[0] = array(
			"id_attribute_value" => 8,
			"id_lang" => 1,
			"value"=> "La Côte Mâconnaise"
		);
		$regions[1] = array(
			"id_attribute_value" => 7,
			"id_lang" => 1,
			"value"=> "La Côte Chalonnaise"
		);
		$regions[2] = array(
			"id_attribute_value" => 6,
			"id_lang" => 1,
			"value"=> "La Côte de Beaune"
		);
		$regions[3] = array(
			"id_attribute_value" => 5,
			"id_lang" => 1,
			"value"=> "La Côte de Nuits"
		);
		$regions[4] = array(
			"id_attribute_value" => 9,
			"id_lang" => 1,
			"value"=> "Le Chablisien"
		);
		$liste_regions = $regions;
		
		// 2. Récupérer listes des domaines en fonction de la valeur d'attributs
		foreach( $regions as &$region ){
			$domaines = Entity::getEntitiesListWithAttributeValue($this->entity_model->id_entity_model, $region['id_attribute_value'], $this->cookie->id_lang, true);	
			$region['domaines']	= $domaines;
		}
		
		$this->smarty->assign( array(
			'liste_regions' => $liste_regions,
			'regions' => $regions
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