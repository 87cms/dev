<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class AjaxController extends AdminController {
	
	
	function __construct(){
		
		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		
		// User is logged ?
		$this->user = new User();
		
		$this->user->authUser($this->cookie->emailp, $this->cookie->hashp);
		
		if( Tools::getSuperglobal('submitLogin') ){
			$this->user->authUser(Tools::getSuperglobal('login_email'), '', Tools::getSuperglobal('login_password'));
			if( $this->user->is_logged ){
				$this->cookie->emailp = $this->user->email; 
				$this->cookie->hashp = hash('sha256', $this->user->password);	
				Tools::redirect('/admin/index.php');		
			}
		}
		
		if( !$this->user->is_logged )
			die();
	}
	
	
	public function start(){
			
		/**
		* Mediabox
		*/
		if( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "getDirectoryContent" ){	
			$mediabox = new Mediabox(); 
			$_SESSION['id_current_directory'] = (int)Tools::getSuperglobal('id_directory');
			echo json_encode( $mediabox->getDirectoryContent( (int)Tools::getSuperglobal('id_directory') ));
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "addDirectory" ){	
			$mediabox = new Mediabox();
			$data['dirname'] = Tools::getSuperglobal('directory_name');
			$data['id_parent'] = Tools::getSuperglobal('id_parent');
			return $mediabox->createDirectory( $data );
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "deleteFile" ){	
			$mediabox = new Mediabox();
			$mediabox->deleteMedia( (int)Tools::getSuperglobal('id_media') );
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "deleteFolder" ){	
			$mediabox = new Mediabox();
			$mediabox->deleteDirectory( (int)Tools::getSuperglobal('id_directory') );
		}
		
		elseif(  Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "updateDirectoryName" ){	
			$mediadir = new MediaboxDir( Tools::getSuperglobal('id_directory') );
			if( $mediadir->id_directory > 0 ){
				$mediadir->dirname = Tools::getSuperglobal('name');
				$mediadir->update();
			}
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "getDirectoryTree" ){	
			$mediabox = new Mediabox(); 
			echo json_encode( $mediabox->getDirectoryTree(Tools::getSuperglobal('id_parent'))  );
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "moveFiles" ){	
			$mediabox = new Mediabox(); 
			$ids_files = json_decode( Tools::getSuperglobal('files') );
			$id_directory_to = Tools::getSuperglobal('id_directory_to');
			$mediabox->moveFiles($ids_files, $id_directory_to);
		}
		
		elseif( Tools::getSuperglobal('mediabox') && Tools::getSuperglobal('action') == "moveDirectories" ){	
			$mediabox = new Mediabox(); 
			$ids_files = json_decode( Tools::getSuperglobal('directories') );
			$id_directory_to = Tools::getSuperglobal('id_directory_to');
			$mediabox->moveDirectories($ids_files, $id_directory_to);
		}
		/*
		END Mediabox
		*/
		
		
		
		elseif( Tools::getSuperglobal('action') == "getAttributesList" ){	
			echo json_encode( Attribute::getAttributesList($this->cookie->id_lang) );		
		}
		
		elseif( Tools::getSuperglobal('action') == "addEntityModel" ){
			$data = json_decode(Tools::getSuperglobal('data'));
			$entity_model = new EntityModel( $data->id_entity_model );
			$entity_model->addModel($data);
		}
		
		elseif( Tools::getSuperglobal('action') == "addEntity" ){
			$data = json_decode(Tools::getSuperglobal('data'));
			$entity = new Entity( $data->id_entity );
			$entity->addEntity($data);
		}
		
		elseif( Tools::getSuperglobal('action') == "getModelsList" ){
			echo json_encode(EntityModel::getModels());
		}
		
		elseif( Tools::getSuperglobal('action') == "getFieldModelsList" ){
			echo json_encode(EntityModelField::getFieldModelsList( Tools::getSuperglobal('id_entity_model'), Tools::getSuperGlobal('field_type') ));
		}
		
		elseif( Tools::getSuperglobal('action') == "updatePositionEntities" ){
			Entity::updatePositionEntities(Tools::getValue('id_parent'), Tools::getValue('positions'), Tools::getValue('id_entity_model'));
		}

	}

}

?>