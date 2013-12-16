<?php


class UsersController extends AdminController {
	
	
	public function run(){
		
		
		
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "form" ){
				
				$models = EntityModel::getModels($this->cookie->id_lang_admin);
				foreach( $models as &$model )
					$model['entities'] = Entity::getEntitiesList($model['id_entity_model'], $this->cookie->id_lang_admin, NULL, 'id_entity-asc', 0, 1000, true);
				
				$user = new User( Tools::getSuperglobal('id_user') );
				$permissions = $user->getPermissions();
				
				$this->smarty->assign( array(
					'models' => $models,
					'user' => $user,
					'permissions' => $permissions
				));
				$this->smarty->display('user_form.html');
			}
			
			
			
		}
		else{
			
			if( Tools::getSuperglobal('submitUser')  ){
				
				if( Tools::getSuperglobal('id_user') )
					$user = new User(Tools::getSuperglobal('id_user'));
				else
					$user = new User();
					
				$user->lastname = Tools::getSuperglobal('lastname');
				$user->firstname = Tools::getSuperglobal('firstname');
				$user->email = Tools::getSuperglobal('email');
	
				if( Tools::getSuperglobal('password') )
					$user->password = create_hash(Tools::getSuperglobal('password'));
				
				$user->is_admin = Tools::getSuperglobal('is_admin');
				
				if( $user->id_user )
					$user->update();
				else
					$user->add();
				
				$permissions = array();
				foreach( $_POST as $key => $value ){
					if( strrpos($key, 'permission') > -1 ){
						$explode = explode('#', $key);
						$id_entity_model = $explode[1];
						$id_entity = $explode[2];
						array_push($permissions, array(
							'id_user' => $user->id_user,
							'id_entity_model' => $id_entity_model,
							'id_entity' => $id_entity
						));
					}
					
				}
				
				if( !$user->is_admin )
					$user->setPermissions($permissions);
				
				Tools::redirect('/admin/index.php?p=users&added=1');
				
			}
			
			$users = User::getUsersList(); 
			$this->smarty->assign( array(
					'users' => $users,
				));
			$this->smarty->display('user.html');
		
		}
	}
	
	
	public function preprocess(){ 
		$this->action = Tools::getSuperglobal('action');
		if( isset($this->action) && !empty($this->action) && $this->action == "deleteUser" ){
			$user = new User( Tools::getSuperglobal('id_user') );
			$user->delete();
			Tools::redirect('/admin/index.php?p=users&added=1');
		}	
	}
	
	
}