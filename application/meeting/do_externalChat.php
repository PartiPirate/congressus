<?php /*
	Copyright 2017 Cédric Levieux, Parti Pirate

	This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If age, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

require_once("config/database.php");
require_once("config/memcache.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/PingBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/utils/DateTimeUtils.php");
require_once("engine/utils/EventStackUtils.php");

$data = array();

if (!testTokens()) {
	$data["ko"] = "ko";
	$data["message"] = "bad_tokens";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit;
}

$connection = openConnection();

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

// FROM DATA
$message = $_GET["message"];
$messageId = $_GET["messageId"];
$channel = $_GET["channel"];
$externalUser = $_GET["user"];

if (isset($_GET["avatar"])) {
    $avatar = $_GET["avatar"];
}
else {
    $avatar = "assets/images/no_avatar.svg";
}

// FOUND DATA
$meetingBo = MeetingBo::newInstance($connection, $config);
$pingBo = PingBo::newInstance($connection, $config);

$meetings = $meetingBo->getByFilters(array("loc_text_channel" => $channel, "with_status" => array("waiting","open")));

//print_r($meetings);

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);

$userSource = UserSourceFactory::getInstance($config["modules"]["usersource"]);
$userSource->selectQuery($queryBuilder, $config);

$queryBuilder->where("diaf.field_val = :discord_id_adh");

$query = $queryBuilder->constructRequest();
$statement = $connection->prepare($query);

$statement->execute(array("discord_id_adh" => $externalUser));
$results = $statement->fetchAll();

if (count($results)) {
    $user = $results[0];
}
else {
    $user = array("id_adh" => 0, "pseudo_adh" => "$externalUser", "nom_adh" => "", "prenom_adh" => "");
}

$data["ok"] = "ok";

$options = array("user" => array("id_adh" => $user["id_adh"]), "message" => $message, "message_id" => $messageId);
$options["user"]["mem_nickname"] = htmlspecialchars(utf8_encode($user["pseudo_adh"] ? $user["pseudo_adh"] : $user["nom_adh"] . ' ' . $user["prenom_adh"]), ENT_SUBSTITUTE);
$options["user"]["mem_avatar_url"] = $avatar;

$message = $options["user"]["mem_nickname"] . " : " . $message;

foreach($meetings as $meeting) {
    addEvent($meeting[$meetingBo->ID_FIELD], EVENT_EXTERNAL_CHAT, $message, $options);

    if ($user["id_adh"]) {
        $ping = array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]);
    	$ping["pin_member_id"] = $user["id_adh"];

        $now = getNow();
        $ping["pin_datetime"] = $now->format("Y-m-d H:i:s");
        
        $previousPings = $pingBo->getByFilters($ping);
        
        if (count($previousPings)) {
        	$ping[$pingBo->ID_FIELD] = $previousPings[0][$pingBo->ID_FIELD];
        	$ping["pin_speaking_request"] = $previousPings[0]["pin_speaking_request"];
        }

        switch(strtolower($options["message"])) {
            case "o/":
            case "0/":
                // ask to speach
                if (!$ping["pin_speaking_request"]) {
                	$ping["pin_speaking_request"] = time();
                	addEvent($meeting[$meetingBo->ID_FIELD], EVENT_SPEAK_REQUEST, "", array("userId" => $ping["pin_member_id"]));
                }
                break;
            case "o\\":
            case "0\\":
                // ask to unspeach
                if ($ping["pin_speaking_request"]) {
                	$ping["pin_speaking_request"] = 0;
                	addEvent($meeting[$meetingBo->ID_FIELD], EVENT_SPEAK_RENOUNCE, "", array("userId" => $ping["pin_member_id"]));
                }
                break;
        }

        print_r($ping);

        $pingBo->save($ping);
    }
}

echo json_encode($data, JSON_NUMERIC_CHECK);
?>