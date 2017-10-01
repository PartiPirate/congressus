/*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

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
/* global bootbox */

/* global speakingTimesChartTitle */
/* global common_close */
/* global computeTimeString */

var speakingStats = {speakingTimePerPerson: {}};

function updateChart(motionContainer, data) {
	var chartContainer = motionContainer.find(".motion-charts");
	if (chartContainer.length) {
		var chart = chartContainer.CanvasJSChart(); 
	
		chart.options.data = data;
		try {
			chart.render();
		}
		catch(e) {
			
		}
	}
}

function initPercentChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");

	if (chartContainer.data("status") != "to-init") return;
	chartContainer.data("status", "in-construction");

	var width = chartContainer.width();
	var height = 400;

	chartContainer.css({height: height + "px"});

	var chartOptions = {
		theme: "theme2",
		exportFileName: speakingTimesChartTitle,
		exportEnabled: true,
        animationEnabled: true,
        height: height,
        width: width,
        legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		toolTip: {
			shared: true,
			contentFormatter: function (e) {
				var content = " ";
				content += e.entries[0].dataPoint.indexLabel + " - " + "<strong>" + e.entries[0].dataPoint.y + "%</strong>";
				return content;
			}
		},
		data: []
	};


	chartContainer.CanvasJSChart(chartOptions);

	chartContainer.data("status", "initialized");
}

function initJMChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");

	if (chartContainer.data("status") != "to-init") return;
	chartContainer.data("status", "in-construction");

	var width = chartContainer.width();
	var height = 400;

	chartContainer.css({height: height + "px"});

	var chartOptions = {
		theme: "theme2",
		exportFileName: motionContainer.find(".motion-title").text(),
		exportEnabled: true,
        animationEnabled: true,
        height: height,
        width: width,
        legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		axisX: {
			minimum: 0,
			interval: 1,
			labelFormatter: function ( e ) {
				var content = "";

				if (e.value && (e.value == Math.round(e.value))) {
					
					content += motionContainer.find(".proposition").eq(e.value - 1).find(".proposition-label").text();
				}
			
				return content;
			}
		},
		axisY: {
			minimum: 0,
			maximum: 100,
			interval: 10,
			labelFormatter: function ( e ) {
				var content = "";

				content += e.value + "%";

				return content;
        	}  
		},
		toolTip: {
			contentFormatter: function (e) {
				var content = " ";
				content += e.entries[0].dataPoint.xLabel + " - " + "<strong>" + e.entries[0].dataPoint.yLabel + "</strong>";

				content += " (" +e.entries[0].dataPoint.y + "%)";

				return content;
			}
		},
		data: []
	};

	chartContainer.CanvasJSChart(chartOptions);

	chartContainer.data("status", "initialized");
}

function initBordaChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");

	if (chartContainer.data("status") != "to-init") return;
	chartContainer.data("status", "in-construction");

	var width = chartContainer.width();
	var height = 400;

	chartContainer.css({height: height + "px"});

	var chartOptions = {
		theme: "theme2",
		exportFileName: motionContainer.find(".motion-title").text(),
		exportEnabled: true,
        animationEnabled: true,
        height: height,
        width: width,
        legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		axisX: {
			minimum: 0,
			interval: 1,
			labelFormatter: function ( e ) {
				var content = "";

				if (e.value && (e.value == Math.round(e.value))) {
					
					content += motionContainer.find(".proposition").eq(e.value - 1).find(".proposition-label").text();
				}
			
				return content;
			}
		},
		axisY: {
			minimum: 0,
//			maximum: 100,
//			interval: 10,
			labelFormatter: function ( e ) {
				var content = "";

				content += e.value;

				return content;
        	}  
		},
		toolTip: {
			contentFormatter: function (e) {
				var content = " ";
				content += e.entries[0].dataPoint.xLabel + " - " + "<strong>" + e.entries[0].dataPoint.yLabel + "</strong>";

//				content += " (" +e.entries[0].dataPoint.y + "%)";

				return content;
			}
		},
		data: []
	};

	chartContainer.CanvasJSChart(chartOptions);

	chartContainer.data("status", "initialized");
}

function showSpeakingStats(event) {
	
	var width = 568;
	var height = 400;
	var dialog = $("<div id='speaking-charts-container'></div>");
	dialog.css({height: height + "px", width: width + "px"});
	
	bootbox.dialog({
	    title: speakingTimesChartTitle,
	    message: dialog,
	    buttons: {
	        close: {
	            label: common_close,
	            className: "btn-default",
	            callback: function () {
	
	                }
	            }
	    },
	    className: "not-large-dialog"
	});

	var speakingTimesData = [];
	for(var nickname in speakingStats.speakingTimePerPerson) {
		speakingTimesData[speakingTimesData.length] = {indexLabel: nickname, y: speakingStats.speakingTimePerPerson[nickname], timeLabel: computeTimeString(speakingStats.speakingTimePerPerson[nickname])};
	}

	var chartOptions = {
		theme: "theme2",
		exportFileName: speakingTimesChartTitle,
		exportEnabled: true,
        animationEnabled: true,
        height: height,
        width: width,
        legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		toolTip: {
			shared: true,
			contentFormatter: function (e) {
				var content = " ";
				content += e.entries[0].dataPoint.indexLabel + " - " + "<strong>" + e.entries[0].dataPoint.timeLabel + "</strong>";
				
				var total = 0;	
				for (var i = 0; i < e.entries[0].dataSeries.dataPoints.length; i++) {
					total += e.entries[0].dataSeries.dataPoints[i].y;
				}
				
				content += " (" + Math.round(e.entries[0].dataPoint.y * 10000 / total) / 100 + "%)";

				return content;
			}
		},
		data: [
		{
			type: "pie",
			showInLegend: true,
			legendText: "{indexLabel}",
			dataPoints: speakingTimesData
		}
		]
	};
	var chart = new CanvasJS.Chart("speaking-charts-container", chartOptions);
	chart.render();
}

$(function() {
	$(".btn-see-speaking-stats").click(showSpeakingStats);
	$("#agenda_point ul.objects").on("click", ".btn-see-motion-stats", function(event) {
		var button = $(this);
		
		button.toggleClass("active");
		
		var chartContainer = button.parents(".motion").find(".motion-charts");
		
		if (button.hasClass("active")) {
			chartContainer.show();
		}
		else {
			chartContainer.hide();
		}

	});
//	$(".btn-see-motion-stats").click(showMotionStats);
});
