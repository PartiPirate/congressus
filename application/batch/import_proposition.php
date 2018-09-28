<?php

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once("config/database.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/bo/AgendaBo.php");

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection, $config);
$agendaBo = AgendaBo::newInstance($connection, $config);


print_r($argv);

//exit();

// Argv 1 : file
// Argv 2 : agenda point id
// Argv 3 : title
// Argv 4 ... x : propositions

$agendaPointId = $argv[2];
$title = $argv[3];

$propositions = array();

for($index = 4; $index < count($argv); $index++) {
    $propositions[] = $argv[$index];
}

$fh = fopen($argv[1], "r");

if ($fh) {

    echo "File open";

    while (($line = fgets($fh)) !== false) {
	echo "Title : $title";
	echo "\n";

        $description = trim(utf8_decode($line));

        echo $description;
	echo "\n";

	$motion = array();
	$motion["mot_agenda_id"] = $agendaPointId;
	$motion["mot_title"] = "$title";
	$motion["mot_description"] = "$description";
	$motion["mot_type"] = "yes_no";
	$motion["mot_status"] = "voting";
	$motion["mot_deleted"] = "0";
	$motion["mot_win_limit"] = "50";

	$motionBo->save($motion);

	$motionId = $motion["mot_id"];

	$agenda = $agendaBo->getById($agendaPointId);
	$agenda["age_objects"] = json_decode($agenda["age_objects"]);
	$agenda["age_objects"][] = array("motionId" => $motion[$motionBo->ID_FIELD]);
	$agenda["age_objects"] = json_encode($agenda["age_objects"]);
	$agendaBo->save($agenda);

	foreach($propositions as $proposition) {
            echo $proposition;
            echo "\n";

	    $prop = array();
	    $prop["mpr_motion_id"] = $motionId;
	    $prop["mpr_label"] = $proposition;
	    
	    $motionBo->saveProposition($prop);
	}


	$prop = array();
	$prop["mpr_motion_id"] = $motionId;
	$prop["mpr_label"] = "NSPP";
	$prop["mpr_neutral"] = 1;

	$motionBo->saveProposition($prop);
    }
    if (!feof($fh)) {
        echo "Erreur: fgets() a échoué\n";
    }
    fclose($fh);
}


?>
