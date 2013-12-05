<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityControllerCore extends FrontController {
	
	protected $entity;
	protected $entity_model;
	protected $id_current_entity;
	
	/**
	* Run the entity controller. 
	* First we check if the entity exists, and if it's not a draft.
	* Then we check if a specific class or controller exists. 
	* In deed you can add a specific class or controller per Slug. You have to add a process() method in your specific controller
	* If yes the display process will be executed the specific controller.
	* @param Int $id Entity's ID
	*/	
	public function run($id=0){
		
		$this->entity = new Entity($id);
		
		$this->entity->link_rewrite = Link::getEntityLink($this->entity->id_entity, $this->cookie->id_lang);
		
		if( $this->entity->id_entity && ( $this->entity->state !== 'draft' || $this->user->is_logged ) ){
			
			$this->id_current_entity = $this->entity->id_entity;
			
			$this->entity_model = new EntityModel( $this->entity->id_entity_model );
			
			$this->process();
			
		}else
			$this->error404(1);	
			
	}
	
	public function process(){
		
		
		if( $this->entity_model->hierarchic == 1 ){
			
			$p = (int)Tools::getValue('p');
			$n = (int)Tools::getValue('n');
			$orderway = (int)Tools::getValue('orderway');
			$orderby = (int)Tools::getValue('orderby');
			
			$this->entity->children = $this->entity->getChildren($this->cookie->id_lang, 0, 1000, '');
							
		}
		
		$this->entity->getData($this->cookie->id_lang);
		
		if( $this->entity_model->hierarchic == 0 ){
			foreach( $this->entity->fields as &$field ){
				
				if( is_array($field) && $field['type'] == "linkedEntities" ){
					
					$id_entities = explode(',' , $field['raw_value']);
					$entities = array();
					foreach( $id_entities as $id_entity ){
						if( $id_entity ){
							$entity = new Entity( $id_entity );
							$entity->getData($this->cookie->id_lang);
							array_push($entities, $entity);
						}
					}
					$field = $entities;
										
				}
			}
		}
		
		
		
		$b = $this->entity->getBreadcrumb($this->cookie->id_lang); 
		$this->entity->breadcrumb = array_reverse($this->entity->breadcrumb);
		
		// Add in view the active state for link
		// If you want to add this state to an link, simply add class "entity + ID" or "entityModel + ID"
		// For ex : entity87 or entityModel13
		$active_entity = $this->entity->breadcrumb;
		array_push($active_entity, array(
			'id_entity' => $this->entity->id_entity,
			'id_entity_model' => $this->entity->id_entity_model
		));
		
		
		$this->smarty->assign(array(
			$this->entity->slug => $this->entity,
			'active_entity' => $active_entity
		));
				
		$this->getSEO();
		$this->display();
			
	}
	
	
	
	
	
	/**
	* The classic display method
	*/
	public function display(){
		$tpls = $this->entity->getTemplates();
		$this->sendTemplatesToSmarty( $tpls );		
	}
	
	/**
	* Set SEO elements as meta elements, or canonical
	* TODO : canonical
	*/
	public function getSEO(){
		$seo = Db::getInstance()->getRow('
			SELECT link_rewrite, meta_title, meta_keywords, meta_description FROM '._DB_PREFIX_.'entity_lang 
			WHERE id_lang='.(int)$this->cookie->id_lang.' AND id_entity='.(int)$this->entity->id_entity);
		$sitename = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="sitename"');
		$slogan = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="slogan"');
		
		$this->smarty->assign(array(
			'seo' => $seo,
			'slogan' => $slogan,
			'sitename' => $sitename
		));
		$this->smarty->assign('seo', $seo);			
		
	}
	
	
}