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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once("engine/utils/QuorumUtils.php");

$quorumFormula = $meeting["mee_quorum"] ? $meeting["mee_quorum"] : 0;

$quorumFormula = phpToLanguageQuorum($quorumFormula);

?>
<style>
@media (min-width: 1024px) {
    #set-quorum-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #set-quorum-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="set-quorum-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="<?=lang("common_close")?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?=lang("setQuorum_title")?></h4>

            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <input type="hidden" value="<?=$meeting["mee_id"]?>" name="meetingId">
                    <fieldset>
                        <div id="quorum-formula-div" class="form-group">
                            <label class="col-md-4 control-label"></label>
                            <div class="col-md-8">                     
                                <button class="btn btn-default btn-xs quorum-keyword"><?=lang("setQuorum_numberOfConnected")?></button>
                                <button class="btn btn-default btn-xs quorum-keyword"><?=lang("setQuorum_numberOfPresents")?></button>
                                <button class="btn btn-default btn-xs quorum-keyword"><?=lang("setQuorum_numberOfVoters")?></button>
                                <button class="btn btn-default btn-xs quorum-keyword"><?=lang("setQuorum_numberOfNoticed")?></button>
                                <button class="btn btn-default btn-xs quorum-keyword"><?=lang("setQuorum_numberOfPowers")?></button>
                            </div>
                        </div>
                        <div id="quorum-formula-div" class="form-group">
                            <label class="col-md-4 control-label" for="quorum-formula-area"><?=lang("setQuorum_formula")?></label>
                            <div class="col-md-8">                     
                                <textarea class="form-control autogrow" id="quorum-formula-area" name="quorum-formula" style="max-height: 200px;"><?=$quorumFormula?></textarea>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal"><?=lang("common_close")?></button>
                <button type="button" class="btn btn-primary btn-set-quorum"><?=lang("common_modify")?></button>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
