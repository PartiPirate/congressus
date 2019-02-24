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

if (isset($lang)) {
    $lang = array();
}

function changeCharset($array) {
	foreach($array as $key => $value) {
		$array[$key] = utf8_decode($value);
	}

	return $array;
}

function getLanguage() {
	if (isset($_SESSION["language"]) && $_SESSION["language"]) {
		return $_SESSION["language"];
	}

	global $config;

	if (isset($config["default_language"]) && $config["default_language"]) {
		return $config["default_language"];
	}

    return "fr";
}

function isLanguageKey($key, $language = null, $path = null) {
	global $lang;

    if (!$path) {
        $path = dirname(realpath(dirname(__FILE__))) . "/";
    }

	if (!$language) {
		$language = getLanguage();
	}

    $language = strtolower(substr($language, 0, 2));

//    echo("Call for $key in $language around $path");

	if (!$lang || !count($lang)) {
		$directoryHandler = dir($path . "language/" . $language);
		while(($fileEntry = $directoryHandler->read()) !== false) {
			if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
				include("language/" . $language . "/" . $fileEntry);
			}
		}
		$directoryHandler->close();

		// Uncomment if you're not in UTF-8
		// $lang = changeCharset($lang);
	}

	if (array_key_exists($key, $lang)) {
		return true;
	}

	return false;
}

/**
 * returns array All translations for a given key
 */
function langs($key, $path = "") {
    global $lang;

    $langs = array();
    $languages = array();

	$directoryHandler = dir($path . "language/");
	
	while(($fileEntry = $directoryHandler->read()) !== false) {
		if($fileEntry != '.' && $fileEntry != '..' && is_dir($path . "language/" . $fileEntry)) {
		    $languages[] = $fileEntry;
		}
	}
	$directoryHandler->close();

    foreach($languages as $language) {
        $lang = array();

    	$directoryHandler = dir($path . "language/" . $language);
    	while(($fileEntry = $directoryHandler->read()) !== false) {
    		if($fileEntry != '.' && $fileEntry != '..' && strpos($fileEntry, ".php")) {
    			include("language/" . $language . "/" . $fileEntry);
    		}
    	}
		if (isset($lang[$key])) {
		    $langs[] = $lang[$key];
		}
    }

    $lang = array();

    return $langs;
}

function lang($key, $htmlencode = true, $language = null, $path = "") {
    global $lang;

    if (isLanguageKey($key, $language, $path)) {
    	$text = $lang[$key];

    	if ($htmlencode) {
	    	$text = htmlentities($text, ENT_NOQUOTES);
	    	$text = htmlspecialchars_decode($text);
    	}

        return $text;
    }

    return '$lang["'.$key.'"] = "";';
}

function langFormat($condition, $trueKey, $falseKey, $args = array(), $htmlencode = true, $language = null, $path = "") {
    $result = $condition ? lang($trueKey, $htmlencode, $language, $path) : lang($falseKey, $htmlencode, $language, $path);
    
    foreach($args as $key => $value) {
		$result = str_replace("{" . $key . "}", "$value", $result);
	}
	
	return $result;
}

function dateTranslate($date) {
    $date = str_replace("Sunday", lang("Sunday"), $date);
    $date = str_replace("Monday", lang("Monday"), $date);
    $date = str_replace("Tuesday", lang("Tuesday"), $date);
    $date = str_replace("Wednesday", lang("Wednesday"), $date);
    $date = str_replace("Thursday", lang("Thursday"), $date);
    $date = str_replace("Friday", lang("Friday"), $date);
    $date = str_replace("Saturday", lang("Saturday"), $date);

    $date = str_replace("January", lang("January"), $date);
    $date = str_replace("February", lang("February"), $date);
    $date = str_replace("March", lang("March"), $date);
    $date = str_replace("April", lang("April"), $date);
    $date = str_replace("May", lang("May"), $date);
    $date = str_replace("June", lang("June"), $date);
    $date = str_replace("July", lang("July"), $date);
    $date = str_replace("August", lang("August"), $date);
    $date = str_replace("September", lang("September"), $date);
    $date = str_replace("October", lang("October"), $date);
    $date = str_replace("November", lang("November"), $date);
    $date = str_replace("December", lang("December"), $date);
    
    return $date;
}
?>