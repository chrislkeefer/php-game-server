<?php

/**
 * Simple auto-opponent for local PvP testing.
 * Connects, chases nearest player, moves and shoots.
 *
 * Usage: php scripts/pvp-bot.php [host] [port] [name]
 */

$ip = $argv[1] ?? '127.0.0.1';
$port = (int) ($argv[2] ?? 12345);
$name = $argv[3] ?? 'Bot';

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    die("Could not create socket\n");
}

socket_set_nonblock($socket);

$send = function (array $payload) use ($socket, $ip, $port) {
    $message = json_encode($payload);
    socket_sendto($socket, $message, strlen($message), 0, $ip, $port);
};

$me = null;
$players = [];
$aimDx = 1.0;
$aimDy = 0.0;

echo "Bot '{$name}' connecting to {$ip}:{$port}...\n";
$send(['command' => 'connect', 'name' => $name]);

$lastMove = 0.0;
$lastShoot = 0.0;

while (true) {
    $buffer = '';
    $from = '';
    $fromPort = 0;
    while (@socket_recvfrom($socket, $buffer, 65535, 0, $from, $fromPort) !== false) {
        $data = json_decode($buffer, true);
        if (!is_array($data)) {
            continue;
        }

        if (($data['type'] ?? '') === 'welcome' && isset($data['player'])) {
            $me = $data['player'];
            echo "Connected as {$me['name']} ({$me['id']})\n";
        }

        if (($data['type'] ?? '') === 'state' && isset($data['players'])) {
            $players = $data['players'];
            foreach ($players as $p) {
                if ($me !== null && ($p['id'] ?? '') === ($me['id'] ?? '')) {
                    $me = $p;
                    break;
                }
            }
        }

        if (($data['type'] ?? '') === 'shot' && !empty($data['hit'])) {
            $target = $data['target']['name'] ?? '?';
            $killed = !empty($data['killed']) ? ' (FRAG)' : '';
            echo "Hit {$target}{$killed}\n";
        }
    }

    $now = microtime(true);

    if ($me !== null && ($now - $lastMove) >= 0.05) {
        $lastMove = $now;
        $target = null;
        $bestDist = PHP_FLOAT_MAX;

        foreach ($players as $p) {
            if (($p['id'] ?? '') === ($me['id'] ?? '')) {
                continue;
            }
            $dx = ($p['x'] ?? 0) - ($me['x'] ?? 0);
            $dy = ($p['y'] ?? 0) - ($me['y'] ?? 0);
            $dist = sqrt($dx * $dx + $dy * $dy);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $target = $p;
                if ($dist > 0.001) {
                    $aimDx = $dx / $dist;
                    $aimDy = $dy / $dist;
                }
            }
        }

        if ($target === null) {
            // Wander until an opponent appears
            $aimDx = cos($now);
            $aimDy = sin($now);
            $send(['command' => 'move', 'dx' => $aimDx * 8, 'dy' => $aimDy * 8]);
        } else {
            // Keep some distance, circle-strafe a bit
            $speed = $bestDist > 160 ? 12 : ($bestDist < 80 ? -6 : 4);
            $strafe = sin($now * 2) * 6;
            $mx = $aimDx * $speed - $aimDy * $strafe;
            $my = $aimDy * $speed + $aimDx * $strafe;
            $send(['command' => 'move', 'dx' => $mx, 'dy' => $my]);

            if (($now - $lastShoot) >= 0.25 && $bestDist <= 240) {
                $lastShoot = $now;
                $send(['command' => 'shoot', 'dx' => $aimDx, 'dy' => $aimDy]);
            }
        }
    }

    usleep(10000);
}
