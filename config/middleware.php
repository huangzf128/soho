<?php

use Selective\BasePath\BasePathMiddleware;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use App\Middleware\ViewMiddleware;
use App\Middleware\HttpBasicAuth;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

	// session
	$app->add(new \Slim\Middleware\Session([
		'autorefresh' => true,
		'lifetime' => '3 minutes',
	]));

    // $app->add(BasePathMiddleware::class);
    // $app->add(new HttpBasicAuth('bpoint-admin', '2021zhao', '', $app));
    
    $app->add(ViewMiddleware::class);

    // Catch exceptions and errors
    $app->add(ErrorMiddleware::class);


    $app->add(\Slim\Views\TwigMiddleware::createFromContainer($app));
};
