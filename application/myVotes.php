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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("header.php");

require_once("engine/bo/MotionBo.php");
require_once("engine/bo/TagBo.php");
require_once("engine/utils/Parsedown.php");
require_once("engine/emojione/autoload.php");

$Parsedown = new Parsedown();
$emojiClient = new Emojione\Client(new Emojione\Ruleset());

$motionBo = MotionBo::newInstance($connection, $config);
$tagBo = TagBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) $userId = -1;

$filters = array();
$filters["mot_status"] = MotionBo::VOTING;
$filters["mee_status"] = "open";
$filters["mee_type"] = "meeting";
$filters["vot_member_id"] = $userId;
$filters["with_meeting"] = true;
$filters["with_notice"] = true;

if (isset($_REQUEST["motionId"])) {
	$filters["mot_id"] = intval($_REQUEST["motionId"]);
}

if (isset($_REQUEST["agendaId"])) {
	$filters["age_id"] = intval($_REQUEST["agendaId"]);
}

if (isset($_REQUEST["meetingId"])) {
	$filters["mee_id"] = intval($_REQUEST["meetingId"]);
}

//print_r($filters);

$motions = $motionBo->getByFilters($filters);

// echo "<!-- \n";
// print_r($motions);
// echo "\n -->";

$sortedMotions = array();
$now = getNow();

foreach($motions as $motion) {
	$foundUser = false;
	foreach($motion as $key => $value) {
		if (strpos($key, "_id_adh") !== false && $value == $userId) {
			$foundUser = true;
			break;
		}
	}
	if (!$foundUser) continue;

	$motion["mot_tag_ids"] = json_decode($motion["mot_tag_ids"]);
	$motion["mot_tags"] = array();
	
	if (count($motion["mot_tag_ids"])) {
		$tags = $tagBo->getByFilters(array("tag_ids" => $motion["mot_tag_ids"]));
		$motion["mot_tags"] = $tags;
	}

	if ($motion["mot_deadline"]) {
		$date = getDateTime($motion["mot_deadline"]);
		$dateFormat = $date->format(lang("date_format", false));

		$motion["mot_deadline_string"] = str_replace("{date}", $dateFormat, str_replace("{time}", $date->format(lang("time_format", false)), lang("datetime_format", false)));

		$interval = $date->diff($now);

		$hours = $interval->format("%a") * 24 + $interval->format("%H");

		$motion["mot_deadline_diff"] = $interval->format("%r". ($hours < 10 ? "0" : "") . $hours.":%I:%S");
		$motion["mot_deadline_expired"] = (strpos($motion["mot_deadline_diff"], "-") === false);
	}

	if(!isset($sortedMotions[$motion["mot_id"]])) {
		$sortedMotions[$motion["mot_id"]] = $motion;
		$sortedMotions[$motion["mot_id"]]["propositions"] = array();
	}

	$sortedMotions[$motion["mot_id"]]["propositions"][] = $motion;
}

ksort($sortedMotions);

function sortPropositions($a, $b) {
    if ($a["vot_power"] == $b["vot_power"]) {

    	if ($a["vot_power"] == 0) {
    		if (strtolower(($a["mpr_label"])) == "oui" || strtolower(($a["mpr_label"])) == "pour") return -1;
    		if (strtolower(($a["mpr_label"])) == "nspp") return 1;

    		if (strtolower(($b["mpr_label"])) == "oui" || strtolower(($b["mpr_label"])) == "pour") return 1;
    		if (strtolower(($b["mpr_label"])) == "nspp") return -1;
    	}

    	return 0;
    }

    return ($a["vot_power"] > $b["vot_power"]) ? -1 : 1;
}

?>

<div class="container theme-showcase meeting" role="main"
	data-id="<?php echo @$meeting[$meetingBo->ID_FIELD]; ?>"
	data-user-id="<?php echo $userId ? $userId : "G" . $guestId; ?>"
	>
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_myVotes"); ?></li>
	</ol>
	
	<div>

<?php	

$numberOfMotions = 0;
foreach($sortedMotions as $motionId => $motion) { 

	foreach($config["modules"]["groupsources"] as $groupSourceKey) {
		$groupSource = GroupSourceFactory::getInstance($groupSourceKey);

    	if ($groupSource->getGroupKey() != $motion["not_target_type"]) continue;

	    $maxVotePower = $groupSource->getMaxVotePower($motion);

	    break;
	}

	if (!$maxVotePower) continue;
	
	$numberOfMotions++;
}
?>



<?php echo str_replace("{value}", $numberOfMotions, lang("myVotes_numberOfMotions")); ?><br><br>

<?php	if (!count($sortedMotions)) {
?>	<div class="well well-sm"><?php
			echo lang("myVotes_no_motion");				
?>	</div><?php
		}
		else { ?>
<div class="text-center">
	<button class="btn btn-default btn-previous pull-left" type="button" style="display: none;"><span class="glyphicon glyphicon-chevron-left"></span></button>
	<button class="btn btn-default btn-next pull-right" type="button" style="display: none;"><span class="glyphicon glyphicon-chevron-right"></span></button>

	<button class="btn btn-default btn-show-summary" type="button"><span class="glyphicon glyphicon-list-alt"></span> <?=lang("myVotes_showSummary")?></button>
	<button class="btn btn-default btn-paper-vote" type="button"><span class="glyphicon glyphicon-list-alt"></span> <?=lang("myVotes_printBallot")?></button>
	<a href="#" class="ballot-link" download="bulletin.pdf" style="display: none;"><?=lang("myVotes_downloadBallot")?></a>
</div>
<?php	} ?>

