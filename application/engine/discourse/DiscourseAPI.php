<?php

/**
  * Discourse API client for PHP
  *
  * Expanded on original by DiscourseHosting
  *
  * @category  DiscourseAPI
  * @package   DiscourseAPI
  * @author    Original author DiscourseHosting <richard@discoursehosting.com>
  * Additional work, timolaine, richp10 and others..
  * @copyright 2013, DiscourseHosting.com
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2 
  * @link      https://github.com/richp10/discourse-api-php
  */

namespace richp10\discourseAPI;

class DiscourseAPI
{
    private $_protocol = 'http';
    private $_apiKey = null;
    private $_dcHostname = null;

    function __construct($dcHostname, $apiKey = null, $protocol='http')
    {
        $this->_dcHostname = $dcHostname;
        $this->_apiKey = $apiKey;
        $this->_protocol=$protocol;
    }

    private function _deleteRequest($reqString, $paramArray, $apiUser = 'system')
    {
        return $this->_getRequest($reqString, $paramArray, $apiUser, "DELETE" );
    }

    private function _getRequest($reqString, $paramArray = null, $apiUser = 'system', $HTTPMETHOD = "GET" )
    {
        if ($paramArray == null) {
            $paramArray = array();
        }
        $paramArray['api_key'] = $this->_apiKey;
        $paramArray['api_username'] = $apiUser;
        $paramArray['show_emails'] = 'true';
        $ch = curl_init();
        $url = sprintf(
            '%s://%s%s?%s',
            $this->_protocol, 
            $this->_dcHostname, 
            $reqString, 
            http_build_query($paramArray)
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTPMETHOD );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $body = curl_exec($ch);
        $rc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $resObj = new \stdClass();
        $resObj->http_code = $rc;
	// Only return valid json
        $json = json_decode($body);
	if (json_last_error() == JSON_ERROR_NONE) {
        	$resObj->apiresult = $json;
	} else {
		$resObj->apiresult = $body;
	}
        return $resObj;
    }

    private function _putRequest($reqString, $paramArray, $apiUser = 'system')
    {
        return $this->_putpostRequest($reqString, $paramArray, $apiUser, "PUT" );
    }

    private function _postRequest($reqString, $paramArray, $apiUser = 'system')
    {
        return $this->_putpostRequest($reqString, $paramArray, $apiUser, "POST" );
    }

