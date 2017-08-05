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

		$queryBuilder->join($galetteDatabase . "galette_adherents", ($as ? $as . "." : "")."id_adh = $joinField", $as, "left");
    }

    function selectQuery(&$queryBuilder, $config) {
        $galetteDatabase = "";

        if (isset($config["galette"]["db"]) && $config["galette"]["db"]) {
            $galetteDatabase = $config["galette"]["db"];
            $galetteDatabase .= ".";
        }

		$queryBuilder->addSelect("galette_adherents" . ".*");
		$queryBuilder->select($galetteDatabase . "galette_adherents");
    }

    function whereId(&$queryBuilder, $config, $value) {
        $queryBuilder->where("id_adh = " . $value);
    }

    function whereEmail(&$queryBuilder, $config, $value) {
        $queryBuilder->where("email_adh = " . $value);
    }
}

?>