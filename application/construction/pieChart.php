<?php /*
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

// Need $voteCounters
// Need $chartId
// Need $width
// Need $height

?>
<canvas id="<?php echo $chartId; ?>-chart-area" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px"></canvas>
<script type="text/javascript">
$(function() {
	var config = {
		type: 'pie',
		data: {
			datasets: [{
				data: [
					<?php echo $voteCounters[1]; ?>,
					<?php echo $voteCounters[2]; ?>,
					<?php echo $voteCounters[3]; ?>,
				],
				backgroundColor: [
	                "#5cb85c",
	                "#f0ad4e",
	                "#d9534f",
				],
				borderWidth: [
					0,
					0,
					0,
				],
				label: 'Dataset 1'
			}],
			labels: [
				<?php echo json_encode(lang("advice_pro", false)); ?>,
				<?php echo json_encode(lang("advice_doubtful", false)); ?>,
				<?php echo json_encode(lang("advice_against", false)); ?>,
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
	            custom: function(tooltipModel) {
//	            	console.log(this);

	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';
	                    
		                tooltipEl.style.borderColor = "#101010";
		                tooltipEl.style.borderStyle = "solid";
		                tooltipEl.style.borderWidth = "1px";
		                tooltipEl.style.borderRadius = "5px";
		                tooltipEl.style.color = "#fff";
		                tooltipEl.style.backgroundColor = "#101010";
		                tooltipEl.style.padding = "5px";

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
	                        var coloredSquare = "<span style='display: inline-block; width: 14px; height: 14px; border: 2px solid #f0f0f0; background-color: "+colors.backgroundColor+"; position: relative; top: 2px;'></span> ";

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
	                tooltipEl.style.left = (position.left - $(tooltipEl).width() - 16 + this._eventPosition.x) + 'px';
	                tooltipEl.style.top = (position.top - 15 + this._eventPosition.y) + 'px';

	            }
	        }
		}
	};

	var ctx = document.getElementById('<?php echo $chartId; ?>-chart-area').getContext('2d');
	new Chart(ctx, config);

	
/*	
	var votingData = [];
	votingData[0] = {indexLabel: <?php echo json_encode(lang("advice_pro")); ?>, y: <?php echo $voteCounters[1]; ?>};
	votingData[1] = {indexLabel: <?php echo json_encode(lang("advice_doubtful")); ?>, y: <?php echo $voteCounters[2]; ?>};
	votingData[2] = {indexLabel: <?php echo json_encode(lang("advice_against")); ?>, y: <?php echo $voteCounters[3]; ?>};

	var chartOptions = {
		colorSet: "adviceColorSet",
		theme: "theme2",
		exportFileName: speakingTimesChartTitle,
		exportEnabled: false,
        animationEnabled: true,
        height: <?php echo $width; ?>,
        width: <?php echo $height; ?>,
        legend: false,
		toolTip: {
			shared: true,
			contentFormatter: function (e) {
				var content = " ";
				content += e.entries[0].dataPoint.indexLabel + " - " + "<strong>" + e.entries[0].dataPoint.y + "</strong>";
				
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
			showInLegend: false,
			legendText: "{indexLabel}",
			dataPoints: votingData
		}
		]
	};
	var chart = new CanvasJS.Chart("<?php echo $chartId; ?>", chartOptions);
	chart.render();	
*/	
});
</script>	