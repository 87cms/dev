<?php

class ModulesController extends AdminController {
	
	
	public function run(){
		
		$hook_list = Hook::getHooks();
		
		if( isset($this->id_module) && $this->id_module > 0 ){
			
			$module = new Module($this->id_module);
			if( $module->id_module ){
				$module_name = strtolower($module->slug);
				require_once(_MODULES_DIR_.'/'.$module_name.'/'.$module_name.'.php');
				$class = ucfirst($module_name);
				$m = new $class($this->id_module);
				
			}
			
			if( $m && Tools::getSuperglobal('action') == "configure" )
				$m->displayAdmin();
				
		} else {
			
			$this->smarty->assign(array(
				'installed_modules' => Module::getModulesList(1),
				'available_modules' => Module::getModulesList(0),
				'hook_list' => $hook_list
			));
			
			if( Tools::getValue('manageHook') ){
				$this->smarty->assign( 'hooksfull', Hook::getHooksWithModules());
				$this->smarty->display('modules_hook.html');
				
			}
			else
				$this->smarty->display('modules.html');
		
		}
	
	}
	
	
	public function preprocess(){
		
		$this->id_module = Tools::getSuperglobal('id_module');
		
		if( Tools::getSuperglobal('installModule') ){
				$module = new Module();
				$module->module_name = Tools::getSuperglobal('name');
				$module->module_description = Tools::getSuperglobal('description');
				$module->slug = Tools::getSuperglobal('slug');
				$module->active = 1;
				$module->add();
				
				if( $module->id_module ){
					$module_name = strtolower($module->slug);
					require_once(_MODULES_DIR_.'/'.$module_name.'/'.$module_name.'.php');
					$class = ucfirst($module_name);
					$m = new $class( $module->id_module );
					$m->installModule();
				}				
				
				Tools::redirect('/admin/index.php?p=modules');
		
		}elseif( Tools::getValue('action') == 'deleteHook' && Tools::getValue('id_hook') > 0 ){
			$hook = new Hook( Tools::getValue('id_hook') );
			$hook->delete();
			Tools::redirect('/admin/index.php?p=modules&manageHook=1');		
		
			
		}elseif( Tools::getValue('action') == 'addHook' ){
			$hook = new Hook();
			$hook->smarty_name = Tools::getValue('smarty_name');		
			$hook->method_name = Tools::getValue('method_name');
			$hook->add();
			Tools::redirect('/admin/index.php?p=modules&manageHook=1');
		
		
		}elseif( Tools::getValue('attachModule') ){
			Hook::attachModule( (int)Tools::getValue('id_module'), (int)Tools::getValue('id_hook') );
			Tools::redirect('/admin/index.php?p=modules&manageHook=1');
		
			
		}elseif( Tools::getValue('detachModule') ){
			Hook::detachModule( Tools::getValue('id_module'), Tools::getValue('id_hook') );
			Tools::redirect('/admin/index.php?p=modules&manageHook=1');
		
		}
		
		else if( isset($this->id_module) && $this->id_module > 0 ){
			
			$module = new Module($this->id_module);
			if( $module->id_module ){
				$module_name = strtolower($module->slug);
				require_once(_MODULES_DIR_.'/'.$module_name.'/'.$module_name.'.php');
				$class = ucfirst($module_name);
				$m = new $class($this->id_module);
				
			}
			
			if( $m && Tools::getSuperglobal('action') == "activate" ){
				$m->active = Tools::getSuperglobal('value');
				$m->update();
				Tools::redirect('/admin/index.php?p=modules');	
				
			}
			elseif( $m && Tools::getSuperglobal('action') == "delete" ){
				
				$m->uninstallModule();
				$m->delete();
				Tools::redirect('/admin/index.php?p=modules');	
							
			}
			
		}
			
	}
	
}