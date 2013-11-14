<?php

class AdminControllerCore extends Core {

	protected $smarty;
	protected $cookie;
	
	public $action;
	protected $user;
	
	function __construct(){
		
		global $smarty, $cookie;
		$this->smarty = $smarty;

		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
		
	}
	
	public function start(){
		
		Tools::setToken();
		$this->smarty->assign('token', Tools::getToken());
		$this->sendLangToSmarty();
		
		$page = Tools::getSuperglobal('p');
		
		if( Tools::getSuperglobal('logout') == 1 ){
			$this->cookie->destroy();
			Tools::redirect('/admin/index.php?p=login'); 
		}
	
		
		// 1. User is logged ?
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
		
		if( !$this->user->is_logged && $page !== "login" )
			Tools::redirect('/admin/index.php?p=login'); 
			
		
		
		// 2. Menu
		$menu = $this->getMenu();
		$menuModules = Module::getModulesListForAdminMenu();
		
		$this->smarty->assign(array(
			'menu' => $menu,
			'menuModules' => $menuModules,
			'user' => $this->user			
		));
		
		
		if( !empty($page) /*&& self::validatePage($page)*/ ){
			
			if( $page == "login" ){
				$this->smarty->display('login.html');
				die();	
			}
			
			

			$controllerName = ucfirst($page).'Controller';
			
			if( file_exists('controller/'.$controllerName.'.php') ){
				require_once('controller/'.$controllerName.'.php');
				$controller = new $controllerName();
				
				$controller->preprocess($this->user);
				
				$this->smarty->display('head.html');
				$this->smarty->display('sidebar.html');
				
				$controller->run($this->user);
				
				$this->smarty->display('footer.html');
			}
			
			
		
		}else{
			
			$this->displayHome();
		
		}
	
	}
	
	private function sendLangToSmarty(){
		$languages = Lang::getLanguages();
		$this->smarty->assign('LANG', Lang::getLangCode($this->cookie->id_lang) );
		$this->smarty->assign('ID_LANG', (int)$this->cookie->id_lang );
		$this->smarty->assign('languages', $languages);	
	}
	
	private function displayHome(){
		$this->smarty->display('head.html');
		$this->smarty->display('sidebar.html');
		$this->smarty->display('home.html');
		$this->smarty->display('footer.html');
	}
	
	private static function validatePage(){
		return true;
	}
	
	private function getMenu(){
		$results = array();
		$sql =	'SELECT * FROM '._DB_PREFIX_.'model_entity M
		LEFT JOIN '._DB_PREFIX_.'model_entity_lang L ON M.id_entity_model = L.id_entity_model
		WHERE L.id_lang ='.(int)$this->cookie->id_lang.'
		AND M.deleted=0
		ORDER BY hierarchic DESC';
		$elements = Db::getInstance()->Select($sql);
		
		if( $this->user->is_admin )
			return $elements;
		
		else {
			$array = array();
			for($i=0;$i<count($elements);$i++){
				if( $this->user->userHasPermission($elements[$i]['id_entity_model'], -1) )
					array_push( $array, $elements[$i] );
			}
			return $array;
		}
		
	}
	
	public static function loadElements(){
		// Step 1 : include mandatory class
		$classes = array(
			'Cookie',
			'Lang',
			'FrontController',
			'Module',
			'Hook',
			'Db',
			'MediaboxFile',
			'AttributeValue',
			'Attribute',
			'EntityField',
			'EntityModel',
			'Richtext',
			'MediaboxDir',
			'SimpleImage',
			'Mediabox',
			'Mysql',
			'Tools',
			'Entity',
			'EntityModelField',
			'Error',
			'Link',
			'UploadHandler',
			'User',
			'Contact',
			'Config'
		);
		
		foreach( $classes as $class ){
			require_once(_ABSOLUTE_PATH_.'/classes/'.$class.'.php');
		
			if( file_exists( _ABSOLUTE_PATH_.'/override/classes/'.$class.'.php' ) ) 
				require_once(_ABSOLUTE_PATH_.'/override/classes/'.$class.'.php');	
			
			else
				eval('class '.$class.' extends '.$class.'Core {}');					
			
		}
		
		// Step 2 : include user added classes in override folder
		$dir = opendir(_ABSOLUTE_PATH_.'/override/classes');
		while( $file = readdir($dir) ) {    
			if($file !== "." && $file !== ".." && substr($file,0,1) !== "." ){
				if( !in_array( str_replace('.php','',$file), $classes) )
					require_once($overrideDirectory.'/'.$file);	
			}
		}
	
	}
	
}




