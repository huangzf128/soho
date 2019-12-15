<?php

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
        // realpath(APPLICATION_PATH . '/../library'),   // for zend
        realpath(APPLICATION_PATH . '/library'),
        get_include_path(),
)));

require_once 'Zend/Application.php';

$application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application_bs.ini'
        );


$client = new Zend_Http_Client();
$client->setConfig(array(
        'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        'keepalive' => true,
        'curloptions' => array(CURLOPT_FOLLOWLOCATION => false),
));


	$api = "http://ad8-ssl.sakura.ne.jp/kw/result?site=3&keyword=football";
	$client->setUri($api);
	
	$response = $client->request();
	
	$res = json_decode($response->getBody());
	echo $res->result;

	
?>
