<?php

/* Dev settings */
/*set_time_limit(6000);
ini_set('display_errors','On');
define("SQL_DEBUG", true);*/

/* Prod settings */
ini_set('display_errors','Off');
define("SQL_DEBUG", false);

date_default_timezone_set('Europe/Paris');
define("_ABSOLUTE_PATH_", getcwd() );

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

$cookie = new Cookie();
 
session_start();


?>