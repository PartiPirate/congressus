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
include_once("header.php");

require_once("engine/bo/GuestBo.php");
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
		$nickname = "Guest $guestId";

		$_SESSION["guestId"] = $guestId;
		$_SESSION["guestNickname"] = $nickname;
	}
	$guestId = $_SESSION["guestId"];
}

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
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active">
			<?php echo $meeting["mee_label"]; ?>
		</li>
		<li class="pull-right no-crumb">
			<?php addShareButton("dropdownMeetingShareButton", "btn-primary btn-xs btn-share-meeting", "", $config["server"]["base"] ."meeting.php?id=" . $meeting["mee_id"], $meeting["mee_label"], "congressus"); ?>
		</li>
	</ol>

	<div class="row">
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- Start time -->
					<div>
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

					<!-- End time -->
					<div>
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

				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- President -->
					<div class="president">
						<span class="glyphicon glyphicon-education" style=""></span> <?php echo lang("meeting_president"); ?>
						<span class="mee_president_member_id read-data" data-id="0"></span>
						<select class="form-control" data-type="president" style="margin-top: -7px; height: 20px; padding: 0px 3px;">
							<option value="0"></option>
							<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
							<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
							<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
							<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
						</select>
					</div>

					<!-- Secretary -->
					<div class="secretary">
						<span class="glyphicon glyphicon-user" style=""></span> <?php echo lang("meeting_secretary"); ?>
						<span class="mee_secretary_member_id read-data" data-id="0"></span>
						<select class="form-control" data-type="secretary" style="margin-top: -7px; height: 20px; padding: 0px 3px;">
							<option value="0"></option>
							<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
							<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
							<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
							<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
						</select>
					</div>
					
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading"></div>
				<div class="panel-body">

					<!-- Location -->
					<div>
						<span class="glyphicon glyphicon-map-marker"></span> <?php echo lang("createMeeting_place"); ?>
						<?php echo $meeting["loc_type"];?>
					</div>

					<?php if (($meeting["loc_type"] == "mumble") AND ($meeting["loc_channel"] !== "")) {?>
						<!-- Mumble -->
						<div >
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
						<!-- Discord -->
						<div>
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

				</div>
			</div>
		</div>
		<div class="col-md-3">
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

					<!-- Synchro vote -->
					<div class="synchro-vote" style="/*padding-top: 7px; padding-bottom: 7px;*/">
						<span class="fa fa-archive"></span> 
							<span class="synchro-vote-option synchro-vote-0" style="display: none;"><?php echo lang("mee_synchro_vote_0"); ?></span>
							<span class="synchro-vote-option synchro-vote-1" style="display: none;"><?php echo lang("mee_synchro_vote_1"); ?></span>
			
						<select class="form-control" data-type="mee_synchro_vote" style="margin-top: -7px; height: 20px; padding: 0px 3px;">
							<option value="0"><?php echo lang("mee_synchro_vote_0"); ?></option>
							<option value="1"><?php echo lang("mee_synchro_vote_1"); ?></option>
						</select>
						
						<a		class="btn btn-primary btn-xs btn-vote-meeting" style="margin-top: -3px;" 
							href="myVotes.php?meetingId=<?php echo $meeting["mee_id"]; ?>" target="_blank"><?php echo lang("meeting_voteExternal"); ?> <span class="glyphicon glyphicon-new-window" style="font-size: 10px;"></span></a>
						<button class="btn btn-default btn-local-anonymous btn-xs" style="margin-top: -3px; margin-left: -2px;" data-toggle="tooltip" data-placement="bottom"
							title="<?php echo lang("meeting_hideVotes"); ?>"><?php echo lang("meeting_noInfluence"); ?> <span class="fa fa-archive"></span>
						</button>
					</div>

				</div>
			</div>
		</div>
	</div>

	<div class="row president-panels" style="margin-bottom: 5px; ">
		<div class="col-md-8">
			<div id="speaking-panel" class="panel panel-default">
				<div class="panel-heading">
					<button class="btn btn-default request-speaking pull-right" style="margin-top: -7px;"><?php echo lang("meeting_speakingAsk"); ?>
						<span class="fa fa-hand-paper-o"></span>
						<span class="badge" style="display: none;"></span>
					</button>

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
				<div class="panel-footer text-right">
					<span class="glyphicon glyphicon-list-alt"></span> <?php echo lang("meeting_number_of_presents"); ?>
					<span class="number-of-presents">0</span>
					-
					<i class="fa fa-archive"></i> <?php echo lang("meeting_number_of_voters"); ?>
					<span class="number-of-voters">0</span>
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
						<button style="display: none;" class="margin-top-5 btn btn-primary btn-waiting-meeting"><?php echo lang("meeting_waiting"); ?></button>
						<button style="display: none;" class="margin-top-5 btn btn-danger btn-delete-meeting"><?php echo lang("meeting_delete"); ?></button>
						<button style="display: none;" class="margin-top-5 btn btn-success btn-open-meeting"><?php echo lang("meeting_open"); ?></button>
						<button style="display: none;" class="margin-top-5 btn btn-danger btn-close-meeting"><?php echo lang("meeting_close"); ?></button>
					</div>

					<span style="display: none;" class="closed-meeting"><?php echo lang("meeting_closed"); ?></span>

					<br style="display: none;" class="export-br">

					<div class="row">
						<button style="display: none;" data-template="html"			class="margin-top-5 btnShowExport export-link btn btn-success btn-xs"><?php echo lang("export_html"); ?></button>
						<button style="display: none;" data-template="pdf" 			class="margin-top-5 btnShowExport export-link btn btn-success btn-xs"><?php echo lang("export_pdf"); ?></button>
						<button style="display: none;" data-template="markdown"		class="margin-top-5 btnShowExport export-link btn btn-success btn-xs"><?php echo lang("export_wiki"); ?></button>
						<button style="display: none;" data-template="discourse"	class="margin-top-5 btnShowExport export-link btn btn-success btn-xs"><?php echo lang("export_discourse"); ?></button>
					</div>

				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-<?php echo $leftColumn ?>" id="right-panel">
			<div id="meeting-agenda" class="panel panel-default">
				<div class="panel-heading">
					<button class="btn btn-warning btn-xs pull-right btn-agenda-mode" style="display: none; margin-left: 5px;" title="<?php echo lang("meeting_agenda_mode"); ?>" data-toggle="tooltip" data-placement="bottom"><span class="glyphicon glyphicon-book"></span></button>
					<button class="btn btn-primary btn-xs pull-right btn-add-point" style="display: none; margin-left: 5px;" ><span class="glyphicon glyphicon-plus"></span></button>
					<a data-toggle="collapse" data-target="#agenda-points-list" href="#"><?php echo lang("meeting_agenda"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="agenda-points-list">
				</ul>
			</div>

			<div id="noticed-people" class="panel panel-default">
				<div class="panel-heading">
					<button class="btn btn-warning btn-xs pull-right btn-hide-missing"
						title="Montrer / cacher les absents" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-eye-open"></span></button>
					<button class="btn btn-primary btn-xs pull-right btn-add-notice" style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-plus"></span></button>
					<a data-toggle="collapse" data-target="#noticed-people-list" href="#"><?php echo lang("meeting_noticed_people"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="noticed-people-list">
				</ul>
				<div class="panel-footer" style="display: none;">
					<button class="btn btn-primary btn-xs btn-notice-people"><?php echo lang("meeting_notice"); ?> <span class="glyphicon glyphicon-envelope"></span></button>
				</div>
			</div>

			<div id="visitors" class="panel panel-default">
				<div class="panel-heading">
					<a data-toggle="collapse" data-target="#visitors-list" href="#"><?php echo lang("meeting_visitors"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="visitors-list">
				</ul>
			</div>

			<div id="meeting_rights" class="panel panel-default" style="display: none;">
				<div class="panel-heading">
					<a data-toggle="collapse" data-target="#meeting_rights_list" href="#"><?php echo lang("meeting_rights"); ?></a>
				</div>
				<ul class="list-group panel-collapse collapse in" id="meeting_rights_list">
					<li class="list-group-item"><label for="handle_notice_checkbox" class="right-label"><input type="checkbox" id="handle_notice_checkbox" value="handle_notice" class="right" /> <?php echo lang("meeting_rights_noticed"); ?></label></li>
					<li class="list-group-item"><label for="handle_agenda_checkbox" class="right-label"><input type="checkbox" id="handle_agenda_checkbox" value="handle_agenda" class="right" /> <?php echo lang("meeting_rights_topics"); ?></label></li>
					<li class="list-group-item"><label for="handle_motion_checkbox" class="right-label"><input type="checkbox" id="handle_motion_checkbox" value="handle_motion" class="right" /> <?php echo lang("meeting_rights_motion"); ?></label></li>
					<li class="list-group-item"><label for="handle_conclusion_checkbox" class="right-label"><input type="checkbox" id="handle_conclusion_checkbox" value="handle_conclusion" class="right" /> <?php echo lang("meeting_rights_conclusion"); ?></label></li>
				</ul>
			</div>
		</div>
		<div class="col-md-<?php echo $mainColumn; ?>" id="main-panel">
			<div id="tasks" class="panel panel-default">
				<div class="panel-heading">
					<a data-toggle="collapse" class="collapsed" data-target="#tasks-list" href="#"><?php echo lang("meeting_tasks"); ?> <span
						class="badge tasks-counter" style="display: none;">0</span></a>
				</div>
				<ul class="list-group panel-collapse collapse" id="tasks-list">
				</ul>
			</div>

			<div id="agenda_point" class="panel panel-default" data-id="0" style="display: none;">
				<div class="panel-heading">
					<?php echo lang("meeting_agenda_point"); ?><span class="agenda-label"></span>
					<button class="btn btn-default btn-xs pull-right btn-go-down"
						title="Descendre" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><i class="fa fa-chevron-down" aria-hidden="true"></i></button>
					<button class="btn btn-default btn-xs pull-right btn-next-point"
						title="Point suivant" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-chevron-right"></span></button>
					<button class="btn btn-default btn-xs pull-right btn-previous-point"
						title="Point précédent" data-toggle="tooltip" data-placement="bottom"
						style="display: none; margin-left: 5px;"><span class="glyphicon glyphicon-chevron-left"></span></button>
				</div>
				<ul class="list-group objects">
				</ul>
				<div class="panel-footer">
					<div class="form-group">
					  	<div class="col-md-12">                     
					    	<textarea class="form-control" id="starting-text" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5"></textarea>
						</div>
					</div>
					<div class="clearfix"></div>
					<div style="margin-top: 5px;">
						<button class="btn btn-default btn-xs btn-add-chat disabled"><?php echo lang("meeting_chat"); ?> <span class="fa fa-comment"></span></button>
						<button class="btn btn-default btn-xs btn-add-motion disabled"><?php echo lang("meeting_motion"); ?> <span class="fa fa-archive"></span></button>
						<button class="btn btn-default btn-xs btn-add-task disabled"><?php echo lang("meeting_task"); ?> <span class="fa fa-tasks"></span></button>
						<button class="btn btn-default btn-xs btn-add-conclusion disabled"><?php echo lang("meeting_conclusion"); ?> <span class="fa fa-lightbulb-o"></span></button>
					</div>
				</div>
			</div>
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
	<div data-template-id="proposition" id="proposition-${mpr_id}"
			class="template row proposition text-success" data-id="${mpr_id}"
			style="margin: 2px; min-height:22px; display: block;">
		<button class="btn btn-primary btn-xs pull-right btn-vote"
			title="<?php echo lang("meeting_vote"); ?>"
			style="display: none;">
			Vote <span class="glyphicon glyphicon-envelope"></span>
		</button>
		<span class="pull-left fa fa-cube"></span>
		<span class="pull-left proposition-label"></span>
		<button class="btn btn-danger btn-xs pull-right btn-remove-proposition"
			title="<?php echo lang("meeting_proposalDelete"); ?>"
			style="margin-right: 5px; display: none;">
			<span class="glyphicon glyphicon-remove"></span>
		</button>
		<span
			class="pull-right glyphicon glyphicon-pencil"
			title="<?php echo lang("meeting_clicEdit"); ?>"
			style="margin-right: 5px; display: none;"></span>
		<span class="pull-left powers"></span>
		<span class="pull-left"> : </span>
		<ul class="pull-left vote-container">
		</ul>
	</div>

	<form data-template-id="vote-form" action="" class="template form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-md-4 control-label" for="mpr_label"><?php echo lang("meeting_proposal"); ?> :</label>
				<div class="col-md-4">
					<input id="mpr_label" name="mpr_label"
						value="${mpr_label}"
						type="text" disabled="disabled" class="form-control input-md disabled">
				</div>
			</div>

			<!-- Text input-->
			<div class="form-group">
				<label class="col-md-4 control-label" for="vot_power"><?php echo lang("meeting_power"); ?> :</label>
				<div class="col-md-4">
					<input id="vot_power" name="vot_power" type="number"
						class="form-control input-md power" required=""
						value="${vot_power}" min="0" max="${vot_power}">
				</div>
			</div>
		</fieldset>
	</form>

	<form data-template-id="schulze-form" action="" class="template form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-md-4 control-label" for="mpr_label"><?php echo lang("meeting_proposals"); ?> :</label>
				<div class="col-md-4 propositions">

				</div>
			</div>
		</fieldset>
	</form>

	<form data-template-id="majority-judgment-form" action="" class="template form-horizontal">
		<fieldset>
			<div class="form-group">
				<div class="col-md-12 propositions">

				</div>
			</div>
		</fieldset>
	</form>

	<ul>
		<li data-template-id="old-task" id="task-${tas_id}"
			class="template list-group-item task"
			data-id="${tas_id}"
			data-agenda-id="${tas_agenda_id}"
			data-meeting-id="${tas_meeting_id}"
			style="display: block;">

			<button class="btn btn-success btn-xs btn-finish-task pull-right"
				title="<?php echo lang("meeting_taskEnded"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-ok"></span>
			</button>

			<a class="btn btn-info btn-xs btn-link-task pull-right"
				title="<?php echo lang("meeting_taskContext"); ?>"
				style="margin-right: 5px; display: none;"
				target="_blank"
				href="meeting.php?id=${tas_meeting_id}#agenda-${tas_agenda_id}|task-${tas_id}"><span class="glyphicon glyphicon-eye-open"></span></a>

			<span class="fa fa-tasks pull-left"></span>
			<span class="task-label"></span>
		</li>

		<li data-template-id="echat" id="echat-${message_id}"
				class="template list-group-item echat" data-id="${message_id}">
			<img src="${mem_avatar_url}" style="max-height: 20px; max-width: 20px; border-radius: 10px; ">
			<span class="nickname">${mem_nickname}</span> : 
			<span class="message">${message}</span>
		</li>

		<li data-template-id="task" id="task-${tas_id}"
				class="template list-group-item task" data-id="${tas_id}" style="display: block;">
			<button class="btn btn-danger btn-xs btn-remove-task pull-right"
				title="<?php echo lang("meeting_taskDelete"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>

			<span class="glyphicon glyphicon-pencil pull-right"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-tasks pull-left"></span>
			<span class="task-label"></span>
		</li>

		<li data-template-id="chat" id="chat-${cha_id}"
				class="template list-group-item chat" data-id="${cha_id}" style="display: block;">
			<button class="btn btn-danger btn-xs btn-remove-chat pull-right"
				title="<?php echo lang("meeting_chatDelete"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>

			<button class="btn btn-danger btn-xs btn-advice btn-thumb-down pull-right zero"
				data-advice="thumb_down" data-chat-id="${cha_id}"
				title="<?php echo lang("meeting_chatDisapprove"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-thumbs-down"></span>
			</button>

			<button class="btn btn-warning btn-xs btn-advice btn-thumb-middle pull-right zero"
				data-advice="thumb_middle" data-chat-id="${cha_id}"
				title="<?php echo lang("meeting_chatShare"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-hand-left"></span>
			</button>

			<button class="btn btn-success btn-xs btn-advice btn-thumb-up pull-right zero"
				data-advice="thumb_up" data-chat-id="${cha_id}"
				title="<?php echo lang("meeting_chatLike"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-thumbs-up"></span>
			</button>

			<span class="glyphicon glyphicon-pencil pull-right"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-comment pull-left"></span>
			<span class="chat-member"><img src="getAvatar.php?userId=${mem_id}" class="img-circle avatar" style="max-width: 16px; max-height: 16px; position: relative; top: -2px; margin-right: 2px;" 
							 data-toggle="tooltip" data-placement="top" title="${mem_nickname}"><span class="chat-nickname" style="width: none;"></span><select class="chat-select-member" style="display: none;">
				<optgroup class="voting" label="<?php echo lang("meeting_voters"); ?>"></optgroup>
				<optgroup class="noticed" label="<?php echo lang("meeting_attended"); ?>"></optgroup>
				<optgroup class="connected" label="<?php echo lang("meeting_connected"); ?>"></optgroup>
				<optgroup class="unknown" label="<?php echo lang("meeting_unknown"); ?>"></optgroup>
			</select> </span>
			<span> : </span>
			<span class="chat-text"></span>

			<div class="progress" style="margin-top: 5px; margin-bottom: 0; /*height: 3px;*/">
				<div class="progress-bar progress-bar-danger" role="progressbar"
					data-advice="thumb_down"
					aria-valuenow="0" aria-valuemin="0" aria-valuemax="0"
					style="width: 0%; padding: 1px;"><span class="glyphicon glyphicon-thumbs-down pull-left" style="margin: 2px;"></span>
					<span class="value pull-left"></span>
					<span class="sr-only">&nbsp;</span>
				</div>
				<div class="progress-bar progress-bar-warning text-center" role="progressbar"
					data-advice="thumb_middle"
					aria-valuenow="0" aria-valuemin="0" aria-valuemax="0"
					style="width: 0%; padding: 1px;"><span class="glyphicon glyphicon-hand-left" style="margin: 2px;"></span>
					<span class="value pull-right" style=""></span>
					<span class="sr-only">&nbsp;</span>
				</div>
				<div class="progress-bar progress-bar-success" role="progressbar"
					data-advice="thumb_up"
					aria-valuenow="0" aria-valuemin="0" aria-valuemax="0"
					style="width: 0%; padding: 1px;"><span class="glyphicon glyphicon-thumbs-up pull-right" style="margin: 2px;"></span>
					<span class="value pull-right" style=""></span>
					<span class="sr-only">&nbsp;</span>
				</div>
			</div>

		</li>

		<li data-template-id="conclusion" id="conclusion-${con_id}"
				class="template list-group-item conclusion" data-id="${con_id}" style="display: block;">
			<button class="btn btn-danger btn-xs btn-remove-conclusion pull-right"
				title="<?php echo lang("meeting_conclusionDelete"); ?>"
				style="margin-right: 5px; display:none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>
			<span class="glyphicon glyphicon-pencil pull-right"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-lightbulb-o pull-left"></span>
			<span class="conclusion-text"></span>
		</li>

		<li data-template-id="motion" id="motion-${mot_id}" data-id="${mot_id}" class="template list-group-item motion simply-hidden">
			<h4>
				<span class="fa fa-archive"></span>
				<span class="motion-title"></span>
				<span class="glyphicon glyphicon-pencil"
					title="<?php echo lang("meeting_clicEdit"); ?>"
					style="font-size: smaller; display: none;"></span>
			</h4>

			<div class="motion-description">

				<div class="motion-description-text"></div>
				<span class="glyphicon glyphicon-pencil"
					title="<?php echo lang("meeting_clicEdit"); ?>"
					style="font-size: smaller; display: none;"></span>
			</div>

			<div class="motion-propositions">
			</div>
			<div class="motion-actions">
				<button class="btn btn-primary btn-xs btn-add-proposition"
					title="<?php echo lang("meeting_proposalAdd"); ?>"
					style="display: none;">
					<?php echo lang("meeting_proposal"); ?>&nbsp;<span class="glyphicon glyphicon-plus"></span>
				</button>

				<div id="motionLimitsButtons" class="btn-group" role="group">
<?php foreach($config["congressus"]["ballot_majorities"] as $majority) {
	// Development condition
	if (($userId != 12 && $userId != 1) && $majority < -2) continue;
?>
					<button value="<?php echo $majority; ?>" type="button" style="display: none;"
						class="btn btn-default btn-xs btn-motion-limits btn-motion-limit-<?php echo $majority; ?>"><?php echo lang("motion_ballot_majority_$majority"); ?></button>
<?php }?>
				</div>

				<button value="0" type="button" class="btn btn-default btn-xs btn-motion-anonymous"><?php echo lang("meeting_voteAnon"); ?></button>

				<button class="btn btn-success btn-xs btn-do-vote"
					title="<?php echo lang("meeting_motionVote"); ?>"
					style="display: none;">
					<?php echo lang("meeting_voteNow"); ?>&nbsp;<span class="fa fa-archive"></span>
				</button>
				<button class="btn btn-danger btn-xs btn-remove-motion"
					title="<?php echo lang("meeting_motionDelete"); ?>"
					style="display: none;">
					<?php echo lang("meeting_motionDelete"); ?>&nbsp;<span class="glyphicon glyphicon-remove"></span>
				</button>
				<button class="btn btn-danger btn-xs btn-do-close"
					title="<?php echo lang("meeting_motionCloseVote"); ?>"
					style="display: none;">
					<?php echo lang("meeting_voteClose"); ?>&nbsp;<span class="fa fa-archive"></span>
				</button>
				
				<button class="btn btn-info btn-xs btn-see-motion-stats pull-right"
					titleeee="<?php echo lang("meeting_speakingStats"); ?>"
					style="margin-left: 5px;"><i class="fa fa-line-chart" aria-hidden="true"></i></span>
				</button>

				<span class="simply-hidden voters badge pull-right">
					<span class="number-of-voters">XX</span> <?php echo lang("meeting_voters"); ?>
				</span>
			</div>
			<div class="motion-charts" data-status="to-init" style="display: none;">
			</div>
		</li>

		<li data-template-id="vote" class="template vote"
			id="vote-${vot_id}" data-id="${vot_id}"
			data-proposition-id="${vot_motion_proposition_id}"
			data-member-id="${vot_member_id}">
			<span class="nickname"></span>
			<span
				title="<?php echo lang("meeting_votePower"); ?>"
				class="badge power"></span>
		</li>

		<li data-template-id="agenda-point" id="agenda-${age_id}" class="template list-group-item"
				style="padding-top: 2px; padding-bottom: 2px;" data-id="${age_id}">
			<a class="agenda-link" style="margin: 0;" href="#" id="agenda-link-${age_id}" data-id="${age_id}"></a>
			<span class="fa fa-archive to-vote"
				title="<?php echo lang("meeting_motionHas"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<button class="btn btn-primary btn-xs btn-add-point" data-parent-id="${age_id}"
				title="<?php echo lang("meeting_topicAdd"); ?>"
				style="margin-right: 5px; display: none;">
				<span class="glyphicon glyphicon-plus"></span>
			</button>
			<button class="btn btn-danger btn-xs btn-remove-point" data-id="${age_id}"
				title="<?php echo lang("meeting_topicDelete"); ?>"
				style="display: none;">
				<span class="glyphicon glyphicon-remove"></span>
			</button>
			<ul class="list-group points" style="margin: 0;"></ul>
		</li>

		<li data-template-id="me-member"
			class="template list-group-item member"
			style="padding-top: 2px; padding-bottom: 2px;"
			id="member-${mem_id}" data-id="${mem_id}">

			<img src="getAvatar.php?userId=${mem_id}" class="img-circle" style="max-width: 20px; max-height: 20px;" 
							 data-toggle="tooltip" data-placement="top" title="${mem_nickname}">
							 
			<span class="member-nickname" style="margin-right: 5px;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-commenting-o"
				title="<?php echo lang("meeting_speaking"); ?>"
				style="display: none;"></span>
			<button
				data-id="${mem_id}"
				title="<?php echo lang("meeting_speakingAsk"); ?>"
				class="btn btn-default btn-xs request-speaking">
				<span class="fa fa-hand-paper-o"></span><span class="badge"
					style="display: none;">0</span>
			</button>

			<button
				data-id="${mem_id}"
				title="<?php echo lang("meeting_speakingSet"); ?>"
				class="btn btn-default btn-xs set-speaking">
				<span class="fa fa-commenting-o"></span>
			</button>

			<span class="fa fa-archive voting"
				title="<?php echo lang("meeting_rightsVote"); ?>"
				style="display: none;">
				<span style="margin-left: 5px;">
					<span class="power">1</span><span class="fa fa-ils"></span>
				</span>
			</span>
		</li>

		<li data-template-id="member"
			class="template list-group-item member"
			style="padding-top: 2px; padding-bottom: 2px;"
			id="member-${mem_id}" data-id="${mem_id}">

			<img src="getAvatar.php?userId=${mem_id}" class="img-circle" style="max-width: 20px; max-height: 20px;" 
				 data-toggle="tooltip" data-placement="top" title="${mem_nickname}">

			<span class="member-nickname" style="margin-right: 5px;"></span>
			<span class="glyphicon glyphicon-pencil"
				title="<?php echo lang("meeting_clicEdit"); ?>"
				style="margin-right: 5px; display: none;"></span>
			<span class="fa fa-commenting-o"
				title="<?php echo lang("meeting_speaking"); ?>"
				style="display: none;"></span>
			<span class="fa fa-hand-paper-o btn-xs"
				title="<?php echo lang("meeting_speakingAsk"); ?>"
				style="display: none;">
				<span class="badge">0</span>
			</span>

			<button
				data-id="${mem_id}"
				title="<?php echo lang("meeting_speakingSet"); ?>"
				class="btn btn-default btn-xs set-speaking">
				<span class="fa fa-commenting-o"></span>
			</button>

			<span class="fa fa-archive voting"
				title="<?php echo lang("meeting_rightsVote"); ?>"
				style="display: none;">
				<span style="margin-left: 5px;">
					<span class="power">10</span><span class="fa fa-ils"></span>
				</span>
			</span>
		</li>

	</ul>

	<div data-template-id="judgementProposition" class="proposition" style="width: 100%; border-radius: 4px; margin-top: 5px; margin-bottom: 5px;" data-id="${mpr_id}" data-power="0">
		${mpr_label}
		<div class="btn-group" style="width: 100%; margin: 2px;">
			<?php 	$nbItems = count($config["congressus"]["ballot_majority_judgment"]);
					foreach($config["congressus"]["ballot_majority_judgment"] as $judgeIndex => $judgementMajorityItem) {?>
				<div class="btn btn-default judgement" style="width: <?php echo 100 / $nbItems; ?>%; background: hsl(<?php echo 120 * (0 + ($judgeIndex / ($nbItems - 1))); ?>, 70%, 70%);" type="button" data-power="<?php echo $judgementMajorityItem; ?>"><?php echo lang("motion_majorityJudgment_" . $judgementMajorityItem); ?></div>
			<?php	} ?>
		</div>
	</div>


</templates>

<div id="exportModal"></div>

<div class="modal fade" tabindex="-1" role="dialog" id="start-meeting-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
<!--
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
 -->
        <h4 class="modal-title"><?php echo lang("meeting_start"); ?>...</h4>
      </div>
      <div class="modal-body">
        <p><?php echo lang("meeting_preparation"); ?></p>
      </div>
      <div class="modal-footer">
<!--
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
 -->
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
<script src="assets/js/perpage/meeting_time.js"></script>
<script src="assets/js/perpage/meeting_agenda.js"></script>
<script src="assets/js/perpage/meeting_people.js"></script>
<script src="assets/js/perpage/meeting_motion.js"></script>
<script src="assets/js/perpage/meeting_events.js"></script>
<script src="assets/js/perpage/meeting_timer.js"></script>
<script src="assets/js/perpage/meeting_export.js"></script>
<script src="assets/js/perpage/meeting_charts.js"></script>

<script type="text/javascript">
var userLanguage = '<?php echo SessionUtils::getLanguage($_SESSION); ?>';
/* global judgmentVoteIsMandatory */

judgmentVoteIsMandatory = <?php echo json_encode(isset($config["congressus"]["ballot_majority_judgment_force"]) ? $config["congressus"]["ballot_majority_judgment_force"] : false); ?>;

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
