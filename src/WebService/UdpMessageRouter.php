<?php

namespace PHPP\WebService;

use DI\Container;
use InvalidArgumentException;

class UdpMessageRouter
{
    private $handlers = [];

    public function __construct(
        private Container $container,
        private UdpMessageResponder $udpMessageResponder,
    ) {
       
    }

    public function registerCommandFQCN(array $commands): void
    {
        foreach ($commands as $fqcn) {
            $this->registerHandlerClass($fqcn);
        }
    }

    public function registerHandlerClass(string $handlerClass): void
    {
        if (!class_exists($handlerClass) || !in_array(MessageHandlerInterface::class, class_implements($handlerClass))) {
            throw new InvalidArgumentException("Handler class must implement MessageHandlerInterface");
        }

        $commandName = $handlerClass::getCommandName();
        $this->handlers[$commandName] = $handlerClass;
    }

    public function registerHandler($command, callable $handler)
    {
        $this->handlers[$command] = $handler;
    }

    public function route($message, $address, $server)
    {
        $data = json_decode($message, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Received invalid message format: $message\n";
            return;
        }

        if (
            isset($data['command']) &&
            isset($this->handlers[$data['command']])
        ) {
            $handlerClass = $this->handlers[$data['command']];
            $handler = $this->container->get($handlerClass);
            $message = new UdpMessage($data, $address, $server);
            $this->udpMessageResponder->setUdpMessage($message);
            $handler->handle($message);
        } else {
            echo "No handler registered for command: " . ($data['command'] ?? 'unknown') . "\n";
        }
    }
}
