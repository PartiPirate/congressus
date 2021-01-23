<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
$queryBuilder->select("galette.galette_month_adherents");
$queryBuilder->addSelect("*");
$queryBuilder->orderASCBy("year");
$queryBuilder->orderASCBy("month");

$query = $queryBuilder->constructRequest();
$statement = $connection->prepare($query);
$statement->execute(array());
$byMonthMembers = $statement->fetchAll();

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
$queryBuilder->select("don.accepted_month_transactions");
$queryBuilder->addSelect("*");
$queryBuilder->orderASCBy("tra_month_date");

$query = $queryBuilder->constructRequest();
$statement = $connection->prepare($query);
$statement->execute(array());
$byMonthTransactions = $statement->fetchAll();

$now = getNow();
$maxMonth = $now->format("Y-m");

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);
$queryBuilder->select("personae.dlp_fixations");
$queryBuilder->addSelect("*");
$queryBuilder->join("personae.dlp_themes", "the_current_fixation_id	 = fix_id");
$queryBuilder->join("personae.dlp_group_themes", "the_id = gth_theme_id");
$queryBuilder->join("personae.dlp_groups", "gro_id = gth_group_id");
$queryBuilder->where("gro_deleted = 0");
$queryBuilder->where("the_deleted = 0");
$queryBuilder->where("gro_label like '%quipage%'");
$queryBuilder->where("the_label like '%embre%'");
$queryBuilder->orderASCBy("fix_until_date");

$query = $queryBuilder->constructRequest();
$statement = $connection->prepare($query);
$statement->execute(array());
$fixations = $statement->fetchAll();


$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);

$queryBuilder->select("votes");
$queryBuilder->addSelect("DATE_FORMAT(mee_start_time, '%Y-%m')", "vot_month");
$queryBuilder->addSelect("count(distinct vot_member_id)", "vot_number_of_votes");
$queryBuilder->join("motion_propositions", "mpr_id = vot_motion_proposition_id");
$queryBuilder->join("motions", "mot_id = mpr_motion_id");
$queryBuilder->join("agendas", "age_id = mot_agenda_id");
$queryBuilder->join("meetings", "mee_id = age_meeting_id");
$queryBuilder->where("vot_power > 0");
$queryBuilder->where("mee_status = 'closed'");
//$queryBuilder->where("mee_type = 'meeting'");
$query = $queryBuilder->constructRequest();

$query .= " group by DATE_FORMAT(mee_start_time, '%Y-%m') ASC";

$statement = $connection->prepare($query);
$statement->execute(array());
$byMonthVoters = $statement->fetchAll();

$queryBuilder = QueryFactory::getInstance($config["database"]["dialect"]);

$queryBuilder->select("votes");
$queryBuilder->addSelect("DATE_FORMAT(mee_start_time, '%Y-%m')", "vot_month");
$queryBuilder->addSelect("count(distinct vot_member_id)", "vot_number_of_votes");
$queryBuilder->join("motion_propositions", "mpr_id = vot_motion_proposition_id");
$queryBuilder->join("motions", "mot_id = mpr_motion_id");
$queryBuilder->join("agendas", "age_id = mot_agenda_id");
$queryBuilder->join("meetings", "mee_id = age_meeting_id");
$queryBuilder->join("notices", "not_meeting_id = mee_id");
$queryBuilder->where("vot_power > 0");
$queryBuilder->where("mee_status = 'closed'");
$queryBuilder->where("mee_type = 'meeting'");
$queryBuilder->where("not_target_id = 46");
$queryBuilder->where("not_target_type = 'dlp_themes'");
$query = $queryBuilder->constructRequest();

$query .= " group by DATE_FORMAT(mee_start_time, '%Y-%m') ASC";

$statement = $connection->prepare($query);
$statement->execute(array());
$byMonthAPVoters = $statement->fetchAll();

$query = "select *, DATE_FORMAT(mee_start_time, '%Y-%m') as mee_month
from motion_propositions
join motions on mot_id = mpr_motion_id
join agendas on age_id = mot_agenda_id
join meetings on mee_id = age_meeting_id
join notices on not_meeting_id = mee_id 
where 
	mee_status = 'closed'
