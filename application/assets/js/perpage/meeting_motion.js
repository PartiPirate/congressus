/*
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
/* global $ */
/* global majority_judgement_values */
/* global majority_judgement_translations */

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
	var votes = motion.find(".vote");
	var voters = {};

	var propositionPowers = {};
	var judgedPropositions = {};
	var scaledJudgedPropositions = {};
	var totalJudgedPropositions = {};
	var winningJudgedPropositions = {};
	var totalPowers = 0;
	var max = 0;

	var winLimit = motion.data("win-limit");

	motion.find(".proposition").each(function() {
		propositionPowers[$(this).data("id")] = 0;
		judgedPropositions[$(this).data("id")] = [];
		scaledJudgedPropositions[$(this).data("id")] = [];
		totalJudgedPropositions[$(this).data("id")] = 0;
		winningJudgedPropositions[$(this).data("id")] = 0;

		for(var index = 0; index < majority_judgement_values.length; ++index) {
			judgedPropositions[$(this).data("id")][majority_judgement_values[index]] = 0;
			scaledJudgedPropositions[$(this).data("id")][majority_judgement_values[index]] = 0;
		}

		if (areVotesAnonymous(motion)) {
			$("#proposition-" + $(this).data("id") + " .powers").hide();
		}
		else {
			$("#proposition-" + $(this).data("id") + " .powers").show();
		}
	});

	for(var index = 0; index < votes.length; ++index) {
		var vote = votes.eq(index);

		var memberId = vote.data("memberId");
		var propositionId = vote.data("propositionId");
		var parentNotices = $(".member[data-id=" + memberId + "]").parent().parent();
		var parentNotice = parentNotices.eq(0);
		parentNotices.each(function() {
			if ($(this).children("h5").find(".power").text()) {
				parentNotice = $(this);
			}
		})
		var themePower = parentNotice.children("h5").find(".power").text();

		var proposition = $("#proposition-" + propositionId);
		var neutral = proposition.data("neutral");

		if (!propositionPowers[propositionId]) {
			propositionPowers[propositionId] = 0;
		}

		var votePower = 0;
		var jmPower = 0;

		if (themePower) {
			var membersPower = 0;
			parentNotice.children("ul.members").children("li.member").each(function () {

				for(var jndex = 0; jndex < votes.length; ++jndex) {
					var jote = votes.eq(jndex);
					var jemberId = jote.data("memberId");

					if (jemberId == $(this).data("id")) {
						membersPower -= -$(this).find(".power").eq(0).text();
						break;
					}
				}
			});

			votePower += themePower * vote.data("power") / membersPower * (1 - neutral);
			jmPower += themePower / membersPower * (1 - neutral);
		}
		else if (parentNotice.find(".btn-modify-voting.active").length == 1) {
			votePower += vote.data("power") * (1 - neutral);
			jmPower += $(this).find(".power").eq(0).text() * (1 - neutral);
		}

		vote.attr("data-effective-power", votePower);
		vote.data("effective-power", votePower);

		if (winLimit != -2) {
			vote.find(".power").attr("title", meeting_votePower + " " + vote.data("power") + " => " + voteRound(votePower)).text(voteRound(votePower));
		}
		else {
//			console.log(propositionId + " , " + vote.data("power") + " , " + jmPower)

			totalJudgedPropositions[propositionId] += jmPower;
			winningJudgedPropositions[propositionId] = -1;

			scaledJudgedPropositions[propositionId][vote.data("power")] += jmPower

			for(var vindex = 0; vindex < majority_judgement_values.length; ++vindex) {
				var jmValue = majority_judgement_values[vindex];
				if (vote.data("power") >= jmValue) {
					judgedPropositions[propositionId][jmValue] += jmPower;
				}

				if (judgedPropositions[propositionId][jmValue] * 2 >= totalJudgedPropositions[propositionId]) {
					winningJudgedPropositions[propositionId] = jmValue;
				}
			}

//			judgedPropositions[propositionId][vote.data("power")] += jmPower;

			vote.find(".power").attr("title", meeting_votePower + " " + vote.data("power") + " => " + voteRound(jmPower));
		}

		if (vote.data("power") != 0) {
			voters[memberId] = memberId;
		}

		propositionPowers[propositionId] += votePower;
		totalPowers += votePower;

		if (propositionPowers[propositionId] > max) {
			max = propositionPowers[propositionId];
		}
	}

