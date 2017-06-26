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

/*
function showMotion($motions, $id) {
	$first = true;

	echo "{{motion|title=";

	$winning = "contre";

	foreach($motions as $motion) {
		if ($motion["mot_id"] == $id) {
			if ($first) {
				echo $motion["mot_title"] . "\n";
				echo "|text=" . $motion["mot_description"] . "\n";
				$first = false;
			}

			$explanation = json_decode($motion["mpr_explanation"], true);

			if ($motion["mpr_winning"] == 1) {
				$winning = $motion["mpr_label"];
			}

//			echo $motion["mpr_label"] . "&nbsp;(" . $explanation["power"] . ") : ";
			echo "|";
			echo $motion["mpr_label"] . "=";

			$voteSeparator = " ";
			foreach($explanation["votes"] as $vote) {
				if ($vote["votePower"] == 0) continue;

				echo "$voteSeparator";
				echo $vote["memberLabel"];
				echo " (" . $vote["votePower"] . ")";

				$voteSeparator = ", ";
			}

			echo "\n";
		}
	}

	if (strtolower($winning) == "pour" || strtolower($winning) == "oui") {
		echo "|close=Motion adoptée\n";
		echo "|result=pour\n";
	}
	else {
		echo "|close=Motion rejetée\n";
//		echo "|result=$winning\n";
		echo "|result=contre\n";
	}

	echo "|}}\n\n";
}
*/

function showMotion($motions, $id, &$voters) {
	$first = true;

	echo "{{motion|title=";

	$winning = "contre";

	foreach($motions as $motion) {
		if ($motion["mot_id"] == $id) {
			if ($first) {
				echo $motion["mot_title"] . "\n";
				echo "|text=" . $motion["mot_description"] . "\n";
				$first = false;
			}

			$explanation = json_decode($motion["mpr_explanation"], true);

			if ($motion["mpr_winning"] == 1) {
				$winning = $motion["mpr_label"];
			}

			//			echo $motion["mpr_label"] . "&nbsp;(" . $explanation["power"] . ") : ";

			if (strtolower( $motion["mpr_label"] ) == "pour" || strtolower( $motion["mpr_label"] ) == "oui") {
				echo "|pour=";
			}
			else if (strtolower( $motion["mpr_label"] ) == "contre" || strtolower( $motion["mpr_label"] ) == "non") {
				echo "|contre=";
			}
			else if (strtolower( $motion["mpr_label"] ) == "nspp") {
				echo "|nspp=";
			}
			else {
				echo "|vote=";
				echo $motion["mpr_label"] . " :";
			}

			$voteSeparator = "";
			foreach($explanation["votes"] as $vote) {
				if ($vote["power"] == 0) continue;

				echo "$voteSeparator";
				echo $vote["memberLabel"];
				echo " (";

				if ($motion["mot_win_limit"] == -2) {
					echo $vote["votePower"] . " x " .  lang("motion_majorityJudgment_" . $vote["jm_power"], false, null, "../");
				}
				else {
					echo  $vote["power"];
				}

				echo ")";

				$voteSeparator = ", ";
				$voters[$vote["memberId"]] = $vote["memberLabel"];
			}

			echo "\n";

		}
	}

	if (strtolower($winning) == "pour" || strtolower($winning) == "oui") {
		echo "|close=Motion adoptée\n";
		echo "|result=pour\n";
	}
	else if ($motion["mot_win_limit"] == -2) {
		echo "|close=Motion adoptée\n";
		
		foreach($motions as $motion) {
			if ($motion["mot_id"] == $id && $motion["mpr_winning"] == 1) {
				echo "|result=" . $motion["mpr_label"] . " (" . lang("motion_majorityJudgment_" . $explanation["jm_winning"], false, null, "../") . ", " . $explanation["jm_percent"] . "%" .")\n";
				
				break;
			}
		}
	}
	else if (strtolower($winning) == "contre" || strtolower($winning) == "non") {
		echo "|close=Motion rejetée\n";
		echo "|result=contre\n";
	}
	else {
		echo "|close=Motion adoptée\n";
		
		foreach($motions as $motion) {
			if ($motion["mot_id"] == $id && $motion["mpr_winning"] == 1) {
				echo "|result=" . $motion["mpr_label"] . "\n";
				
				break;
			}
		}
	}

	echo "|}}\n\n";
}

function showChat($chats, $id) {
	foreach($chats as $chat) {
		if ($chat["cha_id"] == $id) {
//					print_r($chat);

			echo ":";

			if ($chat["cha_member_id"]) {
				if ($chat["pseudo_adh"]) {
					echo htmlspecialchars(utf8_encode($chat["pseudo_adh"]), ENT_SUBSTITUTE);
				}
				else {
					echo htmlspecialchars(utf8_encode($chat["nom_adh"]), ENT_SUBSTITUTE);
					echo " ";
					echo htmlspecialchars(utf8_encode($chat["prenom_adh"]), ENT_SUBSTITUTE);
				}
			}
			else {
				echo "Guest";
			}

			echo ": ";

			echo str_replace("\n", "\n\n", $chat["cha_text"]) . "\n";

			return;
		}
	}
}

