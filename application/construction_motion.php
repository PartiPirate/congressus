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
require_once("engine/bo/VoteBo.php");
require_once("engine/bo/ChatBo.php");
require_once("engine/bo/ChatAdviceBo.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/NoticeBo.php");
require_once("engine/bo/UserBo.php");
require_once("engine/bo/SourceBo.php");
require_once("engine/utils/Parsedown.php");
require_once("engine/emojione/autoload.php");

$Parsedown = new Parsedown();
$emojiClient = new Emojione\Client(new Emojione\Ruleset());

function showDate($date) {
	$msg = lang("datetime_format");
	$msg = str_replace("{date}", $date->format(lang("date_format")), $msg);
	$msg = str_replace("{time}", $date->format(lang("time_format")), $msg);
	
	return $msg;
}

$hasWritingRights = false;
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
else {
	if ($userId == $meeting["mee_president_member_id"]) {
		$hasWritingRights = true;
	}
	else if ($userId == $meeting["mee_secretary_member_id"]) {
		$hasWritingRights = true;
	}
}

$motionId = intval($_REQUEST["motionId"]);

$agendaBo = AgendaBo::newInstance($connection, $config);
$motionBo = MotionBo::newInstance($connection, $config);
$voteBo   = VoteBo::newInstance($connection, $config);
$chatBo   = ChatBo::newInstance($connection, $config);
$chatAdviceBo = ChatAdviceBo::newInstance($connection, $config);
$noticeBo = NoticeBo::newInstance($connection, $config);
$userBo   = UserBo::newInstance($connection, $config);
$sourceBo = SourceBo::newInstance($connection, $config);

$motion = $motionBo->getByFilters(array("with_meeting" => true, "mot_id" => $motionId));

if (count($motion)) {
	$motion = $motion[0];
	$meeting = $motion;
	
//	print_r($motion);
}
else {
	exit();
}

$agendas = $agendaBo->getByFilters(array("age_id" => $motion["mot_agenda_id"]));

if (count($agendas)) {
	$agenda = $agendas[0];
}
else {
	exit();
}
// print_r($agendas);

$author = null;
if ($motion["mot_author_id"]) {
	$author = $userBo->getById($motion["mot_author_id"]);
}

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

$amendmentAgenda = $agendaBo->getByFilters(array("age_parent_id" => $motion["mot_agenda_id"], "age_label" => "amendments-" . $motion["mot_id"]));
if (count($amendmentAgenda)) {
	$amendmentAgenda = $amendmentAgenda[0];
}
else {
	$amendmentAgenda = array("age_parent_id" => $motion["mot_agenda_id"], "age_label" => "amendments-" . $motion["mot_id"], "age_meeting_id" => $meeting["mee_id"]);
	$amendmentAgenda["age_order"] = time();
	$amendmentAgenda["age_active"] = 0;
	$amendmentAgenda["age_expected_duration"] = 0;
	$amendmentAgenda["age_objects"] = "[]";
	$amendmentAgenda["age_description"] = "Pas de description";

	// create
	$agendaBo->save($amendmentAgenda);
}

$amendments = $motionBo->getByFilters(array("mot_agenda_id" => $amendmentAgenda[$agendaBo->ID_FIELD]));

$previousAmendmentId = null;
$numberOfAmendments = 0;
foreach($amendments as $amendment) {
	if ($previousAmendmentId == $amendment["mot_id"]) continue;
	$previousAmendmentId = $amendment["mot_id"];
	
	$numberOfAmendments++;
}

$amendmentAgenda["age_motions"] = $amendments;

$chats = $chatBo->getByFilters(array("cha_motion_id" => $motion["mot_id"]));
$numberOfChats = array(0, 0, 0);
foreach($chats as $chat) {
	if ($chat["cha_type"] != "pro" && $chat["cha_type"] != "against") continue;
	
	$numberOfChats[0]++;
	
	if ($chat["cha_type"] == "pro") $numberOfChats[1]++;
	if ($chat["cha_type"] == "against") $numberOfChats[2]++;
}

$parentMotion = null;

if ($agenda["age_parent_id"])	 { 
	$parentAgenda = $agendaBo->getById($agenda["age_parent_id"]);
	$parentMotionId = str_replace("amendments-", "", $agenda["age_label"]);
	$parentMotion = $motionBo->getById($parentMotionId);
}

$sources = $sourceBo->getByFilters(array('sou_motion_id' => $motion["mot_id"]));
$numberOfsources = count($sources);

$source = null;

foreach($sources as $src) {
	if ($src["sou_is_default_source"]) $source = $src;
	break;
}

$mainColumn = 12;

?>

<style>

#diff del, #motion-description del {
	color: #a94442;
	background-color: #f2dfde;
}

#diff ins, #motion-description ins {
	color: #3c763d;
	background-color: #ddeedd;
	text-decoration: none;
}

