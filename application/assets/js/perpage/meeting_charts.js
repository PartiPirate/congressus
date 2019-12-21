/*
    Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
/* global Chart */
/* global joint */

/* global speakingTimesChartTitle */
/* global motionDelegationsTitle */
/* global common_close */
/* global computeTimeString */

//var chartColors = ["hsla(349, 99%, 63%, opacity)", "hsla(142, 81%, 63%, opacity)", "hsla(2, 94%, 80%, opacity)", "hsla(25, 86%, 83%, opacity)", "hsla(60, 22%, 72%, opacity)", "hsla(153, 22%, 60%, opacity)", "hsla(282, 41%, 54%, opacity)", "hsla(164, 79%, 67%, opacity)"];
var chartColors = [];
for(var index = 0; index < 20; ++index) {
	chartColors.push("hsla("+ ((360 * index / 15 + (index % 2) * 180) % 360) +", 70%, 70%, opacity)");
}
var positiveColor = "hsla(120, 70% ,70%, opacity)";
var negativeColor = "hsla(0, 70% ,70%, opacity)";

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

function updateChart2(motionContainer, data) {
	var chartContainer = motionContainer.find(".motion-charts");
	if (chartContainer.length) {
		var chart = chartContainer.data("chart");

		chart.data.labels = data.labels;
		chart.data.datasets = data.datasets;

	    chart.update();
	}
}

function shortenLabel(label, length) {
	var newLabel = label;

	if (label && label.length > length) {
		newLabel = label.substring(0, Math.round(length / 2) - 2).trim();
		newLabel += "...";
		newLabel += label.substring(label.length - Math.round(length / 2) + 1).trim();
	}

	return newLabel;
}

function initPercentChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");
	
	if (chartContainer.data("status") != "to-init") return;

	chartContainer.data("status", "in-construction");

	var config = {
		type: 'pie',
		data: {
			datasets: [{
				data: [
				],
				label: ''
			}],
			labels: [
			]
		},
		options: {
			rotation:	-0.25 * Math.PI	,
			responsive: true,
			legend: {
            	display: false
			},
			tooltips: {
	            // Disable the on-canvas tooltip
	            enabled: false,
				callbacks: {
	                label: function(tooltipItem, data) {
	                    var label = data.labels[tooltipItem.index] || '';
	                    label = shortenLabel(label, 20);

	                    if (label) {
	                    	label += " (";
	                    	label += data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
	                    	label += ") "
	                        label += ': ';
	                    }
	                    
	                    // Compute total
						var total = 0;
						for(var index = 0; index < data.datasets[tooltipItem.datasetIndex].data.length; ++index) {
							total += data.datasets[tooltipItem.datasetIndex].data[index];
						}

	                    label += Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] * 10000 / total) / 100 + "%";
	                    return label;
	                }
	            },
	            custom: function(tooltipModel) {
	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';

	                    document.body.appendChild(tooltipEl);
	                }
	
	                // Hide if no tooltip
	                if (tooltipModel.opacity === 0) {
	                    tooltipEl.style.opacity = 0;
	                    tooltipEl.style.display = "none";
	                    return;
	                }
	
	                // Set caret Position
	                tooltipEl.classList.remove('above', 'below', 'no-transform');
	                if (tooltipModel.yAlign) {
	                    tooltipEl.classList.add(tooltipModel.yAlign);
	                } 
	                else {
	                    tooltipEl.classList.add('no-transform');
	                }
	
	                function getBody(bodyItem) {
	                    return bodyItem.lines;
	                }

	                // Set Text
	                if (tooltipModel.body) {
	                    var titleLines = tooltipModel.title || [];
	                    var bodyLines = tooltipModel.body.map(getBody);
	
	                    var innerHtml = '';

	                    bodyLines.forEach(function(body, i) {
	                        var colors = tooltipModel.labelColors[i];
	                        var coloredSquare = "<span style='display: inline-block; width: 14px; height: 14px; border: 2px solid #f0f0f0; background-color: "+colors.borderColor+"; position: relative; top: 2px;'></span> ";

	                        innerHtml = coloredSquare + body;
	                    });

						tooltipEl.innerHTML = innerHtml;
	                }
	
	                // `this` will be the overall tooltip
	                var position = this._chart.canvas.getBoundingClientRect();
	
	                // Display, position
	                tooltipEl.style.opacity = 0.85;
                    tooltipEl.style.display = "inline";

	                tooltipEl.style.position = 'absolute';
	                tooltipEl.style.left = (position.left - $(tooltipEl).width() - 30 + this._eventPosition.x + $(window).scrollLeft()) + 'px';
	                tooltipEl.style.top = (position.top - 15 + this._eventPosition.y + $(window).scrollTop()) + 'px';

	            }
	        }
		}
	};

	var ctx = chartContainer.find(".chart-area").get(0).getContext('2d');
	var chart = new Chart(ctx, config);

	chartContainer.data("chart", chart);
	chartContainer.data("status", "initialized");
}

function initJMChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");
	
	if (chartContainer.data("status") != "to-init") return;

	chartContainer.data("status", "in-construction");

	var config = {
		type: 'bar',
		data: {
			datasets: [{
				data: [
				],
				label: ''
			}],
			labels: [
			]
		},
		options: {
			responsive: true,
			legend: {
            	display: false
			},
			scales: {
				xAxes: [{
					stacked: true,
				}],
				yAxes: [{
					stacked: true,
				}]
			},
			tooltips: {
	            // Disable the on-canvas tooltip
	            enabled: false,
				callbacks: {
	                label: function(tooltipItem, data) {
	                    var label = data.labels[tooltipItem.index] || '';
	                    label = shortenLabel(label, 20);

	                    if (label) {
	                    	label += " - ";
	                    	label += data.datasets[tooltipItem.datasetIndex].label;
	                    	label += " "
	                        label += ': ';
	                    }

						label += Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] * 100) / 100 + "%";

						var total = 0;
						for(var index = 0; index <= tooltipItem.datasetIndex; ++index) {
							total += data.datasets[index].data[tooltipItem.index];
						}

						label += " - ";
						label += Math.round(total * 100) / 100 + "%";

	                    return label;
	                }
	            },
	            custom: function(tooltipModel) {
	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';

	                    document.body.appendChild(tooltipEl);
	                }
	
	                // Hide if no tooltip
	                if (tooltipModel.opacity === 0) {
	                    tooltipEl.style.opacity = 0;
	                    tooltipEl.style.display = "none";
	                    return;
	                }
	
	                // Set caret Position
	                tooltipEl.classList.remove('above', 'below', 'no-transform');
	                if (tooltipModel.yAlign) {
	                    tooltipEl.classList.add(tooltipModel.yAlign);
	                } 
	                else {
	                    tooltipEl.classList.add('no-transform');
	                }

	                function getBody(bodyItem) {
	                    return bodyItem.lines;
	                }

	                // Set Text
	                if (tooltipModel.body) {
	                    var titleLines = tooltipModel.title || [];
	                    var bodyLines = tooltipModel.body.map(getBody);
	
	                    var innerHtml = '';

	                    bodyLines.forEach(function(body, i) {
	                        var colors = tooltipModel.labelColors[i];
	                        var coloredSquare = "<span style='display: inline-block; width: 14px; height: 14px; border: 2px solid #f0f0f0; background-color: "+colors.borderColor+"; position: relative; top: 2px;'></span> ";

	                        innerHtml = coloredSquare + body;
	                    });

						tooltipEl.innerHTML = innerHtml;
	                }
	
	                // `this` will be the overall tooltip
	                var position = this._chart.canvas.getBoundingClientRect();
	
	                // Display, position
	                tooltipEl.style.opacity = 0.85;
                    tooltipEl.style.display = "inline";

	                tooltipEl.style.position = 'absolute';
	                tooltipEl.style.left = (position.left - $(tooltipEl).width() - 30 + this._eventPosition.x + $(window).scrollLeft()) + 'px';
	                tooltipEl.style.top = (position.top - 15 + this._eventPosition.y + $(window).scrollTop()) + 'px';

	            }
	        }
		}
	};

	var ctx = chartContainer.find(".chart-area").get(0).getContext('2d');
	var chart = new Chart(ctx, config);

	chartContainer.data("chart", chart);
	chartContainer.data("status", "initialized");
}

