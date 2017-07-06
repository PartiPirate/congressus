<div id="exportHtml" class="export_container">
  <div class="export_content">
    <div class="navbar navbar-inverse">
      <div class="navbar-header pull-left hidden-xs">
        <a class="navbar-brand" href="#">Export HTML</a>
      </div>
      <div class="navbar-header pull-left">
        <a class="hidden-xs btn btn-default navbar-btn btn-active" href="#"><?php echo lang("export_rendering"); ?></a>
        <!-- <a class="btn btn-default navbar-btn" href="#">Code HTML</a> -->
        <a class="btn btn-default navbar-btn" href="meeting/do_export.php?template=html&id=<?php echo $meeting["mee_id"]; ?>"><?php echo lang("export_open"); ?></a>
      </div>
      <div class="navbar-header pull-right">
        <a id="export_close_html" title="<?php echo $meeting["common_close"]; ?>" class="btn btn-default navbar-btn" href="#"><span class="glyphicon glyphicon-remove"></span></a>
      </div>
    </div>
    <iframe src="meeting/do_export.php?template=html&id=<?php echo $meeting["mee_id"]; ?>"><?php echo lang("export_iframes"); ?></iframe>
  </div>
</div>
