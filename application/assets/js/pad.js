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
/* global PAD_WS */
/* global stringDiff */

$(function() {
    $("*[data-pad=enabled]").each(function() {
        var lastPadEventTime = 0;
        var area = null;
        var padTimer = null;
        var socket = null;
        var internalText = null;

        var toolbar = null;
        var nicknameHolder = null;

        var sendPadEvent = function(event) {
            socket.send(JSON.stringify(event));
        };

        var attach = function() {
            var padId = area.data("pad-id");
            var senderId = area.data("pad-sender");
            
            var event = {padId: padId, senderId: senderId, nickname: $(".nickname-link").data("nickname"), event: "attach"};
            
            sendPadEvent(event);
            
            event = {padId: padId, senderId: senderId, event: "synchronize", content: area.html()};
            
            internalText = area.html();
            sendPadEvent(event);
        }
    
        var updatePad = function(event) {
            area.html(event.content);
            area.caret('pos', event.caretPosition);
            internalText = event.content;
        };
    
        var openSocket = function() {
            socket = new WebSocket(PAD_WS);
            socket.onopen = function(e) {
                attach();                
            };

            socket.onmessage = function(e) {
                var data = JSON.parse(e.data);
//                console.log(data);

                if (area.is(":visible")) {
    //                var nicknameHolder = $(".nickname-holder[data-pad-id="+data.padId+"]");
                    var position = area.offset();
    //                nicknameHolder.css({top: Math.round(position.top - 50) +"px", left: Math.round(position.top + 50) +"px"});
                    toolbar.css({top: Math.round(position.top - toolbar.height()) +"px", left: Math.round(position.left) +"px"});
                    toolbar.width(area.width());
                    toolbar.show();
                }

                switch(data.event) {
                    case "connected":
                        toolbar.find(".connection-status").text("Connecté");
                        toolbar.find(".ws-connect-btn").prop("disabled", true);
//                        console.log("Rid " + data.rid);
                        break;
                    case "synchronize":
                        var senderId = area.data("pad-sender");
                        var event = {event: "synchronizer", rid: data.rid, padId : data.padId, senderId: senderId, content: area.html()};
                        sendPadEvent(event)
                        break;
                    case "synchronizer":
                        area.html(data.content);
                        internalText = data.content;
                        break;
                    case "keyup":
                    case "diff":
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

                autogrowElement(area.get(0));
            };

            socket.onclose = function(e) {
                console.log("perte de la connexion !");
                toolbar.find(".connection-status").text("Déconnecté");
                toolbar.find(".ws-connect-btn").prop("disabled", false);
            }
        }

/*
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
*/

        var createToolbar = function() {
            var padId = area.data("pad-id");

            toolbar = $(".pad-toolbar[data-pad-id="+padId+"]");
            if (toolbar.length == 0) {
                toolbar = "<div class='pad-toolbar' data-pad-id='"+padId+"' style='height: 20px; border: 1px solid black; border-radius: 2px; background: white; position: absolute; z-index: 1000; opacity: 0.5; display: none;'>Statut : <span class='connection-status'></span> <button class='ws-connect-btn btn btn-xs btn-default'>Connecter</button></div>";
                nicknameHolder = "<div class='nickname-holder'></div>"

                $("body").append(toolbar);
                toolbar = $(".pad-toolbar[data-pad-id="+padId+"]");
                toolbar.append($(nicknameHolder));
            }

            nicknameHolder = toolbar.find(".nickname-holder");
            toolbar.find(".ws-connect-btn").click(function() {
                openSocket();
            });
        }

        var enablePad2 = function(eventArea) {
            area = eventArea;

            createToolbar();

            var padId = area.data("pad-id");
            var senderId = area.data("pad-sender");

            openSocket();

            var keyTimeoutId = null;
            var previousCaretPosition = area.caret("pos");
            var previousContent = area.html();

            var cleanReturn = function(content) {
                content = content.replace("</div>", "");
                content = content.replace("<div>", "\n").replace("<br>", "\n");

                return content;
            }

            var endOfTyping = function() {
                var currentContent = area.html();
                var currentCaretPosition = area.caret("pos");
                clearInterval(keyTimeoutId);
                keyTimeoutId = null;

                var diff = stringDiff(previousContent, currentContent);

                console.log(diff);

                if (diff.length == 1 && diff[0][0] == "=") {
                    var padEvent = {event: "newCaretPosition", padId: padId, senderId: senderId, caretPosition: currentCaretPosition ? currentCaretPosition : 0};

                    sendPadEvent(padEvent);

                    return;
                }

                var padEvent = {event: "diff", padId: padId, senderId: senderId, caretPosition: currentCaretPosition ? currentCaretPosition : 0, diff: diff};

                sendPadEvent(padEvent);
            };

            area.keydown(function(event) {
                if (!keyTimeoutId) {
                    previousCaretPosition = area.caret("pos");
                    previousContent = cleanReturn(area.html());
                }
                else {
                    clearInterval(keyTimeoutId);
                }
            });

            area.keyup(function(event) {

                if (event.keyCode == 13) {
                    event.stopImmediatePropagation();
                    event.preventDefault();

                    var localCaretPosition = area.caret("pos");
                    var content = cleanReturn(area.html());

                    area.html(content);
                    area.caret("pos", localCaretPosition + 1);
                } 

                keyTimeoutId = setTimeout(endOfTyping, 300);
            });

            area.click(function(event) {
                var caretPosition = area.caret("pos");
                var padEvent = {event: "newCaretPosition", padId: padId, senderId: senderId, caretPosition: caretPosition ? caretPosition : 0};

                sendPadEvent(padEvent);
            });
        }

        var enablePad = function(eventArea) {
            area = eventArea;

            addNicknameHolder();
            
            var padId = area.data("pad-id");
            var senderId = area.data("pad-sender");
//            var caretPositionsBefore = {};

            openSocket();

            var previousCaretPosition = 0;
/*
            var previousContent = null;

            var keyTimeoutId = null;

            var endOfTyping = function() {
                var currentContent = area.html();
                var currentCaretPosition = area.caret("pos");
                keyTimeoutId = null;
                
                console.log(previousContent + " <> " + currentContent);
                console.log(previousCaretPosition + " <> " + currentCaretPosition);
            };

            area.keydown(function(event) {
                if (!keyTimeoutId) {
                    previousCaretPosition = area.caret("pos");
                    previousContent = area.html();
                }
                else {
                    clearInterval(keyTimeoutId);
                }
                keyTimeoutId = setTimeout(endOfTyping, 300);
            });
*/

//            console.log("Enable pad " + padId);

            
            area.keydown(function(event) {
                previousCaretPosition = area.caret("pos");
            });

/*
            area.keydown(function(event) {
                caretPositionsBefore[event.key] = area.caret("pos");
                console.log("Keydown " + event.key + " => " + caretPositionsBefore[event.key]);
            });
            
            area.keypress(function(event) {
                console.log("Keypress " + event.key + " => " + caretPositionsBefore[event.key]);
            });
*/
            area.keyup(function(event) {
//                console.log("Keyup " + event.key + " => " + caretPositionsBefore[event.key] + ", " +area.caret("pos"));
                
//                var myCaretPosition = event.target.selectionStart;
                var keyCode = event.keyCode;
                var key = event.key;
                
                console.log(event);

                if (!   event.keyCode || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 225
                    ||  event.key == "Dead"
//                 ||   event.keyCode == 219 || event.keyCode == 55 || event.keyCode == 49
                ) {
                    // Do nothing;
                    return;
                }

                if (event.keyCode == 33 || event.keyCode == 34 || event.keyCode == 35 || event.keyCode == 36 ||
                    event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40) {

                    var caretPosition = area.caret("pos");
                    var padEvent = {event: "newCaretPosition", padId: padId, senderId: senderId, caretPosition: caretPosition};

                    sendPadEvent(padEvent);

                    return;
                }

                if (event.keyCode == 13) {
                    event.stopImmediatePropagation();
                    event.preventDefault();
                } 

                if (event.ctrlKey) {
                    return;
                }

                if (event.keyCode == 46) {

                    var numberOfDeletedCharacters = internalText.length;
                    internalText = area.html();
                    numberOfDeletedCharacters -= internalText.length;

                    var caretPositionAfter = area.caret("pos");

                    var padEvent = {event: "keyup", padId: padId, senderId: senderId, /*caretPositionBefore: caretPositionsBefore[event.key], */caretPositionAfter: caretPositionAfter, keyCode: keyCode, key: key, /*content: area.html(), */numberOfDeletedCharacters: numberOfDeletedCharacters};

                    console.log(padEvent);

                    sendPadEvent(padEvent);

                    return;
                }

                internalText = area.html();

                var caretPositionAfter = area.caret("pos");
/*
                if ((previousCaretPosition - caretPosition) == 2) {
                    caretPosition--;
                }
*/

                var padEvent = {event: "keyup", padId: padId, senderId: senderId, /*caretPositionBefore: caretPositionsBefore[event.key], */caretPositionAfter: caretPositionAfter, keyCode: keyCode, key: key/*, *//*content: area.html()*/};

//                console.log(padEvent);

                sendPadEvent(padEvent);
            });

            area.click(function(event) {
                var caretPosition = area.caret("pos");
                var padEvent = {event: "newCaretPosition", padId: padId, senderId: senderId, caretPosition: caretPosition};

                sendPadEvent(padEvent);
            });
        }

//        enablePad($(this));
        enablePad2($(this));
    });
});