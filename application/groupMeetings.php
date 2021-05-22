<?php /*
    Copyright 2015 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
include_once("header.php");

/*
require_once("engine/modules/groupsource/GroupSourceFactory.php");
require_once("engine/modules/groupsource/PersonaeThemeSource.php");
require_once("engine/modules/groupsource/PersonaeGroupSource.php");
*/

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/GuestBo.php");

$meetingBo = MeetingBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) {
	if (!isset($_SESSION["guestId"])) {
		$guestBo = GuestBo::newInstance($connection, $config);
		// Create guestId
		$guest = array();
		$guestBo->save($guest);

		$guestId = $guest[$guestBo->ID_FIELD];
		$nickname = "Guest " . substr(md5($guestId), 0, 6);

		$_SESSION["guestId"] = $guestId;
		$_SESSION["guestNickname"] = $nickname;
	}
	$guestId = $_SESSION["guestId"];
}

//$meetings = $meetingBo->getByFilters(array("by_personae_group" => true, "with_status" => array("waiting", "open", "closed")));
$meetings = $meetingBo->getByFilters(array("by_notice" => true, "with_status" => array("waiting", "open", "closed")));

$groupMeetings = array();

function removeAccents($string) {
    return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'))), ' '));
}

function sortGroupMeetingByLabel($groupA, $groupB) {
	$labelA = removeAccents(strtolower($groupA["label"]));
	$labelB = removeAccents(strtolower($groupB["label"]));

	return $labelA <=> $labelB;
}

// we put the meetings into some meeting groups
foreach($meetings as $meeting) {
	$groupId = $meeting["not_target_type"] . $meeting["not_target_id"];
	if (!isset($groupMeetings[$groupId])) {
		// TODO cherche le bon group source en fonction de $meeting["not_target_type"]
		$groupSource = GroupSourceFactory::getInstance("personaethemes");
		$options = array("without_deleted" => true);
		$groupSourceLabel = $groupSource->getGroupLabel($meeting["not_target_id"], $options);

		// TODO si on activé l'option visualisation des archives
		if (false) {
			$groupSourceLabel = $groupSource->getGroupLabel($meeting["not_target_id"]);
		}
		
		if (!$groupSourceLabel) continue;

		$groupMeetings[$groupId] = array("label" => $groupSourceLabel, "meetings" => array());
	}
	$groupMeetings[$groupId]["meetings"][] = $meeting;
}

// order them by label
uasort($groupMeetings, "sortGroupMeetingByLabel");
?>

<div class="container theme-showcase meeting" role="main"
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_groupMeetings"); ?></li>
	</ol>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
<?php 	//$active = "active";
		foreach($groupMeetings as $groupId => $group) { 
			$meetings = $group["meetings"];
			$active = ($groupId == "dlp_themes46" ? "active" : "")
?>
		<li role="presentation" class="<?=$active?>">
			<a href="#id-<?=$groupId?>" aria-controls="id-<?=$groupId?>" role="tab" data-toggle="tab"><?=$group["label"]?>
				<span class="badge"><?=count($meetings)?></span>
			</a>
		</li>
<?php 	//	$active = "";
		}?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
<?php 	$active = "active";
		foreach($groupMeetings as $groupId => $group) { 
			$meetings = $group["meetings"];
			$active = ($groupId == "dlp_themes46" ? "active" : "")
?>
		<div role="tabpanel" class="tab-pane <?php echo $active; ?>" id="id-<?=$groupId?>">

<?php 		if (count($meetings)) {?>
			<table class="table">
				<thead>
					<tr>
						<th style="width: 60%"><?=lang("meeting_name")?></th>
						<th style="width: 15%"><?=lang("meeting_status")?></th>
						<th style="width: 170px;"><?=lang("common_date")?></th>
					</tr>
				</thead>
				<tbody>
<?php 			foreach($meetings as $meeting) {

					$statusLabelClass = "text-success";
					switch($meeting["mee_status"]) {
						case "waiting": 
							$statusLabelClass = "text-info";
							break;
						case "closed":
							$statusLabelClass = "text-danger";
							break;
					}
?>
					<tr>
						<td><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></td>
						<td class=<?=$statusLabelClass?>><?=lang("meeting_status_" . $meeting["mee_status"])?></td>
						<td><?php
								$start = new DateTime($meeting["mee_datetime"]);

								$datetime = lang("datetime_format");
								$datetime = str_replace("{date}", $start->format(lang("date_format")), $datetime);
								$datetime = str_replace("{time}", $start->format(lang("time_format")), $datetime);

								echo $datetime;
							?></td>
					</tr>
<?php 			}?>
				</tbody>
			</table>
<?php 		} 
			else {?>
<br>
			<div class="well"><?php echo lang("meeting_empty"); ?></div>
<?php 		}?>



		</div>
<?php 		$active = "";
		}?>
	</div>

<?php include("connect_button.php"); ?>
</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
</script>
<?php include("footer.php");?>

</body>
</html>
