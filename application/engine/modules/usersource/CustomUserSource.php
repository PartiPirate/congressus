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
/**
 * 
 *        $config["modules"]["usersource"] = "Custom";
 *
 *        $config["modules"]["custom"]["database"] = "galette";
 *        $config["modules"]["custom"]["table"] = "galette_adherents";
 *        $config["modules"]["custom"]["fields"] = array();
 *        $config["modules"]["custom"]["fields"]["id_adh"]        = "id_adh";
 *        $config["modules"]["custom"]["fields"]["pseudo_adh"]    = "pseudo_adh";
 *        $config["modules"]["custom"]["fields"]["login_adh"]     = "login_adh";
 *        $config["modules"]["custom"]["fields"]["nom_adh"]       = "nom_adh";
 *        $config["modules"]["custom"]["fields"]["prenom_adh"]    = "prenom_adh";
 *        $config["modules"]["custom"]["fields"]["email_adh"]     = "email_adh";
 */
class CustomUserSource {
    
    function upgradeQuery(&$queryBuilder, $config, $joinField, $as = null) {

        $customDatabase = $config["modules"]["custom"]["database"];

		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["id_adh"],      "id_adh");
		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["pseudo_adh"],  "pseudo_adh");
		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["login_adh"],   "login_adh");
		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["nom_adh"],     "nom_adh");
		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["prenom_adh"],  "prenom_adh");
		$queryBuilder->addSelect(($as ? $as : $config["modules"]["custom"]["table"]) . "." . $config["modules"]["custom"]["fields"]["email_adh"],   "email_adh");

		$queryBuilder->join($customDatabase . "." . $config["modules"]["custom"]["table"], ($as ? $as . "." : "") . $config["modules"]["custom"]["fields"]["id_adh"] . " = $joinField", $as, "left");
    }

    function selectQuery(&$queryBuilder, $config) {
        $customDatabase = $config["modules"]["custom"]["database"];

		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . ".*");

		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["id_adh"],      "id_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["pseudo_adh"],  "pseudo_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["login_adh"],   "login_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["nom_adh"],     "nom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["prenom_adh"],  "prenom_adh");
		$queryBuilder->addSelect($config["modules"]["custom"]["table"] . "." . $config["modules"]["custom"]["fields"]["email_adh"],   "email_adh");

		$queryBuilder->select($customDatabase . "." . $config["modules"]["custom"]["table"]);
    }

    function whereId(&$queryBuilder, $config, $value) {
        $queryBuilder->where($config["modules"]["custom"]["fields"]["id_adh"] . " = " . $value);
    }

    function whereNotId(&$queryBuilder, $config, $value) {
        $queryBuilder->where($config["modules"]["custom"]["fields"]["id_adh"] . " != " . $value);
    }

    function whereEmail(&$queryBuilder, $config, $value) {
        $queryBuilder->where($config["modules"]["custom"]["fields"]["email_adh"] . " = " . $value);
    }

    function wherePseudo(&$queryBuilder, $config, $value) {
        $queryBuilder->where($config["modules"]["custom"]["fields"]["pseudo_adh"] . " = " . $value);
    }
}

?>