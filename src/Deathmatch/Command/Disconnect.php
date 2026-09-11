<?php

namespace PHPP\Deathmatch\Command;

use Monolog\Logger;
use PHPP\GameEngine\Concern\Command;
use PHPP\GameEngine\World;
use PHPP\WebService\UdpMessage;
use PHPP\WebService\UdpMessageResponder;

class Disconnect extends Command
{
    public function __construct(
        private Logger $logger,
        private UdpMessageResponder $udpMessageResponder,
        private World $world,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'disconnect';
    }

    public function handle(UdpMessage $udpMessage): void
    {
        $player = $this->world->removePlayer($udpMessage->address);

        if ($player === null) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'error',
                'message' => 'not connected',
            ]));
            return;
        }

        $this->logger->info('Player disconnected', [
            'id' => $player->id,
            'name' => $player->name,
        ]);

        $this->udpMessageResponder->send(json_encode([
            'type' => 'goodbye',
            'message' => 'disconnected',
            'player' => $player->toArray(),
        ]));
    }
}
