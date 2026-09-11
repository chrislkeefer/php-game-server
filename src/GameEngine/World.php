<?php

namespace PHPP\GameEngine;

use React\Datagram\Socket;

class World
{
    public const ARENA_WIDTH = 800;
    public const ARENA_HEIGHT = 600;
    public const TICK_HZ = 20;
    public const MAX_MOVE_SPEED = 12;
    public const HITSCAN_RANGE = 220;
    public const HITSCAN_RADIUS = 18;

    /** @var array<string, Player> keyed by UDP address */
    private array $players = [];

    private ?Socket $socket = null;

    public function setSocket(Socket $socket): void
    {
        $this->socket = $socket;
    }

    public function getSocket(): ?Socket
    {
        return $this->socket;
    }

    public function addPlayer(Player $player): void
    {
        $this->players[$player->address] = $player;
    }

    public function removePlayer(string $address): ?Player
    {
        if (!isset($this->players[$address])) {
            return null;
        }

        $player = $this->players[$address];
        unset($this->players[$address]);

        return $player;
    }

    public function getPlayer(string $address): ?Player
    {
        return $this->players[$address] ?? null;
    }

    /** @return array<string, Player> */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /** @return array{0: float, 1: float} */
    public function randomSpawn(): array
    {
        return [
            (float) random_int(40, self::ARENA_WIDTH - 40),
            (float) random_int(40, self::ARENA_HEIGHT - 40),
        ];
    }

    public function clampPosition(float $x, float $y): array
    {
        return [
            max(0, min(self::ARENA_WIDTH, $x)),
            max(0, min(self::ARENA_HEIGHT, $y)),
        ];
    }

    public function broadcastState(): void
    {
        if ($this->socket === null || $this->players === []) {
            return;
        }

        $payload = json_encode([
            'type' => 'state',
            'players' => array_values(array_map(
                fn (Player $player) => $player->toArray(),
                $this->players
            )),
        ]);

        foreach ($this->players as $player) {
            $this->socket->send($payload, $player->address);
        }
    }

    public function replyJson(string $address, array $data): void
    {
        if ($this->socket === null) {
            return;
        }

        $this->socket->send(json_encode($data), $address);
    }
}
