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

<li id="motion-<?=$object["mot_id"]?>" data-id="<?=$object["mot_id"]?>" class="list-group-item motion">
    <h4>
        <span class="fa fa-archive"></span>
        <span class="motion-title"><?=$object["mot_title"]?></span>
    </h4>

    <div class="motion-description margin-bottom">
        <div class="motion-description-text"><?=$object["mot_description"]?></div>
    </div>

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
