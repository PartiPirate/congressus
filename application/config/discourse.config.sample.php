<?php
if(!isset($config)) {
	$config = array();
}

$config["discourse"]["exportable"] = false; // set to true to enable export with a discourse
$config["discourse"]["api_key"] = "";
$config["discourse"]["url"] = "";
$config["discourse"]["protocol"] = "";
$config["discourse"]["user"] = "";
$config["discourse"]["base"] = "";
$config["discourse"]["allowed_categories"] = array(
  // Only the ID of the categories, go on administration.php.
);


?>
