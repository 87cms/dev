<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Webservices
 */

global $cookie;

define("_ABSOLUTE_PATH_", getcwd().'/../' );

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	/**
		Use of REST webservices
		
		All data must be passed in POST method.
		
		3 mandatory parameters :
			$apikey = String
			$returnFormat = (JSON|XML)
			$action = String
		
		If a method contains parameters, you have to name as parameters.
		
		Example :
		You want to get the entity data with id_entity=5. The return format is JSON.
		URL = http://yourdomaine.com/webservices/rest.php?apikey=YOUR_API_KEY&returnFormat=JSON&action=getEntity&id_entity=5		
	*/	
	
	$return = array();
	$apikey = ( isset($_POST['apikey']) ? $_POST['apikey'] : '' );
	$returnFormat = ( strtoupper($_POST['returnFormat']) == "JSON" || strtoupper($_POST['returnFormat']) == "XML" ? strtoupper($_POST['returnFormat']) : 'JSON' );
	$action = $_POST['action'];
	
	unset($_POST['apikey'], $_POST['returnFomat'], $_POST['action']);
	
	if( $apikey ){
		
		$webservice = new Webservice();
		$webservice->authenticate($apikey);
		
		$reflector = new ReflectionClass('Webservice');
		if( $reflector->hasMethod( $action ) ){
			
			$pass = array();
			$reflectionMethod = new ReflectionMethod('Webservice', $action);
			foreach($reflectionMethod->getParameters() as $param){
				
				if( isset( $_POST[$param->getName()] ) )
					$pass[] = $_POST[$param->getName()];
				
			}
			$return = $reflectionMethod->invokeArgs($webservice, $pass);			
				
		}
		else {
			$return['response']['code'] = 403;
			$return['response']['message'] = 'Action not available';			
		}
		
		
	
	} else {
		$return['response']['code'] = 403;
		$return['response']['message'] = 'Access forbidden';
	
	}
	//if( $returnFormat == "JSON" )
		echo json_encode($return);
	
	
}
 

