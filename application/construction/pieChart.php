<?php /*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
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
/*					
					{ value: <?php echo $voteCounters[1]; ?>, color: "#5cb85c", label: '<?php echo $voteCounters[1]; ?>', labelColor: 'blac', labelFontSize: '16' },
					{ value: <?php echo $voteCounters[2]; ?>, color: "#f0ad4e", label: '<?php echo $voteCounters[2]; ?>', labelColor: 'white', labelFontSize: '16' },
					{ value: <?php echo $voteCounters[3]; ?>, color: "#d9534f", label: '<?php echo $voteCounters[3]; ?>', labelColor: 'white', labelFontSize: '16' },
*/					
				],
				backgroundColor: [
	                "hsla(120, 39%, 54%, 0.3)",
	                "hsla(35, 84%, 62%, 0.3)",
	                "hsla(2, 64%, 58%, 0.3)",
				],
				borderWidth: [
					2,
					2,
					2,
				],
				borderColor: [
	                "hsla(120, 39%, 54%, 1)",
	                "hsla(35, 84%, 62%, 1)",
	                "hsla(2, 64%, 58%, 1)",
				],
				label: 'Soutiens'
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
				callbacks: {
	                label: function(tooltipItem, data) {
	                    var label = data.labels[tooltipItem.index] || '';
	
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
//	            	debugger;
//	            	console.log(this);

	                // Tooltip Element
	                var tooltipEl = document.getElementById('chartjs-tooltip');
	
	                // Create element on first render
	                if (!tooltipEl) {
	                    tooltipEl = document.createElement('div');
	                    tooltipEl.id = 'chartjs-tooltip';
//	                    tooltipEl.class='chart-tooltip';
/*
		                tooltipEl.style.borderColor = "#101010";
		                tooltipEl.style.borderStyle = "solid";
		                tooltipEl.style.borderWidth = "1px";
		                tooltipEl.style.borderRadius = "5px";
		                tooltipEl.style.color = "#fff";
		                tooltipEl.style.backgroundColor = "#101010";
		                tooltipEl.style.padding = "5px";
*/
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

	var ctx = document.getElementById('<?php echo $chartId; ?>-chart-area').getContext('2d');
	new Chart(ctx, config);
});
</script>	