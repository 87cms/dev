<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityCore extends Core {
	
	protected $table = 'entity';	
	protected $identifier = 'id_entity';
	
	public $breadcrumb;
	public $slug;
	
	function __construct($id=0){
		if( $id > 0 )
			parent::setObject($id);	
		if( $this->id_entity ){
			$this->parents = $this->getParents();
			$this->id_default_parent = $this->getDefaultParent();	
			$this->setSlug();
		}
	}
	
	
	/**
	* @deprecated
	*/	
	public function getLinkRewrite($id_lang = 0){
		$sql = "SELECT link_rewrite FROM "._DB_PREFIX_."entity_lang WHERE id_entity=:id_entity";
		if( $id_lang )
			$sql .= " AND id_lang=".(int)$id_lang;
		$link = Db::getInstance()->getValue($sql, array('id_entity' => $this->id_entity));
		if( $link )
			return (int)$this->id_entity.'-'.$link;
	}
	
	
	/**
	* Get all entity fields
	* @param Int $id_lang
	* @return $array
	*/
	public function getData($id_lang){
		$entity_fields = Db::getInstance()->Select('SELECT F.*, M.slug FROM '._DB_PREFIX_.'entity_field F, '._DB_PREFIX_.'model_entity_field M WHERE M.id_field_model=F.id_field_model AND F.id_entity=:id_entity',array('id_entity'=>$this->id_entity));
		$data = array();
		foreach( $entity_fields as $field ){
			$data[ $field['slug'] ] = EntityField::getFieldValues($field['id_field_model'], $this->id_entity, $id_lang);
		}
		$this->link_rewrite = Link::getEntityLink($this->id_entity, $id_lang);
		return $data;
	}
	
	
	/**
	* Get parents (not recursive)
	*/
	public function getParents(){
		$out = array();
		$p = Db::getInstance()->Select('SELECT id_parent FROM '._DB_PREFIX_.'entity_level WHERE id_entity='.(int)$this->id_entity);
		foreach( $p as $parent )
			array_push($out, $parent['id_parent']);
		return $out;
	}
	
	
	/*
	* Get the default parent
	*/
	public function getDefaultParent(){
		return Db::getInstance()->getValue('SELECT id_parent FROM '._DB_PREFIX_.'entity_level WHERE isdefault=1 AND id_entity='.(int)$this->id_entity);	
	}
	
	/**
	* Get children of an entity. Warning : a hierarchic entity can have multiple model as child.
	* @param Int $id_lang
	* @param Int $p
	* @param Int $n
	* @param String $sort Sort results : id_entity-desc, id_entity-asc, state-published, state-draft, meta_title. Default : position in parent
	* @return $array
	* @todo Change $orderby with sort system (as Entity::getEntitiesList);
	*/
	public function getChildren($id_lang, $p=0, $n=0, $sort=NULL, $id_entity_model=0){
		
		$ids_children = Db::getInstance()->Select(
			'SELECT id_entity_model, slug FROM '._DB_PREFIX_.'model_entity 
			WHERE 
			id_parent='.$this->id_entity_model.'
			'.( $id_entity_model ? ' AND id_entity_model = '.(int)$id_entity_model : '' )
			);
		$children = array();
		
		foreach( $ids_children as $child ){
			
			if( $sort ){
				if( $sort == 'id_entity-desc' ) $sort = 'ORDER BY E.id_entity DESC';
				elseif( $sort == 'id_entity-asc' ) $sort = 'ORDER BY E.id_entity ASC';
				elseif( $sort == 'state-published' ) $sort = 'AND E.state="published" ORDER BY E.id_entity DESC';
				elseif( $sort == 'state-draft' ) $sort = 'AND E.state="draft" ORDER BY E.id_entity DESC';
				elseif( $sort == 'meta_title' ) $sort = 'ORDER BY L.meta_title ASC';
			}else
				$sort = ' ORDER BY LV.position ASC ';
			
			$entities = Db::getInstance()->Select('
				SELECT E.*, L.meta_title FROM '._DB_PREFIX_.'entity_level LV
				LEFT JOIN '._DB_PREFIX_.'entity E ON E.id_entity = LV.id_entity
				LEFT JOIN '._DB_PREFIX_.'entity_lang L ON L.id_entity = E.id_entity
				WHERE 
					LV.id_parent='.$this->id_entity.' AND
					E.id_entity_model='.$child['id_entity_model'].' AND
					E.state="published" AND
					E.deleted = 0 AND
					L.id_lang='.(int)$id_lang.'
				
				'.( $p && $n ? 'LIMIT '.(int)($p*$n).','.(int)$n : '' ).'
				'.( $sort ? $sort : '' ).'
			');
			
			if( $entities && count($entities) > 0 ){
				foreach( $entities as &$entity ){
					
					$entity_fields = Db::getInstance()->Select('SELECT F.*, M.slug FROM '._DB_PREFIX_.'entity_field F, '._DB_PREFIX_.'model_entity_field M 
						WHERE M.id_field_model=F.id_field_model 
						AND F.id_entity='.(int)$entity['id_entity'] );
				
					$datas = array();
					foreach( $entity_fields as $field )
						$datas[ $field['slug'] ] = EntityField::getFieldValues($field['id_field_model'], $entity['id_entity'], $id_lang, false);
					
					$entity['fields'] = $datas;
					
					$entity['link_rewrite'] = Link::getEntityLink($entity['id_entity'], $id_lang);
				
				}
				
			}
			
			if( $id_entity_model > 0 )
				$children = $entities;
			else
				$children[ $child['slug'] ] = $entities;
		}
		
		return $children;
	}
	
	/**
	* @deprecated
	* Get list of children entities of a hierarchic model
	* @param Int $id_lang
	* @param Int $p
	* @param Int $n
	* @param String $orderby
	* @return Array
	*/
	public function getEntitiesAttachedToModel($id_lang, $p=0, $n=0, $orderby=NULL){
		$entities = Db::getInstance()->Select('
			SELECT E.* FROM '._DB_PREFIX_.'model_entity ME 
			LEFT JOIN '._DB_PREFIX_.'entity E ON ME.id_entity_model = E.id_entity_model
			LEFT JOIN '._DB_PREFIX_.'entity_level L ON E.id_entity = L.id_entity
			WHERE 
				ME.id_parent='.$this->id_entity_model.' AND
				L.id_parent='.$this->id_entity.' AND
				E.state="published" AND
				E.deleted = 0
			'.( $p && $n ? 'LIMIT '.(int)($p*$n).','.(int)$n : '' ).'
			'.( $orderby ? Tools::cleanSQL($orderby) : '' ).'
		');
		
		foreach( $entities as &$entity ){
			
			$entity_fields = Db::getInstance()->Select('SELECT F.*, M.slug FROM '._DB_PREFIX_.'entity_field F, '._DB_PREFIX_.'model_entity_field M 
					WHERE M.id_field_model=F.id_field_model 
					AND F.id_entity='.(int)$entity['id_entity'] );
			
			$datas = array();
			foreach( $entity_fields as $field )
				$datas[ $field['slug'] ] = EntityField::getFieldValues($field['id_field_model'], $entity['id_entity'], $id_lang, false);
			
			$entity['fields'] = $datas;
			$entity['link_rewrite'] = Link::getEntityLink($entity['id_entity'], $id_lang);

		}
		return $entities;
		
	}
	
	
	/**
	* Return slug from id_entity_model
	*/
	public function getEntitySlugAttachedToModel(){
		return Db::getInstance()->getValue('SELECT slug FROM '._DB_PREFIX_.'model_entity WHERE id_parent='.$this->id_entity_model);
	}
	
	public static function getEntitySlugAttachedToModelStatic($id_entity_model){
		return Db::getInstance()->getValue('SELECT slug FROM '._DB_PREFIX_.'model_entity WHERE id_parent='.$id_entity_model);	
	}
	
	/**
	* Get Entities list from the id_parent
	* @param Int $id_model
	* @param Int $id_lang
	* @param Int $id_parent
	* @param String $sort Sort results : id_entity-desc, id_entity-asc, state-published, state-draft, meta_title. Default : position in parent 
	* @param Int $p
	* @param Int $n
	* @param Bool $with_drafts
	* @param User $user Mandatory to check permission
	* @return Array
	*/	
	public static function getEntitiesList($id_model, $id_lang, $id_parent=NULL, $sort=NULL, $p=0, $n=0, $with_drafts=NULL, User $user = NULL){
		
		$p = ($p-1)*$n;
		if( $p < 0 ) $p = 0;
		
		if( $sort ){
			if( $sort == 'id_entity-desc' ) $sort = 'ORDER BY E.id_entity DESC';
			elseif( $sort == 'id_entity-asc' ) $sort = 'ORDER BY E.id_entity ASC';
			elseif( $sort == 'state-published' ) $sort = 'AND E.state="published" ORDER BY E.id_entity DESC';
			elseif( $sort == 'state-draft' ) $sort = 'AND E.state="draft" ORDER BY E.id_entity DESC';
			elseif( $sort == 'meta_title' ) $sort = 'ORDER BY L.meta_title ASC';
		}else
			$sort = ' ORDER BY LV.position ASC ';
		
		// MODIFTB 24/09: Ajout DISTINCT
		$sql = 'SELECT DISTINCT E.*, L.meta_title FROM '._DB_PREFIX_.'entity E
		LEFT JOIN '._DB_PREFIX_.'entity_lang L ON E.id_entity = L.id_entity
		LEFT JOIN '._DB_PREFIX_.'entity_level LV ON E.id_entity=LV.id_entity';
		
		if( $user )
			$sql .= ' LEFT JOIN '._DB_PREFIX_.'user_permission P ON P.id_entity = E.id_entity ';
		
		$sql .= ' WHERE 
			L.id_lang='.(int)$id_lang.' 
			AND E.deleted=0 
			AND E.id_entity_model='.(int)$id_model.'
			'.( $with_drafts ? '' : ' AND E.state="published"' ).'
			'.( is_int($id_parent) && $id_parent>=0 ? ' AND LV.id_parent='.(int)$id_parent : '' ).'
			'.( $user ? ' AND id_user = '.(int)$user->id_user : '' ).'
			'.( $sort ? $sort : '' ).'
			'.( $n && isset($p) ? "LIMIT $p,$n": '').'
			';
		
		$entities = Db::getInstance()->Select($sql);
		
		foreach( $entities as &$entity ){
			$entity['link_rewrite'] = Link::getEntityLink($entity['id_entity'], $id_lang);
		}
		return $entities;
		
	}
	
	
	/**
	* Count Entities list from the id_parent
	* @param Int $id_model
	* @param Int $id_lang
	* @param Int $id_parent
	* @param String $sort
	* @param Bool $with_drafts
	* @param User $user To check permission
	* @return Array
	*/	
	public static function countEntities($id_model, $id_lang, $id_parent=NULL, $sort=NULL, $with_drafts=NULL, User $user = NULL){
		
		if( $sort ){
			if( $sort == 'id_entity-desc' ) $sort = 'ORDER BY E.id_entity DESC';
			elseif( $sort == 'id_entity-asc' ) $sort = 'ORDER BY E.id_entity ASC';
			elseif( $sort == 'state-published' ) $sort = 'AND state="published" ORDER BY E.id_entity DESC';
			elseif( $sort == 'state-draft' ) $sort = 'AND state="draft" ORDER BY E.id_entity DESC';
		}
		
		$sql = 'SELECT E.*, L.meta_title FROM '._DB_PREFIX_.'entity E
		LEFT JOIN '._DB_PREFIX_.'entity_lang L ON E.id_entity = L.id_entity
		LEFT JOIN '._DB_PREFIX_.'entity_level LV ON E.id_entity=LV.id_entity
		WHERE 
			L.id_lang='.(int)$id_lang.' 
			AND E.deleted=0 
			AND E.id_entity_model='.(int)$id_model.'
			'.( $with_drafts ? '' : ' AND E.state="published"' ).'
			'.( is_int($id_parent) && $id_parent >= 0 ? ' AND LV.id_parent='.(int)$id_parent : '' ).'
			'.( $sort ? $sort : '' ).'
			';
		
		$entities = Db::getInstance()->Select($sql);
		
		return count($entities);
		
	}
	
	
	/**
	* Get an entities list wich contains a specific attributes ( eg : product with color blue)
	* @param Int $id_entity_model Id of entity model
	* @param Int $id_attribute_value Id of attribute value
	* @param Int $id_lang Lang id
	* @param Bool $include_data If this parameter is TRUE, the method return a list of entity object
	* @param Bool $with_drafts Include drafts in results
	* @param String $sort Sort results : id_entity-desc, id_entity-asc, state-published, state-draft, meta_title 
	* @return Array Entities list
	*/
	public static function getEntitiesListWithAttributeValue($id_entity_model, $id_attribute_value, $id_lang, $include_data=false, $with_drafts=NULL, $sort=''){
		
		if( $sort ){
			if( $sort == 'id_entity-desc' ) $sort = 'ORDER BY E.id_entity DESC';
			elseif( $sort == 'id_entity-asc' ) $sort = 'ORDER BY E.id_entity ASC';
			elseif( $sort == 'state-published' ) $sort = 'AND E.state="published" ORDER BY E.id_entity DESC';
			elseif( $sort == 'state-draft' ) $sort = 'AND E.state="draft" ORDER BY E.id_entity DESC';
			elseif( $sort == 'meta_title' ) $sort = 'ORDER BY L.meta_title ASC';
		}
		
		$sql = 'SELECT DISTINCT E.*, L.meta_title FROM '._DB_PREFIX_.'entity E
		LEFT JOIN '._DB_PREFIX_.'entity_lang L ON E.id_entity = L.id_entity
		';
		
		$sql .= ' WHERE 
			L.id_lang='.(int)$id_lang.' 
			AND E.deleted=0 
			AND E.id_entity_model='.(int)$id_entity_model.'
			'.( $with_drafts ? '' : ' AND E.state="published"' ).'
			AND E.id_entity IN (
				SELECT id_entity FROM '._DB_PREFIX_.'entity_field 
				WHERE raw_value LIKE "%,'.$id_attribute_value.',%" OR raw_value = "'.$id_attribute_value.'"					
			)
			'.( $sort ? $sort : '' );

		$entities = Db::getInstance()->Select($sql);
		
		if( $include_data ){
			$out = array();
			for($i=0; $i<count($entities); $i++){
				$tmpEntity = new Entity($entities[$i]['id_entity']);
				$tmpEntity->fields = $tmpEntity->getData($id_lang);				
				$out[$i] = $tmpEntity;
			}
		}else
			$out = $entities;
				
		return $out;
	}
	
	
	
	/**
	* Recursivce function to get the entire Hierarchic Entities list
	* @param Int $id_model
	* @param Int $id_lang
	* @param Int $id_parent
	* @param String $sort Sort results : id_entity-desc, id_entity-asc, state-published, state-draft, meta_title. Default : position in parent 
	* @param Int $p
	* @param Int $n
	* @param Bool $with_drafts
	* @param User $user To check permission
	* @return Array
	*/
	public static function getHierarchicEntitiesList($id_model, $id_lang, $id_parent=NULL, $sort='', $p=0, $n=0, $with_drafts=NULL, User $user = NULL){
		
		if( $sort ){
			if( $sort == 'id_entity-desc' ) $sort = 'ORDER BY E.id_entity DESC';
			elseif( $sort == 'id_entity-asc' ) $sort = 'ORDER BY E.id_entity ASC';
			elseif( $sort == 'state-published' ) $sort = 'AND E.state="published" ORDER BY E.id_entity DESC';
			elseif( $sort == 'state-draft' ) $sort = 'AND E.state="draft" ORDER BY E.id_entity DESC';
			elseif( $sort == 'meta_title' ) $sort = 'ORDER BY L.meta_title ASC';
		}else
			$sort = ' ORDER BY LV.position ASC ';
		
		$sql = 'SELECT * FROM '._DB_PREFIX_.'entity E
		LEFT JOIN '._DB_PREFIX_.'entity_lang L ON E.id_entity = L.id_entity
		LEFT JOIN '._DB_PREFIX_.'entity_level LV ON E.id_entity=LV.id_entity';
		
		if( $user && $user->id_user > 0 )
			$sql .= ' LEFT JOIN '._DB_PREFIX_.'user_permission P ON P.id_entity = E.id_entity ';

		$sql .= ' WHERE 
			L.id_lang='.(int)$id_lang.' 
			AND E.deleted=0 
			AND E.id_entity_model='.(int)$id_model.'
			'.( $with_drafts ? '' : ' AND E.state="published"' ).'
			'.( $id_parent>=0 ? ' AND LV.id_parent='.(int)$id_parent : '' ).'
			'.( $user ? ' AND id_user = '.(int)$user->id_user : '' ).'
			'.( $sort ? $sort : '' )
		;
		
		$hierarchicEntities = Db::getInstance()->Select($sql);
		
		foreach( $hierarchicEntities as &$entity ){
			
			$fields = Db::getInstance()->Select('
				SELECT MF.slug, MF.id_field_model FROM '._DB_PREFIX_.'model_entity ME
				LEFT JOIN '._DB_PREFIX_.'model_entity_field MF ON ME.id_entity_model = MF.id_entity_model
				WHERE ME.id_entity_model='.(int)$id_model.'
				AND MF.deleted=0
			');

			foreach( $fields as $field )
				$entity[ $field['slug'] ] = EntityField::getFieldValues($field['id_field_model'], $entity['id_entity'], $id_lang);
				
			
			$children = self::getHierarchicEntitiesList($id_model, $id_lang, $entity['id_entity'],$sort, 0, 0, $with_drafts, $user);
			if( count($children) > 0 )
				$entity['children'] = $children;
				
			$entity['link_rewrite'] = Link::getEntityLink($entity['id_entity'], $id_lang);
				
		}
		
		return $hierarchicEntities;
		
	}
	
	
	/**
	* Method to add an entity. The parameters data come froms Javascript.
	* If you want to add an entity, you have to use the webservice (see documentation)
	* @param Array $data
	*/
	public function addEntity($data){
		
		if( !$data )
			die();
		
		$this->id_entity = $data->id_entity;
		
		foreach( $data->meta_description as $lang )
				$meta_description[$lang->id_lang] = utf8_encode(urldecode($lang->meta_description));		
		$this->meta_description = $meta_description;
		
		foreach( $data->link_rewrite as $lang )
				$link_rewrite[$lang->id_lang] = $lang->link_rewrite;
		$this->link_rewrite = $link_rewrite;
		
		foreach( $data->meta_title as $lang )
				$meta_title[$lang->id_lang] = utf8_encode(urldecode($lang->meta_title));
		$this->meta_title = $meta_title;
		
		foreach( $data->meta_keywords as $lang )
				$meta_keywords[$lang->id_lang] = $lang->meta_keywords;
		$this->meta_keywords = $meta_keywords;
		
		$this->state = ( $data->state == 'draft' ? 'draft' : '').( $data->state == 'published' ? 'published' : '');
		$this->templates = $data->templates; 
		$this->id_entity_model = $data->id_entity_model;
		
		if( $this->id_entity > 0 )
			$this->update();
		else
			$this->add();
		
		if( $this->id_entity ){	
			
			$tmpdbparents = Db::getInstance()->Select('SELECT id_parent FROM '._DB_PREFIX_.'entity_level WHERE id_entity='.$this->id_entity);
			$dbparents = array();
			
			foreach($tmpdbparents as $d)
				$dbparents[] = $d['id_parent'];
			
			if( count($data->parents) > 0 ){
				$inserted_parents = array();
				foreach( $data->parents as $id_parent ){
					
					if( !in_array($id_parent, $dbparents) ){
						$maxp = self::countEntities($this->id_entity_model, 1, $id_parent) + 1;
						Db::getInstance()->Insert(_DB_PREFIX_.'entity_level', array('id_entity' => $this->id_entity, 'id_parent' => $id_parent, 'position' => $maxp) );	
					
					}
					$inserted_parents[] = $id_parent;
					
				}
				foreach($dbparents as $dbparent){
					
					if( !in_array($dbparent, $inserted_parents) )
						Db::getInstance()->Delete(_DB_PREFIX_.'entity_level', array('id_entity' => $this->id_entity, 'id_parent'=>$dbparent));
						
				}
				
				Db::getInstance()->UpdateDB(_DB_PREFIX_.'entity_level', array('isdefault' => 0), array('id_entity' => $this->id_entity) );
				Db::getInstance()->UpdateDB(_DB_PREFIX_.'entity_level', array('isdefault' => 1), array('id_parent' => $data->id_default_parent) );
				
			}
			else {
				Db::getInstance()->Delete(_DB_PREFIX_.'entity_level', array('id_entity' => $this->id_entity) );
				Db::getInstance()->Insert(_DB_PREFIX_.'entity_level', array('id_entity' => $this->id_entity, 'id_parent' => 0, 'isdefault' => 1) );
			}
			
			
			
			$fields = $data->fields;
			
			foreach( $fields as $field ){

				$id_entity_field = Db::getInstance()->getValue('SELECT id_entity_field FROM '._DB_PREFIX_.'entity_field WHERE id_entity=:id_entity AND id_field_model=:id_field_model', 
														array('id_entity' => $this->id_entity, 'id_field_model' => $field->id_field_model) );


				$entity_field = new EntityField($id_entity_field);
			
				$entity_field->id_entity = $this->id_entity;
				$entity_field->id_field_model = $field->id_field_model;
				
				$raw_value = '';
				$values = array();
				
				if( !empty($field->raw_value) )
				{
					$raw_value = urldecode($field->raw_value);
					
					// Images resize
					$field_model = new EntityModelField( $field->id_field_model );
					if( $field_model->type == "inputImage" )
						Tools::resizeImagesFromJSON($raw_value, $field->id_field_model, $field_model->params);
					// End images resize
					
				}
				elseif( count($field->values) > 0 ){
					foreach( $field->values as $value )
						$values[ $value->id_lang ] = utf8_encode(urldecode($value->value));
				}
				
				$entity_field->raw_value = $raw_value;
				$entity_field->value = $values;
				
				if( $id_entity_field )
					$entity_field->update();	
				else
					$entity_field->add();
			
			}
		
		}
			
	}

	
	/**
	* Get the entity display name. We consider the display name of an entity is the first field.
	* @param Int $id_entity
	* @param Int $id_lang
	* @return String Name
	*/
	public static function getDisplayName($id_entity, $id_lang){
		return Db::getInstance()->getValue('
			SELECT L.value FROM '._DB_PREFIX_.'entity_field_lang L
			LEFT JOIN '._DB_PREFIX_.'entity_field E ON L.id_entity_field = E.id_entity_field
			WHERE E.id_entity='.(int)$id_entity.'
			AND L.id_lang='.(int)$id_lang.'
			ORDER BY L.id_entity_field ASC
			LIMIT 0,1
		');		
	}
	
	
	/**
	*
	*/
	public function getSEO(){
		return true;	
	}
	
	
	public function getTemplates(){
		return $this->templates;	
	}
	
	
	public function setSlug(){
		$this->slug = Db::getInstance()->getValue('SELECT slug FROM '._DB_PREFIX_.'model_entity WHERE id_entity_model='.(int)$this->id_entity_model);
	}
	
	
	public static function getSlug($id_entity){
		return Db::getInstance()->getValue('SELECT M.slug FROM '._DB_PREFIX_.'model_entity M, '._DB_PREFIX_.'entity E 
						WHERE 
							E.id_entity_model = M.id_entity_model
							AND E.id_entity='.(int)$id_entity);
	}
	
	
	/**
	* Check if an entity is deleted or not.
	* @param Int $id_entity
	* @return Int Data from deleted column in DB
	*/
	public static function isDeleted($id_entity){
		return Db::getInstance()->getValue('SELECT deleted FROM '._DB_PREFIX_.'entity WHERE id_entity='.(int)$id_entity);
	}
	
	
	/**
	* Get breadcrumb and set a new "breadcrumb" attribute
	* @param Int $id_entity
	* @param Int $id_lang
	* @todo Remove $id_entity param
	*/
	public function getBreadcrumb($id_entity='', $id_lang){
		
		if( !is_array($this->breadcrumb) )
			$this->breadcrumb = array();
		if( !$id_entity )
			$id_entity = $this->id_entity;
		
		$id_parent = Db::getInstance()->getValue('
			SELECT id_parent FROM '._DB_PREFIX_.'entity_level
			WHERE id_entity=:id_entity', array('id_entity'=>$id_entity));
		
		if( $id_parent && $id_parent > 0 ){
			$a = array(
				'id_entity' => $id_parent,
				'id_entity_model' => Db::getInstance()->getValue('SELECT id_entity_model FROM '._DB_PREFIX_.'entity WHERE id_entity='.(int)$id_parent),
				'name' => self::getDisplayName($id_parent, $id_lang),
				'link_rewrite' => Link::getEntityLink($id_parent, $id_lang)
			);
			
			array_push($this->breadcrumb, $a);
			$this->getBreadcrumb($id_parent, $id_lang);
			
		}if( $id_parent == 0 ){
			
			$model = Db::getInstance()->getRow('
				SELECT L.* FROM '._DB_PREFIX_.'entity E
				LEFT JOIN '._DB_PREFIX_.'model_entity M ON M.id_entity_model = E.id_entity_model
				LEFT JOIN '._DB_PREFIX_.'model_entity_lang L ON M.id_entity_model = L.id_entity_model
				WHERE E.id_entity=:id_entity
					AND L.id_lang=:id_lang
			', array('id_entity'=>$id_entity, 'id_lang'=>$id_lang) );
			
			$a = array(
				'id_entity' => '',
				'id_entity_model' => $model['id_entity_model'],
				'name' => $model['name'],
				'link_rewrite' => Link::getEntityModelLink($model['id_entity_model'], $id_lang='')
			);
			array_push($this->breadcrumb, $a); 			
		}
		
	}
	
	/**
	* Update the entities position in the parent
	* @param Int $id_parent 
	* @param Array $positions Array : array( index => id_entity1, index => $id_entity2, ...)
	*/
	public static function updatePositionEntities($id_parent, $positions){
		
		foreach( $positions as $position => $id_entity ){
			Db::getInstance()->UpdateDB(
				_DB_PREFIX_.'entity_level',
				array('position' => (int)$position),
				array('id_parent' => (int)$id_parent, 'id_entity' => (int)$id_entity)
			);
		}
	}
	
}




?>