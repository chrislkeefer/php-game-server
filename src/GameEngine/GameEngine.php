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
        $udpServer = UdpServerFactory::create(
            onFulfilled: function ($server) {
                $server->on('message', function ($message, $address, $server) {
                    $this->messageRouter->route($message, $address, $server);
                });
            },
            onRejected: function (Exception $e) {
                echo $e->getMessage();
            },
        );

        $udpServer->run();
    }
}
