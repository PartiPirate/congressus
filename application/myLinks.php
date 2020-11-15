<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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
include_once("header.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/TrustLinkBo.php");

$userId = SessionUtils::getUserId($_SESSION);

$trustLinkBo = TrustLinkBo::newInstance($connection, $config);

$yourLinks = $trustLinkBo->getByFilters(array("tli_from_member_id" => $userId));
$toYouLinks = $trustLinkBo->getByFilters(array("tli_to_member_id" => $userId));

?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_myLinks"); ?></li>
	</ol>

	<div class="well well-sm">
		<p><?php echo lang("mylinks_guide"); ?></p>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Mes relations de confiance</h3>
		</div>
		<div class="panel-body">
			<form class="form-horizontal" id="authorize-form">
				<input type="hidden" name="type" value="from">
				<div class="form-group">
					<label class="col-md-4 control-label" for="pseudo-input">Personne à qui donner des droits</label>  
					<div class="col-md-8">
						<input id="pseudo-input" name="pseudo" type="text" placeholder="" class="form-control input-md">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-4 control-label">Droits</label>  
					<div class="col-md-8">
						<label class="checkbox-inline" for="right_authoring">
							<input type="checkbox" name="rights[]" id="right_authoring" value="authoring">
							<?=lang("links_authorized_authoring")?>
						</label>
    				</div>
				</div>
				<div class="form-group text-center">
					<button class="btn btn-primary btn-a-add"><?=lang("links_authorized_add")?></button>
				</div>
			</form>
		</div>
		<table class="table table-striped from-links">
<?php	foreach($yourLinks as $link) { 
			$rights = json_decode($link["tli_rights"], true);

			$explainRights = array();

			foreach($rights as $rightKey => $right) {
				if ($right) {
					$explainRights[] = lang("links_authorized_" . $rightKey);
				}
			}

			$explanation = implode(", ", $explainRights);
?>
			<tr>
				<td style="width: 20px;"><img src="getAvatar.php?userId=<?=$link["tli_to_member_id"]?>"  style="max-width: 16px; max-height: 16px; position: relative; top: -2px;"></td>
				<td style="width: 150px;"><?=GaletteBo::showIdentity($link)?></td>
				<td><?=$explanation?></td>
				<td style="width: 150px;">
					<?=lang("links_" . $link["tli_status"])?>
				</td>
				<td style="width: 150px;">
<?php		
			switch($link["tli_status"]) {
				case "link":
?>
					<button class="btn btn-danger btn-cancel btn-xs"  data-id="<?=$link["tli_id"]?>"><?=lang("links_cancel")?></button>
<?php
					break;
				case "asking":
?>
					<button class="btn btn-success btn-accept btn-xs" data-id="<?=$link["tli_id"]?>"><?=lang("links_accept")?></button>
					<button class="btn btn-danger btn-reject btn-xs"  data-id="<?=$link["tli_id"]?>"><?=lang("links_reject")?></button>
<?php
					break;
				case "refused":
					break;
			}
?>
				</td>
			</tr>
<?php	} ?>
		</table>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">Les gens qui placent de la confiance en moi</h3>
		</div>
		<div class="panel-body">
			<form class="form-horizontal" id="im-authorized-form">
				<input type="hidden" name="type" value="to">
				<div class="form-group">
					<label class="col-md-4 control-label" for="pseudo-input_">Personne à qui demander des droits</label>  
					<div class="col-md-8">
						<input id="pseudo-input_" name="pseudo" type="text" placeholder="" class="form-control input-md">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-4 control-label">Droits</label>  
					<div class="col-md-8">
						<label class="checkbox-inline" for="right_authoring_">
							<input type="checkbox" name="rights[]" id="right_authoring_" value="authoring">
							<?=lang("links_im_authorized_authoring")?>
						</label>
    				</div>
				</div>
				<div class="form-group text-center">
					<button class="btn btn-primary btn-ima-add"><?=lang("links_im_authorized_add")?></button>
				</div>
			</form>
		</div>
		<table class="table table-striped to-links">
<?php	foreach($toYouLinks as $link) { 
			$rights = json_decode($link["tli_rights"], true);

			$explainRights = array();

			foreach($rights as $rightKey => $right) {
				if ($right) {
					$explainRights[] = lang("links_im_authorized_" . $rightKey);
				}
			}

			$explanation = implode(", ", $explainRights);
?>
			<tr>
				<td style="width: 20px;"><img src="getAvatar.php?userId=<?=$link["tli_from_member_id"]?>" style="max-width: 16px; max-height: 16px; position: relative; top: -2px;"></td>
				<td style="width: 150px;"><?=GaletteBo::showIdentity($link)?></td>
				<td><?=$explanation?></td>
				<td style="width: 150px;">
					<?=lang("links_" . $link["tli_status"])?>
				</td>
				<td style="width: 150px;">
<?php		
			switch($link["tli_status"]) {
				case "link":
					break;
				case "asking":
?>
					<button class="btn btn-danger btn-cancel btn-xs" data-id="<?=$link["tli_id"]?>"><?=lang("links_cancel")?></button>
<?php
					break;
				case "refused":
?>
					<button class="btn btn-danger btn-cancel btn-xs" data-id="<?=$link["tli_id"]?>"><?=lang("links_cancel")?></button>
<?php
					break;
			}
?>
				</td>
			</tr>
<?php	} ?>
		</table>
	</div>

</div>

<div class="lastDiv"></div>

<script type="text/javascript">
var userLanguage = '<?php echo SessionUtils::getLanguage($_SESSION); ?>';
</script>
<?php include("footer.php");?>
</body>
</html>