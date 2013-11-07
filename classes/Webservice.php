<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class WebserviceCore {
	
	public $id_lang;
	public $auth_key;
	private $authorized_ips;
	private $logged = 0;
	
	function __construct(){
		
		$active = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="webservice_active"');
		if( $active == 1 ){
			
			$this->authorized_ips = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="webservice_authorized_ips"');
			if( !empty($authorized_ips) ){
				$ips = explode(' ', $authorized_ips);
				if( !in_array($_SERVER["REMOTE_ADDR"], $ips) ){
					$response['response']['code'] = 403;
					$response['response']['message'] = 'Access forbidden.';
					return $response;
					die();
				}
			}
			
		}
		else{
			$response['response']['code'] = 403;
			$response['response']['message'] = 'Access forbidden !';
			return $response;
			die();
		}
	}
	
	/**
	* Authentication
	* @param String $apikey The api key define in admin
	*/
	public function authenticate($apikey){
		$this->auth_key = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="webservice_authkey"');
		if( $this->auth_key !== $key ){
			$response['response']['code'] = 403;
			$response['response']['message'] = 'Invalid API Key.';
			return $response;
			die();
		}else
			$this->logged = 1;
	}
	
	public function __call($name, $arguments){
		if( $this->logged = 0 ){
			$response['response']['code'] = 403;
			$response['response']['message'] = 'Invalid API Key.';
			return $response;
			die();	
		}			
	}
	
	/*###########*/	
	/*-- Model --*/
	
	/**
	* Get model list
	* @return Array
	*/	
	public function getModelsList(){ 
		return Entity::getModelsList();
	}
	
	/**
	* Get model details 
	* @param Int $id_entity_model
	* @return Array
	*/
	public function getModel($id_entity_model){ 
		return EntityModel::getModelData($id_entity_model);
	}
	
	/**
	* Add a new model
	* @param Array $data Array format can be found on the wiki
	* @return Int The new id_entity_model
	*/
	public function addModel($data){ 
		$model = new EntityModel();
		$model->add($data);
		return $model->id_entity_model;
	}
	
	/**
	* Update a new model
	* @param Array $data Array format can be found on the wiki
	*/
	public function updateModel(){ 
		if( isset($data['id_entity_model']) ){
			$model = new EntityModel();
			$model->add($data);
		}
	}
	
	/**
	* Delete model
	* @param Int $id_entity_model
	*/
	public function deleteModel($id_entity_model){ 
		$m = new EntityModel( $id_entity_model );
		$m->delete();
	}
	
	
	
	
	/*###########*/
	/*-- Entity --*/	
	
	/**
	* Get Entities list from the id_parent
	* @param Int $id_model
	* @param Int $id_lang
	* @param Int $id_parent
	* @param String $sort
	* @param Int $p
	* @param Int $n
	* @param Bool $with_drafts
	* @param User $user Mandatory to check permission
	* @return Array
	*/	
	public function getEntitiesList($id_model, $id_lang, $id_parent=NULL, $sort=NULL, $p=0, $n=0, $with_drafts=NULL, User $user = NULL){ 
		return Entity::getEntitiesList($id_model, $id_lang, $id_parent, $sort, $p, $n, $with_drafts, $user);
	}
	
	/**
	* Count Entities list from the id_parent
	* @param Int $id_model
	* @param Int $id_lang
	* @param Int $id_parent
	* @param String $sort
	* @param Bool $with_drafts
	* @param User $user Mandatory to check permission
	* @return Array
	*/	
	public function countEntities($id_model, $id_lang, $id_parent=NULL, $sort=NULL, $with_drafts=NULL, User $user = NULL){
		return Entity::countEntities($id_model, $id_lang, $id_parent, $sort, $with_drafts, $user);
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
	public function getEntitiesListWithAttributeValue($id_entity_model, $id_attribute_value, $id_lang, $include_data=false, $with_drafts=NULL, $sort=''){
		return getEntitiesListWithAttributeValue($id_entity_model, $id_attribute_value, $id_lang, $include_data, $with_drafts, $sort);
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
	public function getHierarchicEntitiesList($id_model, $id_lang, $id_parent=NULL, $sort='', $p=0, $n=0, $with_drafts=NULL, User $user = NULL){
		return Entity::getHierarchicEntitiesList($id_model, $id_lang, $id_parent, $sort, $p, $n, $with_drafts, $user);
	}
	
	
	/**
	* Get entity data
	* @param Integer $id_entity
	* @return Entity An entity object 
	*/
	public function getEntity($id_entity){ 
		$entity = new Entity($id_entity);
		
		if( $entity->entity_model->hierarchic == 1 ){
			
			$p = (int)Tools::getValue('p');
			$n = (int)Tools::getValue('n');
			$orderway = (int)Tools::getValue('orderway');
			$orderby = (int)Tools::getValue('orderby');
			
			$entity->children = $entity->getChildren($entity->cookie->id_lang, 0, 1000, '');
							
		}
		
		$fields = $entity->getData($entity->cookie->id_lang);
		$entity->fields = $fields;
		
		if( $entity->entity_model->hierarchic == 0 ){
			foreach( $entity->fields as &$field ){
				
				if( $field['type'] == "linkedEntities" ){
					
					$id_entities = explode(',' , $field['raw_value']);
					$entities = array();
					foreach( $id_entities as $id_entity ){
						if( $id_entity ){
							$entity = new Entity( $id_entity );
							$entity->fields = $entity->getData($entity->cookie->id_lang);
							array_push($entities, $entity);
						}
					}
					$field = $entities;
										
				}
			}
		}
		
		$b = $entity->getBreadcrumb('',$entity->cookie->id_lang); 
		$entity->breadcrumb = array_reverse($entity->breadcrumb);
		
		return $entity;
	}
	
	
	/**
	* Return slug from id_entity_model
	*/
	public function getEntitySlugAttachedToModel($id_entity_model){ 
		return Entity::getEntitySlugAttachedToModelStatic($id_entity_model);
	}
	
	/**
	* Method to add an entity. 
	* @param Array $data The array format is available in wiki
	* @return Int Id of new entity
	*/
	public function addEntity($data){ 
		$entity = new Entity();
		$entity->addEntity($data);
		return $entity->id_entity;
	}
	
	/**
	* Delete an entity
	*@param Int $id_entity
	*/
	public function deleteEntity($id_entity){ 
		$entity = new Entity( $id_entity );
		$entity->delete();
	}
	
	/**
	* Method to update an entity. 
	* @param Array $data The array format is available in wiki
	*/
	public function updateEntity(){ 
		$entity = new Entity();
		$entity->addEntity($data);
	}
	
	
	
	
	
	
	/*###########*/
	/*-- Attribute --*/
	
	/**
	* Get the attributes list
	* @param Int $id_lang
	* @return Array
	*/
	public function getAttributesList($id_lang=1){ 
		return Attribute::getAttributesList($id_lang);
	}
	
	/**
	* Get attribute values
	* @param Int $id_attribute
	* @param Int $id_lang
	* @return $id_lang
	*/
	public function getAttributeValues($id_attribute, $id_lang=0){ 
		return Attribute::getValues($id_attribute, $id_lang);
	}
	
	/**
	* Add a new attribute
	* @param Array $data Array of data | $array = array( 'slug' => String, 'name' => array( $id_lang1 => $value, $id_lang2 => $value, ... ) );
	* @return Int Id of new attribute
	*/
	public function addAttribute($data){ 
		$attribute = new Attribute();
		$attribute->slug = $data['slug'];
		$name = array();	
		foreach( $data['name'] as $id_lang => $value )
			$name[ $id_lang ] = $value;
		$attribute->name = $name;
		$attribute->add();
		return $attribute->id_attribute;
	}
	
	/**
	* Update attribute
	* @param Array $data Array of data | $array = array( 'id_attribute' => Integer, 'slug' => String, 'name' => array( $id_lang1 => $value, $id_lang2 => $value, ... ) );
	*/
	public function updateAttribute($data){ 
		$attribute = new Attribute($data['id_attribute']);
		$attribute->slug = $data['slug'];
		$name = array();	
		foreach( $data['name'] as $id_lang => $value )
			$name[ $id_lang ] = $value;
		$attribute->name = $name;
		$attribute->update();
	}
	
	/**
	* Delete attribute
	* @param Int $id_attribute
	*/
	public function deleteAttribute($id_attribute){ 
		$attribute = new Attribute($id_attribute);
		$attribute->delete();
	}
	
	/**
	* Add attribute value
	* @param Array $values Array of data | $array = array( 'id_attribute' => Integer, 'value' => array( $id_lang1 => $value, ... ) );
	* @return Int Id of new attribute value
	*/
	public function addAttributeValue($value){ 
		$attribute_value = new AttributeValue();
		$attribute_value->value = $value;
		$attribute_value->id_attribute = (int)Tools::getSuperglobal('id_attribute');
		$attribute_value->add();
		return $attribute_value->id_attribute_value;
	}
	
	/**
	* Update attribute value
	* @param Array $values Array of data | $array = array( 'id_attribute' => Integer, 'id_attribute_value' => Integer , 'value' => array( $id_lang1 => $value, ... ) );
	*/
	public function updateAttributeValue($value){ 
		$attribute_value = new AttributeValue($value['id_attribute_value']);
		$attribute_value->value = $value;
		$attribute_value->id_attribute = (int)Tools::getSuperglobal('id_attribute');
		$attribute_value->update();
	}
	
	/**
	* Delete attribute value
	* @param Int $id_attribute_value
	*/
	public function deleteAttributeValue($id_attribute_value){ 
		$attribute_value = new AttributeValue($id_attribute_value);
		$attribute_value->delete();
	}
	
	
	
	/*###########*/
	/*-- Media --*/
	
	/**
	* Create a new directory
	* @param Array $data Array of data | $array = array('id_parent' => Integer, 'dirname' => String, 'id_directory' => 0);
	*/
	public function addDirectoryMedia($data) {
		$mediabox = new Mediabox();
		$mediabox->createDirectory($data);
	}
	
	/**
	* Delete a directory
	* @param Int $id_directory
	*/
	public function deleteDirectoryMedia($data) { 
		$directory = new MediaboxDir($id_directory);
		$directory->delete();
	}
	
	/**
	* Update directory
	* @param Array $data Array of data | array = ('id_parent' => Integer, 'dirname' => String, 'id_directory' => Integer);
	*/
	public function renameDirectoryMedia() { 
		$mediabox = new Mediabox();
		$mediabox->updateDirectory($data);
	}
	
	/**
	* Create a new medias
	* @param Array $data Array of data | array = ('id_directory' => Integer, 'filename' => String, 'path' => String, "mimetype" => String, 'id_media' => 0);
	* @return Int Id of new media
	*/
	public function addMedia($data) { 
		$media = new Mediabox();
		return $media->addMedia($data);
	}
	
	/**
	* Delete a media
	* @param Int $id_media
	*/
	public function deleteMedia($id_media) { 
		$media = new Mediabox($id_media);
		$media->delete;
	}
	
	/**
	* Update media
	* @param Array $data Array of data | array = ('id_directory' => Integer, 'filename' => String, 'path' => String, "mimetype" => String, 'id_media' => Integer);
	*/
	public function renameMedia() { 
		$media = new Mediabox();
		$media->addMedia($data);
	}
	
	
	/**
	* Get directory content
	* @param Int $id_directory
	* @return Array
	*/
	public function getDirectoryContent($id_directory){
		$mediabox = new Mediabox();
		return $mediabox->getDirectoryContent($id_directory);
	}
		
		
	/**
	* Get the complete tree from the id_parent (0 = complete tree)
	* @param Int $id_parent
	* @return Array
	*/
	public function getDirectoryTree($id_parent){
		$mediabox = new Mediabox();
		return $mediabox->getDirectoryTree($id_directory);
	}
		
}




?>