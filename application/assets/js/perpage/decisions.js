/* global $ */

function loadGroup(group, div, from) {
//    console.log(group);
    
    $.get("", {id: group}, function(data) {
        var previousChildren = div.children();
        var children = $(data).find("#" + group).children();
        
        div.append(children);
        previousChildren.remove();
        
        children.find('[data-toggle="tooltip"]').tooltip();
    }, "html");
}

$(function() {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // console.log(e.target); // newly activated tab
        // console.log(e.relatedTarget); // previous active tab
        
        var newlyGroup = $(e.target).attr("href");
        
        var newlyDiv = $(newlyGroup);
        var previousDiv = $($(e.relatedTarget).attr("href"));
        
        loadGroup(newlyGroup.replace("#", ""), newlyDiv, 0);
    });

    $('li.active a[data-toggle="tab"]').each(function () {
        var newlyGroup = $(this).attr("href");
        var newlyDiv = $(newlyGroup);

        loadGroup(newlyGroup.replace("#", ""), newlyDiv, 0);
    })
});