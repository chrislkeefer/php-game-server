<?php

namespace PHPP\Deathmatch\Command;

use Monolog\Logger;
use PHPP\GameEngine\Concern\Command;
use PHPP\GameEngine\Weapon\Blaster;
use PHPP\GameEngine\World;
use PHPP\WebService\UdpMessage;
use PHPP\WebService\UdpMessageResponder;

class Shoot extends Command
{
    public function __construct(
        private Logger $logger,
        private UdpMessageResponder $udpMessageResponder,
        private World $world,
        private Blaster $blaster,
    ) {
    }

    public static function getCommandName(): string
    {
        return 'shoot';
    }

    public function handle(UdpMessage $udpMessage): void
    {
        $shooter = $this->world->getPlayer($udpMessage->address);
        if ($shooter === null) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'error',
                'message' => 'not connected',
            ]));
            return;
        }

        if ($shooter->health <= 0) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'error',
                'message' => 'dead',
            ]));
            return;
        }

        $dx = (float) ($udpMessage->data['dx'] ?? 0);
        $dy = (float) ($udpMessage->data['dy'] ?? 0);
        $length = sqrt($dx * $dx + $dy * $dy);

        if ($length < 0.001) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'shot',
                'hit' => false,
                'message' => 'no aim direction',
            ]));
            return;
        }

        $ux = $dx / $length;
        $uy = $dy / $length;
        $damage = $this->blaster->attack()->getDamageAmount();

        $hitPlayer = null;
        $bestProj = World::HITSCAN_RANGE + 1;

        foreach ($this->world->getPlayers() as $target) {
            if ($target->id === $shooter->id || $target->health <= 0) {
                continue;
            }

            $vx = $target->x - $shooter->x;
            $vy = $target->y - $shooter->y;
            $proj = $vx * $ux + $vy * $uy;

            if ($proj < 0 || $proj > World::HITSCAN_RANGE) {
                continue;
            }

            $closestX = $shooter->x + $ux * $proj;
            $closestY = $shooter->y + $uy * $proj;
            $dist = sqrt(
                ($target->x - $closestX) ** 2 +
                ($target->y - $closestY) ** 2
            );

            if ($dist <= World::HITSCAN_RADIUS && $proj < $bestProj) {
                $bestProj = $proj;
                $hitPlayer = $target;
            }
        }

        if ($hitPlayer === null) {
            $this->udpMessageResponder->send(json_encode([
                'type' => 'shot',
                'hit' => false,
            ]));
            return;
        }

        $hitPlayer->health = max(0, $hitPlayer->health - $damage);
        $killed = false;

        if ($hitPlayer->health <= 0) {
            $killed = true;
            $hitPlayer->deaths++;
            $shooter->kills++;
            [$sx, $sy] = $this->world->randomSpawn();
            $hitPlayer->respawn($sx, $sy);
            $this->logger->info('Player fragged', [
                'killer' => $shooter->name,
                'victim' => $hitPlayer->name,
            ]);
        }

        $this->udpMessageResponder->send(json_encode([
            'type' => 'shot',
            'hit' => true,
            'damage' => $damage,
            'killed' => $killed,
            'target' => $hitPlayer->toArray(),
            'shooter' => $shooter->toArray(),
        ]));
    }
}
