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

function enableMovingAbilty(selector, resizeHandles, css, stopFunction) {
	$(selector).resizable({
//		animate: true, 
		handles: resizeHandles,
		stop: stopFunction
	});
	$(selector).draggable({});
	css["position"]  = "fixed";
	css["z-index"]  = 10;
	css["box-shadow"] = "#101010 1px 1px 5px";
	$(selector).css(css)
}

function disableMovingAbilty(selector) {
	$("#speaking-panel").draggable("destroy");
	$("#speaking-panel").resizable("destroy");
	$("#speaking-panel").css({left:"", top: "", position: "", width:"", heigh: "", "box-shadow": "", "z-index": ""});
}

function enableMovingAbiltyOnSpeakingPanel() {
/*
	let stopFunction = function(event, ui) {
		ui.element.css({height: ""});
	}

	enableMovingAbilty("#speaking-panel", "e, w", {}, stopFunction);
*/
	enableMovingAbilty("#speaking-panel", "e, w", {}, null);
}

$(function() {
	$("#speaking-panel .btn-move").click(function() {
		if ($(this).hasClass("active")) {
			$(this).removeClass("active");
			disableMovingAbilty("#speaking-panel");
		}
		else {
			$(this).addClass("active");
			enableMovingAbiltyOnSpeakingPanel();
		}
	})
});