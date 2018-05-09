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

require_once("engine/bo/MotionBo.php");

$motionBo = MotionBo::newInstance($connection, $config);

$userId = SessionUtils::getUserId($_SESSION);
if (!$userId) $userId = -1;

$filters = array();
$filters["mot_status"] = MotionBo::VOTING;
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

$sortedMotions = array();

foreach($motions as $motion) {
	if(!isset($sortedMotions[$motion["mot_id"]])) {
		$sortedMotions[$motion["mot_id"]] = $motion;
		$sortedMotions[$motion["mot_id"]]["propositions"] = array();
	}

	$sortedMotions[$motion["mot_id"]]["propositions"][] = $motion;
}

ksort($sortedMotions);

function sortPropositions($a, $b) {
    if ($a["vot_power"] == $b["vot_power"]) return 0;

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

<?php echo str_replace("{value}", count($sortedMotions), lang("myVotes_numberOfMotions")); ?><br><br>

<?php	if (!count($sortedMotions)) {
?>	<div class="well well-sm"><?php
			echo lang("myVotes_no_motion");				
?>	</div><?php
		}
		else { ?>
<div class="text-center">
	<button class="btn btn-default btn-previous pull-left" type="button" style="display: none;"><span class="glyphicon glyphicon-chevron-left"></span></button>
	<button class="btn btn-default btn-next pull-right" type="button" style="display: none;"><span class="glyphicon glyphicon-chevron-right"></span></button>
	
	<button class="btn btn-default btn-paper-vote" type="button"><span class="glyphicon glyphicon-list-alt"></span> Imprimer un bulletin papier</button>
	<a href="#" class="ballot-link" download="bulletin.pdf" style="display: none;">Télécharger le bulletin</a>
</div>
<?php	} ?>

<div class="clearfix"></div><br>


<?php	foreach($sortedMotions as $motionId => $motion) { 

	foreach($config["modules"]["groupsources"] as $groupSourceKey) {
		$groupSource = GroupSourceFactory::getInstance($groupSourceKey);

    	if ($groupSource->getGroupKey() != $motion["not_target_type"]) continue;

	    $maxVotePower = $groupSource->getMaxVotePower($motion);
	}

	$propositions = $motion["propositions"];

	if ($motion["mot_win_limit"] == -1) {
		usort($propositions, "sortPropositions");
	}

?>

<div class="panel panel-default motion" style="display: none;" data-id="<?php echo $motion["mot_id"]; ?>" data-max-power="<?php echo $maxVotePower; ?>" data-method="<?php echo $motion["mot_win_limit"]; ?>">
	<div class="panel-heading">
		<h3 class="panel-title"><a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>#agenda-<?php echo $motion["age_id"]; ?>|motion-<?php echo $motion["mot_id"]; ?>"><?php echo $motion["mot_title"]; ?> 
<!--			(<?php echo $motion["mot_id"]; ?>) -->
		</a></h3>
	</div>
	<div class="panel-body">
		<h4><?php echo $motion["mot_description"]; ?></h4>
		<h4><?php echo str_replace("{value}", $maxVotePower, lang("myVotes_maxPower")); ?></h4>
		<h4><?php echo str_replace("{value}", lang("motion_ballot_majority_" . $motion["mot_win_limit"]), lang("myVotes_voteMethod")); ?></h4>

		<div class="propositions">
	<?php	
			foreach($propositions as $index => $proposition) {
	
				if ($motion["mot_win_limit"] == -2) {
					if ($index != 0) echo "<br>";
				?>
			<div class="proposition" style="width: 100%; border-radius: 4px;" data-id="<?php echo $proposition["mpr_id"]; ?>" data-power="<?php echo ($proposition["vot_power"] ? $proposition["vot_power"] : 0); ?>">
				<?php echo $proposition["mpr_label"]; ?>
				<div class="btn-group" style="width: 100%; margin: 2px;">
					<?php 	$nbItems = count($config["congressus"]["ballot_majority_judgment"]);
							foreach($config["congressus"]["ballot_majority_judgment"] as $judgeIndex => $judgementMajorityItem) {?>
						<div class="btn btn-default judgement" style="width: <?php echo 100 / $nbItems; ?>%; background: hsl(<?php echo 120 * (0 + ($judgeIndex / ($nbItems - 1))); ?>, 70%, 70%);" type="button" data-power="<?php echo $judgementMajorityItem; ?>"><?php echo lang("motion_majorityJudgment_" . $judgementMajorityItem); ?></div>				
					<?php	} ?>
				</div>
			</div>
	<?php 		} 
				else {?>
			<div class="btn btn-default proposition" style="width: 100%;" type="button" data-id="<?php echo $proposition["mpr_id"]; ?>" data-power="<?php echo ($proposition["vot_power"] ? $proposition["vot_power"] : 0); ?>"><?php echo $proposition["mpr_label"]; ?></div>
	<?php 		}
			}
			?>

		</div>

		<br>
		<button class="btn btn-default btn-primary btn-vote" style="width: 100%;" type="button">Voter</button>

	</div>
	<div class="panel-footer text-right">
		<a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>"><?php echo $motion["mee_label"]; ?></a> 
		/ 
		<a href="meeting.php?id=<?php echo $motion["mee_id"]; ?>#agenda-<?php echo $motion["age_id"]; ?>"><?php echo $motion["age_label"]; ?></a>
	</div>
</div>

	
<?php 	} ?>

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

judgmentVoteIsMandatory = <?php echo json_encode(isset($config["congressus"]["ballot_majority_judgment_force"]) ? $config["congressus"]["ballot_majority_judgment_force"] : false); ?>;
</script>
</body>
</html>
