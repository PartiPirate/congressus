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

session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/ConclusionBo.php");
require_once("engine/bo/TaskBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/VoteBo.php");

// People
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/PingBo.php");
require_once("engine/bo/FixationBo.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");

require_once("language/language.php");

require_once("engine/utils/LogUtils.php");
addLog($_SERVER, $_SESSION, null, $_POST);

$connection = openConnection();

$meetingBo = MeetingBo::newInstance($connection, $config);
$agendaBo = AgendaBo::newInstance($connection, $config);
$chatBo = ChatBo::newInstance($connection, $config);
$conclusionBo = ConclusionBo::newInstance($connection, $config);
$taskBo = TaskBo::newInstance($connection, $config);
$motionBo = MotionBo::newInstance($connection, $config);
$voteBo = VoteBo::newInstance($connection, $config);

// People
$noticeBo = NoticeBo::newInstance($connection, $config);
$pingBo = PingBo::newInstance($connection, $config);
$fixationBo = FixationBo::newInstance($connection, $config);
$groupBo = GroupBo::newInstance($connection, $config);
$themeBo = ThemeBo::newInstance($connection, $config);
$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);


$template = $_REQUEST["template"];
$meeting = $meetingBo->getById($_REQUEST["id"]);
if (isset($_REQUEST["textarea"]) AND ($_REQUEST["textarea"]=='true')) {$textarea = true;} else {$textarea = false;}

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
}

$data = array();

$agendas = $agendaBo->getByFilters(array("age_meeting_id" => $meeting[$meetingBo->ID_FIELD], "with_count_motions" => true));

