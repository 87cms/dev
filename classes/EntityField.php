<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class EntityFieldCore extends Core {

	protected $table = 'entity_field';	
	protected $identifier = 'id_entity_field';
	
	/**
	* Get value for a specific fields.
	* @param Int $id_field_model
	* @param Int $id_entity
	* @param Int $id_lang If id_lang is not supplied, the system will return all languages
	* @param Bool $from_admin True if the call is sent from the back office
	* @return Array
	*/
	public static function getFieldValues($id_field_model, $id_entity, $id_lang=0, $from_admin=0){
		
		$out = array();
		
		$rq = 'SELECT F.raw_value, M.type FROM '._DB_PREFIX_.'entity_field F
			LEFT JOIN '._DB_PREFIX_.'model_entity_field M ON M.id_field_model = F.id_field_model
			WHERE F.id_field_model=:id_field_model AND F.id_entity=:id_entity';
		$value = Db::getInstance()->getRow($rq, array('id_field_model'=>$id_field_model,'id_entity'=>$id_entity));
		
		if( !$value['raw_value'] ){
			
			$rq = 'SELECT L.* FROM '._DB_PREFIX_.'entity_field F
				LEFT JOIN '._DB_PREFIX_.'entity_field_lang L ON L.id_entity_field = F.id_entity_field
				
				WHERE F.id_field_model=:id_field_model
					  AND F.id_entity=:id_entity
				'.( $id_lang > 0 ? ' AND L.id_lang='.(int)$id_lang : '' );	
			$datas = Db::getInstance()->Select($rq, array('id_field_model'=>$id_field_model,'id_entity'=>$id_entity));
			
			foreach( $datas as $data ){
				if( $id_lang ) { 
					
					/* convert markdown */
					if( $value['type'] == "markdown" ){					
						require_once(_ABSOLUTE_PATH_.'/tools/markdown/Markdown.php');
						require_once(_ABSOLUTE_PATH_.'/tools/markdown/MarkdownExtra.php');
						$out['html'] = MarkdownExtra::defaultTransform($data['value']);
						$out['markdown'] = $data['value'];
					}
					else
						$out = $data['value'];	
				
				
				} else {
					
					/* convert markdown */
					if( $value['type'] == "markdown" ){					
						require_once(_ABSOLUTE_PATH_.'/tools/markdown/Markdown.php');
						require_once(_ABSOLUTE_PATH_.'/tools/markdown/MarkdownExtra.php');
						$out[ $data['id_lang'] ]['html'] = MarkdownExtra::defaultTransform($data['value']);
						$out[ $data['id_lang'] ]['markdown'] = $data['value'];
					}
					else
						$out[ $data['id_lang'] ] = $data['value'];	
						
				}
			}
			
		}else{
			
			if( $value['type'] == "inputImage" ){
				
				$meta = Db::getInstance()->getValue('SELECT meta_title FROM '._DB_PREFIX_.'entity_lang WHERE id_entity=:id_entity AND id_lang=:id_lang', array('id_entity'=>$id_entity,'id_lang'=>$id_lang));
				
				$imagesizes = array('admin', 'large', 'medium', 'thumb');
				$images = json_decode($value['raw_value'], true);
	
				if( count($images) > 0 ){
					
					foreach( $images as $index => $image ){
						$path = explode('/', $image['path']);
						unset( $path[ (count($path)-1) ] );
						$path = implode('/', $path);
						
						foreach( $imagesizes as $size ){						
							$out[ $index ][ $size ]['id_media'] = $image['id_media'];
							$out[ $index ][ $size ]['path'] = $path.'/'.$size.'/'.$id_field_model.'-'.$image['name'];
							$out[ $index ][ $size ]['name'] = $image['name'];
							$out[ $index ][ $size ]['title'] = $meta;
							$out[ $index ][ $size ]['alt'] = $meta;
						}
						
						$out[ $index ]['original']['id_media'] = $image['id_media'];
						$out[ $index ]['original']['path'] = $path.'/'.$image['name'];
						$out[ $index ]['original']['name'] = $image['name'];
						$out[ $index ]['original']['title'] = $meta;
						$out[ $index ]['original']['alt'] = $meta;
						
					}
				}
				
			}
			
			elseif( $value['type'] == "select" ){
				$value = rtrim($value['raw_value'], ',');
				$ids = explode(',', $value);
				$out = AttributeValue::getAttributeValue($ids, $id_lang);
			}		
				
			elseif( $value['type'] == "linkedEntities" ){
				
				$out['type'] = "linkedEntities";
				$out['raw_value'] = $value['raw_value'];
				
			}
			
			elseif( $value['type'] == "date" ){
				$out = $value['raw_value'];
			}
			
			else
				$out = json_decode($value['raw_value'], true);
		
		}
		
		return $out;
		
	}
	
	public static function getRawValue($id_field_model, $id_entity, $id_lang=0){
		$rq = 'SELECT raw_value FROM '._DB_PREFIX_.'entity_field WHERE id_field_model=:id_field_model AND id_entity=:id_entity';
		$out = Db::getInstance()->getValue($rq, array('id_field_model'=>$id_field_model,'id_entity'=>$id_entity));
		return $out;
	}
	
}