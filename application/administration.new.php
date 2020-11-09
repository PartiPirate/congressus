<?php /*
    Copyright 2015 Cédric Levieux, Parti Pirate

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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["administrator"])) {
	$data["ko"] = "not_enough_rights";
	echo json_encode($data, JSON_NUMERIC_CHECK);
	exit();
}

@include_once("config/config.php");
@include_once("config/mail.config.php");
@include_once("config/discourse.config.php");
@include_once("config/mediawiki.config.php");
@include_once("config/style.config.php");

include_once("header.php");
include_once("config/discourse.structure.php");

$directoryHandler = dir("config/configurators/");

$entries = array();
while(($fileEntry = $directoryHandler->read()) !== false) {
	if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
		$entries[] = $fileEntry;
//		require_once("config/configurators/" . $fileEntry);
	}
}
$directoryHandler->close();

asort($entries);

foreach($entries as $fileEntry) {
	require_once("config/configurators/" . $fileEntry);
}

//print_r($configurators);

function getConfigValue($config, $path) {
	$parts = explode("/", $path);

	$value = $config;
	foreach($parts as $part) {
		if (!isset($value[$part])) {
			return null;
		}
		$value = $value[$part];
	}

	return $value;
}

?>

<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li class="active"><?=lang("breadcrumb_administration")?></li>
	</ol>

	<div class="well well-sm">
		<p><?=lang("administration_guide")?></p>
	</div>

	<br />

<div class="col-md-2">
	
<ul id="form-nav" class="nav nav-pills nav-stacked nav-tabs" role="tablist">
<?php	foreach($configurators as $cindex => $configurator) {
			foreach($configurator["panels"] as $pindex => $panel) { ?>
				<li role="presentation" class="<?=((($cindex + 1) * ($pindex + 1)) == 1) ? "active" : ""?>"><a 
				href="#<?=$panel["id"]?>-panel" id="<?=$panel["id"]?>-link" role="tab" data-toggle="tab"><?=lang($panel["label"])?></a></li>
<?php		}
		} ?>
</ul>	
	
</div>
<div class="col-md-10 tab-content">

<?php	foreach($configurators as $cindex => $configurator) {
			foreach($configurator["panels"] as $pindex => $panel) { ?>

		<div id="<?=$panel["id"]?>-panel" role="tabpanel" class="panel panel-default tab-pane <?=((($cindex + 1) * ($pindex + 1)) == 1) ? "active" : ""?>" data-file="<?=$configurator["file"]?>" data-panel="<?=$panel["id"]?>">
			<form id="<?=$panel["id"]?>-form" class="form-horizontal">
			<input type="hidden" name="file" value="<?=$configurator["file"]?>">
			<input type="hidden" name="panel" value="<?=$panel["id"]?>">
			<div class="panel-heading">

				<a disabled-data-toggle="collapse" data-target="#<?=$panel["id"]?>-panel-body" class="disabled-collapsed" href="#"><?=lang($panel["label"])?></a>

<?php			if (isset($panel["toggle"])) { ?>
				<input id="<?=$panel["toggle"]["id"]?>" name="<?=$panel["toggle"]["id"]?>" type="checkbox" <?=getConfigValue($config, $panel["toggle"]["path"]) ?  "checked='checked'" : ""?>  
					data-toggle="toggle" data-size="mini" data-height="10">
<?php			} ?>					
			</div>
			<div class="panel-body panel-collapse disabled-collapse " id="<?=$panel["id"]?>-panel-body">

				<div class="form-group">

<?php			foreach($panel["fields"] as $field) { 

					if ($field["type"] == "separator") {
						echo "</div><div class=\"form-group\">";
						continue;
					}

					if ($field["type"] == "content") { ?>

					<div class="col-md-6">
						<?=$field["content"]?>
					</div>
<?php
						continue;
					}
?>
					<label class="col-md-2 control-label" for="<?=$field["id"]?>"><?=lang($field["label"])?></label>

<?php				if ($field["type"] == "text" || $field["type"] == "number") { ?>

					<div class="col-md-<?=(isset($field["width"]) ? $field["width"] : 4)?>">
						<input id="<?=$field["id"]?>" name="<?=$field["id"]?>" type="<?=$field["type"]?>" value="<?=getConfigValue($config, $field["path"])?>"  class="form-control input-md">
					</div>

<?php				}
					else if ($field["type"] == "text_wide") { ?>

					<div class="col-md-<?=(isset($field["width"]) ? $field["width"] : 4)?>">
						<textarea class="form-control autogrow" id="<?=$field["id"]?>" name="<?=$field["id"]?>" style="<?=isset($field["minHeight"]) ? "min-height: " . $field["minHeight"] .";" : ""?>"><?=getConfigValue($config, $field["path"])?></textarea>
					</div>

<?php				}
					else  if ($field["type"] == "select") { ?>

					<div class="col-md-<?=(isset($field["width"]) ? $field["width"] : 4)?>">
						<select id="<?=$field["id"]?>" name="<?=$field["id"]?>" class="form-control">
<?php					foreach($field["values"] as $value)	{ ?>
							<option <?=($value["value"] == getConfigValue($config, $field["path"])) ?  "selected='selected'" : ""?> 
								value="<?=$value["value"]?>"><?=isLanguageKey($value["label"]) ? lang($value["label"]) : $value["label"]?></option>
<?php 					} ?>
						</select>
					</div>

<?php				}
					else  if ($field["type"] == "file") { 
						$directory = dir($field["upload"]);
						$entries = array("" => "");
						while (false !== ($entry = $directory->read())) {
//							echo "$entry \n";
							if (is_file($field["upload"] . $entry)) {
								$entries[] = $entry;
							}
						}
						$directory->close();
						sort($entries);
?>
					<div class="col-md-<?=(isset($field["width"]) ? ceil($field["width"]) / 2 : 2)?>">
						<select id="<?=$field["id"]?>" name="<?=$field["id"]?>" class="form-control">
<?php					foreach($entries as $value)	{ ?>
							<option <?=($value == getConfigValue($config, $field["path"])) ?  "selected='selected'" : ""?> 
								value="<?=$value?>"><?=$value?></option>
<?php 					} ?>
						</select>
					</div>
					<div class="col-md-<?=(isset($field["width"]) ? floor($field["width"]) / 2 : 2)?>">
							<input id="<?=$field["id"]?>-file" name="<?=$field["id"]?>-file" type="file"  class="form-control input-md">
						</select>
					</div>

<?php				}
					else  if ($field["type"] == "checkboxes" || $field["type"] == "tree") { ?>
					<div class="col-md-<?=(isset($field["width"]) ? $field["width"] : 4)?>">
<?php					$checkboxIndex = -1;
						foreach($field["values"] as $index => $value)	{ 
							$checkboxIndex++;
?>
						<label for="<?=str_replace("[]", "_" . $checkboxIndex, $field["id"])?>" class="checkbox-inline">
							<input id="<?=str_replace("[]", "_" . $checkboxIndex, $field["id"])?>" name="<?=$field["id"]?>" 
								<?=(in_array($value["value"], getConfigValue($config, $field["path"]))) ?  "checked='checked'" : ""?>
								type="checkbox" value="<?=$value["value"]?>">
							<?=isLanguageKey($value["label"]) ? lang($value["label"]) : $value["label"]?>
						</label>

<?php						if (isset($value["values"]) || $field["type"] == "tree") {

?>						<div id="sub_checkboxes_<?=$checkboxIndex?>" class="sub_checkboxes"> <?php

								foreach($value["values"] as $index => $ivalue)	{ 
									$checkboxIndex++;
?>
							<label for="<?=str_replace("[]", "_" . $checkboxIndex, $field["id"])?>" class="checkbox-inline">
								<input id="<?=str_replace("[]", "_" . $checkboxIndex, $field["id"])?>" name="<?=$field["id"]?>" 
									<?=(in_array($ivalue["value"], getConfigValue($config, $field["path"]))) ?  "checked='checked'" : ""?>
									type="checkbox" value="<?=$ivalue["value"]?>">
								<?=isLanguageKey($ivalue["label"]) ? lang($ivalue["label"]) : $ivalue["label"]?>
							</label>
<?php 							} ?>
						</div>
<?php						}?>
<?php 					} ?>
					</div>
<?php				}
					else  if ($field["type"] == "concat") { ?>
					<div class="col-md-<?=(isset($field["width"]) ? $field["width"] : 4)?>">
<?php					$values = getConfigValue($config, $field["path"]);
						$value = trim(implode("\n", $values));?>
						<textarea class="form-control autogrow" style="min-height: 100px; max-height: 300px;" rows="10" id="<?=str_replace("[]", "_" . $index, $field["id"])?>" name="<?=$field["id"]?>"><?=$value?></textarea>
					</div>
<?php				}

				} ?>

				</div>


				<div class="text-center">

<?php			if (isset($panel["buttons"])) {
					foreach($panel["buttons"] as $button) { 

						if (!isset($button["input"])) { ?>

					<button id="<?=$button["id"]?>" class="btn btn-primary <?=$button["class"]?>" type="button" disabled="disabled"><?=lang($button["label"])?></button>

<?php					}
						else { ?>

					<div class="input-group">
				    	<span class="input-group-addon"><?=lang($button["input-label"])?></span>
				    	<input id="<?=$button["input"]?>" name="<?=$button["input"]?>" class="form-control" type="text">
						<div class="input-group-btn">
							<button id="btn-mail-test" type="button" class="btn btn-primary"><?=lang($button["label"])?></button>
					    </div>
				    </div>

<?php					}
					?>


<?php				}
				}
?>

					<button class="btn btn-success btn-save-configuration" type="button"><?=lang("common_save")?></button>

				</div>


			</div>
			</form>
		</div>


<?php		}
		} ?>

</div>

	<?php echo addAlertDialog("administration_save_successAlert", 				lang("administration_alert_ok"), "success"); ?>

	<?php echo addAlertDialog("administration_ping_successAlert", 				lang("administration_alert_ping_ok"), "success"); ?>
	<?php echo addAlertDialog("administration_ping_no_hostAlert", 				lang("administration_alert_ping_no_host"), "danger"); ?>
	<?php echo addAlertDialog("administration_ping_bad_credentialsAlert", 		lang("administration_alert_ping_bad_credentials"), "danger"); ?>
	<?php echo addAlertDialog("administration_ping_no_databaseAlert", 			lang("administration_alert_ping_no_database"), "warning"); ?>

	<?php echo addAlertDialog("administration_create_successAlert", 			lang("administration_alert_create_ok"), "success"); ?>
	<?php echo addAlertDialog("administration_deploy_successAlert", 			lang("administration_alert_deploy_ok"), "success"); ?>

	<?php echo addAlertDialog("administration_memcached_successAlert", 			lang("administration_alert_memcached_ok"), "success"); ?>
	<?php echo addAlertDialog("administration_memcached_no_hostAlert", 			lang("administration_alert_memcached_no_host"), "danger"); ?>

	<?php echo addAlertDialog("administration_mail_successAlert", 				lang("administration_alert_mail_ok"), "success"); ?>
	<?php echo addAlertDialog("administration_mail_bad_credentialsAlert", 		lang("administration_alert_mail_no_host"), "danger"); ?>

</div>

<div class="lastDiv"></div>
<script>
	if (typeof lang == "undefined") lang = {};
	lang.administration_alert_ok = <?=json_encode(lang("administration_alert_ok"))?>;
</script>
<?php include("footer.php"); ?>

</body>
</html>
