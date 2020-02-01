<?php

require 'engine/utils/SimpleDiff.php';
require 'engine/utils/Merger.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WsConnectionHandler implements MessageComponentInterface {
    const WAITING = "waiting";
    const CLOSING_FRAME = "closing_frame";
    const PROCESSING = "processing";

    public $clients;
    protected $pads;
    public $padIds;
    protected $nicknames;
    protected $contents;
    protected $carets;


    public $meetings = array();

    protected $state = WsConnectionHandler::WAITING;

    public function __construct() {
        $this->pads = new \SplObjectStorage;
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
            case "diff":
                $this->diff($from, $msg, $data);
                break;
            case "process":
                $this->process($from, $msg, $data);
                break;
            case "m_attach":
                $this->m_attach($from, $data);
                break;
            case "m_ping":
                $this->m_ping($from, $data);
                break;
            case "m_computeVote":
                $this->m_computeVote($from, $data);
                break;
            case "m_vote":
                $this->m_vote($from, $data);
                break;
            case "m_getAgendaPoint":
                $this->m_getAgendaPoint($from, $data);
                break;
            case "m_getAgenda":
                $this->m_getAgenda($from, $data);
                break;
            case "m_getEvents":
                $this->m_getEvents($from, $data);
                break;
            case "m_getPeople":
                $this->m_getPeople($from, $data);
                break;
            default:
                $this->defaultHandle($from, $msg, $data);
                break;
        }
    }
    
    public function diff(ConnectionInterface $from, $msg, $data) {
        echo "Receive diff \n";
        
        $pad = $this->getPad($data["padId"]);
        if ($pad->state == WsConnectionHandler::WAITING) {
            $pad->state = WsConnectionHandler::CLOSING_FRAME;
            
            $event = array("event" => "closingTimer", "timer" => "500");

            foreach ($this->clients as $client) {
                if ($this->padIds[$client->resourceId] == $data["padId"]) {// && $from !== $client) {
                    $from->send(json_encode($event));
                }
            }
        }

        $composer = new Composer();
        $composer->revision = $pad->seed++;
        $composer->sender = $data["senderId"];
        $composer->content = $data["content"];

        $pad->composers[$composer->sender] = $composer;

/*
//        print_r($data["diff"]);

        $content = $this->contents[$data["padId"]];
        $newContent = "";
        $offset = 0;

        foreach($data["diff"] as $localDiff) {
            foreach($localDiff[1] as $word) {

                $wordLength = mb_strlen($word);

                if ($localDiff[0] == "=") {
                    if (mb_substr($content, $offset, $wordLength)) {
                        $newContent .= $word;
                        $offset += $wordLength;
                    }
                }
                else if ($localDiff[0] == "-") {
                    $offset += $wordLength;

                    foreach ($this->clients as $client) {
                        if ($this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                            if ($this->carets[$client->resourceId] > $offset) {
                                $this->carets[$client->resourceId] -= $wordLength;
                            }
                        }
                    }

                }
                else if ($localDiff[0] == "+") {
                    $newContent .= $word;

                    foreach ($this->clients as $client) {
                        if ($this->padIds[$client->resourceId] == $data["padId"] && $from !== $client) {
                            if ($this->carets[$client->resourceId] > $offset) {
                                $this->carets[$client->resourceId] += $wordLength;
                            }
                        }
                    }
                }
            }
        }

//        echo $newContent, "\n";

        $this->contents[$data["padId"]] = $newContent;
        $this->newCaretPosition($from, $msg, $data);
        foreach ($this->clients as $client) {
            if ($this->padIds[$client->resourceId] == $data["padId"]) {// && $from !== $client) {
                $event = array();
                $event["event"] = "diff";
                $event["padId"] = $data["padId"];
                $event["content"] = $this->contents[$data["padId"]];
                $event["caretPosition"] = $this->carets[$client->resourceId];
                $client->send(json_encode($event));
            }
        }
*/
    }

    public function process(ConnectionInterface $from, $msg, $data) {
        $padId = $data["padId"];
        $pad = $this->getPad($padId);

/*
        // Send all connections that we are processing
        $this->state = WsConnectionHandler::PROCESSING;
*/

        if ($pad->state == WsConnectionHandler::CLOSING_FRAME) {
            // Do stuff
            echo "Processing $padId \n";
            $pad->process($this);
        }

/*        
        // Send all connections that we are ready to listen again
        $this->state = WsConnectionHandler::WAITING;
*/

        echo "Waiting \n";
    }

    public function newCaretPosition(ConnectionInterface $from, $msg, $data) {
        $this->carets[$from->resourceId] = $data["caretPosition"];
//        echo "Upgrade newCaretPosition position : " . $this->carets[$from->resourceId] . "\n";
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

            if ($numberOfChars == 2) {
                $numberOfChars = 1;
                $this->carets[$from->resourceId] = $data["caretPositionAfter"] - 1;
                $caretAfter  = $this->carets[$from->resourceId];
            }
            if ($numberOfChars == 0) {
                $numberOfChars++;
                $this->carets[$from->resourceId] = $data["caretPositionAfter"] + 1;
                $caretAfter  = $this->carets[$from->resourceId];
//                $contentBeforeCaret = mb_substr($contentBeforeCaret, 0, mb_strlen($contentBeforeCaret) - 1);
            }

            echo $numberOfChars . " x " . $char . "\n";

            $contentBeforeCaret = mb_substr($this->contents[$data["padId"]], 0, $caretBefore);
            $contentAfterCaret = mb_substr($this->contents[$data["padId"]], $caretBefore);
/*
            if ($numberOfChars == 0) {
                $numberOfChars++;
                $contentBeforeCaret = mb_substr($contentBeforeCaret, 0, mb_strlen($contentBeforeCaret) - 1);
            }
*/
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

                if ($from == $client) {
                    print_r($event);
                    echo "\n";
                }

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

    public function getMeetingApi() {
        require_once("language/language.php");
        require_once("engine/utils/SessionUtils.php");
        require_once("engine/utils/DateTimeUtils.php");
        require_once("engine/utils/FormUtils.php");
        require_once("engine/utils/GamifierClient.php");
        require_once("engine/utils/MeetingAPI.php");
        require_once("engine/utils/EventStackUtils.php");
        require_once("engine/utils/QuorumUtils.php");
        require_once("engine/bo/AgendaBo.php");
        require_once("engine/bo/ChatBo.php");
        require_once("engine/bo/ChatAdviceBo.php");
        require_once("engine/bo/ConclusionBo.php");
        require_once("engine/bo/GaletteBo.php");
        require_once("engine/bo/GameEvents.php");
        require_once("engine/bo/LocationBo.php");
        require_once("engine/bo/MeetingBo.php");
        require_once("engine/bo/MeetingRightBo.php");
        require_once("engine/bo/MotionBo.php");
        require_once("engine/bo/NoticeBo.php");
        require_once("engine/bo/PingBo.php");
        require_once("engine/bo/TagBo.php");
        require_once("engine/bo/TaskBo.php");
        require_once("engine/bo/VoteBo.php");

        global $config;

        $connection = openConnection();
        $api = new MeetingAPI($connection, $config);

        return $api;        
    }

    public function m_attach(ConnectionInterface $from, $data) {
        if (!isset($this->meetings[$data["meetingId"]])) {
            $this->meetings[$data["meetingId"]] = array("users" => array());
        }

        $this->meetings[$data["meetingId"]]["users"][$data["userId"]]= array("cnx" => $from, "lastCalls" => array("m_computeVote" => array(), "m_getAgenda" => null, "m_getAgendaPoint" => null, "m_getEvents" => null, "m_vote" => null, "m_getPeople" => null));

//        $api = $this->getMeetingApi();
//        $response = $api->ping($data["meetingId"], $data["userId"]);

        echo "Connection on ".$data["meetingId"]." from ".$data["userId"]."\n";

//        echo json_encode($response);
//        echo "\n";
    }

    public function m_computeVote(ConnectionInterface $from, $data) {
        $motionId = $data["motionId"];
        $save = $data["save"];

        $force = $data["force"];

        echo "Force compute $force \n";

        foreach($this->meetings as $meetingId => $meeting) {
            $connectUserId = null;
            foreach($meeting["users"] as $userId => $connection) {
                if ($connection["cnx"] === $from) {
                    $connectUserId = $userId;

                    $api = $this->getMeetingApi();
                    $response = $api->computeVote($motionId, $save);
                    $responseHash = sha1(json_encode($response));

//                    echo "Computed Hash : " . $responseHash . "\n";
//                    echo "Previous Hash : " . $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_computeVote"] . "\n";

                    if ($force || !isset($this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_computeVote"][$motionId]) || ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_computeVote"][$motionId])) {
                        $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_computeVote"][$motionId] = $responseHash;

                        $response["HASH"] = $responseHash;
                        $response["EVENT_ID"] = $data["EVENT_ID"];
                        $response["EVENT_SRC"] = $data["event"];
                
                        echo "Compute vote on ".$data["motionId"]."\n";
                
                        $from->send(json_encode($response));
                    }
                    break;
                }
            }

            if ($connectUserId) {
                // It's fine
                break;
            }
        }
    }

    public function m_vote(ConnectionInterface $from, $data) {
        echo "Vote on ".$data["motionId"]."\n";

        $motionId = $data["motionId"];
        $propositionId = $data["propositionId"];
        $votePower = $data["power"];

        foreach($this->meetings as $meetingId => $meeting) {
            $connectUserId = null;
            foreach($meeting["users"] as $userId => $connection) {
                if ($connection["cnx"] === $from) {
                    $connectUserId = $userId;

                    $api = $this->getMeetingApi();
                    $response = $api->vote($motionId, $propositionId, $connectUserId, $votePower);
                    $responseHash = sha1(json_encode($response));

                    if ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_vote"]) {
                        $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_vote"] = $responseHash;
    
                        $response["HASH"] = $responseHash;
                        $response["EVENT_ID"] = $data["EVENT_ID"];
                        $response["EVENT_SRC"] = $data["event"];
    
                        $from->send(json_encode($response));
                    }
                    break;
                }
            }

            if ($connectUserId) {
                $push = array("event" => "motion", "motionId" => $motionId);

                foreach($meeting["users"] as $userId => $connection) {
                    $connection["cnx"]->send(json_encode($push));
                }

                // It's fine
                break;
            }
        }
    }

    public function m_getEvents(ConnectionInterface $from, $data) {
        $meetingId = $data["meetingId"];

        foreach($this->meetings[$meetingId]["users"] as $userId => $connection) {
            if ($connection["cnx"] === $from) {
                $connectUserId = $userId;

                $api = $this->getMeetingApi();
                $response = $api->getEvents($meetingId);

                $timestamp = $response["timestamp"];
                
                unset($response["timestamp"]);
                $responseHash = sha1(json_encode($response));
                $response["timestamp"] = $timestamp;

                if ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getEvents"]) {
                    $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getEvents"] = $responseHash;

                    $response["HASH"] = $responseHash;
                    $response["EVENT_ID"] = $data["EVENT_ID"];
                    $response["EVENT_SRC"] = $data["event"];
    
                    $from->send(json_encode($response));
                }
                break;
            }
        }
    }

    public function m_getPeople(ConnectionInterface $from, $data) {
        $meetingId = $data["id"];

        foreach($this->meetings[$meetingId]["users"] as $userId => $connection) {
            if ($connection["cnx"] === $from) {
                $connectUserId = $userId;

                $api = $this->getMeetingApi();
                $response = $api->getPeople($meetingId, $connectUserId);
                $responseHash = sha1(json_encode($response));

                if ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getPeople"]) {
                    $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getPeople"] = $responseHash;

                    $response["HASH"] = $responseHash;
                    $response["EVENT_ID"] = $data["EVENT_ID"];
                    $response["EVENT_SRC"] = $data["event"];
    
                    $from->send(json_encode($response));
                }
                break;
            }
        }
    }

    public function m_getAgenda(ConnectionInterface $from, $data) {
        $meetingId = $data["id"];

        foreach($this->meetings[$meetingId]["users"] as $userId => $connection) {
            if ($connection["cnx"] === $from) {
                $connectUserId = $userId;

                $api = $this->getMeetingApi();
                $response = $api->getAgenda($meetingId);
                $responseHash = sha1(json_encode($response));

                if ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getAgenda"]) {
                    $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getAgenda"] = $responseHash;

                    $response["HASH"] = $responseHash;
                    $response["EVENT_ID"] = $data["EVENT_ID"];
                    $response["EVENT_SRC"] = $data["event"];
    
                    $from->send(json_encode($response));
                }
                break;
            }
        }
    }

    public function m_getAgendaPoint(ConnectionInterface $from, $data) {
        $meetingId = $data["id"];
        $pointId = $data["pointId"];
        $requestId = $data["requestId"];

// error_log(print_r($_REQUEST, true));

        foreach($this->meetings[$meetingId]["users"] as $userId => $connection) {
            if ($connection["cnx"] === $from) {
                $connectUserId = $userId;

                $api = $this->getMeetingApi();
                $response = $api->getAgendaPoint($meetingId, $pointId, $connectUserId, $requestId);

                unset($response["requestId"]);
                $responseHash = sha1(json_encode($response));
                $response["requestId"] = $data["requestId"];

                if ($responseHash != $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getAgendaPoint"]) {
                    $this->meetings[$meetingId]["users"][$userId]["lastCalls"]["m_getAgendaPoint"] = $responseHash;

                    $response["HASH"] = $responseHash;
                    $response["EVENT_ID"] = $data["EVENT_ID"];
                    $response["EVENT_SRC"] = $data["event"];
    
                    $from->send(json_encode($response));
                }
                break;
            }
        }
    }

    public function m_ping(ConnectionInterface $from, $data) {
        $meetingId = $data["id"];
        foreach($this->meetings as $meetingId => $meeting) {
            $connectUserId = null;
            foreach($meeting["users"] as $userId => $connection) {
                if ($connection["cnx"] === $from) {
                    $connectUserId = $userId;
                    $guestId = null;
                    $guestName = null;
            
                    if (strpos("$userId", "G") !== false) {
                        $guestId = str_replace("G", "", "$userId");
                        $userId = null;
                    }
            
                    $api = $this->getMeetingApi();
                    $response = $api->ping($meetingId, $userId, $guestId, $guestName);
            
                    $response["EVENT_ID"] = $data["EVENT_ID"];
                    $response["EVENT_SRC"] = $data["event"];
            
                    echo "Ping on ".$data["id"]." from ".$connectUserId."\n";
            
                    echo json_encode($response);
                    echo "\n";
            
                    $from->send(json_encode($response));
                    break;
                }
            }
            
            if ($connectUserId) {
                break;
            }
        }
    }

    public function attach(ConnectionInterface $from, $data) {
        $pad = $this->getPad($data["padId"]);

        if (!$pad) {
            $pad = new Pad();
            $pad->id = $data["padId"];
            $this->pads->attach($pad);
        }

        $this->padIds[$from->resourceId] = $data["padId"];
        $this->nicknames[$from->resourceId] = $data["nickname"];
        $this->carets[$from->resourceId] = 0;

        $this->sendAllConnected($data["padId"]);
    }

    public function getPad($padId) {
        foreach($this->pads as $currentPad) {
            if ($currentPad->id == $padId) {
                return $currentPad;
            }
        }

        return null;
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

/*
class ClosingThread extends Thread {

    private $padId = null;
    private $padConnectionHandler = null;

    public function __construct($padId, $padConnectionHandler){
        $this->padId = $padId;
        $this->padConnectionHandler = $padConnectionHandler;
    }
    
    public function run(){
        // Send all connections that we are closing
        echo "Closing in 0.5s \n";

        usleep(2000000); // sleep during 1/2 second
        $padConnectionHandler->process($this->padId);
    }    
}
*/

class Composer {
    public $revision;
    public $content;
    public $sender;
}

class Pad {
    public $id;
    public $start;
    public $head;
    public $state = WsConnectionHandler::WAITING;
    
    /**
     * Array of <code>Composer</code>
     */
    public $composers = array();
    
    public $steps = array();
    
    public $seed = 0;

    public function sortComposers($a, $b) {
        return $a->revision - $b->revision;
    }
    
    public function process($handler) {
        if ($this->state != WsConnectionHandler::CLOSING_FRAME) {
            // another process is occuring or occured
            return;
        }

        echo "[START] Inner process $this->id \n";
        $this->state = WsConnectionHandler::PROCESSING;

        $event = json_encode(array("event" => "processing"));
        foreach($handler->clients as $client) {
            if ($handler->padIds[$client->resourceId] == $this->id) {
                $client->send($event);
            }
        }

        $this->steps[] = $this->composers;

        usort($this->composers, array($this, "sortComposers"));

        $mergers = array();
        foreach($this->composers as $composer) {
            $mergers[] = $composer->content;
        }

        $this->composers = array();

        $this->head = merge($this->head, $mergers);

        $this->state = WsConnectionHandler::WAITING;

        $event = json_encode(array("event" => "waiting", "mergedContent" => $this->head));
        foreach($handler->clients as $client) {
            if ($handler->padIds[$client->resourceId] == $this->id) {
                $client->send($event);
            }
        }

        echo " [END]  Inner process $this->id \n";
    }
}
