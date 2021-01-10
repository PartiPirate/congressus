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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

function createDiscourseTopic($discourseApi, $title, $content, $category, $username) {

//	$content .= "\n\nLongueur : " . strlen($content);

	$contentParts = splitDiscourseContent($content);

	$contentParts[0] = sanitizeDiscourseContent($contentParts[0]);
	$response = $discourseApi->createTopic($title, $contentParts[0], $category, $username, 0);
	
	if (!isset($response->apiresult->topic_id)) return null;

	$postId = $response->apiresult->id;
	$topicId = $response->apiresult->topic_id;

	foreach($contentParts as $index => $contentPart) {
		if ($index == 0) continue; // we already have created the topic

		createDiscoursePost($discourseApi, $contentPart, $topicId, $username);
	}

	return $topicId;
//		$response = $discourseApi->createPost($chat["cha_text"], $motion["mot_external_chat_id"], $discourseUser->username);
}

function createDiscoursePost($discourseApi, $content, $topicId, $username) {
	// make stuff
	
	$content = sanitizeDiscourseContent($content);
	$response = $discourseApi->createPost($content, $topicId, $username);

	return $response;
}

function sanitizeDiscourseContent($content) {
	// the problem is the %

	$content = str_replace("%", " Pourcents", $content);

	return $content;
}

function splitDiscourseContent($content) {
	$contentParts = array();
	$cuttingLength = 30000;

	if (strlen($content) < $cuttingLength) {
		$contentParts[] = $content; // for the moment
	}
	else {
		$subContent = substr($content, 0, $cuttingLength);
		$cutIndex = strrpos($subContent, "\n#");

		if ($cutIndex === false) {
			$cutIndex = strrpos($subContent, "\n");

			if ($cutIndex === false) {
				$cutIndex = strrpos($subContent, " ");

				if ($cutIndex === false) {
					$cutIndex = $cuttingLength;
				}
			}
		}
		else {
			$cutIndex++; // we skip the \n
		}

		$contentParts[] = substr($content, 0, $cutIndex);
		$subParts = splitDiscourseContent(substr($content, $cutIndex));

		$contentParts = array_merge($contentParts, $subParts);
	}

	return $contentParts;
}

?>