<?php
/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class CookieCore {
	
	private $_name = '87cms';
	private $_expire;
	private $_checksum;
	
	public $decrypted_cookie;
	public $encrypted_cookie;
	
	private $_key = "}386<9O^BjgB4O`rp?ZyG&8?";
	private $_iv;
	
	function __construct($path = '', $expire = NULL)	{
		
		$this->_expire = isset($expire) ? intval($expire) : (time() + 1728000);
		
		$this->_checksum = "KTchLo47TvdhEskFG9Rhb7KXFLEVhNnV";
		
		$this->isCookieSet();
		
		$this->getCookieData();

	}
	
	/**
	* Check if a cookie is set or not
	* @return Bool True of false
	*/
	public function isCookieSet(){
		if( !isset($_COOKIE[$this->_name]) ) setcookie($this->_name, '', $this->_expire, '/', _COOKIE_DOMAIN_, 0, true);
		$this->encrypted_cookie = $_COOKIE[$this->_name];
	}
	
	/**
	* Check the checksum write in the cookie
	* @return Bool True or False
	*/
	private function testChecksum($checksumToTest){
		if (!isset($checksumToTest) OR $checksumToTest !== $this->_checksum){
			$this->logout();
			return false;
		}else return true;
	}
	
	/**
	* Function to get the date and decrypt it
	* @return Array All datas formated in one single array
	*/
	private function getCookieData(){
		
		if( empty($this->decrypted_cookie) ){
			$this->decrypted_cookie = array(); 
			$this->decrypted_cookie = $this->decrypt( $_COOKIE[$this->_name] );
			$this->decrypted_cookie = unserialize($this->decrypted_cookie);
			
		}
				
	}
	
	/** Fonction pour récupérer un champ dans le cookie
	* @param String Nom du champ
	* @return String La valeur du champ
	*/
	public function __get($name){
		return $this->decrypted_cookie[$name];
	}
	
	public function __set($name, $value){
		$this->decrypted_cookie[ $name ] = $value;
		$this->_setCookie();
	}
	
	
	
	/**
	* Magic function to set the cookie
	* @return Bool If the cookie is write or not
	*/
	private function _setCookie(){
		$this->encrypted_cookie = $this->prepareContent();
		@setcookie($this->_name, $this->encrypted_cookie, $this->_expire, '/', _COOKIE_DOMAIN_, 0, true);
	}
	
	
	/**
	* We prepare the content in a single string line, we add the checksum line and we blowfish it
	* @return String The crypted content
	*/
	private function prepareContent(){
		//$this->_content["checksum"] = $this->_checksum;
		$data = serialize($this->decrypted_cookie);
		return $this->encrypt($data);
	}
	
	
	
	
	/**
	* Log out the user = destroy all data contained in the cookie
	*/
	public function destroy(){
		$this->decrypted_cookie = '';
		$this->_setCookie();
	}
	
	
	/**
	* Encrypt plain text in Rijndael
	* @param String $plaintext The text to encrypt
	* @return String The text encrypted
	*/
    public function encrypt($plaintext){
        if (($length = strlen($plaintext)) >= 1048576)
			return false;
		$this->_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_key, $plaintext, MCRYPT_MODE_ECB, $this->_iv)).sprintf('%06d', $length);
    }
	
	/**
	* Decrypt plain text in Rijndael
	* @param String $crypttext The text encrypted to decrypt
	* @return String The text decrypted
	*/
    public function decrypt($ciphertext){
        $plainTextLength = intval(substr($ciphertext, -6));
		$ciphertext = substr($ciphertext, 0, -6);
		$this->_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
		return substr(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_key, base64_decode($ciphertext), MCRYPT_MODE_ECB, $this->_iv), 0, $plainTextLength);
    }
	

}



?>
