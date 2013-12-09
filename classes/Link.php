<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class LinkCore extends Core {
	
	public static function getEntityLink($id_entity, $id_lang=''){
		
		global $cookie;
		if( empty($id_lang) )
			$id_lang = $cookie->id_lang;
		
		$link_rewrite = Db::getInstance()->getValue(
			'SELECT link_rewrite FROM '._DB_PREFIX_.'entity_lang 
				WHERE id_entity=:id_entity 
					AND id_lang=:id_lang', 
			array('id_entity'=> $id_entity, 'id_lang' => $id_lang ));
		
		if( !$link_rewrite )
			return false;
		
		$iso_lang = Db::getInstance()->getValue('SELECT code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang);
		
		$is_hierarchic = Db::getInstance()->getValue(
			'SELECT M.hierarchic FROM '._DB_PREFIX_.'model_entity M
			LEFT JOIN '._DB_PREFIX_.'entity E ON E.id_entity_model=M.id_entity_model
			WHERE E.id_entity=:id_entity'
		, array('id_entity'=> $id_entity));
		
		
		/*if( $is_hierarchic ){
			$link = 'http://'._DOMAIN_.'/'.$iso_lang.'/'.$id_entity.'-'.$link_rewrite;
		}
		else{
			$link = 'http://'._DOMAIN_.'/'.$iso_lang.'/'.$id_entity.'-'.$link_rewrite;
		}*/
			
		
		$link = 'http://'._DOMAIN_.'/'.$iso_lang.'/'.$id_entity.'-'.$link_rewrite;
		
		
		return $link;
	}
	
	
	public static function getEntityModelLink($id_entity_model, $id_lang=''){
		
		global $cookie;
		if( empty($id_lang) )
			$id_lang = $cookie->id_lang;
		
		$link_rewrite = Db::getInstance()->getValue('SELECT link_rewrite FROM '._DB_PREFIX_.'model_entity_lang WHERE id_entity_model=:id_entity_model AND id_lang=:id_lang', array('id_entity_model'=>$id_entity_model, 'id_lang'=>$id_lang));
		$iso_lang = Db::getInstance()->getValue('SELECT code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang);
		
		return 'http://'._DOMAIN_.'/'.$iso_lang.'/'.$link_rewrite;
	}
	
	
	public static function getHomeLink($id_lang=''){

		global $cookie;
		if( empty($id_lang) )
			$id_lang = $cookie->id_lang;
			
		$iso_lang = Db::getInstance()->getValue('SELECT code FROM '._DB_PREFIX_.'lang WHERE id_lang='.(int)$id_lang);
		
		return 'http://'._DOMAIN_.'/'.$iso_lang.'/';
	}
	
	
	public static function getEntityLinkTitle($id_entity, $id_lang=''){
		return Db::getInstance()->getValue('SELECT meta_title FROM '._DB_PREFIX_.'entity_lang WHERE id_entity=:id_entity AND id_lang=:id_lang', array('id_entity'=>$id_entity, 'id_lang'=>$id_lang));
	}
	
	
	public static function getEntityModelLinkTitle($id_entity_model, $id_lang=''){
		return Db::getInstance()->getValue('SELECT meta_title FROM '._DB_PREFIX_.'model_entity_lang WHERE id_entity_model=:id_entity_model AND id_lang=:id_lang', array('id_entity_model'=>$id_entity_model, 'id_lang'=>$id_lang));
	}
	
	public static function getHomeLinkTitle($id_lang=''){
		return Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config_lang WHERE name="meta_title" AND id_lang=:id_lang', array('id_lang'=>$id_lang));
	}
	
}