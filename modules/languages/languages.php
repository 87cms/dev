<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Modules
 */

class Languages extends Module implements ModuleInterface {
	
	public $name = "Languages";
	public $description = "Display a lang selection block.";
	public $slug = "languages";
	
	public $hook_name = "HOOK_SELECT_LANG";
	public $method_name = "displaySelectLang";
	
	public function start(){
			
	}	
	
	public function displaySelectLang(){
		
		$this->initController();
		
		$langs = Lang::getLanguages();
		$links = array();
		
		foreach( $langs as &$lang ){
			$link = '';
			if( (int)Tools::getSuperglobal('id_entity') ){
				$link = Link::getEntityLink( (int)Tools::getSuperglobal('id_entity') , $lang['id_lang']);
			}
			
			elseif( Tools::getSuperglobal('entity_link_rewrite') ){
				$model = EntityModel::getModelIdFromLinkRewrite( Tools::getSuperglobal('entity_link_rewrite'), $this->cookie->id_lang );
				$mId = $model['id_entity_model'];
				$link = Link::getEntityModelLink($mId, $lang['id_lang']);
				
			}
			
			else {
				$uri = $_SERVER["REQUEST_URI"];	
				$ex = explode('/', $uri);
				$url = '';
				for( $i=2; $i < count($ex); $i++ )
					$url .= $ex[$i].'/';
				$link = 'http://'._DOMAIN_.'/'.$lang['code'].'/'.rtrim($url, '/');				
			}
			
			$lang['link'] = $link;
		}

		$this->smarty->assign('langs', $langs);			
		$this->smarty->display('modules/languages/languages.html');
	
	}
	
}




