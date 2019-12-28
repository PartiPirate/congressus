<?php /*
    Copyright 2015-2019 Cédric Levieux, Parti Pirate

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

require_once("engine/bo/GuestBo.php");
require_once("engine/bo/TagBo.php");
require_once("engine/utils/bootstrap_forms.php");

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

$tagBo = TagBo::newInstance($connection, $config);
$tags = $tagBo->getByFilters();

$hasChat = false;
$leftColumn = 3;
$mainColumn = 9;
$rightColumn = 0;

if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {
	$hasChat = true;
	$mainColumn = 6;
	$rightColumn = 3;
}

?>

<div class=" theme-showcase meeting" role="main"
	style="margin-left: 32px; margin-right: 32px; "
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	data-speaking-id="-1"
	>
	<ol class="breadcrumb" style="max-height: 38px;">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active">
			<span id="meeting-label" class="read-data"><?php echo $meeting["mee_label"]; ?></span>
			<input style="display: inline-block; width: 300px; height: 22px; padding: 0 3px; margin-top: -1px;" type="input" class="form-control input-md">
			<button style="display: none;" id="update-meeting-label-btn" type="button" class="btn btn-xs btn-default update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
			<button style="display: none;" id="save-meeting-label-btn" type="button" class="btn btn-xs btn-success save-btn"><i class="fa fa-save" aria-hidden="true"></i></button>
			<button style="display: none;" id="cancel-meeting-label-btn" type="button" class="btn btn-xs btn-danger cancel-btn"><i class="fa fa-close" aria-hidden="true"></i></button>
		</li>
		<li class="pull-right no-crumb" style="margin-left: 5px;">
			<button class="btn btn-xs btn-default show-info-panels-btn active" style="width: 22px;"><i class="fa fa-info"></i></button>
		</li>
		<li class="pull-right no-crumb" style="margin-left: 5px;">
			<button class="btn btn-xs btn-default show-speaking-btn active" style="width: 22px;"><i class="fa fa-comment"></i></button>
		</li>
		<li class="pull-right no-crumb">
			<?php addShareButton("dropdownMeetingShareButton", "btn-primary btn-xs btn-share-meeting", "", $config["server"]["base"] ."meeting.php?id=" . $meeting["mee_id"], $meeting["mee_label"], "congressus"); ?>
		</li>
	</ol>

	<div id="meeting-info-panels" class="row">
		<div class="col-md-6 col-xs-12 col-sm-12 col-lg-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- Start time -->
					<div class="start-date-time" style="min-height: 21px;">
						<span class="glyphicon glyphicon-time"></span> <?php echo lang("meeting_dateStart"); ?>
						<div style="display: inline-block;">
							<span class="mee_start datetime-control">
								<?php echo lang("meeting_the"); ?>
								<span class="date-control">
									<span class="span-date read-data"><?php echo @$start->format(lang("date_format"))?></span>
									<input style="display:none; height: 20px; width: 130px; border-radius: 4px; border: 1px solid;" class="input-date" type="date" value="<?php echo @$start->format("Y-m-d"); ?>" />
								</span>
								<?php echo lang("meeting_at"); ?>
								<span class="time-control">
									<span class="span-time read-data"><?php echo @$start->format(lang("time_format"))?></span>
									<input style="display:none; height: 20px; border-radius: 4px; border: 1px solid;" class="input-time" type="time" value="<?php echo @$start->format("H:i"); ?>" />
								</span>
							</span>
							<button style="display: none; margin-top: -5px;" id="update-start-btn" type="button" class="btn btn-xs btn-default update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
							<button style="display: none; margin-top: -5px;" id="save-start-btn" type="button" class="btn btn-xs btn-success save-btn"><i class="fa fa-save" aria-hidden="true"></i></button>
							<button style="display: none; margin-top: -5px;" id="cancel-start-btn" type="button" class="btn btn-xs btn-danger cancel-btn"><i class="fa fa-close" aria-hidden="true"></i></button>
						</div>
					</div>

					<!-- End time -->
					<div class="end-date-time" style="min-height: 21px;">
						<span class="glyphicon glyphicon-time"></span> <?php echo lang("meeting_dateEnd"); ?>
						<div style="display: inline-block;">
							<span class="mee_finish datetime-control">
								<?php echo lang("meeting_the"); ?>
								<span class="date-control">
									<span class="span-date read-data"><?php echo @$end->format(lang("date_format"))?></span>
									<input style="display:none; height: 20px; width: 130px; border-radius: 4px; border: 1px solid;" class="input-date" type="date" value="<?php echo @$end->format("Y-m-d"); ?>" />
								</span>
								<?php echo lang("meeting_at"); ?>
								<span class="time-control">
									<span class="span-time read-data"><?php echo @$end->format(lang("time_format"))?></span>
									<input style="display:none; height: 20px; border-radius: 4px; border: 1px solid;" class="input-time" type="time" value="<?php echo @$end->format("H:i"); ?>" />
								</span>
							</span>
							<button style="display: none; margin-top: -5px;" id="update-end-btn" type="button" class="btn btn-xs btn-default update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
							<button style="display: none; margin-top: -5px;" id="save-end-btn" type="button" class="btn btn-xs btn-success save-btn"><i class="fa fa-save" aria-hidden="true"></i></button>
							<button style="display: none; margin-top: -5px;" id="cancel-end-btn" type="button" class="btn btn-xs btn-danger cancel-btn"><i class="fa fa-close" aria-hidden="true"></i></button>
						</div>
					</div>

				</div>
			</div>
		</div>
		<div class="col-md-6 col-xs-12 col-sm-12 col-lg-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- President -->
					<div class="president" style="height: 21px;">
						<span class="glyphicon glyphicon-education" style=""></span> <?php echo lang("meeting_president"); ?>
						<span class="mee_president_member_id read-data" data-id="0" style="display: inline-block; min-width: 1px;"></span>
						<select class="form-control" data-type="president" style="margin-top: -7px; height: 20px; padding: 0px 3px; width: calc(100% - 22px - 162px);">
							<option value="0"></option>
							<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
							<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
							<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
							<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
						</select>
						<button style="display: none; margin-top: -5px;" id="update-president-btn" type="button" class="btn btn-xs btn-default update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
						<button style="display: none; margin-top: -5px;" id="cancel-president-btn" type="button" class="btn btn-xs btn-danger cancel-btn"><i class="fa fa-close" aria-hidden="true"></i></button>
					</div>

					<!-- Secretary -->
					<div class="secretary" style="height: 21px;">
						<span class="glyphicon glyphicon-user" style=""></span> <?php echo lang("meeting_secretary"); ?>
						<span class="mee_secretary_member_id read-data" data-id="0" style="display: inline-block; min-width: 1px;"></span>
						<select class="form-control" data-type="secretary" style="margin-top: -7px; height: 20px; padding: 0px 3px; width: calc(100% - 22px - 166px);">
							<option value="0"></option>
							<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
							<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
							<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
							<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
						</select>
						<button style="display: none; margin-top: -5px;" id="update-secretary-btn" type="button" class="btn btn-xs btn-default update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
						<button style="display: none; margin-top: -5px;" id="cancel-secretary-btn" type="button" class="btn btn-xs btn-danger cancel-btn"><i class="fa fa-close" aria-hidden="true"></i></button>
					</div>
					
				</div>
			</div>
		</div>
		<div class="col-md-6 col-xs-12 col-sm-12 col-lg-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- Location -->
					<div id="location" style="height: 21px;" data-type="<?php echo $meeting["loc_type"];?>">
						<span class="glyphicon glyphicon-map-marker"></span> <?php echo lang("createMeeting_place"); ?>
						<span class="location-type"><?php echo $meeting["loc_type"];?></span>
						<button style="display: none; margin-top: -5px;" type="button" class="btn btn-xs btn-default update-meeting-location-btn update-btn"><i class="fa fa-pencil" aria-hidden="true"></i></button>
					</div>

					<?php if (($meeting["loc_type"] == "mumble") AND ($meeting["loc_channel"] !== "")) {?>
						<!-- Mumble -->
						<div id="location-mumble">
							<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_mumblePlace"); ?>
							<?php
							include("config/mumble.structure.php");
							$mumble_channel = $meeting["loc_channel"];
							$mumble_link = "mumble://" . $mumble_server . "/" . $mumble[$mumble_channel] . "?title=" . $mumble_title . "&version=" . $mumble_version;
							echo "<a href='$mumble_link' target='_blank'>$mumble_channel</a>";
							?>
						</div>
						<div id="location-discord" style="display: none;">
							<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_discordPlace"); ?>

							<span class='discord-text'><i class='fa fa-hashtag' aria-hidden='true'></i> <a href='#' target='_blank'></a></span>
							<span class='discord-vocal'><i class='fa fa-volume-up' aria-hidden='true'></i> <a href='#' target='_blank'></a></span>
						</div>
					<?php }
						  else if (($meeting["loc_type"] == "discord") AND ($meeting["loc_channel"] !== "")) {?>
						<!-- Discord -->
						<div id="location-mumble" style="display:none;">
							<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_mumblePlace"); ?>
							<a href='#' target='_blank'></a>
						</div>
						<div id="location-discord">
							<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_discordPlace"); ?>
							<?php
							include("config/discord.structure.php");
			
							list($discord_text_channel, $discord_vocal_channel) = explode(",", $meeting["loc_channel"]);
							
							$discord_text_link = @$discord_text_channels[$discord_text_channel];
							$discord_vocal_link = @$discord_vocal_channels[$discord_vocal_channel];
							
							echo "<span class='discord-text'><i class='fa fa-hashtag' aria-hidden='true'></i> <a href='$discord_text_link' target='_blank'>$discord_text_channel</a></span> ";
							echo "<span class='discord-vocal'><i class='fa fa-volume-up' aria-hidden='true'></i> <a href='$discord_vocal_link' target='_blank'>$discord_vocal_channel</a></span>";
							
							?>
						</div>
					<?php }
						  else {?>
						<div id="location-mumble" style="display:none;">
							<span class="glyphicon glyphicon-link"></span> <span class="mumble-place"></span>
							<a href='#' target='_blank'></a>
						</div>
						<div id="location-discord" style="display: none;">
							<span class="glyphicon glyphicon-link"></span> <?php echo lang("createMeeting_discordPlace"); ?>

							<span class='discord-text'><i class='fa fa-hashtag' aria-hidden='true'></i> <a href='#' target='_blank'></a></span>
							<span class='discord-vocal'><i class='fa fa-volume-up' aria-hidden='true'></i> <a href='#' target='_blank'></a></span>
						</div>
					<?php } ?>

				</div>
			</div>
		</div>
		<div class="col-md-6 col-xs-12 col-sm-12 col-lg-3">
			<?php 
				$panelClass = "panel-default";
				switch ($meeting["mee_status"]) {
					case "construction":
						$panelClass = "panel-info";
						break;
					case "waiting":
						$panelClass = "panel-primary";
						break;
					case "open":
						$panelClass = "panel-success";
						break;
					case "template":
						$panelClass = "panel-warning";
						break;
					case "closed":
						$panelClass = "panel-warning";
						break;
					case "deleted":
						$panelClass = "panel-danger";
						break;
				}
			?>
			<div class="panel <?php echo $panelClass;?>" id="meeting-state-panel">
				<div class="panel-heading"></div>
				<div class="panel-body">
					<!-- End time -->
					<div class="meeting-type">
						<span class="glyphicon glyphicon-flag" style=""></span> <?php //echo lang("createMeeting_base_type"); ?>
						<?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?>
					</div>
					<div id="meeting-status-panel">
						<div>
							<button style="display: none;" class="margin-top-5 btn btn-primary btn-waiting-meeting"><?php  echo lang("meeting_waiting"); ?></button>
							<button style="display: none;" class="margin-top-5 btn btn-danger  btn-delete-meeting"><?php   echo lang("meeting_delete"); ?></button>
							<button style="display: none;" class="margin-top-5 btn btn-success btn-open-meeting"><?php     echo lang("meeting_open"); ?></button>
							<button style="display: none;" class="margin-top-5 btn btn-danger  btn-close-meeting"><?php    echo lang("meeting_close"); ?></button>
						</div>
						<span style="display: none;" class="closed-meeting"><?php echo lang("meeting_closed"); ?></span>
					</div>
				</div>
			</div>
		</div>

	</div>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<div class="container otbHidden">
</div>

<style>
@media (min-width: 1024px) {
	#change-location-modal .modal-dialog {
	    width: 900px;
	}
}

@media (min-width: 1600px) {
	#change-location-modal .modal-dialog {
	    width: 1300px;
	}
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="change-location-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo lang("meeting_change_location_title"); ?>...</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal">
<?php	include("location_form.php");	?>					
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
				<button type="button" class="btn btn-primary btn-save-location"><?php echo lang("common_save"); ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
</script>
<?php include("footer.php");?>
<script>
$("#start-meeting-modal").modal({
	  keyboard: false
//	  ,
//	  show: true
	});
$("#start-meeting-modal").modal("show");

var meeting_id = "<?php echo $meeting["mee_id"]; ?>";
</script>

<!-- jointjs -->

<link rel="stylesheet" type="text/css" href="assets/css/jointjs/joint.min.css"></script>
<script src="assets/js/jointjs/lodash.js"></script>
<script src="assets/js/jointjs/backbone.js"></script>
<script src="assets/js/jointjs/joint.min.js"></script>

<!-- meeting -->

<script src="assets/js/perpage/meeting.js?<?=filemtime("assets/js/perpage/meeting.js")?>"></script>
<script src="assets/js/perpage/meeting_time.js?<?=filemtime("assets/js/perpage/meeting_time.js")?>"></script>
<script src="assets/js/perpage/meeting_agenda.js?<?=filemtime("assets/js/perpage/meeting_agenda.js")?>"></script>
<script src="assets/js/perpage/meeting_people.js?<?=filemtime("assets/js/perpage/meeting_people.js")?>"></script>
<script src="assets/js/perpage/meeting_motion.js?<?=filemtime("assets/js/perpage/meeting_motion.js")?>"></script>
<script src="assets/js/perpage/meeting_timer.js?<?=filemtime("assets/js/perpage/meeting_timer.js")?>"></script>
<script src="assets/js/perpage/meeting_forms.js?<?=filemtime("assets/js/perpage/meeting_forms.js")?>"></script>
<script src="assets/js/perpage/location_form.js?<?=filemtime("assets/js/perpage/location_form.js")?>"></script>

<script>
var mumble_count = "<?php echo count($mumble);?>";
var mumble_server = "<?php echo $mumble_server;?>";
var mumble_title = "<?php echo $mumble_title;?>";
var mumble_version = "<?php echo $mumble_version;?>";
</script>

<script type="text/javascript">
var userLanguage = '<?php echo SessionUtils::getLanguage($_SESSION); ?>';
/* global judgmentVoteIsMandatory */

