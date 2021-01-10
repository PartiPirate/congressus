/* global $ */

function loadGroup(group, div, from, page) {
//    console.log(group);
    
    $.get("", {id: group, page: page}, function(data) {
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
    
    $("body").on("click", "a.pagination-link", function(e) {
        e.preventDefault();

        if ($(this).find("select").length) {
            return;
        }

        const page = $(this).data("page");
        const div = $(".tab-pane.active");
        const group = div.attr("id").replace("#", "");

        loadGroup(group, div, 0, page)
    })

    $("body").on("change", "select.page-select", function(e) {
        e.preventDefault();

        const page = $(this).find("option:selected").data("page");
        const div = $(".tab-pane.active");
        const group = div.attr("id").replace("#", "");

        loadGroup(group, div, 0, page)
    })


/*
    $('li.active a[data-toggle="tab"]').each(function () {
        var newlyGroup = $(this).attr("href");
        var newlyDiv = $(newlyGroup);

        loadGroup(newlyGroup.replace("#", ""), newlyDiv, 0);
    });
*/
    $('li a[data-toggle="tab"]').eq(0).click();
});