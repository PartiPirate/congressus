<div id="export_container_markdown" class="export_container simply-hidden">
  <div class="export_content">
    <div class="navbar navbar-inverse">
      <div class="navbar-header pull-left hidden-xs">
        <a class="navbar-brand" href="#">Export Wiki</a>
      </div>
      <div class="navbar-header pull-left">
        <a class="btn btn-default navbar-btn" href="meeting/do_export.php?template=markdown&id=<?php echo $meeting["mee_id"]; ?>"><?php echo lang("export_open"); ?></a>
      </div>
      <div class="navbar-header pull-right">
        <a title="<?php echo $meeting["common_close"]; ?>" class="btn btn-default navbar-btn exportClose" href="#"><span class="glyphicon glyphicon-remove"></span></a>
      </div>
    </div>
    <iframe id="iframe_markdown" src=""><?php echo lang("export_iframes"); ?></iframe>
  </div>
</div>
