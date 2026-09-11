<?php

/**
 * Terminal human client: WASD move, F shoot (aims along last move direction).
 * Q quits (sends disconnect).
 *
 * Usage: php scripts/human-client.php [host] [port] [name]
 */

$ip = $argv[1] ?? '127.0.0.1';
$port = (int) ($argv[2] ?? 12345);
$name = $argv[3] ?? 'Human';

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    die("Could not create socket\n");
}

socket_set_nonblock($socket);

$send = function (array $payload) use ($socket, $ip, $port) {
    $message = json_encode($payload);
    socket_sendto($socket, $message, strlen($message), 0, $ip, $port);
};

$stdin = fopen('php://stdin', 'r');
stream_set_blocking($stdin, false);

$isWindows = stripos(PHP_OS, 'WIN') !== false;
$sttyMode = null;
if (!$isWindows && function_exists('shell_exec')) {
    $sttyMode = shell_exec('stty -g');
    shell_exec('stty -icanon -echo');
}

$me = null;
$players = [];
$aimDx = 1.0;
$aimDy = 0.0;
$lastHud = 0.0;

echo "Human client '{$name}' → {$ip}:{$port}\n";
echo "Controls: W/A/S/D move, F shoot, Q quit\n";
$send(['command' => 'connect', 'name' => $name]);

$running = true;
while ($running) {
    $buffer = '';
    $from = '';
    $fromPort = 0;
    while (@socket_recvfrom($socket, $buffer, 65535, 0, $from, $fromPort) !== false) {
        $data = json_decode($buffer, true);
        if (!is_array($data)) {
            continue;
        }

        $type = $data['type'] ?? '';
        if ($type === 'welcome' && isset($data['player'])) {
            $me = $data['player'];
            echo "Connected. id={$me['id']}\n";
        } elseif ($type === 'state' && isset($data['players'])) {
            $players = $data['players'];
            foreach ($players as $p) {
                if ($me !== null && ($p['id'] ?? '') === ($me['id'] ?? '')) {
                    $me = $p;
                    break;
                }
            }
        } elseif ($type === 'shot') {
            if (!empty($data['hit'])) {
                $target = $data['target']['name'] ?? '?';
                $frag = !empty($data['killed']) ? ' FRAG!' : '';
                echo "\nHit {$target}{$frag}\n";
            }
        } elseif ($type === 'error') {
            echo "\nError: " . ($data['message'] ?? 'unknown') . "\n";
        }
    }

    $key = fread($stdin, 1);
    if ($key !== false && $key !== '') {
        $key = strtolower($key);
        $dx = 0.0;
        $dy = 0.0;
        $moved = false;

        if ($key === 'w') {
            $dy = -12;
            $moved = true;
        } elseif ($key === 's') {
            $dy = 12;
            $moved = true;
        } elseif ($key === 'a') {
            $dx = -12;
            $moved = true;
        } elseif ($key === 'd') {
            $dx = 12;
            $moved = true;
        } elseif ($key === 'f') {
            $send(['command' => 'shoot', 'dx' => $aimDx, 'dy' => $aimDy]);
        } elseif ($key === 'q') {
            $send(['command' => 'disconnect']);
            $running = false;
        }

        if ($moved) {
            $len = sqrt($dx * $dx + $dy * $dy);
            if ($len > 0.001) {
                $aimDx = $dx / $len;
                $aimDy = $dy / $len;
            }
            $send(['command' => 'move', 'dx' => $dx, 'dy' => $dy]);
        }
    }

    $now = microtime(true);
    if ($me !== null && ($now - $lastHud) >= 0.25) {
        $lastHud = $now;
        $others = array_filter(
            $players,
            fn ($p) => ($p['id'] ?? '') !== ($me['id'] ?? '')
        );
        $scoreboard = implode(' | ', array_map(
            fn ($p) => sprintf('%s %d/%d HP:%d', $p['name'], $p['kills'], $p['deaths'], $p['health']),
            $players
        ));
        printf(
            "\rYou @ (%.0f,%.0f) HP:%d K/D:%d/%d  opponents:%d  [%s]   ",
            $me['x'],
            $me['y'],
            $me['health'],
            $me['kills'],
            $me['deaths'],
            count($others),
            $scoreboard
        );
    }

    usleep(10000);
}

echo "\nDisconnected.\n";

if ($sttyMode !== null) {
    shell_exec('stty ' . escapeshellarg(trim($sttyMode)));
}

fclose($stdin);
socket_close($socket);
