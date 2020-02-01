<?php
//require dirname(__DIR__) . '/vendor/autoload.php';
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once("config/database.php");
require_once("config/memcache.php");
require_once("config/mail.php");
require_once "pad/WsConnectionHandler.php";

$options = $config["server"]["pad"];

print_r($options);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WsConnectionHandler()
        )
    ),
    $config["server"]["pad"]["local_port"], "0.0.0.0", $options
);

$server->run();