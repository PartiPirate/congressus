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
<style>
@media (min-width: 1024px) {
    #move-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #move-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="move-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang("common_close")?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?=str_replace("{motion_title}", $motion["mot_title"], lang("move_title"))?></h4>
        </div>
        <div class="modal-body">
            
            <form class="form-horizontal">
                <fieldset>

                    <input id="agendaIdInput" name="pointId" type="hidden" value="<?=$agenda["age_id"]?>" class="form-control input-md">
                    <input id="meetingIdInput" name="meetingId" type="hidden" value="<?=$meeting["mee_id"]?>" class="form-control input-md">
                    <input id="motionIdInput" name="motionId" type="hidden" value="<?=$motion["mot_id"]?>" class="form-control input-md">


                    <!-- Select Basic -->
                    <div id="meetingSelectDiv" class="form-group">
                        <label class="col-md-4 control-label" for="meetingSelect">Réunion</label>
                        <div class="col-md-8">
                            <select id="meetingSelect" name="meetingId" class="form-control">
<!--                                
                                <option value="0" disabled="disabled" selected="selected"></option>
                                <optgroup label="Discussion et Co-construction" id="construction-group"></optgroup>
                                <optgroup label="Réunion et prise de décisions" id="meeting-group"></optgroup>
-->                                
                            </select>
                        </div>
                    </div>
    
                    <!-- Select Basic -->
                    <div id="agendaSelectDiv" class="form-group" style="display: none;">
                        <label class="col-md-4 control-label" for="agendaSelect">Point</label>
                        <div class="col-md-8">
                            <select id="agendaSelect" name="agendaId" class="form-control">
                            </select>
                        </div>
                    </div>

                </fieldset>
                    
            </form>          
              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("common_close")?></button>
        <button type="button" class="btn btn-success btn-move-motion"><i class="fa fa-share-square-o"></i> <?=lang("motion_move")?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
