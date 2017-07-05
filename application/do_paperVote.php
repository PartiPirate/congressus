<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

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

include_once("config/database.php");
require_once("engine/bo/MotionBo.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/qr/phpqrcode.php");
require_once("language/language.php");
include_once("mpdf/mpdf.php");

$connection = openConnection();

$motionBo = MotionBo::newInstance($connection, $config);

$votes = $_REQUEST["votes"];

$votesData = gzencode($votes, 9);
$votesData = base64_encode($votesData);

$votes = json_decode($votes, true);

$data = array();
//$data["gzip"] = $votesData;
//$data["votes"] = $votes;

// Create qr-code
$qrcode = tempnam(sys_get_temp_dir(), 'qr_code_');
QRCode::png($votesData, $qrcode, QR_ECLEVEL_M, 2);

ob_start();

?>
<html>
<head>
<style>

.indent {
	padding-left: 20px;
}

.notice .powers {
	text-align: right;
}

.motion {
	border: 1px solid black;
	border-radius: 5px;
	padding: 5px;
	margin: 5px 0 5px 0;
}

.motion .title, .notice .label {
	font-size: 18px;
}

.motion .description {
	font-style: italic;
}

.proposition {
}

.winning {
	color: green;
}

.motion-winning {
	border-color: green;
}

.motion-losing {
	border-color: red;
}

</style>
</head>
<body>

<img src="<?php echo $qrcode; ?>">

<?php

foreach($votes as $motionId => $vote) {

	?><div class="motion"><?php

	$motions = $motionBo->getByFilters(array("mot_id" => $motionId));
	$sortedMotions = array();
	
	foreach($motions as $motion) {
		if(!isset($sortedMotions[$motion["mot_id"]])) {
			$sortedMotions[$motion["mot_id"]] = $motion;
			$sortedMotions[$motion["mot_id"]]["propositions"] = array();
		}
	
		$sortedMotions[$motion["mot_id"]]["propositions"][] = $motion;
	}
	
	$motion = $motions[0];
	$motion = $sortedMotions[$motion["mot_id"]];

	?>
	<h2><?php echo $motion["mot_title"]; ?>  (<?php echo lang("motion_ballot_majority_" . $motion["mot_win_limit"]); ?>)</h2>
	<h3><?php echo $motion["mot_description"]; ?></h3>
	
	<?php
	
		if ($motion["mot_win_limit"] == -1) {
			arsort($vote);
		}
	
		foreach($vote as $propositionId => $voteData) {
			foreach($motion["propositions"] as $proposition) {
				
//				echo $propositionId;
//				echo " " . print_r($proposition, true);
//				echo "<br>";
				
				if ($proposition["mpr_id"] != $propositionId) continue;
			?>
			<h4><?php echo $proposition["mpr_label"]; ?>
				<?php 	if ($motion["mot_win_limit"] == -2) { ?>
					: <?php echo lang("motion_majorityJudgment_" . $voteData); ?>
				<?php	} 
						else if ($motion["mot_win_limit"] > 0 && ($voteData > 1 || count($vote) > 1)) { ?>
					: <?php echo $voteData; ?>
				<?php 	} ?>
			</h4>
			<?php

			}
		}
	?>	
	</div>
	<?php
}

?>
</body>
<?php

$content = ob_get_contents();

ob_end_clean();


//echo $content;

$mpdf = new mPDF();
$mpdf->WriteHTML($content);

$content = $mpdf->Output('', 'S');

// Delete qr-code png file
unlink($qrcode);

/*
$filename = "ballot.pdf";

header("Content-type:application/pdf");
header("Content-Disposition:inline;filename='$filename");
header('Content-Length: '.strlen( $content ));

echo $content;
*/
$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)  );
$ballotFilename = "ballots/" . $uuid . ".pdf";

$data["uuid"] = $uuid;


$result = file_put_contents($ballotFilename, $content, FILE_USE_INCLUDE_PATH);
/*
$handle = fopen($ballotFilename, "w", true);
if (!$handle) {
	echo "Impossible d'ouvrir le fichier ($ballotFilename)";
}
echo $handle;

$result = fwrite($handle, $content, strlen($content));
fclose($handle);
*/
//echo $ballotFilename;
//echo "#" . $result . "#";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>