    private function _putpostRequest($reqString, $paramArray, $apiUser = 'system', $HTTPMETHOD = "POST" )
    {
        $ch = curl_init();
        $url = sprintf(
            '%s://%s%s?api_key=%s&api_username=%s',
            $this->_protocol, 
            $this->_dcHostname, 
            $reqString, 
            $this->_apiKey, 
            $apiUser
        );
        curl_setopt($ch, CURLOPT_URL, $url);
	$query = '';
	if (isset($paramArray['group'])) {
        	foreach ($paramArray['group'] as $param => $value) {
           		$query .= $param.'='.$value .'&';
        	}
	} else {
        	foreach ($paramArray as $param => $value) {
           		$query .= $param.'='.$value .'&';
        	}
	}
        $query = trim($query, '&');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTPMETHOD );
        $body = curl_exec($ch);
        $rc = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
	$resObj = new \stdClass();
        $json = json_decode($body);
        if (json_last_error() == JSON_ERROR_NONE) {
                $resObj->apiresult = $json;
        } else {
                $resObj->apiresult = $body;
        }
        $resObj->http_code = $rc;
        return $resObj;
    }

     /**
     * group
     *
     * @param string $groupname     name of group to be created
     * @param string $usernames     users in the group
     *
     * @return mixed HTTP return code and API return object
     */
    function addGroup($groupname, $usernames = array(), $aliaslevel = 3, $visible = 'true', 
			$automemdomain = '', $automemretro = 'false', $title = '', 
			$primegroup = 'false', $trustlevel = '0' )
    {

	$groupId = $this->getGroupIdByGroupName($groupname);
        if ($groupId) {
            return false;
        }
        $params = array(
            'group' => array(
                'name' 					=> $groupname,
	 	'usernames'                             => implode(',', $usernames),
                'alias_level' 				=> $aliaslevel,
                'visible' 				=> $visible,
                'automatic_membership_email_domains' 	=> $automemdomain,
                'automatic_membership_retroactive' 	=> $automemretro,
                'title' 				=> $title,
                'primary_group' 			=> $primegroup,
                'grant_trust_level' 			=> $trustlevel
            )
        );
        return $this->_postRequest('/admin/groups', $params);
    }

    function removeGroup($groupname)
    {
        $groupId = $this->getGroupIdByGroupName($groupname);
        if (!$groupId) {
            return false;
         } else {
         	return $this->_deleteRequest('/admin/groups/'.$groupId);
         }
     }

     /**
     * Edit Category
     * 
     * @param integer $catid
     * @param string  $allow_badges
     * @param string  $auto_close_based_on_last_post
     * @param string  $auto_close_hours	
     * @param string  $background_url	
     * @param string  $color
     * @param string  $contains_messages
     * @param string  $email_in	
     * @param string  $email_in_allow_strangers
     * @param string  $logo_url	
     * @param string  $name	
     * @param integer $parent_category_id	
     * @param integer $position	
     * @param string  $slug	
     * @param string  $suppress_from_homepage
     * @param string  $text_color
     * @param string  $topic_template	
     * @param array   $permissions 
     * @return mixed HTTP return code and API return object
     */
     function updatecat($catid, $allow_badges='true',$auto_close_based_on_last_post='false', $auto_close_hours='',$background_url,$color='0E76BD',
		$contains_messages='false', $email_in='', $email_in_allow_strangers='false', $logo_url='', $name='', $parent_category_id='', 
		$groupname, $position='', $slug='', $suppress_from_homepage='false', $text_color='FFFFFF', $topic_template='', $permissions )
     {
        $params = array(
		'allow_badges'			=> $allow_badges,
		'auto_close_based_on_last_post'	=> $auto_close_based_on_last_post,
		'auto_close_hours'		=> $auto_close_hours,
		'background_url'		=> $background_url,
		'color'				=> $color,
		'contains_messages'		=> $contains_messages,
		'email_in'			=> $email_in,
		'email_in_allow_strangers'	=> $email_in_allow_strangers,
		'logo_url'			=> $logo_url,
		'name'				=> $name,
		'parent_category_id'		=> $parent_category_id,
		'position'			=> $position,
		'slug'				=> $slug,
		'suppress_from_homepage'	=> $suppress_from_homepage,
		'text_color'			=> $text_color,
		'topic_template'		=> $topic_template
        );

	# Add the permissions - this is an array of group names and integer permission values. 
	if (sizeof($permissions > 0)) {
		foreach ($permissions as $key => $value)	{
		 	$params['permissions['.$key.']'] = $permissions[$key] ; 
		}
	}

	# This must PUT
	return $this->_putRequest('/categories/'.$catid, $params );
     }

     /**
     * getCategories
     *
     * @return mixed HTTP return code and API return object
     */
    
    function getCategories()
    {
        return $this->_getRequest("/categories.json");
    }

    function getPostsByNumber( $topic_id, $post_number )
    {
	return $this->_getRequest("/posts/by_number/".$topic_id.'/'.$post_number.'.json');
    }

    /**
     * getGroups
     *
     * @return mixed HTTP return code and API return object
     */

    function getGroups()
    {
        return $this->_getRequest("/admin/groups.json");
    }

    /**
     * getGroupMembers
     *
     * @param string $group         name of group
     * @return mixed HTTP return code and API return object
     */

    function getGroupMembers($group)
    {
        return $this->_getRequest("/groups/{$group}/members.json");
    }

    /**
     * createUser
     *
     * @param string $name         name of new user
     * @param string $userName     username of new user
     * @param string $emailAddress email address of new user
     * @param string $password     password of new user
     *
     * @return mixed HTTP return code and API return object
     */

    function createUser($name, $userName, $emailAddress, $password)
    {
        $obj = $this->_getRequest('/users/hp.json');
        if ($obj->http_code != 200) {
            return false;
        }

        $params = array(
            'name' => $name,
            'username' => $userName,
            'email' => $emailAddress,
            'password' => $password,
            'challenge' => strrev($obj->apiresult->challenge),
            'password_confirmation' => $obj->apiresult->value
        );

        return $this->_postRequest('/users', $params);
    }

    /**
     * activateUser
     *
     * @param integer $userId      id of user to activate
     *
     * @return mixed HTTP return code 
     */

    function activateUser($userId)
    {
        return $this->_putRequest("/admin/users/{$userId}/activate", array());
    }

    /**
     * getUsernameByEmail
     *
     * @param string $email     email of user
     *
     * @return mixed HTTP return code and API return object
     */

    function getUsernameByEmail($email)
    {
        $users = $this->_getRequest("/admin/users/list/active.json?filter=".urlencode($email));
        foreach($users->apiresult as $user) {
            if($user->email === $email) {
                return $user->username;
            }
        }
	
        return false;
    }

     /**
     * getUserByUsername
     *
     * @param string $userName     username of user
     *
     * @return mixed HTTP return code and API return object
     */

    function getUserByUsername($userName)
    {
        return $this->_getRequest("/users/{$userName}.json");
    }

    /**
     * createCategory
     *
     * @param string $categoryName name of new category
     * @param string $color        color code of new category (six hex chars, no #)
     * @param string $textColor    optional color code of text for new category
     * @param string $userName     optional user to create category as
     *
     * @return mixed HTTP return code and API return object
     */

    function createCategory($categoryName, $color, $textColor = '000000', $userName = 'system')
    {
        $params = array(
            'name' => $categoryName,
            'color' => $color,
            'text_color' => $textColor
        );
        return $this->_postRequest('/categories', $params, $userName);
    }

    /**
     * createTopic
     *
     * @param string $topicTitle   title of topic
     * @param string $bodyText     body text of topic post
     * @param string $categoryName category to create topic in
     * @param string $userName     user to create topic as
     * @param string $replyToId    post id to reply as
     *
     * @return mixed HTTP return code and API return object
     */

    function createTopic($topicTitle, $bodyText, $categoryId, $userName, $replyToId = 0) 
    {
        $params = array(
            'title' => $topicTitle,
            'raw' => $bodyText,
            'category' => $categoryId,
            'archetype' => 'regular',
            'reply_to_post_number' => $replyToId,
        );
        return $this->_postRequest('/posts', $params, $userName);
    }

     function getCategory($categoryName) {
	return $this->_getRequest("/c/{$categoryName}.json");	
     }

    /**
     * getTopic
     *
     */

    function getTopic($topicId) {
	return $this->_getRequest("/t/{$topicId}.json");
    }

    /**
     * createPost
     *
     * NOT WORKING YET
     */

    function createPost($bodyText, $topicId, $userName)
    {
        $params = array(
            'raw' => $bodyText,
            'archetype' => 'regular',
            'topic_id' => $topicId
        );
        return $this->_postRequest('/posts', $params, $userName);
    }

    /**
     * UpdatePost
     *
     */

    function updatePost($bodyhtml, $post_id, $userName='system')
    {
	$bodyraw = htmlspecialchars_decode( $bodyhtml );
        $params = array(
            'post[cooked]' => $bodyhtml,
            'post[edit_reason]' => '',
            'post[raw]' => $bodyraw
        );
        return $this->_putRequest('/posts/'.$post_id, $params, $userName);
    }

    function inviteUser($email, $topicId, $userName = 'system')
    {
        $params = array(
            'email' => $email,
            'topic_id' => $topicId
        );
        return $this->_postRequest('/t/'.intval($topicId).'/invite.json', $params, $userName);
    }

    function changeSiteSetting($siteSetting, $value)
    {
        $params = array($siteSetting => $value);
        return $this->_putRequest('/admin/site_settings/' . $siteSetting, $params);
    }

     /**
     * getUserByEmail
     *
     * @param string $email     email of user
     *
     * @return mixed user object
     */

    function getUserByEmail($email)
    {
        $users = $this->_getRequest("/admin/users/list/active.json", array('filter' => $email));
        foreach($users->apiresult as $user) {
            if(strtolower($user->email) === strtolower($email)) {
                return $user;
            }
        }
	
        return false;
    }

    /*
    * getGroupIdByGroupName
    *
    * @param string $groupname    name of group
    *
    * @return mixed id of the group, or false if nonexistent
    */

    function getGroupIdByGroupName($groupname)
    {
        $obj = $this->getGroups();
        if ($obj->http_code != 200) {
            return false;
        }

        foreach($obj->apiresult as $group) {
            if($group->name === $groupname) {
                $groupId = intval($group->id);
                break;
            }
            $groupId = false;
        }

	return $groupId;
    }

     /**
     * joinGroup 
     * @param string $groupname    name of group
     * @param string $username     user to add to the group
     *
     * @return mixed HTTP return code and API return object
     */

    function joinGroup($groupname, $username)
    {
	$groupId = $this->getGroupIdByGroupName($groupname);
        if (!$groupId) {
	    return false;
         } else {
            $params = array(
                'usernames' => $username
            );
         return $this->_putRequest('/groups/' . $groupId . '/members', $params);
         }
     }

    function leaveGroup($groupname, $username)
    {
        $userid=$this->getUserByUsername($username)->apiresult->user->id;
        $groupId = $this->getGroupIdByGroupName($groupname);
        if (!$groupId) {
            return false;
         } else {
            $params = array(
                'user_id' => $userid
            );
         return $this->_deleteRequest('/groups/'.$groupId.'/members.json', $params);
	}
     }


     /**
     * topTopics
     * @param string $category    slug of category
     * @param string $period      daily, weekly, monthly, yearly
     *
     * @return mixed HTTP return code and API return object
     */

    function topTopics($category, $period = 'daily')
    {
         return $this->_getRequest('/c/'.$category.'/l/top/'.$period.'.json');
     }

     /**
     * latestTopics
     * @param string $category    slug of category
     *
     * @return mixed HTTP return code and API return object
     */

    function latestTopics($category )
    {
         return $this->_getRequest('/c/'.$category.'/l/latest.json');
     }


}

