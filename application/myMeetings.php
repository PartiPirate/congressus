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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once("header.php");

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/GuestBo.php");

$meetingBo = MeetingBo::newInstance($connection, $config);
$noticeBo = NoticeBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);

$secretaryMeetings = $meetingBo->getByFilters(array("mee_secretary_member_id" => $userId));

$meetings = array();
$meetings = array_merge($meetings, $secretaryMeetings);

$statusMeetings = array("waiting" => array(),
		"construction" => array(),
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
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_myMeetings"); ?></li>
	</ol>

	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">
<?php 	$active = "active";
		foreach($statusMeetings as $status => $meetings) {?>
		<li role="presentation" class="<?php echo $active; ?>">
			<a href="#<?php echo $status; ?>" aria-controls="<?php echo $status; ?>" role="tab" data-toggle="tab"><?php echo lang("myMeetings_$status"); ?>
				<?php if (count($meetings)) {?><span class="badge"><?php echo count($meetings); ?></span><?php }?>
			</a>
		</li>
<?php 		$active = "";
		}?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
<?php 	$active = "active";
		foreach($statusMeetings as $status => $meetings) {?>
		<div role="tabpanel" class="tab-pane <?php echo $active; ?>" id="<?php echo $status; ?>">

<?php 		if (count($meetings)) {?>
			<table class="table">
				<thead>
					<tr>
						<th style="">Nom de la réunion</th>
						<th style="width: 220px;">Type</th>
						<th style="width: 220px;">Convocation</th>
						<th style="width: 170px;">Date</th>
						<th style="width: 170px;">Fin</th>
						<th style="width: 170px;">Actions</th>
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
						<td><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"] ? $meeting["mee_label"] : "-"; ?></a></td>
						<td><?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?></td>
						<td><?php echo implode(", ", $groupLabels); ?></td>
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
<?php				if ($status == "construction" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]))) { ?>
						<button class="btn btn-primary btn-waiting-meeting" data-status="<?php echo $status; ?>" data-meeting-id="<?php echo $meeting["mee_id"]; ?>"><?php echo lang("meeting_waiting"); ?></button>
						<button class="btn btn-danger  btn-delete-meeting"  data-status="<?php echo $status; ?>"  data-meeting-id="<?php echo $meeting["mee_id"]; ?>"><?php echo lang("meeting_delete"); ?></button>
<?php				} ?>
<?php				if ($status == "waiting" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]))) { ?>
						<button class="btn btn-success btn-open-meeting" data-status="<?php echo $status; ?>" data-meeting-id="<?php echo $meeting["mee_id"]; ?>"><?php echo lang("meeting_open"); ?></button>
<?php				} ?>
<?php				if ($status == "open" && (($userId == $meeting["mee_secretary_member_id"]) || ($userId == $meeting["mee_president_member_id"]))) { ?>
						<button class="btn btn-danger btn-close-meeting" data-status="<?php echo $status; ?>" data-meeting-id="<?php echo $meeting["mee_id"]; ?>"><?php echo lang("meeting_close"); ?></button>
<?php				} ?>
						</td>
					</tr>
<?php 			}?>
				</tbody>
			</table>
<?php 		} else {?>
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
