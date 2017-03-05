<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
include_once("header.php");

require_once("engine/bo/SearchBo.php");

$searchBo = SearchBo::newInstance($connection, $config);

$lines = $searchBo-> search(array("query" => $_REQUEST["query"]));

?>

<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_search"); ?></li>
	</ol>

	<!--
	<div id="tree-results" class="">
	</div>
	 -->

	<table class="table">
		<thead>
			<tr>
				<th style="width: 30%;">Nom de la réunion</th>
				<th style="width: 170px;">Date</th>
				<th style="width: 15%;">Type</th>
				<th>Texte</th>
			</tr>
		</thead>
		<tbody>

<?php foreach($lines as $line) {
		$url = "meeting.php?id=". $line["mee_id"] . "#";
?>
			<tr>
				<td><a href="<?php echo $url; ?>"><?php echo $line["mee_label"]; ?></a></td>
				<td>
					<?php
						$start = new DateTime($line["mee_datetime"]);

						$datetime = lang("datetime_format");
						$datetime = str_replace("{date}", $start->format(lang("date_format")), $datetime);
						$datetime = str_replace("{time}", $start->format(lang("time_format")), $datetime);

						echo $datetime;
					?>
				</td>
				<td>
					<?php

						$object = json_decode($line["object"], true);

//						echo $object[count($object) - 1]["type"];

						switch($object[count($object) - 1]["type"]) {
							default:
							case "meeting":
								$typeLabel = "Réunion";
								break;
							case "agenda":
								$typeLabel = "Agenda";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|";
								break;
							case "chat":
								$typeLabel = "Chat";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|chat-" . $line["cha_id"];
								break;
							case "conclusion":
								$typeLabel = "Conclusion";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|conclusion-" . $line["con_id"];
								break;
							case "motion":
								$typeLabel = "Motion";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|motion-" . $line["mot_id"];
								break;
							case "proposition":
								$typeLabel = "Proposition de motion";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|motion-" . $line["mot_id"];
								$url .= "|proposition-" . $line["mpr_id"];
								break;
							case "task":
								$typeLabel = "Tâche";
								$url .= "agenda-" . $line["age_id"];
								$url .= "|task-" . $line["tas_id"];
								break;
						}
					?>
					<a href="<?php echo $url; ?>"><?php echo $typeLabel; ?></a>
				</td>
				<td class="text-search">
					<?php 	echo $line["text"];
							if (isset($line["text2"])) {
								echo "<hr />";
								echo $line["text2"];
							}?>
				</td>
				<td></td>
			</tr>
<?php }?>

		</tbody>
	</table>

</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>

<?php

$root = array();

foreach($lines as $line) {
	$line["object"] = json_decode($line["object"], true);
	$object = $line["object"];

	$indexes = array();

	foreach($object as $level => $info) {
		switch($level) {
			case 0:
				$findex = -1;
				foreach($root as $index => $level0) {
					if ($level0["id"] == $object[0]["type"] . $object[0]["id"]) {
						$findex = $index;
						break;
					}
				}
				if ($findex == -1) {
					$findex = count($root);
					$root[] = array("id" => $object[0]["type"] . $object[0]["id"], "text" => $line["mee_label"], "nodes" => array(), "href" => "meeting.php?id=" . $line["mee_id"]);
				}

				$indexes[0] = $findex;
				break;
			case 1:
				$findex = -1;
				foreach($root[$indexes[0]]["nodes"] as $index => $level1) {
					if ($level1["id"] == $object[1]["type"] . $object[1]["id"]) {
						$findex = $index;
						break;
					}
				}
				if ($findex == -1) {
					$findex = count($root[$indexes[0]]["nodes"]);
					$root[$indexes[0]]["nodes"][] = array("id" => $object[1]["type"] . $object[1]["id"], "text" => $line["age_label"], "nodes" => array());
				}

				$indexes[1] = $findex;
				break;
			case 2:
				$findex = -1;
				foreach($root[$indexes[0]]["nodes"][$indexes[1]]["nodes"] as $index => $level2) {
					if ($level2["id"] == $object[2]["type"] . $object[2]["id"]) {
						$findex = $index;
						break;
					}
				}
				if ($findex == -1) {
					$findex = count($root[$indexes[0]]["nodes"][$indexes[1]]["nodes"]);

					switch($object[2]["type"]) {
						case "chat":
							$text = $line["cha_text"];
							break;
						case "conclusion":
							$text = $line["con_text"];
							break;
						case "motion":
							$text = $line["mot_title"];
							break;
						case "task":
							$text = $line["tas_text"];
							break;
					}

					$root[$indexes[0]]["nodes"][$indexes[1]]["nodes"][] = array("id" => $object[2]["type"] . $object[2]["id"], "text" => $text, "nodes" => array());
				}

				$indexes[2] = $findex;
				break;
			case 3:
				$findex = -1;
				foreach($root[$indexes[0]]["nodes"][$indexes[1]]["nodes"][$indexes[2]]["nodes"] as $index => $level3) {
					if ($level3["id"] == $object[3]["type"] . $object[3]["id"]) {
						$findex = $index;
						break;
					}
				}
				if ($findex == -1) {
					$findex = count($root[$indexes[0]]["nodes"][$indexes[1]]["nodes"][$indexes[2]]["nodes"]);

					switch($object[3]["type"]) {
						case "proposition":
//							print_r($line);
							$text = $line["mpr_label"];
							break;
					}

					$root[$indexes[0]]["nodes"][$indexes[1]]["nodes"][$indexes[2]]["nodes"][] = array("id" => $object[3]["type"] . $object[3]["id"], "text" => $text, "nodes" => array());
				}

				$indexes[3] = $findex;
				break;
		}
	}
}

?>

<script>
var query = <?php echo json_encode($_REQUEST["query"]); ?>;
var data = <?php echo json_encode($root); ?>;

$('#tree-results').treeview({
    levels: 99,
    enableLinks: true,
    data: data
  });
</script>

</body>
</html>