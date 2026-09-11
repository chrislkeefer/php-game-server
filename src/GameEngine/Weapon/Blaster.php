<?php

namespace PHPP\GameEngine\Weapon;

use PHPP\GameEngine\Concern\Damage;
use PHPP\GameEngine\Concern\Weapon;

class Blaster implements Weapon
{
    public function attack(): Damage
    {
        return new BulletDamage();
    }
}
