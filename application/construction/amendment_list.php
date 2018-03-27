<?php
			// save one request
			if (isset($agenda["age_motions"])) {
				$motions = $agenda["age_motions"];
			}
			else {
	            $motions = $motionBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
			}
			$votes = $voteBo->getByFilters(array("mot_agenda_id" => $agenda[$agendaBo->ID_FIELD]));
?>			
			<div class="panel panel-default agenda-entry" id="agenda-entry-<?php echo $agenda["age_id"]; ?>" data-id="<?php echo $agenda["age_id"]; ?>">
<?php			if ($showTitle) { ?>
				<div class="panel-heading">
					<a href="?id=<?php echo $meeting["mee_id"]; ?>&agendaId=<?php echo $agenda["age_id"]; ?>"><?php echo $agenda["age_label"]; ?></a>
				</div>
<?php				if ($agenda["age_description"]) { ?>
				<div class="panel-body">
					<?php echo $agenda["age_description"]; ?>
				</div>
<?php				} 
				} ?>
				<ul class="list-group objects">
<?php
			$previousMotionId = null;

			foreach($motions as $motion) {
//				echo $motion["mot_title"] . "<br>";
				
				if ($previousMotionId == $motion["mot_id"]) continue;
				$previousMotionId = $motion["mot_id"];

				$amendmentAgenda = $agendaBo->getByFilters(array("age_parent_id" => $motion["mot_agenda_id"], "age_label" => "amendments-" . $motion["mot_id"]));
				$numberOfAmendments = 0;

				if (count($amendmentAgenda)) {
					$amendmentAgenda = $amendmentAgenda[0];
		            $amendments = $motionBo->getByFilters(array("mot_agenda_id" => $amendmentAgenda[$agendaBo->ID_FIELD]));
		            
					$previousAmendmentId = null;
		            foreach($amendments as $amendment) {
						if ($previousAmendmentId == $amendment["mot_id"]) continue;
						$previousAmendmentId = $amendment["mot_id"];
						
						$numberOfAmendments++;
		            }
				}

				$author = null;
				if ($motion["mot_author_id"]) {
					$author = $userBo->getById($motion["mot_author_id"]);
				}

				$chats = $chatBo->getByFilters(array("cha_motion_id" => $motion["mot_id"]));
				$numberOfChats = array(0, 0, 0);
				foreach($chats as $chat) {
					if ($chat["cha_type"] != "pro" && $chat["cha_type"] != "against") continue;
					
					$numberOfChats[0]++;
					
					if ($chat["cha_type"] == "pro") $numberOfChats[1]++;
					if ($chat["cha_type"] == "against") $numberOfChats[2]++;
				}

				$voteCounters = array(0, 0, 0, 0);
				foreach($votes as $vote) {
					if ($motion["mot_id"] != $vote["mot_id"]) continue;
					$voteCounters[0] += $vote["vot_power"];

					if ($vote["mpr_label"] == "pro" || strtolower($vote["mpr_label"]) == "oui" || strtolower($vote["mpr_label"]) == "pour") $voteCounters[1] += $vote["vot_power"];
					else if ($vote["mpr_label"] == "doubtful") $voteCounters[2] += $vote["vot_power"];
					else if ($vote["mpr_label"] == "against" || strtolower($vote["mpr_label"]) == "non" || strtolower($vote["mpr_label"]) == "contre") $voteCounters[3] += $vote["vot_power"];
				}

?>
					<li class="list-group-item">
						<div class="pull-right" style="width: 36px; height: 36px; font-size: smaller;" id="mini-voting-panel-<?php echo $motion["mot_id"]; ?>">

<?php	

$chartId = "mini-voting-panel-" . $motion["mot_id"];
$width = 36;
$height = 36;

echo include("construction/pieChart.php"); 

?>

						</div>
						<div style="font-size: larger;">
							<p class="text-info"><a href="construction_motion.php?motionId=<?php echo $motion["mot_id"]; ?>"><?php echo $motion["mot_title"]; ?></a></p>
						</div>
						<div style="font-size: smaller;">
							<?php 	if ($author) { ?>
								<?php echo GaletteBo::showIdentity($author); ?>
							<?php 	}	?>
						</div>
						<div style="font-size: smaller;">
							<?php echo $voteCounters[0]; ?> votes - 
							<?php echo $numberOfChats[0]; ?> arguments -
							<?php echo $numberOfAmendments; ?> amendements
						</div>
					</li>
<?php 		} ?>			
				</ul>
				<div class="panel-footer">
<?php				if ($hasWritingRights) { ?>
						<button class="btn btn-default btn-xs btn-add-motion" data-meeting-id="<?php echo $meeting["mee_id"]; ?>" data-agenda-id="<?php echo $agenda["age_id"]; ?>">Amendement <span class="fa fa-archive"></span></button>
<?php				} ?>
				</div>
			</div>
