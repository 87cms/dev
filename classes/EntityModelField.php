<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityModelFieldCore extends Core {

	protected $table = 'model_entity_field';	
	protected $identifier = 'id_field_model';
	
	
	public static function getFieldModelsList($id_entity_model, $type="", $deleted=0){
		$sql = 'SELECT * FROM '._DB_PREFIX_.'model_entity_field WHERE id_entity_model='.(int)$id_entity_model;
		
		if( $type ){
			$sql .= ' AND type=:type ';
			$params = array("type"=>$type);
		}
		
		if( $deleted )
			$sql .= " AND deleted=1 ";
		else
			$sql .= " AND deleted=0 ";
		
		return Db::getInstance()->Select($sql, $params);
			
	}
	
	
}