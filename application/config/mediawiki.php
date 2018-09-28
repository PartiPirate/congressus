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

require_once("config/mediawiki.config.php");
require_once("engine/mediawiki/autoload.php");

function openWikiSession() {
    global $config;
    
    $api = new \Mediawiki\Api\MediawikiApi($config["mediawiki"]["url"]);
    $api->login( new \Mediawiki\Api\ApiUser($config["mediawiki"]["login"], $config["mediawiki"]["password"]));
    $services = new \Mediawiki\Api\MediawikiFactory( $api );

    return $services;    
}

?>