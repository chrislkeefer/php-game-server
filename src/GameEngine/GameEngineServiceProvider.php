<?php

namespace PHPP\GameEngine;

use DI\Container;

class GameEngineServiceProvider
{
    public function __construct(
        private Container $container
    ) {
    }

    public static function commands(): array
    {
        // Deathmatch owns gameplay command registration.
        return [];
    }
}