//	console.log(judgedPropositions);

	var numberOfVoters = 0;
	for(var id in voters) {
		numberOfVoters++;
	}

	motion.find(".number-of-voters").text(numberOfVoters);

	if (!areVotesAnonymous(motion)) {

		var maxJMValue = -1;
		var maxJMPercent = 0;

		if (winLimit == -2) {
			for(var id in propositionPowers) {
				if (maxJMValue < winningJudgedPropositions[id]) {
					maxJMValue = winningJudgedPropositions[id];
				}
			}

			for(var id in propositionPowers) {
				if (maxJMValue == winningJudgedPropositions[id]) {
					
					var percent = 0;
					var total = totalJudgedPropositions[id];
					var winning = winningJudgedPropositions[id];
					var winningTotal = judgedPropositions[id][winning];
					
					if (total) {
						percent = Math.round(winningTotal / total * 1000, 1) / 10;
					}
					
					if (maxJMPercent < percent) {
						maxJMPercent = percent;
					}
				}
			}
		}

		for(var id in propositionPowers) {
			var propositionPower = propositionPowers[id];

			var percent = 0;
			if (totalPowers) {
				percent = Math.round(propositionPower / totalPowers * 1000, 1) / 10;
			}

			if ((winLimit > 0 && percent >= winLimit) || ((winLimit == 0 || winLimit == -1) && propositionPower == max)) {
				$("#proposition-" + id).addClass("text-success");
				$("#proposition-" + id).removeClass("text-danger");
			}
			else if (winLimit == 2) {
			}
			else {
				$("#proposition-" + id).removeClass("text-success");
				$("#proposition-" + id).addClass("text-danger");
			}

			if (winLimit != -2) {
				propositionPower = voteRound(propositionPower);
	
				$("#proposition-" + id + " .powers").html("&nbsp;(" + propositionPower + " / " + percent + "%)");
			}
			else {
				var percent = 0;
				var total = totalJudgedPropositions[id];
				var winning = winningJudgedPropositions[id];
				var winningTotal = judgedPropositions[id][winning];
				
				if (total) {
					percent = Math.round(winningTotal / total * 1000, 1) / 10;
				}

				if (percent == maxJMPercent && winning == maxJMValue) {
					$("#proposition-" + id).addClass("text-success");
					$("#proposition-" + id).removeClass("text-danger");
				}
				
				var winningTranslate = winning;
				for(jmValue = 0; jmValue < majority_judgement_values.length; ++jmValue) {
					if (majority_judgement_values[jmValue] == winning) {
						winningTranslate = majority_judgement_translations[jmValue];
					}
				}

				$("#proposition-" + id + " .powers").html("&nbsp;(" + winningTranslate + " / " + percent + "%)");
			}
		}

		if (winLimit == -2) {
			var charts = motion.find(".motion-charts");
			charts.children().remove();
	
			for(var id in scaledJudgedPropositions) {
				var scaledJudgedProposition = scaledJudgedPropositions[id];

				var propositionLabel = $("#proposition-" + id + " .proposition-label").text();

				var bar = "<div style='width:100%;'><div class='label' style='width: 25%; box-sizing: border-box; display: inline-block; color: #000; '>"+propositionLabel+"</div><div style='width: 75%; box-sizing: border-box; display: inline-block;'>";
				
				for(var index = 0; index < majority_judgement_values.length; ++index) {
					var number = scaledJudgedProposition[majority_judgement_values[index]];
					if (number == 0) continue;
	
					var color = 120 * index / majority_judgement_values.length;
					var percent = number / totalJudgedPropositions[id];
					var width = (Math.round(percent * 1000, 1) / 10) + "%";
					percent = (Math.round(percent * 1000, 1) / 10) + "%";
					
					bar += "<div class='text-center' style='height: 22px; width: " + width + "; background: hsl(" + color + ", 100%, 90%); border: 2px solid hsl(" + color + ", 100%, 50%); box-sizing: border-box; display: inline-block;'>"+percent+"</div>";
				}
				
				bar += "</div></div>";
				
				charts.append(bar);
			}
		}
	}
//	console.log(propositionPowers);
}

