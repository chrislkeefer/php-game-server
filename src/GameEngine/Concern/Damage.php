<?php

namespace PHPP\GameEngine\Concern;

use PHPP\GameEngine\Enum\DamageType;

interface Damage
{
    public function getDamageType(): DamageType;
    public function getDamageAmount(): int;
}