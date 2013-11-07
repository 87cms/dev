<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */


class ToolsCore {
	
    /**
    * Redirect user to another page
    *
    * @param string $url Desired URL
    * @param string $domain Base URI 
    */
    public static function redirect($url, $domain = _DOMAIN_)
    {
        if (isset($_SERVER['HTTP_REFERER']) AND ($url == $_SERVER['HTTP_REFERER']))
            header('Location: '.$_SERVER['HTTP_REFERER']);
        else
            header('Location: http://'.$domain.$url);
        exit;
    }

    /**
    * Get a value from $_POST / $_GET
    * if unavailable, take a default value
    *
    * @param string $key Value key
    * @param mixed $defaultValue (optional)
    * @return mixed Value
    */
    public static function getSuperglobal($key, $defaultValue = false)
    {
        if(!isset($key) OR empty($key) OR !is_string($key))
            return false;
        
		$var = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

        if( is_string($var) === true )
            $var = stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($var))));
       
		return $var;
    }
	
	public static function getValue($key, $defaultValue = false){
		return self::getSuperglobal($key, $defaultValue);
	}

    /**
     * Return a friendly url made from the provided string
     * If the mbstring library is available, the output is the same as the js function of the same name
     *
     * @param string $str
     * @return string
     */
    public static function str2url($str)
    {
        if (function_exists('mb_strtolower'))
            $str = mb_strtolower($str, 'utf-8');

        $str = trim($str);
        $str = self::replaceAccentedChars($str);
		$str = preg_replace('/[^a-zA-Z0-9\s\'\:\/\[\]-]/','', $str);
        $str = preg_replace('/[\s\'\:\/\[\]-]+/',' ', $str);
        $str = preg_replace('/[ ]/','-', $str);
        $str = preg_replace('/[\/]/','-', $str);
		$str = strtolower($str);

        return $str;
    }

    /**
     * Replace all accented chars by their equivalent non accented chars.
     *
     * @param string $str
     * @return string
     */
    public static function replaceAccentedChars($str)
    {
        $str = preg_replace('/[\x{0105}\x{0104}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u','a', $str);
        $str = preg_replace('/[\x{00E7}\x{010D}\x{0107}\x{0106}]/u','c', $str);
        $str = preg_replace('/[\x{010F}]/u','d', $str);
        $str = preg_replace('/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{011B}\x{0119}\x{0118}]/u','e', $str);
        $str = preg_replace('/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}]/u','i', $str);
        $str = preg_replace('/[\x{0142}\x{0141}\x{013E}\x{013A}]/u','l', $str);
        $str = preg_replace('/[\x{00F1}\x{0148}]/u','n', $str);
        $str = preg_replace('/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{00D3}]/u','o', $str);
        $str = preg_replace('/[\x{0159}\x{0155}]/u','r', $str);
        $str = preg_replace('/[\x{015B}\x{015A}\x{0161}]/u','s', $str);
        $str = preg_replace('/[\x{00DF}]/u','ss', $str);
        $str = preg_replace('/[\x{0165}]/u','t', $str);
        $str = preg_replace('/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}]/u','u', $str);
        $str = preg_replace('/[\x{00FD}\x{00FF}]/u','y', $str);
        $str = preg_replace('/[\x{017C}\x{017A}\x{017B}\x{0179}\x{017E}]/u','z', $str);
        $str = preg_replace('/[\x{00E6}]/u','ae', $str);
        $str = preg_replace('/[\x{0153}]/u','oe', $str);
        return $str;
    }

    /**
    * Sort a multi dimensional array
    * @param Array $array The array to sort
    * @param String $key The key
    * @return Array $sorted_arr The array sorted
    */
    public static function array_sort($array, $key){
        for ($i = 0; $i < sizeof($array); $i++) {
            $sort_values[$i] = $array[$i][$key];
        }
        asort  ($sort_values);
        reset ($sort_values);
        while (list ($arr_key, $arr_val) = each ($sort_values)) {
            $sorted_arr[] = $array[$arr_key];
        }
        unset($array);
        return $sorted_arr;
    }

    public static function getToken(){
        return $_SESSION['token'];
    }

    public static function verifyToken($token){
        if( $token == $_SESSION['token'] ) return true;
        else return false;
    }


    public static function setToken(){
        $token = md5(uniqid());
        $_SESSION['token'] = $token;
    }


    public static function pr($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }


   
   public static function getExt($fichier)
    {
        $ext="";
        $tab = explode(".", $fichier);
        if(count($tab)>0)
        {
            $ext = $tab[count($tab)-1];
        }
        return $ext;
    }

    /*
    * Nettoyer une chaine avant l'insertion en base
    * @param String $string Chaine à nettoyer
    * @return String
    */
    public static function cleanSQL($string)
    {
        // On regarde si le type de string est un nombre entier (int)
        if(ctype_digit($string))
        {
            $string = intval($string);
        }
        // Pour tous les autres types
        else
        {
            $string = addslashes($string);
            $string = strip_tags(nl2br($string));
            //$string = addcslashes($string, '%_');
        }

        return $string;
    }

    /*
    * Méthode pour analyser l'en-tête http auth
    * @param String $txt Requete HTTP
    * @return Array Tableau de résultat
    */
    public static function http_digest_parse($txt)
    {
        // protection contre les données manquantes
        $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }

    public static function debugException($e){
		$debug = $e->getCode();
		$debug .= ' : ';
		$debug .= $e->getMessage();
		$debug .= ' in ';		
		$debug .= $e->getFile();
		$debug .= '<br/><br/>';
		$debug .= $e->getTraceAsString();
		//die($debug);
		echo $debug;
    }
	
	
	public static function updateHomepageSEO($meta_description, $meta_title, $meta_keywords){
		Db::getInstance()->Delete(_DB_PREFIX_.'config_lang', array('name'=>'meta_title'));
		Db::getInstance()->Delete(_DB_PREFIX_.'config_lang', array('name'=>'meta_description'));
		Db::getInstance()->Delete(_DB_PREFIX_.'config_lang', array('name'=>'meta_keywords'));
		
		foreach( $meta_description as $id_lang => $value )
			Db::getInstance()->Insert(_DB_PREFIX_.'config_lang', array('id_lang'=>$id_lang, 'name'=>'meta_description', 'value'=>$value ));
		
		foreach( $meta_title as $id_lang => $value )
			Db::getInstance()->Insert(_DB_PREFIX_.'config_lang', array('id_lang'=>$id_lang, 'name'=>'meta_title', 'value'=>$value ));
		
		foreach( $meta_keywords as $id_lang => $value )
			Db::getInstance()->Insert(_DB_PREFIX_.'config_lang', array('id_lang'=>$id_lang, 'name'=>'meta_keywords', 'value'=>$value ));
		
	}
	
	
	public static function resizeImagesFromJSON($json, $id_field_model, $field_params){
		ini_set('max_execution_time', 3600);
		$raw = json_decode($json, true);
		if( $raw ) {
			foreach( $raw as $image ){
				
				
				$data = json_decode( $field_params, true );
				$sizes = array();
				foreach( $data as $s ){
					$e = explode("_", $s['name']);
					$sizes[ $e[0] ][ $e[1] ] = $s['value'];
				}
				
				$sizes['admin']['height'] = 100;
				$sizes['admin']['width'] = 100;
				
				$types = array('admin', 'thumb', 'medium', 'large');
				foreach( $types as $type ){
					$options = array(
						'width' => $sizes[ $type ]['width'],
						'height' => $sizes[ $type ]['height']
					);
					
					$file_path = _ABSOLUTE_PATH_.$image['path'];
					
					$path = explode('/', $file_path);
					unset( $path[count($path)-1] );
					$path = implode('/', $path);
					
					if( !is_dir($path.'/'.$type) )
						mkdir($path.'/'.$type); 
					
					$new_file_path = $path.'/'.$type.'/'.$id_field_model.'-'.$image['name'];
					
					if( file_exists($new_file_path) )
						unlink($new_file_path);
						
					UploadHandler::create_scaled_image_ext($file_path, $new_file_path, $options);
				
				}	
			
			}
		}
	}
	
	
}