var defaultPropositions = {yesno: [], proagainst: []};

defaultPropositions.proagainst.push(<?=json_encode(lang("common_proposition_pro"))?>);
defaultPropositions.proagainst.push(<?=json_encode(lang("common_proposition_against"))?>);
defaultPropositions.yesno.push(<?=json_encode(lang("common_proposition_yes"))?>);
defaultPropositions.yesno.push(<?=json_encode(lang("common_proposition_no"))?>);

judgmentVoteIsMandatory = <?=json_encode(isset($config["congressus"]["ballot_majority_judgment_force"]) ? $config["congressus"]["ballot_majority_judgment_force"] : false); ?>;

var common_edit  = <?=json_encode(lang("common_edit"))?>;
var common_close = <?=json_encode(lang("common_close"))?>;

var meeting_speakingAsk           = <?=json_encode(strtolower(lang("meeting_speakingAsk")))?>;
var meeting_speaking              = <?=json_encode(lang("meeting_speaking"))?>;
var meeting_speakingRenounce      = <?=json_encode(lang("meeting_speakingRenounce"))?>;
var meeting_arrival               = <?=json_encode(lang("meeting_arrival"))?>;
var meeting_left                  = <?=json_encode(lang("meeting_left"))?>;
var meeting_votePower             = <?=json_encode(lang("meeting_votePower"))?>;
var meeting_notification          = <?=json_encode(lang("meeting_notification"))?>;
var meeting_notificationDelete    = <?=json_encode(lang("meeting_notificationDelete"))?>;
var meeting_motionVote2 		  = <?=json_encode(lang("meeting_motionVote2"))?>;
var meeting_vote				  = <?=json_encode(lang("meeting_vote"))?>;
var meeting_motionDelete		  = <?=json_encode(lang("meeting_motionDelete"))?>;
var meeting_taskDelete			  = <?=json_encode(lang("meeting_taskDelete"))?>;
var meeting_taskEnd 			  = <?=json_encode(lang("meeting_taskEnd"))?>;
var meeting_taskCancel			  = <?=json_encode(lang("meeting_taskCancel"))?>;
var meeting_chatDelete			  = <?=json_encode(lang("meeting_chatDelete"))?>;
var meeting_conclusionDelete	  = <?=json_encode(lang("meeting_conclusionDelete"))?>;
var meeting_proposalDelete        = <?=json_encode(lang("meeting_proposalDelete"))?>;
var meeting_taskEnd_conclusion    = <?=json_encode(lang("meeting_taskEnd_conclusion"))?>;
var meeting_taskCancel_conclusion = <?=json_encode(lang("meeting_taskCancel_conclusion"))?>;
var motion_expired                = <?=json_encode(lang("motion_expired"))?>;

