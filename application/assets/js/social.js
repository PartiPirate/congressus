/*
    Copyright 2014-2017 Cédric Levieux, Parti Pirate

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

function windowPopup(url, width, height) {
    // Calculate the position of the popup so
    // it’s centered on the screen.
    var left = (screen.width / 2) - (width / 2),
        top = (screen.height / 2) - (height / 2);

    window.open(url, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left);
}

function directWindowPopup(url, text, width, height) {
    var left = (screen.width / 2) - (width / 2),
        top = (screen.height / 2) - (height / 2);

    var openWindow = window.open("", "Direct link", "menubar=no,toolbar=no,location=no,directories=no,resizable=yes,scrollbars=yes,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left);
    openWindow.document.title = "Direct link";
    openWindow.document.body.innerHTML = (text ? text + "<br>" : "") + "<textarea style='width: 100%;'>"+url+"</textarea>";
}

$(function() {
    $(".social-link").on("click", function(event) {
        event.preventDefault();

        var width = $(this).data("popup-width") ? $(this).data("popup-width") : 500;
        var height = $(this).data("popup-height") ? $(this).data("popup-height") : 300;

        windowPopup($(this).attr("href"), width, height);
    });
    
    $(".direct-link").on("click", function(event) {
        event.preventDefault();

        var width = $(this).data("popup-width") ? $(this).data("popup-width") : 500;
        var height = $(this).data("popup-height") ? $(this).data("popup-height") : 300;
        var text = $(this).data("popup-text") ? $(this).data("popup-text") : "";
        
         directWindowPopup($(this).attr("href"), text, width, height);
    });
    
});