-- AND age_id = 5160
AND not_target_id = 46 and not_target_type = 'dlp_themes'
AND mee_type = 'meeting'";
$statement = $connection->prepare($query);
$statement->execute(array());
$propositions = $statement->fetchAll();

$propositionAnalisys = array();
$byMonthDelegations = array();

foreach($propositions as $proposition) {
	if (!isset($propositionAnalisys[$proposition["mee_month"]])) {
		$propositionAnalisys[$proposition["mee_month"]] = array("nb_program_motions" => 0, "nb_crew_motions" => 0, "nb_other_motions" => 0, "nb_motions" => 0, 
																"nb_program_motions_pro" => 0, "nb_crew_motions_pro" => 0, "nb_other_motions_pro" => 0, "nb_motions_pro" => 0, 
																"mee_motions" => array(), "mon_voters" => array(), "mon_total_power" => 0);
	}

	$motionType = "nb_other_motions";

	if (strpos(strtolower($proposition["mot_title"]), "programme") !== false) {
		$motionType = "nb_program_motions";
	}
	else if (strpos(strtolower($proposition["mot_title"]), "quipage") !== false) {
		$motionType = "nb_crew_motions";
	}

	if (!isset($propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]])) {
		$propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]] = $proposition;
		$propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]]["mot_propositions"] = array();
		$propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]]["mot_voters"] = array();

		$propositionAnalisys[$proposition["mee_month"]][$motionType]++;
		$propositionAnalisys[$proposition["mee_month"]]["nb_motions"]++;
	}

	if ((strtolower($proposition["mpr_label"]) == "pour" || strtolower($proposition["mpr_label"]) == "oui") && $proposition["mpr_winning"]) {
		$propositionAnalisys[$proposition["mee_month"]][$motionType . "_pro"]++;
		$propositionAnalisys[$proposition["mee_month"]]["nb_motions_pro"]++;
	}

	$propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]]["mot_propositions"][$proposition["mpr_id"]] = $proposition;
	$propositionAnalisys[$proposition["mee_month"]]["mee_motions"][$proposition["mot_id"]]["mot_propositions"][$proposition["mpr_id"]]["mpr_voters"] = array();
}

foreach($propositionAnalisys as $month => &$monthMotions) {
	foreach($monthMotions["mee_motions"] as &$motion) {
		foreach($motion["mot_propositions"] as &$proposition) {
			$explanation = json_decode($proposition["mpr_explanation"], true);
			if (!isset($explanation["votes"])) continue;

			if (false && $month == "2020-08") {
				echo $proposition["age_id"];
				echo "<br>";
				echo $proposition["mpr_explanation"];
				echo "<br>";
				print_r($explanation["votes"]);
				echo "<br>";
			}

			foreach($explanation["votes"] as &$vote) {
				$proposition["mpr_voters"][$vote["memberId"]] = $vote["votePower"];

				if (!isset($motion["mot_voters"][$vote["memberId"]])) {
					$motion["mot_voters"][$vote["memberId"]] = 0;
				}

				$motion["mot_voters"][$vote["memberId"]] = $vote["votePower"];


				if (!isset($monthMotions["mon_voters"][$vote["memberId"]])) {
					$monthMotions["mon_voters"][$vote["memberId"]] = 0;
				}

				if (false && $month == "2020-08") echo $vote["memberId"] . " => " . $vote["votePower"] . "<br>";

				$monthMotions["mon_voters"][$vote["memberId"]] = max($monthMotions["mon_voters"][$vote["memberId"]], $vote["votePower"]);
			}
		}
	}

	foreach($monthMotions["mon_voters"] as $voterId => $votePower) {
		$monthMotions["mon_total_power"] += $votePower;
	}

//	echo "$month => " . ($monthMotions["mon_total_power"] / 100) . "<br>";
	
	$byMonthDelegations[$month] = $monthMotions["mon_total_power"];
}

//echo print_r($byMonthDelegations, true);
//echo print_r($propositionAnalisys["2020-08"]["mon_voters"], true);
//echo print_r($propositionAnalisys["2020-08"]["mon_total_power"], true);

?>

<div class="container theme-showcase" role="main">

<h2 id="members">Membres</h2>

