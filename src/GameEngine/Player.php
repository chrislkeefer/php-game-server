<?php

namespace PHPP\GameEngine;

use PHPP\GameEngine\Concern\Weapon;

readonly class Player
{
    public function __construct(
        public User $user,
        public int $health,
        public Weapon $weapon,
    ) {
    }
}
