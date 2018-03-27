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
<script type="text/javascript">
$(function() {
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
});
</script>	