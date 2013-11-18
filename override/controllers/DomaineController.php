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
				$vins = array();
				$vins = Entity::getEntitiesListWithAttributeValue(
					2, 
					(int)Tools::getValue('catmancey'), 
					$this->cookie->id_lang, 
					true,
					false,
					NULL,
					25
				);
				$catmancey = AttributeValue::getAttributeValue((int)Tools::getValue('catmancey'), $this->cookie->id_lang);
				$this->smarty->assign('catmancey', $catmancey);
				// Petit hack pour résoudre un problème de conception
				// Dans la version 0.7 ci présente, certaines méthodes renvoient aussi bien une liste d'objet qu'un pur tableau
				// Je dois donc unifier le tout dans une version suivate.
				// Pour le moment je rectifie en créant un nouveau tableau
				$children = array();
				$z = 0;
				foreach( $vins as $vin ){
					$children['vin'][$z]['id_entity'] = $vin->id_entity;
					$children['vin'][$z]['id_entity_model'] = $vin->id_entity_model;
					$children['vin'][$z]['state'] = $vin->state;
					$children['vin'][$z]['templates'] = $vin->templates;
					$children['vin'][$z]['deleted'] = $vin->deleted;					
					$children['vin'][$z]['date_add'] = $vin->date_add;
					$children['vin'][$z]['date_upd'] = $vin->date_upd;
					$children['vin'][$z]['meta_title'] = $vin->meta_title;
					$children['vin'][$z]['fields'] = $vin->fields;
					$z++;
				}
				// Et voilà c'est finis
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