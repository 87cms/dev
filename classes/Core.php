<?php
/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

abstract class Core {
	
	protected $table = NULL;
	protected $identifier = NULL;
	
	protected static $post;
	protected static $get;
	protected static $files;
	
	public $langFields = array('name', 'description', 'link_rewrite', 'meta_title', 'meta_keywords', 'meta_description', 'value');
	
	public $date_add;
	public $date_upd;
	public $deleted;
	public $lang;
	
	
	/**
	* Initialization
	*/ 
    function __construct($id=0){
		
		self::$post = $_POST;
		self::$get = $_GET;
		self::$files = $_FILES;
		
		$this->table = _DB_PREFIX_.$this->table;
		
		if( $id && is_numeric($id) && $id > 0 )
			$this->setObject($id);
		
	}
	
	
	/**
	 * Get lang from Cookie
	 * If no lang is defined, the system starts with default id_lang.
	 * @param Cookie $cookie Cookie object
	 * @return Integer ID lang
	 */
	protected function getLang(Cookie $cookie){
		
		if( isset($cookie) && !empty($cookie) ){
			if( intval($cookie->id_lang) > 0 )
				return $cookie->id_lang;
			else
				return 1;
		}elseif( !isset($cookie->id_lang) )
			return 1;
	}
	
		
	/**
	* Add an object in DB
	*/
	public function add(){
		$this->date_add = date('Y-m-d H:i:s');
		if($lastInsertedId = Db::getInstance()->Insert($this->table, array($this->identifier => '')))
		{
			$identifier = $this->identifier;
			$this->$identifier = $lastInsertedId;
			$this->update();
		}
	}
	
	
	/**
	* Update an object in DB
	*/
	public function update(){
		foreach( $this->langFields as $field ){
			
			if( !empty($this->$field) && is_array($this->$field) && count($this->$field) > 0  )
				$this->updateLangValue($field, $this->$field);
			
			
			elseif( !empty($this->$field) && !is_array($this->$field) )
				Error::show('"'.$field.'" must be an array');
			
			unset($this->$field);
		}
		
		$identifier = $this->identifier;
		$this->date_upd = date('Y-m-d H:i:s');
		
		$where = array( $identifier => $this->$identifier  ); 
		
		$columns = Db::getInstance()->getColumns( $this->table );
		$data = array();

		foreach( $columns as $column )
			$data[ $column['Field'] ] = $this->$column['Field'];
			
		Db::getInstance()->UpdateDB($this->table, $data, $where);
	}

	
	/**
	* Delete an object in DB
	*/
	public function delete(){
		$identifier = $this->identifier;
		$where = array( $identifier => $this->$identifier  ); 
		Db::getInstance()->UpdateDB($this->table, array('deleted' => 1 ), $where);
	}
	

	/**
	 * Update object lang attributes.
	 * This method compares column name and a predefined attribute name. If an attribute is defined as "lang", a special threathment insert or update data into  DB.
	 * The predefined lang attributes are set in Core->$langFields variable.
	 *
	 * @param String $columnName
	 * @param Array $array An array with the following structure array( $id_lang => $value );
	 * @todo The object attributes must be defined as lang in the definition class.
	 */
	public function updateLangValue($columnName, $array){
		
		if( Db::getInstance()->tableExists($this->table.'_lang') ){
			
			if( Db::getInstance()->columnExists($this->table.'_lang', $columnName) ){
				
				foreach($array as $key => $value){
					$identifier = $this->identifier;
					$param = array(
						$identifier => $this->$identifier,
						'id_lang' => $key
					);
					
					$line_exists = Db::getInstance()->getRow('SELECT id_lang FROM '.$this->table.'_lang WHERE '.$identifier.'="'.$this->$identifier.'" AND id_lang='.(int)$key);
					
					if( $line_exists )
						Db::getInstance()->UpdateDB($this->table.'_lang', array($columnName=>$value), $param);
					else
						Db::getInstance()->Insert($this->table.'_lang', array(
							$identifier => $this->$identifier,
							'id_lang' => $key,
							$columnName=>$value
						));
					
				}
			
			}
		
		}
	}
	
	
	/**
	* Retrieve object data from DB with the correct column name
	* @param Int $id Object identifier
	*/
	protected function setObject($id) {
		if( isset($this->table) && isset($this->identifier) ){
			$result = Db::getInstance()->getRow('SELECT * FROM '.$this->table.' WHERE '.$this->identifier.'=:id', array('id' => $id));
			if(count($result)>0){
				foreach( $result as $key => $value ){
					$this->$key = $value;
				}
			}
		}
		$this->setLangFields($id);		
	}
	
	/**
	* Instatiate lang object attribute from __construct() method
	* @param Integer $id Object unique ID in DB
	*/
	protected function setLangFields($id){
		if( Db::getInstance()->tableExists($this->table.'_lang') ){
			$langs = Lang::getLanguages();
			foreach( $langs as $lang )
				$this->lang[$lang['id_lang']] = Db::getInstance()->getRow('SELECT * FROM '.$this->table.'_lang WHERE '.$this->identifier.'=:id AND id_lang='.(int)$lang['id_lang'], array('id' => $id));	
		}
	}
	
	
}
?>
