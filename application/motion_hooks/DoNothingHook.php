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

class DoNothingHook implements WinningMotionHook {

    function doHook($agenda, $motion, $proposition, $chats) {
        return "did nothing on proposition '" . $proposition["mpr_label"] . "' of the motion '" . $motion["mot_title"] . "'";
    }

}

global $hooks;

if (!$hooks) $hooks = array();

$hooks["dnh"] = new DoNothingHook();

?>