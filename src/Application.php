<?php

namespace PHPP;

use PHPP\Deathmatch\Deathmatch;
use PHPP\GameEngine\GameEngine;

class Application
{
    public function __construct(
        protected GameEngine $gameEngine,
        protected Deathmatch $deathMatch,
    ) {
    }

    public function start(): void
    {
        $this->gameEngine->setGameMode($this->deathMatch);
        $this->gameEngine->run();
    }
}
