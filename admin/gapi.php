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


$SERVICE_ACCOUNT_EMAIL =  Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="GA_SERVICE_ACCOUNT_EMAIL"');
$SERVICE_ACCOUNT_PKCS12_FILE_PATH = 'tools/gapi/'.Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="GA_SERVICE_ACCOUNT_PKCS12_FILE_PATH"'); 
$CLIENT_ID = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="GA_CLIENT_ID"');
$PROJECT_ID = Db::getInstance()->getValue('SELECT value FROM '._DB_PREFIX_.'config WHERE name="GA_PROJECT_ID"');

ini_set('display_errors','on');
if( $SERVICE_ACCOUNT_EMAIL && $SERVICE_ACCOUNT_PKCS12_FILE_PATH && $CLIENT_ID && $PROJECT_ID ){

	require_once dirname(__FILE__).'/tools/gapi/Google_Client.php';
	require_once dirname(__FILE__).'/tools/gapi/contrib/Google_AnalyticsService.php';
	
	require_once 'tools/gapi/Google_Client.php';
	require_once 'tools/gapi/contrib/Google_AnalyticsService.php';
	
	// create client object and set app name
	$client = new Google_Client();
	$client->setApplicationName(APP_NAME);
	
	// set assertion credentials
	$client->setAssertionCredentials(
	  new Google_AssertionCredentials(
		$SERVICE_ACCOUNT_EMAIL,
		array('https://www.googleapis.com/auth/analytics.readonly'),
			  file_get_contents($SERVICE_ACCOUNT_PKCS12_FILE_PATH)  // keyfile
	));
	
	// other settings
	$client->setClientId($CLIENT_ID);
	$client->setAccessType('offline_access');
	
	// create service
	$service = new Google_AnalyticsService($client);
	
	// *** Line charts, visits for the last months ***
	$from = date('Y-m-d', time()-30*24*60*60); // - 1 monts
	$to = date('Y-m-d'); // today
	$dimensions = 'ga:date';
	$metrics = 'ga:visits';
	$data = $service->data_ga->get('ga:'.$PROJECT_ID, $from, $to, $metrics, array('dimensions' => $dimensions));
	
	$rows = $data['rows'];
	$out['graph'] = $rows;
	
	// *** Stats for the last 30 days ***
	$from = date('Y-m-d', time()-30*24*60*60); // - 1 months
	$to = date('Y-m-d'); // today
	$metrics = 'ga:visits,ga:pageviews,ga:bounces,ga:entranceBounceRate,ga:visitBounceRate,ga:avgTimeOnSite,ga:newVisits,ga:pageviewsPerVisit';
	$dimensions = 'ga:year';
	$data = $service->data_ga->get('ga:'.$PROJECT_ID, $from, $to, $metrics, array('dimensions' => $dimensions));
	
	$out['numbers'] = $data['rows'];
	
	
	// *** Browser Stats for the last 30 days ***
	$from = date('Y-m-d', time()-30*24*60*60); // - 1 months
	$to = date('Y-m-d'); // today
	$dimensions = 'ga:browser';
	$metrics = 'ga:visits';
	$data = $service->data_ga->get('ga:'.$PROJECT_ID, $from, $to, $metrics, array('dimensions' => $dimensions));
	
	$out['browser'] = $data['rows'];
	usort($out['browser'], 'fonctionComparaison');
	
	// *** Mobile Stats for the last 30 days ***
	$from = date('Y-m-d', time()-30*24*60*60); // - 1 months
	$to = date('Y-m-d'); // today
	$dimensions = 'ga:isMobile';
	$metrics = 'ga:visitors';
	$data = $service->data_ga->get('ga:'.$PROJECT_ID, $from, $to, $metrics, array('dimensions' => $dimensions));
	
	$out['mobile'] = $data['rows'];
	
	
	
	// *** Mobile Stats 2 for the last 30 days ***
	$from = date('Y-m-d', time()-30*24*60*60); // - 1 months
	$to = date('Y-m-d'); // today
	$dimensions = 'ga:mobileDeviceBranding';
	$metrics = 'ga:visitors';
	$data = $service->data_ga->get('ga:'.$PROJECT_ID, $from, $to, $metrics, array('dimensions' => $dimensions));
	
	$out['mobileOS'] = $data['rows'];
	echo json_encode($out);
	
	
	
}

function fonctionComparaison($a, $b){
    $a[1] = intval($a[1]);
	$b[1] = intval($b[1]);
	return $a[1] < $b[1];
}

session_write_close();