foreach($agendas as $aIndex => $agenda) {
	$agenda["age_objects"] = json_decode($agenda["age_objects"], true);

	$motions = $motionBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
	$chats = $chatBo->getByFilters(array("cha_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

	$conclusions = $conclusionBo->getByFilters(array("con_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
	$tasks = $taskBo->getByFilters(array("tas_agenda_id" => $agenda[$agendaBo->ID_FIELD]));

	$agenda["chats"] = $chats;
	$agenda["conclusions"] = $conclusions;
	$agenda["motions"] = $motions;
	$agenda["tasks"] = $tasks;

	$agendas[$aIndex] = $agenda;
}

$end = new DateTime($meeting["mee_datetime"]);
$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
$meeting["mee_end_datetime"] = $end->add($duration);
$meeting["mee_end_datetime"] = $meeting["mee_end_datetime"]->format("Y-m-d H:i:s");

// People

$pings = $pingBo->getByFilters(array("pin_meeting_id" => $meeting[$meetingBo->ID_FIELD]));
//usort($pings, "pingSpeakingRequestCompare");

$order = 1;

//print_r($pings);
$now = new DateTime();
$now->add(new DateInterval('PT2H'));

foreach($pings as $index => $ping) {
	if (!$ping["pin_speaking_request"]) continue;
	if ($ping["pin_guest_id"])
	{
		$lastPing = new DateTime($ping["pin_datetime"]);

		$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

		if ($diff >= 60) continue;
	}

	$pings[$index]["pin_speaking_request"] = $order;
	$order++;
}

//print_r($pings);

$dbNotices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting[$meetingBo->ID_FIELD]));

$data = array();
$notices = array();

$usedPings = array();

foreach($dbNotices as $notice) {
	// Search fixation for the notice

	if ($notice["not_target_type"] == "galette_groups") {

		//		echo "galette_groups\n";

		$groups = $galetteBo->getGroups(array("id_group" => $notice["not_target_id"]));

		$group = array("group_name" => "");
		if (count($groups)) {
			$group = $groups[0];
		}
		$members = $galetteBo->getMembers(array("adh_group_ids" => array($notice["not_target_id"])));

		//		print_r($members);

		$notice["not_label"] = htmlspecialchars(utf8_encode($group["group_name"]), ENT_SUBSTITUTE);
		$notice["not_people"] = array();

		foreach($members as $member) {
			$people = array("mem_id" => $member["id_adh"]);
			$people["mem_nickname"] = htmlspecialchars(utf8_encode($member["pseudo_adh"] ? $member["pseudo_adh"] : $member["nom_adh"] . ' ' . $member["prenom_adh"]), ENT_SUBSTITUTE);
			$people["mem_power"] = 2;
			$people["mem_noticed"] = 1;
			$people["mem_voting"] = $notice["not_voting"];
			$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
			$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
			$people["mem_present"] = 0;

			$found = false;
			foreach($pings as $index => $ping) {
				if ($ping["pin_member_id"] == $member["id_adh"]) {
					$found = true;
					$lastPing = new DateTime($ping["pin_datetime"]);

					$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

					if ($diff < 60) {
						$people["mem_connected"] = true;
					}

					$people["mem_speaking"] = $ping["pin_speaking"];
					$people["mem_speaking_request"] = $ping["pin_speaking_request"];
					$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);

					$usedPings[] = $ping;
					unset($pings[$index]);
					$found = true;
				}
			}
			if (!$found) {
				foreach($usedPings as $index => $ping) {
					if ($ping["pin_member_id"] == $member["id_adh"]) {
						$found = true;
						$lastPing = new DateTime($ping["pin_datetime"]);

						$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

						if ($diff < 60) {
							$people["mem_connected"] = true;
						}

						$people["mem_speaking"] = $ping["pin_speaking"];
						$people["mem_speaking_request"] = $ping["pin_speaking_request"];
						$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);
					}
				}
			}

			$notice["not_people"][] = $people;
		}

		//		print_r($notice["not_people"]);
	}
	else if ($notice["not_target_type"] == "dlp_themes") {
		$theme = $themeBo->getTheme($notice["not_target_id"]);
		$fixationMembers = $fixationBo->getFixations(array("fix_id" => $theme["the_current_fixation_id"], "with_fixation_members" => true));

		//		$theme["the_fixation_members"] = $fixationMembers;
		//		$notice["not_target"] = $theme;

		$notice["not_label"] = $theme["the_label"];
		$notice["not_people"] = array();

		foreach($fixationMembers as $fixationMember) {
			if (!$fixationMember["id_adh"]) continue;
			$people = array("mem_id" => $fixationMember["id_adh"]);
			$people["mem_nickname"] = htmlspecialchars(utf8_encode($fixationMember["pseudo_adh"] ? $fixationMember["pseudo_adh"] : $fixationMember["nom_adh"] . ' ' . $fixationMember["prenom_adh"]), ENT_SUBSTITUTE);
			$people["mem_power"] = $fixationMember["fme_power"];
			$people["mem_voting"] = $notice["not_voting"];
			$people["mem_noticed"] = 1;
			$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
			$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
			$people["mem_present"] = "0";

			$found = false;
			foreach($pings as $index => $ping) {
				if ($ping["pin_member_id"] == $fixationMember["id_adh"]) {
					$lastPing = new DateTime($ping["pin_datetime"]);

					$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

					if ($diff < 60) {
						$people["mem_connected"] = true;
					}

					$people["mem_speaking"] = $ping["pin_speaking"];
					$people["mem_speaking_request"] = $ping["pin_speaking_request"];
					$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);

					$usedPings[] = $ping;
					unset($pings[$index]);
					$found = true;
				}
			}
			if (!$found) {
				foreach($usedPings as $index => $ping) {
					if ($ping["pin_member_id"] == $fixationMember["id_adh"]) {
						$found = true;
						$lastPing = new DateTime($ping["pin_datetime"]);

						$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

						if ($diff < 60) {
							$people["mem_connected"] = true;
						}

						$people["mem_speaking"] = $ping["pin_speaking"];
						$people["mem_speaking_request"] = $ping["pin_speaking_request"];
						$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);
					}
				}
			}

			$notice["not_people"][] = $people;
		}
	}
	else if ($notice["not_target_type"] == "dlp_groups") {
		$group = $groupBo->getGroup($notice["not_target_id"]);

		//		$notice["not_group"] = $group;

		$notice["not_label"] = $group["gro_label"];
		$notice["not_people"] = array();
		$notice["not_children"] = array();

		foreach($group["gro_themes"] as $theme) {
			$child = array();
			$child["not_voting"] = $notice["not_voting"];
			$child["not_label"] = $theme["the_label"];
			$child["not_people"] = array();
			$child["not_power"] = $theme["gth_power"];

			foreach($theme["fixation"]["members"] as $fixationMember) {
				if (!$fixationMember["id_adh"]) continue;
				$people = array("mem_id" => $fixationMember["id_adh"]);
				$people["mem_nickname"] = htmlspecialchars(utf8_encode($fixationMember["pseudo_adh"] ? $fixationMember["pseudo_adh"] : $fixationMember["nom_adh"] . ' ' . $fixationMember["prenom_adh"]), ENT_SUBSTITUTE);
				$people["mem_power"] = $fixationMember["fme_power"];
				$people["mem_voting"] = $notice["not_voting"];
				$people["mem_noticed"] = 1;
				$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
				$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;
				$people["mem_present"] = 0;

				$found = false;
				foreach($pings as $index => $ping) {
					if ($ping["pin_member_id"] == $fixationMember["id_adh"]) {
						$lastPing = new DateTime($ping["pin_datetime"]);

						$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

						if ($diff < 60) {
							$people["mem_connected"] = true;
						}

						$people["mem_speaking"] = $ping["pin_speaking"];
						$people["mem_speaking_request"] = $ping["pin_speaking_request"];
						$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);

						$usedPings[] = $ping;
						unset($pings[$index]);
						$found = true;
					}
				}
				if (!$found) {
					foreach($usedPings as $index => $ping) {
						if ($ping["pin_member_id"] == $fixationMember["id_adh"]) {
							$found = true;
							$lastPing = new DateTime($ping["pin_datetime"]);

							$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

							if ($diff < 60) {
								$people["mem_connected"] = true;
							}

							$people["mem_speaking"] = $ping["pin_speaking"];
							$people["mem_speaking_request"] = $ping["pin_speaking_request"];
							$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);
						}
					}
				}

				$child["not_people"][] = $people;
			}

			$notice["not_children"][] = $child;
		}

		//		$notice["not_target"] = $group;
	}

	$notices[] = $notice;
}