/**---- SECOND PART ----**/
/*---- PASSWORD HASH ----*/

/*
 * Password hashing with PBKDF2.
 * Author: havoc AT defuse.ca
 * www: https://defuse.ca/php-pbkdf2.htm
 */

// These constants may be changed without breaking existing hashes.
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 567);
define("PBKDF2_SALT_BYTES", 24);
define("PBKDF2_HASH_BYTES", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

function create_hash($password)
{
    // format: algorithm:iterations:salt:hash
    $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));
    return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
        base64_encode(pbkdf2(
            PBKDF2_HASH_ALGORITHM,
            $password,
            base64_decode($salt),
            PBKDF2_ITERATIONS,
            PBKDF2_HASH_BYTES,
            true
        ));
}

function validate_password($password, $good_hash)
{
    $params = explode(":", $good_hash);
    if(count($params) < HASH_SECTIONS)
       return false;
    $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
    return slow_equals(
        $pbkdf2,
        pbkdf2(
            $params[HASH_ALGORITHM_INDEX],
            $password,
            base64_decode($params[HASH_SALT_INDEX]),
            (int)$params[HASH_ITERATION_INDEX],
            strlen($pbkdf2),
            true
        )
    );
}

// Compares two strings $a and $b in length-constant time.
function slow_equals($a, $b)
{
    $diff = strlen($a) ^ strlen($b);
    for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
    {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
}

/*
 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
 * $algorithm - The hash algorithm to use. Recommended: SHA256
 * $password - The password.
 * $salt - A salt that is unique to the password.
 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
 * $key_length - The length of the derived key in bytes.
 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
 * Returns: A $key_length-byte key derived from the password and salt.
 *
 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
 *
 * This implementation of PBKDF2 was originally created by https://defuse.ca
 * With improvements by http://www.variations-of-shadow.com
 */
function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
{
    $algorithm = strtolower($algorithm);
    if(!in_array($algorithm, hash_algos(), true))
        die('PBKDF2 ERROR: Invalid hash algorithm.');
    if($count <= 0 || $key_length <= 0)
        die('PBKDF2 ERROR: Invalid parameters.');

    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);

    $output = "";
    for($i = 1; $i <= $block_count; $i++) {
        // $i encoded as 4 bytes, big endian.
        $last = $salt . pack("N", $i);
        // first iteration
        $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
        // perform the other $count - 1 iterations
        for ($j = 1; $j < $count; $j++) {
            $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
        }
        $output .= $xorsum;
    }

    if($raw_output)
        return substr($output, 0, $key_length);
    else
        return bin2hex(substr($output, 0, $key_length));
}


