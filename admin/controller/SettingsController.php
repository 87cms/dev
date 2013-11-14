<?php


class SettingsController extends AdminController {
	
	
	public function run(){
		
		if( Tools::getValue("submitForm") )
			$this->updateSettings();
		
		if( Tools::getValue("submitFormResize") )
			$this->resizeImages( Tools::getValue('id_field_model') );
			
		if( Tools::getValue("submitFormResizeAllImage") )
			$this->resizeImages();
		
		if( Tools::getValue('submitEmptyCache') )
			$this->emptySmartyCache();
			
		$settings = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'config');
		$homepage_template = array();
		foreach( $settings as $setting )
			$this->smarty->assign( $setting['name'], $setting['value'] );		
		
		/** 
		Homepage SEO
		**/
		$homepageSEO = array();
		$data = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'config_lang WHERE name="meta_description" OR name="meta_title" OR name="meta_keywords"');
		foreach( $data as $d )
			$homepageSEO[ $d['id_lang'] ][ $d['name'] ] = $d['value'];	
		$this->smarty->assign( 'homepageSEO', $homepageSEO );
		/** END **/
		
		$this->smarty->assign('models', EntityModel::getModels($this->cookie->id_lang_admin) );
		
		$this->smarty->display('settings.html');
		
	}
	
	
	
	public function updateSettings(){
		
		if( Tools::getValue('homepage_settings') ){
			
			$seo = array();
			$meta_description = $meta_title = $meta_keywords = array();
			
			foreach( $_POST as $key => $value ){
				if( preg_match('/meta_title/',$key) OR preg_match('/meta_description/',$key) OR preg_match('/meta_keywords/',$key) ){
					$d = explode('#', $key);
					if( $d[0] == "meta_description" )
						$meta_description[ $d[1] ] = $value;
					if( $d[0] == "meta_title" )
						$meta_title[ $d[1] ] = $value;
					if( $d[0] == "meta_keywords" )
						$meta_keywords[ $d[1] ] = $value;
					unset($_POST[$key]);
				}				
			}
			
			Tools::updateHomepageSEO($meta_description, $meta_title, $meta_keywords);
			
		}
		
		foreach( $_POST as $name => $value ){
			
			$inDB = Db::getInstance()->getValue('SELECT name FROM '._DB_PREFIX_.'config WHERE name="'.Tools::cleanSQL($name).'"');
			
			if( $inDB )
				Db::getInstance()->UpdateDB(_DB_PREFIX_.'config', array('value'=>$value), array('name'=>$name));
			
			elseif( preg_match('/homepage_template_([a-z]{2,3})/', $name) )
				Db::getInstance()->Insert(_DB_PREFIX_.'config', array('name'=>Tools::cleanSQL($name),'value'=>$value));
			
			else
				Db::getInstance()->Insert(_DB_PREFIX_.'config', array('value'=>$value, 'name'=>$name));
			
		}
		
		$this->smarty->assign( 'added', 1 );
		
	}
	
	
	public function resizeImages($id_field_model){
		if( $id_field_model > 0 ){
			$field_model = new EntityModelField($id_field_model);
			if( $field_model->params && !empty($field_model->params) ){
				$images = Db::getInstance()->Select('SELECT raw_value FROM '._DB_PREFIX_.'entity_field WHERE id_field_model=:id_field_model', array('id_field_model' => $id_field_model) );
				foreach( $images as $raw )
					Tools::resizeImagesFromJSON($raw['raw_value'], $id_field_model, $field_model->params);					
			}
		}		
	}
	
	public function resizeMediaboxThumbs(){
		$images = Db::getInstance()->Select('SELECT * FROM '._DB_PREFIX_.'media WHERE deleted=0 AND mimetype LIKE "%image%" ');
			
		foreach( $images as $image ){
			$options = array(
				'width' => '100',
				'height' => '100'
			);
			
			$file_path = _ABSOLUTE_PATH_.'/medias'.$image['path'].$image['filename'];
			$type = "admin";
			
			$path = _ABSOLUTE_PATH_.'/medias'.$image['path'];
			
			if( !is_dir($path.'admin') )
				mkdir($path.'admin'); 

			$new_file_path = $path.'admin/'.$image['filename'];
			
			if( file_exists($new_file_path) )
				unlink($new_file_path);
			
			UploadHandler::create_scaled_image_ext($file_path, $new_file_path, $options);
			
		}	
	}
	
	
	public function emptySmartyCache(){
		$dir = new DirectoryIterator(_ABSOLUTE_PATH_.'/tools/smarty/cache');
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot() && $fileinfo->getFilename() !== ".gitignore" ) {
				unlink($fileinfo->getPathname());
			}
		}
	}
	
	public function preprocess(){ }
}