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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

/* global $ */
/* global getUserId */

let socket = null;
const eventCallbacks = {};
let eventID = 0;

const sendEvent = function(event, callback) {
    if (socket) {
        eventID++;
        eventCallbacks[eventID] = callback;
        event["EVENT_ID"] = eventID;
        socket.send(JSON.stringify(event));
    }
};

$(function() {
    const PAD_WS = "wss://congressus.partipirate.org:33333/";
    const meetingId = $(".meeting").data("id");
    const userId = getUserId();

    const attach = function() {

        var event = {meetingId: meetingId, userId: userId, event: "m_attach"};
        sendEvent(event);
    };

    const openSocket = function() {

        const localSocket = new WebSocket(PAD_WS);
        localSocket.onopen = function(e) {
            console.log("On Socket Open");
            socket = localSocket;
            attach();                
        };
        
        localSocket.onerror = function(e) {
            console.log("refus de connexion !");

            socket = null;
        }

        localSocket.onmessage = function(e) {
            var data = JSON.parse(e.data);


            switch(data.event) {
                case "connected":
                    console.log("Connecté");
                    break;
                case "motion":
                    console.log("Motion "+data.motionId+" need to be updated");
                    break;
            }


            if (data.EVENT_ID) {
                if (eventCallbacks[data.EVENT_ID]) {
//                    console.log("Call a callback : " + eventCallbacks[data.EVENT_ID])
                    eventCallbacks[data.EVENT_ID](data);
                }
                delete eventCallbacks[data.EVENT_ID];
            }
        };

        localSocket.onclose = function(e) {
            console.log("perte de la connexion !");

            socket = null;
        }
    }

    openSocket();
});