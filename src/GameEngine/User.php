<?php

namespace PHPP\GameEngine;

use PHPP\GameEngine\Enum\UserType;

readonly class User
{
    public function __construct(
        protected string $name,
        protected UserType $type = UserType::Human,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): UserType
    {
        return $this->type;
    }
}
