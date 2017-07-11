<!--
	Copyright 2017 Nino Treyssat-Vincent, Parti Pirate

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
-->
<div id="export_container_html" class="export_container simply-hidden">
  <div class="export_content">
    <div class="navbar navbar-inverse">
      <div class="navbar-header pull-left hidden-xs">
        <a class="navbar-brand" href="#">Export HTML</a>
      </div>
      <div class="navbar-header pull-left">
        <a id="rendering" data-format="html" class="btnShowExport hidden-xs btn btn-default navbar-btn btn-active" href="#"><?php echo lang("export_rendering"); ?></a>
        <a id="html-code" data-format="html-code" class="btnShowExport hidden-xs btn btn-default navbar-btn" href="#">Code HTML</a>
        <a id="newpage_html" class="btn btn-default navbar-btn" href="meeting/do_export.php?template=html&id=<?php echo $meeting["mee_id"]; ?>?textarea=false" target="_blank"><?php echo lang("export_open"); ?></a>
      </div>
      <div class="navbar-header pull-right">
        <a title="<?php echo lang("common_close"); ?>" class="btn btn-default navbar-btn exportClose" href="#"><span class="glyphicon glyphicon-remove"></span></a>
      </div>
    </div>
    <iframe id="iframe_html" src=""><?php echo lang("export_iframes"); ?></iframe>
  </div>
</div>
