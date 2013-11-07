<?php

$directory = '../../template';
$lang_dir = '../../lang';
$langs = array('fr');


//require_once('config.inc.php');
require_once('class/MicrosoftTranslator.class.php');
//ACCOUNT_KEY you have to get from https://datamarket.azure.com/dataset/1899a118-d202-492c-aa16-ba21c33c06cb
$translator = new MicrosoftTranslator('UXyVuiuKq6yEdjkUdeTGBcomWUGaB8rEaFPUclc3mks');


$iter = new DirectoryIterator( $directory );
foreach($iter as $file ) {
    
	if ( !$file->isDot() ) {
       
		$file_content = file_get_contents($directory.'/'.$file->getFilename());
		
		//preg_match_all('/\{l s=\'(.[^><])+\'\}/', $file_content, $translations );
		preg_match_all('/\{l s=(\'|")([^}]+)(\'|")\}/', $file_content, $translations );
		
	
		
		foreach( $langs as $lang ){
			$__l = array();
			if( file_exists($lang_dir.'/'.$lang.'.php') ){
				require_once( $lang_dir.'/'.$lang.'.php' );
				$fp = fopen($lang_dir.'/'.$lang.'.php', 'a+');
			}
			else{
				$fp = fopen($lang_dir.'/'.$lang.'.php', 'w+');
				fwrite($fp, '<?php'."\r\n".'$__l = array();'."\r\n");				
			}
				
			$array = array();
				
			foreach( $translations[2] as $match ){
				
				
					$hash = md5($match);
					
					if( !in_array($hash, $array) ){
						
						
						$text_to_translate = $match; 
						$from = 'en';
						$to = 'fr';
						$translator->translate($from, $to, $text_to_translate);
						$r = $translator->response->jsonResponse;
						$response = json_decode($r, true);
						
						$t = str_replace('</string>','',$response['translation']);
						$t = preg_replace('<string xmlns=".*">','',$t);
						$t = str_replace('<>','',$t);
						
						$line = '$__l[\''.$hash.'\'] = \''.addslashes($t).'\';'.' //'.$text_to_translate."\r\n";
						fwrite($fp, $line);
						array_push($array, $hash);
						
						$__l[ $hash ] = $match;
						sleep(1);	
					}
				
				
			}
			fclose($fp);
		}
		
		
    }
}

