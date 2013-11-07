<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:        function.getEntityLink.php
 * Type:        function
 * Name:        getEntityLink
 * Description: getEntityLink
 *
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 *
 * -------------------------------------------------------------
 * Parameter:
 * - id_entity        = id of entity
 * - id_lang		  = id of lang
 * -------------------------------------------------------------
 * Example usage:
 *
 * <div>{agots s="Home"}</div>
 */
function smarty_function_getEntityModelLinkTitle($params, &$smarty)
{
    
	$id_entity_model = $params['id_entity_model'];
	$id_lang = $params['id_lang'];
	
	return Link::getEntityModelLinkTitle($id_entity_model, $id_lang);
	
}
?>
