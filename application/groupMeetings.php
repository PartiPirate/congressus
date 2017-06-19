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
include_once("header.php");

require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/GuestBo.php");

$meetingBo = MeetingBo::newInstance($connection, $config);

//$userId = SessionUtils::getUserId($_SESSION);

$meetings = $meetingBo->getByFilters(array("by_personae_group" => true, "with_status" => array("waiting", "open", "closed")));

$groupMeetings = array();

foreach($meetings as $meeting) {
	if (!isset($groupMeetings[$meeting["not_target_id"]])) {
		$groupMeetings[$meeting["not_target_id"]] = array();
	}
	$groupMeetings[$meeting["not_target_id"]][] = $meeting;
}

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
<?php 	$active = "active";
		foreach($groupMeetings as $groupId => $meetings) { ?>
		<li role="presentation" class="<?php echo $active; ?>">
			<a href="#id-<?php echo $meetings[0]["not_target_id"]; ?>" aria-controls="id-<?php echo $meetings[0]["not_target_id"]; ?>" role="tab" data-toggle="tab"><?php echo $meetings[0]["gro_label"] ?>
				<span class="badge"><?php echo count($meetings); ?></span>
			</a>
		</li>
<?php 		$active = "";
		}?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
<?php 	$active = "active";
		foreach($groupMeetings as $groupId => $meetings) {?>
		<div role="tabpanel" class="tab-pane <?php echo $active; ?>" id="id-<?php echo $meetings[0]["not_target_id"]; ?>">

<?php 		if (count($meetings)) {?>
			<table class="table">
				<thead>
					<tr>
						<th style="width: 60%"><?php echo lang("groupMeeting_name"); ?></th>
						<th style="width: 15%"><?php echo lang("groupMeeting_status"); ?></th>
						<th style="width: 170px;"><?php echo lang("groupMeeting_name"); ?>Date</th>
					</tr>
				</thead>
				<tbody>
<?php 			foreach($meetings as $meeting) {?>
					<tr>
						<td><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></td>
						<td><?php echo $meeting["mee_status"]; ?></td>
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
<?php 		} else {?>
<br>
			<div class="well"><?php echo lang("groupMeeting_empty"); ?></div>
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
