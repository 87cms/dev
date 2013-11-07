<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class DomaineController extends EntityController {
	
	public function process(){
		
		$fields = $this->entity->getData($this->cookie->id_lang);
		$this->entity->fields = $fields;
		
		//vins
		if( Tools::getValue('page') == "vins" ){
			
			// Mancey
			if( $this->entity->id_entity == 25 ){
				$children = array();
				$children['vin'] = Entity::getEntitiesListWithAttributeValue(
					2, 
					(int)Tools::getValue('catmancey'), 
					$this->cookie->id_lang, 
					$include_data=true
				);
			}
			else
				$children = $this->entity->getChildren($this->cookie->id_lang);
				
			$this->smarty->assign('vins', $children['vin']);
			$tpls = 'head.html, header_light.html, domaine_vins.html, footer.html';
			
		}
		
		// Galerie
		elseif( Tools::getValue('page') == "photos" ){
			shuffle($this->entity->fields['galerie']);
			$tpls = 'head.html, header_light.html, domaine_photos.html, footer.html';
			
		}
		
		// Galerie
		elseif( Tools::getValue('page') == "actualites" ){
			
			$children = $this->entity->getChildren($this->cookie->id_lang);
			
			$this->smarty->assign('actus', $children['revue_de_presse']);
			$tpls = 'head.html, header_light.html, domaine_actus.html, footer.html';
			
		}
		
		// Le domaine
		else {
		
			$tpls = 'head.html, header_light.html, domaine.html, footer.html';
		
		}
		
		$active_entity = array();
		array_push($active_entity, array(
			'id_entity' => $this->entity->id_entity,
			'id_entity_model' => $this->entity->id_entity_model
		));
		
		$this->smarty->assign(array(
			$this->entity->slug => $this->entity,
			'active_entity' => $active_entity
		));
				
		$this->getSEO();
		
		$this->sendTemplatesToSmarty( $tpls );
			
	}
	
}