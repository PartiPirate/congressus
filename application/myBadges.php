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

$badges = $gamifierClient->getBadges($config["gamifier"]["service_uuid"], $config["gamifier"]["service_secret"]);
$notices = array();

if (isset($gamifiedUser)) {

	foreach($gamifiedUser["data"]["badges"] as $userBadge) {
//		print_r($userBadge);
		if (!$userBadge["noticed"]) {
			$notices[] = array("service_uuid" => $config["gamifier"]["service_uuid"], "service_secret" => $config["gamifier"]["service_secret"], "user_uuid" => sha1($config["gamifier"]["user_secret"] . $sessionUserId), "badge_uuid" => $userBadge["uuid"]);
		}
	}
}

if (count($notices)) {
	$gamifierClient->setNoticed($notices);	
}
//print_r($notices);


?>

<style>

.blink {
  animation-duration: 2000ms;    /*blinking speed decreases and increase */
  animation-name: tgle;
  animation-iteration-count: 5;
}

@keyframes tgle {
  0% {
    opacity: 0.25;
  }

  50% {
    opacity: 1;
  }

  100% {
    opacity: 0.25;
  }
}
 </style>


<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_mybadges"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("mybadges_guide"); ?></p>
	</div>

	<?php 

//	print_r($badges);

/*	if (isset($gamifiedUser)) {
		print_r($gamifiedUser);
	}
*/	

		foreach($badges["data"]["badges"] as $index => $badge) { 
		
			$badgeStatus = "default";
			$count = 0;

			if (isset($gamifiedUser)) {
				$notNoticed = false;
				foreach($gamifiedUser["data"]["badges"] as $userBadge) {
					if ($userBadge["uuid"] == $badge["uuid"]) {
						$count++;
						
						if (!$userBadge["noticed"]) $notNoticed = true;
					}
				}
				
				if ($count) {
					$badgeStatus = "success";
				}
				
				if ($notNoticed) {
					$badgeStatus .= " blink";
				}
			}
?>

<div class="col-xs-6 col-md-3">
	<div class="panel panel-<?php echo $badgeStatus; ?> ">
		<div class="panel-heading"><a data-toggle="collapse" class="collapsed" data-target="#rule-<?php echo $badge["uuid"]; ?>" href="#"><?php echo $badge["label"]; ?></a></div>
		<div id="rule-<?php echo $badge["uuid"]; ?>" class="panel-body panel-collapse collapse">
			<?php //echo $badge["rule"]; ?>
			<?php echo $badge["description"]; ?>
		</div>
		<div class="panel-footer text-right">&nbsp; <?php if ($count) {?><span class="glyphicon glyphicon-tag<?php if ($count > 1) echo "s"; ?>"></span> <?php echo $count; ?><?php } ?></div>
	</div>
</div>

<?php 
if ((($index + 1) % 4) == 0) {
?>
<div style="clear: both;"></div>
<?php 
}
?>

<?php	}
?>

<?php include("connect_button.php"); ?>

</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
</script>
<?php include("footer.php");?>

</body>
</html>