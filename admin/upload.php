<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
 
/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */
 
session_start();
define("SQL_DEBUG", true);
define("_ABSOLUTE_PATH_", getcwd().'/..' );
require_once('../config/constant.php');
require_once('../config/db_settings.php');

require_once('../classes/Core.php');

require_once('controller/AdminController.php');
if( file_exists('../override/controller/AdminController.php') )
	require_once('../override/controller/AdminController.php');
else
	eval('class AdminController extends AdminControllerCore {}');	

AdminController::loadElements();

$cookie = new Cookie();

// User is logged ?
$user = new User();

$user->authUser($cookie->emailp, $cookie->hashp);

if( !$user->is_logged )
	die();

$upload_handler = new UploadHandler();
session_write_close();