function initBordaChart(motionContainer) {
	var chartContainer = motionContainer.find(".motion-charts");

	if (chartContainer.data("status") != "to-init") return;
	chartContainer.data("status", "in-construction");

	var config = {
		type: 'bar',
		data: {
			datasets: [{
				data: [
				],
				label: ''
			}],
			labels: [
			]
		},
		options: {
			responsive: true,
			legend: {
            	display: false
			},
			scales: {
				xAxes: [{
					stacked: false,
				}],
				yAxes: [{
					stacked: false,
					ticks: {
						min: 0,
					}
				}]
			},
			tooltips: {
	            // Disable the on-canvas tooltip
	            enabled: false,
				callbacks: {
	                label: function(tooltipItem, data) {
	                    var label = data.labels[tooltipItem.index] || '';
	                    label = shortenLabel(label, 20);

	                    if (label) {
	                        label += ': ';
	                    }

						label += Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] * 100) / 100;

	                    return label;
	                }
	            },
	            custom: function(tooltipModel) {
	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';

	                    document.body.appendChild(tooltipEl);
	                }
	
	                // Hide if no tooltip
	                if (tooltipModel.opacity === 0) {
	                    tooltipEl.style.opacity = 0;
	                    tooltipEl.style.display = "none";
	                    return;
	                }
	
	                // Set caret Position
	                tooltipEl.classList.remove('above', 'below', 'no-transform');
	                if (tooltipModel.yAlign) {
	                    tooltipEl.classList.add(tooltipModel.yAlign);
	                } 
	                else {
	                    tooltipEl.classList.add('no-transform');
	                }
	
	                function getBody(bodyItem) {
	                    return bodyItem.lines;
	                }

	                // Set Text
	                if (tooltipModel.body) {
	                    var titleLines = tooltipModel.title || [];
	                    var bodyLines = tooltipModel.body.map(getBody);
	
	                    var innerHtml = '';

	                    bodyLines.forEach(function(body, i) {
	                        var colors = tooltipModel.labelColors[i];
	                        var coloredSquare = "<span style='display: inline-block; width: 14px; height: 14px; border: 2px solid #f0f0f0; background-color: "+colors.borderColor+"; position: relative; top: 2px;'></span> ";

	                        innerHtml = coloredSquare + body;
	                    });

						tooltipEl.innerHTML = innerHtml;
	                }
	
	                // `this` will be the overall tooltip
	                var position = this._chart.canvas.getBoundingClientRect();
	
	                // Display, position
	                tooltipEl.style.opacity = 0.85;
                    tooltipEl.style.display = "inline";

	                tooltipEl.style.position = 'absolute';
	                tooltipEl.style.left = (position.left - $(tooltipEl).width() - 30 + this._eventPosition.x + $(window).scrollLeft()) + 'px';
	                tooltipEl.style.top = (position.top - 15 + this._eventPosition.y + $(window).scrollTop()) + 'px';

	            }
	        }
		}
	};

	var ctx = chartContainer.find(".chart-area").get(0).getContext('2d');
	var chart = new Chart(ctx, config);

	chartContainer.data("chart", chart);
	chartContainer.data("status", "initialized");
}

