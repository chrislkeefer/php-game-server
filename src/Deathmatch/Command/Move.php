<?php

namespace PHPP\Deathmatch\Command;

use PHPP\GameEngine\Concern\Command;
use PHPP\GameEngine\World;
use PHPP\WebService\UdpMessage;
use PHPP\WebService\UdpMessageResponder;

class Move extends Command
{
    public function __construct(
        private UdpMessageResponder $udpMessageResponder,
        private World $world,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'move';
    }

    public function handle(UdpMessage $udpMessage): void
    {
        $player = $this->world->getPlayer($udpMessage->address);
        if ($player === null) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'error',
                'message' => 'not connected',
            ]));
            return;
        }

        $dx = (float) ($udpMessage->data['dx'] ?? 0);
        $dy = (float) ($udpMessage->data['dy'] ?? 0);

        $magnitude = sqrt($dx * $dx + $dy * $dy);
        if ($magnitude > World::MAX_MOVE_SPEED) {
            $scale = World::MAX_MOVE_SPEED / $magnitude;
            $dx *= $scale;
            $dy *= $scale;
        }

        [$x, $y] = $this->world->clampPosition($player->x + $dx, $player->y + $dy);
        $player->x = $x;
        $player->y = $y;

        $this->udpMessageResponder->send(json_encode([
            'type' => 'moved',
            'player' => $player->toArray(),
        ]));
    }
}
