<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class LangCore extends Core {

	public static function getLanguages(){
		return Db::getInstance()->Select('SELECT id_lang,name,code,defaultlang FROM '._DB_PREFIX_.'lang WHERE active=1 ORDER BY defaultlang DESC');		
	}
	
	public static function getLangCode($id_lang){
		$code = Db::getInstance()->getValue('SELECT code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang);
		if( $code )
			return $code;
		else
			return 'en';
	}
	
}

