<?php

namespace PHPP\WebService;

interface MessageHandlerInterface
{
    public static function getCommandName(): string;
    public function handle(UdpMessage $udpMessage): void;
}