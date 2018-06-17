/*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

function changeListOrderHandler(event) {
    var newOrder = $(this).val();
    var lis = [];
    var ul = $(this).parents("div").eq(1).find("ul.list-group");

    ul.children().each(function() {
        if ($(this).hasClass("unsortable")) return;
        lis.push($(this).detach());
    });
    
    lis.sort(function(a, b) {
        var aPinned = a.data("pinned") ? a.data("pinned") : 0;
        var bPinned = b.data("pinned") ? b.data("pinned") : 0;

        var diffPinned = bPinned - aPinned;

        // Pinned always first
        if (diffPinned != 0) return diffPinned;

        var aValue = a.data(newOrder);
        var bValue = b.data(newOrder);

        var diffValue = bValue - aValue;

        if (diffValue != 0) return diffValue;

        // If no difference respect internal order, maybe there is a structure to keep
        var aInternalOrder = a.data("internal-order") ? a.data("internal-order") : 0;
        var bInternalOrder = b.data("internal-order") ? b.data("internal-order") : 0;

        return aInternalOrder - bInternalOrder;
    });

    for(var index = 0; index < lis.length; ++index) {
        var li = lis[index];
        
        ul.append(li);
    }
}

$(function() {
    $("body").on("change", ".select-order", changeListOrderHandler)
});