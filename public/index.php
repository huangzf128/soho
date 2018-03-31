<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


// --------------------------------------------------------
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Controller/Router/Route.php';

//独自ルートの構築
$front = Zend_Controller_Front::getInstance();
$router = $front->getRouter();

//独自ルートをルータに定義
$router->addRoute('resultUrl', 
        new Zend_Controller_Router_Route('result',	
            array('module' => 'keyword', 'controller'=>'keyword', 'action'=>'get-Suggest-Keyword')));

$router->addRoute('historyindex',
		new Zend_Controller_Router_Route('history/index.html',
				array('module' => 'keyword', 'controller'=>'keyword',
						'action'=>'get-Search-History-List', 'currentNo' => 1)
				));
$router->addRoute('history',
		new Zend_Controller_Router_Route('history/',
				array('module' => 'keyword', 'controller'=>'keyword',
						'action'=>'get-Search-History-List', 'currentNo' => 1)
		));
$router->addRoute('historyindexn',
		new Zend_Controller_Router_Route_Regex('history/index(\d+)\.html',
				array('module' => 'keyword', 'controller'=>'keyword', 
				        'action'=>'get-Search-History-List'),
		        array('currentNo' => 1)));


$router->addRoute('historyDetailUrl',
		new Zend_Controller_Router_Route_Regex('history/archive/(.+)',
				array('module' => 'keyword', 'controller'=>'keyword', 
				        'action'=>'get-Search-History-Detail-File'),
		        array('filename' => 1)));



// --------------------------------------------------------

$application->bootstrap()
            ->run();
