<?php

class Mhespacepro extends Module implements ModuleInterface {
	
	public $name = "Mhespacepro";
	public $description = "Espace pro";
	
	public $hook_name = "DISPLAY_ESPACE_PRO";
	public $method_name = "displayEspacePro";
	
	
	public function start(){ }	
	
	
	public function displayAdmin(){
		$this->smarty->display('../modules/mhsuggestions/admin.html');
	}
	
	
	public function displayEspacePro(){
		
		$this->initController();
		
		if( Tools::getValue('ids_entity') ){
			
			$ids_entities = explode(',', Tools::getValue('ids_entity') );
			if( count($ids_entities) > 0 ){
				$this->generatePDF($ids_entities);
			}
				
		}		
		
		$domaines = Entity::getEntitiesList('1', $this->cookie->id_lang, 0, 'meta_title');
		
		foreach( $domaines as &$domaine ){
			$d = new Entity( $domaine['id_entity'] );
			$domaine['fields'] = $d->getData($this->cookie->id_lang);
			$domaine['children'] = $d->getChildren($this->cookie->id_lang, 0, 0, 'ORDER BY meta_title ASC', 2);
		}
		
		$this->smarty->assign('domaines', $domaines);
		
		$this->smarty->display('modules/mhespacepro/mhespacepro.html');
	
	}
	
	
	public function generatePDF($ids_entities){
		$entities = array();
		foreach( $ids_entities as $id_entity ){
			$id_entity = (int)$id_entity;
			
			if( $id_entity > 0 ){
				
				$tmpE = new Entity($id_entity);
				$tmpE->fields = $tmpE->getData($this->cookie->id_lang);
				
				//QR CODE
				$postfields = array(
					'c' => '1',
					'url' => $url = Link::getEntityLink($id_entity, $this->cookie->id_lang),
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
				
				
				$tmpE->fields['base64'] = $base64;
				
				// Parent
				$parent = new Entity( $tmpE->id_default_parent );
				$parent->fields = $parent->getData( $this->cookie->id_lang );
				$tmpE->fields['nom_domaine'] = $parent->fields['nom_domaine'];
				
				$entities[] = $tmpE;
			}
			
		}
		
		if( $entities ){
			$this->smarty->assign('exported_entities', $entities);
			$content = $this->smarty->fetch('print_espacepro_fr.html');
			
			$m = uniqid();
			$fp = fopen(_ABSOLUTE_PATH_.'/tmp/'.$m.'.html', 'w+');
			fwrite($fp, $content);
			fclose($fp);
			
			$pdf = 'Export_vins_'.$m.'.pdf';
			$commande = str_replace(' ','\\', _ABSOLUTE_PATH_).'/tmp/bin/wkhtmltopdf '.str_replace(' ','\\', _ABSOLUTE_PATH_).'/tmp/'.$m.'.html '.str_replace(' ','\\', _ABSOLUTE_PATH_).'/tmp/'.$pdf;

			shell_exec($commande);
			
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
			readfile(  str_replace(' ','\\', _ABSOLUTE_PATH_).'/tmp/'.$pdf  );
			die();
		}
		
	}
	
	
}




