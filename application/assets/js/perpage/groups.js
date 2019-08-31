/*
	Copyright 2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/

/* global $ */

function openGroupUrl(url) {
    const frame = $("#frame");
    frame.html("");

    $.get(url, function(data) {
        var groupContainer = $(data).filter(".group-container").removeClass("container");
        var alertContainer = $(data).filter(".alert-container");

        frame.append(groupContainer);
        frame.append(alertContainer);
        frame.append('<script src="assets/js/perpage/group.js"></script>');
    }, "html");
}

function openThemeUrl(url) {
    const frame = $("#frame");
    frame.html("");

    $.get(url, function(data) {
        var themeContainer = $(data).filter(".theme-container").removeClass("container");
        var alertContainer = $(data).filter(".alert-container");

        frame.append(themeContainer);
        frame.append(alertContainer);
        frame.append('<script src="assets/js/perpage/theme.js"></script>');
        frame.append('<script src="assets/js/perpage/theme_user_delegation_advanced.js"></script>');
        
    }, "html");
}

/**
 * Update the tree part of the page
 */
function updateGroupTree() {
    const frame = $(".group-container-div");
    frame.html("");

    $.get("groups.php", function(data) {
        var groupsContainer = $(data).filter(".theme-showcase").children(".group-container-div").children(".group-container-list");

        frame.append(groupsContainer);

    }, "html");
}

$(function() {
    
    $("body").on("click", ".add-group-button", function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

		var url = "group.php?id=0&admin=";

        openGroupUrl(url);
    });

	$(".handleGroupButton").click(function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

		var groupId = $(this).data("group-id");
		var url = "group.php?id=" + groupId + "&admin=";

        openGroupUrl(url);
	});
    
    $("body").on("click", ".group-link,.group-admin-link", function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        openGroupUrl($(this).attr("href"));
    });


    $("body").on("click", ".addThemeButton", function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

		var groupId = $(this).data("group-id");
		var url = "theme.php?groupId=" + groupId + "&id=0&admin=";

        openThemeUrl(url);
    });

    $("body").on("click", ".theme-link,.theme-admin-link", function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        openThemeUrl($(this).attr("href"));
    });
});