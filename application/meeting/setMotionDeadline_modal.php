<?php /*
	Copyright 2019 Cédric Levieux, Parti Pirate

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
?>
<style>
@media (min-width: 1024px) {
    #set-motion-deadline-modal .modal-dialog {
        width: 500px;
    }
}

@media (min-width: 1600px) {
    #set-motion-deadline-modal .modal-dialog {
        width: 500px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="set-motion-deadline-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang("common_close")?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?=lang("setMotionDeadline_title")?></h4>

            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <input type="hidden" value="<?=$meeting["mee_id"]?>" name="meetingId">
                    <input type="hidden" value="" name="agendaId">
                    <input type="hidden" value="" name="motionId">
                    <fieldset>
                        <div id="quorum-formula-div" class="form-group">
                            <label class="col-md-2 control-label" for="motion-deadline-date"><?=lang("setMotionDeadline_date")?></label>
                            <div class="col-md-5">                     
                                <input class="form-control input-md" type="date" id="motion-deadline-date" placeholder="aaaa-mm-jj" >
                            </div>
                            <label class="col-md-2 control-label" for="motion-deadline-time"><?=lang("setMotionDeadline_time")?></label>
                            <div class="col-md-3">                     
                                <input class="form-control input-md" type="time" id="motion-deadline-time" placeholder="hh:mm" >
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("common_close")?></button>
                <button type="button" class="btn btn-primary btn-set-motion-deadline"><?=lang("common_modify")?></button>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
