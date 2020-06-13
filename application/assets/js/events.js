/*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

function uuidv4() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
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

function addEventAlert(text, eventClass, showDelay) {
    const alertId = "alert-" + uuidv4();
    
	var eventAlert = $("<p id='" + alertId + "' style='width: 350px; height: 55px; z-index: 1000; position: fixed; right: 10px;' class='congressus-event form-alert simply-hidden bg-" + eventClass + "'>" + text + "</p>");
	var body = $("body");
	body.append(eventAlert);

	computeEventPositions();

    eventAlert.show();

    if (showDelay) {
    	eventAlert.delay(showDelay).fadeOut(1000, function() {
    		$(this).remove();
    		computeEventPositions();
    	});
    }

    return alertId;
}

function removeEventAlert(alertId) {
    $("#" + alertId).remove();
	computeEventPositions();
}