<?php

namespace PHPP\GameEngine;

class Player
{
    public const MAX_HEALTH = 100;

    public function __construct(
        public string $id,
        public string $address,
        public string $name,
        public float $x = 0,
        public float $y = 0,
        public int $health = self::MAX_HEALTH,
        public int $kills = 0,
        public int $deaths = 0,
    ) {
    }

    public function respawn(float $x, float $y): void
    {
        $this->x = $x;
        $this->y = $y;
        $this->health = self::MAX_HEALTH;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'x' => round($this->x, 2),
            'y' => round($this->y, 2),
            'health' => $this->health,
            'kills' => $this->kills,
            'deaths' => $this->deaths,
        ];
    }
}
