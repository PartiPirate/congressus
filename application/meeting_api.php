<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/database.php");
include_once("config/memcache.php");
include_once("config/mail.php");
include_once("language/language.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/utils/FormUtils.php");
require_once("engine/utils/GamifierClient.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/LocationBo.php");
require_once("engine/bo/NoticeBo.php");

require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/FixationBo.php");

session_start();

xssCleanArray($_REQUEST);
xssCleanArray($_GET);
xssCleanArray($_POST);

$method = $_GET["method"];

error_log("Meeting API Method : $method");
// Security

if (strpos($method, "..") !== false) {
	echo json_encode(array("error" => "not_a_service"));
	exit();
}

if (!file_exists("meeting/$method.php")) {
	echo json_encode(array("error" => "not_a_service"));
	exit();
}

$arguments = $_POST;
$api = true;

require_once("engine/utils/LogUtils.php");

// We don't log the get methods => A LOT OF THEM
if (strpos($method, "do_get") === false) {
	addLog($_SERVER, $_SESSION, $method, $arguments);
}

//error_log(print_r($_POST, true));

$gamifierClient = GamifierClient::newInstance($config["gamifier"]["url"]);

include("meeting/$method.php");

?>