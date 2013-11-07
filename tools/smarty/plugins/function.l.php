<?php
/*
 *     Smarty plugin
 * -------------------------------------------------------------
 * File:        function.l.php
 * Type:        function
 * Name:        l
 * Description: Translation with an array
 *
 * -------------------------------------------------------------
 * @license GNU Public License (GPL)
 *
 * -------------------------------------------------------------
 * Parameter:
 * - s        = string to translate
 * -------------------------------------------------------------
 * Example usage:
 *
 * <div>{agots s="Home"}</div>
 */
function smarty_function_l($params, &$smarty)
{
    global $__l;
	$s = $params['s'];
	
	if($__l[md5($s)])
		return $__l[md5($s)];
	else
		return $s;
}
?>
