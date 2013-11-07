<?php


/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityModelCore extends Core {

	protected $table = 'model_entity';	
	protected $identifier = 'id_entity_model';
	
	
	/**
	* Get the entiere list of entities
	* @param Bool $hierarchic 
	* @return Array
	*/	
	public static function getModels($id_lang=1, $hierarchic=0){
		
		$sql = 'SELECT * FROM '._DB_PREFIX_.'model_entity M
		LEFT JOIN '._DB_PREFIX_.'model_entity_lang L ON M.id_entity_model = L.id_entity_model
		WHERE L.id_lang='.$id_lang.' AND M.deleted=0 '.( $hierarchic ? 'AND M.hierarchic=1' : '' );
		return Db::getInstance()->Select($sql);
		
	}
	
	/**
	* Add a new model
	* @param Array $data
	*/
	public function addModel($data){
		
		$this->slug = $data->slug;
		$this->hierarchic = ( $data->is_hierarchic > 0 ? 1 : 0 );
		
		$this->templates = $data->templates;
		$this->entities_templates = $data->entities_templates;
		$this->id_entity_model = $data->id_entity_model;
		
		$name = array();
		foreach( $data->name as $lang )
			$name[$lang->id_lang] = $lang->name;
		
		$this->id_parent = $data->id_parent;
		
		$this->name = $name;
		
		foreach( $data->meta_description as $lang )
				$meta_description[$lang->id_lang] = $lang->meta_description;		
		$this->meta_description = $meta_description;
		
		foreach( $data->link_rewrite as $lang )
				$link_rewrite[$lang->id_lang] = $lang->link_rewrite;
		$this->link_rewrite = $link_rewrite;
		
		foreach( $data->meta_title as $lang )
				$meta_title[$lang->id_lang] = $lang->meta_title;
		$this->meta_title = $meta_title;
		
		foreach( $data->meta_keywords as $lang )
				$meta_keywords[$lang->id_lang] = $lang->meta_keywords;
		$this->meta_keywords = $meta_keywords;
			

		if( $this->id_entity_model > 0 )
			$this->update();
		else
			$this->add();
		
		if( !empty($data->changeforall) )
			Db::getInstance()->UpdateDB(_DB_PREFIX_.'entity', array('templates'=>$this->entities_templates), array('id_entity_model' => $data->id_entity_model ));
		
		// In case of update
		if( $data->id_entity_model > 0 )
			Db::getInstance()->UpdateDB(_DB_PREFIX_.'model_entity_field', array('deleted'=>1), array('id_entity_model' => $data->id_entity_model ));
		
		$updated_fields = array();
		
		foreach( $data->fields as $field ){
			$field_model = new EntityModelField( $field->id_field_model );
			
			$name = array();
			foreach( $field->name as $lang )
				$name[$lang->id_lang] = $lang->name;
			
			$field_model->name = $name;
				
			$field_model->id_entity_model = $this->id_entity_model;
			$field_model->slug = $field->slug;
			$field_model->type = $field->type;
			$field_model->params = json_encode($field->params);
			$field_model->position = $field->position;
			$field_model->deleted = 0;
			
			if( $field_model->id_field_model > 0 )
				$field_model->update();
			else
				$field_model->add();	
			
			array_push($updated_fields, $field_model->id_field_model);
		}		
		
		$this->deleteFields($updated_fields);
	}
	
	public function update(){
		$old_data = Db::getInstance()->getValue('SELECT templates FROM '._DB_PREFIX_.'model_entity WHERE id_entity_model='.(int)$this->id_entity_model);
		if( $old_data !== $this->templates ){
			$sql = '
				UPDATE '._DB_PREFIX_.'model_entity
				SET templates="'.$this->templates.'" 
				WHERE id_entity_model='.(int)$this->id_entity_model.'
				AND templates="'.Tools::cleanSQL($old_date).'"
			';	
			Db::getInstance()->query($sql);
		}
		parent::update();			
	}
	
	public function deleteFields($field_ids){
		if( count($field_ids) > 0 ){
			$sql = ' 
				DELETE FROM '._DB_PREFIX_.'model_entity_field_lang WHERE id_field_model IN ( 
					SELECT id_field_model FROM '._DB_PREFIX_.'model_entity_field WHERE id_entity_model='.(int)$this->id_entity_model.' AND id_field_model NOT IN ('.implode(',', $field_ids).')
				)
			';
			
			Db::getInstance()->query($sql);
			
		}
	}
	
	
	public function getFieldsList($id_lang){
		$sql = '
			SELECT * FROM '._DB_PREFIX_.'model_entity_field F
			LEFT JOIN '._DB_PREFIX_.'model_entity_field_lang L ON F.id_field_model=L.id_field_model
			WHERE F.id_entity_model='.(int)$this->id_entity_model.'
			AND L.id_lang='.(int)$id_lang.'
			AND F.deleted=0
			ORDER BY F.position ASC
		';	
		$fields = Db::getInstance()->Select($sql);
		
		foreach( $fields as &$field ){
			
			$field['params'] = json_decode( $field['params'], true ); 
			
			if( $field['type'] == "checkbox" || $field['type'] == "selectbox" || $field['type'] == "radio" ){
				$id_attribute = $field['params'][0]['value'];
				$field['attributes'] = Attribute::getValues($id_attribute, $id_lang);
			}
			
		}
		return $fields;
	}
	
	
	/**
	* Get model details 
	* @param Int $id_entity_model
	* @return Array
	*/
	public static function getModelData($id_entity_model){
		$entity = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'model_entity WHERE id_entity_model='.(int)$id_entity_model);

		if( $entity ){
			
			$sql = 'SELECT * FROM '._DB_PREFIX_.'model_entity_lang WHERE id_entity_model=:id_entity_model';
			$name_values = Db::getInstance()->Select($sql, array('id_entity_model' => $id_entity_model));
			foreach( $name_values as $value )
				$entity['name'][ $value['id_lang'] ] = $value['name'];
			
			$entity['fields'] = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'model_entity_field WHERE slug!="name" AND deleted=0 AND id_entity_model='.(int)$id_entity_model." ORDER BY position ASC");
			
			foreach( $entity['fields'] as &$field ){
				$values = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'model_entity_field_lang WHERE id_field_model='.(int)$field['id_field_model']);	
				foreach( $values as $value )
					$field['value'][ $value['id_lang'] ] = $value['name'];
				
				$params = json_decode($field['params']);
				$field['params'] = array();
				foreach( $params as $param )
					$field['params'][ $param->name ] = $param->value;
				
			}
		}
		
		return $entity;
	}
	
	public static function getHierarchicEntityId($string,$id_lang){
		return Db::getInstance()->getValue('SELECT id_entity_model FROM '._DB_PREFIX_.'model_entity_lang WHERE id_lang=:id_lang AND link_rewrite=:link_rewrite',
		array('link_rewrite'=>$string, 'id_lang'=>$id_lang ));			
	}
	
	public function getHierarchicTree($id_parent, $id_lang){
		if( !$this->id_entity_model )
			return false;

		$attachedTo = ( $this->id_parent ? $this->id_parent : $this->id_entity_model );
		$entities = Db::getInstance()->Select('
			SELECT * FROM '._DB_PREFIX_.'entity_level LV
			LEFT JOIN '._DB_PREFIX_.'entity E ON E.id_entity=LV.id_entity
			WHERE LV.id_parent = '.(int)$id_parent.' AND
			E.id_entity_model='.(int)$attachedTo.' AND
			E.deleted = 0
			ORDER BY LV.position ASC
			');
		
		if( $entities ){
			foreach( $entities as &$entity ){
				$entity['name'] = Entity::getDisplayName($entity['id_entity'], $id_lang);
				$entity['children'] = $this->getHierarchicTree($entity['id_entity'], $id_lang);				
			}
		}
		
		return $entities;
	}
	
	
	public static function getModelIdFromLinkRewrite($string, $id_lang){
		return Db::getInstance()->getRow('SELECT L.id_entity_model, M.slug FROM '._DB_PREFIX_.'model_entity_lang L
			LEFT JOIN '._DB_PREFIX_.'model_entity M ON L.id_entity_model = M.id_entity_model
			WHERE L.link_rewrite=:link_rewrite AND L.id_lang=:id_lang',
			array('link_rewrite'=>$string, 'id_lang'=>$id_lang) );		
		
	}
	
	public function getSEO(){
		return true;	
	}
	
	public function getTemplates(){
		return $this->templates;	
	}
	
}