<?php

namespace PHPP\GameEngine\Weapon;

use PHPP\GameEngine\Concern\Damage;
use PHPP\GameEngine\Enum\DamageType;

class BulletDamage implements Damage
{
    public function getDamageType(): DamageType
    {
        return DamageType::Bullet;
    }

    public function getDamageAmount(): int
    {
        return 25;
    }
}
