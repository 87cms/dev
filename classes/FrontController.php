<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class FrontControllerCore extends Core {
	
	private $modulesList = array();
	private $actionsList = array();
	private $controllersList = array();
	
	private $css = array();
	
	private $js = array();
	
	private $error404 = true;
	
	private $cache;
	
	function __construct(){
		
		global $smarty, $cookie;
		$this->smarty = $smarty;
		
		$this->cookie = new Cookie();
		$this->autoRedirect();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
		
	}	
	
	public function	run(){
		$this->processController();
		$this->router();
		$this->postProcessController();
	}

	
	public function router(){
		
		$this->importLang();
		
		$this->smarty->assign('id_lang', $this->cookie->id_lang);
		$this->smarty->assign('lang_code', Lang::getLangCode($this->cookie->id_lang));

		if( (int)Tools::getSuperglobal('id_entity') ){
			
			$slug = Entity::getSlug(Tools::getSuperglobal('id_entity'));
			$is_deleted =  Entity::isDeleted(Tools::getSuperglobal('id_entity'));
			
			if( $slug && !$is_deleted ){
				
				$entityController = ucfirst($slug).'Controller';
				$eId = (int)Tools::getSuperglobal('id_entity');
				
				if( file_exists(_ABSOLUTE_PATH_.'/override/controllers/'.$entityController.'.php') ){
					$entityController = new $entityController();
					$entityController->run($eId);
				}else{
					$entityController = new EntityController();
					$entityController->run($eId);
				}
				
			}else
				$this->error404();
			
		}
		
		elseif( Tools::getSuperglobal('entity_link_rewrite') ){
			
			$model = EntityModel::getModelIdFromLinkRewrite( Tools::getSuperglobal('entity_link_rewrite'), $this->cookie->id_lang );
			$mId = $model['id_entity_model'];
			$slug = $model['slug'];
			
			if( $mId ) {
				
				$modelController = ucfirst($slug).'ModelController';
				
				if( file_exists(_ABSOLUTE_PATH_.'/override/controllers/'.$modelController.'.php') ){
					$modelController = new $modelController();
					$modelController->run($mId);
				}else{
					$modelController = new EntityModelController();
					$modelController->run($mId);
				}
			
			}
			else
				$this->error404();
			
		}
		elseif(Tools::getSuperglobal('page')){
			
			if(Tools::getSuperglobal('page') == "contact"){
				$contact = new ContactController();
				$contact->run();
			}else{
				$this->error404();
				$this->getHomepage();
			}
		}
		elseif( Tools::getSuperglobal('iso_lang') ){
			// Home page
			$this->getHomepage();
			$this->error404 = false;

		}else{
			$this->getHomepage();
			//$this->error404();
		}
	}
	
	
	/**
	* Call Classes and check if Classes are overrided or not
	*/	
	public static function loadClasses(){
		
		// Step 1 : include mandatory class
		$classes = array(
			'Lang',
			'Module', 
			'Hook', 
			'Db', 
			'AttributeValue', 
			'Attribute', 
			'EntityField', 
			'EntityModel', 
			'Mysql', 
			'Tools', 
			'Entity', 
			'EntityModelField', 
			'Cookie', 
			'Link', 
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
					require_once(_ABSOLUTE_PATH_.'/override/classes/'.$file);	
			}
		}
		
	}
	
	/**
	* Call overrided Controllers placed in "override" folder
	*/
	public static function loadControllers(){
		
		$controllers = array(
			'HomeController',
			'ContactController',
			'EntityController',
			'EntityModelController'
		);
		
		foreach( $controllers as $controller ){
			require_once(_ABSOLUTE_PATH_.'/controllers/'.$controller.'.php');
		
			if( file_exists( _ABSOLUTE_PATH_.'/override/controllers/'.$controller.'.php' ) ) 
				require_once(_ABSOLUTE_PATH_.'/override/controllers/'.$controller.'.php');	
			
			else
				eval('class '.$controller.' extends '.$controller.'Core {}');					
			
		}
		
		$dir = opendir(_ABSOLUTE_PATH_.'/override/controllers');
		while( $file = readdir($dir) ) {    
			if($file !== "." && $file !== ".." && substr($file,0,1) !== "." ){
				if( !in_array( str_replace('.php','',$file), $controllers) )
					require_once(_ABSOLUTE_PATH_.'/override/controllers/'.$file);	
			}
		}
		
	}
	
	
	/**
	* Automatically link CSS into the header
	*/	
	private function includeCss() {
		foreach( $this->modulesList as $module ){
			$css = 'modules/'.$module['name'].'/'.$module['name'].'.css' ;
			if( is_file($css) )
				$this->css[] = $css;
		}		
	}
	
	/**
	* Automatically link JS into the header
	*/
	private function includeJs() {
		foreach( $this->modulesList as $module ){
			$css = 'modules/'.$module['name'].'/'.$module['name'].'.js' ;
			if( is_file($js) )
				$this->js[] = $js;
		}	
	}
	
	
	/**
	* Get all scripts attached to modules and send to the view
	*/
	private function getScripts(){
		$this->includeCss();
		$this->includeJs();
		$this->smarty->assign('js', $js);
		$this->smarty->assign('css', $css);
		$this->smarty->assign('token', Tools::getToken());
	}
	
	
	
	/**
	* Assign default actions content
	*/
	/*private function defaultActions(){
		$actionOUT = array();
		
		foreach( self::$defaultActions as $action ){
			
			$modulesList = Modules::getModulesAttachedToAction($action);
			foreach( $modulesList as $module ){
				
				$newObject = new $module['className']();
				$actionName = 'action'.$action;
				
				if( method_exists($newObject, $actionName) ){
					$actionOUT[ $action ] .= $newObject->$actionName();
					
				}
				
			}		
		}
		
		$this->smarty->assign( array(
			'ACTION_HEAD' => $actionOUT['head'],
			'ACTION_HEADER' => $actionOUT['header'],
			'ACTION_SIDEBAR' => $actionOUT['sidebar'],
			'ACTION_FOOTER' => $actionOUT['footer']
		));
		
	}*/
	
	/**
	* Display the 404 error page
	*/
	public function error404($force=0){
		if( $this->error404 || $force ){
			header("HTTP/1.0 404 Not Found");
			$tpls = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="error404_templates"');
			$this->sendTemplatesToSmarty( $tpls );
		}
	}
	
	
	/**
	* Display the home page with the homeController
	*/
	public function getHomepage(){
		require_once(_ABSOLUTE_PATH_.'/controllers/HomeController.php');
		$homeController = new HomeController();
		$homeController->run();	
	}
	
	public function sendTemplatesToSmarty($string){
		$string = str_replace(' ', '', $string);
		$array = explode(',', $string);
		
		$module = new Module();
		$module_content = $module->getDisplay($array);
		
		if( $module_content ){
			foreach( $module_content as $hook => $content ){
				$this->smarty->assign($hook, $content);
			}
		}
		
		foreach( $array as $tpl ){
			$this->smarty->display( $tpl );
		}
	}
	
	
	public function processController(){
		$google_analytics_ID = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="google_analytics_ID" ');
		
		$this->smarty->assign(array(
			'google_analytics_ID' => $google_analytics_ID
		));
	}
	
	public function postProcessController(){
		
	}
	
	private function autoRedirect(){
		
		$default_lang = Db::getInstance()->getRow('SELECT id_lang, code FROM '._DB_PREFIX_.'lang WHERE defaultlang=1');
		$redirect = false;
		
		if( empty($_GET) && empty($_POST) ){
			if( !isset($this->cookie->id_lang) ){
				
				$lang_code = $default_lang['code'];	
				$this->cookie->id_lang = $default_lang['id_lang'];
				$redirect = true;
				
			}
			else{
				$lang = Db::getInstance()->getRow('SELECT id_lang, code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$this->cookie->id_lang);
				
				if( !$lang ){
					$lang_code = $default_lang['code'];	
					$this->cookie->id_lang = $default_lang['id_lang'];
					$redirect = true;	
				}
				else{
					$lang_code = $lang['code'];	
					$this->cookie->id_lang = $lang['id_lang'];	
					$redirect = true;
				}
				
			}
		}
		elseif( Tools::getSuperglobal('iso_lang') ){
			$lang = Db::getInstance()->getRow('SELECT id_lang, code FROM '._DB_PREFIX_.'lang WHERE code="'.Tools::cleanSQL(Tools::getSuperglobal('iso_lang')).'"');
			
			if( !$lang ){
				$lang_code = $default_lang['code'];	
				$this->cookie->id_lang = $default_lang['id_lang'];
				$redirect = true;	
			}else
				$this->cookie->id_lang = $lang['id_lang'];
		
		}
		
		if( $redirect ){
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: http://'._DOMAIN_.'/'.$lang_code.'/');
		}
		
	}
	
	private function importLang(){
		$lang_code = Lang::getLangCode($this->cookie->id_lang);
		if(is_file('lang/'.$lang_code.'.php')) 
			include('lang/'.$lang_code.'.php');
		
	}
	
	/**
	* @TODO
	* Set cache settings
	*/
	private function setCache(){
		$cache = Config::getConfig('perf_active_cache');
		if( $cache ){
			
			$caching_system = Config::getConfig('perf_cache_system');
			if( $caching_system == "smarty" ){
				$this->cache = $cache;
				$s = (int)Config::getConfig('perf_cache_refreshing');
				$this->smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
				$this->smarty->setCacheLifetime($s*60);				
			}
			
		}
		
	}
	
}

