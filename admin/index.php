<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

global $cookie, $smarty;

session_start();

define("_ABSOLUTE_PATH_", getcwd().'/..' );
require_once('../config/constant.php');
require_once('../config/db_settings.php');

define("SQL_DEBUG", true);

require_once('../tools/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->compile_dir = 'cache';
$smarty->cache_dir = 'cache';
$smarty->config_dir = '../tools/smarty/configs';
$smarty->template_dir = 'template';
$smarty->caching = 0;
$smarty->debugging = false;

require_once('../classes/Core.php');

require_once('controller/AdminController.php');
if( file_exists('../override/controller/AdminController.php') )
	require_once('../override/controller/AdminController.php');
else
	eval('class AdminController extends AdminControllerCore {}');	

AdminController::loadElements();


date_default_timezone_set('Europe/Paris');

if( Tools::getSuperglobal('ajax') ){
	require_once('ajax.php');	
	$ajaxController = new AjaxController();
	$ajaxController->start();
	
}else{
	$adminController = new AdminController();
	$adminController->start();
}

?>