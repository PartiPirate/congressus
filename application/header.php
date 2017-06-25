<?php /*
	Copyright 2014-2015 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("config/database.php");
include_once("language/language.php");
include_once("engine/utils/bootstrap_forms.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/utils/FormUtils.php");
include_once("engine/utils/LogUtils.php");

require_once("engine/utils/GamifierClient.php");

$gamifierClient = null;
if (isset($config["gamifier"]["url"])) {
	$gamifierClient = GamifierClient::newInstance($config["gamifier"]["url"]);
}

xssCleanArray($_REQUEST, true);
xssCleanArray($_GET, true);
xssCleanArray($_POST, true);

addLog($_SERVER, $_SESSION);

// $user = SessionUtils::getUser($_SESSION);
// $userId = SessionUtils::getUserId($_SESSION);

$isConnected = false;
$isAdministrator = false;
$sessionUserId = 0;
$hasUnnoticed = false;
$gamifiedUser = null;

if (SessionUtils::getUserId($_SESSION)) {
	$sessionUser = SessionUtils::getUser($_SESSION);
	$sessionUserId = SessionUtils::getUserId($_SESSION);

	if ($gamifierClient) {
		$gamifiedUser = $gamifierClient->getUserInformation(sha1($config["gamifier"]["user_secret"] . $sessionUserId), $config["gamifier"]["service_uuid"], $config["gamifier"]["service_secret"]);
	
		foreach($gamifiedUser["data"]["badges"] as $userBadge) {
	//		print_r($userBadge);
			if (!$userBadge["noticed"]) {
				$hasUnnoticed = true;
				break;
			}
		}
	}

	$isConnected = true;
}

if (isset($_SESSION["administrator"]) && $_SESSION["administrator"]) {
	$isAdministrator = true;
}

$language = SessionUtils::getLanguage($_SESSION);

$page = $_SERVER["SCRIPT_NAME"];
if (strrpos($page, "/") !== false) {
	$page = substr($page, strrpos($page, "/") + 1);
}
$page = str_replace(".php", "", $page);

if ($page == "administration" && !$isAdministrator) {
	header('Location: index.php');
}

$connection = openConnection();

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo lang("congressus_title"); ?></title>

<!-- Bootstrap -->

<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet" />
<link href="assets/css/ekko-lightbox.min.css" rel="stylesheet">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
<!--
<link href="assets/css/min.css.php" rel="stylesheet">
-->
 
<link href="assets/css/jquery.template.css" rel="stylesheet" />
<link href="assets/css/jquery-ui.min.css" rel="stylesheet" />
<link href="assets/css/opentweetbar.css" rel="stylesheet" />
<link href="assets/css/calendar.min.css" rel="stylesheet" />
<link href="assets/css/flags.css" rel="stylesheet" />
<link href="assets/css/social.css" rel="stylesheet" />
<link href="assets/css/style.css" rel="stylesheet" />
<link href="assets/css/font-awesome.min.css" rel="stylesheet">

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script>
var gamifiedUser = <?php echo ($gamifiedUser ? json_encode($gamifiedUser["data"]) : "{badges:[]}"); ?>;
</script>
<link rel="shortcut icon" type="image/png" href="favicon.png" />
</head>
<body>
	<nav class="navbar navbar-inverse" role="navigation">
		<div class="container-fluid">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#otb-navbar-collapse">
					<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php"><img src="assets/images/logo.svg"
					alt="Logo Congressus" 
					style="position: relative; top: -14px; width: 48px; height: 48px; background-color: #ffffff;"
					data-toggle="tooltip" data-placement="bottom"
					title="Congressus" /> </a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="otb-navbar-collapse">
				<ul class="nav navbar-nav">
					<li <?php if ($page == "index") echo 'class="active"'; ?>><a href="index.php"><?php echo lang("menu_index"); ?><?php if ($page == "index") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<li <?php if ($page == "decisions") echo 'class="active"'; ?>><a href="decisions.php"><?php echo lang("menu_decisions"); ?><?php if ($page == "decisions") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<?php 	if ($isConnected) {?>
					<li <?php if ($page == "myVotes") echo 'class="active"'; ?>><a href="myVotes.php"><?php echo lang("menu_myVotes"); ?><?php if ($page == "myVotes") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<li <?php if ($page == "createMeeting") echo 'class="active"'; ?>><a href="createMeeting.php"><?php echo lang("menu_createMeeting"); ?><?php if ($page == "createMeeting") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<li <?php if ($page == "myMeetings") echo 'class="active"'; ?>><a href="myMeetings.php"><?php echo lang("menu_myMeetings"); ?><?php if ($page == "myMeetings") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<?php 	} else {?>
					<?php 	}?>
					
					<?php 	if ($isAdministrator) {?>
					<li <?php if ($page == "administration") echo 'class="active"'; ?>><a href="administration.php"><?php echo lang("menu_administration"); ?><?php if ($page == "administration") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<?php 	} else {?>
					<li <?php if ($page == "groupMeetings") echo 'class="active"'; ?>><a href="groupMeetings.php"><?php echo lang("menu_groupMeetings"); ?><?php if ($page == "groupMeetings") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<?php 	}?>
				</ul>
				<ul class="nav navbar-nav navbar-right">

					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo str_replace("{language}", lang("language_$language"), lang("menu_language")); ?> <span
							class="caret"></span> </a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="do_changeLanguage.php?lang=en"><span class="flag en" title="<?php echo lang("language_en"); ?>"></span> <?php echo lang("language_en"); ?></a></li>
							<li><a href="do_changeLanguage.php?lang=fr"><span class="flag fr" title="<?php echo lang("language_fr"); ?>"></span> <?php echo lang("language_fr"); ?></a></li>
						</ul>
					</li>

					<?php 	if ($isConnected || $isAdministrator) {?>
					<?php 	if ($isConnected) {?>
					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo $sessionUser["pseudo_adh"]; ?> <span id="mybadgesInfoSpan" class="glyphicon glyphicon-tag text-info hidden"></span> <span
							class="caret"></span> </a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="mypreferences.php"><?php echo lang("menu_mypreferences"); ?></a></li>
<?php 	if ($gamifierClient) { ?>
							<li id="mybadgesLi"class=""><a href="myBadges.php"><?php echo lang("menu_mybadges"); ?></a></li>
<?php	} ?>
							<li class="divider"></li>
							<li><a class="logoutLink" href="do_logout.php"><?php echo lang("menu_logout"); ?></a></li>
						</ul>
					</li>
					<?php 	}?>
					<li><a class="logoutLink" href="do_logout.php" title="<?php echo lang("menu_logout"); ?>"
						data-toggle="tooltip" data-placement="bottom"><span class="glyphicon glyphicon-log-out"></span><span class="sr-only">Logout</span> </a></li>
					<?php 	} else { ?>
					<li><a id="loginLink" href="connect.php" title="<?php echo lang("menu_login"); ?>"
						data-toggle="tooltip" data-placement="left"><span class="glyphicon glyphicon-log-in"></span><span class="sr-only">Login</span> </a></li>
					<?php 	}?>
				</ul>
				<?php 	if ($isAdministrator) {?>
				<?php 	} else {?>
				<form action="search.php" class="navbar-form navbar-right" role="search">
					<div class="form-group">
						<input type="text" class="form-control" name="query" placeholder="Rechercher">
					</div>
					<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
				</form>
				<?php 	} ?>
			</div>
		</div>
	</nav>

	<a class="skip-main" href="#main">Skip to main content</a>