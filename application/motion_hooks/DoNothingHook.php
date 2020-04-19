<?php 

class DoNothingHook implements WinningMotionHook {

    function doHook($motion, $proposition) {
        return "did nothing on proposition '" . $proposition["mpr_label"] . "' of the motion '" . $motion["mot_title"] . "'";
    }

}

global $hooks;

if (!$hooks) $hooks = array();

$hooks["dnh"] = new DoNothingHook();

?>