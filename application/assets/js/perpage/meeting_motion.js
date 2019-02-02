/*
	Copyright 2015-2019 Cédric Levieux, Parti Pirate

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

/* global initBordaChart */
/* global initPercentChart */
/* global initJMChart */

/* I18N */
/* global meeting_votePower */

/** Chart color **/
/* global positiveColor */
/* global negativeColor */
/* global shortenLabel */

/* global keyupTimeoutId */
/* global clearKeyup */
/* global hasRight */
/* global getUserId */
/* global bootbox */
/* global tags */

function voteRound(value) {
	return (Math.round(value * 100, 2) / 100);
}

function areVotesAnonymous(motion) {
	if (motion.data("status") == "resolved") return false;

	if (motion.data("anonymous")) return true;

	if ($(".btn-local-anonymous").hasClass("active")) return true;

	return false;
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

			if (winLimit == -1) {
				var color = "hsl(0, 70%, 70%) !important";
				if (data.propositions.length) {
					color = "hsl(" + (120 - 120 * data.propositions[index].mpr_position / (data.propositions.length - 1)) + ", 70%, 70%) !important";
				}

				proposition.css({"color": color});
			}

			if (data.propositions[index].mpr_winning && !areVotesAnonymous(motion)) {
				if (proposition.hasClass("text-danger")) {
					proposition.addClass("text-success");
					proposition.removeClass("text-danger");
				}
				proposition.css({"color": ""});
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

			if (motion.data("datahash") != datahash) {
				updateChart2(motion, chartData);
				motion.data("datahash", datahash);
			}
		}
		else if (winLimit == -2) { // Majority Judgement
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

				datahash += shortenLabel(data.propositions[index].mpr_label, 20);

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
		else if (winLimit == -1) { // Borda
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

			if (motion.data("datahash") != datahash) {
				updateChart2(motion, chartData);
				motion.data("datahash", datahash);
			}
		}

		orderPropositions(data.motion, data.propositions);
	}, "json");
}

function dumpMotion(motion) {
	var motionId = motion.data("id");

	$.post("meeting/do_computeVote.php", {motionId: motionId, save: true}, function(data) {
		orderPropositions(data.motion, data.propositions);
	}, "json");

}

