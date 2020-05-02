<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

require_once("engine/converters/html2md/Parser.php");
require_once("engine/converters/html2md/Converter.php");
require_once("engine/converters/html2md/ConverterExtra.php");

class WritingProgramMotionHook implements WinningMotionHook {

    function doHook($agenda, $motion, $proposition, $chats) {

        if (strtolower($proposition["mpr_label"]) != "oui" && strtolower($proposition["mpr_label"]) != "pour") return "did nothing";
    
        $json = array("STATUT" => "voté", "DATE" => "", "TAGS" => array(), "SCORE" => "0");

        $isProgram = false;
        foreach($motion["mot_tags"] as $tag) {
            if (strtolower($tag["tag_label"]) == "programme") {
                $isProgram = true;
            }
            
            if (strpos(strtolower($tag["tag_label"]), "tag - ") !== false) {
                $json["TAGS"][] = substr($tag["tag_label"], 6);
            }
        }

        if (!$isProgram) return "not a program point";

        if (strtolower($proposition["mpr_label"]) != "oui" && strtolower($proposition["mpr_label"]) != "pour") return "not a yes";

        $json["SCORE"] = round($proposition["proportion_power"] * 100, 2) . "%";

        $description = $agenda["age_description"];

        $point = "# " . $agenda["age_label"] . "\n";
        $point .= "\n";
        $point .= "## Exposé des motifs\n";

		$ageObjects = json_decode($agenda["age_objects"], true);
		foreach($ageObjects as $ageObject) {
			if (isset($ageObject["chatId"])) {
			    foreach($chats as $chat) {
			        if ($chat["cha_id"] == $ageObject["chatId"]) {
                        $point .= "\n";
                        $point .= $chat["cha_text"];
                        $point .= "\n";
                        break;
                        break;
			        }
			    }
			}
		}

        $converter = new Markdownify\Converter;

        $point .= "\n";
        $point .= "## Description\n";
        $point .= "\n";
        $point .= $converter->parseString($agenda["age_description"]);
        $point .= "\n";

        $path = "/usr/share/nginx/html/test/";
        $filename = strtolower($agenda["age_label"]);

        // first level
        foreach($motion["mot_tags"] as $tag) {
            if ((strpos(strtolower($tag["tag_label"]), "programme ") !== false) && (strpos(strtolower($tag["tag_label"]), "programme - ") === false)) {
                $path .= strtolower($tag["tag_label"]) . "/";
                break;
            }
        }

        // second level
        foreach($motion["mot_tags"] as $tag) {
            if (strpos(strtolower($tag["tag_label"]), "programme - ") !== false) {
                $path .= str_replace("programme - ", "", strtolower($tag["tag_label"])) . "/";
                break;
            }
        }

        $from = array(" ", ", ", ",", "à", "â", "é", "è", "ê", "ù", "ô", "î");
        $to   = array("_", ""  , "" , "a", "a", "e", "e", "e", "u", "o", "i");

        $path     = str_replace($from, $to, $path);
        $filename = str_replace($from, $to, $filename);

        if (!file_exists($path)) mkdir($path, 0777, true);

        $mdpath = $path . $filename . ".md";
        $jsonpath = $path . $filename . ".json";

        $result = file_put_contents($mdpath, $point);
        $result = file_put_contents($jsonpath, json_encode($json));

        return "writing '" . $proposition["mpr_label"] . "' of the motion '" . $motion["mot_title"] . "' into $path . $filename : " . ($result ? $result : 0) . " bytes written";
    }

}

global $hooks;

if (!$hooks) $hooks = array();

$hooks["wpmh"] = new WritingProgramMotionHook();

?>