<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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
*/
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("install/Installer.php");

include_once("config/database.php");
include_once("language/language.php");
require_once("engine/bo/GaletteBo.php");
require_once("engine/bo/UserPropertyBo.php");
require_once("engine/bo/MeetingBo.php");

include_once("engine/utils/bootstrap_forms.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/utils/FormUtils.php");
include_once("engine/utils/LogUtils.php");
include_once("engine/utils/DateTimeUtils.php");

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

$userProperties = array();
if ($sessionUserId) {
	$userPropertyBo = UserPropertyBo::newInstance($connection, $config);
	$userProperties = $userPropertyBo->getByFilters(array("upr_user_id" => $sessionUserId));
}

function getUserPropertyValue($property) {
	global $userProperties;
	
	foreach($userProperties as $userProperty) {
		if ($userProperty["upr_property"] == $property) {
			return $userProperty["upr_value"];
		}
	}
	
	return null;
}

if ((basename($_SERVER["SCRIPT_FILENAME"])== "meeting.php") OR basename($_SERVER["SCRIPT_FILENAME"])== "construction.php" OR basename($_SERVER["SCRIPT_FILENAME"])== "export_discourse.php") {
	require_once("engine/bo/MeetingBo.php");

	$meetingBo = MeetingBo::newInstance($connection, $config);
	$meetingId = intval($_REQUEST["id"]);
	
	$meeting = null;
	
	if ($meetingId != 0) {
		$meeting = $meetingBo->getById($meetingId, true);
	}
	
	if (!$meeting) {
		header("Location: createMeeting.php");
		exit();
	}
	
	if (basename($_SERVER["SCRIPT_FILENAME"])== "meeting.php" && $meeting["mee_type"] == "construction") {
		header("Location: construction.php?id=" . $meetingId);
		exit();
	}
	else if (basename($_SERVER["SCRIPT_FILENAME"])== "construction.php" && $meeting["mee_type"] == "meeting") {
		header("Location: meeting.php?id=" . $meetingId);
		exit();
	}
}
else if (basename($_SERVER["SCRIPT_FILENAME"])== "construction_motion.php") {
	require_once("engine/bo/MotionBo.php");
	require_once("engine/bo/AgendaBo.php");

	$motionBo = MotionBo::newInstance($connection, $config);
	$agendaBo = AgendaBo::newInstance($connection, $config);

	$motionId = intval($_REQUEST["motionId"]);
	$motion = $motionBo->getByFilters(array("with_meeting" => true, "mot_id" => $motionId));
	
	if (count($motion)) {
		$motion = $motion[0];
		$meeting = $motion;

		if ($meeting["mee_type"] == "meeting") {
			header("Location: meeting.php?id=" . $meeting["mee_id"]);
			exit();
		}
		
	//	print_r($motion);
		$agendas = $agendaBo->getByFilters(array("age_id" => $motion["mot_agenda_id"]));
		
		if (count($agendas)) {
			$agenda = $agendas[0];

			if ($agenda["age_parent_id"]) { 
				$parentAgenda = $agendaBo->getById($agenda["age_parent_id"]);
				$parentMotionId = str_replace("amendments-", "", $agenda["age_label"]);
				$parentMotion = $motionBo->getById($parentMotionId);
			}
		}
	}
	
	if (!$motion) {
		header("Location: createMeeting.php");
		exit();
	}

}

$page_title = lang("congressus_title");
$page_description = lang("index_description");

if (isset($meeting)) {
	$page_title .= " : " . $meeting['mee_label'];

	$start = new DateTime($meeting["mee_datetime"]);

	$date = lang("datetime_format");

	$date = str_replace("{date}", @$start->format(lang("date_format")), $date);
	$date = str_replace("{time}", @$start->format(lang("time_format")), $date);

	$page_description = $date;
}

if (isset($motion)) {
	$page_description = "Motion &quot;" . str_replace("\"", "&quot;", $motion["mot_title"]) . "&quot;";

	if (isset($parentMotion)) {
		$page_description .= ", amendement de &quot;" . str_replace("\"", "&quot;", $parentMotion["mot_title"]) . "&quot;";
	}

	if (isset($_REQUEST["chatId"])) {
		require_once("engine/bo/ChatBo.php");
		$chatBo = ChatBo::newInstance($connection, $config);

		$chats = $chatBo->getByFilters(array("cha_id" => intval($_REQUEST["chatId"]), "cha_motion_id" => $motion["mot_id"]));

		if (count($chats)) {
			$descriptionChat = $chats[0];
			$page_description .= "\n";
			$page_description .= "&laquo;&nbsp;";
			$page_description .= str_replace("\"", "&quot;", mb_strlen($descriptionChat["cha_text"]) < 60 ? $descriptionChat["cha_text"] : mb_substr($descriptionChat["cha_text"], 0, 60));
			$page_description .= "&nbsp;&raquo;";
		}
	}
}

$meetingBo = MeetingBo::newInstance($connection, $config);

$filters = array();
$filters["with_status"] = array("open");

if ($connection) {
	$meetings = $meetingBo->getByFilters($filters);
}
else {
	$meetings = array();
}

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $page_title;?></title>

