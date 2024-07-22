<?php

namespace PHPP\GameEngine\Concern;

use PHPP\WebService\MessageHandlerInterface;
use PHPP\WebService\UdpMessage;

abstract class Command implements MessageHandlerInterface
{
    abstract public static function getCommandName(): string;
    abstract public function handle(UdpMessage $udpMessage): void;
}