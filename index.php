<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

global $cookie, $smarty, $__l;


define("SQL_DEBUG", true);

define("_ABSOLUTE_PATH_", getcwd() );

date_default_timezone_set('Europe/Paris');

require_once('config/constant.php');
require_once('config/db_settings.php');

require_once('tools/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->compile_dir = 'tools/smarty/compile';
$smarty->cache_dir = 'tools/smarty/cache';
$smarty->config_dir = 'tools/smarty/configs';
$smarty->template_dir = 'template';
$smarty->debugging = false;

require_once('classes/Core.php');

require_once('classes/FrontController.php');
if( file_exists('override/classes/FrontController.php') )
	require_once('override/classes/FrontController.php');
else
	eval('class FrontController extends FrontControllerCore {}');	

FrontController::loadClasses();
FrontController::loadControllers();	

session_start();

if( $_REQUEST['ajax'] ){
	require_once('override/controllers/ajax.php');	
	$ajaxController = new AjaxController();
	$ajaxController->start();
	
}else{
	
	$frontController = new FrontController();
	$frontController->run();
	
}