<div class="clearfix"></div><br>

<?php	foreach($sortedMotions as $motionId => $motion) { 

//	echo "<pre>\n";
//	print_r($motion);
//	echo "</pre>";

//	echo $motionId . "\n";
//	echo $motion["not_target_type"] . "\n";

	foreach($config["modules"]["groupsources"] as $groupSourceKey) {
		$groupSource = GroupSourceFactory::getInstance($groupSourceKey);

    	if ($groupSource->getGroupKey() != $motion["not_target_type"]) continue;

//		print_r($groupSource);

//		echo "Oh oh max power : " . $motion["mot_title"] . " (" . print_r($groupSource, true) . ")";
	    $maxVotePower = $groupSource->getMaxVotePower($motion);
//	    echo " => " . $maxVotePower . "<br>\n";

	    break;
	}

	$propositions = $motion["propositions"];

//	if ($motion["mot_win_limit"] == -1) {
		usort($propositions, "sortPropositions");
//	}

	if (!$maxVotePower) continue;
?>

<div class="panel panel-default motion" style="display: none;" data-id="<?php echo $motion["mot_id"]; ?>" data-max-power="<?php echo $maxVotePower; ?>" data-method="<?php echo $motion["mot_win_limit"]; ?>">
	<div class="panel-heading">
		<h3 class="panel-title motion-title"><a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>#agenda-<?php echo $motion["age_id"]; ?>|motion-<?php echo $motion["mot_id"]; ?>"><?php echo $motion["mot_title"]; ?> 
<!--			(<?php echo $motion["mot_id"]; ?>) -->
		</a></h3>
	</div>
	<div class="panel-body">
		<h4><?php echo $emojiClient->shortnameToImage($Parsedown->text($motion["mot_description"])); ?></h4>
		<h4><?php echo str_replace("{value}", $maxVotePower, lang("myVotes_maxPower")); ?></h4>
		<h4><?php echo str_replace("{value}", lang("motion_ballot_majority_" . $motion["mot_win_limit"]), lang("myVotes_voteMethod")); ?>
		<?php	if(isLanguageKey("motion_ballot_majority_" . $motion["mot_win_limit"] . "_help")) {
					?><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" title="<?php echo lang("motion_ballot_majority_" . $motion["mot_win_limit"] . "_help"); ?>"></span><?php
				}
		?>
		<?php	if ($motion["mot_deadline"]) {?>
		<h4><?php echo str_replace("{value}", $motion["mot_deadline_string"], lang("myVotes_deadline")); ?></h4>
		<?php	}?>
		
		</h4>
		<?php	if (count($motion["mot_tags"])) { 
					$tags = array();
					foreach($motion["mot_tags"] as $tag) {
						$tags[] = $tag["tag_label"];
					}
		?>
		<h4><?php echo str_replace("{tags}", implode(", ", $tags), lang("myVotes_tagsMethod")); ?></h4>
		<?php 	} ?>

		<?php

			if ($motion["age_description"]) {
				$randomId = uniqid("description_");
?>
				<a href="#" class="show-description-link" data-tab-id="<?php echo $randomId; ?>" style="             "><?php echo lang("myVotes_show_agenda_description"); ?> <i class="fa fa-caret-right" aria-hidden="true"></i></a>
				<a href="#" class="hide-description-link" data-tab-id="<?php echo $randomId; ?>" style="display:none;"><?php echo lang("myVotes_hide_agenda_description"); ?> <i class="fa fa-caret-down" aria-hidden="true"></i></a>
				<div id="<?php echo $randomId; ?>" style="display:none;">
				<?php echo $emojiClient->shortnameToImage($Parsedown->text($motion["age_description"])); ?>
				</div>
<?php
			}

		?>

		<hr>

		<div class="propositions" data-expired="<?=(($motion["mot_deadline"] && $motion["mot_deadline_expired"]) ? "true" : "false")?>" style="<?=(($motion["mot_deadline"] && $motion["mot_deadline_expired"]) ? "opacity: 0.5;" : "")?>">
	<?php	
			foreach($propositions as $index => $proposition) {

				if ($motion["mot_win_limit"] == -2) {
					if ($index != 0) echo "<br>";
				?>
			<?php echo $proposition["mpr_label"]; ?><br>
			<div class="proposition" style="width: 100%; border-radius: 4px; padding: 2px;" data-expired="<?php echo (($motion["mot_deadline"] && $motion["mot_deadline_expired"]) ? "true" : "false"); ?>" data-id="<?php echo $proposition["mpr_id"]; ?>" data-power="<?php echo ($proposition["vot_power"] ? $proposition["vot_power"] : 0); ?>">
				<div class="btn-group" style="width: 100%; margin: 2px;">
					<?php 	$nbItems = count($config["congressus"]["ballot_majority_judgment"]);
							foreach($config["congressus"]["ballot_majority_judgment"] as $judgeIndex => $judgementMajorityItem) {?>
						<div class="btn btn-default judgement" 
							style="width: <?php echo 100 / $nbItems; ?>%; color: #111111; background: hsl(<?php echo 120 * (0 + ($judgeIndex / ($nbItems - 1))); ?>, 70%, 70%);" type="button" data-power="<?php echo $judgementMajorityItem; ?>"><?php echo lang("motion_majorityJudgment_" . $judgementMajorityItem); ?></div>				
					<?php	} ?>
				</div>
			</div>
	<?php 		} 
				else if ($motion["mot_win_limit"] == -1) {
				?>
			<div class="btn btn-default proposition text-center" data-expired="<?php echo (($motion["mot_deadline"] && $motion["mot_deadline_expired"]) ? "true" : "false"); ?>"
				style="width: 100%; white-space: pre-wrap;" type="button" data-id="<?php echo $proposition["mpr_id"]; ?>" data-power="<?php echo ($proposition["vot_power"] ? $proposition["vot_power"] : 0); ?>">
				<button class='btn btn-up btn-xs pull-left' 
					style='background-color: inherit;'><i class="fa fa-arrow-up" aria-hidden="true"></i></button><span class='proposition-label'><?php echo $proposition["mpr_label"]; ?></span><button class='btn btn-down btn-xs pull-right' 
					style='background-color: inherit;'><i class="fa fa-arrow-down" aria-hidden="true"></i></button>
			</div>
	<?php 		} 
				else {?>
			<div class="btn btn-default proposition" data-expired="<?php echo (($motion["mot_deadline"] && $motion["mot_deadline_expired"]) ? "true" : "false"); ?>"
					style="width: 100%; white-space: pre-wrap;" type="button" data-id="<?php echo $proposition["mpr_id"]; ?>" data-power="<?php echo ($proposition["vot_power"] ? $proposition["vot_power"] : 0); ?>"><?php echo $proposition["mpr_label"]; ?></div>
	<?php 		}
			}
			?>

		</div>

		<br>
		<button class="btn btn-primary btn-vote" style="width: 100%; margin-bottom: 4px;" type="button"><?php echo lang("common_vote"); ?></button>
		<button class="btn btn-warning btn-reset" style="width: calc(50% - 2px);" type="button"><?php echo lang("common_reset"); ?></button>
		<button class="btn btn-info btn-abstain" style="width: calc(50% - 2px);" type="button"><?php echo lang("common_abstain"); ?></button>

	</div>
	<div class="panel-footer text-right">
		<a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>"><?php echo $motion["mee_label"]; ?></a> 
		/ 
		<a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>#agenda-<?php echo $motion["age_id"]; ?>"><?php echo $motion["age_label"]; ?></a>
	</div>
</div>

	
<?php 	} ?>

