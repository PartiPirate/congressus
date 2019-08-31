<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once("config/database.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/bo/GaletteBo.php");

// We sanitize the request fields
//xssCleanArray($_REQUEST);

$connection = openConnection();

$galetteBo = GaletteBo::newInstance($connection, $config["galette"]["db"]);

$members = $galetteBo->getMembers(array("adh_only" => true));

//print_r($members);

?>
"UUID","ZIPCODE","CITY","COUNTRY"
<?php

	foreach($members as $member) {
		$uuid = $member["id_adh"] .  $config["galette"]["db"];
		$uuid = md5($uuid);

		echo "\"";
		echo $uuid;
		echo "\",\"";
		echo $member["cp_adh"];
		echo "\",\"";
		echo mb_strtoupper(utf8_encode($member["ville_adh"]));
		echo "\",\"";
		echo mb_strtoupper(utf8_encode($member["pays_adh"]));
		echo "\"\n";
	}

?>