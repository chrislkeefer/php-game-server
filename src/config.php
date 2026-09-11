<?php

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPP\GameEngine\Weapon\Blaster;
use PHPP\GameEngine\World;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->useAutowiring(true);

    // Definitions are shared (singletons) by default in PHP-DI.
    $containerBuilder->addDefinitions([
        Logger::class => function () {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(__DIR__ . '/app.log', Level::Debug));
            return $logger;
        },

        World::class => function () {
            return new World();
        },

        Blaster::class => function () {
            return new Blaster();
        },
    ]);
};
