<?php

use App\Controller\DefaultController;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\Service\Client;
use App\Service\ClientPager;
use App\Service\Auth;
use App\Lib\PETPagerMock;
//use App\Lib\PETPager;

//Service monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile'   => __DIR__ . '/../log/action.log',
    'monolog.level'     => $app['monolog.level']
));
//GuzzleHTTP
$app['guzzle.guzzle'] = $app->share(function($app) {
    
    return new GuzzleHttpClient($app['guzzle.config']);
});
//Client
$app['client.client'] = $app->share(function($app) {
    
    return new Client($app['monolog'], $app['guzzle.guzzle']);
});
//Auth service
$app['auth.auth'] = $app->share(function($app) {
    
    return new Auth($app['guzzle.guzzle'], $app['monolog'], $app['guzzle.config']);
});
//PET Pager
$app['pet.pager'] = function($app){
  
    //Service PETPagerMock for dev, PETPager for production
    return new PETPagerMock($app['monolog']);
};
//Client Pager
$app['client.pager'] = $app->share(function($app) {
    
    return new ClientPager($app['pet.pager'], $app['client.client'], $app['pager.config']['loginId']);
});
//Service Controller
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app['controller.default'] = function($app) {
    
    return new DefaultController($app['client.client'], $app['client.pager']);
};
