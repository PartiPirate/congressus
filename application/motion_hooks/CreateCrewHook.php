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

require_once("engine/converters/html2md/Parser.php");
require_once("engine/converters/html2md/Converter.php");
require_once("engine/converters/html2md/ConverterExtra.php");

require_once("engine/bo/MessageBo.php");

require_once("engine/bo/GroupBo.php");
require_once("engine/bo/ThemeBo.php");
require_once("engine/bo/FixationBo.php");

class CreateCrewwHook implements WinningMotionHook {

    function doHook($agenda, $motion, $proposition, $chats) {
        global $config;

        if (strtolower($proposition["mpr_label"]) != "oui" && strtolower($proposition["mpr_label"]) != "pour") return "did nothing";

        $json = array("STATUT" => "voté", "DATE" => "", "TAGS" => array(), "SCORE" => "0");

        $isCrew = false;
        $crewType = "thématique"; // default value

        $tags = "";

        foreach($motion["mot_tags"] as $tag) {
            $tags .= mb_strtolower($tag["tag_label"]) . " ";
            if (mb_strtolower($tag["tag_label"]) == "équipage") {
                $isCrew = true;
            }
            else if (strpos(mb_strtolower($tag["tag_label"]), "équipage ") !== false) {
                $isCrew = true;
                $crewType = substr($tag["tag_label"], 9);
            }
        }

        if (!$isCrew) return "not a crew point : $tags";

        $converter = new Markdownify\Converter;
        $operatingCode = $converter->parseString($agenda["age_description"]);

        // L'Assemblée Permanente valide la création de l'équipage "Spatial"
        $crewRegex = '/"([a-zA-Z]*)"/m';
        preg_match_all($crewRegex, $motion["mot_title"], $matches, PREG_SET_ORDER, 0);

        if (!isset($matches[0][1])) return "can't find crew name";

        $returnedData = array(); 

        $crewName = $matches[0][1];

        $returnedData["crew_name"] = $crewName;

        /* WIKI */

        // Create wiki entry

        $services = openWikiSession();

        $explanatoryMemorandum = "";

		$ageObjects = json_decode($agenda["age_objects"], true);
		foreach($ageObjects as $ageObject) {
			if (isset($ageObject["chatId"])) {
			    foreach($chats as $chat) {
			        if ($chat["cha_id"] == $ageObject["chatId"]) {
                        $explanatoryMemorandum .= $chat["cha_text"];
                        $explanatoryMemorandum .= "\n\n-----\n\n";
                        break;
                        break;
			        }
			    }
			}
		}

        $crewWikiBase = "Equipage:" . $crewName;
        $crewWikiOperatingCode = $crewWikiBase . "/code de fonctionnement";

        $wikiTitle = "$crewWikiBase";
        $wikiReport = $explanatoryMemorandum . "Lire notre [[" . $crewWikiOperatingCode . "|code de fonctionnement]]";
        
        $title = new \Mediawiki\DataModel\Title($wikiTitle);
        $newContent = new \Mediawiki\DataModel\Content($wikiReport);
        
        $identifier = new \Mediawiki\DataModel\PageIdentifier($title );
        $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
        
        //print_r($revision);
        
        $result = $services->newRevisionSaver()->save($revision);


        $wikiTitle = "$crewWikiOperatingCode";
//        $wikiReport = "= Équipage “".$crewName."” =\n\n";
        $wikiReport = $operatingCode;

        $title = new \Mediawiki\DataModel\Title($wikiTitle);
        $newContent = new \Mediawiki\DataModel\Content($wikiReport);
        
        $identifier = new \Mediawiki\DataModel\PageIdentifier($title );
        $revision = new \Mediawiki\DataModel\Revision($newContent, $identifier);
        
        //print_r($revision);
        
        $result = $services->newRevisionSaver()->save($revision);

        // TODO change the global crew page

        /* DISCOURSE */

        $discourseApi = new richp10\discourseAPI\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

        // Create category discourse

        $crewCategoryId = 66; // Crew category ID

        $result = $discourseApi->createCategory("Équipage " . $crewName, "3498DB");
        $newCategoryId = $result->apiresult->category->id;

        $result = $discourseApi->updatecat($newCategoryId, 'true', 'false', '', '', '3498DB', 'false', '', 'false', '', "Équipage " . $crewName, 66, '', '', '', 'false', 'FFFFFF', '', array('Membres' => 1));

        $returnedData["addDiscourseCatory"] = array("name" => "Équipage " . $crewName, "id" => $newCategoryId);

        // Create group discourse

        // TODO ?

        /* PERSONAE */

        $connection = openConnection($config["database"]["database"]);

        $groupBo = GroupBo::newInstance($connection, $config);
        $themeBo = ThemeBo::newInstance($connection, $config);
        $fixationBo = FixationBo::newInstance($connection, $config);

        // Create group personae

        $group = array("gro_label" => "Équipage " . $crewName, "gro_contact_type" => "none");
        $groupBo->save($group);

        // Create member theme personae

        $memberTheme = array("the_label" => $crewName . " - Membres", "the_discourse_group_labels" => "[]", "the_discord_export" => 0, "the_voting_group_id" => 0, "the_voting_group_type" => "galette_adherents", "the_voting_power" => 1, "the_voting_method" => "external_results", "the_eligible_group_id" => 0, "the_eligible_group_type" => "galette_adherents");
        $themeBo->save($memberTheme);

        // Create captain theme personae

        $captainTheme = array("the_label" => $crewName . " - Capitaine", "the_discourse_group_labels" => "[\"Capitainerie\"]", "the_discord_export" => 1, "the_voting_group_id" => 0, "the_voting_group_type" => "galette_adherents", "the_voting_power" => 1, "the_voting_method" => "external_results", "the_eligible_group_id" => $memberTheme["the_id"], "the_eligible_group_type" => "dlp_themes");
        $themeBo->save($captainTheme);

        // Link them

        $groupBo->addTheme($group, $memberTheme);
        $groupBo->addTheme($group, $captainTheme);

        // Fix them

        $untilDate = getNow();
        $untilDate->add(new DateInterval('P1Y'));

        $memberFixation = new array("fix_until_date" => $untilDate->format('Y-m-d'), "fix_theme_id" => $memberTheme["the_id"], "" => "dlp_themes");
        $fixationBo->save($memberFixation);
        $memberTheme = array("the_id" => $memberTheme["the_id"], "the_current_fixation_id" => $memberFixation["fix_id"]);
        $themeBo->save($memberTheme);

        $captainFixation = new array("fix_until_date" => $untilDate->format('Y-m-d'), "fix_theme_id" => $captainTheme["the_id"], "" => "dlp_themes");
        $fixationBo->save($captainFixation);
        $captainTheme = array("the_id" => $captainTheme["the_id"], "the_current_fixation_id" => $captainFixation["fix_id"]);
        $themeBo->save($captainTheme);

        /* DISCORD */

        $messageBo = MessageBo::newInstance($connection, $config);

        // Create text channel on discord in the good category

        $discordCategory = "🤝 équipages thématiques 🤝";
        switch($crewType) {
            case "événementiel":
                $discordCategory = "🏟Équipages événementiels";
                break;
            case "géographique":
                $discordCategory = "🏡équipages géographiques🏡";
                break;
        }

        //{"action":"create","type":"channel","label":"bouya","category":"Salons textuels","topic":"My Topic","rights":["DENY_ALL"]}
        $message = array("mes_to" => "discord", "mes_message" => array("action" => "create", "type" => "channel", "label" => $crewName, "category" => $discordCategory, "rights" => array("DENY_ALL")));
        $message["mes_message"] = json_encode($message["mes_message"]);
        $messageBo->save($message);

        $returnedData["createChannel"] = $message;

        // Create member role on discord

        //{"action":"create","type":"role","label":"bouya","color":"#3498DB","permissions":[{"channel":"Bouya","rights":["ALLOW_MESSAGE_MANAGE"]}]}
        $message = array("mes_to" => "discord", "mes_message" => array("action" => "create", "type" => "role", "label" => "Équipage " . $crewName, "color" => "#3498DB", "permissions" => array(array("channel" => $crewName, "rights" => array()))));
        $message["mes_message"] = json_encode($message["mes_message"]);
        $messageBo->save($message);

        $returnedData["createMemberRole"] = $message;

        // Create captain role on discord

        $message = array("mes_to" => "discord", "mes_message" => array("action" => "create", "type" => "role", "label" => $crewName . " - Capitaine", "color" => "#206694", "permissions" => array(array("channel" => $crewName, "rights" => array("ALLOW_MESSAGE_MANAGE")))));
        $message["mes_message"] = json_encode($message["mes_message"]);
        $messageBo->save($message);

        $returnedData["createCaptainrRole"] = $message;

        // Add pirate role in the text channel

        //{"action":"addinchannel","type":"role","channel":"bouya","role":"DJ","rights":["ALLOW_ALL"]}
        $message = array(   
                            "mes_to" => "discord", 
                            "mes_message" => array(
                                "action" => "addinchannel", 
                                "type" => "role", 
                                "channel" => $crewName, 
                                "role" => "Pirates", 
                                "rights" => array("ALLOW_MESSAGE_READ", "ALLOW_MESSAGE_WRITE", "ALLOW_MESSAGE_EMBED_LINKS", "ALLOW_MESSAGE_ATTACH_FILES", "ALLOW_MESSAGE_HISTORY", "ALLOW_MESSAGE_MENTION_EVERYONE", "ALLOW_MESSAGE_EXT_EMOJI", "ALLOW_MESSAGE_ADD_REACTION")
                            )
                        );
        $message["mes_message"] = json_encode($message["mes_message"]);
        $messageBo->save($message);

        $returnedData["addPirateRole"] = $message;

        // Add operating code of the crew

        $message = array(   
                            "mes_to" => "discord", 
                            "mes_message" => array(
                                "action" => "create", 
                                "type" => "message", 
                                "channel" => $crewName, 
                                "isPinned" => true,
                                "content" => "Le code de fonctionnement : https://wiki.partipirate.org/Equipage:$crewName/code_de_fonctionnement"
                            )
                        );
        $message["mes_message"] = json_encode($message["mes_message"]);
        $messageBo->save($message);

        $returnedData["addOperatingCode"] = $message;

        return $returnedData;
    }
}

global $hooks;

if (!$hooks) $hooks = array();

$hooks["cch"] = new CreateCrewwHook();

?>