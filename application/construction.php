<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

require_once("engine/bo/GuestBo.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/SourceBo.php");
require_once("engine/bo/VoteBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/UserBo.php");
require_once("engine/utils/Parsedown.php");
require_once("engine/emojione/autoload.php");

$Parsedown = new Parsedown();
$emojiClient = new Emojione\Client(new Emojione\Ruleset());

$noticeBo = NoticeBo::newInstance($connection, $config);

if (!$meeting) {
	// Ask for creation
	$meeting = array("mee_label" => lang("meeting_eventNew"));
}
else {
	$start = new DateTime($meeting["mee_datetime"]);
	$end = new DateTime($meeting["mee_datetime"]);
	$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
	$end = $end->add($duration);

	if ($meeting["loc_type"] == "framatalk") {
		$framachan = sha1($meeting["mee_id"] . "framatalk" . $meeting["mee_id"]);
	}
}

$userId = SessionUtils::getUserId($_SESSION);

$votingPower = 0;

$notices = $noticeBo->getByFilters(array("not_meeting_id" => $meeting["mee_id"], "not_voting" => 1));

if ($userId) {
	foreach($notices as $notice) {
		foreach($config["modules"]["groupsources"] as $groupSourceKey) {
			$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
        	$groupKeyLabel = $groupSource->getGroupKeyLabel();

        	if ($groupKeyLabel["key"] != $notice["not_target_type"]) continue;

        	$members = $groupSource->getNoticeMembers($notice);
        	
        	foreach($members as $member) {
        		if ($member["id_adh"] != $userId) continue;

//        		echo "<pre>" . print_r($member, true) . "</pre>";

        		$votingPower = $member["fme_power"];
        	}
		}
	}
}

$hasWritingRights = false;
if (!$userId) {
	if (!isset($_SESSION["guestId"])) {
		$guestBo = GuestBo::newInstance($connection, $config);
		// Create guestId
		$guest = array();
		$guestBo->save($guest);

		$guestId = $guest[$guestBo->ID_FIELD];
		$nickname = "Guest $guestId";

		$_SESSION["guestId"] = $guestId;
		$_SESSION["guestNickname"] = $nickname;
	}
	$guestId = $_SESSION["guestId"];
}
else {
	if ($userId == $meeting["mee_president_member_id"]) {
		$hasWritingRights = true;
	}
	else if ($userId == $meeting["mee_secretary_member_id"]) {
		$hasWritingRights = true;
	}
}

$hasChat = false;
//$leftColumn = 3;
$mainColumn = 12;
$rightColumn = 0;

if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {
	$hasChat = true;
	$mainColumn = 9;
	$rightColumn = 3;
}

// print_r($meeting);

$agendaBo = AgendaBo::newInstance($connection, $config);
$motionBo = MotionBo::newInstance($connection, $config);
$voteBo   = VoteBo::newInstance($connection, $config);
$chatBo   = ChatBo::newInstance($connection, $config);
$userBo   = UserBo::newInstance($connection, $config);
$sourceBo = SourceBo::newInstance($connection, $config);

$agendaFilters = array("age_meeting_id" => $meeting["mee_id"]);
$oneAgenda = false;

if (isset($_REQUEST["agendaId"]) && $_REQUEST["agendaId"]) {
	$agendaFilters["age_id"] = intval($_REQUEST["agendaId"]);
	$oneAgenda = true;
}

$agendas = $agendaBo->getByFilters($agendaFilters);

// print_r($agendas);

?>

<style>

.pinned {
    background-color: #eee;
}

</style>

<div class=" theme-showcase meeting" role="main"
	style="margin-left: 32px; margin-right: 32px; "
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	data-speaking-id="-1"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		
		<?php	if ($oneAgenda) { ?>
		<li><a href="?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></li>
		<li class="active"><?php echo $agendas[0]["age_label"]; ?></li>
		<?php	} else { ?>
		<li class="active"><?php echo $meeting["mee_label"]; ?></li>
		<?php	} ?>
	</ol>

	<div class="row">
		<div class="col-md-9">
			<div id="notices-panel" class="panel panel-default">
				<div class="panel-heading">
					<?php echo lang("notice_groups"); ?>
				</div>
				<div class="panel-body">
		<?php
			$groupLabels = array();
		
			foreach($notices as $notice) {
				foreach($config["modules"]["groupsources"] as $groupSourceKey) {
					$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
		        	$groupKeyLabel = $groupSource->getGroupKeyLabel();
		
		        	if ($groupKeyLabel["key"] != $notice["not_target_type"]) continue;
		        	
		//        	$members = $groupSource->getNoticeMembers($notice);
					$groupLabel = $groupSource->getGroupLabel($notice["not_target_id"]);
					
					$groupLabels[] = $groupLabel;
				}
			}
		
			echo implode(", ", $groupLabels);
		?>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div id="notices-panel" class="panel panel-default">
				<div class="panel-heading">
					<?php echo lang("meeting_base_type"); ?>
				</div>
				<div class="panel-body">
					<?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-<?php echo $mainColumn; ?>" id="main-panel">
			
