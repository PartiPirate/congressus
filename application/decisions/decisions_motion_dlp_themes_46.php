<?php /*
    Copyright 2021 Cédric Levieux, Parti Pirate

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

?>

<?php   // this a way to call back the default motion display if not in a voting session
        if (strpos($meeting["mee_label"], "Session") === false || strpos($meeting["mee_label"], "Vote") === false) {
            include("decisions/decisions_motion_default.php");
            return;
        } 

        $explanationChatId = null;

        // The last chatId is the explanation
        foreach($agendaObjects as $cagendaObject) {
            if (isset($cagendaObject["chatId"])) {
                $explanationChatId = $cagendaObject["chatId"];
            }
        }

        $explanation = null;

        if ($explanationChatId) {
            // Retrieve this chat
            require_once("engine/bo/ChatBo.php");
            
            $chatBo = ChatBo::newInstance($connection, $config);
            $explanation = $chatBo->getById($explanationChatId);
        }
?>

<?php   if ($explanation) { ?>
<li id="explanation-<?=$object["mot_id"]?>" class="list-group-item motion">
    <h4>Exposé des motifs</h4>
    <div>
        <?php   $text = $emojiClient->shortnameToImage($parsedown->text($explanation["cha_text"])); ?>
        <?=$text?>
    </div>
</li>
<?php   } ?>

<li id="description-<?=$object["mot_id"]?>" class="list-group-item motion">
    <h4>Contenu de la proposition</h4>
    <?=$agenda["age_description"]?>
</li>
    
</li>

<li id="motion-<?=$object["mot_id"]?>" data-id="<?=$object["mot_id"]?>" class="list-group-item motion">
    <h4>
        <span class="fa fa-archive"></span>
        <span class="motion-title"><?=$object["mot_title"]?></span>
    </h4>

<?php
        include("decisions/decisions_motion_charts.php");
?>

<?php   if (count($object["mot_tags"])) { ?>
    <div class="motion-tags">
<?php       foreach($object["mot_tags"] as $tag) { ?>
        <span class="badge"><?php echo $tag["tag_label"]; ?></span>
<?php       } ?>
    </div>
<?php   } ?>
</li>
