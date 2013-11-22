<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Modules
 */

class Searchengine extends Module implements ModuleInterface {
	
	public $name = "Search Engine";
	public $description = "Add a search engine to your website";
	public $slug = "searchengine";
	
	public $hook_name = "HOOK_SEARCH_ENGINE";
	public $method_name = "displaySearchEngine";
	
	public function start(){
			
	}	
	
	public function displayAdmin(){
		
		
		if( Tools::getValue('updateSettings') ) {
			$data = array();
			for( $i=0; $i<count($_POST['id_entity_model']); $i++ ){
				if( $_POST['id_entity_model'][$i] > 0 ){
					$tmp = array();
					$tmp['id_entity_model'] = (int)$_POST['id_entity_model'][$i];
					$tmp['id_field_model'] = (int)$_POST['id_field_model'][$i];
					$tmp['weight'] = (int)$_POST['weight'][$i];
					array_push($data, $tmp);
				}
			}
			Db::getInstance()->updateDB(_DB_PREFIX_.'config', array('value'=>json_encode($data)), array('name'=>"searchEngine"));
		}
		
		parent::displayAdmin();	
		
		$models = EntityModel::getModels($this->cookie->id_lang);
		$params = json_decode(Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="searchEngine"'), true);	
		
		if( $params ){
			foreach($params as	&$p)
				$p['fields'] = EntityModelField::getFieldModelsList($p['id_entity_model']);
		}
				
		$this->smarty->assign(array(
			'models' => $models,
			'params' => $params
		));
		
		$this->smarty->display('../modules/searchengine/admin.html');
	}
	
	public function installModule(){
		parent::installModule();
		if( !Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'config WHERE name="searchEngine"') )
			Db::getInstance()->Insert(_DB_PREFIX_.'config', array('name' => "searchEngine") );			
	}
	
	public function displaySearchEngine(){
		
		$this->initController();
		
		$results = array();
		
		if( Tools::getValue('submitSearch') && Tools::getValue('search') )
			$results = $this->search( Tools::getValue('search') );
		
		$this->smarty->assign(array(
			'results' => $results,
			'string' => Tools::getValue('search')
		));
		$this->smarty->display('modules/searchengine/searchengine.html');
	
	}
	
	
	public function search($string){
		$fields = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="searchEngine"');	
		$fields = json_decode($fields, true);
		$weight = array();
		
		// Threat string
		if( file_exists(__DIR__.'/emptywords/'.$this->cookie->lang.'.php') ){
			require_once(__DIR__.'/emptywords/'.$this->cookie->lang.'.php');
			$string = str_replace($emptywords, "", $string);				
		}
		
		$strings = explode(' ', $string);
		
		foreach( $strings as $string ){
			if( !empty($string) ){
				foreach( $fields as $f ){
					
					$field_model = new EntityModelField( $f['id_field_model'] );
					
					// Search engine on text field
					if( $field_model->type == "inputText" || $field_model->type == "textarea" ){
						$ids = Db::getInstance()->Select('
							SELECT E.id_entity FROM '._DB_PREFIX_.'entity E
							LEFT JOIN '._DB_PREFIX_.'entity_field F ON E.id_entity = F.id_entity
							LEFT JOIN '._DB_PREFIX_.'entity_field_lang L ON L.id_entity_field = F.id_entity_field
							WHERE
								E.deleted=0 AND 
								E.state="published" AND
								F.id_field_model='.(int)$f['id_field_model'].' AND
								L.id_lang='.(int)$this->cookie->id_lang.' AND
								L.value LIKE "%'.Tools::cleanSQL($string).'%"
						');	
					}
					
					// Search engine on attribute
					elseif( $field_model->type == "select" || $field_model->type == "radio" ){
						
						$param = json_decode($field_model->params, true);
						$id_attribute = $params['value'];
						
						$attribute_values	= Db::getInstance()->Select('
							SELECT V.id_attribute_value FROM '._DB_PREFIX_.'attribute A
							LEFT JOIN '._DB_PREFIX_.'attribute_value V ON A.id_attribute = V.id_attribute
							LEFT JOIN '._DB_PREFIX_.'attribute_value_lang L ON V.id_attribute_value=L.id_attribute_value
							WHERE 
								A.id_attribute='.(int)$id_attribute.' AND
								L.id_lang='.(int)$this->cookie->id_lang.' AND
								L.value LIKE "%'.Tools::cleanSQL($string).'%" 
						');
						
						if( count($attribute_values) > 0 ){
							
							$id_values = '';
							foreach($attribute_values as $value)
								$id_values .= $value['id_attribute_value'].',';
								
							$id_values = rtrim(',', $id_values);
							
							$ids = Db::getInstance()->Select('
								SELECT E.id_entity FROM '._DB_PREFIX_.'entity E
								LEFT JOIN '._DB_PREFIX_.'entity_field F ON E.id_entity = F.id_entity
								WHERE
									E.deleted=0 AND 
									E.state="published" AND
									F.id_field_model='.(int)$f['id_field_model'].' AND
									F.raw_value IN ('.$id_values.')
							');
						
						}
						
					}			
					
					// Search engine on multiple attribute
					elseif( $field_model->type == "checkbox" ){
						
						$param = json_decode($field_model->params, true);
						$id_attribute = $params['value'];
						
						$attribute_values	= Db::getInstance()->Select('
							SELECT V.id_attribute_value FROM '._DB_PREFIX_.'attribute A
							LEFT JOIN '._DB_PREFIX_.'attribute_value V ON A.id_atribute = V.id_attribute
							LEFT JOIN '._DB_PREFIX_.'attribute_value_lang L ON V.id_attribute_value=L.id_attribute_value
							WHERE 
								A.id_attribute='.(int)$id_attribute.' AND
								L.id_lang='.(int)$this->cookie->id_lang.' AND
								L.value LIKE "%'.Tools::cleanSQL($string).'%" 
						');
						
						$id_values = '';
						
						if( count($attribute_values) > 0 ){
							
							$id_values = '';
							foreach($attribute_values as $value)
								$id_values .= ' F.raw_value LIKE "%,'.$value['id_attribute_value'].'," OR';
								
							$id_values = rtrim('OR', $id_values);
							
							$ids = Db::getInstance()->Select('
								SELECT E.id_entity FROM '._DB_PREFIX_.'entity E
								LEFT JOIN '._DB_PREFIX_.'entity_field F ON E.id_entity = F.id_entity
								WHERE
									E.deleted=0 AND 
									E.state="published" AND
									F.id_field_model='.(int)$f['id_field_model'].' AND
									( '.$id_values.' )
							');
						
						}
						
					}
					
					if( $ids ){
						foreach( $ids as $i ){
							$id = $i['id_entity'];
							if( !isset($weight[$id]) )
								$weight[$id] = $f['weight'];
							else
								$weight[$id] += $f['weight'];
						}
					}
					
				}
			}
		}
		
		
		if( count($weight) > 0 ) {
			$entities = array();
			asort($weight, SORT_NUMERIC);
			$weight =  array_reverse( $weight, true );
			foreach( $weight as $id => $weight ){
				$e = new Entity($id);
				$tmp = array();
				
				foreach( get_object_vars($e) as $attr => $value)
					$tmp[$attr] = $value;
				
				$tmp['fields'] = $e->getData($this->cookie->id_lang);
				$tmp['id_default_parent'] = $e->getDefaultParent();
				
				$entities[] = $tmp;	
			}
			
			return $entities;			
		} else
			return false;
		
	}
	
	
}




