<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();
include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("language/language.php");

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection);
$meetings = $meetingBo->getByFilters(array("with_principal_location" => true, "with_status" => array("waiting", "open", "closed")));

//print_r($meetings);

$data = array();

$events = array();

foreach($meetings as $meeting) {

	$start = new DateTime($meeting["mee_datetime"]);
	$start = $start->getTimestamp() - 7200; // TODO fix this
	$end = $start + 60 * ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60);

	$event = array(
			'id' => $meeting[$meetingBo->ID_FIELD],
			'title' => $meeting["mee_label"],
			'url' => "meeting.php?id=" . $meeting[$meetingBo->ID_FIELD],
			'class' => $meeting["mee_class"],
			'start' => $start . '000',
			'end' => $end . '000'
	);

	if ($meeting["loc_type"]) {
		$event["title"] .= " - " . lang("loc_type_" . $meeting["loc_type"]);

		if ($meeting["loc_extra"]) {
			$event["title"] .= " (" . $meeting["loc_extra"] . ")";
		}
	}

	$events[] = $event;
}

$data["ok"] = "ok";

$data["success"] = 1;
$data["result"] = $events;


echo json_encode($data, JSON_NUMERIC_CHECK);
?>