<?php
/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */


class ConfigCore {

	/**
	* Get a configuration value
	* @param String $name
	* @return String
	*/
	public static function getConfig($name){
		return DB::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name=:name', array('name'=>$name));
	}
	
	
	/**
	* Set a configuration value. If value already exists, the value is overwritted
	* @param String $name
	* @param String $value
	* @return Bool
	*/
	public static function setConfig($name, $value){
		$inDB = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'config WHERE name="'.Tools::cleanSQL($name).'"');
		
		if( $inDB )
			return Db::getInstance()->UpdateDB(_DB_PREFIX_.'config', array('value'=>$value), array('name'=>$name));
		
		else
			return Db::getInstance()->Insert(_DB_PREFIX_.'config', array('value'=>$value, 'name'=>$name));	
		
	}
	
	
}