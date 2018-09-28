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
require_once("header.php");

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

//echo "<!-- \n";
//print_r($trendingMotions);
//echo "\n -->";

//print_r($meetings);

?>

<div class="container theme-showcase" role="main" id="main" tabindex="-1">
	<ol class="breadcrumb">
		<li class="active"><?php echo lang("breadcrumb_index"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("index_guide"); ?></p>
	</div>

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

	<?php	if (count($trendingMotions)) { ?>
	
	<div class="row">
	<?php		foreach($trendingMotions as $motion) { ?>
		<div class="<?php echo $headingColMd; ?>">
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

	<div class="calendar-nav clearfix">
		<div class="pull-right form-inline" style="margin-top: 15px;">
			<div class="btn-group">
				<button class="btn btn-primary" data-calendar-nav="prev">&lt;&lt; <?php echo lang("calendar_prev"); ?></button>
				<button class="btn btn-default" data-calendar-nav="today"><?php echo lang("calendar_today"); ?></button>
				<button class="btn btn-primary" data-calendar-nav="next"><?php echo lang("calendar_next"); ?> &gt;&gt;</button>
			</div>
			<div class="btn-group">
				<button class="btn btn-warning" data-calendar-view="year"><?php echo lang("calendar_year"); ?></button>
				<button class="btn btn-warning active" data-calendar-view="month"><?php echo lang("calendar_month"); ?></button>
				<button class="btn btn-warning" data-calendar-view="week"><?php echo lang("calendar_week"); ?></button>
				<button class="btn btn-warning" data-calendar-view="day"><?php echo lang("calendar_day"); ?></button>
			</div>
		</div>
		<h3>&nbsp;</h3>
	</div>

	<br />

	<div id="calendar"></div>

	<div class="text-center"><a href="do_downloadCalendar.php"><?php echo lang("index_downloadCalendar"); ?></a></div>


<?php 	if ($isConnected) {?>


<?php 	} else {?>


<?php 	}?>

<?php include("connect_button.php"); ?>

</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>
<script type="text/javascript">
/* global $ */
$(function() {
	var calendar = $("#calendar").calendar(
            {
            	language: "fr-FR",
                tmpl_path: "tmpls/",
                events_source: "do_getMeetings.php",
                time_end: "23:30",
                onAfterViewLoad: function(view) {
        			$('.calendar-nav h3').text(this.getTitle());
        			$('.btn-group button').removeClass('active');
        			$('button[data-calendar-view="' + view + '"]').addClass('active');
        		},
            });

	$('.btn-group button[data-calendar-nav]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.navigate($this.data('calendar-nav'));
		});
	});

	$('.btn-group button[data-calendar-view]').each(function() {
		var $this = $(this);
		$this.click(function() {
			calendar.view($this.data('calendar-view'));
		});
	});

});
</script>

</body>
</html>
