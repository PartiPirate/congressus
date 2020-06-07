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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class PersonaeClient {
	var $url = null;

	function __construct($url) {
		$this->url = $url;
	}

	static function newInstance($url) {
		return new PersonaeClient($url);
	}

	function getNoticePowers($themeId, $motion, $votes) {
		$parameters = array();
		
		$parameters["themeId"] = $themeId;
		$parameters["votes"] = json_encode($votes);
		$parameters["motion"] = json_encode($motion);
		
		$parameters["token"] = "456";
		$parameters["secret"] = "456";

		return $this->_send("do_getNoticePowers", null, $parameters);
	}

    function _send($method, $request = null, $parameters = null) {

		if ($request) {
			//url-ify the data for the POST
	    	$request = array("request" => json_encode($request));
		}
		else {
			$fieldsString = http_build_query($parameters);
		}

        $getUrl = $this->url . "?method=$method";
/*
		echo "---<br>\n";
		echo $getUrl;
		echo "\n---<br>\n";

		echo "---<br>\n";
		print_r($fieldsString);
		echo "\n---<br>\n";

		return array();
*/
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data, and say that we want the result returnd not printed
		curl_setopt($ch, CURLOPT_URL, $getUrl);
		curl_setopt($ch, CURLOPT_POST, count($request ? $request : $parameters));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		//execute post
		return $this->_exec($ch);
    }

	function _exec(&$ch) {
		// Execute request
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

/*
		echo "---<br>\n";
		echo $result;
		echo "\n---<br>\n";

		return array();
*/
		// json decode the result, the api has json encoded result
		$result = json_decode($result, true);

//		print_r($result);

		return $result;
	}
}
