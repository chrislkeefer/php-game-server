<?php

namespace PHPP\WebService;

class UdpMessageResponder
{
    private UdpMessage $udpMessage;

    public function __construct()
    {
    }

    public function setUdpMessage(UdpMessage $message): self
    {
        $this->udpMessage = $message;
        return $this;
    }

    public function send(string $response): void
    {
        $this->udpMessage->socket->send(
            $response,
            $this->udpMessage->address
        );
    }
}
