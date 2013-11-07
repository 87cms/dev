<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityModelControllerCore extends FrontController {
	
	protected $entity_model;
	protected $id_current_model;
	
	/**
	* Run the model entity controller. 
	* First we check if the model exists.
	* Then we check if a specific class or controller exists. 
	* In deed you can add a specific class or controller per Slug. You have to add a process() method in your specific controller
	* If yes the display process will be executed the specific controller.
	* @param Int $id Entity's ID
	*/	
	public function run($id){
		
		$this->entity_model = new EntityModel($id);

		if( $this->entity_model->id_entity_model ){
			
			$this->id_current_model = $this->entity_model->id_entity_model;
			
			$this->process();
			
		}else
			$this->error404(1);	
			
	}
	
	
	
	public function process(){
		
		if( $this->entity_model->hierarchic ){
			
			$p = (int)Tools::getValue('p');
			$n = (int)Tools::getValue('n');
			$orderway = (int)Tools::getValue('orderway');
			$orderby = (int)Tools::getValue('orderby');
			
			$this->entity_model->children = $this->entity_model->getHierarchicTree(0, $this->cookie->id_lang);
			
			if( $this->entity_model->children ){
				foreach( $this->entity_model->children as &$children ){
					$entity = new Entity( $children['id_entity'] );
					$children['fields'] = $entity->getData($this->cookie->id_lang);
					$children['link_rewrite'] = Link::getEntityLink($children['id_entity'], $this->cookie->id_lang);
				}
			}
					
		}else {
			
			$p = (int)Tools::getValue('p');
			$n = (int)Tools::getValue('n');
			$orderway = (int)Tools::getValue('orderway');
			$orderby = (int)Tools::getValue('orderby');
			
			$this->entity_model->children = Entity::getEntitiesList($this->entity_model->id_entity_model, $this->cookie->id_lang, NULL, 'id_entity-desc', 0, 5);
			
			if( $this->entity_model->children ){
				foreach( $this->entity_model->children as &$children ){
					$entity = new Entity( $children['id_entity'] );
					$children['fields'] = $entity->getData($this->cookie->id_lang);
					$children['link_rewrite'] = Link::getEntityLink($children['id_entity'], $this->cookie->id_lang);
				}
			}	
			
		}

		$this->smarty->assign(array(
			$this->entity_model->slug => $this->entity_model,
			'active_entity' => array('id_model_entity' => $this->entity_model->id_entity_model)
		));
				
		$this->getSEO();
		$this->display();
			
	}
	
	
	
	
	
	/**
	* The classic display method
	*/
	public function display(){
		$tpls = $this->entity_model->getTemplates();
		$this->sendTemplatesToSmarty( $tpls );		
	}
	
	/**
	* Set SEO elements as meta elements, or canonical
	* TODO : canonical
	*/
	public function getSEO(){
		$seo = Db::getInstance()->getRow('
			SELECT link_rewrite, meta_title, meta_keywords, meta_description FROM '._DB_PREFIX_.'model_entity_lang 
			WHERE id_lang='.(int)$this->cookie->id_lang.' AND id_entity_model='.(int)$this->entity_model->id_entity_model);
		
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