function showConclusion($conclusions, $id) {
	foreach($conclusions as $conclusion) {
		if ($conclusion["con_id"] == $id) {
//			print_r($conclusion);
			echo "{{CC|" . $conclusion["con_text"] . "}}\n";

			return;
		}
	}
}

function showLevel($agendas, $level, $parent, &$voters) {
	foreach($agendas as $agenda) {
		if ($agenda["age_parent_id"] == $parent) {
			echo "\n";
			echo str_repeat("=", $level);
			echo $agenda["age_label"];
			echo str_repeat("=", $level);
			echo "\n\n";

			$descriptions = explode("\n", $agenda["age_description"]);
			foreach($descriptions as $index => $description) {
				$descriptions[$index] = trim($description);
			}
			$descriptions = implode(" ", $descriptions);

			echo $descriptions ."\n\n";

//			print_r($agenda["age_objects"]);

			foreach($agenda["age_objects"] as $object) {
				if (isset($object["conclusionId"])) {
					showConclusion($agenda["conclusions"], $object["conclusionId"]);
				}
				else if (isset($object["chatId"])) {
					showChat($agenda["chats"], $object["chatId"]);
				}
				else if (isset($object["motionId"])) {
					showMotion($agenda["motions"], $object["motionId"], $voters);
				}
			}

			showLevel($agendas, $level + 1, $agenda["age_id"], $voters);
		}
	}
}

?>
=<?php echo $meeting["mee_label"]; ?>=

==Convoqués==

<?php

//print_r($notices);

foreach ($notices as $notice) {

	echo "* " . $notice["not_label"] . " : \n";

	$presentPowers = 0;
	$powers = 0;

	if (isset($notice["not_people"])) {

		$separator = " ";
		foreach($notice["not_people"] as $people) {

			if ($people["mem_voting"] == 1) {
				$powers += $people["mem_power"];
			}
			
			if ($people["mem_present"] != 1) continue;

			echo "** ";
			echo $people["mem_nickname"];
			
			if ($people["mem_voting"] == 1) {
				$presentPowers += $people["mem_power"];
				echo " (";
				echo $people["mem_power"];
				echo ")";
			}
			echo "\n";
		}

		foreach($notice["not_children"] as $child_notice) {
			echo "** " . $child_notice["not_label"] . " : \n";
				
			$separator = " ";
			
			$child_presentPowers = 0;
			$child_powers = 0;
				
			foreach($child_notice["not_people"] as $people) {
	
				if ($people["mem_voting"] == 1) {
					$powers += $people["mem_power"];
					$child_powers += $people["mem_power"];
				}
				
				if ($people["mem_present"] != 1) continue;
				
				echo "*** ";
				echo $people["mem_nickname"];
	
				if ($people["mem_voting"] == 1) {
					$presentPowers += $people["mem_power"];
					$child_presentPowers += $people["mem_power"];

					echo " (";
					echo $people["mem_power"];
					echo ")";
				}
				echo "\n";
			}

			if ($child_presentPowers) {
				echo "**** ";
				echo $child_presentPowers . "/" . $child_powers;
				echo "\n";
			}
		}
		
		if ($presentPowers) {
			echo "*** ";
			echo $presentPowers . "/" . $powers;
			echo "\n";
		}
	}

}
?>

==Absents==

<?php

//print_r($notices);

foreach ($notices as $notice) {

	echo "* " . $notice["not_label"] . " : \n";

	if (isset($notice["not_people"])) {

		$separator = " ";
		foreach($notice["not_people"] as $people) {

			if ($people["mem_present"] != 0) continue;

			echo "** ";
			echo $people["mem_nickname"];
			echo "\n";
		}

		foreach($notice["not_children"] as $child_notice) {
			echo "** " . $child_notice["not_label"] . " : \n";
				
			$separator = " ";
			
			foreach($child_notice["not_people"] as $people) {
	
				if ($people["mem_present"] != 0) continue;
				
				echo "*** ";
				echo $people["mem_nickname"];
				echo "\n";
			}
		}
	}

}

$voters = array();
?>

<?php showLevel($agendas, 2, null , $voters); ?>

<?php
if (count($voters)) {
?>
==Ayant participé à un vote==

<?php 
	foreach($voters as $memberId => $memberLabel) {
?>
* <?php echo $memberId . " - " . $memberLabel; ?>

<?php 
	}
}
?>
