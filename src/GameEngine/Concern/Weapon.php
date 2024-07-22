<?php

namespace PHPP\GameEngine\Concern;

interface Weapon 
{    
    public function attack(): Damage;
}