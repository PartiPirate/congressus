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
    along with Personae.  If age, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

$connection = openConnection();

$data = array();

echo json_encode($data, JSON_NUMERIC_CHECK);
?>