<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */


/**
 */
class AttributeValueCore extends Core {

	protected $table = 'attribute_value';	
	protected $identifier = 'id_attribute_value';
	
	public $id_attribute_value;
	public $id_attribute;
	public $name;
	
	
	public function delete(){
		Db::getInstance()->Delete('attribute_value_lang', array('id_attribute_value' => $this->id_attribute_value));
		Db::getInstance()->Delete('attribute_value', array('id_attribute_value' => $this->id_attribute_value));	
	}
	
	/**
	* Get attribute value
	* @param Int|Array $id_attribute_value
	* @param Int $id_lang
	* @param String $type Attribute type
	* @return Array
	*/
	public static function getAttributeValue($id_attribute_value, $id_lang, $attribute_type=''){
		$sql = 'SELECT value FROM '._DB_PREFIX_.'attribute_value_lang WHERE id_lang=:id_lang AND ';
		
		if( is_array($id_attribute_value) ){
			$sql .= ' ( ';
			for( $i=0; $i<count($id_attribute_value); $i++){
				if( $i > 1 )
					$sql .= ' OR ';
				$sql .= ' id_attribute_value='.(int)$id_attribute_value[$i].' ';
			}
			$sql .= ' ) ';			
		}else
			$sql .= ' id_attribute_value='.(int)$id_attribute_value;
		
		if( $attribute_type == "checkbox" )
			$data = Db::getInstance()->Select($sql, array('id_lang'=>$id_lang));	
		
		else
			$data = Db::getInstance()->getValue($sql, array('id_lang'=>$id_lang));
		
		return $data;
		
	}
	
}

