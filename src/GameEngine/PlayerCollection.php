<?php

namespace PHPP\GameEngine;

use Ramsey\Collection\AbstractCollection;

class PlayerCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Player::class;
    }
}