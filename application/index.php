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
require_once("engine/utils/Parsedown.php");
require_once("engine/emojione/autoload.php");

$Parsedown = new Parsedown();
$emojiClient = new Emojione\Client(new Emojione\Ruleset());

$motionBo = MotionBo::newInstance($connection, $config);

$motions = $motionBo->getByFilters(array("with_meeting" => true, "mee_type" => "construction", "with_total_votes" => true, "mee_status" => "open", "mpr_label" => "pro"));
$trendingMotions = array();

$alreadyDoneMeetingIds = array();
foreach($motions as $motion) {
	if (isset($alreadyDoneMeetingIds[$motion["mee_id"]])) continue;

	$trendingMotions[] = $motion;
	$alreadyDoneMeetingIds[$motion["mee_id"]] = $motion["mee_id"];

	if (count($trendingMotions) == 4) break;
}

$headingColMd = "col-md-" . (12 / (count($trendingMotions) ? count($trendingMotions) : 1));

$filters = array();
$filters["with_status"] = array("closed");
$filters["limit"] = 7;

$closedMeetings = $meetingBo->getByFilters($filters);

$filters = array();
$filters["with_status"] = array("waiting");
$filters["older_first"] = true;
$filters["limit"] = 7;

$waitingMeetings = $meetingBo->getByFilters($filters);

/*
echo "<pre>";
print_r($closedMeetings);
echo "</pre>";
*/

//echo "<!-- \n";
//print_r($trendingMotions);
//echo "\n -->";

//print_r($meetings);

?>

<div class=" theme-showcase" role="main" id="main" 
	style="margin-left: 32px; margin-right: 32px; "
	tabindex="-1">
	<ol class="breadcrumb">
		<li class="active"><?php echo lang("breadcrumb_index"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("index_guide"); ?></p>
	</div>

<!--
	<?php	$nearMeetings = 0;
			$now = getNow();
			foreach($meetings as $meeting) { 
				$openDate = new DateTime($meeting["mee_start_time"]);
				$diff = $openDate->diff($now);
				if ($diff->days > 15) continue;
				
				$nearMeetings++;
			};

			if ($nearMeetings) { ?>
	<div class="well well-sm">
		<?php echo lang("index_open_meetings"); ?>
		
		<?php	
				$now = getNow();
				$separator = "";
				foreach($meetings as $meeting) { 
		
					$openDate = new DateTime($meeting["mee_start_time"]);
					$diff = $openDate->diff($now);

					if ($diff->days > 15) continue;
					
					echo $separator;
		?>
		<a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"] ? $meeting["mee_label"] : "-"; ?></a>
		<?php	
					$separator = ", ";
				} ?>
	</div>
	<?php	} ?>

-->

	<?php	if (count($trendingMotions)) { ?>
	
	<div class="row">
	<?php		foreach($trendingMotions as $motion) { ?>
		<div class="<?php echo $headingColMd; ?> col-xs-12 col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading no-caret" style="height: 61px;">
					<p class="text-info" style="display: inline-block;"><a 
						href="construction_motion.php?motionId=<?php echo $motion["mot_id"]; ?>" data-toggle="tooltip" data-placement="top" 
						title="<?php echo str_replace("\"", "&quot;", $emojiClient->shortnameToUnicode($motion["mot_explanation"])); ?>"><?php echo $motion["mot_title"]; ?></a></p>
				</div>
				<div class="panel-body" style="height: 100px; overflow-x: hidden;">
					<?php 
						$text = $motion["mot_explanation"];
						
//						if ($text) $text .= "<br><br>";
						if (!$text) {
							$text .= $motion["mot_description"];
	
							if (strlen($text) > 200) {
								$text = substr($text, 0, 200);
							}
						}

						echo $emojiClient->shortnameToImage($Parsedown->text($text)); 
					?>
				</div>
				<div class="panel-footer text-right" style="height: 61px;">
					<a href="construction.php?id=<?php echo $motion["mee_id"]; ?>"><?php echo $motion["mee_label"]; ?></a>
					/ 
					<a href="construction.php?id=<?php echo $motion["mee_id"]; ?>&agendaId=<?php echo $motion["age_id"]; ?>"><?php echo $motion["age_label"]; ?></a>
				</div>
			</div>
		</div>
	<?php		} ?>
	</div>
	
	<?php	} ?>

