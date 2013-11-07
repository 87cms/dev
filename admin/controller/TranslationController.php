<?php


class TranslationController extends AdminController {

	protected $directory = '../template';
	protected $lang_dir = '../lang';


	public function run(){
		
		$this->action = Tools::getSuperglobal('action');
		if( isset($this->action) && !empty($this->action) ){
			
			if( $this->action == "updateTranslation")
				$this->updateTranslation();
			if( $this->action == "displayPreviousTranslations")
				$this->getPreviousTranslations();
			if( $this->action == "restoreTranslation")
				$this->restoreTranslation();
			if( $this->action == "deleteTranslation")
				$this->deleteTranslation();

		}else{

			$langs = array();

			foreach (LangCore::getLanguages() as $key => $lang) {
				if($lang["defaultlang"] == 0){
					$langs[] = $lang["code"];
				}
			}

			//Contains each "sentence" which is already in the array
			$collection = array();

			$iter = new DirectoryIterator( $this->directory );
			$t = array();
			$t['languages'] = $langs;
			$__l = array();
			$inArray = array();
			foreach($iter as $nPage => $file){

				if(!$file->isDot()){

			       
					$file_content = file_get_contents($this->directory.'/'.$file->getFilename());
					$file_content = str_replace("\'","'",$file_content);

					preg_match_all('/\{l s=(\'|")([^}]+)(\'|")\}/', $file_content, $translations );
					//var_dump($translations);
					//duplicate elimination
					$allSentences = array();
					foreach ($translations[2] as $key => $value) {
						if(!in_array($value, $allSentences) && !in_array($value, $inArray)){
							$allSentences[] = $value;
							$inArray[] = $value;
						}else{
							$t["pages"][$nPage]["duplicates"][$key]["value"] = $value;
							$t["pages"][$nPage]["duplicates"][$key]["md5"] = md5($value);
						}
					}

					if(!empty($allSentences)){
						
						foreach( $langs as $idString => $lang ){
							if(count($allSentences)>0){
								$t["pages"][$nPage]["filename"] = $file->getFilename();
								$t["pages"][$nPage]['original'] = $allSentences;
							}

							if( file_exists($this->lang_dir.'/'.$lang.'.php') ){
								require_once($this->lang_dir.'/'.$lang.'.php');
								$fp = fopen($this->lang_dir.'/'.$lang.'.php', 'a+');
							}else{
								$fp = fopen($this->lang_dir.'/'.$lang.'.php', 'w+');
								fwrite($fp, '<?php'."\r\n".'global $__l;'."\r\n".'$__l = array();'."\r\n");	
							}

							foreach($allSentences as $key => $match ){
								$i = array_keys($t["pages"][$nPage]['original'], $match);
								if(count($i)>1){
									print_r($i);
								}
								$md5 = md5($match);
								$t["pages"][$nPage]['md5'][$i[0]] = $md5;
								foreach($i as $k => $value){
									if(isset($__l[$md5])){
										$t["pages"][$nPage][$lang][$i[$k]] = $__l[$md5];
									}
								}								
							}

							fclose($fp);
						}
					}
			    }
			}

			$this->smarty->assign(array(
				'translations' => $t,
				'pageName' => "Translation"
			));

			$this->smarty->display('translation.html');
		}	
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
					/*if(preg_match('%Secteur%', $post)){
						$post = str_replace('\'', '&apos;', $post);
					}*/
					

					//$post = htmlspecialchars($post);
					preg_match('/('.$langPattern.')-'.$md5Pattern.'/', $key, $key);
					$l = $key[1];
					$key = $key[2];

					$output[$l] .= '$__l[\''.$key.'\'] = \''.addslashes($post).'\';'."\r\n";
				}
			}
		}

		foreach ($output as $lang => $string) {
			if($string != ""){

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


	public function preprocess(){ }



	

}