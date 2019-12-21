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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
/* global $ */
/* global judgementMajorityValues */

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

function removeVotes(motion) {
	var form = {"motionId": motion.data("id")};

	$.post("meeting_api.php?method=do_removeVotes", form, function(data) {
		if (data.ok) {
			setMotionDirty(motion, false);
			showAlert("Vous avez annulé votre vote sur la motion en cours.", "warning");

			var propositionHolders = motion.find(".proposition");
		
			propositionHolders.each(function() {
		
				var proposition = $(this);
			
				let badge = $("li[data-proposition-id=" + proposition.data("id") + "] .badge");
				badge.text("");
			});
		}
	}, "json");
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

				let badge = $("li[data-proposition-id="+data.vote.mpr_id+"] .badge");

				if (data.vote.vot_power) {
					if (data.vote.mot_win_limit == -2) { // JM
						badge.html(judgementMajorityValues[data.vote.vot_power]);
					}
					else {
						badge.text(data.vote.vot_power);
					}
				}
				else {
					badge.text("");
				}
			
				if (numberOfWaitingVotes == 0) {
					setMotionDirty(motion, false);

					if ($(".btn-next").is(":disabled")) {
						showAlert("Votre vote a été pris en compte. C'est fini !", "success");
						showSummary();
					}
					else {
						showAlert("Votre vote a été pris en compte, on passe à la suite", "success");
						$(".btn-next").click();
					}
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

				let badge = $("li[data-proposition-id="+data.vote.mpr_id+"] .badge");
				badge.text(data.vote.vot_power);

				if (numberOfWaitingVotes == 0) {
					setMotionDirty(motion, false);

					var sortPropositions = function(a, b) {
						let aValue = $(a).find(".badge").text();
						let bValue = $(b).find(".badge").text();
						
						if (bValue == aValue) return 0;
						
					    return (bValue > aValue) ? 1 : -1;
					};
				
					var motionContainer = $("li[data-motion-id="+data.vote.mot_id+"]");
					var propositions = motionContainer.find("ul").children();
					propositions.detach();

					propositions.sort(sortPropositions).appendTo(motionContainer.find("ul"));

					if ($(".btn-next").is(":disabled")) {
						showAlert("Votre vote a été pris en compte. C'est fini !", "success");
						showSummary();
					}
					else {
						showAlert("Votre vote a été pris en compte, on passe à la suite", "success");
						$(".btn-next").click();
					}
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
			var proposition = $(this);
			if (proposition.data("expired")) return;

			motion.find(".proposition").removeClass("active").data("power", 0);
			proposition.addClass("active").data("power", 1);
			setMotionDirty(motion, true);
		});

		motion.find(".proposition").each(function() {
			var proposition = $(this);
			if (proposition.data("expired")) return;

			if (proposition.data("power") == 1) {
				proposition.addClass("active");
			}
		});
	}
	else {
		motion.find(".proposition").click(function(e) {
			var proposition = $(this);
			if (e.target == proposition.find("input").get(0)) {
				e.stopImmediatePropagation();
				return;
			}

			if (proposition.data("expired")) return;

			motion.find(".proposition input").each(function() {
				$(this).val(0);	
			});

			proposition.find("input").val(maxPower);

			checkMaxValues(motion.find(".proposition"), maxPower);
			setMotionDirty(motion, true);
		});
		motion.find(".proposition").each(function() {
			var proposition = $(this);

			var input = $("<input type='number' " + (proposition.data("expired") ? "disabled=disabled" : "") + " class='pull-right text-right' style='width: 60px; color: #000;' min='0' max='"+maxPower+"' value='"+($(this).data("power") ? $(this).data("power") : 0)+"'>");
			input.change(function() {
				if (proposition.data("expired")) return;
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

	motion.find(".btn-reset").click(function() {
		motion.find(".proposition").each(function() {
			var proposition = $(this);
			proposition.find("input").val(0);
			proposition.removeClass("active");
		});

		removeVotes(motion);
	});

}

function setSchulzeOrderStyle(propositionsHolder) {
	var propositions = propositionsHolder.find(".proposition");
	
	var maxPower = propositionsHolder.parents(".motion").data("max-power");

	propositions.each(function(index) {
		$(this).find(".btn-up").removeAttr("disabled");
		$(this).find(".btn-down").removeAttr("disabled");

		$(this).data("power", (propositions.length - index) * maxPower);
		var hue = 120 - (propositions.length == 1 ? 0 : 120 * index / (propositions.length - 1));
		$(this).css({"background" : "hsl(" + hue + ", 70%, 70%)", "color" : "#111111"});

		if (index == 0) {
			$(this).find(".btn-up").attr("disabled", "disabled");
		}

		if (index == propositions.length - 1) {
			$(this).find(".btn-down").attr("disabled", "disabled");
		}

	});
}

function addBordaHandlers(motion) {
//    addLog("Add borda on " + motion.data("id"));

	var propositionsHolder = motion.find(".propositions");

	setSchulzeOrderStyle(propositionsHolder);

	if (propositionsHolder.data("expired")) return;

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

	propositionsHolder.find(".btn-up").click(function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		var proposition = $(this).parent(".proposition");
		var prevProposition = proposition.prev();

		proposition.detach();
		proposition.insertBefore(prevProposition);

		setSchulzeOrderStyle(propositionsHolder);
		setMotionDirty(motion, true);
	});
	propositionsHolder.find(".btn-down").click(function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();

		var prevProposition = $(this).parent(".proposition");
		var proposition = prevProposition.next();

		proposition.detach();
		proposition.insertBefore(prevProposition);

		setSchulzeOrderStyle(propositionsHolder);
		setMotionDirty(motion, true);
	});

	motion.find(".btn-vote").click(function() {
	 	doBordaVote(motion);
	});

	motion.find(".btn-reset").click(function() {
		removeVotes(motion);
	});
}

function addMajorityJudgmentHandlers(motion) {
//    addLog("Add MajorityJudgment on " + motion.data("id"));

	var propositionsHolder = motion.find(".propositions");
	propositionsHolder.find(".proposition").each(function() {
		var proposition = $(this);

		if (proposition.data("expired")) return;
		
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

	motion.find(".btn-reset").click(function() {
		propositionsHolder.find(".proposition").each(function() {
			var proposition = $(this);
			proposition.find(".judgement").removeClass("active");
			if (judgmentVoteIsMandatory) {
				proposition.find(".judgement").eq(0).click();
			}
		});

		removeVotes(motion);
	});
}

function addVoteHandlers(motion) {
//	addLog(motion.data("id") + " " + motion.data("method"));

	var method = motion.data("method");

	if (method == "-2" || method == "-3") {
		addMajorityJudgmentHandlers(motion);
	}
	else if (method == "-1") {
		addBordaHandlers(motion);
	}
	else if (method >= 0) {
		addPointHandlers(motion);
	}

}

function showSummary() {
	$(".motion").hide();
	$(".summary").show();
}

function checkNavigation(motion) {
	$(".btn-previous").removeAttr("disabled");
	$(".btn-next").removeAttr("disabled");

	if (!motion.prev(".motion").length) {
		$(".btn-previous").attr("disabled", "disabled");
	}

	if (!motion.next(".motion").length) {
		$(".btn-next").attr("disabled", "disabled");
	}
}

function orderPropositions(motion, propositions) {
	if (motion.mot_status != "resolved") return;
	var motionId = motion.mot_id;

	var motionContainer = $("#agenda_point .motion[data-id=" + motionId + "]");

	if (motionContainer.data("ordered-propositions") == "ordered") return;

	motionContainer.data("ordered-propositions", "ordered")

	motionContainer.find(".proposition").each(function() {

		for(var propositionIndex = 0; propositionIndex < propositions.length; ++propositionIndex) {
			if (propositions[propositionIndex].mpr_id == $(this).data("id")) {
				$(this).data("order", propositions[propositionIndex].mpr_position);
				break;
			}
		}

	});

	var sortPropositions = function(a, b) {
		if ($(b).data('order') == $(a).data('order')) return 0;
		
	    return ($(b).data('order')) < ($(a).data('order')) ? 1 : -1;
	};

	var propositionDivs = motionContainer.find(".motion-propositions").children();
	propositionDivs.detach();

	propositionDivs.sort(sortPropositions).appendTo(motionContainer.find(".motion-propositions"));
}

$(function() {

	$(".btn-show-summary").click(showSummary);

	$(".btn-show-motion").click(function() {
		let motion = $(".motion[data-id=" + $(this).data("motion-id") + "]");
		$(".motion").hide();
		motion.show();
		$(".summary").hide();

		checkNavigation(motion);
	});

	$(".show-description-link").click(function() {
		var tabId = $(this).data("tab-id");
		$(".show-description-link[data-tab-id="+tabId+"]").hide();
		$(".hide-description-link[data-tab-id="+tabId+"]").show();
		$("#"+tabId).show();
	});

	$(".hide-description-link").click(function() {
		var tabId = $(this).data("tab-id");
		$(".show-description-link[data-tab-id="+tabId+"]").show();
		$(".hide-description-link[data-tab-id="+tabId+"]").hide();
		$("#"+tabId).hide();
	});

	$(".btn-previous").attr("disabled", "disabled").click(function() {
		if ($(this).is(":disabled")) return;
		$(".summary").hide();

		var current = $(".motion:visible");
		if (!current.length) {
			var previous = $(".motion").last();
		}
		else {
			var previous = current.prev(".motion");
		}

		current.hide();
		previous.show();

		checkNavigation(previous);
	});

	$(".btn-next").attr("disabled", "disabled").click(function() {
		if ($(this).is(":disabled")) return;
		$(".summary").hide();

		var current = $(".motion:visible");
		if (!current.length) {
			var next = $(".motion").first();
		}
		else {
			var next = current.next(".motion");
		}

		current.hide();
		next.show();

		checkNavigation(next);
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
	
	$(".btn-abstain").click(function() {
		if ($(".btn-next").is(":disabled")) {
			showAlert("Vous vous êtes abstenu·e. C'est fini !", "success");
			showSummary();
		}
		else {
			showAlert("Vous vous êtes abstenu·e, on passe à la suite", "success");
			$(".btn-next").click();
		}
	});
});