<?php
	$now = getNow();
	$nowFormat = $now->format(lang("date_format"));

	$tomorrow = getNow();
	$tomorrow = $tomorrow->add(new DateInterval('P1D'));
	$tomorrowFormat = $tomorrow->format(lang("date_format"));

	$yesterday = getNow();
	$yesterday = $yesterday->sub(new DateInterval('P1D'));
	$yesterdayFormat = $yesterday->format(lang("date_format"));
?>

<div class="row">
	<div class="col-md-4 col-xs-12 col-sm-12">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo lang("meetings_upcoming_meetings"); ?></div>
<?php	if (count($waitingMeetings)) { ?>
			<ul class="list-group meetings">
<?php		foreach($waitingMeetings as $meeting) { 

				$date = getDateTime($meeting["mee_datetime"]);
				$dateFormat = $date->format(lang("date_format"));

				if ($dateFormat == $nowFormat) {
					$date = str_replace("{time}", $date->format(lang("time_format")), lang("todaytime_format"));
				}
				else if ($dateFormat == $tomorrowFormat) {
					$date = str_replace("{time}", $date->format(lang("time_format")), lang("tomorrowtime_format"));
				}
				else {
					$date = str_replace("{date}", $dateFormat, str_replace("{time}", $date->format(lang("time_format")), lang("datetime_format")));
				}

?>
				<li class="list-group-item <?php echo $meeting["mee_class"]; ?>">
					<div class="pull-right"><?php echo $date; ?></div>
					<div><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></div>
					<br>
					<div><em><?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?></em></div>
				</li>
<?php		} ?>
			</ul>
<?php	}
		else { ?>
			<div class="panel-body">
				<?php echo lang("meetings_no_upcoming_meeting"); ?>
			</div>
<?php	} ?>
		</div>
	</div>	

	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo lang("meetings_ongoing_meetings"); ?></div>
<?php	if (count($meetings)) { ?>
			<ul class="list-group meetings">
<?php		foreach($meetings as $meeting) { 

				$start = new DateTime($meeting["mee_datetime"]);
				$end = new DateTime($meeting["mee_datetime"]);
				$duration = new DateInterval("PT" . ($meeting["mee_expected_duration"] ? $meeting["mee_expected_duration"] : 60) . "M");
				$end = $end->add($duration);

				$date = $end;
				$dateFormat = $date->format(lang("date_format"));

				if ($dateFormat == $nowFormat) {
					$date = str_replace("{time}", $date->format(lang("time_format")), lang("until_todaytime_format"));
				}
				else if ($dateFormat == $tomorrowFormat) {
					$date = str_replace("{time}", $date->format(lang("time_format")), lang("until_tomorrowtime_format"));
				}
				else {
					$date = str_replace("{date}", $dateFormat, str_replace("{time}", $date->format(lang("time_format")), lang("until_datetime_format")));
				}

//				print_r($meeting);
?>
				<li class="list-group-item <?php echo $meeting["mee_class"]; ?>">
					<div class="pull-right"><?php echo $date; ?></div>
					<div><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></div>
					<br>
					<div><em><?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?></em></div>
				</li>
<?php		} ?>
			</ul>
<?php	}
		else { ?>
			<div class="panel-body">
				<?php echo lang("meetings_no_ongoing_meeting"); ?>
			</div>
<?php	} ?>
		</div>
	</div>	
	
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading"><?php echo lang("meetings_passed_meetings"); ?></div>
<?php	if (count($closedMeetings)) { ?>
			<ul class="list-group meetings">
<?php		foreach($closedMeetings as $meeting) { 

				$date = getDateTime($meeting["mee_datetime"]);
				$dateFormat = $date->format(lang("date_format"));

				if ($dateFormat == $nowFormat) {
					$date = lang("today");
				}
				else if ($dateFormat == $yesterdayFormat) {
					$date = lang("yesterday");
				}
				else {
					$date = $dateFormat;
				}

?>
				<li class="list-group-item <?php echo $meeting["mee_class"]; ?>">
					<div class="pull-right"><?php echo $date; ?></div>
					<div><a href="meeting.php?id=<?php echo $meeting["mee_id"]; ?>"><?php echo $meeting["mee_label"]; ?></a></div>
					<br>
					<div><em><?php echo lang("createMeeting_base_type_" . $meeting["mee_type"]); ?></em></div>
				</li>
<?php		} ?>
			</ul>
<?php	}
		else { ?>
			<div class="panel-body">
				<?php echo lang("meetings_no_passed_meeting"); ?>
			</div>
<?php	} ?>
		</div>
	</div>	
</div>


<?php 	if ($isConnected) {?>


<?php 	} else {?>


<?php 	}?>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>

</body>
</html>
