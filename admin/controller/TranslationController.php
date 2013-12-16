<?php


class TranslationController extends AdminController {

	protected $directory = '../template';
	protected $lang_dir = '../lang';


	public function run(){
		
		$langs = array();

		foreach (LangCore::getLanguages() as $key => $lang) {
			if($lang["defaultlang"] == 0){
				$langs[] = $lang["code"];
				$__l = array();
				if( file_exists($this->lang_dir.'/'.$lang["code"].'.php') ){
					require_once($this->lang_dir.'/'.$lang["code"].'.php');
				}else{
					$fp = fopen($this->lang_dir.'/'.$lang["code"].'.php', 'w+');
					fwrite($fp, '<?php'."\r\n".'global $__l;'."\r\n".'$__l = array();'."\r\n");	
				}
				$languages[ $lang["code"] ] = $__l;
				
			}
		}
		
		$iter = new DirectoryIterator( $this->directory );
		$translation = array();
		
		$doublon = array();
		$duplicate = array();
		$translated = array();
		$files = array();
		foreach($iter as $file){
			if(!$file->isDot()){
				$ext = pathinfo($file->getPathname(), PATHINFO_EXTENSION);
				if( $ext == "html" OR $ext == "tpl" ){
					$files[] = $file->getFilename();
				}
			}
		}
		sort($files);
		
		foreach($files as $filename){
			
			$translation[ $filename ] = array();
			
			// matching && Extract string from {l s=""}
			$file_content = file_get_contents($this->directory.'/'.$filename);
			$file_content = str_replace("\'","'",$file_content);
			preg_match_all('/\{l s=(\'|")([^}]+)(\'|")\}/', $file_content, $translations );
			
			
			$sentences = $translations[2];
			// $translation[ PageName ][ $i ]['original'] = mention
			// $translation[ PageName ][ $i ]['md5'] = md5;
			// $translation[ PageName ][ $i ]['translation'][ $lang_code ] = translation
			$i = 0;
			foreach( $sentences as $string ){
				
				if( !in_array(md5($string), $doublon) ){
					
					array_push($doublon, md5($string));
					
					$translation[ $filename ][$i]['original'] = $string;
					$translation[ $filename ][$i]['md5'] = md5($string);
				
					foreach ($langs as $lang_code ){
		
						$translation[ $filename ][$i]['translation'][ $lang_code ] = '';
						if( array_key_exists(md5($string), $languages[ $lang_code ]) )
							$translation[ $filename ][$i]['translation'][ $lang_code ] = $languages[ $lang_code ][md5($string)];
							
					}
					$i++;
				}
				else {
					$duplicate[ $filename ][ ] = $string;	
				}
				
			}
			
		}
		//var_dump($duplicate);
		$this->smarty->assign(array(
			'translations' => $translation,
			'pageName' => "Translation",
			'langs' => $langs,
			'duplicate' => $duplicate
		));

		$this->smarty->display('translation.html');
	
	}



	private function updateTranslation(){

		$output = array();
		$langPattern = '';
		foreach (LangCore::getLanguages() as $key => $lang) {
			if($lang["defaultlang"] == 0){
				$langPattern .= $lang["code"].'|';
				$output[$lang["code"]] = "";
			}
		}
		$langPattern = substr($langPattern, 0, -1);
		$langPattern .= '';

		$md5Pattern = '([a-z0-9]{32})';

		$pattern = '/['.$langPattern.']-'.$md5Pattern.'/';

		foreach ($_POST as $key => $post) {
			
			if(preg_match($pattern, $key)){
				if($post != ""){
					preg_match('/('.$langPattern.')-'.$md5Pattern.'/', $key, $key);
					$l = $key[1];
					$key = $key[2];

					$output[$l] .= '$__l[\''.$key.'\'] = \''.addslashes($post).'\';'."\r\n";
				}
			}
		}
		
		foreach ($output as $lang => $string) {
			if($string != "" && $lang){

				if( file_exists($this->lang_dir.'/'.$lang.'.php') ){
					require_once($this->lang_dir.'/'.$lang.'.php');

					//création d'une copie
					if (!copy($this->lang_dir.'/'.$lang.'.php', $this->lang_dir.'/back/'.date("Y_m_d-H_i_s").'-'.$lang.'.php')) {
					    echo "<p style=\"color:red;\">La sauvegarde a échouée.</p>";
					}

					$fp = fopen($this->lang_dir.'/'.$lang.'.php', 'w+');
				}else{
					$fp = fopen($this->lang_dir.'/'.$lang.'.php', 'w+');	
				}
				fwrite($fp, '<?php'."\r\n".'global $__l;'."\r\n".'$__l = array();'."\r\n");
				fwrite($fp, $output[$lang]);
				fclose($fp);
			}
		}

		Tools::redirect('/admin/index.php?p=translation');

	}