<div>

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#memberChartsDiv" aria-controls="memberChartsDiv" role="tab" data-toggle="tab">Chart</a></li>
	<li role="presentation"><a href="#memberChartsData" aria-controls="memberChartsData" role="tab" data-toggle="tab">Data</a></li>
</ul>

<div class="tab-content">
<div  role="tabpanel" class="tab-pane active" id="memberChartsDiv"  style="width: 100%; ">
<canvas id="memberCharts" style="width: 100%; height: 600px; " width=1140 height=600></canvas>
</div>
<div  role="tabpanel" class="tab-pane" id="memberChartsData"  style="width: 100%; max-height: 600px; overflow: scroll;">
	<table class="table table-condensed table-striped table-hover">
		<thead>
			<tr>
				<th style="text-align: center; min-width: 125px;">Mois</th>
				<th style="text-align: center; width: 16%;">Nombre d'adhérents</th>
				<th style="text-align: center; width: 16%;">Variation du Nombre d'adhérents</th>
				<th style="text-align: center; width: 16%;">Nombre d'adhérents ayant participé à un vote/sondage</th>
				<th style="text-align: center; width: 16%;">Nombre de personnes ayant voté en assemblée permanente</th>
				<th style="text-align: center; width: 16%;">Nombre de personnes, en comptant les délégations, ayant voté en assemblée permanente</th>
				<th style="text-align: center; width: 16%;">Participation</th>
			</tr>
		</thead>
		<tbody>
<?php	foreach($byMonthMembers as $index => $monthMember) {
			$monthString = $monthMember["year"]."-".$monthMember["month"];
			if ($monthString > $maxMonth) continue;
			$date = getDateTime($monthString);
?>
			<tr>
				<td style="text-align: left;">
						<?=html_entity_decode(dateTranslate($date->format("F Y")))?>
				</td>
				<td style="text-align: right;">
						<?=$monthMember["nb_adh"]?>
				</td>
				<td style="text-align: right;">
						<?=$monthMember["nb_adh"] - $byMonthMembers[$index == 0 ? 0 : $index - 1]["nb_adh"]?>
				</td>
				<td style="text-align: right;">
<?php
			for($vindex = 0; $vindex < count($byMonthVoters); $vindex++) {
				if ($byMonthVoters[$vindex]["vot_month"] == $monthString) {
					echo $byMonthVoters[$vindex]["vot_number_of_votes"];
					break;
				}
			}
?>			
				</td>
				<td style="text-align: right;">
<?php
			for($vindex = 0; $vindex < count($byMonthAPVoters); $vindex++) {
				if ($byMonthAPVoters[$vindex]["vot_month"] == $monthString) {
					echo $byMonthAPVoters[$vindex]["vot_number_of_votes"];
					break;
				}
			}
?>
				</td>
				<td style="text-align: right;">
<?php
					if (isset($byMonthDelegations[$monthString])) {
						echo $byMonthDelegations[$monthString] / 100;
					}
?>
				</td>
				<td style="text-align: right;">
<?php
					if (isset($byMonthDelegations[$monthString])) {
						echo round($byMonthDelegations[$monthString] / 100 / $monthMember["nb_adh"] * 100, 2) . "%";
					}
					else {
						$found = false;
						for($vindex = 0; $vindex < count($byMonthAPVoters); $vindex++) {
							if ($byMonthAPVoters[$vindex]["vot_month"] == $monthString) {
								echo round($byMonthAPVoters[$vindex]["vot_number_of_votes"] / $monthMember["nb_adh"] * 100, 2) . "%";
								$found = true;
								break;
							}
						}

						if (!$found) {
							for($vindex = 0; $vindex < count($byMonthVoters); $vindex++) {
								if ($byMonthVoters[$vindex]["vot_month"] == $monthString) {
									echo round($byMonthVoters[$vindex]["vot_number_of_votes"] / $monthMember["nb_adh"] * 100, 2) . "%";
									$found = true;
									break;
								}
							}
						}
					}
?>
				</td>
			</tr>
<?php	} ?>
		</tbody>	
	</table>
</div>
</div>
</div>

