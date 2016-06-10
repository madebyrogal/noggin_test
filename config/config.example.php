<?php
Use Monolog\Logger;

//Set debug mode
$app['debug'] = true;
//Monolog level
$app['monolog.level'] = Logger::INFO;
//Guzzle
$app['guzzle.config'] = [
    'base_url' => 'https://bitnoise.nogginoca.com:443/api/v2/',
    'port' => 443,
    'defaults' => [
        'headers' => [
            'X-Session-Id' => apc_fetch('sessionID')
        ],
        'verify' => false,
    ],
    'username' => '',
    'password' => ''
];
//Pager config
$app['pager.config'] = [
    'loginid' => 0
];

