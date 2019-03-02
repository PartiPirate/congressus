<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PadConnectionHandler implements MessageComponentInterface {
    protected $clients;
    protected $padIds;
    protected $nicknames;
    protected $contents;
    protected $carets;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->padIds = array();
        $this->nicknames = array();
        $this->contents = array();
        $this->carets = array();
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
            case "keyup":
                $this->keyup($from, $msg, $data);
                break;
            case "newCaretPosition":
                $this->newCaretPosition($from, $msg, $data);
                break;
            default:
                $this->defaultHandle($from, $msg, $data);
                break;
        }
    }

    public function newCaretPosition(ConnectionInterface $from, $msg, $data) {
        $this->carets[$from->resourceId] = $data["caretPosition"];
        echo "Upgrade newCaretPosition position : " . $this->carets[$from->resourceId] . "\n";
    }

    public function keyup(ConnectionInterface $from, $msg, $data) {
/*        
        if (isset($data["keyCode"])) {
            switch($data["keyCode"]) {
                case 33:
                case 34:
                case 35:
                case 36:
                case 37:
                case 38:
                case 39:
                case 40:
                    $this->carets[$from->resourceId] = $data["caretPositionAfter"];
                    echo "Upgrade caret position : " . $this->carets[$from->resourceId] . "\n";
                    
                    // send everyone else the new position
                    
                    return;
                case 16 : // Shift
                case 17 : // Control
                case 18 : // Alt
                case 225: // AltGraph
                    // alt chars
                    return;
            }
        }
*/
        $caretBefore = $this->carets[$from->resourceId];
        $this->carets[$from->resourceId] = $data["caretPositionAfter"];
        $caretAfter  = $this->carets[$from->resourceId];

        $numberOfChars = $caretAfter - $caretBefore;

        print_r($data);
        echo "\n";

        if ($data["keyCode"] == 13) {
            $contentBeforeCaret = mb_substr($this->contents[$data["padId"]], 0, $caretBefore);
            $contentAfterCaret = mb_substr($this->contents[$data["padId"]], $caretBefore);
            
            $content  = $contentBeforeCaret;
            $content .= "\n";
            $content .= $contentAfterCaret;

            $numberOfChars = 1;
            $this->carets[$from->resourceId] = $data["caretPositionAfter"] + 1;
            $caretAfter  = $this->carets[$from->resourceId];

            $this->contents[$data["padId"]] = $content;
            echo "=> $content \n";
        }
        else if ($data["keyCode"] == 46) {
            $contentBeforeCaret = mb_substr($this->contents[$data["padId"]], 0, $caretBefore);
            $contentAfterCaret = mb_substr($this->contents[$data["padId"]], $caretBefore + $data["numberOfDeletedCharacters"]);

            $numberOfChars = -$data["numberOfDeletedCharacters"];

            echo "$contentBeforeCaret <=> $contentAfterCaret \n";

            $content  = $contentBeforeCaret;
            $content .= $contentAfterCaret;
    
            $this->contents[$data["padId"]] = $content;
            echo "=> $content \n";
        }
        else if ($numberOfChars < 0 && $data["keyCode"] == 8) {
            $contentBeforeCaret = mb_substr($this->contents[$data["padId"]], 0, $caretAfter);
            $contentAfterCaret = mb_substr($this->contents[$data["padId"]], $caretBefore);

            echo "$contentBeforeCaret <=> $contentAfterCaret \n";

            $content  = $contentBeforeCaret;
            $content .= $contentAfterCaret;
    
            $this->contents[$data["padId"]] = $content;
            echo "=> $content \n";
        }
        else {
            $char = $data["key"];
    
            echo $numberOfChars . " x " . $char . "\n";

            $contentBeforeCaret = mb_substr($this->contents[$data["padId"]], 0, $caretBefore);
            $contentAfterCaret = mb_substr($this->contents[$data["padId"]], $caretBefore);

            if ($numberOfChars == 0) {
                $numberOfChars++;
                $contentBeforeCaret = mb_substr($contentBeforeCaret, 0, mb_strlen($contentBeforeCaret) - 1);
            }

            echo "$contentBeforeCaret <=> $contentAfterCaret \n";
    
            $content  = $contentBeforeCaret;
            for($index = 0; $index < $numberOfChars; $index++) {
                $content .= $char;
            }
            $content .= $contentAfterCaret;
    
            $this->contents[$data["padId"]] = $content;
            echo "=> $content \n";
        }

        // upgrade others carets
        echo "Caret positions : $caretAfter";
        foreach ($this->clients as $client) {
            if ($this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                if ($this->carets[$client->resourceId] > $caretBefore) {
                    $this->carets[$client->resourceId] += $numberOfChars;
                    echo ", " . $this->carets[$client->resourceId];
                }
            }
        }
        echo "\n";

        foreach ($this->clients as $client) {
            if ($this->padIds[$client->resourceId] == $data["padId"]) {// && $from !== $client) {
                $event = array();
                $event["event"] = "keyup";
                $event["padId"] = $data["padId"];
//                $event["content"] = str_replace("\n", "<br>", $content);
                $event["content"] = $content;
                $event["caretPosition"] = $this->carets[$client->resourceId];

                $client->send(json_encode($event));
            }
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
        $this->carets[$from->resourceId] = 0;

        $this->sendAllConnected($data["padId"]);
    }

    public function synchronizer(ConnectionInterface $from, $msg, $data) {
        $this->contents[$data["padId"]] = str_replace("<br>", "\n", $data["content"]);

        foreach ($this->clients as $client) {
            if ($client->resourceId == $data["rid"] && $this->padIds[$client->resourceId] == $data["padId"]) {
                $client->send($msg);
                break;
            }
        }
    }

    public function synchronize(ConnectionInterface $from, $data) {

/*
        if (!isset($this->contents[$data["padId"]])) {
            $this->contents[$data["padId"]] = $data["content"];
            echo "Set {$data['padId']} content:\n{$data['content']}\n";
        }
*/
        // search for the first available client
        foreach ($this->clients as $client) {
            if (isset($this->padIds[$client->resourceId]) && $this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                $data["rid"] = $from->resourceId;

                $client->send(json_encode($data));
                return;
            }
        }

        $this->contents[$data["padId"]] = str_replace("<br>", "\n", $data["content"]);
        echo "Set {$data['padId']} content:\n{$data['content']}\n";

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
            unset($this->carets[$conn->resourceId]);
        }

        $this->sendAllConnected($padId);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}