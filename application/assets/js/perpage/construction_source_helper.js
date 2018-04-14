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

function sourceSelectHandler(container, $this) {
	var source = $this.val();

	switch(source) {
		case "leg_text":
		case "leg_article":
		case "wiki_text":
		case "congressus_motion":
			container.find("#sourceUrlDiv").show();

			break;
		case "forum":
		case "pdf":
			container.find("#sourceUrlDiv").show();
			container.find("#sourceTitleDiv input").val();
			container.find("#sourceTitleDiv").show();

			break;
		default: 
		container.find("#sourceUrlDiv").hide();
	}
}

function sourceUrlHandler(container, $this) {
	var source = container.find("#sourceSelect").val();
	var url = $this.val();

	if (source == "leg_article") {
		legifranceArticleRequester(url, container);
	}
	else if (source == "leg_text") {
		legifranceTextRequester(url, container);
	}
	else if (source == "wiki_text") {
		wikiTextRequester(url, container);
	}
	else if (source == "congressus_motion") {
		congressusMotionRequester(url, container);
	}
}

var articles = null;

function legifranceTextRequester(url, container) {
	$.get("construction/getLegifranceText.php", {"url":url}, function(data) {
		if (data.status == "ok") {
			container.find("#sourceTitleDiv").show();
			container.find("#sourceTitleInput").val(data.title);
			container.find("#sourceContentDiv").show();
			container.find("#sourceContentArea").val("");
			container.find("#sourceContentArea").keyup();

			articles = data.articles;

			var sourceArticlesSelect = container.find("#sourceArticlesSelect");
			sourceArticlesSelect.children().remove();

			for(var index = 0; index < articles.length; ++index) {
				var option = $("<option></option>").val(index).text(articles[index].title);
				sourceArticlesSelect.append(option);
			}

			container.find("#sourceArticlesDiv").show();
		}
		else {
			container.find("#sourceTitleDiv").hide();
			container.find("#sourceContentDiv").hide();
			container.find("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function wikiTextRequester(url, container) {
	$.get("construction/getWikiText.php", {"url":url}, function(data) {
		if (data.status == "ok") {
			container.find("#sourceTitleDiv").show();
			container.find("#sourceTitleInput").val(data.title);
			container.find("#sourceContentDiv").show();
			container.find("#sourceContentArea").val("");
			container.find("#sourceContentArea").keyup();

//			container.find("#sourceContentArea").val(data.content);

			articles = data.articles;

			var sourceArticlesSelect = container.find("#sourceArticlesSelect");
			sourceArticlesSelect.children().remove();

			for(var index = 0; index < articles.length; ++index) {
				var option = $("<option></option>").val(index).text(articles[index].title);
				sourceArticlesSelect.append(option);
			}

			container.find("#sourceArticlesDiv").show();
		}
		else {
			container.find("#sourceTitleDiv").hide();
			container.find("#sourceContentDiv").hide();
			container.find("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function legifranceArticleRequester(url, container) {
	$.get("construction/getLegifranceArticle.php", {"url":url}, function(data) {
		if (data.status == "ok") {
			container.find("#sourceTitleDiv").show();
			container.find("#sourceTitleInput").val(data.title);
			container.find("#sourceContentDiv").show();
			container.find("#sourceContentArea").val(data.content);
			container.find("#sourceContentArea").keyup();
		}
		else {
			container.find("#sourceTitleDiv").hide();
			container.find("#sourceContentDiv").hide();
			container.find("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function congressusMotionRequester(url, container) {
	$.get("construction/getCongressusMotion.php", {"url":url}, function(data) {
		if (data.status == "ok") {
			container.find("#sourceTitleDiv").show();
			container.find("#sourceTitleInput").val(data.title);
			container.find("#sourceContentDiv").show();
			container.find("#sourceContentArea").val(data.content);
			container.find("#sourceContentArea").keyup();
		}
		else {
			container.find("#sourceTitleDiv").hide();
			container.find("#sourceContentDiv").hide();
			container.find("#sourceArticlesDiv").hide();
		}
	}, "json");
}

function sourceArticlesHandler(container) {
	var content = "";
	var contentSeparator = "";
	
	container.find("#sourceArticlesSelect option:selected").each(function() {
		var index = $(this).val();
		var article = articles[index];
		
		content += contentSeparator;
		if (article.level) {
			for(var lindex = 0; lindex < article.level; ++lindex) {
				content += "=";
			}
			content += " ";
		}
		content += article.title;
		if (article.level) {
			content += " ";
			for(var lindex = 0; lindex < article.level; ++lindex) {
				content += "=";
			}
		}
		
		if (article.content.trim()) {
			content += "\n\n" + article.content.trim();
		}
		
		contentSeparator = "\n\n";
	});
	
	container.find("#sourceContentArea").val(content);
	container.find("#sourceContentArea").keyup();
}
