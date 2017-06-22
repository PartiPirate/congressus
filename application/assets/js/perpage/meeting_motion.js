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
	var totalPowers = 0;
	var max = 0;

	motion.find(".proposition").each(function() {
		propositionPowers[$(this).data("id")] = 0;

		if (areVotesAnonymous(motion)) {
			$("#proposition-" + $(this).data("id") + " .powers").hide();
		}
		else {
			$("#proposition-" + $(this).data("id") + " .powers").show();
		}
	});

	var winLimit = motion.data("win-limit");

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
		}
		else if (parentNotice.find(".btn-modify-voting.active").length == 1) {
			votePower += vote.data("power") * (1 - neutral);
		}

		vote.attr("data-effective-power", votePower);
		vote.data("effective-power", votePower);

		vote.find(".power").attr("title", meeting_votePower + " " + vote.data("power") + " => " + voteRound(votePower)).text(voteRound(votePower));

		if (vote.data("power") != 0) {
			voters[memberId] = memberId;
		}

		propositionPowers[propositionId] += votePower;
		totalPowers += votePower;

		if (propositionPowers[propositionId] > max) {
			max = propositionPowers[propositionId];
		}
	}

	var numberOfVoters = 0;
	for(var id in voters) {
		numberOfVoters++;
	}

	motion.find(".number-of-voters").text(numberOfVoters);

	if (!areVotesAnonymous(motion)) {
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
			else {
				$("#proposition-" + id).removeClass("text-success");
				$("#proposition-" + id).addClass("text-danger");
			}

			propositionPower = voteRound(propositionPower);

			$("#proposition-" + id + " .powers").html("&nbsp;(" + propositionPower + " / " + percent + "%)");
		}
	}
//	console.log(propositionPowers);
}

function dumpMotion(motion) {
	var votes = motion.find(".vote");

	var explanations = {};
	var totalPowers = 0;
	var max = 0;

	var winLimit = motion.data("win-limit");

	motion.find(".proposition").each(function() {
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

			propositionVote["votePower"] = themePower * vote.data("power") / membersPower * (1 - neutral);
		}
		else if (parentNotice.find(".btn-modify-voting.active").length == 1) {
			propositionVote["votePower"] = vote.data("power") * (1 - neutral);
		}
		else {
			propositionVote["votePower"] = 0;
		}

		totalPowers += propositionVote["votePower"];

		explanations["proposition_" + propositionId]["votes"][explanations["proposition_" + propositionId]["votes"].length] = propositionVote;
		explanations["proposition_" + propositionId]["power"] -= -propositionVote["votePower"];

		if (explanations["proposition_" + propositionId]["power"] > max) {
			max = explanations["proposition_" + propositionId]["power"];
		}
	}

	for(var id in explanations) {

		var percent = 0;
		if (totalPowers) {
			percent = Math.round(explanations[id]["power"] / totalPowers * 1000, 1) / 10;
		}

		if ((winLimit > 0 && percent >= winLimit) || ((winLimit == 0 || winLimit == -1) && explanations[id]["power"] == max)) {
			explanations[id]["winning"] = 1;
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
