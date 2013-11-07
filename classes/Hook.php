<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class HookCore extends Core {
	
	protected $table = 'hook';	
	protected $identifier = 'id_hook';
		
	
	public static function getHooks(){
		return Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'hook WHERE deleted=0');
	}
	
	
	public static function getHooksWithModules(){
		$hooks = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'hook WHERE deleted=0');
		foreach( $hooks as &$hook ){
			
			$hook['module'] = Db::getInstance()->Select('
				SELECT * FROM '._DB_PREFIX_.'module_hook H
				LEFT JOIN '._DB_PREFIX_.'module M ON H.id_module = M.id_module
				WHERE H.id_hook='.(int)$hook['id_hook'].'
				ORDER BY position				
			');
		}
		
		return $hooks;
	}
	
	public static function attachModule($id_module, $id_hook){
		$position = Db::getInstance()->getValue('SELECT MAX(position) FROM '._DB_PREFIX_.'module_hook WHERE id_hook='.(int)$id_hook);
		$position += 1;
		Db::getInstance()->Insert(_DB_PREFIX_.'module_hook', array('id_module' => (int)$id_module, 'id_hook'=>(int)$id_hook, 'position'=>$position) );	
	}
	
	public static function detachModule($id_module, $id_hook){
		$modules = Db::getInstance()->Select('
				SELECT * FROM '._DB_PREFIX_.'module_hook H
				LEFT JOIN '._DB_PREFIX_.'module M ON H.id_module = M.id_module
				WHERE H.id_hook='.(int)$id_hook.'
				ORDER BY position			
			');
		
		Db::getInstance()->Delete(_DB_PREFIX_.'module_hook', array('id_hook' => $id_hook ) );
		$z=1;
		foreach( $modules as $module ){
			if( $module['id_module'] !== $id_module ){
				Db::getInstance()->Insert(_DB_PREFIX_.'module_hook', array('id_module' => $module['id_module'], 'id_hook'=>(int)$id_hook, 'position'=>$z));
				$z++;	
			}
		}
			
	}
	
	public static function hookExists($smarty_name, $method_name){
		return Db::getInstance()->getValue('SELECT id_hook FROM '._DB_PREFIX_.'hook WHERE smarty_name=:smarty_name AND method_name=:method_name', array('smarty_name' => $smarty_name, 'method_name' => $method_name));
	}
	
}