function showSpeakingStats(event) {
	
	var width = 568;
	var height = 400;

	var dialog = $("<div id='speaking-charts-container'><canvas class='chart-area'></canvas></div>");
	dialog.css({height: height + "px", width: width + "px"});
	dialog.find("canvas").css({height: height + "px", width: width + "px"});

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

	var datasetData = [];
	var labels = [];
	var backgroundColors = [];
	var borderWidths = [];
	var borderColors = [];
	
	var index = 0;
	for(var nickname in speakingStats.speakingTimePerPerson) {
		labels.push(nickname);
		datasetData.push(speakingStats.speakingTimePerPerson[nickname]);
		borderWidths.push(2);
		
		var color = chartColors[index % chartColors.length];

		backgroundColors.push(color.replace("opacity", 0.3));
		borderColors.push(color.replace("opacity", 1));
		index++;
	}
	
	var config = {
		type: 'pie',
		data: {
			datasets: [{
				data: datasetData,
				backgroundColor: backgroundColors,
				borderWidth: borderWidths,
				borderColor: borderColors,
				label: ''
			}],
		
			labels: labels
		},
		options: {
			rotation:	-0.25 * Math.PI	,
			responsive: true,
			legend: {
            	display: false
			},
			tooltips: {
	            // Disable the on-canvas tooltip
	            enabled: false,
				callbacks: {
	                label: function(tooltipItem, data) {
	                    var label = data.labels[tooltipItem.index] || '';
	
	                    if (label) {
	                    	label += " (";
	                    	label += computeTimeString(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
	                    	label += ") "
	                        label += ': ';
	                    }
	                    
	                    // Compute total
						var total = 0;
						for(var index = 0; index < data.datasets[tooltipItem.datasetIndex].data.length; ++index) {
							total += data.datasets[tooltipItem.datasetIndex].data[index];
						}

	                    label += Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] * 10000 / total) / 100 + "%";
	                    return label;
	                }
	            },
	            custom: function(tooltipModel) {
	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';

	                    document.body.appendChild(tooltipEl);
	                }
	
	                // Hide if no tooltip
	                if (tooltipModel.opacity === 0) {
	                    tooltipEl.style.opacity = 0;
	                    tooltipEl.style.display = "none";
	                    return;
	                }
	
	                // Set caret Position
	                tooltipEl.classList.remove('above', 'below', 'no-transform');
	                if (tooltipModel.yAlign) {
	                    tooltipEl.classList.add(tooltipModel.yAlign);
	                } 
	                else {
	                    tooltipEl.classList.add('no-transform');
	                }
	
	                function getBody(bodyItem) {
	                    return bodyItem.lines;
	                }

	                // Set Text
	                if (tooltipModel.body) {
	                    var titleLines = tooltipModel.title || [];
	                    var bodyLines = tooltipModel.body.map(getBody);
	
	                    var innerHtml = '';

	                    bodyLines.forEach(function(body, i) {
	                        var colors = tooltipModel.labelColors[i];
	                        var coloredSquare = "<span style='display: inline-block; width: 14px; height: 14px; border: 2px solid #f0f0f0; background-color: "+colors.borderColor+"; position: relative; top: 2px;'></span> ";

	                        innerHtml = coloredSquare + body;
	                    });

						tooltipEl.innerHTML = innerHtml;
	                }
	
	                // `this` will be the overall tooltip
	                var position = this._chart.canvas.getBoundingClientRect();
	
	                // Display, position
	                tooltipEl.style.opacity = 0.85;
                    tooltipEl.style.display = "inline";

	                tooltipEl.style.position = 'absolute';
	                tooltipEl.style.left = (position.left - $(tooltipEl).width() - 30 + this._eventPosition.x + $(window).scrollLeft()) + 'px';
	                tooltipEl.style.top = (position.top - 15 + this._eventPosition.y + $(window).scrollTop()) + 'px';

	            }
	        }
		}
	};

	var ctx = dialog.find(".chart-area").get(0).getContext('2d');
	var chart = new Chart(ctx, config);	
}

