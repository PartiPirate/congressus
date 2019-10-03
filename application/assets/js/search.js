/*
    Copyright 2019 Cédric Levieux, Parti Pirate

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

function hideUnactiveSkills() {
	$(".btn-skill-filter").each(function() {
		if ($(this).hasClass("active")) {
			return;
		}
		
		$(this).hide();
	});
}

$(function() {
	const checkSuccesButtonState = function(target) {
		var dialog = target.parents(".modal-dialog");
		if (dialog.length == 0) return;

		var hasSelectedRow = dialog.find("tbody tr.selected-row").length != 0;

		var successButton = dialog.find(".modal-footer button[data-bb-handler=success]");

		successButton.removeClass("disabled");
		if (hasSelectedRow == 0) {
			successButton.addClass("disabled");
		}
	};

	$("body").on("click", ".search-user", function(event) {
		event.preventDefault();
		event.stopPropagation();

		var parameters = {};

		if ($(this).data("success-function")) {
			parameters["successFunction"] = $(this).data("success-function");
		}
		if ($(this).data("filter-theme-id")) {
			parameters["filterThemeId"] = $(this).data("filter-theme-id");
		}
		if ($(this).data("filter-with")) {
			parameters["filterWith"] = $(this).data("filter-with");
		}
		if ($(this).data("selection-type")) {
			parameters["selectionType"] = $(this).data("selection-type");
		}

		var successLabel = "OK";

		if ($(this).data("success-label")) {
			successLabel = $(this).data("success-label");
		}

		$.post("search_member.php?isModal=", parameters, function(data) {
			bootbox.dialog({
	            title: "Chercher un membre",
	            message: data,
	            buttons: {
	                success: {
	                    label: successLabel,
	                    className: "btn-primary",
	                    callback: function () {
	                    		var dialog = $(this);
	                    		var successFunction = eval(dialog.find("form #successFunction").val());
	                    		if (successFunction) {
//	                    			console.log("I have a success function");
//	                    			console.log(successFunction);

	                    			var rows = dialog.find("tbody tr.selected-row");

	                    			if (rows.length == 0) return;

	                    			successFunction(rows);
	                    		}
		                    }
		                },
		            close: {
	                    label: "Close",
	                    className: "btn-default",
	                    callback: function () {

		                    }
		                }
	            },
	            className: "large-dialog"
			});
			checkSuccesButtonState($("table.search-member-table"));

		}, "html");
	});

	$("body").on("click", ".btn-search-member", function(event) {
		event.preventDefault();
		event.stopPropagation();

		$.post("do_search_member.php", $(".search-member-form").serialize(), function(data) {
			/*
			data = {
					numberOfRows: 15,
					rows: [
							{id: 1, lastname: "A", firstname: "D", nickname: "farlistener", mail: "toto@toto.com", zipcode: "82400", city: "Valence d'Agen", status: ""},
							{id: 13, lastname: "B", firstname: "E", nickname: "nathouille", mail: "toto@toto.com", zipcode: "82400", city: "Valence d'Agen", status: ""},
							{id: 14, lastname: "C", firstname: "F", nickname: "jeey", mail: "toto@toto.com", zipcode: "82400", city: "Valence d'Agen", status: ""}
					]
			};
			*/

			var table = $("table.search-member-table");
			var tbody = $("table.search-member-table tbody");

			tbody.children().remove();
			table.show();

			for(var index = 0; index < data.rows.length; ++index) {
				var row = data.rows[index];

				var htmlRow = $("*[data-template-id=template-member]").template("use", {"data": row});
				htmlRow.data("row", row);
				tbody.append(htmlRow);
			}
		}, "json");
	});

	$("body").on("click", "table.search-member-table tbody tr", function(event) {
		var dialog = $(this).parents(".modal-dialog");
		var form = dialog.find("form");
		var selectionType = form.find("#selectionType").val();

		// If multi
		if (selectionType == "single"&& !$(this).hasClass("selected-row")) {
				dialog.find("table.search-member-table tbody tr").removeClass("selected-row");
		}
		$(this).toggleClass("selected-row");

		checkSuccesButtonState($(this));
	});

	$("body").on("click", ".btn-add-skill-filter", function(event) {
		var button = $(this);
		var icon = button.find(".fa");
		
		if (icon.hasClass("fa-plus")) {
			$(".btn-skill-filter").show();
		}
		else {
			hideUnactiveSkills();
		}
		
		button.toggleClass("btn-primary").toggleClass("btn-danger");
		icon.toggleClass("fa-plus").toggleClass("fa-minus");
		
		$(this).blur();
	});

	$("body").on("click", ".btn-skill-filter", function(event) {
		var button = $(this);
		button.toggleClass("active");
		
		var checkbox = $("#" + button.data("for"));
		checkbox.prop("checked", button.hasClass("active"));
	});
});