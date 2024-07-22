<?php

namespace PHPP\GameEngine;

use PHPP\GameEngine\Enum\UserType;

readonly class User
{
    public function __construct(
        protected string $name
    ) {
    }
}