function showMotionDelegations(event) {

	var textColor = "black";
	if ($("body").css("backgroundColor") == "rgb(39, 43, 48)") {
		textColor = "white";
	}

	var motion = $(this).parents(".motion");
	var powers = motion.data("delegation-powers");

//	var width = 568;
	var width = 858;
	var height = 600;

	var dialog = $("<div id='motion-delegations-dialog'></div>");
	dialog.css({height: height + "px", width: width + "px"});

	var container = dialog;

	bootbox.dialog({
	    title: motionDelegationsTitle,
	    message: dialog,
	    size: "large",
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
	
    var graph = new joint.dia.Graph;

    var paper = new joint.dia.Paper({
        el: container.get(0),
        model: graph,
        width: width,
        height: height,
        background: {
		   color: "rgba(127, 127, 127, 0)",
        },
        gridSize: 1
    });

	var brightnessFilter = {
                name: 'brightness',
                args: {
                    amount: 0.1
                }
            };

	var hideAllElements = function() {
		var cells = graph.getCells();
		for(var index = 0; index < cells.length; ++index) {
			var cell = cells[index];

			cell.attr('./display', 'none');
		}
	}

	var showAllElements = function() {
		var cells = graph.getCells();
		for(var index = 0; index < cells.length; ++index) {
			var cell = cells[index];

			cell.attr('./display', null);
		}
	}

	var showPreviousMembers = function(memberElement, depth) {
		var links = graph.getConnectedLinks(memberElement, { inbound: true });
		var sourceDepth = depth - 1;

		for(var index = 0; index < links.length; ++index) {
			var link = links[index];
			link.attr('./display', null);
			var sourceElement = link.getSourceElement();
			sourceElement.attr('./display', null);

			if (sourceDepth != 0) {
				showPreviousMembers(sourceElement, sourceDepth);
			}
		}
	}

	var showNextMembers = function(memberElement, depth) {
		var links = graph.getConnectedLinks(memberElement, { outbound: true });
		var targetDepth = depth - 1;

		for(var index = 0; index < links.length; ++index) {
			var link = links[index];
			link.attr('./display', null);
			var targetElement = link.getTargetElement();
			targetElement.attr('./display', null);

			if (targetDepth != 0) {
				showNextMembers(targetElement, targetDepth);
			}
		}
	}

	paper.on('element:pointerclick', function(cellView, evt, x, y) {
		var memberElement = cellView.model;
		
		hideAllElements();

		memberElement.attr('./display', null);

		showPreviousMembers(memberElement, 0);
		showNextMembers(memberElement, 0);

	});

	paper.on('blank:pointerclick', function(evt, x, y) {
		showAllElements();
	});

	joint.shapes.standard.Rectangle.define('congressus.MemberRectangle', {
	    attrs: {
	        body: {
	            refWidth: '100%',
	            refHeight: '100%',
	            rx: 10, // add a corner radius
	            ry: 10,
	            strokeWidth: 1,
	            fill: 'cornflowerblue'
	        },
	        label: {
//	            textAnchor: 'left', // align text to left
//	            refY: -2,
//	            refX: 10, // offset text from right edge of model bbox
	            fill: textColor,
	            fontSize: 14
	        }
	    }
	}, {
	    // inherit joint.shapes.standard.Rectangle.markup
	}, {
	    init: function() {
	
	        var rectangle = new this();

	        var fill = 'hsla(300, 70%, 70%, 0.3)';
	        var stroke = 'hsla(300, 70%, 70%, 1)';


	        rectangle.attr({
	            body: {
	                fill: fill,
	                stroke: stroke
	            },
	            label: { // ensure visibility on dark backgrounds
	                fill: textColor
	            }
	        });

	        return rectangle;
	    }
	});

	joint.shapes.standard.Link.define('congressus.DelegationLink', {
	    attrs: {
	        line: {
	            stroke: '#000000',
	            strokeWidth: 1,
	            targetMarker: {
	                'type': 'path',
	                'd': 'M 10 -5 0 0 10 5 z'
	            }
	        }
	    },
	    defaultLabel: {
	        markup: [
	            {
	                tagName: 'rect',
	                selector: 'body'
	            }, {
	                tagName: 'text',
	                selector: 'label'
	            }
	        ],
	        attrs: {
	            label: {
	                fill: textColor, // default text color
//	                stroke: 'white',
	                fontSize: 12,
	                textAnchor: 'middle',
	                yAlignment: 'middle',
	                pointerEvents: 'none'
	            },
	            body: {
//	                ref: 'label',
	                fill: 'white',
	                stroke: 'cornflowerblue',
	                strokeWidth: 2,
	                refWidth: '120%',
	                refHeight: '120%',
	                refX: '-10%',
	                refY: '-10%'
	            }
	        }
	    }
	}, {
	    // inherit joint.shapes.standard.Link.markup
	}, {
	    init: function() {
	
	        var link = new this();

	        var fill = 'hsla(300, 70%, 70%, 0.3)';
	        var stroke = 'hsla(300, 70%, 70%, 1)';
	
	        link.prop('attrs/line/stroke', stroke);
	        link.prop('defaultLabel/attrs/body/stroke', stroke);
	        link.prop('defaultLabel/attrs/body/fill', fill);
	
	        return link;
	    }
	});	

    var memberShapeElementSource = joint.shapes.congressus.MemberRectangle.init();
	var levelOffsets = {};
	var memberElements = {};

	var nbMembers = 0;
	for(var memberId in powers) {
	
		var power = powers[memberId];
		
		if (power.delegation_level == 1 && power.power == 100) continue; // TODO change 100 to proper normal power
		
		nbMembers++;
	}

	var memberIndex = 0;
	for(var memberId in powers) {
	
		var power = powers[memberId];
		
		if (power.delegation_level == 1 && power.power == 100) continue; // TODO change 100 to proper normal power

		// Queued ditribution
		if (!levelOffsets[power.delegation_level]) {
			levelOffsets[power.delegation_level] = 0;
		}
		var position = {x: (power.delegation_level - 1) * 300 + 10, y: levelOffsets[power.delegation_level] * 30 + 10};
		levelOffsets[power.delegation_level]++;

		// Circular distribution
//		var position = {x: (1 + Math.cos(memberIndex / nbMembers * 2 * Math.PI)) * (width - 220) / 2 + 5, y: (1 + Math.sin(memberIndex / nbMembers * 2 * Math.PI)) * (height - 26) / 2 + 5};
		memberIndex++;

		var pseudo = power.nickname;

	    var memberShapeElement = memberShapeElementSource.clone();
		    memberShapeElement.position(position.x, position.y);
		    memberShapeElement.resize(200, 16);
		    memberShapeElement.attr('label/text', pseudo + ' : ' + power.power);
		    memberShapeElement.addTo(graph);
		    
		memberElements[memberId] = memberShapeElement;
	}

	for(var memberId in powers) {
	
		var power = powers[memberId];
		if (!power.givers) continue;
		
		for(var fromMemberId in power.givers) {
			var givenPower = power.givers[fromMemberId].given_power;

		    var link = joint.shapes.congressus.DelegationLink.init();
		    link.source(memberElements[fromMemberId]);
		    link.target(memberElements[memberId]);
		    link.appendLabel({
			        attrs: {
			            label: {
			                text: givenPower
			            }
			        }
		    });

/*
		    link.appendLabel({
    markup: [
        {
            tagName: 'circle',
            selector: 'body'
        }, {
            tagName: 'text',
            selector: 'label'
        }, {
            tagName: 'circle',
            selector: 'asteriskBody'
        }, {
            tagName: 'text',
            selector: 'asterisk'
        }
    ],
    attrs: {
        label: {
            text: '½',
            fill: '#000000',
            fontSize: 14,
            textAnchor: 'middle',
            yAlignment: 'middle',
            pointerEvents: 'none'
        },
        body: {
            ref: 'label',
            fill: '#ffffff',
            stroke: '#000000',
            strokeWidth: 1,
            refR: 1,
            refCx: 0,
            refCy: 0,
            refWidth: '120%',
            refHeight: '120%',
        },
        asterisk: {
            ref: 'label',
            text: '＊',
            fill: '#ff0000',
            fontSize: 8,
            textAnchor: 'middle',
            yAlignment: 'middle',
            pointerEvents: 'none',
            refX: 16.5,
            refY: -2
        },
        asteriskBody: {
            ref: 'asterisk',
            fill: '#ffffff',
            stroke: '#000000',
            strokeWidth: 1,
            refR: 1,
            refCx: '50%',
            refCy: '50%',
            refX: 0,
            refY: 0
        }
    }
});
*/
		    link.addTo(graph);
		    link.toBack();
		}
	}
/*
	for(var memberId in powers) {
	
		var power = powers[memberId];
		
		if (power.delegation_level == 1 && power.power == 100) continue; // TODO change 100 to proper normal power

		if (!levelOffsets[power.delegation_level]) {
			levelOffsets[power.delegation_level] = 0;
		}

		var position = {x: (power.delegation_level - 1) * 300 + 10, y: levelOffsets[power.delegation_level] * 30 + 10};
		levelOffsets[power.delegation_level]++;

	    var memberShapeElement = memberElements[memberId];
		    memberShapeElement.position(position.x, position.y);
		    memberShapeElement.resize(200, 16);
	}
*/
	setTimeout(showAllElements, 1000);
//	showAllElements();
}

$(function() {
	$(".btn-see-speaking-stats").click(showSpeakingStats);
	$("#agenda_point ul.objects").on("click", ".btn-see-motion-delegations", showMotionDelegations);

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
});