<script>
$(function() {
	var color = Chart.helpers.color;
	var config = {
		type: 'line',
		data: {
			labels: [
<?php	foreach($byMonthMembers as $monthMember) {
			if ($monthMember["year"]."-".$monthMember["month"] > $maxMonth) continue;
			$date = getDateTime($monthMember["year"]."-".$monthMember["month"]);
?>
						"<?=html_entity_decode(dateTranslate($date->format("F Y")))?>",
<?php	} ?>
					],
			datasets: [{
				label: 'Nombre d\'adhérents',
				backgroundColor: "hsla(120, 39%, 54%, 0)",
				borderColor: "hsla(120, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthMembers as $index => $monthMember) {
			if ($monthMember["year"]."-".$monthMember["month"] > $maxMonth) continue;
?>
						<?=$monthMember["nb_adh"]?>,
<?php	} ?>
						]
			},{
				label: 'Variation du Nombre d\'adhérents',
				backgroundColor: "hsla(180, 39%, 54%, 0)",
				borderColor: "hsla(180, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthMembers as $index => $monthMember) {
			if ($monthMember["year"]."-".$monthMember["month"] > $maxMonth) continue;
?>
						<?=$monthMember["nb_adh"] - $byMonthMembers[$index == 0 ? 0 : $index - 1]["nb_adh"]?>,
<?php	} ?>
						]
			},{
				label: 'Nombre d\'adhérents ayant participé à un vote/sondage',
				backgroundColor: "hsla(0, 39%, 54%, 0)",
				borderColor: "hsla(0, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthMembers as $index => $monthMember) {

			$found = false;

			for($vindex = 0; $vindex < count($byMonthVoters); $vindex++) {
				if ($byMonthVoters[$vindex]["vot_month"] == $monthMember["year"]."-".$monthMember["month"]) {
					echo $byMonthVoters[$vindex]["vot_number_of_votes"];
					$found = true;
					break;
				}
			}
			
			if (!$found) {
				echo "0";
			}
			echo ",";
		} ?>
						]
			},{
				label: 'Nombre de personnes ayant voté en assemblée permanente',
				backgroundColor: "hsla(300, 39%, 54%, 0)",
				borderColor: "hsla(300, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthMembers as $index => $monthMember) {

			$found = false;

			for($vindex = 0; $vindex < count($byMonthAPVoters); $vindex++) {
				if ($byMonthAPVoters[$vindex]["vot_month"] == $monthMember["year"]."-".$monthMember["month"]) {
					echo $byMonthAPVoters[$vindex]["vot_number_of_votes"];
					$found = true;
					break;
				}
			}
			
			if (!$found) {
				echo "0";
			}
			echo ",";
		} ?>
						]
			},{
				label: 'Nombre de personnes, en comptant les délégations, ayant voté en assemblée permanente',
				backgroundColor: "hsla(240, 39%, 54%, 0)",
				borderColor: "hsla(240, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthMembers as $index => $monthMember) {
			$monthString = $monthMember["year"] . "-" . $monthMember["month"];
			if ($monthString > $maxMonth) continue;

			$currentDelegationPower = 0;

			if (isset($byMonthDelegations[$monthString])) {
				$currentDelegationPower = $byMonthDelegations[$monthString];
			}
/*
			foreach($byMonthDelegations as $month => $monthDelegationPower) {
				if ($month != $monthString) continue;
				echo "// " . $month . " => " . $monthString . ",  " . $currentDelegationPower . "\n";
				$currentMotions = $monthDelegationPower;
			}
*/			
//				echo "// " . $month . " => " . $monthString . ",  " . $currentDelegationPower . "\n";

?>
						<?=$currentDelegationPower / 100?>,
<?php	} ?>
						]
			}]},
		options: {
			responsive: true,
			legend: {
				position: 'top',
			},
			title: {
				display: true,
				text: 'Adhérents du Parti Pirate au cours du temps'
			},
			tooltips: {
				mode: 'index',
				intersect: false
			}
		}
	};

	const ctx = $("#memberCharts").get(0).getContext('2d');
	new Chart(ctx, config);
});
</script>

<h2 id="motions">Motions</h2>

<div id="motionChartsDiv"  style="width: 100%; ">
<canvas id="motionCharts" style="width: 100%; height: 600px; " width=1140 height=600></canvas>
</div>

