<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class PageController extends EntityController {
	
	protected $entity;
	protected $entity_model;
	protected $id_current_entity;
	
	public function run($id=0){
		
		$this->entity = new Entity($id);
		
		$this->entity->link_rewrite = Link::getEntityLink($this->entity->id_entity, $this->cookie->id_lang);
		
		if( $this->entity->id_entity && $this->entity->state !== 'draft' ){
			
			$this->id_current_entity = $this->entity->id_entity;
			
			// get entity slug
			$this->entity_model = new EntityModel( $this->entity->id_entity_model );
			$slug = $this->entity_model->slug;
			
			if( $this->entity->id_entity == 45 ){
				$files = Entity::getEntitiesList(6, $this->cookie->id_lang);
				foreach($files as &$file){
					$tmpE = new Entity( $file['id_entity'] );
					$file['fields'] = $tmpE->getData( $this->cookie->id_lang );
				}
				$this->smarty->assign('fichiers', $files);
			}
			
			$this->process();
			
		}else
			$this->error404(1);	
			
	}
	
}