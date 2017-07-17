/*
	Copyright 2017 Nino Treyssat-Vincent, Parti Pirate

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

$('.btnShowExport').click(function(){
  template = $(this).data("template");
  if (template=='markdown' || template=='discourse'){
    textarea = 'true';
  } else {
    textarea = 'false';
  }
  $.get("export.php", {template: template, id: meeting_id, textarea: textarea}, function(data){
    $("#exportModal").empty().append(data);
  });
});

// closed by esc
$(window).keyup(function(e) {
  if (e.keyCode == 27) {
    $("#exportModal").empty();
  }
});
// closed by click outside of modal
$(document).on('click', function(event){
    if(event.target.id.slice(0,16) == "export_container"){
        $("#exportModal").empty();
    }
});
