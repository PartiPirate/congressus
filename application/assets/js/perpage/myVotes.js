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

var judgmentVoteIsMandatory = false;

function addLog(log) {
	$("#log").append($("<span>" + log + "</span><br>"));
}

function doVote(motion) {
//    addLog("vote on : " + motion.data("id"));
}

function computeVotes() {
	
	$(".ballot-link").hide();
	var data = {};

	$(".motion").each(function() {
		var motion = $(this);
		var datum = {};

		motion.find(".proposition").each(function() {
			if ($(this).data("power") != 0) {
				datum[$(this).data("id")] = $(this).data("power") - 0;	
			}
		});
		
		data[motion.data("id")] = datum;
	});

//	addLog(JSON.stringify(data));

	$.post("do_paperVote.php", {votes: JSON.stringify(data)}, function(data) {
//		console.log(data);
		$(".ballot-link").attr("href", "ballots/" + data.uuid + ".pdf").show();
	}, "json");
}

function setMotionDirty(motion, state) {
	var voteButton = motion.find(".btn-vote");
	
	if (state) {
		voteButton.removeClass("btn-primary").removeClass("btn-default").addClass("btn-special");
	}
	else {
		voteButton.removeClass("btn-special").removeClass("btn-default").addClass("btn-primary");
	}
}

function computeEventPositions() {
	var margin = 5;
	var currentPosition = 60;

	$(".congressus-event").each(function() {
		var eventAlert = $(this);

		eventAlert.css({"bottom" : currentPosition + "px"});

		currentPosition += (eventAlert.height() + margin + 16);

	});
}

function showAlert(text, eventClass) {
	var eventAlert = $("<p style='width: 350px; height: 55px; z-index: 1000; position: fixed; right: 10px;' class='congressus-event form-alert simply-hidden bg-" + eventClass + "'>" + text + "</p>");
	var body = $("body");
	body.append(eventAlert);

	computeEventPositions();

	eventAlert.show().delay(5000).fadeOut(1000, function() {
		$(this).remove();
		computeEventPositions();
	});
	
}

function doPointVote(motion) {
//    addLog("point vote on : " + motion.data("id"));
	
	var propositionHolders = motion.find(".proposition");

	var numberOfWaitingVotes = propositionHolders.length;

	propositionHolders.each(function() {

		var proposition = $(this);
		var form = {"motionId": motion.data("id"),
					"propositionId": proposition.data("id"),
					"power": proposition.data("power")};

	//    addLog(JSON.stringify(form));

		$.post("meeting_api.php?method=do_vote", form, function(data) {
			if (data.ok) {
				numberOfWaitingVotes--;
//				addVotes([data.vote], proposition, motion);
//				testBadges(data.gamifiedUser.data);
			//    addLog("Done !");
			
				if (numberOfWaitingVotes == 0) {
					setMotionDirty(motion, false);
					
					if ($(".btn-next").is(":disabled")) {
						showAlert("Votre vote a été pris en compte. C'est fini !", "success");
					}
					else {
						showAlert("Votre vote a été pris en compte, on passe à la suite", "success");
					}
					
					$(".btn-next").click();
				}
			}
		}, "json");

	});
}

function doBordaVote(motion) {
//    addLog("borda vote on : " + motion.data("id"));
	
	var propositionHolders = motion.find(".proposition");
	
	var index = 0;
	var maxPower = motion.data("max-power");

	var numberOfWaitingVotes = propositionHolders.length;
	
	propositionHolders.each(function() {

		var power = (propositionHolders.length - index) * maxPower;
		var proposition = $(this);
		var form = {"motionId": motion.data("id"),
					"propositionId": proposition.data("id"),
					"power": power};

	//    addLog(JSON.stringify(form));

		$.post("meeting_api.php?method=do_vote", form, function(data) {
			if (data.ok) {
				numberOfWaitingVotes--;
//				addVotes([data.vote], proposition, motion);
//				testBadges(data.gamifiedUser.data);
			//    addLog("Done !");
			
				if (numberOfWaitingVotes == 0) {
					setMotionDirty(motion, false);
					$(".btn-next").click();
				}
			}
		}, "json");

		++index;

	});
}

