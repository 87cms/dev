<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class HomeControllerCore extends FrontController {
	
	
	public function run(){
		$this->smarty->assign('ishome', 1);
		$this->getSEO();
		$templates = $this->getTemplates($this->cookie->id_lang);
		$this->sendTemplatesToSmarty( $templates );
	
	}
	
	
	public function getSEO(){
		$data = Db::getInstance()->Select('
			SELECT name,value FROM '._DB_PREFIX_.'config_lang 
			WHERE 
				( name="meta_description" 
				OR name="meta_title" 
				OR name="meta_keywords" )
				AND id_lang='.(int)$this->cookie->id_lang);
		$seo = array();
		foreach( $data as $d )
			$seo[ $d['name'] ] = $d['value'];
		
		$sitename = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="sitename"');
		$slogan = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="slogan"');
		
		$this->smarty->assign(array(
			'seo' => $seo,
			'slogan' => $slogan,
			'sitename' => $sitename
		));		
	}
	
	
	public function getTemplates($id_lang){
		$code = Lang::getLangCode($id_lang);
		return Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="homepage_template_'.$code.'"');	
	}
	
	
}