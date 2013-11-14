<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class ModuleCore extends Core {
	
	private $module_dir;
	
	protected $table = 'module';	
	protected $identifier = 'id_module';
	

	public function getDisplay($arrayTpl){
		
		$hooks = Hook::getHooks();
		
		$module_content = array();
		
		foreach( $arrayTpl as $tpl ){
			
			$handle = fopen('template/'.$tpl, "r");
			$content = @fread($handle, filesize('template/'.$tpl));
			
			foreach( $hooks as $hook ){
				
				if( preg_match('/\{\$'.$hook['smarty_name'].'\}/', $content ) ){
					
					$modulesToStart = Db::getInstance()->Select('
						SELECT M.* FROM '._DB_PREFIX_.'module M
						LEFT JOIN '._DB_PREFIX_.'module_hook H ON M.id_module=H.id_module
						WHERE
							M.active=1
							AND H.id_hook='.(int)$hook['id_hook'].'
						ORDER BY position ASC
					');

					ob_start();
					
					foreach( $modulesToStart as $module ){
						$module['name'] = strtolower($module['slug']);
						require_once('modules/'.$module['slug'].'/'.$module['slug'].'.php');
						$class = ucfirst($module['slug']);
						
						$m = new $class;
						
						$reflectionMethod = new ReflectionMethod($class, $hook['method_name']);
						if( $reflectionMethod->isPublic() )
							$reflectionMethod->invoke($m, $hook['method_name']);
						
					}
					
					$output = ob_get_contents();
					ob_end_clean();

					$module_content[ $hook['smarty_name'] ] = $output;
					
					
				}
				
			}
		
		}
		
		return $module_content;
		
	}
	
	
	
	/*public static function getModulesToDisplay($displayName){
		$rq = '
			SELECT M.* FROM '._DB_PREFIX.'modules_actions A
			LEFT JOIN '._DB_PREFIX_.'modules M ON A.id_module = M.id_module
			WHERE A.name=:actionName		
		';
		return Db::getInstance()->Select($rq, array('actionName'=>$actionName));	
	}*/
	
	
	
	/*--- Admin ---*/
	
	public static function downloadModule(){
		
	}
	
	
	
	public static function getModulesList($installed=0){
		$modules = array();
		
		if( $installed )
			$modules = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'module');	
		
		else {
			$i = 0;
			$rawmodules = scandir(_MODULES_DIR_);
			foreach( $rawmodules as $module ){
				if( $module !== '.' && $module !== ".." && file_exists(_MODULES_DIR_.'/'.$module.'/'.$module.'.php') ){
					require_once(_MODULES_DIR_.'/'.$module.'/'.$module.'.php');
					$class = ucfirst($module);
					$m = new $class;
					if( $m ){
						$modules[$i]['slug'] = $module;
						$modules[$i]['name'] = $m->name;
						$modules[$i]['description'] = $m->description;
						$i++;
					}
				}		
			}
		}		
		return $modules;
	}
	
	
	public static function getModulesListForAdminMenu(){
		$modules = self::getModulesList(1);	
		$menu = array();
		foreach( $modules as $module ){		
		
			if( file_exists(_MODULES_DIR_.'/'.$module['slug'].'/'.$module['slug'].'.php') ){
				require_once(_MODULES_DIR_.'/'.$module['slug'].'/'.$module['slug'].'.php');
				$class = ucfirst($module['slug']);
				
				$m = new $class;
				if( $m ){
					if( isset($m->menu) )
						array_push($menu, array( 'id_module'=>$module['id_module'], 'name'=>$m->menu ));
				}
				
			}
		
		}
		return $menu;
	}
	
	public function delete(){
		Db::getInstance()->Delete($this->table, array('id_module' => $this->id_module));
	}
	
	public function displayAdmin(){
		$this->initController();	
	}
	
	public function installModule(){
		
		if( $this->hook_name && $this->method_name ){

			$id_hook = Hook::hookExists($this->hook_name, $this->method_name);
			if( !$id_hook ){
				$hook = new Hook();
				$hook->smarty_name = $this->hook_name;
				$hook->method_name = $this->method_name;
				$hook->add();
			}else
				$hook = new Hook($id_hook);
			Hook::attachModule( $this->id_module, $hook->id_hook );
			
		}
		
	}
	
	public function uninstallModule(){
		Db::getInstance()->Delete(_DB_PREFIX_.'module_hook', array('id_module' => $this->id_module));
		Db::getInstance()->Delete(_DB_PREFIX_.'module', array('id_module' => $this->id_module));
	}
	
	
	public function initController(){
		global $smarty, $cookie;
		$this->smarty = $smarty;

		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
			
	}
	
}

interface ModuleInterface {

	public function displayAdmin();
	
	public function start();
	
}