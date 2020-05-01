<?php 

class WritingProgramMotionHook implements WinningMotionHook {

    function doHook($motion, $proposition) {

        if (strtolower($proposition["mpr_label"]) != "oui" && strtolower($proposition["mpr_label"]) != "pour") return "did nothing";

        $mdpath = "/usr/share/nginx/html/test/" . $motion["mot_id"] . ".md";
        $jsonpath = "/usr/share/nginx/html/test/" . $motion["mot_id"] . ".json";
    
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

        $json["SCORE"] = round($proposition["proportion_power"] * 100, 2) . "%";

        $result = file_put_contents($mdpath, json_encode(array("motion" => $motion, "proposition" => $proposition)));
        $result = file_put_contents($jsonpath, json_encode($json));

        return "writing '" . $proposition["mpr_label"] . "' of the motion '" . $motion["mot_title"] . "' into $path : " . ($result ? $result : 0) . " bytes written";
    }

}

global $hooks;

if (!$hooks) $hooks = array();

$hooks["wpmh"] = new WritingProgramMotionHook();

?>