#diff, #motion-description, #source, #destination {
	max-height: 300px;
	overflow-y: scroll;
}

#diff, #motion-description {
	width: calc(100% - 20px);
/*	background: #d0d0ff;	*/
}

.change-scroll {
	border: #ccc 1px solid;
	width: 18px;
/*	margin-top:-1px;*/
/*	margin-bottom:-1px;*/
	margin-right: 2px;
/*	background: #ffd0d0;	*/
	float: left;
	position: relative;
}

.scroll-zone {
	position: relative;
	width: 16px;
	background: #eee;
	float: left;
}

.inserted {
	border-radius: 4px;
	border: #3c763d 1px solid;
	background-color: #ddeedd;
	height: 8px;
	width: 8px;
	margin: 0 -8px -8px 0;
	cursor: zoom-in;
}

.deleted {
	border-radius: 4px;
	border: #a94442 1px solid;;
	background-color: #f2dfde;
	height: 8px;
	width: 8px;
	margin: 0 -8px -8px 0;
	cursor: zoom-in;
}

#markdown-area h1 {
	padding-left: calc(1 * 5px);
	text-decoration: underline;
}

#markdown-area h2 {
	padding-left: calc(2 * 5px);
	text-decoration: underline;
}

#markdown-area h3 {
	padding-left: calc(3 * 5px);
	text-decoration: underline;
}

#markdown-area h4 {
	padding-left: calc(4 * 5px);
	text-decoration: underline;
}

#markdown-area h5 {
	padding-left: calc(5 * 5px);
	text-decoration: underline;
}

#markdown-area h6 {
	padding-left: calc(6 * 5px);
	text-decoration: underline;
}

#markdown-area h7 {
	padding-left: calc(7 * 5px);
}

.children-chat {
    margin-left: 50px;
    background-color: #f8f8f8;
}

.pinned {
    background-color: #eee;
}

.help-tip{
    text-align: center;
/*
    background-color: #1f1f1f;
    border-radius: 50%;
    width: 24px;
    height: 24px;
*/    
    font-size: 14px;
    line-height: 26px;
    cursor: default;
    display: inline-block;
    position: relative;
    font-family: 'Glyphicons Halflings';
}

.help-tip:before{
	content: "\e086";
/*
	content:'i';
    font-weight: bold;
    color:#fff;
*/    
}

.help-tip:hover p{
    display:block;
    transform-origin: 100% 0%;

    -webkit-animation: fadeIn 0.3s ease-in-out;
    animation: fadeIn 0.3s ease-in-out;

}

.help-tip p{    /* The tooltip */
	font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
    display: none;
    text-align: left;
    background-color: #1f1f1f;
    padding: 20px;
    width: 300px;
    position: absolute;
    border-radius: 3px;
    box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
    right: -4px;
    color: #FFF;
    font-size: 13px;
    line-height: 1.4;
	left: -277px;
    top: 27px; 
}

.help-tip p:before{ /* The pointer of the tooltip */
    position: absolute;
    content: '';
    width:0;
    height: 0;
    border:6px solid transparent;
    border-bottom-color:#1E2021;
    right:10px;
    top:-12px;
}

.help-tip p:after{ /* Prevents the tooltip from being hidden */
    width:100%;
    height:40px;
    content:'';
    position: absolute;
    top:-40px;
    left:0;
}

/* CSS animation */

@-webkit-keyframes fadeIn {
    0% { 
        opacity:0; 
        transform: scale(0.6);
    }

    100% {
        opacity:100%;
        transform: scale(1);
    }
}

@keyframes fadeIn {
    0% { opacity:0; }
    100% { opacity:100%; }
}
</style>

<div class=" theme-showcase construction-motion" role="main"
	style="margin-left: 32px; margin-right: 32px; "
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	data-speaking-id="-1"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li><a href="construction.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></li>
		
