<?php

namespace PHPP\Deathmatch;

use PHPP\Deathmatch\Command\Connect;
use PHPP\Deathmatch\Command\Disconnect;
use PHPP\Deathmatch\Command\Move;
use PHPP\Deathmatch\Command\Shoot;
use PHPP\GameEngine\Concern\GameMode;
use PHPP\WebService\UdpMessageRouter;

class Deathmatch implements GameMode
{
    public function __construct(UdpMessageRouter $router)
    {
        $router->registerCommandFQCN([
            Connect::class,
            Disconnect::class,
            Move::class,
            Shoot::class,
        ]);
    }
}
