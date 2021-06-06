<?php /*
    Copyright 2021 Cédric Levieux, Parti Pirate

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

?>

    <div class="motion-charts" data-status="to-init">
		<canvas class="chart-area" style="width: 100%; height: 400px;"></canvas>
    </div>
    <script>
<?php   if ($object["mot_win_limit"] > 0) { ?>
$(function() {
    const chartMotionContainer = $("#motion-<?=$object["mot_id"]?>");

    initPercentChart(chartMotionContainer);
    
	var chartData = {};

	chartData.labels = [];
	chartData.datasets = [{
		data: [
		],
		backgroundColor: [
		],
		borderWidth: [
		],
		borderColor: [
		],
		label: ''
	}];

	var datahash = "";

    const data = {propositions: []};
    let proposition = null;

<?php   $totalPower = 0;
        foreach($object["propositions"] as $proposition) { 
            $explanation = json_decode($proposition["mpr_explanation"], true);
            $totalPower += $explanation["power"] * ($proposition["mpr_neutral"] ? 0 : 1);
            $dataProposition = array("mpr_id" => $proposition["mpr_id"], "mpr_label" => $proposition["mpr_label"], "mpr_neutral" => $proposition["mpr_neutral"], "total_power" => round($explanation["power"], 2));
    ?>

    proposition = <?=json_encode($dataProposition, JSON_NUMERIC_CHECK);?>;
    data.propositions.push(proposition);
    
<?php   } ?>

//debugger;

	for(var index = 0; index < data.propositions.length; ++index) {

		if (data.propositions[index].mpr_neutral && data.propositions[index].mpr_neutral !== "0") continue;
		if (!data.propositions[index].mpr_label) continue;
		if (!data.propositions[index].total_power) continue;
		
		var label = shortenLabel(data.propositions[index].mpr_label, 20);
		
		chartData.labels.push(label);
		chartData.datasets[0].data.push(data.propositions[index].total_power);

		var color = chartColors[data.propositions[index].mpr_id % chartColors.length];

		switch(label.toLowerCase()) {
			case "pour":
			case "oui":
				color = positiveColor;
				break;
			case "contre":
			case "non":
				color = negativeColor;
				break;
		}

		chartData.datasets[0].backgroundColor.push(color.replace("opacity", 0.3));
		chartData.datasets[0].borderColor.push(color.replace("opacity", 1));
		chartData.datasets[0].borderWidth.push(2);

		datahash += data.propositions[index].mpr_label;
		datahash += data.propositions[index].total_power;
	}

	updateChart2(chartMotionContainer, chartData);

});
<?php   } 
        else if ($object["mot_win_limit"] == -2 || $object["mot_win_limit"] == -3) { ?>
$(function() {
    const chartMotionContainer = $("#motion-<?=$object["mot_id"]?>");

	initJMChart(chartMotionContainer);

	var chartData = {};

	chartData.labels = [];
	chartData.datasets = [];

	var datahash = "";
	const winLimit = <?=$object["mot_win_limit"]?>

	let translations = winLimit == -2 ? majority_judgement_translations : approval_translations;

	for(var index = translations.length - 1; index >= 0; --index) {
		var dataset = {
			data: [
			],
			backgroundColor: [
			],
			borderWidth: [
			],
			borderColor: [
			],
			label: translations[index]
		};

		chartData.datasets.push(dataset);
	}

    const data = {propositions: []};
    let proposition = null;

<?php   foreach($object["propositions"] as $proposition) { 
            $explanation = json_decode($proposition["mpr_explanation"], true);
            $dataProposition = array("mpr_id" => $proposition["mpr_id"], "mpr_label" => $proposition["mpr_label"], "mpr_neutral" => $proposition["mpr_neutral"], "jm_proportion_powers" => array());
            $totalPower = 0;
            
            $dataProposition["jm_proportion_powers"][0] = 0;

            if (isset($explanation["votes"])) {
                foreach($explanation["votes"] as $vote) {
                    if (!isset($dataProposition["jm_proportion_powers"][$vote["jmPower"]])) {
                        $dataProposition["jm_proportion_powers"][$vote["jmPower"]] = 0;
                    }
                    $dataProposition["jm_proportion_powers"][$vote["jmPower"]] += $vote["votePower"];
                    $totalPower += $vote["votePower"];
                }
            }
            
            if ($totalPower) {
                foreach($dataProposition["jm_proportion_powers"] as $jmindex => $jm) {
                    $dataProposition["jm_proportion_powers"][$jmindex] /= $totalPower;
                }
            }
    ?>

    proposition = <?=json_encode($dataProposition, JSON_NUMERIC_CHECK);?>;

	for(var jndex = translations.length - 1; jndex >= 0; --jndex) {
	    if (!(proposition["jm_proportion_powers"][jndex])) {
	        proposition["jm_proportion_powers"][jndex] = 0;
	    }
	}

    data.propositions.push(proposition);
    
<?php   } ?>

	for(var index = 0; index < data.propositions.length; ++index) {

		if (data.propositions[index].mpr_neutral) continue;
		
		chartData.labels.push(data.propositions[index].mpr_label);

		datahash += shortenLabel(data.propositions[index].mpr_label, 20);

		for(var jndex = translations.length - 1; jndex >= 0; --jndex) {
			var percent = data.propositions[index].jm_proportion_powers[jndex + 1] * 100;
			chartData.datasets[translations.length - jndex - 1].data.push(percent);

			var hue = (translations.length == 1 ? 0 : 120 * jndex / (translations.length - 1));
			chartData.datasets[translations.length - jndex - 1].backgroundColor.push("hsla(" + hue + ", 70%, 70%, 0.25)");
			chartData.datasets[translations.length - jndex - 1].borderWidth.push(2);
			chartData.datasets[translations.length - jndex - 1].borderColor.push("hsla(" + hue + ", 70%, 70%, 1)");

			datahash += percent;
		}
	}

	updateChart2(chartMotionContainer, chartData);
});
<?php   } 
        else if ($object["mot_win_limit"] == -1) { ?>

$(function() {
    const chartMotionContainer = $("#motion-<?=$object["mot_id"]?>");

    initBordaChart(chartMotionContainer);

	var chartData = {};

	chartData.labels = [];
	chartData.datasets = [{
		data: [
		],
		backgroundColor: [
		],
		borderWidth: [
		],
		borderColor: [
		],
		label: ''
	}];

	var datahash = "";

    const data = {propositions: []};
    let proposition = null;

<?php   $totalPower = 0;
        foreach($object["propositions"] as $proposition) { 
            $explanation = json_decode($proposition["mpr_explanation"], true);
            $totalPower += $explanation["power"] * ($proposition["mpr_neutral"] ? 0 : 1);
            $dataProposition = array("mpr_id" => $proposition["mpr_id"], "mpr_label" => $proposition["mpr_label"], "mpr_neutral" => $proposition["mpr_neutral"], "total_power" => round($explanation["power"], 2));
    ?>

    proposition = <?=json_encode($dataProposition, JSON_NUMERIC_CHECK);?>;
    data.propositions.push(proposition);
    
<?php   } ?>


	for(var index = 0; index < data.propositions.length; ++index) {

		if (data.propositions[index].mpr_neutral) continue;
		
		var label = shortenLabel(data.propositions[index].mpr_label, 20);
		
		chartData.labels.push(label);
		chartData.datasets[0].data.push(data.propositions[index].total_power);
		
		var color = chartColors[data.propositions[index].mpr_id % chartColors.length];

		switch(label.toLowerCase()) {
			case "pour":
			case "oui":
				color = positiveColor;
				break;
			case "contre":
			case "non":
				color = negativeColor;
				break;
		}

		chartData.datasets[0].backgroundColor.push(color.replace("opacity", 0.3));
		chartData.datasets[0].borderColor.push(color.replace("opacity", 1));
		chartData.datasets[0].borderWidth.push(2);

		datahash += data.propositions[index].mpr_label;
		datahash += data.propositions[index].total_power;
	}

	updateChart2(chartMotionContainer, chartData);

});
<?php   } ?>
        
        
    </script>

<!--
    <div class="motion-description">
        <div class="motion-description-text"><?=$object["mot_description"]?></div>
    </div>
-->


    <div class="motion-propositions margin-top">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center">Proposition</th>
<?php       if ($object["mot_win_limit"] > 0) { ?>
                    <th class="text-center">Pouvoirs</th>
                    <th class="text-center">Résultat</th>
<?php       } 
            else if ($object["mot_win_limit"] == -1) { 
                $sortedPropositions = $object["propositions"];
                usort($sortedPropositions, "sortByPower");
?>
                    <th class="text-center">Pouvoirs</th>
                    <th class="text-center">Rang</th>
<?php       } 
            else if ($object["mot_win_limit"] == -2) { ?>
                    <th class="text-center">Motion médiane atteinte</th>
                    <th class="text-center">Résultat</th>
<?php       } ?>
                </tr>
            </thead>
<?php   foreach($object["propositions"] as $proposition) {
            $explanation = json_decode($proposition["mpr_explanation"], true);
            $propositionClass = "text-danger";

            if ($proposition["mpr_winning"] == "1") {
                $propositionClass = "text-success";
            }

            if ($proposition["mpr_neutral"] == "1") {
                $propositionClass = "text-info";
            }
            
            if ($object["mot_win_limit"] == -1) { 
                foreach($sortedPropositions as $sindex => $sortedProposition) {
                    if ($sortedProposition["mpr_id"] == $proposition["mpr_id"]) {
                        $rank = count($sortedPropositions) - $sindex;
                        break;
                    }
                }
            }
?>
            <tr class="<?=$propositionClass?>" id="proposition-summary-<?=$proposition["mpr_id"]?>">
                <td><?=$proposition["mpr_label"]?>
                    <button data-proposition-id="<?=$proposition["mpr_id"]?>" class="btn btn-info btn-xs btn-explanation-toggler" style="position: relative; top: -2px;"><i class="glyphicon glyphicon-folder-close"></i></button>
                </td>

<?php       if ($object["mot_win_limit"] > 0) { ?>
                <td class="text-right"><?=$explanation["power"]?></td>
                <td class="text-right"><?=$proposition["mpr_neutral"] ? "-" : round($explanation["power"] * 100 / $totalPower, 2) . "%" ?></td>
<?php       } 
            else if ($object["mot_win_limit"] == -1) { ?>
                <td class="text-right"><?=$explanation["power"]?></td>
                <td class="text-right"><?=$rank?></td>
<?php       } 
            else if ($object["mot_win_limit"] == -2) { ?>
                <td class="text-center"><?=lang("motion_majorityJudgment_" . $explanation["jm_winning"])?></td>
                <td class="text-right"><?=round($explanation["jm_percent"], 2) . "%"?></td>
<?php       } ?>
            </tr>
            <tr style="display: none;" id="proposition-explanation-<?=$proposition["mpr_id"]?>">
                <td colspan="3" class="proposition">
                    <ul class="pull-left vote-container">
<?php   
            //		        echo $proposition["mpr_explanation"];
                    $explanation = json_decode($proposition["mpr_explanation"], true);

                    if (isset($explanation["votes"])) {
                        foreach($explanation["votes"] as $vote) { 
                            if (!$vote["power"]) continue;
?>
                        <li class="vote">
                            <span class="nickname"><?php echo $vote["memberLabel"]; ?></span>
                            <span
                                title="Pouvoir du vote"
                                data-toggle="tooltip" data-placement="bottom" 
                                class="badge power">
<?php                       if ($object["mot_win_limit"] == -2) { 
                                echo lang("motion_majorityJudgment_" . $vote["power"]);
                                echo " / ";
                                echo $vote["votePower"];
                            }
                            else if ($vote["votePower"]) { ?>
<?php                           echo $vote["power"]; ?>
            
<?php                           if ($vote["votePower"] != $vote["power"]) { ?> / <?php          echo $vote["votePower"]; ?>
<?php                           } ?>
<?php                       } 
                            else {
                                echo "0";
                            } ?>
            
                            </span>
                        </li>
<?php                   }
                    } ?>
                    </ul>
                    
                </td>
            </tr>
<?php   } ?>
        </table>
    </div>
