<?php


/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

/**
* @deprecated
*/
class RichTextCore extends Core {

	function __construct($id){
		if($id)
			$this->setObject($id);	
	}
	
	public static function getElement(){
		$elements = self::scanDir();
		
		foreach( $elements as &$element ){
			$element['icon_small'] = $element['directory'].'/small_icon.png';
			$element['icon'] = $element['directory'].'/icon.png';
			$element['js'] = $element['directory'].'/'.$element['name'].'.js';
			$elementName = $element['name'];
			
			/*if( is_file(_RICHTEXT_DIR_.'/'.$elementName.'.php') ){
				require_once(_RICHTEXT_DIR_.'/'.$elementName.'.php');
				
			}*/
		}
		return $elements;
	}
	
	protected static function scanDir(){
		$element['name'] = '';
		$element['directory'] = '';
	}
	
	public function getData(){
	
	}

}