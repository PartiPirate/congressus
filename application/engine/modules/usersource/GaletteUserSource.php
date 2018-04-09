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

class GaletteUserSource {
    
    function upgradeQuery(&$queryBuilder, $config, $joinField, $as = null) {
        $galetteDatabase = "";

        if (isset($config["galette"]["db"]) && $config["galette"]["db"]) {
            $galetteDatabase = $config["galette"]["db"];
            $galetteDatabase .= ".";
        }

		$queryBuilder->addSelect(($as ? $as : "galette_adherents") . ".*");
//		$queryBuilder->addSelect("gp" . ".picture");

		$queryBuilder->join($galetteDatabase . "galette_adherents", ($as ? $as . "." : "")."id_adh = $joinField", $as, "left");
		

		$queryBuilder->addSelect(($as ? "diaf_$as" : "diaf") . ".field_val as discord_id_adh");
		$queryBuilder->join($galetteDatabase . "galette_dynamic_fields", ($as ? "diaf_$as" : "diaf").".item_id = $joinField AND ".($as ? "diaf_$as" : "diaf").".field_id = 2 AND ".($as ? "diaf_$as" : "diaf").".field_form = 'adh'", ($as ? "diaf_$as" : "diaf"), "left");
//		$queryBuilder->join($galetteDatabase . "galette_pictures", ($as ? "gp_$as" : "gp").".id_adh = " . ($as ? $as . "." : "galette_adherents.") . "id_adh", ($as ? "gp_$as" : "gp"), "left");
    }

    function selectQuery(&$queryBuilder, $config) {
        $galetteDatabase = "";

        if (isset($config["galette"]["db"]) && $config["galette"]["db"]) {
            $galetteDatabase = $config["galette"]["db"];
            $galetteDatabase .= ".";
        }

		$queryBuilder->addSelect("galette_adherents" . ".*");
//		$queryBuilder->addSelect("gp" . ".picture");
		$queryBuilder->select($galetteDatabase . "galette_adherents");

		$queryBuilder->addSelect("diaf.field_val as discord_id_adh");
		$queryBuilder->join($galetteDatabase . "galette_dynamic_fields", "diaf.item_id = galette_adherents.id_adh AND diaf.field_id = 2 AND diaf.field_form = 'adh'", "diaf", "left");
//		$queryBuilder->join($galetteDatabase . "galette_pictures", "gp.id_adh = galette_adherents.id_adh", "gp", "left");
    }

    function whereId(&$queryBuilder, $config, $value) {
        $queryBuilder->where("id_adh = " . $value);
    }

    function whereEmail(&$queryBuilder, $config, $value) {
        $queryBuilder->where("email_adh = " . $value);
    }
}

?>