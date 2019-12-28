/*
    Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
/* global numberOfConnected */
/* global updateAgenda */
/* global updateAgendaPoint */
/* global updatePeople */
/* global getEvents */
/* global ping */
/* global checkAgendaMembers */

var getAgendaTimer;
var getAgendaTimerInterval = 2000;
var getAgendaPointTimer;
var getAgendaPointTimerInterval = 1500;
var getPeopleTimer;
var getPeopleTimerInterval = 2500;
var getEventsTimer;
var getEventsTimerInterval = 1500;

/* Invariable */
var pingTimer;
var performanceTimer;
var agendaMemberTimer;

function tweakTimerPerformance() {
    var performanceTweak = 1 + (numberOfConnected - 1) / 10;

    var newGetAgendaTimerInterval = Math.round(getAgendaTimerInterval * performanceTweak);
    if (newGetAgendaTimerInterval != getAgendaTimer.intervalTime) {
        getAgendaTimer.set({time: newGetAgendaTimerInterval});
    }

    var newGetAgendaPointTimerInterval = Math.round(getAgendaPointTimerInterval * performanceTweak);
    if (newGetAgendaPointTimerInterval != getAgendaPointTimer.intervalTime) {
        getAgendaPointTimer.set({time: newGetAgendaPointTimerInterval});
    }

    var newGetPeopleTimerInterval = Math.round(getPeopleTimerInterval * performanceTweak);
    if (newGetPeopleTimerInterval != getPeopleTimer.intervalTime) {
        getPeopleTimer.set({time: newGetPeopleTimerInterval});
    }

    if (typeof getEvents != "undefined") {
        var newGetEventsTimerInterval = Math.round(getEventsTimerInterval * performanceTweak);
        if (newGetEventsTimerInterval != getEventsTimer.intervalTime) {
            getEventsTimer.set({time: newGetEventsTimerInterval});
        }
    }
}

$(function() {
	getAgendaTimer = $.timer(updateAgenda);
	getAgendaTimer.set({ time : getAgendaTimerInterval, autostart : true });

	getAgendaPointTimer = $.timer(updateAgendaPoint);
	getAgendaPointTimer.set({ time : getAgendaPointTimerInterval, autostart : true });

	getPeopleTimer = $.timer(updatePeople);
	getPeopleTimer.set({ time : getPeopleTimerInterval, autostart : true });

	pingTimer = $.timer(ping);
	pingTimer.set({ time : 40000, autostart : true });

    if (typeof getEvents != "undefined") {
    	getEventsTimer = $.timer(getEvents);
    	getEventsTimer.set({ time : getEventsTimerInterval, autostart : true });
    }

	performanceTimer = $.timer(tweakTimerPerformance);
	performanceTimer.set({ time : 10000, autostart : true });

	agendaMemberTimer = $.timer(checkAgendaMembers);
	agendaMemberTimer.set({ time : 60000, autostart : true });
});