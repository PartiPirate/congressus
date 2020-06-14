<style>
@media (min-width: 1024px) {
    #save-amendment-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #save-amendment-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="save-amendment-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?php echo lang((isset($jsonMotion) ? "save_amendment_title" : "save_motion_title")); ?>...</h4>
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
                        <label class="col-md-4 control-label" for="explanationArea"><?php echo lang("amendment_explanation"); ?></label>
                        <div class="col-md-8">
                            <textarea class="form-control autogrow" id="explanationArea" name="explanation" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 200px;"></textarea>
                        </div>
                    </div>

                    <!-- Textarea -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="descriptionArea">Description</label>
                        <div class="col-md-8">
                            <textarea class="form-control autogrow" id="descriptionArea" name="description" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 200px;"></textarea>
                        </div>
                    </div>
                </fieldset>
                    
                <fieldset>
                    <!-- Select Basic -->
                    <div id="sourceSelectDiv" class="form-group">
                        <label class="col-md-4 control-label" for="sourceSelect">Source</label>
                        <div class="col-md-8">
                            <select id="sourceSelect" name="sourceType" class="form-control">
                                <option value=""></option>
                                <option value="leg_text">Legifrance - Texte</option>
                                <option value="leg_article">Legifrance - Article</option>
                                <option value="wiki_text">Wiki - Texte</option>
                                <option value="congressus_motion">Congressus - Motion</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Text input-->
                    <div id="sourceUrlDiv" class="form-group" style="display: none;">
                        <label class="col-md-4 control-label" for="sourceUrlInput">Url</label>  
                        <div class="col-md-8">
                            <input id="sourceUrlInput" name="sourceUrl" type="text" placeholder="" class="form-control input-md">
                        </div>
                    </div>

                    <div id="sourceTitleDiv" class="form-group" style="display: none;">
                        <label class="col-md-4 control-label" for="sourceTitleInput">Titre</label>  
                        <div class="col-md-8">
                            <input id="sourceTitleInput" name="sourceTitle" type="text" placeholder="" class="form-control input-md">
                        </div>
                    </div>

                    <!-- Select Multiple -->
                    <div id="sourceArticlesDiv" class="form-group" style="display: none;">
                        <label class="col-md-4 control-label" for="sourceArticlesSelect">Articles</label>
                        <div class="col-md-8">
                            <select id="sourceArticlesSelect" name="sourceArticles[]" class="form-control" multiple="multiple">
                            </select>
                        </div>
                    </div>

                    <div id="sourceContentDiv" class="form-group" style="display: none;">
                        <label class="col-md-4 control-label" for="sourceContentArea">Contenu</label>  
                        <div class="col-md-8">
                            <textarea class="form-control autogrow" id="sourceContentArea" name="sourceContent" data-provide="markdown" data-hidden-buttons="cmdPreview" rows="5" style="max-height: 200px;"></textarea>
                        </div>
                    </div>

                </fieldset>
            </form>          
              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
        <button type="button" class="btn btn-info btn-preview-motion"><?php echo lang("common_preview"); ?></button>
        <button type="button" class="btn btn-primary btn-save-motion"><?php echo lang("common_create"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
