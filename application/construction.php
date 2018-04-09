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

<!--
	<div class="row" style="margin-bottom: 5px; height: 30px; ">
		<div class="col-md-4" style="/*padding-top: 7px; padding-bottom: 7px;*/">
			<span class="glyphicon glyphicon-time"></span> <?php echo lang("meeting_dateStart"); ?>
			<span class="mee_start datetime-control">
				<?php echo lang("meeting_the"); ?>
				<span class="date-control">
					<span class="span-date"><?php echo @$start->format(lang("date_format"))?></span>
					<input style="display:none; height: 20px;" class="input-date" type="date" value="<?php echo @$start->format("Y-m-d"); ?>" />
				</span>
				<?php echo lang("meeting_at"); ?>
				<span class="time-control">
					<span class="span-time"><?php echo @$start->format(lang("time_format"))?></span>
					<input style="display:none; height: 20px;" class="input-time" type="time" value="<?php echo @$start->format("H:i"); ?>" />
				</span>
			</span>
		</div>
		<div class="col-md-4" style="/*padding-top: 7px; padding-bottom: 7px;*/">
			<span class="glyphicon glyphicon-time"></span> <?php echo lang("meeting_dateEnd"); ?>
			<span class="mee_finish datetime-control">
				<?php echo lang("meeting_the"); ?>
				<span class="date-control">
					<span class="span-date"><?php echo @$end->format(lang("date_format"))?></span>
					<input style="display:none; height: 20px;" class="input-date" type="date" value="<?php echo @$end->format("Y-m-d"); ?>" />
				</span>
				<?php echo lang("meeting_at"); ?>
				<span class="time-control">
					<span class="span-time"><?php echo @$end->format(lang("time_format"))?></span>
					<input style="display:none; height: 20px;" class="input-time" type="time" value="<?php echo @$end->format("H:i"); ?>" />
				</span>
			</span>
		</div>
		<div class="col-md-4 synchro-vote" style="/*padding-top: 7px; padding-bottom: 7px;*/">
			<span class="fa fa-archive"></span> 
				<span class="synchro-vote-option synchro-vote-0" style="display: none;"><?php echo lang("mee_synchro_vote_0"); ?></span>
				<span class="synchro-vote-option synchro-vote-1" style="display: none;"><?php echo lang("mee_synchro_vote_1"); ?></span>

			<select class="form-control" data-type="mee_synchro_vote" style="margin-top: -7px;">
				<option value="0"><?php echo lang("mee_synchro_vote_0"); ?></option>
				<option value="1"><?php echo lang("mee_synchro_vote_1"); ?></option>
			</select>
		</div>
	</div>
	<div class="row" style="margin-bottom: 5px; height: 30px; ">
		<div class="col-md-4 president">
			<span class="glyphicon glyphicon-education" style=""></span> <?php echo lang("meeting_president"); ?>
			<span class="mee_president_member_id read-data" data-id="0"></span>
			<select class="form-control" data-type="president" style="margin-top: -7px;">
				<option value="0"></option>
				<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
				<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
				<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
				<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
			</select>
		</div>
		<div class="col-md-4 secretary">
			<span class="glyphicon glyphicon-user" style=""></span> <?php echo lang("meeting_secretary"); ?>
			<span class="mee_secretary_member_id read-data" data-id="0"></span>
			<select class="form-control" data-type="secretary" style="margin-top: -7px;">
				<option value="0"></option>
				<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
				<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
				<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
				<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
			</select>
		</div>
	</div>
	<div class="row" style="margin-bottom: 5px; height: 30px; ">
		<div class="col-md-4">
			<span class="glyphicon glyphicon-map-marker"></span> <?php echo lang("createMeeting_place"); ?>
			<?php echo $meeting["loc_type"];?>
		</div>
		<?php if (($meeting["loc_type"] == "mumble") AND ($meeting["loc_channel"] !== "")) {?>
			<div class="col-md-4" >
				<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_mumblePlace"); ?>
				<?php
				include("config/mumble.structure.php");
				$mumble_channel = $meeting["loc_channel"];
				$mumble_link = "mumble://" . $mumble_server . "/" . $mumble[$mumble_channel] . "?title=" . $mumble_title . "&version=" . $mumble_version;
				echo "<a href='$mumble_link' target='_blank'>$mumble_channel</a>";
				?>
			</div>
		<?php }
			  else if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {?>
			<div class="col-md-4" >
				<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_discordPlace"); ?>
				<?php
				include("config/discord.structure.php");

				list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);
				
				$discord_text_link = @$discord_text_channels[$discord_text_channel];
				$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];
				
				echo "<i class='fa fa-hashtag' aria-hidden='true'></i> <a href='$discord_text_link' target='_blank'>$discord_text_channel</a> ";
				echo "<i class='fa fa-volume-up' aria-hidden='true'></i> <a href='$discord_vocal_link' target='_blank'>$discord_vocal_channel</a>";
				
				?>
			</div>
		<?php }?>
		<div class="col-md-4">
			<span class="glyphicon glyphicon-list-alt"></span> <?php echo lang("meeting_number_of_presents"); ?>
			<span class="number-of-presents">0</span>
		</div>
	</div>
	<div class="clearfix"></div>

	<div class="row president-panels" style="margin-bottom: 5px; ">
		<div class="col-md-8">
			<div id="speaking-panel" class="panel panel-default">
				<div class="panel-heading">
					<?php echo lang("meeting_talkManagement"); ?>
				</div>
				<div class="panel-body">
					<div class="row form-horizontal">
						<label class="control-label col-md-3"><?php echo lang("meeting_speaking"); ?> : </label>
						<label class="control-label col-md-2 speaker" style="text-align: left;"></label>
						<label class="control-label col-md-2 speaking-time"><span></span></label>
						<label class="control-label col-md-4">
							<button class="btn btn-danger btn-xs btn-remove-speaker pull-left"
								title="<?php echo lang("meeting_removeSpeaking"); ?>"
								style="display: none;"><?php echo lang("meeting_speakingEnd"); ?> <span class="glyphicon glyphicon-remove"></span>
							</button>
						</label>
						<label class="control-label col-md-1">
							<button class="btn btn-info btn-xs btn-see-speaking-stats pull-left"
								title="<?php echo lang("meeting_speakingStats"); ?>"
								style=""><i class="fa fa-pie-chart" aria-hidden="true"></i></span>
							</button>
						</label>
					</div>
					<div class="row form-horizontal">
						<label class="control-label col-md-3"><?php echo lang("meeting_speakingAsk"); ?> : </label>
						<div class="col-md-9 speaking-requesters">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div id="meeting-status-panel" class="panel panel-default">
				<div class="panel-heading">
					<?php echo lang("meeting_action"); ?>
				</div>
				<div class="panel-body text-center">

					<div class="row">
						<button class="margin-top-5 btn btn-primary btn-waiting-meeting simply-hidden"><?php echo lang("meeting_waiting"); ?></button>
						<button class="margin-top-5 btn btn-danger btn-delete-meeting simply-hidden"><?php echo lang("meeting_delete"); ?></button>
						<button class="margin-top-5 btn btn-success btn-open-meeting simply-hidden"><?php echo lang("meeting_open"); ?></button>
						<button class="margin-top-5 btn btn-danger btn-close-meeting simply-hidden"><?php echo lang("meeting_close"); ?></button>
						
						<a		class="margin-top-5 btn btn-primary btn-vote-meeting" href="myVotes.php?meetingId=<?php echo $meeting["mee_id"]; ?>" target="_blank"><?php echo lang("meeting_voteExternal"); ?> <span class="glyphicon glyphicon-new-window"></span></a>
						<button class="margin-top-5 btn btn-default request-speaking"><?php echo lang("meeting_speakingAsk"); ?>
							<span class="fa fa-hand-paper-o"></span>
							<span class="badge" style="display: none;"></span>
						</button>
					</div>

					<button class="btn btn-default btn-local-anonymous" style="margin-top:5px;" data-toggle="tooltip" data-placement="bottom"
						title="<?php echo lang("meeting_hideVotes"); ?>"><?php echo lang("meeting_noInfluence"); ?> <span class="fa fa-archive"></span>
					</button>
					<br />

					<span class="closed-meeting simply-hidden"><?php echo lang("meeting_closed"); ?></span>

					<br class="export-br simply-hidden">

					<div class="row">
						<button data-template="html"		class="margin-top-5 btnShowExport export-link simply-hidden btn btn-success"><?php echo lang("export_html"); ?></button>
						<button data-template="pdf" 		class="margin-top-5 btnShowExport export-link simply-hidden btn btn-success"><?php echo lang("export_pdf"); ?></button>
						<button data-template="markdown"	class="margin-top-5 btnShowExport export-link simply-hidden btn btn-success"><?php echo lang("export_wiki"); ?></button>
						<button data-template="discourse"	class="margin-top-5 btnShowExport export-link simply-hidden btn btn-success"><?php echo lang("export_discourse"); ?></button>
					</div>

				</div>
			</div>
		</div>
	</div>
	
-->

<script type="text/javascript">
$(function() {
	CanvasJS.addColorSet("adviceColorSet",
	                [//colorSet Array
	                "#5cb85c",
	                "#f0ad4e",
	                "#d9534f"                
	                ]);	
});
</script>

	<div class="row">
		<div class="col-md-<?php echo $mainColumn; ?>" id="main-panel">
			
<?php 
		foreach($agendas as $agenda) { 
			if ($agenda["age_parent_id"]) continue;

			$showTitle = true;

			$phasWritingRights = $hasWritingRights;
			$hasWritingRights = $votingPower || $hasWritingRights;

			include("construction/amendment_list.php");

			$hasWritingRights = $phasWritingRights;
			
	 	} ?>			
			
<?php	if ($hasWritingRights && !$oneAgenda && ($meeting["mee_status"] != "closed")) { ?>
			<button class="btn btn-default btn-add-point" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" style="width: 100%;">Point <span class="fa fa-list-alt"></span></button>
<?php	} ?>

			
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
