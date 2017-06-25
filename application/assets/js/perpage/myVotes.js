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

function addLog(log) {
	$("#log").append($("<span>" + log + "</span><br>"));
}

function doVote(motion) {
	addLog("vote on : " + motion.data("id"));
}

function setMotionDirty(motion, state) {
	var voteButton = motion.find(".btn-vote");
	
	if (state) {
		voteButton.removeClass("btn-primary").addClass("btn-danger");
	}
	else {
		voteButton.removeClass("btn-danger").addClass("btn-primary");
	}
}

function doPointVote(motion) {
	addLog("borda vote on : " + motion.data("id"));
	
	var propositionHolders = motion.find(".proposition");

	propositionHolders.each(function() {

		var proposition = $(this);
		var form = {"motionId": motion.data("id"),
					"propositionId": proposition.data("id"),
					"power": proposition.data("power")};

		addLog(JSON.stringify(form));

		$.post("meeting_api.php?method=do_vote", form, function(data) {
			if (data.ok) {
//				addVotes([data.vote], proposition, motion);
//				testBadges(data.gamifiedUser.data);
				addLog("Done !");
			}
		}, "json");

	});

	setMotionDirty(motion, false);
	$(".btn-next").click();
}

function doBordaVote(motion) {
	addLog("borda vote on : " + motion.data("id"));
	
	var propositionHolders = motion.find(".proposition");
	
	var index = 0;
	var maxPower = motion.data("max-power");
	
	propositionHolders.each(function() {

		var power = (propositionHolders.length - index) * maxPower;
		var proposition = $(this);
		var form = {"motionId": motion.data("id"),
					"propositionId": proposition.data("id"),
					"power": power};

		addLog(JSON.stringify(form));

		$.post("meeting_api.php?method=do_vote", form, function(data) {
			if (data.ok) {
//				addVotes([data.vote], proposition, motion);
//				testBadges(data.gamifiedUser.data);
				addLog("Done !");
			}
		}, "json");

		++index;

	});

	setMotionDirty(motion, false);
	$(".btn-next").click();
}

function checkMaxValues(propositions, maxPower) {
	propositions.each(function() {
		var currentMax = maxPower;
		var current = this;
		
		propositions.each(function() {
			if (this != current) {
				currentMax -= $(this).find("input").val();
			}
		});

		var value = $(this).find("input").val();
		if (value > currentMax) {
			value = currentMax;
			$(this).find("input").val(currentMax);
		}

		$(this).data("power", value);
		$(this).find("input").attr("max", currentMax);
	});
}

function addPointHandlers(motion) {
	addLog("Add point on " + motion.data("id"));

	var maxPower = motion.data("max-power");

	if (maxPower == 1) {
		motion.find(".proposition").click(function() {
			motion.find(".proposition").removeClass("active").data("power", 0);
			$(this).addClass("active").data("power", 1);
		});

		motion.find(".proposition").each(function() {
			if ($(this).data("power") == 1) {
				$(this).addClass("active");
			}
		});
	}
	else {
		motion.find(".proposition").each(function() {
			var input = $("<input type='number' class='pull-right text-right' style='width: 40px;' min='0' max='"+maxPower+"' value='"+($(this).data("power") ? $(this).data("power") : 0)+"'>");
			input.change(function() {
				checkMaxValues(motion.find(".proposition"), maxPower);
				setMotionDirty(motion, true);
			});
			$(this).append(input);
		})
	}
	
	checkMaxValues(motion.find(".proposition"), maxPower);

	motion.find(".btn-vote").click(function() {
	 	doPointVote(motion);
	});
}

function setSchulzeOrderStyle(propositionsHolder) {
	var propositions = propositionsHolder.find(".proposition");
	propositions.each(function(index) {
		var hue = 120 - (propositions.length == 1 ? 0 : 120 * index / (propositions.length - 1));
		$(this).css({"background" : "hsl(" + hue + ", 50%, 50%)"});
	});
}

function addBordaHandlers(motion) {
	addLog("Add borda on " + motion.data("id"));

	var propositionsHolder = motion.find(".propositions");
/*	propositionsHolder.find(".proposition").each(function() {
		$(this).width($(this).width());
	});
*/
	propositionsHolder.sortable({
		"axis": "y",
		"helper": "clone",
		containment: "parent",
		"sort": function() {
			setSchulzeOrderStyle(propositionsHolder);
		},
		"stop": function() {
			setSchulzeOrderStyle(propositionsHolder);
			setMotionDirty(motion, true);
		}
	});

	setSchulzeOrderStyle(propositionsHolder);

	motion.find(".btn-vote").click(function() {
	 	doBordaVote(motion);
	});
}

function addVoteHandlers(motion) {
//	addLog(motion.data("id") + " " + motion.data("method"));

	var method = motion.data("method");

	if (method == "-1") {
		addBordaHandlers(motion);
	}
	else if (method >= 0) {
		addPointHandlers(motion);
	}

}

$(function() {

	$(".btn-previous").attr("disabled", "disabled").click(function() {
		if ($(this).is(":disabled")) return;

		var current = $(".motion:visible");
		var previous = current.prev(".motion");

		current.hide();
		previous.show();

		$(".btn-next").removeAttr("disabled");

		if (!previous.prev(".motion").length) {
			$(".btn-previous").attr("disabled", "disabled");
		}
	});

	$(".btn-next").attr("disabled", "disabled").click(function() {
		if ($(this).is(":disabled")) return;

		var current = $(".motion:visible");
		var next = current.next(".motion");

		current.hide();
		next.show();

		$(".btn-previous").removeAttr("disabled");

		if (!next.next(".motion").length) {
			$(".btn-next").attr("disabled", "disabled");
		}
	});

	if ($(".motion").length > 1) {
		$(".btn-previous").show();
		$(".btn-next").show();
	}

	$(".motion").each(function(index) {
		
		if (index != 0) {
			$(this).hide();
			$(".btn-next").removeAttr("disabled");
		}
		else {
			$(this).show();
		}

		addVoteHandlers($(this));
	});

});