<?php	if ($agenda["age_parent_id"])	 { ?>
		<li><a href="construction.php?id=<?php echo $meeting["mee_id"]; ?>&agendaId=<?php echo $parentAgenda["age_id"]; ?>"><?php echo $parentAgenda["age_label"]; ?></a></li>
		<li><a href="?motionId=<?php echo $parentMotion["mot_id"]; ?>"><?php echo $parentMotion["mot_title"]; ?></a></li>
		<li><a href="?motionId=<?php echo $parentMotion["mot_id"]; ?>#amendments">Amendements</a></li>
<?php	} else { ?>
		<li><a href="construction.php?id=<?php echo $meeting["mee_id"]; ?>&agendaId=<?php echo $agenda["age_id"]; ?>"><?php echo $agenda["age_label"]; ?></a></li>
<?php	} ?>
		<li class="active"><?php echo $motion["mot_title"]; ?></li>
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

			$votes = $voteBo->getByFilters(array("mot_id" => $motionId, "mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
?>			
			<div class="panel panel-default motion-entry" data-id="<?php echo $motion["mot_id"]; ?>">
				<div class="panel-heading">
<?php

					$voteCounters = array(0, 0, 0, 0);
					$votingMembers = array();

					$hasPro = 0;
					$hasDoubt = 0;
					$hasAgainst = 0;

					$voters = array();

					foreach($votes as $vote) {
						if ($motion["mot_id"] != $vote["mot_id"]) continue;
						if (!$vote["vot_power"]) continue;
						if (!isset($voters[$vote["vot_member_id"]])) {
							$voteCounters[0] += 1;
							$voters[$vote["vot_member_id"]] = $vote["vot_member_id"];
						}

//						print_r($vote);

						if ($userId == $vote["vot_member_id"]) {
							if (($vote["mpr_label"] == "pro" || strtolower($vote["mpr_label"]) == "oui" || strtolower($vote["mpr_label"]) == "pour") && $vote["vot_power"]) $hasPro = $vote["vot_power"];
							else if (($vote["mpr_label"] == "doubtful") && $vote["vot_power"]) $hasDoubt = $vote["vot_power"];
							else if (($vote["mpr_label"] == "against" || strtolower($vote["mpr_label"]) == "non" || strtolower($vote["mpr_label"]) == "contre") && $vote["vot_power"]) $hasAgainst = $vote["vot_power"];
						}

						$memberName = GaletteBo::showIdentity($vote);

						$votingMembers[$memberName] = $memberName;

						if ($vote["mpr_label"] == "pro" || strtolower($vote["mpr_label"]) == "oui" || strtolower($vote["mpr_label"]) == "pour") $voteCounters[1] += $vote["vot_power"];
						else if ($vote["mpr_label"] == "doubtful") $voteCounters[2] += $vote["vot_power"];
						else if ($vote["mpr_label"] == "against" || strtolower($vote["mpr_label"]) == "non" || strtolower($vote["mpr_label"]) == "contre") $voteCounters[3] += $vote["vot_power"];
					}

?>
					<div class="pull-right" style="width: 36px; height: 36px; font-size: smaller;" id="mini-voting-panel">

<?php	

$chartId = "mini-voting-panel";
$width = 36;
$height = 36;

include("construction/pieChart.php"); 

?>

					</div>
					<div style="font-size: larger;">
						<p class="text-info" id="motion-title"><?php echo $motion["mot_title"]; ?></p>
					</div>
					<div style="font-size: smaller;">
						<?php 	if ($author) { ?>
							<?php echo GaletteBo::showIdentity($author); ?>
						<?php 	}	?>
					</div>
					<div class="counters" style="font-size: smaller;">
						<?php echo langFormat($voteCounters[0] < 2, "amendments_vote", "amendments_votes", array("vote" => $voteCounters[0])); ?> -
						<?php echo langFormat($numberOfChats[0] < 2, "amendments_argument", "amendments_arguments", array("argument" => $numberOfChats[0])); ?> -
						<?php echo langFormat($numberOfAmendments < 2, "amendments_amendment", "amendments_amendments", array("amendment" => $numberOfAmendments)); ?> -
						<?php echo langFormat($numberOfsources < 2, "amendments_source", "amendments_sources", array("source" => $numberOfsources)); ?>
					</div>
<?php 		
//		} ?>			
				</div>
				<div class="btn-toolbar panel-body" role="toolbar">
					<div class="btn-group btn-type-group " role="group">
						<button id="show-motion-btn" type="button" class="btn btn-default active"><i class="fa fa-archive" aria-hidden="true"></i></button>
						<button id="show-diff-btn" type="button" class="btn btn-default"><i class="fa fa-balance-scale" aria-hidden="true"></i></button>
						<button id="show-markdown-btn" type="button" class="btn btn-default"><i class="fa fa-file-text" aria-hidden="true"></i></button>
						<?php 	if ($motion["mot_author_id"] == $userId) { ?>
						<button id="show-motion-authoring-btn" type="button" class="btn btn-default"><i class="fa fa-pencil" aria-hidden="true"></i></button>
						<?php	} ?>
					</div>
						<?php 	if ($motion["mot_author_id"] == $userId) { ?>
					<div class="btn-group btn-authoring-group" style="display: none;" role="group">
						<button id="show-both-panels-btn" type="button" class="btn btn-default active"><i class="fa fa-arrows-h" aria-hidden="true"></i></button>
						<button id="show-right-panel-btn" type="button" class="btn btn-default"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
						<button id="save-motion-btn" type="button" class="btn btn-success" disabled="disabled" ><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
					</div>
						<?php	} ?>
						<?php 	if ($hasWritingRights) { ?>
					<div class="btn-group btn-admin-group" role="group">
						<button id="btn-pin" type="button" class="btn btn-primary <?php echo $motion["mot_pinned"] ? "active" : ""; ?>" ><span class="glyphicon glyphicon-pushpin"></span></button>
					</div>
						<?php	} ?>
				</div>
				<div class="panel-body">
					<div id="motion-description-group" class="with-scroll">
						<div class="change-scroll"><div class="scroll-zone"></div></div>
						<div id="motion-description" class="scroller" style="position: relative; "><?php echo str_replace("\n", "<br>", $motion["mot_description"]); ?></div>
					</div>
					<textarea class="col-md-6 autogrow" disabled="disabled" style="height: auto;" id="source"><?php 
						echo $parentMotion ? $parentMotion["mot_description"] : ($source ? $source["sou_content"] : ""); ?></textarea>
					<textarea class="col-md-6 autogrow" style="height: auto;" id="destination" ><?php echo $motion["mot_description"]; ?></textarea>
					<div class="clearfix "></div>
					<div id="diff-group" class="with-scroll" style="display: none;">
						<div class="change-scroll"><div class="scroll-zone"></div></div>
						<div id="diff" class="scroller" style="position: relative; display: none;" ></div>
					</div>
					<div id="markdown-group" class="with-scroll" style="display: none;">
						<!--
						<div class="change-scroll"><div class="scroll-zone"></div></div>
						-->
						<div id="markdown-area" class="scroller" style="position: relative; display: none;" ></div>
					</div>

					<hr>
					<div>
						<div class="pull-right" style="width: 72px; height: 72px; font-size: smaller;" id="voting-panel">

<?php	

$chartId = "voting-panel";
$width = 72;
$height = 72;

include("construction/pieChart.php"); 

?>

						</div>
<?php	if (($meeting["mee_status"] != "closed")) { ?>
						<div id="motion-buttons-bar" class="" data-voting-power="<?php echo $votingPower; ?>">
							<form>
								<input type="hidden" name="meetingId" value="<?php echo $meeting["mee_id"]; ?>">
								<input type="hidden" name="motionId"  value="<?php echo  $motion["mot_id"]; ?>">
							<?php	if ($votingPower) { ?>
							<div class="btn btn-success btn-vote <?php echo $hasPro ? "active" : "zero"; ?>" type="button">
								<span class="glyphicon glyphicon-thumbs-up"></span> <?php echo lang("advice_pro"); ?> &nbsp;
								<?php	if ($votingPower > 1) { ?>
									<input type='number' name="pro" class='pull-right text-right' style='width: 50px; color: #000; font-size: smaller;' min='0' max='<?php echo $votingPower; ?>' value="<?php echo $hasPro; ?>">
								<?php	} else {?>
									<input type='hidden' name="pro" value="<?php echo $hasPro; ?>">
								<?php	} ?>
							</div>
							<div class="btn btn-warning btn-vote <?php echo $hasDoubt ? "active" : "zero"; ?>" type="button">
								<span class="glyphicon glyphicon-hand-left"></span> <?php echo lang("advice_doubtful"); ?> &nbsp;
								<?php	if ($votingPower > 1) { ?>
									<input type='number' name="doubtful" class='pull-right text-right' style='width: 50px; color: #000; font-size: smaller;' min='0' max='<?php echo $votingPower; ?>' value="<?php echo $hasDoubt; ?>">
								<?php	} else {?>
									<input type='hidden' name="doubtful" value="<?php echo $hasDoubt; ?>">
								<?php	} ?>
							</div>
							<div class="btn btn-danger btn-vote <?php echo $hasAgainst ? "active" : "zero"; ?>" type="button">
								<span class="glyphicon glyphicon-thumbs-down"></span> <?php echo lang("advice_against"); ?> &nbsp;
								<?php	if ($votingPower > 1) { ?>
									<input type='number' name="against" class='pull-right text-right' style='width: 50px; color: #000; font-size: smaller;' min='0' max='<?php echo $votingPower; ?>' value="<?php echo $hasAgainst; ?>">
								<?php	} else {?>
									<input type='hidden' name="against" value="<?php echo $hasAgainst; ?>">
								<?php	} ?>
							</div>
							<div class="help-tip">
							    <p>Donnez votre avis, dispersez votre pouvoir</p>
							</div>
							<?php		if ($userId == $motion["mot_author_id"] || $userId == $meeting["mee_secretary_member_id"]) {?>
							<div class="btn btn-danger btn-delete-motion" type="button" style="height: 36px;"
								data-motion-id="<?php echo $motion["mot_id"]; ?>" data-agenda-point-id="<?php echo $motion["mot_agenda_id"]; ?>" data-meeting-id="<?php echo $meeting["mee_id"]; ?>">
								<span class="glyphicon glyphicon-remove"></span> <?php echo lang("common_delete"); ?> &nbsp;
							</div>
							<?php		} ?>

							<?php	} ?>
							</form>
						</div>
						<br>
<?php	} ?>						
						<div id="voting-members-panel">
							<?php echo implode(", ", $votingMembers); ?>
						</div>
					</div>
				</div>
<!--
				<div class="panel-footer">
				</div>
-->				
			</div>
<?php 	
//		} 
?>			

			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#arguments" aria-controls="home" role="tab" data-toggle="tab"><?php echo lang("construction_arguments"); ?></a>
				</li>
<?php 				
			if (!$agenda["age_parent_id"]) {
?>		
				<li role="presentation">
					<a href="#amendments" aria-controls="profile" role="tab" data-toggle="tab"><?php echo lang("construction_amendments"); ?></a>
				</li>
<?php 				
			}
?>
				<li role="presentation">
					<a href="#sources" aria-controls="profile" role="tab" data-toggle="tab"><?php echo lang("construction_sources"); ?></a>
				</li>
			</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="arguments" style="padding-top: 15px;">
				<div class="col-md-6 pro-chats">
					<div class="well">
	
						<form class="form-horizontal" data-chat-type="pro">
							<fieldset>
	
								<input type="hidden" name="id" value="<?php echo $motion["mee_id"]; ?>">
								<input type="hidden" name="motionId" value="<?php echo $motion["mot_id"]; ?>">
								<input type="hidden" name="pointId" value="<?php echo $motion["mot_agenda_id"]; ?>">
								<input type="hidden" name="userId" value="<?php echo $userId; ?>">
								<input type="hidden" name="type" class="chat-type" value="pro">
	
								<!-- Form Name -->
								<!--<legend>Form Name</legend>-->
								
								<!-- Textarea -->
								<div class="form-group">
									<div class="col-md-12">
										<textarea class="form-control chat-text autogrow" name="startingText" data-provide="markdown" data-hidden-buttons="cmdPreview" placeholder="<?php echo lang("amendments_argument_pro"); ?>"></textarea>
									</div>
								</div>
								
								<!-- Button -->
								<div class="form-group">
									<div class="col-md-12">
										<button class="btn btn-primary btn-chat-send" <?php echo ($meeting["mee_status"] != "closed") ? "" : "disabled='disabled'"; ?>><?php echo lang("construction_argument_send"); ?></button>
									</div>
								</div>
								
							</fieldset>
						</form>
	
					</div>
	
					<div class="panel panel-default">
						<div class="panel-heading"><p class="text-success pro-counter">
							<?php echo langFormat($numberOfChats[1] < 2, "amendments_pro_argument", "amendments_pro_arguments", array("argument" => $numberOfChats[1])); ?>
						</p></div>
						<ul class="list-group objects">
	<?php	foreach($chats as $chat) {
				if ($chat["cha_type"] != "pro") continue;

				$chatAdvices = $chatAdviceBo->getByFilters(array("cad_chat_id" => $chat["cha_id"]));

				$chatAdviceCounters = array("me" => "", "thumb_up" => 0, "thumb_down" => 0, "thumb_middle" => 0, "total" => 0);
				foreach($chatAdvices as $chatAdvice) {
					$chatAdviceCounters[$chatAdvice["cad_advice"]]++;
					$chatAdviceCounters["total"]++;
					if ($chatAdvice["cad_user_id"] == $userId) $chatAdviceCounters["me"] = $chatAdvice["cad_advice"];
				}

	?>
							<li class="list-group-item pro-chat">
								<div><?php echo GaletteBo::showIdentity($chat); ?> <span class="pull-right"><?php $date = new DateTime($chat["cha_datetime"]); echo showDate($date); ?></span></div>
								<div><?php echo $emojiClient->shortnameToImage($Parsedown->text($chat["cha_text"])); ?></div>

								<div class="btn-group btn-group-xs btn-chat-group" role="group" <?php echo ($meeting["mee_status"] != "closed") ? "" : "style='display: none; '"; ?>>
									<button type="button" data-advice="thumb_up"     data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-success <?php echo (($chatAdviceCounters["me"] == "thumb_up") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-up"></span></button>
									<button type="button" data-advice="thumb_middle" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-warning <?php echo (($chatAdviceCounters["me"] == "thumb_middle") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-hand-left"></span></button>
									<button type="button" data-advice="thumb_down"   data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-danger  <?php echo (($chatAdviceCounters["me"] == "thumb_down") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-down"></span></button>
									<button type="button" style="height: 19px;"      data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-primary"><i class="fa fa-commenting" aria-hidden="true"></i></button>
								</div>
								<?php	if ($chatAdviceCounters["total"]) { ?>
								<div class="advice-progress-bar" style="padding-top: 2px;">
									<div class="progress" style="height: 3px;">
										<div class="progress-bar progress-bar-success" style="width: <?php echo $chatAdviceCounters["thumb_up"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_up"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-warning" style="width: <?php echo $chatAdviceCounters["thumb_middle"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_middle"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-danger" style="width: <?php echo $chatAdviceCounters["thumb_down"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_down"]; ?></span>
										</div>
									</div>
								</div>
								<?php	} ?>
							</li>
	
	<?php	
				$childrenChats = $chatBo->getByFilters(array("cha_parent_id" => $chat["cha_id"]));
				
				// Start children chats
				
				foreach($childrenChats as $childrenChat) {

					$chatAdvices = $chatAdviceBo->getByFilters(array("cad_chat_id" => $childrenChat["cha_id"]));
	
					$chatAdviceCounters = array("me" => "", "thumb_up" => 0, "thumb_down" => 0, "thumb_middle" => 0, "total" => 0);
					foreach($chatAdvices as $chatAdvice) {
						$chatAdviceCounters[$chatAdvice["cad_advice"]]++;
						$chatAdviceCounters["total"]++;
						if ($chatAdvice["cad_user_id"] == $userId) $chatAdviceCounters["me"] = $chatAdvice["cad_advice"];
					}

	?>
							<li class="list-group-item pro-chat children-chat">
								<div><?php echo GaletteBo::showIdentity($childrenChat); ?> <span class="pull-right"><?php $date = new DateTime($childrenChat["cha_datetime"]); echo showDate($date); ?></span></div>
								<div><?php echo $emojiClient->shortnameToImage($Parsedown->text($childrenChat["cha_text"])); ?></div>

								<div class="btn-group btn-group-xs btn-chat-group" role="group" <?php echo ($meeting["mee_status"] != "closed") ? "" : "style='display: none; '"; ?>>
									<button type="button" data-advice="thumb_up"     data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-success <?php echo (($chatAdviceCounters["me"] == "thumb_up") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-up"></span></button>
									<button type="button" data-advice="thumb_middle" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-warning <?php echo (($chatAdviceCounters["me"] == "thumb_middle") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-hand-left"></span></button>
									<button type="button" data-advice="thumb_down"   data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-danger  <?php echo (($chatAdviceCounters["me"] == "thumb_down") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-down"></span></button>
								</div>
								<?php	if ($chatAdviceCounters["total"]) { ?>
								<div class="advice-progress-bar" style="padding-top: 2px;">
									<div class="progress" style="height: 3px;">
										<div class="progress-bar progress-bar-success" style="width: <?php echo $chatAdviceCounters["thumb_up"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_up"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-warning" style="width: <?php echo $chatAdviceCounters["thumb_middle"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_middle"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-danger" style="width: <?php echo $chatAdviceCounters["thumb_down"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_down"]; ?></span>
										</div>
									</div>
								</div>
								<?php	} ?>
							</li>
	
	<?php	

				} 

				// End children chats
?>
							<li class="list-group-item pro-chat answer-chat" id="answer-chat-<?php echo $chat["cha_id"]; ?>" style="display: none;">

								<form class="form-horizontal" data-chat-type="pro">
									<fieldset>
			
										<input type="hidden" name="id" value="<?php echo $motion["mee_id"]; ?>">
										<input type="hidden" name="pointId" value="<?php echo $motion["mot_agenda_id"]; ?>">
										<input type="hidden" name="userId" value="<?php echo $userId; ?>">
										<input type="hidden" name="parentId" value="<?php echo $chat["cha_id"]; ?>">
										<input type="hidden" class="chat-type" value="pro">
			
										<!-- Form Name -->
										<!--<legend>Form Name</legend>-->
										
										<!-- Textarea -->
										<div class="form-group">
											<div class="col-md-12">
												<textarea class="form-control chat-text autogrow" name="startingText" data-provide="markdown" data-hidden-buttons="cmdPreview" placeholder="<?php echo lang("amendments_argument_answer"); ?>"></textarea>
											</div>
										</div>

										<!-- Button -->
										<div class="form-group">
											<div class="col-md-12">
												<button class="btn btn-primary btn-chat-send"><?php echo lang("construction_argument_send"); ?></button>
											</div>
										</div>
										
									</fieldset>
								</form>

							</li>

<?php		} ?>
						</ul>
					</div>
	
				</div>
				<div class="col-md-6 against-chats">
					<div class="well">
	
						<form class="form-horizontal" data-chat-type="against">
							<fieldset>
	
								<input type="hidden" name="id" value="<?php echo $motion["mee_id"]; ?>">
								<input type="hidden" name="motionId" value="<?php echo $motion["mot_id"]; ?>">
								<input type="hidden" name="pointId" value="<?php echo $motion["mot_agenda_id"]; ?>">
								<input type="hidden" name="userId" value="<?php echo $userId; ?>">
								<input type="hidden" name="type" class="chat-type" value="against">
	
								<!-- Form Name -->
								<!--<legend>Form Name</legend>-->
								
								<!-- Textarea -->
								<div class="form-group">
									<div class="col-md-12">
										<textarea class="form-control chat-text autogrow" name="startingText" data-provide="markdown" data-hidden-buttons="cmdPreview" placeholder="<?php echo lang("amendments_argument_against"); ?>"></textarea>
									</div>
								</div>
								
								<!-- Button -->
								<div class="form-group">
									<div class="col-md-12">
										<button class="btn btn-primary btn-chat-send" <?php echo ($meeting["mee_status"] != "closed") ? "" : "disabled='disabled'"; ?>><?php echo lang("construction_argument_send"); ?></button>
									</div>
								</div>
								
							</fieldset>
						</form>
	
					</div>
	
					<div class="panel panel-default">
						<div class="panel-heading"><p class="text-danger against-counter">
							<?php echo langFormat($numberOfChats[2] < 2, "amendments_against_argument", "amendments_against_arguments", array("argument" => $numberOfChats[2])); ?>
						</p></div>
						<ul class="list-group objects">
	<?php	foreach($chats as $chat) {
				if ($chat["cha_type"] != "against") continue;

				$chatAdvices = $chatAdviceBo->getByFilters(array("cad_chat_id" => $chat["cha_id"]));

				$chatAdviceCounters = array("me" => "", "thumb_up" => 0, "thumb_down" => 0, "thumb_middle" => 0, "total" => 0);
				foreach($chatAdvices as $chatAdvice) {
					$chatAdviceCounters[$chatAdvice["cad_advice"]]++;
					$chatAdviceCounters["total"]++;
					if ($chatAdvice["cad_user_id"] == $userId) $chatAdviceCounters["me"] = $chatAdvice["cad_advice"];
				}

	?>
							<li class="list-group-item against-chat">
								<div><?php echo GaletteBo::showIdentity($chat); ?> <span class="pull-right"><?php $date = new DateTime($chat["cha_datetime"]); echo showDate($date); ?></span></div>
								<div><?php echo $emojiClient->shortnameToImage($Parsedown->text($chat["cha_text"])); ?></div>

								<div class="btn-group btn-group-xs btn-chat-group" role="group" <?php echo ($meeting["mee_status"] != "closed") ? "" : "style='display: none; '"; ?>>
									<button type="button" data-advice="thumb_up"     data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-success <?php echo (($chatAdviceCounters["me"] == "thumb_up") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-up"></span></button>
									<button type="button" data-advice="thumb_middle" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-warning <?php echo (($chatAdviceCounters["me"] == "thumb_middle") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-hand-left"></span></button>
									<button type="button" data-advice="thumb_down"   data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-danger  <?php echo (($chatAdviceCounters["me"] == "thumb_down") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-down"></span></button>
									<button type="button" style="height: 19px;"      data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $chat["cha_id"]; ?>" class="btn btn-primary"><i class="fa fa-commenting" aria-hidden="true"></i></button>
								</div>
								<?php	if ($chatAdviceCounters["total"]) { ?>
								<div class="advice-progress-bar" style="padding-top: 2px;">
									<div class="progress" style="height: 3px;">
										<div class="progress-bar progress-bar-success" style="width: <?php echo $chatAdviceCounters["thumb_up"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_up"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-warning" style="width: <?php echo $chatAdviceCounters["thumb_middle"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_middle"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-danger" style="width: <?php echo $chatAdviceCounters["thumb_down"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_down"]; ?></span>
										</div>
									</div>
								</div>
								<?php	} ?>
							</li>
	
	<?php	
	
				$childrenChats = $chatBo->getByFilters(array("cha_parent_id" => $chat["cha_id"]));
				
				// Start children chats
				
				foreach($childrenChats as $childrenChat) {

					$chatAdvices = $chatAdviceBo->getByFilters(array("cad_chat_id" => $childrenChat["cha_id"]));
	
					$chatAdviceCounters = array("me" => "", "thumb_up" => 0, "thumb_down" => 0, "thumb_middle" => 0, "total" => 0);
					foreach($chatAdvices as $chatAdvice) {
						$chatAdviceCounters[$chatAdvice["cad_advice"]]++;
						$chatAdviceCounters["total"]++;
						if ($chatAdvice["cad_user_id"] == $userId) $chatAdviceCounters["me"] = $chatAdvice["cad_advice"];
					}

	?>
							<li class="list-group-item against-chat children-chat">
								<div><?php echo GaletteBo::showIdentity($childrenChat); ?> <span class="pull-right"><?php $date = new DateTime($childrenChat["cha_datetime"]); echo showDate($date); ?></span></div>
								<div><?php echo $emojiClient->shortnameToImage($Parsedown->text($childrenChat["cha_text"])); ?></div>

								<div class="btn-group btn-group-xs btn-chat-group" role="group" <?php echo ($meeting["mee_status"] != "closed") ? "" : "style='display: none; '"; ?>>
									<button type="button" data-advice="thumb_up"     data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-success <?php echo (($chatAdviceCounters["me"] == "thumb_up") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-up"></span></button>
									<button type="button" data-advice="thumb_middle" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-warning <?php echo (($chatAdviceCounters["me"] == "thumb_middle") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-hand-left"></span></button>
									<button type="button" data-advice="thumb_down"   data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>" data-chat-id="<?php echo $childrenChat["cha_id"]; ?>" class="btn btn-danger  <?php echo (($chatAdviceCounters["me"] == "thumb_down") ? "active" : "zero"); ?>"><span class="glyphicon glyphicon-thumbs-down"></span></button>
								</div>
								<?php	if ($chatAdviceCounters["total"]) { ?>
								<div class="advice-progress-bar" style="padding-top: 2px;">
									<div class="progress" style="height: 3px;">
										<div class="progress-bar progress-bar-success" style="width: <?php echo $chatAdviceCounters["thumb_up"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_up"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-warning" style="width: <?php echo $chatAdviceCounters["thumb_middle"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_middle"]; ?></span>
										</div>
										<div class="progress-bar progress-bar-danger" style="width: <?php echo $chatAdviceCounters["thumb_down"] / $chatAdviceCounters["total"] * 100; ?>%">
											<span class="sr-only"><?php echo $chatAdviceCounters["thumb_down"]; ?></span>
										</div>
									</div>
								</div>
								<?php	} ?>
							</li>
	
	<?php	

				} 

				// End children chats
?>
							<li class="list-group-item against-chat answer-chat" id="answer-chat-<?php echo $chat["cha_id"]; ?>" style="display: none;">

								<form class="form-horizontal" data-chat-type="against">
									<fieldset>
			
										<input type="hidden" name="id" value="<?php echo $motion["mee_id"]; ?>">
										<input type="hidden" name="pointId" value="<?php echo $motion["mot_agenda_id"]; ?>">
										<input type="hidden" name="userId" value="<?php echo $userId; ?>">
										<input type="hidden" name="parentId" value="<?php echo $chat["cha_id"]; ?>">
										<input type="hidden" class="chat-type" value="against">
			
										<!-- Form Name -->
										<!--<legend>Form Name</legend>-->
										
										<!-- Textarea -->
										<div class="form-group">
											<div class="col-md-12">
												<textarea class="form-control chat-text autogrow" name="startingText" data-provide="markdown" data-hidden-buttons="cmdPreview" placeholder="<?php echo lang("amendments_argument_answer"); ?>"></textarea>
											</div>
										</div>

										<!-- Button -->
										<div class="form-group">
											<div class="col-md-12">
												<button class="btn btn-primary btn-chat-send"><?php echo lang("construction_argument_send"); ?></button>
											</div>
										</div>
										
									</fieldset>
								</form>

							</li>

<?php		} ?>

						</ul>
					</div>
				</div>
			</div>

			<div class="clearfix"></div>

<?php 
	if (!$agenda["age_parent_id"]) {
		$agenda = $amendmentAgenda;
		$hasWritingRights = $hasWritingRights || $votingPower > 0;
		$showTitle = false;
?>
			<div role="tabpanel" class="tab-pane" id="amendments" style="padding-top: 15px;">
<?php	
		include("construction/amendment_list.php");
?>
			</div>
<?php	
	}
?>

			<div role="tabpanel" class="tab-pane" id="sources" style="padding-top: 15px;">

				<div class="panel panel-default agenda-entry" id="agenda-entry-<?php echo $agenda["age_id"]; ?>" data-id="<?php echo $agenda["age_id"]; ?>">
					<ul class="list-group objects">
	<?php
	foreach($sources as $src) {
	?>
						<li class="list-group-item" data-id="<?php echo $src["sou_id"]; ?>">
							<?php echo lang("source_icon_" . $src["sou_type"]); ?> <a href="<?php echo $src["sou_url"]; ?>"><?php echo $src["sou_title"]; ?></a> <a href="<?php echo $src["sou_url"]; ?>" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a>
						</li>
	<?php 		} ?>			
					</ul>
					<div class="panel-footer">
	<?php				if ($hasWritingRights && ($meeting["mee_status"] != "closed")) { ?>
							<button class="btn btn-default btn-xs btn-add-source" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>"><?php echo lang("motion_add_source"); ?> <span class="fa fa-rss"></span></button>
	<?php				} ?>
					</div>
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
	include("construction/source_modal.php");
?>

<script>
</script>
<?php include("footer.php");?>
<script src="assets/js/perpage/construction_source_helper.js"></script>
<script src="assets/js/perpage/construction_source_save.js"></script>
<script src="assets/js/perpage/construction_motion_save.js"></script>
<script src="assets/js/perpage/meeting_events.js"></script>
<script>
sourceEnabled = false;
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

<div id="motion-json" class="hidden"><?php 
	$motion["mot_description"] = str_replace("<", "#lt;", $motion["mot_description"]);
	echo json_encode($motion); 
?></div>
</body>
</html>
