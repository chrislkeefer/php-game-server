<?php

namespace PHPP\WebService;

use React\Datagram\Socket;

readonly class UdpMessage
{
    public function __construct(
        public array $data, 
        public string $address, 
        public Socket $socket
    ) {
    }
}