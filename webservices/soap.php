<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Webservices
 */


/*********************************/
/*********************************/
/*********************************/
/*   	ALPHA NOT TESTED		 */
/*********************************/
/*********************************/
/*********************************/

global $cookie;

define("_ABSOLUTE_PATH_", getcwd() );

date_default_timezone_set('Europe/Paris');

require_once('../config/constant.php');
require_once('../config/db_settings.php');

require_once('../classes/Core.php');

require_once('../classes/FrontController.php');
if( file_exists('../override/classes/FrontController.php') )
	require_once('../override/classes/FrontController.php');
else
	eval('class FrontController extends FrontControllerCore {}');	

FrontController::loadClasses();
FrontController::loadControllers();	

require_once('../classes/Webservice.php');
if( file_exists('../override/classes/Webservice.php') )
	require_once('../override/classes/Webservice.php');
else
	eval('class Webservice extends WebserviceCore {}');

session_start();

$wsdl = "87cmsSOAP.wsdl";
if ( isset($_GET['wsdl']) ){
	header('location: '.$wsdl);
}
else {
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		/**
			Use of SOAP webservices
			
			1) Start SOAP client
			2) Authenticate with API Key
			3) Do what you want !
			
		*/
		
		ini_set("soap.wsdl_cache_enabled", "0"); 
		$server = new SoapServer($wsdl);
		$server->setClass("Webservice");
		$server->handle();
	
	}
 
}

