<?php

$ip = $argv[1] ?? '127.0.0.1';
$port = (int) ($argv[2] ?? 12345);
$name = $argv[3] ?? 'Tester';

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    die("Could not create socket\n");
}

socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);

$send = function (array $payload) use ($socket, $ip, $port) {
    $message = json_encode($payload);
    socket_sendto($socket, $message, strlen($message), 0, $ip, $port);
};

$recv = function () use ($socket) {
    $buffer = '';
    $from = '';
    $fromPort = 0;
    $bytes = @socket_recvfrom($socket, $buffer, 65535, 0, $from, $fromPort);
    if ($bytes === false) {
        return null;
    }
    return $buffer;
};

echo "Connecting as {$name} to {$ip}:{$port}...\n";
$send(['command' => 'connect', 'name' => $name]);

$response = $recv();
echo $response !== null ? "Reply: {$response}\n" : "No reply (timeout)\n";

// Wait briefly for a few state broadcasts
$deadline = microtime(true) + 1.5;
while (microtime(true) < $deadline) {
    $msg = $recv();
    if ($msg !== null) {
        echo "Push: {$msg}\n";
    }
}

$send(['command' => 'move', 'dx' => 5, 'dy' => 0]);
$moved = $recv();
echo $moved !== null ? "Move: {$moved}\n" : "No move reply\n";

$send(['command' => 'disconnect']);
$bye = $recv();
echo $bye !== null ? "Bye: {$bye}\n" : "No disconnect reply\n";

socket_close($socket);
