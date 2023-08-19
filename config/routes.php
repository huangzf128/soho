<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    $condtainer = $app->getContainer();

    $app->any('/', '\App\Action\HomeAction:index')->setName('index');
    $app->get('/index', '\App\Action\HomeAction:index')->setName('index');
    $app->post('/search', '\App\Action\HomeAction:search')->setName('search');
    $app->post('/reverse', '\App\Action\HomeAction:reverse')->setName('reverse');


	$app->post('/login', '\App\Action\AuthAction:login')->setName('login');
	$app->get('/logout', '\App\Action\AuthAction:logout')->setName('logout');
	$app->post('/signup', '\App\Action\AuthAction:signup')->setName('signup');
	

	$app->any('/product', '\App\Action\ProductAction:list')->setName('product-list');
	$app->any('/detail', '\App\Action\ProductAction:detail')->setName('detail');



    $app->get('/{viewId}', '\App\Action\HomeAction:contact')->setName('contact');


};
