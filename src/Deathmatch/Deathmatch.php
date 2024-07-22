<?php

namespace PHPP\Deathmatch;

use PHPP\Deathmatch\Command\Connect;
use PHPP\GameEngine\Concern\GameMode;
use PHPP\WebService\UdpMessageRouter;

class Deathmatch implements GameMode
{
    public function __construct(UdpMessageRouter $router)
    {
        $router->registerCommandFQCN([Connect::class]);
    }
}
