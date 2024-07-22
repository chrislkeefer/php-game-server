<?php

namespace PHPP\GameEngine;

use DI\Container;
use PHPP\GameEngine\Command\Connect;

class GameEngineServiceProvider
{

    public function __construct(
        private Container $container
    ) {
    }

    public static function commands(): array
    {
        return [
            Connect::class
        ];
    }
}