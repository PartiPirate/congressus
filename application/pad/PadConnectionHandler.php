<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PadConnectionHandler implements MessageComponentInterface {
    protected $clients;
    protected $padIds;
    protected $nicknames;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->padIds = array();
        $this->nicknames = array();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $conn->send(json_encode(array("rid" => $conn->resourceId, "event" => "connected")));

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        switch($data["event"]) {
            case "attach":
                $this->attach($from, $data);
                break;
            case "synchronize":
                $this->synchronize($from, $data);
                break;
            case "synchronizer":
                $this->synchronizer($from, $msg, $data);
                break;
            default:
                $this->defaultHandle($from, $msg, $data);
                break;
        }
    }
    
    public function defaultHandle(ConnectionInterface $from, $msg, $data) {
        $numRecv = count($this->clients) - 1;

        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, trim($msg), $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                // The pad of the receiver is the same as the sender
                // The sender is not the receiver,
                // send to each client connected
                $client->send($msg);
            }
        }
    }

    public function attach(ConnectionInterface $from, $data) {
        $this->padIds[$from->resourceId] = $data["padId"];
        $this->nicknames[$from->resourceId] = $data["nickname"];

        $this->sendAllConnected($data["padId"]);
    }

    public function synchronizer(ConnectionInterface $from, $msg, $data) {
        foreach ($this->clients as $client) {
            if ($client->resourceId == $data["rid"] && $this->padIds[$client->resourceId] == $data["padId"]) {
                $client->send($msg);
                break;
            }
        }
    }

    public function synchronize(ConnectionInterface $from, $data) {
        // search for the first available client
        foreach ($this->clients as $client) {
            if ($this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                $data["rid"] = $from->resourceId;

                $client->send(json_encode($data));
                break;
            }
        }

        $from->send(json_encode(array("result" => "ko")));
    }

    public function sendAllConnected($padId) {
        $nicknames = array();

        foreach ($this->clients as $client) {
            if (isset($this->padIds[$client->resourceId]) && $this->padIds[$client->resourceId] == $padId) {
                $nicknames[] = $this->nicknames[$client->resourceId];
            }
        }

        $event = array("event" => "nicknames", "nicknames" => $nicknames, "padId" => $padId);
        $event = json_encode($event);

        foreach ($this->clients as $client) {
            if (isset($this->padIds[$client->resourceId]) && $this->padIds[$client->resourceId] == $padId) {
                $client->send($event);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $padId = null;

        if (isset($this->padIds[$conn->resourceId])) {
            $padId = $this->padIds[$conn->resourceId];
            unset($this->padIds[$conn->resourceId]);
            unset($this->nicknames[$conn->resourceId]);
        }

        $this->sendAllConnected($padId);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}