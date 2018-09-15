<style>
@media (min-width: 1024px) {
    #authorship-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #authorship-modal .modal-dialog {
        width: 1300px;
    }
}

.co-author + .co-author:before {
    content: ", ";
}

.co-author .delete-co-author-btn:hover, .co-author .exchange-co-author-btn:hover {
    cursor: pointer;
}

</style>

<div class="modal fade" tabindex="-1" role="dialog" id="authorship-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?php echo lang("authorship_title"); ?></h4>
        </div>
        <div class="modal-body">
            
            <form class="form-horizontal" id="add-co-author-form">
                <fieldset>

                    <legend><?php echo lang("authorship_add_co_author"); ?></legend>

                    <input id="agendaIdInput" name="pointId" type="hidden" value="<?php echo $agenda["age_id"]; ?>" class="form-control input-md">
                    <input id="meetingIdInput" name="meetingId" type="hidden" value="<?php echo $meeting["mee_id"]; ?>" class="form-control input-md">
                    <input id="motionIdInput" name="motionId" type="hidden" value="<?php echo $motion["mot_id"]; ?>" class="form-control input-md">

                    <!-- Text input-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="userDataInput">Identification</label>  
                        <div class="col-md-6">
                            <input id="userDataInput" name="userData" type="text" placeholder="" class="form-control input-md">
                        </div>
                        <div class="col-md-1">
                            <button id="add-co-author-btn" type="button" class="btn btn-success form-control input-md" ><i class="fa fa-plus" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    

                </fieldset>
                    
            </form>          

            <fieldset id="co-author-container">

                <legend><?php echo lang("authorship_co_authors"); ?></legend>

                <div class="form-group co-authors">
                    <?php
                        foreach($coAuthors as $coAuthor) {
                            ?><span class="co-author co-author-<?php echo $coAuthor["cau_id"]; ?>"> 
                                    <i class="fa fa-times text-danger delete-co-author-btn" data-co-author-id="<?php echo $coAuthor["cau_id"]; ?>" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="<?php echo lang("amendment_delete_co_author"); ?>"></i> 
                                    <i class="fa fa-exchange text-primary exchange-co-author-btn" data-co-author-id="<?php echo $coAuthor["cau_id"]; ?>" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="<?php echo lang("amendment_exchange_co_author"); ?>"></i> 
                                    <span class="nickname"><?php echo GaletteBo::showPseudo($coAuthor); ?></span>
                                </span><?php
                        }
                    ?>
                </div>

            </fieldset>              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
    
var deleteCoAuthorTitle = <?php echo json_encode(lang("amendment_delete_co_author", false)); ?>;
var exchangeCoAuthorTitle = <?php echo json_encode(lang("amendment_exchange_co_author", false)); ?>;

</script>