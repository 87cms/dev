<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:        function.getEntityModelLink.php
 * Type:        function
 * Name:        getEntityModelLink
 * Description: getEntityModelLink
 *
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 *
 * -------------------------------------------------------------
 * Parameter:
 * - id_entity_model        = id of entity model
 * - id_lang		  = id of lang
 * -------------------------------------------------------------
 * Example usage:
 *
 * <div>{agots s="Home"}</div>
 */
function smarty_function_getEntityModelLink($params, &$smarty)
{
    
	$id_entity_model = $params['id_entity_model'];
	if( array_key_exists('id_lang', $params) ){
		$id_lang = $params['id_lang'];
	}else{
		global $cookie;
		$id_lang = $cookie->id_lang;	
	}
	
	return Link::getEntityModelLink($id_entity_model, $id_lang);
	
}
?>