var majority_judgement_values	  = <?=json_encode($config["congressus"]["ballot_majority_judgment"])?>

var speakingTimesChartTitle = "Temps de parole par personne";
var motionDelegationsTitle  = "Délégations en jeu";

var tags = <?=json_encode($tags)?>;

isWriting = <?=json_encode(lang("meeting_user_is_writing", false))?>; 

<?php

$translatons = array();
foreach($config["congressus"]["ballot_majority_judgment"] as $value) {
	$translatons[] = lang("motion_majorityJudgment_" . $value, false);
}

?>

var majority_judgement_translations = <?=json_encode($translatons)?>


<?php

$translatons = array();
$translatons[] = lang("motion_approval_" . 1, false);
$translatons[] = lang("motion_approval_" . 2, false);

?>

var approval_translations = <?php echo json_encode($translatons); ?>

var isPeopleReady = false;
var isAgendaReady = false;

var initAgenda = function() {
	var hash = window.location.hash;

	if (!hash) return;

	hash = hash.substring(1);

	if (!hash) return;

	parts = hash.split("|");

	for(var i = 0; i < parts.length; i++) {
		var part = parts[i];
		var subs = part.split("-");

		if (subs.length != 2) continue;

		switch(subs[0]) {
			case "agenda":
				$("#" + part + " a").click();
				break;
		}
	}
};

var initObject = function() {
	var hash = window.location.hash;

	if (!hash) return;

	hash = hash.substring(1);

	if (!hash) return;

	parts = hash.split("|");

	for(var i = 0; i < parts.length; i++) {
		var part = parts[i];
		var subs = part.split("-");

		if (subs.length != 2) continue;

		switch(subs[0]) {
			case "chat":
			case "conclusion":
			case "motion":
			case "proposition":
			case "task":
				$("#agenda_point ul").scrollTo("#" + part, 800);
				break;
		}
	}
};

</script>

<?php
if (isset($framachan)) {?>

	<style>
		.ui-resizable-helper { border: 1px dotted gray; }
	</style>

	<div id="framatalk" class="resizable" >
		<iframe src="https://framatalk.org/<?php echo $framachan; ?>" />
	</div>
<!--
	<iframe id="framatalk" src="framatalk.php?channel=<?php echo $framachan; ?>" style=""/>
 -->

<?php
} ?>

</body>
</html>
