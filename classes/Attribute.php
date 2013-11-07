<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */


class AttributeCore extends Core {

	protected $table = 'attribute';	
	protected $identifier = 'id_attribute';
	
	public $id_attribute;
	public $slug;
	public $name;
	
	function __construct($id){
		if($id){
			$this->setObject($id);	
			$this->values = self::getValues((int)$this->id_attribute);			
		}
	}
	
	/**
	* Add attribute values from admin
	* @param Array $values Array of data | $array = array( 'id_attribute' => Integer, 'value' => array( $id_lang1 => $value, ... ) );
	*/
	public function addAttributeValue($values){
		$langs = Lang::getLanguages;
		foreach( $langs as $lang ){
			$param = array(
				'id_lang' => $lang['id_lang'],
				'id_attribute' => (int)$this->id_attribute,
				'value' => $values[ $lang['id_lang'] ] 
			);
			Db::getInstance()->Insert(_DB_PREFIX.'attribute_value_lang', $param);
		}
	}
	
	
	public function updateEntityAttribute($id_entity, $ids_attribute){
		Db::getInstance()->Delete(_DB_PREFIX.'entity_attribute', array('id_entity' => $id_entity) );	
		foreach( $ids_attribute as $id_attribute )
			Db::getInstance()->Insert(_DB_PREFIX.'entity_attribute', array('id_entity' => $id_entity, 'id_attribute' => $id_attribute) );		
		
	}
	
	/**
	* Get the attributes list
	* @param Int $id_lang
	* @return Array
	*/
	public static function getAttributesList($id_lang=1){
		$sql = '
			SELECT * FROM '._DB_PREFIX_.'attribute A
			LEFT JOIN '._DB_PREFIX_.'attribute_lang L ON A.id_attribute = L.id_attribute
			WHERE L.id_lang = '.$id_lang.'
			AND A.deleted=0';
		$attributes = Db::getInstance()->Select($sql);	
		
		return $attributes;
	}
	
	/**
	* Get attribute values
	* @param Int $id_attribute
	* @param Int $id_lang
	* @param Bool $order_by_value
	* @return $id_lang
	*/
	public static function getValues($id_attribute, $id_lang=0, $order_by_value=false){
		$out = array();
		if( $id_lang )
			$attribute_values = Db::getInstance()->Select('SELECT L.*  FROM '._DB_PREFIX_.'attribute_value_lang L 
			LEFT JOIN '._DB_PREFIX_.'attribute_value V ON L.id_attribute_value = V.id_attribute_value
			WHERE V.id_attribute='.(int)$id_attribute.' AND L.id_lang='.(int)$id_lang.'
			'.( $order_by_value ? 'ORDER BY L.value' : '' ).'
			');			
		
		else{
			$langs = Lang::getLanguages();
			$sql = 'SELECT id_attribute_value  FROM '._DB_PREFIX_.'attribute_value WHERE id_attribute='.(int)$id_attribute;
			$attribute_values = Db::getInstance()->Select($sql);
			foreach( $attribute_values as &$value ){
				foreach( $langs as $lang ){
					$value['value'][ $lang['id_lang'] ] = Db::getInstance()->getRow('SELECT id_lang, value, id_attribute_value FROM '._DB_PREFIX_.'attribute_value_lang WHERE id_attribute_value='.(int)$value['id_attribute_value'].' AND id_lang='.(int)$lang['id_lang']);	
				}
			}
			
		}
		
		return $attribute_values;
	}
	
	
	
}
