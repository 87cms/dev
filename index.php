<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

global $cookie, $smarty, $__l;

require_once('config/settings.php');

if( $_REQUEST['ajax'] ){
	require_once('override/controllers/ajax.php');	
	$ajaxController = new AjaxController();
	$ajaxController->start();
	
}else{
	
	$frontController = new FrontController();
	$frontController->run();
	
}

