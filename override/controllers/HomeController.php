<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class HomeController extends HomeControllerCore {
	
	
	public function run(){
		
		/*$actus = Entity::getEntitiesList(4, $this->cookie->id_lang, NULL, 'id_entity-desc', 0, 5);
		foreach( $actus as &$actu ){
			$a = new Entity($actu['id_entity']);
			$actu['fields'] = $a->getData($this->cookie->id_lang);
		}*/
		$actus = Entity::getEntitiesListWithAttributeValue(
			4, 
			106, 
			$this->cookie->id_lang, 
			true, 
			false, 
			'id_entity-desc'
		);
		
		$this->smarty->assign('actus', $actus);
		
		$this->getSEO();
		$templates = $this->getTemplates($this->cookie->id_lang);
		$this->sendTemplatesToSmarty( $templates );
	
	}
	
	public function getMiniatures(){
		
	}
	
}