function checkMaxValues(propositions, maxPower) {
	propositions.each(function() {
		var currentMax = maxPower;
		var current = this;
		
		propositions.each(function() {
			if (this != current) {
				currentMax -= $(this).find("input").val() > maxPower ? maxPower : ($(this).find("input").val() < 0 ? 0 : $(this).find("input").val());
			}
		});

		if (currentMax < 0) currentMax = 0;

		var value = $(this).find("input").val();
		if (value > currentMax) {
			value = currentMax;
			$(this).find("input").val(currentMax);
		}
		else if (value > maxPower) {
			value = maxPower;
			$(this).find("input").val(maxPower);
		}
		else if (value < 0) {
			value = 0;
			$(this).find("input").val(0);
		}
		

		$(this).data("power", value);
		if (($(this).data("power") ? $(this).data("power") : 0) == 0) {
			$(this).removeClass("active");
		}
		else {
			$(this).addClass("active");
		}
		
//		$(this).find("input").attr("max", currentMax);
	});
}

function addPointHandlers(motion) {
//    addLog("Add point on " + motion.data("id"));

	var maxPower = motion.data("max-power");

	if (maxPower == 1) {
		motion.find(".proposition").click(function() {
			motion.find(".proposition").removeClass("active").data("power", 0);
			$(this).addClass("active").data("power", 1);
			setMotionDirty(motion, true);
		});

		motion.find(".proposition").each(function() {
			if ($(this).data("power") == 1) {
				$(this).addClass("active");
			}
		});
	}
	else {
		motion.find(".proposition").click(function(e) {
			if (e.target == $(this).find("input").get(0)) {
				e.stopImmediatePropagation();
				return;
			}

			motion.find(".proposition input").each(function() {
				$(this).val(0);	
			});

			$(this).find("input").val(maxPower);

			checkMaxValues(motion.find(".proposition"), maxPower);
			setMotionDirty(motion, true);
		});
		motion.find(".proposition").each(function() {
			var proposition = $(this);
			var input = $("<input type='number' class='pull-right text-right' style='width: 60px; color: #000;' min='0' max='"+maxPower+"' value='"+($(this).data("power") ? $(this).data("power") : 0)+"'>");
			input.change(function() {
				checkMaxValues(motion.find(".proposition"), maxPower);
				setMotionDirty(motion, true);

				if ($(this).val() == 0) {
					proposition.removeClass("active");
				}
				else {
					proposition.addClass("active");
				}
			});

			if (($(this).data("power") ? $(this).data("power") : 0) == 0) {
				proposition.removeClass("active");
			}
			else {
				proposition.addClass("active");
			}

			proposition.append(input);
		});
	}
	
	checkMaxValues(motion.find(".proposition"), maxPower);

	motion.find(".btn-vote").click(function() {
	 	doPointVote(motion);
	});
}

function setSchulzeOrderStyle(propositionsHolder) {
	var propositions = propositionsHolder.find(".proposition");
	
	var maxPower = propositionsHolder.parents(".motion").data("max-power");

	
	propositions.each(function(index) {
		$(this).data("power", (propositions.length - index) * maxPower);
		var hue = 120 - (propositions.length == 1 ? 0 : 120 * index / (propositions.length - 1));
		$(this).css({"background" : "hsl(" + hue + ", 70%, 70%)", "color" : "#111111"});
	});
}

function addBordaHandlers(motion) {
//    addLog("Add borda on " + motion.data("id"));

	var propositionsHolder = motion.find(".propositions");

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

function addMajorityJudgmentHandlers(motion) {
//    addLog("Add MajorityJudgment on " + motion.data("id"));

	var propositionsHolder = motion.find(".propositions");
	propositionsHolder.find(".proposition").each(function() {
		var proposition = $(this);
		
		proposition.find(".judgement").click(function() {
			proposition.find(".judgement").removeClass("active");
			$(this).addClass("active");

			proposition.data("power", $(this).data("power"));
			proposition.css({background: $(this).css("background-color")});
			setMotionDirty(motion, true);
		});

		// Init class
		var foundAlreadyVoted = false;
		proposition.find(".judgement").each(function() {
			if ($(this).data("power") == proposition.data("power")) {
				$(this).addClass("active");
				proposition.css({background: $(this).css("background-color")});
				foundAlreadyVoted = true;
			}
		});
		
		if (!foundAlreadyVoted && judgmentVoteIsMandatory) {
			proposition.find(".judgement").eq(0).click();
		}
	});

	motion.find(".btn-vote").click(function() {
	 	doPointVote(motion);
	});
}

function addVoteHandlers(motion) {
//	addLog(motion.data("id") + " " + motion.data("method"));

	var method = motion.data("method");

	if (method == "-2") {
		addMajorityJudgmentHandlers(motion);
	}
	else if (method == "-1") {
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

	$(".btn-paper-vote").click(computeVotes);
});