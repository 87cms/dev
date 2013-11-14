<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Specific Override
 */


class VinController extends EntityController {
	
	public function process(){
		
		$fields = $this->entity->getData($this->cookie->id_lang);
		$this->entity->fields = $fields;
		
		$domaine = new Entity($this->entity->id_default_parent);
		$domaine->fields = $domaine->getData($this->cookie->id_lang);
		
		foreach( $this->entity->fields as &$field ){
			
			if( $field['type'] == "linkedEntities" ){
					
				$id_entities = explode(',' , rtrim($field['raw_value'], ',') );
				$entities = array();
				foreach( $id_entities as $id_entity ){
					if( $id_entity ){
						$entity = new Entity( $id_entity );
						$entity->fields = $entity->getData($this->cookie->id_lang);
						$entity->parent = new Entity( $this->entity->id_default_parent );
						$entity->parent->fields = $entity->parent->getData($this->cookie->id_lang);
						array_push($entities, $entity);
					}
				}
				
				$field = $entities;
									
			}
		}
		
		
		$active_entity = array();
		array_push($active_entity, array(
			'id_entity' => $this->entity->id_entity,
			'id_entity_model' => 1
		));
		
		$this->smarty->assign(array(
			$this->entity->slug => $this->entity,
			'active_entity' => $active_entity,
			'domaine' => $domaine
		));
				
		$this->getSEO();
		
		if( Tools::getValue('pdf') == 1 )
			$this->generatePDF($this->entity->id_entity, $this->entity->id_default_parent);
		
		$this->display();
			
	}
	
	
	public function generatePDF($id_entity, $id_default_parent){
		
		//QR CODE
		$postfields = array(
			'c' => '1',
			'url' => $url = Link::getEntityLink($id_default_parent, $this->cookie->id_lang),
			'cache' => 'getCode'
		);
		$curl = curl_init();
		$lien = 'http://qrcodes.misterharry.fr/ws/ws.php';
		curl_setopt($curl, CURLOPT_URL, $lien);
		curl_setopt($curl, CURLOPT_COOKIESESSION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
		$base64 = curl_exec($curl);
		curl_close($curl);
		$this->smarty->assign('base64', $base64);
		
		// Parent
		$parent = new Entity( $this->entity->id_default_parent );
		$parent->fields = $parent->getData( $this->cookie->id_lang );
		$this->smarty->assign('nom_domaine', $parent->fields['nom_domaine']);
		
		$content = $this->smarty->fetch('print_vin_'.Lang::getLangCode($this->cookie->id_lang).'.html');
		
		$m = uniqid();
		$fp = fopen(_ABSOLUTE_PATH_.'/tmp/'.$m.'.html', 'w+');
		fwrite($fp, $content);
		fclose($fp);
		$pdf = Tools::str2url($this->entity->fields['nom_vin']).'.pdf';
		$commande = _ABSOLUTE_PATH_.'/tmp/bin/wkhtmltopdf '._ABSOLUTE_PATH_.'/tmp/'.$m.'.html '._ABSOLUTE_PATH_.'/tmp/'.$pdf;
		
		exec($commande);
		unlink(_ABSOLUTE_PATH_.'/tmp/'.$m.'.html');

		//nettoyage
		$dirname = _ABSOLUTE_PATH_.'/tmp/';
		$dir = opendir($dirname); 
		while($file = readdir($dir)) {
			if($file != '.' && $file != '..' && !is_dir($dirname.$file))
			{
				if( filemtime(_ABSOLUTE_PATH_.'/tmp/'.$file) < (time()-60*60) )
					unlink(_ABSOLUTE_PATH_.'/tmp/'.$file);
			}
		}
		closedir($dir);
		
		header("Content-type: application/pdf");
		header("Content-Disposition: attachment; filename=".$pdf);
		readfile('tmp/'.$pdf);
		
	}
	
}