<script>
$(function() {
	var color = Chart.helpers.color;
	var config = {
		type: 'bar',
		data: {
			labels: [
<?php	foreach($byMonthTransactions as $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
			$date = getDateTime($monthTransaction["tra_month_date"]);
?>
						"<?=html_entity_decode(dateTranslate($date->format("F Y")))?>",
<?php	} ?>
					],
			datasets: [{
				label: 'Motions',
				yAxisID: 'y-axis-1',
				stack: 'Stack 1',
				backgroundColor: "hsla(0, 39%, 54%, 0.3)",
				borderColor: "hsla(0, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_motions"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Motions programmatiques',
				yAxisID: 'y-axis-1',
				stack: 'Stack 0',
				backgroundColor: "hsla(60, 39%, 54%, 0.3)",
				borderColor: "hsla(60, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_program_motions"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Motions d\'équipage',
				yAxisID: 'y-axis-1',
				stack: 'Stack 0',
				backgroundColor: "hsla(120, 39%, 54%, 0.3)",
				borderColor: "hsla(120, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_crew_motions"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Autre motions',
				yAxisID: 'y-axis-1',
				stack: 'Stack 0',
				backgroundColor: "hsla(180, 39%, 54%, 0.3)",
				borderColor: "hsla(180, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_other_motions"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Motions acceptées',
				yAxisID: 'y-axis-1',
				stack: 'Stack 3',
				backgroundColor: "hsla(0, 39%, 54%, 0.3)",
				borderColor: "hsla(0, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_motions_pro"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Motions programmatiques acceptées',
				yAxisID: 'y-axis-1',
				stack: 'Stack 2',
				backgroundColor: "hsla(60, 39%, 54%, 0.3)",
				borderColor: "hsla(60, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_program_motions_pro"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Motions d\'équipage acceptées',
				yAxisID: 'y-axis-1',
				stack: 'Stack 2',
				backgroundColor: "hsla(120, 39%, 54%, 0.3)",
				borderColor: "hsla(120, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_crew_motions_pro"] : "0"?>,
<?php	} ?>
						]
			},{
				label: 'Autre motions acceptées',
				yAxisID: 'y-axis-1',
				stack: 'Stack 2',
				backgroundColor: "hsla(180, 39%, 54%, 0.3)",
				borderColor: "hsla(180, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;

			$currentMotions = null;

			foreach($propositionAnalisys as $month => $monthMotions) {
				if ($month != $monthTransaction["tra_month_date"]) continue;
				$currentMotions = $monthMotions;
			}

?>
						<?=$currentMotions ? $currentMotions["nb_other_motions_pro"] : "0"?>,
<?php	} ?>
						]
			}]},
		options: {
			responsive: true,
			legend: {
				position: 'top',
			},
			title: {
				display: true,
				text: 'Vue sur les motions'
			},
			tooltips: {
				mode: 'index',
				intersect: false
			},
			scales: {
				xAxes: [{
					stacked: true,
				}],
				yAxes: [{
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'left',
					stacked: true,
					id: 'y-axis-1',
				}, {
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'right',
					id: 'y-axis-2',
					stacked: true,
				}]
			}
		}
	};

	const ctx = $("#motionCharts").get(0).getContext('2d');
	new Chart(ctx, config);
});
</script>

<h2 id="transactions">Flux financier entrant</h2>


<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation" class="active"><a href="#transactionChartsDiv" aria-controls="transactionChartsDiv" role="tab" data-toggle="tab">Chart</a></li>
	<li role="presentation"><a href="#transactionChartsData" aria-controls="transactionChartsData" role="tab" data-toggle="tab">Data</a></li>
</ul>

