<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate
    
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

class GroupSourceFactory {

    static function getInstance($source) {
        if (strtolower($source) == "galettegroups") return new GaletteGroupSource();
        else if (strtolower($source) == "personaegroups") return new PersonaeGroupSource();
        else if (strtolower($source) == "personaethemes") return new PersonaeThemeSource();
        
        return null;
    }

    static function fixPeople(&$people, $ping, $now) {
		$lastPing = new DateTime($ping["pin_datetime"]);

		$diff = $now->getTimestamp() -  $lastPing->getTimestamp();

		if ($diff < 60) {
			$people["mem_connected"] = true;
		}
		else {
			$people["mem_connected"] = false;
		}

		$people["mem_present"] = ($ping["pin_first_presence_datetime"] ? 1 : 0);

		$people["mem_speaking"] = $ping["pin_speaking"];
		$people["mem_speaking_request"] = $ping["pin_speaking_request"];
    }

    static function fixPing(&$pings, &$usedPings, &$people, $member, $now) {

		$found = false;
		// Search into $pings
		foreach($pings as $index => $ping) {
			if ($ping["pin_member_id"] == $member["id_adh"]) {

                // Set it @ found
				$found = true;

                GroupSourceFactory::fixPeople($people, $ping, $now);

                // Move ping into usedPings
				$usedPings[] = $ping;
				unset($pings[$index]);

				break;
			}
		}
		
		// If not found
		if (!$found) {
    		// Search into $usedPings
			foreach($usedPings as $index => $ping) {
				if ($ping["pin_member_id"] == $member["id_adh"]) {

                    // Set it @ found
					$found = true;

                    GroupSourceFactory::fixPeople($people, $ping, $now);

    				break;
				}
			}
		}

    }

}

?>