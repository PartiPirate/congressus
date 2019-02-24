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
/* gloabl PAD_WS */
/* gloabl stringDiff */

$(function() {
    $("*[data-pad=enabled]").each(function() {
        var lastPadEventTime = 0;
        var area = null;
        var padTimer = null;
        var socket = null;
        var internalText = null;

        var sendPadEvent = function(event) {
            socket.send(JSON.stringify(event));
        };

        var attach = function() {
            var padId = area.data("pad-id");
            var senderId = area.data("pad-sender");
            
            var event = {padId: padId, senderId: senderId, nickname: $(".nickname-link").data("nickname"), event: "attach"};
            
            sendPadEvent(event);
            
            event = {padId: padId, senderId: senderId, event: "synchronize"};
            
            internalText = area.val();
            sendPadEvent(event);
        }
    
        var updatePad = function(event) {

            var currentText = area.val();
            var selectionStart = area.get(0).selectionStart;
            var selectionEnd = area.get(0).selectionEnd;

            if (   event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 225
                    || event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 35 || event.keyCode == 36
                    || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40) {
                // Ignore
                return;
            }
/*
            var before = currentText.substring(0, event.caretPosition);
            var after = currentText.substring(event.caretPosition);

//            console.log(before + "##" + after);

            if (!event.key) return;

            else if (   event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 225
                     || event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 35 || event.keyCode == 36
                     || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40) {
                // Ignore
                return;
            }
            else if (event.keyCode == 8) {
                before = before.substring(0, before.length - 1);
                if (selectionStart > event.caretPosition) selectionStart--;
                if (selectionEnd > event.caretPosition) selectionEnd--;
            }
            else if (event.keyCode == 46) {
                after  = after.substring(1);
                if (selectionStart > event.caretPosition) selectionStart--;
                if (selectionEnd > event.caretPosition) selectionEnd--;
            }
            else if (event.keyCode == 13) {
                before += "\n";
                if (selectionStart > event.caretPosition) selectionStart++;
                if (selectionEnd > event.caretPosition) selectionEnd++;
            }
            else {
                before += event.key;
                if (selectionStart > event.caretPosition) selectionStart++;
                if (selectionEnd > event.caretPosition) selectionEnd++;
            }

            currentText = before + after;
*/

            var lengthDiff = event.content.length - internalText.length;
            
            if (selectionStart > event.caretPosition) selectionStart += lengthDiff;
            if (selectionEnd > event.caretPosition) selectionEnd += lengthDiff;

            area.val(event.content);
            area.get(0).selectionStart = selectionStart;
            area.get(0).selectionEnd = selectionEnd;
            
            internalText = event.content;
        };
    
        var openSocket = function() {
            socket = new WebSocket(PAD_WS);
            socket.onopen = function(e) {
//                console.log("Connection established!");

                attach();                
            };

            socket.onmessage = function(e) {
                var data = JSON.parse(e.data);
//                console.log(data);

                var nicknameHolder = $(".nickname-holder[data-pad-id="+data.padId+"]");
                var position = area.offset();
                nicknameHolder.css({top: Math.round(position.top - 50) +"px", left: Math.round(position.top + 50) +"px"});
                
                switch(data.event) {
                    case "connected":
//                        console.log("Rid " + data.rid);
                        break;
                    case "synchronize":
                        var senderId = area.data("pad-sender");
                        var event = {event: "synchronizer", rid: data.rid, padId : data.padId, senderId: senderId, content: area.val()};
                        sendPadEvent(event)
                        break;
                    case "synchronizer":
                        area.val(data.content);
                        internalText = data.content;
                        break;
                    case "keyup":
                        updatePad(data);
                        break;
                    case "nicknames":
                        nicknameHolder.text("");
                        for(var index = 0; index < data.nicknames.length; ++index) {
                            if (index) {
                                nicknameHolder.text(nicknameHolder.text() + ", ");
                            }
                            nicknameHolder.text(nicknameHolder.text() + data.nicknames[index]);
                        }
                        break;
                }
            };

            socket.onclose = function(e) {
                console.log("perte de la connextion !");
            }
        }

        var addNicknameHolder = function() {
            var padId = area.data("pad-id");
            var position = area.offset();
            
            var nicknameHolder = $(".nickname-holder[data-pad-id="+padId+"]");
            if (nicknameHolder.length == 0) {
                nicknameHolder = "<div class='nickname-holder' data-pad-id='"+padId+"' style='float: left; position: absolute; top: "+ Math.round(position.top - 50) +"px; left: "+ Math.round(position.top + 50) +"px; z-index: 1000; opacity: 0.5;'></div>"

                // Not satisfying
//                $("body").append(nicknameHolder);
            }
        }

        var enablePad = function(eventArea) {
            area = eventArea;

            addNicknameHolder();
            
            var padId = area.data("pad-id");
            var senderId = area.data("pad-sender");
            var carePosition = 0;

            openSocket();

//            console.log("Enable pad " + padId);

            area.keydown(function(event) {
                carePosition = event.target.selectionStart;
            });

            area.keyup(function(event) {
//                var myCaretPosition = event.target.selectionStart;
                var keyCode = event.keyCode;
                var key = event.key;

/*
                var diff = stringDiff(internalText, area.val());
*/                
                internalText = area.val();

                var padEvent = {event: "keyup", padId: padId, senderId: senderId, caretPosition: carePosition, keyCode: keyCode, key: key, /*diff: diff, */ content: area.val()};
    
                console.log(padEvent);

                sendPadEvent(padEvent);
            });
        }

        enablePad($(this));
    });
});