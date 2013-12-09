<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:        function.getHomeLink.php
 * Type:        function
 * Name:        getHomeLink
 * Description: getHomeLink
 *
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 *
 * -------------------------------------------------------------
 * Parameter:
 * - id_lang		  = id of lang
 * -------------------------------------------------------------
 * Example usage:
 *
 * <div>{agots s="Home"}</div>
 */
function smarty_function_getHomeLink($params, &$smarty)
{
    
	if( array_key_exists('id_lang', $params) ){
		$id_lang = $params['id_lang'];
	}else{
		global $cookie;
		$id_lang = $cookie->id_lang;	
	}
	
	return Link::getHomeLink($id_lang);
	
}
?>