function dumpMotion(motion) {
	var votes = motion.find(".vote");

	var explanations = {};
	var totalPowers = 0;
	var max = 0;

	var judgedPropositions = {};
	var scaledJudgedPropositions = {};
	var totalJudgedPropositions = {};
	var winningJudgedPropositions = {};
	var totalPowers = 0;
	var max = 0;

	var winLimit = motion.data("win-limit");

	motion.find(".proposition").each(function() {
		judgedPropositions[$(this).data("id")] = [];
		scaledJudgedPropositions[$(this).data("id")] = [];
		totalJudgedPropositions[$(this).data("id")] = 0;
		winningJudgedPropositions[$(this).data("id")] = 0;

		for(var index = 0; index < majority_judgement_values.length; ++index) {
			judgedPropositions[$(this).data("id")][majority_judgement_values[index]] = 0;
			scaledJudgedPropositions[$(this).data("id")][majority_judgement_values[index]] = 0;
		}

		explanations["proposition_" + $(this).data("id")] = {winning: 0, votes: [], power: 0};
	});

	for(var index = 0; index < votes.length; ++index) {
		var vote = votes.eq(index);

		var propositionVote = {};

		var memberId = vote.data("memberId");
		var member = $("#member-" + memberId);
		var propositionId = vote.data("propositionId");
		var parentNotices = $(".member[data-id=" + memberId + "]").parent().parent();
		var parentNotice = parentNotices.eq(0);
		parentNotices.each(function() {
			if ($(this).children("h5").find(".power").text()) {
				parentNotice = $(this);
			}
		})
		var themePower = parentNotice.children("h5").find(".power").text();

		var proposition = $("#proposition-" + propositionId);
		var neutral = proposition.data("neutral");

		propositionVote["neutral"] = neutral;
		propositionVote["power"] = 1 * vote.data("power");
		propositionVote["memberLabel"] = member.find(".member-nickname").text();
		propositionVote["memberId"] = memberId;

		if (themePower) {
			var membersPower = 0;
			parentNotice.children("ul.members").children("li.member").each(function () {

				for(var jndex = 0; jndex < votes.length; ++jndex) {
					var jote = votes.eq(jndex);
					var jemberId = jote.data("memberId");

					if (jemberId == $(this).data("id")) {
						membersPower -= -$(this).find(".power").eq(0).text();
						break;
					}
				}
			});

			propositionVote["themePower"] = themePower;
			propositionVote["themeLabel"] = parentNotice.find("h5 .notice-name").text();
			propositionVote["membersPower"] = membersPower;


			if (winLimit == -2) {
				propositionVote["votePower"] = themePower / membersPower * (1 - neutral);
			}
			else {
				propositionVote["votePower"] = themePower * vote.data("power") / membersPower * (1 - neutral);
			}
		}
		else if (parentNotice.find(".btn-modify-voting.active").length == 1) {
			if (winLimit == -2) {
				propositionVote["votePower"] = vote.data("power") * (1 - neutral);
			}
			else {
				propositionVote["votePower"] = $(this).find(".power").eq(0).text() * (1 - neutral);
			}
		}
		else {
			propositionVote["votePower"] = 0;
		}


		totalPowers += propositionVote["votePower"];

		if (winLimit == -2) {
			propositionVote["jm_power"] = vote.data("power");
			totalJudgedPropositions[propositionId] += propositionVote["votePower"];
	
			winningJudgedPropositions[propositionId] = -1;
			
			scaledJudgedPropositions[propositionId][vote.data("power")] += propositionVote["votePower"];
			
			for(var vindex = 0; vindex < majority_judgement_values.length; ++vindex) {
				var jmValue = majority_judgement_values[vindex];
				if (vote.data("power") >= jmValue) {
					judgedPropositions[propositionId][jmValue] += propositionVote["votePower"];
				}
				
				if (judgedPropositions[propositionId][jmValue] * 2 >= totalJudgedPropositions[propositionId]) {
					winningJudgedPropositions[propositionId] = jmValue;
				}
			}
		}

		explanations["proposition_" + propositionId]["votes"][explanations["proposition_" + propositionId]["votes"].length] = propositionVote;
		explanations["proposition_" + propositionId]["power"] -= -propositionVote["votePower"];

		if (explanations["proposition_" + propositionId]["power"] > max) {
			max = explanations["proposition_" + propositionId]["power"];
		}
	}


	var maxJMValue = -1;
	var maxJMPercent = 0;

	if (winLimit == -2) {
		for(var id in explanations) {
			var propositionId = id.replace("proposition_", "");
			if (maxJMValue < winningJudgedPropositions[propositionId]) {
				maxJMValue = winningJudgedPropositions[propositionId];
			}
		}

		for(var id in explanations) {
			var propositionId = id.replace("proposition_", "");
			if (maxJMValue == winningJudgedPropositions[propositionId]) {
				
				var percent = 0;
				var total = totalJudgedPropositions[propositionId];
				var winning = winningJudgedPropositions[propositionId];
				var winningTotal = judgedPropositions[propositionId][winning];
				
				if (total) {
					percent = Math.round(winningTotal / total * 1000, 1) / 10;
				}
				
				if (maxJMPercent < percent) {
					maxJMPercent = percent;
				}
			}
		}
	}

	for(var id in explanations) {
		var propositionId = id.replace("proposition_", "");
		
		var percent = 0;
		if (totalPowers) {
			percent = Math.round(explanations[id]["power"] / totalPowers * 1000, 1) / 10;
		}

		if ((winLimit > 0 && percent >= winLimit) || ((winLimit == 0 || winLimit == -1) && explanations[id]["power"] == max)) {
			explanations[id]["winning"] = 1;
		}
		else if (winLimit == -2) {
			var percent = 0;
			var total = totalJudgedPropositions[propositionId];
			var winning = winningJudgedPropositions[propositionId];
			var winningTotal = judgedPropositions[propositionId][winning];
			
			if (total) {
				percent = Math.round(winningTotal / total * 1000, 1) / 10;
			}

			if (percent == maxJMPercent && winning == maxJMValue) {
				explanations[id]["winning"] = 1;
			}
			
			explanations[id]["jm_winning"] = winning;
			explanations[id]["jm_percent"] = percent;
			explanations[id]["scale"] = scaledJudgedPropositions[propositionId];
		}
	}

	var motionId = motion.data("id");

	$.post("meeting/do_computeVote.php", {motionId: motionId, explanations: JSON.stringify(explanations)}, function(data) {
	}, "json");
}

$(function() {
	$(".btn-local-anonymous").click(function() {
		$(this).toggleClass("active");
	});
})
