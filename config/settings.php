<?php
set_time_limit(6000);

ini_set('display_errors','On');

include('config/db_settings.php');
include('config/constant.php');

define("SQL_DEBUG", true);

require_once('classes/Core.php');
require_once('classes/Cookie.php');
$cookie = new Cookie();

require_once('classes/Db.php');
require_once('classes/Mysql.php');

require_once('classes/Tools.php');

require_once('tools/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->compile_dir = 'tools/smarty/compile';
$smarty->cache_dir = 'tools/smarty/cache';
$smarty->config_dir = 'tools/smarty/configs';
//$smarty->caching = 1;
$smarty->debugging = false;

//if(is_file('lang/'.LANGUE.'.php')) { include('lang/'.LANGUE.'.php'); }

date_default_timezone_set('Europe/Paris');

session_start();

?>