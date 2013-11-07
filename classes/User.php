<?php


class UserCore extends Core {
	
	protected $table = 'user';	
	protected $identifier = 'id_user';
	
	public $is_logged = 0;
	
	public static function getUsersList(){
		return Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'user	 WHERE deleted=0');
	}
	
	public function delete(){
		Db::getInstance()->Delete(_DB_PREFIX_.'user_permission', array('id_user' => $this->id_user) );
		parent::delete();
	}
	
	public function setPermissions($permissions){
		Db::getInstance()->Delete(_DB_PREFIX_.'user_permission', array('id_user' => $this->id_user) );
		
		foreach( $permissions as $permission )
			Db::getInstance()->Insert(_DB_PREFIX_.'user_permission', $permission);	
	}
	
	public function getPermissions(){
		$lines = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'user_permission WHERE id_user='.(int)$this->id_user);
		$permissions = array();
		foreach( $lines as $line ){
			array_push($permissions, 'permission#'.$line['id_entity_model'].'#'.$line['id_entity']);
		}
		return $permissions;
	}
	
	
	public function userHasPermission($id_entity_model, $id_entity=0){
		$access = false;
		
		if( $id_entity_model > 0 && $id_entity < 0 ){
			$access = Db::getInstance()->getValue('
				SELECT id_entity_model FROM '._DB_PREFIX_.'user_permission 
				WHERE 
					id_user='.(int)$this->id_user.' 
					AND id_entity_model='.(int)$id_entity_model.'
			');
			
		}
		elseif( $id_entity_model > 0 && $id_entity >= 0 ){
			$access = Db::getInstance()->getValue('
				SELECT id_entity_model FROM '._DB_PREFIX_.'user_permission 
				WHERE 
					id_user='.(int)$this->id_user.' 
					AND id_entity_model='.(int)$id_entity_model.'
					AND id_entity = '.($id_entity).'
				');	
		}
		
		return $access;
	}
	
	public function authUser($email, $hash, $password=''){
		$validate = false;
		// from cookie : hash in sha256
		if( !empty($hash) && !empty($email) ){
			$users = self::getUsersList();
			foreach( $users as $user ){
				if( $hash == hash('sha256', $user['password']) && $email == $user['email'] )
					$validate = true;
			}
		}
		// from form
		elseif( !empty($password) ){
			$good_hash = Db::getInstance()->getValue('SELECT password FROM '._DB_PREFIX_.'user WHERE email=:email', array('email' => $email));
			if( !$good_hash )
				return false;
			$validate = validate_password($password, $good_hash);			
		}
		
		if( $validate ){
			$id_user = Db::getInstance()->getValue('SELECT id_user FROM '._DB_PREFIX_.'user WHERE email=:email', array('email' => $email));
			$this->setObject($id_user);
			$this->is_logged = 1;
		}
		
	}
	
}