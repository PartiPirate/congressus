<?php /*
	Copyright 2016-2017 Cédric Levieux, Parti Pirate

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
session_start();
require_once("config/database.php");
require_once("engine/utils/IcsFormatter.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("language/language.php");

$timezone = null;
if ($config["server"]["timezone"]) {
	$timezone = new DateTimeZone($config["server"]["timezone"]);
}

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$meetings = $meetingBo->getByFilters(array("with_principal_location" => true, "with_status" => array("waiting", "open", "closed")));

//print_r($meetings);

$entries = array();
$timestamp = new DateTime(null);

$eveAlarm = new Alarm();
//$eveAlarm->repeat = 3;
//$eveAlarm->duration = Alarm::EVERY_DAY;
$eveAlarm->trigger = Alarm::TRIGGER_EVE;
$eveAlarm->action = Alarm::ACTION_DISPLAY;

foreach($meetings as $meeting) {

	$start = new DateTime($meeting["mee_datetime"], $timezone);
	$start = $start->getTimestamp();
	$end = $start + 60 * ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60);
	
	$summary = $meeting["mee_label"];
	
	if ($meeting["loc_type"]) {
		$summary .= " - " . lang("loc_type_" . $meeting["loc_type"], false);
	
		if ($meeting["loc_extra"] && $meeting["loc_type"] != "discord") {
			$summary .= " (" . $meeting["loc_extra"] . ")";
		}
		else if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {
			require_once("config/discord.structure.php");

			list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);
			
			$discord_text_link = @$discord_text_channels[$discord_text_channel];
			$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];

			if ($discord_text_link || $discord_vocal_link) {
				$summary .= " (";
				if ($discord_text_link) $summary .= "<i class='fa fa-hashtag' aria-hidden='true'></i> <a href='$discord_text_link' target='_blank'>$discord_text_channel</a> ";
				if ($discord_vocal_link) $summary .= "<i class='fa fa-volume-up' aria-hidden='true'></i> <a href='$discord_vocal_link' target='_blank'>$discord_vocal_channel</a>";
				$summary .= ")";
			}
		}
	}
	
	$summary = str_replace("\n", " ", $summary);
	$summary = str_replace("\r", " ", $summary);
	
	$entry = new Event();
	$entry->startDate = new DateTime(null);
	$entry->startDate->setTimestamp($start);
	$entry->endDate = new DateTime(null);
	$entry->endDate->setTimestamp($end);
	$entry->summary = $summary;
	
	$entry->uid = "CONGRESSUS_" . $meeting[$meetingBo->ID_FIELD];
	$entry->timestamp = $timestamp;
	$entry->location = $config["server"]["base"] . "meeting.php?id=" . $meeting[$meetingBo->ID_FIELD];
	
	$entry->alarms[] = $eveAlarm;
	
	$entries[] = $entry;
}

$icsFormatter = new IcsFormatter();
$icsFormatter->company = "Parti Pirate";
$icsFormatter->product = "Congressus";

$calendarName = "calendar.ics";

header('Content-Type: text/calendar');
header("Content-Transfer-Encoding: 8BIT");
header("Content-disposition: attachment; filename=\"".$calendarName."\"");

echo $icsFormatter->format($entries);

?>