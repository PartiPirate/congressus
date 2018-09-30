<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

	This file is part of Installer.

    Installer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Installer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Installer.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!file_exists("config/config.php")) {
    // intercepts a lack of configuration

    // Create config files
    $_SESSION["administrator"] = true;
    $_REQUEST["api"] = false;
    include_once("do_updateAdministration.php");

    // switch to administration mode
	header('Location: administration.php');

    exit();
}

?>