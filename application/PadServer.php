<?php
//require dirname(__DIR__) . '/vendor/autoload.php';
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once "config/config.php";
require_once "pad/PadConnectionHandler.php";

$options = $config["server"]["pad"];

print_r($options);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PadConnectionHandler()
        )
    ),
    $config["server"]["pad"]["local_port"], "0.0.0.0", $options
);

$server->run();