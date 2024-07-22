<?php

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->useAutowiring(true);

    $containerBuilder->addDefinitions([
        Logger::class => function () {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/app.log', Level::Debug));
            return $logger;
        },
    ]);
};