<?php $current_url = $config["server"]["base"] . $_SERVER["REQUEST_URI"]; ?>
<!-- Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="<?php echo $current_url; ?>" />
<meta property="og:title" content="Parti Pirate <?php echo $page_title;?>" />
<meta property="og:description" content="<?php echo $page_description;?>" />
<meta property="og:image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta property="og:locale" content="fr_FR" />
<meta property="og:locale:alternate" content="en_US" />
<meta property="fb:page_id" content="partipiratefr" />
<!-- Google +1 -->
<meta itemprop="name" content="Parti Pirate <?php echo $page_title;?>" />
<meta itemprop="description" content="<?php echo $page_description;?>" />
<meta itemprop="image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta itemprop="author" content="farlistener" />
<!-- Twitter -->
<meta name="twitter:site" content="@partipirate" />
<meta name="twitter:creator" content="@farlistener" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:url" content="<?php echo $current_url; ?>" />
<meta name="twitter:title" content="Parti Pirate <?php echo $page_title;?>" />
<meta name="twitter:description" content="<?php echo $page_description;?>" />
<meta name="twitter:image" content="<?php echo $config["server"]["base"]; ?>assets/images/logo_voile_fond.png" />
<meta name="twitter:image:alt" content="Logo de Congressus" />

<link href="favicon.ico" rel="shortcut icon"/>

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
<link href="assets/css/bootstrap-toggle.css" rel="stylesheet">
<link href="assets/css/bootstrap-markdown.min.css" rel="stylesheet">
<link href="assets/css/emojione.helper.css" rel="stylesheet">

<?php
$themeProperty = getUserPropertyValue("theme");

if ($themeProperty) {	?>
<link href="themes/<?php echo $themeProperty; ?>/css/style.css" rel="stylesheet">
<?php
} ?>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="assets/js/jquery-1.11.1.min.js"></script>
<script>
// Badges;
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
				<a class="navbar-brand" href="index.php"><img src="assets/images/logo_voile_fond.svg"
					alt="Logo Congressus"
					style="position: relative; top: -14px; width: 48px; height: 48px; "
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
					<?php 	}?>
					
					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" id="meetings-menu"><?php echo lang("menu_meetings"); ?> <span
							class="caret"></span> </a>
						<ul class="dropdown-menu" role="menu" aria-labelledby="meetings-menu">
					<?php 	if ($isConnected) {?>
							<li <?php if ($page == "createMeeting") echo 'class="active"'; ?>><a href="createMeeting.php"><?php echo lang("menu_createMeeting"); ?><?php if ($page == "createMeeting") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
							<li <?php if ($page == "myMeetings") echo 'class="active"'; ?>><a href="myMeetings.php"><?php echo lang("menu_myMeetings"); ?><?php if ($page == "myMeetings") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
					<?php 	}?>
							<li <?php if ($page == "groupMeetings") echo 'class="active"'; ?>><a href="groupMeetings.php"><?php echo lang("menu_groupMeetings"); ?><?php if ($page == "groupMeetings") echo ' <span class="sr-only">(current)</span>'; ?></a></li>

							<li style="display: none;" role="separator" class="divider export-divider"></li>

							<li style="display: none;" class="export-li"><a href="#" data-template="html"		class="btnShowExport export-link"><?php echo lang("export_html"); ?></a></li>
							<li style="display: none;" class="export-li"><a href="#" data-template="pdf"		class="btnShowExport export-link"><?php echo lang("export_pdf"); ?></a></li>
							<li style="display: none;" class="export-li"><a href="#" data-template="markdown"	class="btnShowExport export-link"><?php echo lang("export_wiki"); ?></a></li>
							<li style="display: none;" class="export-li"><a href="#" data-template="discourse"	class="btnShowExport export-link"><?php echo lang("export_discourse"); ?></a></li>

							<li role="separator" class="divider"></li>
							
							<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" id="openMeetings-menu"><?php echo lang("menu_openMeetings"); ?> <span
									class="caret"></span> </a>
								<ul class="dropdown-menu" role="menu" aria-labelledby="openMeetings-menu">

<?php							foreach($meetings as $openMeeting) { ?>
									<li><a href="meeting.php?id=<?php echo $openMeeting["mee_id"]; ?>"><?php echo $openMeeting["mee_label"] ? $openMeeting["mee_label"] : "-"; ?></a></li>
<?php							} ?>

								</ul>
							</li>

						</ul>
					</li>
			
					<?php 	if ($isAdministrator) {?>
					<li <?php if ($page == "administration") echo 'class="active"'; ?>><a href="administration.php"><?php echo lang("menu_administration"); ?><?php if ($page == "administration") echo ' <span class="sr-only">(current)</span>'; ?></a></li>
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
					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><img src="getAvatar.php" class="img-circle" style="max-width: 32px; max-height: 32px; margin: -20px 0 -20px 0;" 
								 data-toggle="tooltip" data-placement="top" title="<?php echo GaletteBo::showIdentity($sessionUser); ?>"><?php echo GaletteBo::showIdentity($sessionUser); ?> <span id="mybadgesInfoSpan" class="glyphicon glyphicon-tag text-info hidden"></span> <span
							class="caret"></span> </a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="mypreferences.php"><?php echo lang("menu_mypreferences"); ?></a></li>
<?php 	if ($gamifierClient) { ?>
							<li id="mybadgesLi" class=""><a href="myBadges.php"><?php echo lang("menu_mybadges"); ?></a></li>
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
