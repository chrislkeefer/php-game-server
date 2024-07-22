<?php

namespace PHPP\Deathmatch\Command;

use Monolog\Logger;
use PHPP\GameEngine\Concern\Command;
use PHPP\WebService\UdpMessage;
use PHPP\WebService\UdpMessageResponder;

class Connect extends Command
{
    public function __construct(
        private Logger $logger,
        private UdpMessageResponder $udpMessageResponder
    ) {
    }

    public static function getCommandName(): string
    {
        return 'connect';
    }

    public function handle(UdpMessage $udpMessage): void
    {
        $this->udpMessageResponder->send("DM BABY WOOOOOO!");
    }
}
