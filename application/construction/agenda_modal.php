<style>
@media (min-width: 1024px)
#save-agenda-modal .modal-dialog {
    width: 900px;
}

@media (min-width: 1600px)
#save-agenda-modal .modal-dialog {
    width: 1300px;
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="save-agenda-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

        <h4 class="modal-title"><?php echo lang("save_agenda_title"); ?>...</h4>
      </div>
      <div class="modal-body">
          
        <form class="form-horizontal">
            <fieldset>

                <input id="agendaIdInput" name="pointId" type="hidden" placeholder="" class="form-control input-md">
                <input id="meetingIdInput" name="meetingId" type="hidden" placeholder="" class="form-control input-md">

                <!-- Text input-->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="titleInput">Titre</label>  
                    <div class="col-md-8">
                        <input id="titleInput" name="title" type="text" placeholder="" class="form-control input-md">
                    </div>
                </div>

                <!-- Textarea -->
                <div class="form-group">
                    <label class="col-md-4 control-label" for="descriptionArea">Description</label>
                    <div class="col-md-8">                     
                        <textarea class="form-control autogrow" id="descriptionArea" name="description" rows="5" style="max-height: 300px;"></textarea>
                    </div>
                </div>

            </fieldset>
        </form>          
          
      </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
        <button type="button" class="btn btn-primary btn-save-agenda"><?php echo lang("common_create"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
