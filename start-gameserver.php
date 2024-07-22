<?php

use DI\ContainerBuilder;
use PHPP\Application;

require 'vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerConfig = require __DIR__ . '/src/config.php';
$containerConfig($containerBuilder);
$container = $containerBuilder->build();

$app = $container->get(Application::class);
$app->start();