$nowString = $now->format("Y-m-d H:i:s");

if (
	$meeting["mee_start_time"] && // we have a start date
	$meeting["mee_start_time"] != "0000-00-00 00:00:00" && // and it's not an empty date
	$meeting["mee_start_time"] < $nowString && // and we are now behind it (obviously the case)
	$meeting["mee_status"] != "closed") { // and the meeting is still not closed

	foreach($usedPings as $ping) {
		// If the noticed information is not set, set it, the used pings are noticed people
		if (!$ping["pin_noticed"]) {
			$noticedPing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
			$noticedPing["pin_noticed"] = 1;
			$pingBo->save($noticedPing);
		}

		if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
			$lastPing = new DateTime($ping["pin_datetime"]);

			$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

			if ($diff < 60) {
				$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
				$presencePing["pin_first_presence_datetime"] = $nowString;
				$pingBo->save($presencePing);
			}
		}
	}

	foreach($pings as $ping) {
		if (!$ping["pin_first_presence_datetime"] || $ping["pin_first_presence_datetime"] == "0000-00-00 00:00:00") {
			$lastPing = new DateTime($ping["pin_datetime"]);

			$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

			if ($diff < 60) {
				$presencePing = array($pingBo->ID_FIELD => $ping[$pingBo->ID_FIELD]);
				$presencePing["pin_first_presence_datetime"] = $nowString;
				$pingBo->save($presencePing);
			}
		}
	}
}

$visitors = array();

foreach($pings as $ping) {
	$people = array("mem_id" => $ping["id_adh"] ? $ping["id_adh"] : "G" . $ping["pin_guest_id"]);
	$people["mem_nickname"] = $ping["id_adh"] ? $ping["pseudo_adh"] : $ping["pin_nickname"];
	$people["mem_meeting_president"] = ($people["mem_id"] == $meeting["mee_president_member_id"]) ? 1 : 0;
	$people["mem_meeting_secretary"] = ($people["mem_id"] == $meeting["mee_secretary_member_id"]) ? 1 : 0;

	$lastPing = new DateTime($ping["pin_datetime"]);

	$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

	if ($diff < 60) {
		$people["mem_connected"] = true;
	}
	else if (!$ping["id_adh"]) {
		continue;
	}

	$people["mem_speaking"] = $ping["pin_speaking"];
	$people["mem_speaking_request"] = $ping["pin_speaking_request"];

	$visitors[] = $people;
}

// Process the template

ob_start();

include_once("meeting/export_templates/$template.php");

$content = ob_get_contents();

ob_end_clean();

// Post-process the computation

if (file_exists("export_templates/" . $template . "_post.php")) {
	include_once("meeting/export_templates/" . $template . "_post.php");
}

echo $content;
?>
