<?php /*/*
    Copyright 2018-2019 Cédric Levieux, Parti Pirate

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

$page = "activate";
include_once("header.php");
require_once("engine/bo/UserBo.php");
require_once("engine/utils/SessionUtils.php");

$connection = openConnection();

$userBo = UserBo::newInstance($connection, $config);

$activationStatus = true;
$mail = "";
$code = "";

if (!isset($_REQUEST["mail"])) {
	$activationStatus = false;
}
else {
	$mail = $_REQUEST["mail"];
}

if (!isset($_REQUEST["code"])) {
	$activationStatus = false;
}
else {
	$code = $_REQUEST["code"];
}

if ($activationStatus) {
	$activationStatus = $userBo->activate($mail, $code);
}

$activation = "default";
if ($activationStatus) {
	$activation = "success";
}
else {
	$activation = "danger";
}


?>
<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?> </a></li>
		<li class="active"><?php echo lang("breadcrumb_activation"); ?></li>
	</ol>

	<div class="well well-sm">
		<p>
			<?php echo lang("activation_guide"); ?>
		</p>
	</div>

	<div class="panel panel-<?php echo $activation; ?>">
		<div class="panel-heading">
			<?php echo lang("activation_title"); ?>
		</div>
		<div class="panel-body"><?php echo lang("activation_information_" . $activation); ?></div>
	</div>
</div>

<div class="lastDiv"></div>

<?php include("footer.php");?>
<script>
</script>
</body>
</html>
