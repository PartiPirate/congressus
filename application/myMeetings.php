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

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/GuestBo.php");

$meetingBo = MeetingBo::newInstance($connection, $config);
$noticeBo = NoticeBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);
$serverAdminBo = ServerAdminBo::newInstance($connection, $config);
$isAdmin = count($serverAdminBo->getServerAdmins(array("sad_member_id" => $userId))) > 0;

$filters = array();
if (!$isAdmin) {
	$filters["mee_secretary_member_id"] = $userId;
}

$secretaryMeetings = $meetingBo->getByFilters($filters);

$meetings = array();
$meetings = array_merge($meetings, $secretaryMeetings);

$statusMeetings = array(
		"construction" => array(),
		"template" => array(),
		"waiting" => array(),
		"open" => array(),
		"closed" => array());

foreach($meetings as $meeting) {
	$statusMeetings[$meeting["mee_status"]][] = $meeting;
}

$timezone = null;
if ($config["server"]["timezone"]) {
	$timezone = new DateTimeZone($config["server"]["timezone"]);
}

?>

<div class="container theme-showcase meeting-container" role="main"
	data-id="<?=@$meeting[$meetingBo->ID_FIELD]?>"
	data-user-id="<?=$userId ? $userId : "G" . $guestId?>"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?=lang("breadcrumb_index")?></a></li>
		<li class="active"><?=lang("breadcrumb_myMeetings")?></li>
	</ol>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
<?php 	$active = "active";
		foreach($statusMeetings as $status => $meetings) {?>
		<li role="presentation" class="<?=$active?>">
			<a href="#<?=$status?>" aria-controls="<?=$status?>" role="tab" data-toggle="tab"><?=lang("myMeetings_$status")?>
				<?php if (count($meetings)) {?><span class="badge"><?=count($meetings)?></span><?php }?>
			</a>
		</li>
<?php 		$active = "";
		}?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
<?php 	$active = "active";
		foreach($statusMeetings as $status => $meetings) {?>
		<div role="tabpanel" class="tab-pane <?=$active?>" id="<?=$status?>">

<?php 		if (count($meetings)) {?>
			<table class="table">
				<thead>
					<tr>
						<th style="">Nom de la réunion</th>
						<th style="width: 200px;">Type</th>
						<th style="width: 200px;">Convocation</th>
						<th style="width: 155px;">Date</th>
						<th style="width: 155px;">Fin</th>
						<th style="width: 240px;">Actions</th>
					</tr>
				</thead>
				<tbody>
<?php 			foreach($meetings as $meeting) {
					$notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting["mee_id"], "not_voting" => 1));

					$groupLabels = array();
				
					foreach($notices as $notice) {
						foreach($config["modules"]["groupsources"] as $groupSourceKey) {
							$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
				        	$groupKeyLabel = $groupSource->getGroupKeyLabel();
				
				        	if ($groupKeyLabel["key"] != $notice["not_target_type"]) continue;
				        	
				//        	$members = $groupSource->getNoticeMembers($notice);
							$groupLabel = $groupSource->getGroupLabel($notice["not_target_id"]);
							
				        	$members = $groupSource->getNoticeMembers($notice);
				        	
				        	foreach($members as $member) {
				        		if ($member["id_adh"] != $userId) continue;
				        		
								$groupLabel = "<em>" . $groupLabel . "</em>";
				        		break;
				        	}

//				        	$groupLabel = $groupLabel . "(".$notice["not_target_type"].")";
//				        	$groupLabel = $groupLabel . "[".count($members)."]";
				        	
							$groupLabels[] = $groupLabel;
						}
					}

					if ($meeting["mee_finish_time"]) {
						$endDate = new DateTime($meeting["mee_finish_time"], $timezone);
					}
					else {
						$endDate = new DateTime($meeting["mee_datetime"], $timezone);
						$endDate->add(new DateInterval('PT' . $meeting["mee_expected_duration"] . 'M'));
					}
?>
					<tr>
						<td><a href="meeting.php?id=<?=$meeting["mee_id"]?>"><?=$meeting["mee_label"] ? $meeting["mee_label"] : "-"?></a></td>
						<td><?=lang("createMeeting_base_type_" . $meeting["mee_type"])?></td>
						<td><?=implode(", ", $groupLabels)?></td>
						<td><?php
								$start = new DateTime($meeting["mee_datetime"]);

								$datetime = lang("datetime_format");
								$datetime = str_replace("{date}", $start->format(lang("date_format")), $datetime);
								$datetime = str_replace("{time}", $start->format(lang("time_format")), $datetime);

								echo $datetime;
							?></td>
						<td><?php
								$datetime = lang("datetime_format");
								$datetime = str_replace("{date}", $endDate->format(lang("date_format")), $datetime);
								$datetime = str_replace("{time}", $endDate->format(lang("time_format")), $datetime);

								echo $datetime;
							?></td>
						<td>
							<?php // echo $status; ?>
<?php				if ($status == "construction" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]) || $isAdmin)) { ?>
						<button class="btn btn-primary btn-xs btn-waiting-meeting" data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_waiting")?></button>
						<button class="btn btn-danger  btn-xs btn-delete-meeting"  data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_delete")?></button>
<?php				} ?>
<?php				if ($status == "waiting" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]) || $isAdmin)) { ?>
						<button class="btn btn-success btn-xs btn-open-meeting"    data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_open")?></button>
						<button class="btn btn-danger  btn-xs btn-delete-meeting"  data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_delete")?></button>
<?php				} ?>
<?php				if ($status == "open" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]) || $isAdmin)) { ?>
						<button class="btn btn-danger  btn-xs btn-close-meeting"   data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_close")?></button>
<?php				} ?>
<?php				if ($status == "template") { ?>
						<button class="btn btn-info    btn-xs btn-copy-meeting"    data-status="<?=$status?>" data-meeting-id="<?=$meeting["mee_id"]?>"><?=lang("meeting_copy")?></button>
<?php				} ?>
						</td>
					</tr>
<?php 			}?>
				</tbody>
			</table>
<?php 		} else {?>
<br>
			<div class="well"><?=lang("meeting_empty")?></div>
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
