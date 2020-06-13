<style>
@media (min-width: 1024px) {
    #connect-modal .modal-dialog {
        width: 900px;
    }
}

@media (min-width: 1600px) {
    #connect-modal .modal-dialog {
        width: 1300px;
    }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="connect-modal">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">

            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>

            <h4 class="modal-title"><?=lang("connect_form_legend")?></h4>
        </div>
        <div class="modal-body">
            
            <form class="form-horizontal">
                <fieldset>

                    <div class="error-container" style="height: 20px;">
                        <div class="error-div text-danger text-center" style="display: none;">
                        	<?=lang("error_login_bad")?>
                        </div>
                    </div>

        			<!-- Text input-->
        			<div class="form-group has-feedback">
        				<label class="col-md-4 control-label" for="userLoginInput"><?php echo lang("connect_form_loginInput"); ?></label>
        				<div class="col-md-6">
        					<input id="userLoginInput" name="login" value="" type="text"
        						placeholder="" class="form-control input-md">
        					<span id="userLoginStatus"
        						class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
        					<p class="help-block"><?php echo lang("connect_form_loginHelp");?></p>
        					<p id="userLoginHelp" class="help-block otbHidden"></p>
        				</div>
        			</div>
        
        			<!-- Password input-->
        			<div class="form-group has-feedback">
        				<label class="col-md-4 control-label" for="userPasswordInput"><?php echo lang("connect_form_passwordInput"); ?></label>
        				<div class="col-md-6">
        					<input id="userPasswordInput" name="password" value="" type="password"
        						placeholder="" class="form-control input-md">
        					<span id="passwordStatus" class="glyphicon glyphicon-ok form-control-feedback otbHidden" aria-hidden="true"></span>
        					<p class="help-block"><?php echo lang("connect_form_passwordHelp");?></p>
        				</div>
        			</div>

                </fieldset>
            </form>          
              
        </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
        <button type="button" class="btn btn-primary btn-connect-inline"><?php echo lang("common_connect"); ?></button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
