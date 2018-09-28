<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("language/language.php");

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);

$filters = array("with_principal_location" => true, "with_status" => array("waiting", "open", "closed"));

$timezone = null;
if ($config["server"]["timezone"]) {
	$timezone = new DateTimeZone($config["server"]["timezone"]);
}

if (isset($_REQUEST["from"])) {
	$from = $_REQUEST["from"];
	$from /= 1000;
	$from -= 86400 * 7;  // seems too short for some requests
	$fromDate = new DateTime();
	$fromDate->setTimezone($timezone);
	$fromDate->setTimestamp($from);
	$from = $fromDate->format("Y-m-d");
	
	$filters["mee_from"] = $from;
}

if (isset($_REQUEST["to"])) {
	$to = $_REQUEST["to"];
	$to /= 1000;
	$to += 86400 * 7;  // seems too short for some requests
	$toDate = new DateTime();
	$toDate->setTimezone($timezone);
	$toDate->setTimestamp($to);
	$to = $toDate->format("Y-m-d");
	
	$filters["mee_to"] = $to;
}

$meetings = $meetingBo->getByFilters($filters);

//print_r($meetings);

$data = array();

$events = array();

$timezone = null;
if ($config["server"]["timezone"]) {
	$timezone = new DateTimeZone($config["server"]["timezone"]);
}

//$dateFormatter = new IntlDateFormatter("fr", IntlDateFormatter::FULL, IntlDateFormatter::FULL, $timezone, null);

foreach($meetings as $meeting) {

	$start = new DateTime($meeting["mee_datetime"], $timezone);
	$startDatetime = $start;
	$start = $start->getTimestamp();
	$end = $start + 60 * ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60);

	$event = array(
			'id' => $meeting[$meetingBo->ID_FIELD],
			'title' => str_replace("\"", "&quot;", $meeting["mee_label"]),
			'titleWithLink' => str_replace("\"", "&quot;", $meeting["mee_label"]),
			'url' => $config["server"]["base"] . "meeting.php?id=" . $meeting[$meetingBo->ID_FIELD],
			'class' => $meeting["mee_class"],
			'start' => $start . '000',
			'end' => $end . '000',
			'meetingTitle' => str_replace("\"", "&quot;", $meeting["mee_label"]),
			'meetingDatetime' => dateTranslate($startDatetime->format('\L\e l j F Y \à H:i'))
//			'meetingDatetime' => $dateFormatter->formatObject($startDatetime)
	);

	if ($meeting["loc_type"]) {
		$event["title"] .= " - " . lang("loc_type_" . $meeting["loc_type"]);
		$event["titleWithLink"] .= " - " . lang("loc_type_" . $meeting["loc_type"]);

		$event["location"]["type"] = lang("loc_type_" . $meeting["loc_type"]);

		if ($meeting["loc_extra"] && $meeting["loc_type"] != "discord") {
			$event["titleWithLink"] .= " (" . $meeting["loc_extra"] . ")";
			$event["location"]["extra"] = $meeting["loc_extra"];
		}
		else if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {
			require_once("config/discord.structure.php");

			list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);

			$discord_text_link = @$discord_text_channels[$discord_text_channel];
			$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];

			if ($discord_text_link || $discord_vocal_link) {
				$event["titleWithLink"] .= " (";
				if ($discord_text_link) $event["titleWithLink"] .= "<i class='fa fa-hashtag' aria-hidden='true'></i> <a href='$discord_text_link' target='_blank'>$discord_text_channel</a> ";
				if ($discord_vocal_link) $event["titleWithLink"] .= "<i class='fa fa-volume-up' aria-hidden='true'></i> <a href='$discord_vocal_link' target='_blank'>$discord_vocal_channel</a>";
				$event["titleWithLink"] .= ")";
				$event["location"]["discord"]["vocal"]["link"] = $discord_vocal_link;
				$event["location"]["discord"]["vocal"]["title"] = $discord_vocal_channel;
				$event["location"]["discord"]["text"]["link"] = $discord_text_link;
				$event["location"]["discord"]["text"]["title"] = $discord_text_channel;
			}
		}

		$event["title"] .= " - " . dateTranslate($startDatetime->format('\L\e l j F Y \à H:i'));
		$event["titleWithLink"] .= " - " . dateTranslate($startDatetime->format('\L\e l j F Y \à H:i'));
	}

	$events[] = $event;
}

$data["ok"] = "ok";

$data["success"] = 1;
$data["result"] = $events;


echo json_encode($data, JSON_NUMERIC_CHECK);
?>