<?php /*
    Copyright 2019 Cédric Levieux, Parti Pirate

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

require_once("config/config.php");
require_once("config/mail.config.php");
require_once("config/discourse.config.php");
require_once("config/mail.php");
require_once("engine/discourse/DiscourseAPI.php");

function notify($contactType, $contacts, $templates, $data) {
	switch($contactType) {
		case "mail":
		    notifyByMail($contacts, $templates, $data);
			break;
		case "discourse_group":
            notifyByDiscourseGroup($contacts, $templates, $data);
            break;
		case "discourse_category":
		    notifyByDiscourseCategory($contacts, $templates, $data);
			break;
	}
}

function notifyByMail($contacts, $templates, $data) {
    $subject = evaluateTemplate($templates[0], $data);
    $message = evaluateTemplate($templates[1], $data);

    global $config;

    foreach($contacts as $contact) {
    	$noticeMail = getMailInstance();
    
    	$noticeMail->setFrom($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
    	$noticeMail->addReplyTo($config["smtp"]["from.address"], $config["smtp"]["from.name"]);
    
    	$noticeMail->Subject = subjectEncode($subject);
    	$noticeMail->msgHTML(str_replace("\n", "<br>\n", utf8_decode($message)));
    	$noticeMail->AltBody = utf8_decode($noticeMailMessage);
    
    	$noticeMail->addAddress($contact);
    					
    	$noticeMail->SMTPDebug = 2;
    
//    	echo "Will be sent\n";
    
    	if ($noticeMail->send()) {
//    		echo "Mail sent\n";
    	}
    }
}

function notifyByDiscourseGroup($contacts, $templates, $data) {
    global $config;

    $discourseApi = new pnoeric\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

    $subject = evaluateTemplate($templates[0], $data);
    $message = evaluateTemplate($templates[1], $data);

	$result = $discourseApi->createPM($subject, $message, $contacts, $config["discourse"]["user"], 0);
}

function notifyByDiscourseCategory($contacts, $templates, $data) {
    global $config;

    $discourseApi = new pnoeric\DiscourseAPI($config["discourse"]["url"], $config["discourse"]["api_key"], $config["discourse"]["protocol"]);

	$categories = $discourseApi->getSite()->apiresult->categories;

    $subject = evaluateTemplate($templates[0], $data);
    $message = evaluateTemplate($templates[1], $data);

    foreach($contacts as $contact) {
    	$categorieNames = explode(">", $contact);
    	$parentId = null;
    
    	foreach($categorieNames as $categoryName) {
    		foreach($categories as $category) {
    			if (($category->name == $categoryName || $category->slug == $categoryName) && (($parentId && $parentId == $category->parent_category_id) || (!$parentId && !isset($category->parent_category_id)))) {
    				$parentId = $category->id;
    			}
    		}
    	}

    	if ($parentId) {
    		$result = $discourseApi->createTopic($subject, $message, $parentId, $config["discourse"]["user"], 0);

//        	print_r($result);
    	}
    }
}    
    
function evaluateTemplate($template, $data) {
	ob_start();
    extract($data);
	include($template);
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

?>