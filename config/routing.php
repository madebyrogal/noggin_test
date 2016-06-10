<?php
//Set routing
//Main entry tu middleware application
$app->match('/{entryId}', $app['controller.default'])->assert('entryId', '\d+')->method('GET|POST')->bind('homepage');