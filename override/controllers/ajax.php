<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class AjaxController extends Core {
	
	function __construct(){
		
		$this->cookie = new Cookie();
		$this->cookie->id_lang = $this->getLang( $this->cookie );
		$cookie = $this->cookie;
		
	}
	
	public function start(){
		
		if( Tools::getValue('getTestimonials') ){
			
			$curl = curl_init('http://freegeoip.net/json/46.14.143.136');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$return = curl_exec($curl);
			$json = json_decode($return, true);;

			$max_testimonials = (int)Tools::getValue('maxTestimonials');
			if( $max_testimonials == 0 ) $maxTestimonials = 8;
			
			$entities = array();
			$id_attribute_value = Db::getInstance()->getValue('SELECT id_attribute_value FROM '._DB_PREFIX_.'attribute_value_lang WHERE id_lang=1 AND value=:value', array('value' => strtoupper($json['country_name'])) );
			
			if( $id_attribute_value )
				$entities = Entity::getEntitiesListWithAttributeValue(2, $id_attribute_value, $this->cookie->id_lang, true, NULL, 'random');

			if( !$entities || count($entities) < $max_testimonials ){
				$c = $max_testimonials-count($entities);
				$allentities = Entity::getEntitiesList(2, $this->cookie->id_lang, NULL, 'random', 0, $c, NULL, NULL, true);
				$entities = array_merge($entities, $allentities);
			}
			
			echo json_encode($entities);
			
		}
		
	}
	
}