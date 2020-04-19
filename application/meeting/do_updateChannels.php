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

if (!isset($api)) exit();

include_once("config/database.php");
require_once("engine/bo/DiscordChannelBo.php");
require_once("engine/bo/LocationBo.php");

$data = array();

if (!testTokens()) {
	$data["ko"] = "ko";
	$data["message"] = "bad_tokens";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit;
}

$connection = openConnection();

$discordChannelBo = new DiscordChannelBo($connection, $config);
$locationBo = new LocationBo($connection, $config);

// delete ALL channels to undelete only surviving ones
$channels = $discordChannelBo->getByFilters(array("with_invisible" => true));
foreach($channels as $channel) {
    $channel["dch_deleted"] = 1;
    $discordChannelBo->save($channel);
}

$channels = json_decode($arguments["data"], true);

foreach($channels as $channel) {
    $discordChannel = array();

    $discordChannel["dch_server_id"]    = $channel["server_id"];
    $discordChannel["dch_channel_id"]   = $channel["id"];
    $discordChannel["dch_type"]         = $channel["type"];
    $discordChannel["dch_name"]         = $channel["name"];
    $discordChannel["dch_url"]          = $channel["url"];
    $discordChannel["dch_deleted"]      = 0;

    // search for existing channel
    $foundChannels = $discordChannelBo->getByFilters(array("with_deleted" => true, "with_invisible" => true, "dch_server_id" => $discordChannel["dch_server_id"], "dch_channel_id" => $discordChannel["dch_channel_id"]));
    if (count($foundChannels)) {
        $discordChannel["dch_id"] = $foundChannels[0]["dch_id"];

        if ($discordChannel["dch_name"] != $foundChannels[0]["dch_name"]) {

            // Upgrade locations
            if ($discordChannel["dch_type"] == "text") {
                $location_like = array("loc_like_channel" => $foundChannels[0]["dch_name"] . ",");
            }
            else {
                $location_like = array("loc_like_channel" => "," . $foundChannels[0]["dch_name"]);
            }

            $locations = $locationBo->getByFilters($location_like);

            foreach($locations as $location) {
                $channelParts = explode(",", $location["loc_channel"]);

                if ($discordChannel["dch_type"] == "text") {
                    $channelParts[0] = $discordChannel["dch_name"];
                }
                else {
                    $channelParts[1] = $discordChannel["dch_name"];
                }

                $location["loc_channel"] = implode(",", $channelParts);

                $locationBo->save($location);
            }
        }
    }

    // save channel
    $discordChannelBo->save($discordChannel);
}

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>