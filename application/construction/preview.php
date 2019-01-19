<?php /*
	Copyright 2018 Cédric Levieux, Parti Pirate

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

require_once("engine/utils/Parsedown.php");
require_once("engine/emojione/autoload.php");

$motion = array();
$motion["mot_title"] = $_POST["title"];
$motion["mot_explanation"] = $_POST["explanation"];
$motion["mot_description"] = $_POST["description"];

$Parsedown = new Parsedown();
$emojiClient = new Emojione\Client(new Emojione\Ruleset());
?>

<style>
@media (min-width: 1024px) {
    #preview-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #preview-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="preview-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?php echo str_replace("{motion_title}", $motion["mot_title"], lang("preview_motion_title")); ?></h4>
        </div>
        <div class="modal-body">


					<div id="explanation-div">
						<label for="explanation"><?php echo lang("amendment_explanation"); ?></label>
						<br>
						<div id="explanation-textarea-div" style="display: none;">
							<textarea class="form-control autogrow" name="explanation" id="explanation" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5"><?php echo $motion["mot_explanation"]; ?></textarea>
						</div>
						<div id="explanation-content-div" style="display: block;">
							<?php echo $emojiClient->shortnameToImage($Parsedown->text($motion["mot_explanation"])); ?>
						</div>
						<hr>
					</div>

					<label for="markdown-group"><?php echo lang("amendment_description"); ?> </label>
					<br>

					<div id="markdown-group">
						<div id="markdown-area">
							<?php echo $emojiClient->shortnameToImage($Parsedown->text($motion["mot_description"])); ?>
						</div>
					</div>

              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
