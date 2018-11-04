/*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
/* global $ */
/* global majority_judgement_values */
/* global majority_judgement_translations */

/* global updateChart2 */

/* I18N */
/* global meeting_votePower */

function voteRound(value) {
	return (Math.round(value * 100, 2) / 100);
}

function areVotesAnonymous(motion) {
	if (motion.data("status") == "resolved") return false;

	if (motion.data("anonymous")) return true;

	if ($(".btn-local-anonymous").hasClass("active")) return true;

	return false;
}

function computeMotion(motion) {
	var motionId = motion.data("id");
//	console.log("computeMotion " + motionId);

	$.post("meeting/do_computeVote.php", {motionId: motionId, save: false}, function(data) {
		motion.find(".number-of-voters").text(data.motion.mot_number_of_voters);
		
		if (data.delegations) {
			motion.data("delegation-powers", data.delegations.powers);
		}

		var winLimit = motion.data("win-limit");

		for(var index = 0; index < data.propositions.length; ++index) {
			var proposition = $("#proposition-" + data.propositions[index].mpr_id);

			if (data.propositions[index].mpr_winning && !areVotesAnonymous(motion)) {
				if (proposition.hasClass("text-danger")) {
					proposition.addClass("text-success");
					proposition.removeClass("text-danger");
				}
			}
			else {
				if (proposition.hasClass("text-success")) {
					proposition.addClass("text-danger");
					proposition.removeClass("text-success");
				}
			}
			
			var powers = proposition.find(".powers");
			var html = powers.html();

			if (areVotesAnonymous(motion)) {
				var newHtml = "";
			}
			else {
				if (winLimit >= 0) {
					var percent =  Math.round(data.propositions[index].proportion_power * 10000) / 100.;
					var newHtml = "&nbsp;(" + percent + "%)";
				}
				else if (winLimit == -1) {
					var propositionPower =  data.propositions[index].total_power;
					var newHtml = "&nbsp;(" + propositionPower + ")";
				}
				else if (winLimit == -2) {
					var jmWinning = data.propositions[index].jm_median_power;
					
					var percent = 0;
					var jmLabel = majority_judgement_translations[0];

					if (jmWinning) {
						percent = Math.round(data.propositions[index].jm_sum_proportion_powers[jmWinning] * 10000) / 100.;
						jmLabel = majority_judgement_translations[jmWinning - 1];
					}
	
					var newHtml = "&nbsp;(" + jmLabel + " / " + percent + "%)";
				}
				
				var explanations = JSON.parse(data.propositions[index].mpr_explanation);
//				console.log(explanations);

				for(var jndex = 0; jndex < explanations.votes.length; ++jndex) {
					var explainedVote = explanations.votes[jndex];
					var voteContainer = proposition.find("li[data-member-id=" + explainedVote.memberId + "]");

//					console.log(explainedVote);
//					console.log(voteContainer);

					if (winLimit == -2) {
						voteContainer.find(".power").attr("title", "Pouvoir du vote : " + explainedVote.votePower)
					}
					else {
						voteContainer.find(".power").attr("title", "Pouvoir du vote : " + explainedVote.power + "/" + explainedVote.votePower)
					}
				}
			}

			if (html != newHtml) {
				powers.html(newHtml);
			}

		}
		
		// If anonymous mode, no graph
		if (areVotesAnonymous(motion)) {
		}
		else if (winLimit >= 0) {
			initPercentChart(motion);

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

			for(var index = 0; index < data.propositions.length; ++index) {

				if (data.propositions[index].mpr_neutral) continue;
				if (!data.propositions[index].mpr_label) continue;
				
				var label = data.propositions[index].mpr_label;
				
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

			if (motion.data("datahash") != datahash) {
				updateChart2(motion, chartData);
				motion.data("datahash", datahash);
			}
		}
		else if (winLimit == -2) {
			initJMChart(motion);

			var chartData = {};

			chartData.labels = [];
			chartData.datasets = [];

			var datahash = "";

			for(var index = majority_judgement_translations.length - 1; index >= 0; --index) {
				var dataset = {
					data: [
					],
					backgroundColor: [
					],
					borderWidth: [
					],
					borderColor: [
					],
					label: majority_judgement_translations[index]
				};
	
				chartData.datasets.push(dataset);
			}

			for(var index = 0; index < data.propositions.length; ++index) {

				if (data.propositions[index].mpr_neutral) continue;
				
				chartData.labels.push(data.propositions[index].mpr_label);

				datahash += data.propositions[index].mpr_label;

				for(var jndex = majority_judgement_translations.length - 1; jndex >= 0; --jndex) {
					var percent = data.propositions[index].jm_proportion_powers[jndex + 1] * 100;
					chartData.datasets[majority_judgement_translations.length - jndex - 1].data.push(percent);

					var hue = (majority_judgement_translations.length == 1 ? 0 : 120 * jndex / (majority_judgement_translations.length - 1));
					chartData.datasets[majority_judgement_translations.length - jndex - 1].backgroundColor.push("hsla(" + hue + ", 70%, 70%, 0.25)");
					chartData.datasets[majority_judgement_translations.length - jndex - 1].borderWidth.push(2);
					chartData.datasets[majority_judgement_translations.length - jndex - 1].borderColor.push("hsla(" + hue + ", 70%, 70%, 1)");

					datahash += data.percent;
				}
			}

			if (motion.data("datahash") != datahash) {
				updateChart2(motion, chartData);
				motion.data("datahash", datahash);
			}
		}
		else if (winLimit == -1) {
			initBordaChart(motion);

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

			for(var index = 0; index < data.propositions.length; ++index) {

				if (data.propositions[index].mpr_neutral) continue;
				
				var label = data.propositions[index].mpr_label;
				
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

			if (motion.data("datahash") != datahash) {
				updateChart2(motion, chartData);
				motion.data("datahash", datahash);
			}
		}

	}, "json");
}

function dumpMotion(motion) {
	var motionId = motion.data("id");

	$.post("meeting/do_computeVote.php", {motionId: motionId, save: true}, function(data) {
	}, "json");

}

$(function() {
	$(".btn-local-anonymous").click(function() {
		$(this).toggleClass("active");
	});
})