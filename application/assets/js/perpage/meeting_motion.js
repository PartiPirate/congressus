function computeMotion(motion) {
	var votes = motion.find(".vote");

	var propositionPowers = {};
	var totalPowers = 0;
	var max = 0;

	motion.find(".proposition").each(function() {
		propositionPowers[$(this).data("id")] = 0;
	});

	var winLimit = motion.data("win-limit");

	for(var index = 0; index < votes.length; ++index) {
		var vote = votes.eq(index);

		var memberId = vote.data("memberId");
		var propositionId = vote.data("propositionId");
		var parentNotice = $("#member-" + memberId).parents(".notice").eq(0);
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

			votePower += themePower * vote.find(".power").text() / membersPower * (1 - neutral);
		}
		else {
			votePower += vote.find(".power").text() * (1 - neutral);
		}

		propositionPowers[propositionId] += votePower;
		totalPowers += votePower;

		if (propositionPowers[propositionId] > max) {
			max = propositionPowers[propositionId];
		}
	}

	for(var id in propositionPowers) {
		var propositionPower = propositionPowers[id];

		var percent = 0;
		if (totalPowers) {
			percent = Math.round(propositionPower / totalPowers * 1000, 1) / 10;
		}

		if ((winLimit && percent >= winLimit) || (!winLimit && propositionPower == max)) {
			$("#proposition-" + id).addClass("text-success");
			$("#proposition-" + id).removeClass("text-danger");
		}
		else {
			$("#proposition-" + id).removeClass("text-success");
			$("#proposition-" + id).addClass("text-danger");
		}

		$("#proposition-" + id + " .powers").html("&nbsp;(" + propositionPower + " / " + percent + "%)");
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
		var parentNotice = member.parents(".notice").eq(0);
		var themePower = parentNotice.children("h5").find(".power").text();

		var proposition = $("#proposition-" + propositionId);
		var neutral = proposition.data("neutral");

		propositionVote["neutral"] = neutral;
		propositionVote["power"] = 1 * vote.find(".power").text();
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

			propositionVote["votePower"] = themePower * vote.find(".power").text() / membersPower * (1 - neutral);
		}
		else {
			propositionVote["votePower"] = vote.find(".power").text() * (1 - neutral);
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

		if ((winLimit && percent >= winLimit) || (!winLimit && explanations[id]["power"] == max)) {
			explanations[id]["winning"] = 1;
		}

	}

	var motionId = motion.data("id");

	$.post("meeting/do_computeVote.php", {motionId: motionId, explanations: JSON.stringify(explanations)}, function(data) {
	}, "json");
}