<div class="panel panel-default summary" style="display: none;">
	<div class="panel-heading">
		<h3 class="panel-title">
			<?=lang("myVotes_summary")?>
		</h3>
	</div>
	<ul class="list-group">
<?php	
foreach($sortedMotions as $motionId => $motion) { 
	$propositions = $motion["propositions"];
	usort($propositions, "sortPropositions");
?>
	<li class="list-group-item" style="padding-bottom: 0" data-motion-id="<?=$motion["mot_id"]?>"><?=$motion["mot_title"]?> (<?=lang("motion_ballot_majority_" . $motion["mot_win_limit"])?>) <button class="btn btn-default btn-xs pull-right btn-show-motion" data-motion-id="<?=$motion["mot_id"]?>"><i class="fa fa-archive"></i></button>
		<ul class="list-group" style="margin-bottom: 0">
<?php
	foreach($propositions as $index => $proposition) { ?>
		<li class="list-group-item" data-proposition-id="<?=$proposition["mpr_id"]?>"><?=$proposition["mpr_label"]?> <span class="badge pull-right"><?=($proposition["vot_power"] ? ($motion["mot_win_limit"] == -2 ? lang("motion_majorityJudgment_" . $proposition["vot_power"]) : $proposition["vot_power"]) : "")?></span></li>
<?php
	} ?>
	
		</ul>
	</li>
<?php	
} ?>	
	</ul>
</div>


	</div>

<div id="log"></div>

<?php include("connect_button.php"); ?>
</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
</script>
<?php include("footer.php");?>
<script>
/* global judgmentVoteIsMandatory */

let judgementMajorityValues = {};

<?php
foreach($config["congressus"]["ballot_majority_judgment"] as $ballotJM) { ?>

judgementMajorityValues[<?=$ballotJM?>] = <?=json_encode(lang("motion_majorityJudgment_$ballotJM"))?>;

<?php
} ?>

judgmentVoteIsMandatory = <?php echo json_encode(isset($config["congressus"]["ballot_majority_judgment_force"]) ? $config["congressus"]["ballot_majority_judgment_force"] : false); ?>;
</script>
</body>
</html>
