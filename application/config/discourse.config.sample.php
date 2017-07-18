<<<<<<< HEAD
<?php
if(!isset($config)) {
	$config = array();
}

$config["discourse"]["api_key"] = "";
$config["discourse"]["url"] = "";
$config["discourse"]["protocol"] = "";
$config["discourse"]["user"] = "";
$config["discourse"]["base"] = "";
$config["discourse"]["allowed_categories"] = array(
  // Only the ID of the categories, go on administration.php.
);


?>
=======
<?php 

// TODO: Add a configuration pannel for the administrator to edit theses values.
$allowed_categories = array( // Add here the categories allowed for export.
  // "Ektek",
  // "CR - CN",
  //"Sandbox"
);

?>
>>>>>>> 59a0c5ec5df6f2672a2637e995657bf2a758b81f
