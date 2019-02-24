<?php
//require dirname(__DIR__) . '/vendor/autoload.php';
require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require_once "pad/PadConnectionHandler.php";


    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new PadConnectionHandler()
            )
        ),
        8080
    );

    $server->run();