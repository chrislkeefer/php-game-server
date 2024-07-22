<?php

namespace PHPP\WebService;

use React\Datagram\Factory as DatagramFactory;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

class UdpServerFactory
{
    public static function create(callable $onFulfilled, callable $onRejected): LoopInterface
    {
        $loop = Loop::get();

        $factory = new DatagramFactory($loop);

        $factory->createServer('0.0.0.0:12345')->then($onFulfilled, $onRejected);

        return $loop;
    }
}