<div class="tab-content">
<div role="tabpanel" class="tab-pane active" id="transactionChartsDiv"  style="width: 100%; ">
<canvas id="transactionCharts" style="width: 100%; height: 600px; " width=1140 height=600></canvas>
</div>
<div  role="tabpanel" class="tab-pane" id="transactionChartsData"  style="width: 100%; max-height: 600px; overflow: scroll;">
	<table class="table table-condensed table-striped table-hover">
		<thead>
			<tr>
				<th style="text-align: center; min-width: 125px;">Mois</th>
				<th style="text-align: center; width: 12.5%;">Nombre d'adhésions</th>
				<th style="text-align: center; width: 12.5%;">Nombre de dons</th>
				<th style="text-align: center; width: 12.5%;">Montant adhésions</th>
				<th style="text-align: center; width: 12.5%;">Montant dons</th>
				<th style="text-align: center; width: 12.5%;">Moyenne adhésion</th>
				<th style="text-align: center; width: 12.5%;">Moyenne don</th>
				<th style="text-align: center; width: 12.5%;">Total</th>
				<th style="text-align: center; width: 12.5%;">Cumul</th>
			</tr>
		</thead>
		<tbody>
<?php	
		$totalAmount = 0;
		$numberOfMonths = 0;

		foreach($byMonthTransactions as $index => $monthTransaction) {
			$totalAmount += $monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"];
			$numberOfMonths++;
		}

		$avgAmount = $totalAmount / $numberOfMonths;

		$standardDeviation = 0;

		foreach($byMonthTransactions as $index => $monthTransaction) {
			$standardDeviation += ($monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"] - $avgAmount) * ($monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"] - $avgAmount);
		}

		$standardDeviation = sqrt($standardDeviation) / $numberOfMonths;

		$totalAmount = 0;
		$numberOfMonths = 0;
		$numberOfStandardDeviations = 5;

		foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"] > $avgAmount + $numberOfStandardDeviations * $standardDeviation) {
				$byMonthTransactions[$index]["tra_excluded"] = 1;
			}
			else {
				$byMonthTransactions[$index]["tra_excluded"] = 0;
				$totalAmount += $monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"];
				$numberOfMonths++;
			}
		}

		$avgAmount2 = $totalAmount / $numberOfMonths;
		$standardDeviation2 = 0;

		foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_excluded"]) continue;
			$standardDeviation2 += ($monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"] - $avgAmount2) * ($monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"] - $avgAmount2);
		}

		$standardDeviation2 = sqrt($standardDeviation2) / $numberOfMonths;

		foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
			$date = getDateTime($monthTransaction["tra_month_date"]);

			$monthTransaction["tra_month_total_amount"] = $monthTransaction["tra_month_join_amount"] + $monthTransaction["tra_month_donation_amount"];

			$byMonthTransactions[$index]["tra_month_cumul_amount"] = $monthTransaction["tra_month_total_amount"];

			if ($index != 0 && substr($monthTransaction["tra_month_date"], 4, 3) != "-01") {
				$byMonthTransactions[$index]["tra_month_cumul_amount"] += $byMonthTransactions[$index - 1]["tra_month_cumul_amount"];
			}

			$monthTransaction["tra_month_cumul_amount"] = $byMonthTransactions[$index]["tra_month_cumul_amount"];

?>
			<tr class="<?=$monthTransaction["tra_excluded"] ? "success" : ($monthTransaction["tra_month_total_amount"] > $avgAmount ? "info" : ($monthTransaction["tra_month_total_amount"] > $avgAmount2 ? "text-info" : ""))?>">
				<td><?=html_entity_decode(dateTranslate($date->format("F Y")))?></td>
				<td style="text-align: right;"><?=$monthTransaction["tra_month_joins"]?></td>
				<td style="text-align: right;"><?=$monthTransaction["tra_month_donations"]?></td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_join_amount"], 2, ',', ' ')?>&euro;</td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_donation_amount"], 2, ',', ' ')?>&euro;</td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_joins"] ? $monthTransaction["tra_month_join_amount"] / $monthTransaction["tra_month_joins"] : 0, 2, ',', ' ')?>&euro;</td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_donations"] ? $monthTransaction["tra_month_donation_amount"] / $monthTransaction["tra_month_donations"] : 0, 2, ',', ' ')?>&euro;</td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_total_amount"], 2, ',', ' ')?>&euro;</td>
				<td style="text-align: right;"><?=number_format($monthTransaction["tra_month_cumul_amount"], 2, ',', ' ')?>&euro;</td>
			</tr>