function addMotionHandlers() {
	$("#agenda_point ul.objects").on("mouseenter", ".motion h4,.proposition,.motion-description", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;
		if ($(this).parents(".motion").data("status") == "resolved") return;

		if (!$(this).find("input:visible,textarea:visible").length) {
			$(this).find(".glyphicon-pencil").show();
			$(this).find("button.btn-remove-proposition").show();
		}
	});

	$("#agenda_point ul.objects").on("mouseleave", ".motion h4,.proposition,.motion-description", function(event) {
		$(this).find(".glyphicon-pencil").hide();
		$(this).find("button.btn-remove-proposition").hide();
	});

	$("#agenda_point ul.objects").on("click", ".proposition button.btn-remove-proposition", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;
		if ($(this).parents(".motion").data("status") == "resolved") return;

		var agendaId = $("#agenda_point").data("id");
		var meetingId = $(".meeting").data("id");
		var motionId = $(this).parents(".motion").data("id");

		var proposition = $(this).parents(".proposition");

		var propositionId = proposition.data("id");

		bootbox.setLocale("fr");
		bootbox.confirm(meeting_proposalDelete + " \"" + proposition.children(".proposition-label").text() + "\" ?", function(result) {
			if (result) {
				$.post("meeting_api.php?method=do_removeMotionProposition", {
					meetingId: meetingId,
					pointId: agendaId,
					motionId: motionId,
					propositionId: propositionId
				}, function(data) {}, "json");
			}
		});
	});

	$("#agenda_point ul.objects").on("click", ".motion h4,.proposition", function(event) {
		// Click on vote button intercepted
		if ($(event.target).hasClass("btn")) return;
		if ($(event.target).hasClass("glyphicon")) return;

		if (!hasRight(getUserId(), "handle_motion")) return;

		if ($(this).find("input").length) {
			$(this).find("input").focus();
			return;
		}

		$(this).find(".glyphicon-pencil").hide();
		$(this).find("button.btn-remove-proposition").hide();

		var input = $("<input />", {"class": "form-control", "style": "width: 75%; display: inline-block;"});
		var propertyText = $(this).find(".motion-title,.proposition-label");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_title";
		var propositionId = 0;

		if ($(this).hasClass("proposition")) {
			property = "mpr_label";
			propositionId = $(this).data("id");
			input.addClass("pull-left");
			input.addClass("input-xs");
		}
		else {
			input.addClass("input-sm");
		}

		input.val(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				propertyText.text(newText);
				propertyText.show();
				input.remove();
			}, "json");
		});

		input.keyup(function() {
//			return;
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = input.val();

				$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});

	$("#agenda_point ul.objects").on("click", ".btn-motion-anonymous", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		var button = $(this);
		button.addClass("disabled");

		button.toggleClass("active");
		var checked = button.hasClass("active");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_anonymous";
		var propositionId = 0;
		var newText = checked ? 1 : 0;

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", ".btn-motion-limits", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		$(this).parents(".motion").find(".btn-motion-limits").addClass("disabled");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_win_limit";
		var propositionId = 0;
		var newText = $(this).val();

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
		}, "json");
	});

	$("#agenda_point ul.objects").on("click", ".motion-description", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		if ($(this).find("textarea").length) {
			$(this).find("textarea").focus();
			return;
		}

		$(this).find(".glyphicon-pencil").hide();

		var input = $("<textarea />", {"class": "form-control", "style": "width: 100%;"});
		var propertyText = $(this).find(".motion-description-text");

		var motionId = $(this).parents(".motion").data("id");
		var property = "mot_description";
		var propositionId = 0;

		input.text(propertyText.text());
		input.blur(function() {
//			return;
			clearKeyup();
			// update the text into the server
			var newText = input.val();

			$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				propertyText.text(newText);
				propertyText.show();
				input.remove();
			}, "json");
		});

		input.keyup(function() {
//			return;
			clearKeyup();
			keyupTimeoutId = setTimeout(function() {
				var newText = input.val();

				$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
				}, "json");
			}, 1500);
		});

		propertyText.after(input);
		propertyText.hide();

		input.focus();
	});

	$("#agenda_point ul.objects").on("click", ".btn-motion-add-tag", function(event) {
		if (!hasRight(getUserId(), "handle_motion")) return;

		$(this).parents(".motion").find(".btn-motion-limits").addClass("disabled");

		var motionId = $(this).parents(".motion").data("id");
		$("#add-tag-modal input[name=motionId]").val(motionId);

		var motionContainer = $("#motion-" + motionId);
		var tagIds = motionContainer.data("tag-ids");
		tagIds = JSON.parse(tagIds);

		$("#add-tag-modal input[type=checkbox]").each(function() {
			$(this).prop("checked", false);
			for(var index = 0; index < tagIds.length; ++index) {
				if ($(this).val() == tagIds[index]) {
					$(this).prop("checked", true);
					break;
				}
			}
		});

		$("#add-tag-modal").modal("show");
	});

	$(".btn-modify-tags").click(function(event) {
		var motionId = $("#add-tag-modal input[name=motionId]").val();
		var tagIds = [];
		$("#add-tag-modal input[type=checkbox]:checked").each(function() {
			tagIds.push($(this).val());
		});

		var property = "mot_tag_ids";
		var propositionId = 0;
		var newText = JSON.stringify(tagIds);

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
			setMotionTags(motionId, tagIds);
			$("#add-tag-modal").modal("hide");
		}, "json");
		
	});

	$("#agenda_point ul.objects").on("click", ".btn-remove-tag", function(event) {
		var tag = $(this).parents(".tag");
		var motionId = tag.data("motion-id");
		var toRemoveTagId = tag.data("tag-id");

		var motionContainer = $("#motion-" + motionId);
		var tagIds = motionContainer.data("tag-ids");
		tagIds = JSON.parse(tagIds);
		var newTagIds = [];

		for(var index = 0; index < tagIds.length; ++index) {
			if (toRemoveTagId != tagIds[index]) {
				newTagIds.push(tagIds[index]);
			}			
		}

		var property = "mot_tag_ids";
		var propositionId = 0;
		var newText = JSON.stringify(newTagIds);

		$.post("meeting_api.php?method=do_changeMotionProperty", {motionId: motionId, propositionId: propositionId, property: property, text: newText}, function(data) {
			tag.remove();
			motionContainer.data("tag-ids", JSON.stringify(newTagIds));
		}, "json");
	});
}

function setMotionTags(motionId, tagIds) {
	var motionContainer = $("#motion-" + motionId);
	var motionTagsContainer = motionContainer.find(".motion-tags-container");
	
	motionTagsContainer.children().remove();

	for(var index = 0; index < tagIds.length; ++index) {
		var tagId = tagIds[index];
		
		for(var jndex = 0; jndex < tags.length; ++jndex) {
			var tag = tags[jndex];
			if (tag["tag_id"] == tagId) {
				
				var tagData = {tag_id: tag.tag_id, tag_label: tag.tag_label, mot_id: motionId}

				var tagLabel = $("div[data-template-id=tag]").template("use", {data: tagData});

				motionTagsContainer.append(tagLabel);

				break;
			}
		}
	}
}

$(function() {
	$(".btn-local-anonymous").click(function() {
		$(this).toggleClass("active");
	});

	addMotionHandlers();

});