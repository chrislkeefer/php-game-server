<?php

namespace PHPP\GameEngine;

use Exception;
use PHPP\GameEngine\Concern\GameMode;
use PHPP\WebService\UdpMessageRouter;
use PHPP\WebService\UdpServerFactory;

class GameEngine
{
    protected GameMode $gameMode;

    public function __construct(
        private UdpMessageRouter $messageRouter,
        private World $world,
    ) {
        $this->messageRouter->registerCommandFQCN(
            GameEngineServiceProvider::commands()
        );
    }

    public function setGameMode(GameMode $gameMode): void
    {
        $this->gameMode = $gameMode;
    }

    public function getGameMode(): GameMode
    {
        return $this->gameMode;
    }

    public function run(): void
    {
        $loop = UdpServerFactory::create(
            onFulfilled: function ($server) {
                $this->world->setSocket($server);
                $server->on('message', function ($message, $address, $server) {
                    $this->messageRouter->route($message, $address, $server);
                });
            },
            onRejected: function (Exception $e) {
                echo $e->getMessage();
            },
        );

        $tickInterval = 1 / World::TICK_HZ;
        $loop->addPeriodicTimer($tickInterval, function () {
            $this->world->broadcastState();
        });

        echo "Game server listening on UDP 0.0.0.0:12345 (tick " . World::TICK_HZ . "Hz)\n";
        $loop->run();
    }
}
