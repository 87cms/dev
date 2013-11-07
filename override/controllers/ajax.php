<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class AjaxController extends Core {
	
	function __construct(){
		
		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
		
	}
	
	public function start(){
		
		if( Tools::getValue('action') == "getDomainesList" ){
			
			$vins = Entity::getEntitiesListWithAttributeValue('2', Tools::getValue('id_attribute_value'), $this->cookie->id_lang, false, NULL, 'meta_title');	
			
			$tmp_id_vins = array();
			foreach( $vins as $v ){
				$tmp_id_vins[] = $v['id_entity'];
			}
			$ids_vins = implode(',', $tmp_id_vins);

			$data = array();
			if( $ids_vins ){
				$sql = '
					SELECT DISTINCT E.*, FL.value FROM '._DB_PREFIX_.'entity E
					LEFT JOIN '._DB_PREFIX_.'entity_level L ON L.id_parent=E.id_entity
					LEFT JOIN '._DB_PREFIX_.'entity_field F ON E.id_entity=F.id_entity
					LEFT JOIN '._DB_PREFIX_.'entity_field_lang FL ON F.id_entity_field = FL.id_entity_field	
					WHERE 
						F.id_field_model=1
						AND E.deleted=0
						AND E.state="published"
						AND FL.id_lang='.(int)$this->cookie->id_lang.'
						AND L.id_entity IN ( '.$ids_vins.' )
					ORDER BY FL.value
				';
				
				$data = Db::getInstance()->Select($sql);
				
			}
			
			echo json_encode($data);
			
		}
		
		
		if( Tools::getValue('action') == "getVinsList" ){
			
			if( Tools::getValue('id_attribute_value') > 0 ){
				
				$vins = Entity::getEntitiesListWithAttributeValue('2', Tools::getValue('id_attribute_value'), $this->cookie->id_lang, true, NULL, 'meta_title');	
				
				foreach( $vins as &$vin ){
					$domaine = new Entity($vin->id_default_parent);
					$domaine->fields = $domaine->getData($this->cookie->id_lang);
					$vin->domaine = $domaine->fields['nom_domaine'];
				}
				
				echo json_encode($vins);
				
			}
			
			elseif( Tools::getValue('id_domaine') > 0 ){
				
				$domaine = new Entity( Tools::getValue('id_domaine') );
				$d = $domaine->getChildren($this->cookie->id_lang, $p=0, $n=0, NULL);
				$vins = $d['vin'];
				
				$tmpvins = array();
				
				foreach( $vins as &$vin ){
					$v = new Entity($vin['id_entity']);
					$vin['fields'] = $v->getData($this->cookie->id_lang);					
					$vin['link_rewrite'] = $v->link_rewrite;
				}
				
				echo json_encode($vins);
				
			}
			
		}
		
		
		if( Tools::getValue('action') == 'getDomainesFromRegion' ){
			$domaines = Entity::getEntitiesListWithAttributeValue('1', Tools::getValue('id_attribute_value'), $this->cookie->id_lang, true, NULL, 'meta_title');	
			echo json_encode($domaines);
		}
		
		if( Tools::getValue('action') == 'getAppellationsFromDomaine' ){
			
			$sql = '
				SELECT DISTINCT F.raw_value FROM '._DB_PREFIX_.'entity_level LV 
				LEFT JOIN '._DB_PREFIX_.'entity E ON LV.id_entity=E.id_entity
				LEFT JOIN '._DB_PREFIX_.'entity_field F ON E.id_entity=F.id_entity				
				WHERE LV.id_parent='.(int)Tools::getValue('id_domaine').'
				AND F.id_field_model=14
			';
			
			$fields = Db::getInstance()->Select($sql);
			
			$out = array();
			
			foreach( $fields as $f ){
				$data['id_attribute_value'] = $f['raw_value'];
				$data['value'] = AttributeValue::getAttributeValue($f['raw_value'], $this->cookie->id_lang);
				array_push($out, $data);
			}
			
			echo json_encode($out);
		}
			
	}
	
}