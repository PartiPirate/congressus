<?php /*
    Copyright 2015 Cédric Levieux, Parti Pirate

    This file is part of Gamifier.

    Gamifier is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Gamifier is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Gamifier.  If not, see <https://www.gnu.org/licenses/>.
*/

class GamifierClient {
	var $url = null;

	function __construct($url) {
		$this->url = $url;
	}

	static function newInstance($url) {
		return new GamifierClient($url);
	}

	function setNoticed($notices) {
		return $this->_send("setNoticed", array("notices" => $notices));
	}

	function addEvents($events) {
		return $this->_send("addEvents", array("events" => $events));
	}

	function getUserInformation($userUuid, $serviceUuid, $serviceSecret) {
		return $this->_send("getUserInformation", array("user_uuid" => $userUuid, "service_uuid" => $serviceUuid, "service_secret" => $serviceSecret));
	}

	function getBadges($serviceUuid, $serviceSecret) {
		return $this->_send("getBadges", array("service_uuid" => $serviceUuid, "service_secret" => $serviceSecret));
	}

    function _send($method, $request) {
    	$request = array("request" => json_encode($request));

        $getUrl = $this->url . "?method=$method";
 
		//url-ify the data for the POST
		$fieldsString = http_build_query($request);

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data, and say that we want the result returnd not printed
		curl_setopt($ch, CURLOPT_URL, $getUrl);
		curl_setopt($ch, CURLOPT_POST, count($request));
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

//		echo $result;

		// json decode the result, the api has json encoded result
		$result = json_decode($result, true);

		return $result;
	}
}