	private function getPreviousTranslations(){

		$langPattern = '';
		foreach (LangCore::getLanguages() as $key => $lang) {
			if($lang["defaultlang"] == 0){
				$langPattern .= $lang["code"].'|';
				$output[$lang["code"]] = "";
			}
		}
		$langPattern = substr($langPattern, 0, -1);
		$langPattern .= '';


		/* ouverture du repertoire de nom "photos" */
		$pointeur=opendir($this->lang_dir.'/back');

		$f = array();

		/* on regarde le contenu pointé par $pointeur, nom par nom */
		while ($entree = readdir($pointeur)) {
			if($entree != "." && $entree != ".." &&
				preg_match("/[0-9]{4}_[0-9]{2}_[0-9]{2}-[0-9]{2}_[0-9]{2}_[0-9]{2}-[".$langPattern."]+\.php/", $entree)){
	    		$f[] = $entree;
			}
		}

		/* fermeture du repertoire repere par $pointeur */
		closedir($pointeur);
		$files = array();
		foreach($f as $key => $filemname){
			preg_match("/([0-9]{4})_([0-9]{2})_([0-9]{2})-([0-9]{2})_([0-9]{2})_([0-9]{2})-(".$langPattern.")+\.php/", $filemname, $filename);
			$files[$key]["filename"] = $filename[0];
			$files[$key]["year"] = $filename[1];
			$files[$key]["month"] = $filename[2];
			$files[$key]["day"] = $filename[3];
			$files[$key]["hour"] = $filename[4];
			$files[$key]["min"] = $filename[5];
			$files[$key]["sec"] = $filename[6];
			$files[$key]["lang"] = $filename[7];
		}

		arsort($files);

		$this->smarty->assign(array(
			'pageName' => "Previous translations",
			'previousTranslations' => $files
		));

		$this->smarty->display('translation_previous_list.html');

	} 

	private function restoreTranslation(){

		$file = Tools::getSuperglobal('file');
		if( isset($file) && !empty($file) ){
			if(file_exists($this->lang_dir.'/back/'.$file) ){
				$langPattern = '';
				foreach (LangCore::getLanguages() as $key => $lang) {
					if($lang["defaultlang"] == 0){
						$langPattern .= $lang["code"].'|';
						$output[$lang["code"]] = "";
					}
				}
				$langPattern = substr($langPattern, 0, -1);
				$langPattern .= '';
				preg_match("/[0-9]{4}_[0-9]{2}_[0-9]{2}-[0-9]{2}_[0-9]{2}_[0-9]{2}-(".$langPattern.")+\.php/", $file, $lang);
				$lang = $lang[1];

				//saving of the actual translation
				if(file_exists($this->lang_dir.'/'.$lang.'.php')){

					if(!copy($this->lang_dir.'/'.$lang.'.php', $this->lang_dir.'/back/'.date("Y_m_d-H_i_s").'-'.$lang.'.php')){
					    echo "<p style=\"color:red;\">La sauvegarde a échouée.</p>";
					}else{
						if(!copy($this->lang_dir.'/back/'.$file, $this->lang_dir.'/'.$lang.'.php')) {
						    echo "<p style=\"color:red;\">La restauration a échouée.</p>";
						}
						//!\\ Success
						Tools::redirect('/admin/index.php?p=translation');
					}

				}

			}else{
				echo "Translation not found";
			}
		}else{
			Tools::redirect('/admin/index.php?p=translation');
		}

	} 

	private function deleteTranslation(){

		$file = Tools::getSuperglobal('file');
		$langPattern = '';
		foreach (LangCore::getLanguages() as $key => $lang) {
			if($lang["defaultlang"] == 0){
				$langPattern .= $lang["code"].'|';
				$output[$lang["code"]] = "";
			}
		}
		$langPattern = substr($langPattern, 0, -1);
		$langPattern .= '';
		preg_match("/[0-9]{4}_[0-9]{2}_[0-9]{2}-[0-9]{2}_[0-9]{2}_[0-9]{2}-(".$langPattern.")+\.php/", $file, $lang);
		$lang = $lang[1];
		if( isset($file) && !empty($file) &&
		preg_match("/[0-9]{4}_[0-9]{2}_[0-9]{2}-[0-9]{2}_[0-9]{2}_[0-9]{2}-[".$langPattern."]+\.php/", $file)){
			if(file_exists($this->lang_dir.'/back/'.$file) ){
				unlink($this->lang_dir.'/back/'.$file);
				Tools::redirect('/admin/index.php?p=translation&action=displayPreviousTranslations');
			}else{
				echo "Translation not found";
			}
		}else{
			Tools::redirect('/admin/index.php?p=translation&action=displayPreviousTranslations');
		}

	} 


	public function preprocess(){ 
		$this->action = Tools::getSuperglobal('action');
		
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "displayPreviousTranslations")
				$this->getPreviousTranslations();
			if( $this->action == "restoreTranslation")
				$this->restoreTranslation();
			if( $this->action == "deleteTranslation")
				$this->deleteTranslation();
			if( $this->action == "updateTranslation")
				$this->updateTranslation();
		
		}
	}



	

}