# php-game-server

UDP deathmatch MVP in PHP (ReactPHP datagram + PHP-DI).

Arena is **800×600**. The server broadcasts world state at **20Hz**. Clients talk JSON over UDP on **0.0.0.0:12345**.

## Requirements

- PHP 8.2+ with `sockets` extension
- Composer

## Install & run server

```bash
composer install
php start-gameserver.php
```

You should see:

```text
Game server listening on UDP 0.0.0.0:12345 (tick 20Hz)
```

## Protocol

Client → server:

```json
{"command":"connect","name":"Alice"}
{"command":"move","dx":12,"dy":0}
{"command":"shoot","dx":1,"dy":0}
{"command":"disconnect"}
```

- `move`: `dx`/`dy` are clamped to max speed ~12 per message; position is clamped to the arena.
- `shoot`: hitscan along aim vector, range ~220, hit radius ~18, **Blaster** does **25** damage. On death the victim respawns and K/D updates.

Server → client replies are JSON (`welcome`, `moved`, `shot`, `goodbye`, `error`).

Server also pushes:

```json
{"type":"state","players":[{"id":"...","name":"Alice","x":100,"y":200,"health":100,"kills":0,"deaths":0}]}
```

## Quick smoke test

```bash
php scripts/client-test.php
```

## Human vs bot PvP (local)

Open three terminals:

```bash
# 1) server
php start-gameserver.php

# 2) auto opponent
php scripts/pvp-bot.php 127.0.0.1 12345 Bot

# 3) you
php scripts/human-client.php 127.0.0.1 12345 Human
```

**Human controls:** `W` `A` `S` `D` move, `F` shoot (aims along last move direction), `Q` quit.

The bot chases the nearest player and shoots when in range. Watch the HUD / bot console for frags.

## Architecture notes

- `World` is a DI singleton holding players keyed by UDP address and broadcasting state.
- `Deathmatch` registers gameplay commands (`connect`, `disconnect`, `move`, `shoot`); `GameEngineServiceProvider::commands()` is empty so the mode owns gameplay.
- `GameEngine::run()` opens the UDP socket and starts the 20Hz tick timer on the React event loop.
