<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class Entity extends EntityCore {
	
	
	/**
	* Method to add an entity. The parameters data come froms Javascript.
	* If you want to add an entity, you have to use the webservice (see documentation)
	* @param Array $data
	*/
	public function addEntity($data){
		
		parent::addEntity($data);
		
		$langs = Lang::getLanguages();
		
		foreach( $langs as $lang ){
			$url = Link::getEntityLink($this->id_entity, $lang['id_lang']);
			$name =  strtoupper($lang['code']).' - '.ucfirst($this->slug).' - '.Entity::getDisplayName($this->id_entity, $lang['id_lang']);
			$postfields = array(
				'c' => '1',
				'url' => $url,
				'nom' => $name,
				'cache' => 'setCode'
			);
			
			$curl = curl_init();
			$lien = 'http://qrcodes.misterharry.fr/ws/ws.php';
			curl_setopt($curl, CURLOPT_URL, $lien);
			curl_setopt($curl, CURLOPT_COOKIESESSION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
			
			$return = curl_exec($curl);
			curl_close($curl);
			
		}
		
		
		
	}

	
	
	
}




?>