<?php	} ?>
		</tbody>
	</table>
	<div>
		Montant moyen par mois : <?=number_format($avgAmount, 2, ',', ' ')?>&euro;
	</div>
	<div>
		Ecart-type : <?=number_format($standardDeviation, 2, ',', ' ')?>&euro;
	</div>
	<div>
		Montant moyen par mois après exclusion (après <?=$numberOfStandardDeviations?> écarts-types) : <?=number_format($avgAmount2, 2, ',', ' ')?>&euro;
	</div>
	<div>
		Ecart-type après exclusion : <?=number_format($standardDeviation2, 2, ',', ' ')?>&euro;
	</div>
</div>
</div>







<script>
$(function() {
	var color = Chart.helpers.color;
	var config = {
		type: 'bar',
		data: {
			labels: [
<?php	foreach($byMonthTransactions as $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
			$date = getDateTime($monthTransaction["tra_month_date"]);
?>
						"<?=html_entity_decode(dateTranslate($date->format("F Y")))?>",
<?php	} ?>
					],
			datasets: [{
				label: 'Montant adhésions',
				yAxisID: 'y-axis-1',
				stack: 'Stack 0',
				backgroundColor: "hsla(0, 39%, 54%, 0.3)",
				borderColor: "hsla(0, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
?>
						<?=$monthTransaction["tra_month_join_amount"]?>,
<?php	} ?>
						]
			},{
				label: 'Montant dons',
				yAxisID: 'y-axis-1',
				stack: 'Stack 0',
				backgroundColor: "hsla(120, 39%, 54%, 0.3)",
				borderColor: "hsla(120, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
?>
						<?=$monthTransaction["tra_month_donation_amount"]?>,
<?php	} ?>
						]
			},{
				label: 'Nombre d\'adhésions',
				yAxisID: 'y-axis-2',
				stack: 'Stack 1',
				backgroundColor: "hsla(60, 39%, 54%, 0.3)",
				borderColor: "hsla(60, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
?>
						<?=$monthTransaction["tra_month_joins"]?>,
<?php	} ?>
						]
			},{
				label: 'Nombre de dons',
				yAxisID: 'y-axis-2',
				stack: 'Stack 1',
				backgroundColor: "hsla(180, 39%, 54%, 0.3)",
				borderColor: "hsla(180, 39%, 54%, 1)",
				borderWidth: 1,
				cubicInterpolationMode: 'monotone',
				data:	[
<?php	foreach($byMonthTransactions as $index => $monthTransaction) {
			if ($monthTransaction["tra_month_date"] > $maxMonth) continue;
?>
						<?=$monthTransaction["tra_month_donations"]?>,
<?php	} ?>
						]
			}]},
		options: {
			responsive: true,
			legend: {
				position: 'top',
			},
			title: {
				display: true,
				text: 'Transactions entrantes du Parti Pirate'
			},
			tooltips: {
				mode: 'index',
				intersect: false
			},
			scales: {
				xAxes: [{
					stacked: true,
				}],
				yAxes: [{
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'left',
					stacked: true,
					id: 'y-axis-1',
				}, {
					type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
					display: true,
					position: 'right',
					id: 'y-axis-2',
					stacked: true,
				}]
			}
		}
	};

	const ctx = $("#transactionCharts").get(0).getContext('2d');
	new Chart(ctx, config);
});
</script>

<h2 id="crews">Registre des équipages</h2>

<div id="crewsDiv">
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>Equipage</th>
			<th>Date de renouvellement</th>
		</tr>
	</thead>
	<tbody>
<?php	foreach($fixations as $fixation) { 
			if ($fixation["fix_until_date"] == "0000-00-00") continue;
			$date = getDateTime($fixation["fix_until_date"]);
			$dateString = dateTranslate($date->format("F Y"));

			$diff = $date->diff($now);

			$dateClass = "";

			if (!$diff->invert) {
				$dateClass = "danger";
			}
			else if ($diff->y == 0 && $diff->m < 3) {
				$dateClass = "warning";
			}
?>
		<tr class="<?=$dateClass?>">
			<td><?=$fixation["gro_label"]?></td>
			<td><?=$dateString?></td>
		</tr>
<?php	} ?>
	</tbody>
</table>
</div>

</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>


</body>
</html>
