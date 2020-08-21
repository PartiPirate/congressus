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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!isset($api)) exit();

$data = array();

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

$directoryHandler = dir("config/configurators/");

while(($fileEntry = $directoryHandler->read()) !== false) {
	if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
		require_once("config/configurators/" . $fileEntry);
	}
}
$directoryHandler->close();

//print_r($arguments);
//echo "\n-------\n";

$fileContent = <<<'EOD'
<?php
if(!isset($config)) {
	$config = array();
}


EOD;

if (file_exists("config/" . $arguments["file"])) {
	require_once("config/" . $arguments["file"]);
}
else {
	$config = array();
}

foreach($configurators as $cindex => $configurator) {
	if ($configurator["file"] == $arguments["file"]) {

//		print_r($config);
//		echo "\n-------\n";

		foreach($configurator["panels"] as $pindex => $panel) {

			if (isset($panel["toggle"])) {
				$panel["toggle"]["type"] = "boolean";
				$panel["fields"][] = $panel["toggle"];
			}

			foreach($panel["fields"] as $findex => $field) {

				if ($field["type"] == "separator") continue;
				if ($field["type"] == "content") continue;

				$isArray = (strpos($field["id"], "[]") !== false);

				if ($panel["id"] == $arguments["panel"]) {
					$key = str_replace("[]", "", $field["id"]);

//					echo $key . " " . $isArray . "\n";

					if (isset($arguments[$key])) {
						$value = $arguments[$key];

						if ($field["type"] == "boolean") {
							$value = "true";
						}
						else if ($field["type"] == "concat") {
							$value = explode("\n", $value[0]);
						}
						
					}
					else if ($isArray) {
						$value = array();
					}
					else if ($field["type"] == "boolean") {
						$value = "false";
					}
					else {
						$value = "";
					}
				}
				else {
					$value = getConfigValue($config, $field["path"]);
				}

				$parts = explode("/", $field["path"]);

				$fileContent .= "\$config";

				foreach($parts as $part) {
					$fileContent .= "[\"";
					$fileContent .= $part;
					$fileContent .= "\"]";
				}

				$fileContent .= " = ";

				if (is_numeric($value) || $field["type"] == "boolean") {
					$fileContent .= $value;
				}
				else if ($field["type"] == "text" || $field["type"] == "select") {
					$fileContent .= "\"";
					$fileContent .= str_replace("\"", "\"\"", $value);
					$fileContent .= "\"";
				}
				else if ($isArray) {
					$fileContent .= "[";
					
					$values = $value ? $value : array();
					$separator = "";

					foreach($values as $cvalue) {
						$fileContent .= $separator;
						if (is_numeric($cvalue)) { // || $field["type"] == "boolean") {
							$fileContent .= $cvalue;
							$separator = ", ";
						}
						else {
							$fileContent .= "\"";
							$fileContent .= str_replace("\"", "\"\"", $cvalue);
							$fileContent .= "\"";
							$separator = ",\n";
						}
					}

					$fileContent .= "]";
				}
				else {
					$fileContent .= json_encode($value ? $value : ($isArray ? array() : ""));
				}

				$fileContent .= ";\n";
			}

		}
	}
}

$fileContent .= <<<'EOD'

?>

EOD;

//echo $fileContent;

if (file_exists("config/" . $arguments["file"])) {
	if (file_exists("config/" . $arguments["file"] . "~")) {
		unlink("config/" . $arguments["file"] . "~");
	}
	rename("config/" . $arguments["file"], "config/" . $arguments["file"] . "~");
}
file_put_contents("config/" . $arguments["file"], $fileContent);

$data["ok"] = "ok";
$data["file"] = $arguments["file"];
$data["content"] = $fileContent;

//echo "\n-------\n";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>