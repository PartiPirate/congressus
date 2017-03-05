/* global $ */
/* global query */

function highlight(text) {
	$(".text-search").each(function() {
		var re = new RegExp("("+ text +")", "gmi");
		var str = $(this).html();
		var subst = '<span class="highlight">' + query + '</span>';

		var result = str.replace(re, subst);

		$(this).html(result);
	});
}

$(function() {
	highlight(query);
});