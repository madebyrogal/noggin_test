<?php

require_once __DIR__ . '/../vendor/autoload.php';

//Init app
$app = new App\App();

//Add services, config, routing
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/services.php';
require_once __DIR__ . '/../config/routing.php';

$app->run();