<?php 
		foreach($agendas as $agenda) { 
			if ($agenda["age_parent_id"]) continue;

			$showTitle = true;
			$isTrash = false;

			$phasWritingRights = $hasWritingRights;
			$hasWritingRights = $votingPower || $hasWritingRights;

			include("construction/amendment_list.php");

			$hasWritingRights = $phasWritingRights;
			
	 	} ?>			
			
<?php	if ($hasWritingRights && !$oneAgenda && ($meeting["mee_status"] != "closed")) { ?>
			<button class="btn btn-default btn-add-point" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" style="width: 100%;">Point <span class="fa fa-list-alt"></span></button>
			<br>
			<br>
<?php	} ?>

<?php
		$showTitle = true;
		$isTrash = true;

		$phasWritingRights = $hasWritingRights;
		$hasWritingRights = false;

		$agenda = array("age_id" => -1, "age_description" => lang("trash_description"), "age_label" => lang("trash_title"));
		$agenda["age_motions"] = $motionBo->getByFilters(array("with_meeting" => true, "mee_id" => $meeting["mee_id"], "mot_trashed" => 1));;

		include("construction/amendment_list.php");

		$hasWritingRights = $phasWritingRights;
?>

		</div>
		<?php	if ($hasChat) { ?>
		<div class="col-md-<?php echo $rightColumn; ?>">
			<div id="meeting_external_chat" class="panel panel-default" style="margin-bottom: 0px;">
				<div class="panel-heading">
					<a data-toggle="collapse" data-target="#echat-list" href="#"><?php echo lang("meeting_external_chat"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="echat-list" style="max-height: 422px; overflow-y: scroll;">
				</ul>
			</div>
		</div>
		<?php	} ?>
	</div>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<div class="container otbHidden">
</div>

<templates>
	<ul>
		<li data-template-id="echat" id="echat-${message_id}"
				class="template list-group-item echat" data-id="${message_id}">
			<img src="${mem_avatar_url}" style="max-height: 20px; max-width: 20px; border-radius: 10px; ">
			<span class="nickname">${mem_nickname}</span> : 
			<span class="message">${message}</span>
		</li>
	</ul>
</templates>

<div id="exportModal"></div>

<?php
	include("construction/amendment_modal.php");
	include("construction/agenda_modal.php");
?>

<script>
</script>
<?php include("footer.php");?>
<script src="assets/js/perpage/order_list_helper.js"></script>
<script src="assets/js/perpage/construction_source_helper.js"></script>
<script src="assets/js/perpage/construction_motion_save.js"></script>
<script src="assets/js/perpage/meeting_events.js"></script>
<script>
sourceEnabled = true;
var meeting_id = "<?php echo $meeting["mee_id"]; ?>";


var getEventsTimer;
var getEventsTimerInterval = 1500;

$(function() {
	if ($("#meeting_external_chat").length) {
		getEventsTimer = $.timer(getEvents);
		getEventsTimer.set({ time : getEventsTimerInterval, autostart : true });
	}
});

</script>

<script type="text/javascript">
var userLanguage = '<?php echo SessionUtils::getLanguage($_SESSION); ?>';

var common_edit = "<?php echo lang("common_edit"); ?>";
var common_close = "<?php echo lang("common_close"); ?>";

var meeting_speakingAsk = "<?php echo strtolower(lang("meeting_speakingAsk")); ?>";
var meeting_speaking = "<?php echo lang("meeting_speaking"); ?>";
var meeting_speakingRenounce = "<?php echo lang("meeting_speakingRenounce"); ?>";
var meeting_arrival = "<?php echo lang("meeting_arrival"); ?>";
var meeting_left = "<?php echo lang("meeting_left"); ?>";
var meeting_votePower = "<?php echo lang("meeting_votePower"); ?>";
var meeting_notification = "<?php echo lang("meeting_notification"); ?>";
var meeting_notificationDelete = "<?php echo lang("meeting_notificationDelete"); ?>";
var meeting_motionVote2 = "<?php echo lang("meeting_motionVote2"); ?>";
var meeting_vote = "<?php echo lang("meeting_vote"); ?>";
var meeting_motionDelete = "<?php echo lang("meeting_motionDelete"); ?>";
var meeting_taskDelete = "<?php echo lang("meeting_taskDelete"); ?>";
var meeting_taskEnd = "<?php echo lang("meeting_taskEnd"); ?>";
var meeting_chatDelete = "<?php echo lang("meeting_chatDelete"); ?>";
var meeting_conclusionDelete = "<?php echo lang("meeting_conclusionDelete"); ?>";
var meeting_proposalDelete = "<?php echo lang("meeting_proposalDelete"); ?>";

var majority_judgement_values = <?php echo json_encode($config["congressus"]["ballot_majority_judgment"]); ?>

var speakingTimesChartTitle = "Temps de parole par personne";

<?php

$translatons = array();
foreach($config["congressus"]["ballot_majority_judgment"] as $value) {
	$translatons[] = lang("motion_majorityJudgment_" . $value, false);
}

?>

var majority_judgement_translations = <?php echo json_encode($translatons); ?>


</script>

</body>
</html>
