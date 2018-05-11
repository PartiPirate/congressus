<style>
@media (min-width: 1024px) {
    #save-trash-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #save-trash-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="save-trash-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?php echo str_replace("{motion_title}", $motion["mot_title"], lang("save_trash_title")); ?></h4>
        </div>
        <div class="modal-body">
            
            <form class="form-horizontal">
                <fieldset>

                    <input id="agendaIdInput" name="pointId" type="hidden" value="<?php echo $agenda["age_id"]; ?>" class="form-control input-md">
                    <input id="meetingIdInput" name="meetingId" type="hidden" value="<?php echo $meeting["mee_id"]; ?>" class="form-control input-md">
                    <input id="motionIdInput" name="motionId" type="hidden" value="<?php echo $motion["mot_id"]; ?>" class="form-control input-md">

                    <!-- Textarea -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="explanationArea"><?php echo lang("trash_explanation"); ?></label>
                        <div class="col-md-8">
                            <textarea class="form-control autogrow" id="explanationArea" name="explanation" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 200px;"></textarea>
                        </div>
                    </div>
                </fieldset>
                    
            </form>          
              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
        <button type="button" class="btn btn-warning btn-trash-motion"><span class="glyphicon glyphicon-trash"></span> <?php echo lang("motion_trash"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
