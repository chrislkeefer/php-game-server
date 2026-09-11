<?php

namespace PHPP\Deathmatch\Command;

use Monolog\Logger;
use PHPP\GameEngine\Concern\Command;
use PHPP\GameEngine\Player;
use PHPP\GameEngine\World;
use PHPP\WebService\UdpMessage;
use PHPP\WebService\UdpMessageResponder;

class Connect extends Command
{
    public function __construct(
        private Logger $logger,
        private UdpMessageResponder $udpMessageResponder,
        private World $world,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'connect';
    }

    public function handle(UdpMessage $udpMessage): void
    {
        $existing = $this->world->getPlayer($udpMessage->address);
        if ($existing !== null) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'welcome',
                'message' => 'already connected',
                'player' => $existing->toArray(),
            ]));
            return;
        }

        $name = trim((string) ($udpMessage->data['name'] ?? 'Player'));
        if ($name === '') {
            $name = 'Player';
        }

        [$x, $y] = $this->world->randomSpawn();
        $player = new Player(
            id: uniqid('p_', true),
            address: $udpMessage->address,
            name: $name,
            x: $x,
            y: $y,
        );

        $this->world->addPlayer($player);
        $this->logger->info('Player connected', [
            'id' => $player->id,
            'name' => $player->name,
            'address' => $player->address,
        ]);

        $this->udpMessageResponder->send(json_encode([
            'type' => 'welcome',
            'message' => 'connected',
            'player' => $player->toArray(),
            'arena' => [
                'width' => World::ARENA_WIDTH,
                'height' => World::ARENA_HEIGHT,
            ],
        ]));
    }
}
