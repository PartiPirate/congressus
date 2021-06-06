<?php
/**
 * Discourse API client for PHP
 *
 * This package has been knocking around for years, with many changes from many people...
 *
 * @category     DiscourseAPI
 * @package      DiscourseAPI
 * @author       DiscourseHosting <richard@discoursehosting.com>
 * @author       richp10 https://github.com/richp10
 * @author       timolaine https://github.com/timolaine
 * @author       vinkashq https://github.com/vinkashq
 * @author       pnoeric htps://github.com/pnoeric
 * @copyright    2013, DiscourseHosting.com
 * @license      http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @link         https://github.com/pnoeric/discourse-api-php
 **/

namespace pnoeric;

use CURLFile;
use DateTime;
use Exception;
use stdClass;

use function is_int;

class DiscourseAPI {
	/**
	 * @var string
	 */
	private $_protocol;

	/**
	 * @var string
	 */
	private $_apiKey;

	/**
	 * @var string
	 */
	private $_discourseHostname;

	/**
	 * @var string secret key for SSO
	 */
	protected $sso_secret;

	/**
	 * @var bool flag: dump get requests to console?
	 */
	private $debugGetRequest = false;

	/**
	 * @var bool flag: dump put/post requests to console?
	 */
	private $debugPutPostRequest = false;

	/**
	 * DiscourseAPI constructor.
	 *
	 * @param string $discourseHostname
	 * @param null   $apiKey
	 * @param string $protocol
	 */
	public function __construct( string $discourseHostname, $apiKey = null, $protocol = 'https' ) {
		$this->_discourseHostname = $discourseHostname;
		$this->_apiKey            = $apiKey;
		$this->_protocol          = $protocol;
	}


	////////////////  Groups

	/**
	 * getGroups
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getGroups() {
		return $this->_getRequest( '/groups.json' );
	}

	/**
	 * getGroup
	 *
	 * @param string $groupName name of group
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */

	public function getGroup( $groupName ) {
		return $this->_getRequest( '/groups/' . $groupName . '.json' );
	}

	/**
	 * add a user to a group
	 *
	 * @param string $groupName name of group
	 * @param string $userName  user to add to the group
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function joinGroup( string $groupName, string $userName ) {
		$groupId = $this->getGroupIdByGroupName( $groupName );
		if ( ! $groupId ) {
			return false;
		}

		$params = [
			'usernames' => $userName,
		];

		return $this->_putRequest( '/groups/' . $groupId . '/members.json', [ $params ] );
	}

	/*
	 * getGroupIdByGroupName
	 *
	 * @param string $groupName    name of group
	 *
	 * @return mixed id of the group, or false if nonexistent
	 */
	public function getGroupIdByGroupName( string $groupName ) {
		$obj = $this->getGroup( $groupName );
		if ( $obj->http_code !== 200 ) {
			return false;
		}

		return $obj->apiresult->group->id;
	}

	/**
	 * remove user from a group
	 *
	 * @param $groupName
	 * @param $userName
	 *
	 * @return bool|stdClass
	 * @throws Exception
	 */
	public function leaveGroup( $groupName, $userName ) {
		$userid  = $this->getUserByUsername( $userName )->apiresult->user->id;
		$groupId = $this->getGroupIdByGroupName( $groupName );
		if ( ! $groupId ) {
			return false;
		}
		$params = [
			'user_id' => $userid,
		];

		return $this->_deleteRequest( '/groups/' . $groupId . '/members.json', [ $params ] );
	}

	/**
	 * get all members of a group
	 *
	 * @param string $group name of group
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getGroupMembers( $group ) {
		return $this->_getRequest( "/groups/{$group}/members.json" );
	}

	/**
	 * create a group and add users
	 *
	 * @param string $groupName name of group to be created
	 * @param array  $userNames users in the group
	 *
	 * @param int    $aliasLevel
	 * @param string $isVisible
	 * @param string $autoMembershipDomains
	 * @param string $autoMembershipRetroactiveFlag
	 * @param string $title
	 * @param string $primaryGroup
	 * @param int    $grantTrustLevel
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 * @noinspection MoreThanThreeArgumentsInspection
	 *
	 */
	public function addGroup(
		string $groupName,
		array $userNames = [],
		int $aliasLevel = 3,
		string $isVisible = 'true',
		string $autoMembershipDomains = '',
		string $autoMembershipRetroactiveFlag = 'false',
		string $title = '',
		string $primaryGroup = 'false',
		int $grantTrustLevel = 0
	) {
		$groupId = $this->getGroupIdByGroupName( $groupName );

		// if group already exists, get outta here
		if ( $groupId ) {
			return false;
		}

		$params = [
			'group' => [
				'name'                               => $groupName,
				'usernames'                          => implode( ',', $userNames ),
				'alias_level'                        => $aliasLevel,
				'visible'                            => $isVisible,
				'automatic_membership_email_domains' => $autoMembershipDomains,
				'automatic_membership_retroactive'   => $autoMembershipRetroactiveFlag,
				'title'                              => $title,
				'primary_group'                      => $primaryGroup,
				'grant_trust_level'                  => $grantTrustLevel,
			],
		];

		return $this->_postRequest( '/admin/groups', $params );
	}

	/**
	 * delete a group entirely
	 *
	 * @param string $groupName
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function removeGroup( string $groupName ) {
		$groupId = $this->getGroupIdByGroupName( $groupName );

		if ( ! $groupId ) {
			throw new Exception( "Can't find group Id from group name $groupName" );
		}

		return $this->_deleteRequest( '/admin/groups/' . (string) $groupId, [] );
	}

     /**
     * getSite
     *
     * @return mixed HTTP return code and API return object
     */

    function getSite()
    {
        return $this->_getRequest("/site.json");
    }

	/** @noinspection MoreThanThreeArgumentsInspection * */
	/**
	 * createCategory
	 *
	 * @param string $categoryName name of new category
	 * @param string $color        color code of new category (six hex chars, no #)
	 * @param string $textColor    optional color code of text for new category
	 * @param string $userName     optional user to create category as
	 *
	 * @return mixed HTTP return code and API return object
	 *
	 * @throws Exception
	 */
	public function createCategory(
		string $categoryName,
		string $color,
		string $textColor = '000000',
		string $userName = 'system'
	) {
		$params = [
			'name'       => $categoryName,
			'color'      => $color,
			'text_color' => $textColor,
		];

		return $this->_postRequest( '/categories', [ $params ], $userName );
	}

	/**
	 * ignore/unignore a user
	 *
	 * @param string   $userName
	 * @param string   $userNameToIgnore
	 * @param DateTime $timespan
	 * @param bool     $ignore
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function ignoreUnignoreUser( string $userName, string $userNameToIgnore, DateTime $timespan, bool $ignore = true ) {
		$params = [
			'notification_level' => ( $ignore ) ? 'ignore' : 'normal',
		];

		// if we are ignoring the user, add the time span (up to 4 months)
		if ( $ignore ) {
			$params['expiring_at'] = substr( $timespan->format( 'c' ), 0, 10 );
		}

		return $this->_putRequest( '/u/' . $userNameToIgnore . '/notification_level.json', [ $params ],
		                           $userName );
	}

	/**
	 * get info on a single category - by category ID only
	 *
	 * @param int|string $categoryIdOrSlug
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function getCategory( $categoryIdOrSlug ): stdClass {
		return $this->_getRequest( "/c/{$categoryIdOrSlug}.json" );
	}

	/** @noinspection MoreThanThreeArgumentsInspection * */
	/**
	 * Edit Category
	 *
	 * @param int        $categoryId
	 * @param string     $background_url
	 * @param array      $permissions
	 *
	 * @param string     $allow_badges
	 * @param string     $autoCloseBasedOnLastPost
	 * @param string     $autoCloseHours
	 * @param string     $color
	 * @param string     $containsMessages
	 * @param string     $emailIn
	 * @param string     $emailInAllowStrangers
	 * @param string     $logoUrl
	 * @param string     $name
	 * @param int|string $parentCategory
	 * @param int|string $position
	 * @param string     $slug
	 * @param string     $supressFromHomepage
	 * @param string     $textColor
	 * @param string     $topicTemplate
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function editCategory(
		int $categoryId,
		string $background_url,
		array $permissions,
		string $allow_badges = 'true',
		string $autoCloseBasedOnLastPost = 'false',
		string $autoCloseHours = '',
		string $color = '0E76BD',
		string $containsMessages = 'false',
		string $emailIn = '',
		string $emailInAllowStrangers = 'false',
		string $logoUrl = '',
		string $name = '',
		$parentCategory = 0,        // doesn't have a type since it can be int or string
		$position = '',             // unclear if this can be int or string?
		string $slug = '',
		string $supressFromHomepage = 'false',
		string $textColor = 'FFFFFF',
		string $topicTemplate = ''
	) {
		$params = [
			'allow_badges'                  => $allow_badges,
			'auto_close_based_on_last_post' => $autoCloseBasedOnLastPost,
			'auto_close_hours'              => $autoCloseHours,
			'background_url'                => $background_url,
			'color'                         => $color,
			'contains_messages'             => $containsMessages,
			'email_in'                      => $emailIn,
			'email_in_allow_strangers'      => $emailInAllowStrangers,
			'logo_url'                      => $logoUrl,
			'name'                          => $name,
			'parent_category_id'            => $parentCategory,
			'position'                      => $position,
			'slug'                          => $slug,
			'suppress_from_homepage'        => $supressFromHomepage,
			'text_color'                    => $textColor,
			'topic_template'                => $topicTemplate,
		];

		// Add the permissions - this is an array of group names and integer permission values.
		if ( count( $permissions ) > 0 ) {
			foreach ( $permissions as $key => $value ) {
				$params[ 'permissions[' . $key . ']' ] = $permissions[ $key ];
			}
		}

		return $this->_putRequest( '/categories/' . $categoryId, [ $params ] );
	}

	/**
	 * get all the categories
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getCategories() {
		return $this->_getRequest( '/categories.json' );
	}

	/**
	 * log out user - by username
	 *
	 * @param string $userName username of new user
	 *
	 * @return mixed HTTP return code and API return object
	 *
	 * @throws Exception
	 * @deprecated please use logoutUserByUsername() or logoutUserById() instead
	 *
	 */
	public function logoutUser( string $userName ) {
		return $this->logoutUserByUsername( $userName );
	}

	/**
	 * set user's info
	 * see https://github.com/discourse/discourse_api/blob/master/lib/discourse_api/api/users.rb#L32
	 *
	 * :name, :title, :bio_raw, :location, :website, :profile_background, :card_background,
	 * :email_messages_level, :mailing_list_mode, :homepage_id, :theme_ids, :user_fields
	 *
	 * @param string $userName note this can not be a discourse user ID
	 * @param array  $params   params to set
	 *
	 * @return stdClass HTTP return code and API return object
	 *
	 * @throws Exception
	 */
	public function setUserInfo( string $userName, array $params ): stdClass {
		return $this->_putRequest( '/u/' . $userName . '.json', [ $params ], $userName );
	}

	/**
	 * update trust level
	 *
	 * @param int $userId
	 * @param int $trustLevel
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function setUserTrustLevel( int $userId, int $trustLevel ) {
		$params['level'] = $trustLevel;

		return $this->_putRequest( '/admin/users/' . $userId . '/trust_level', [ $params ] );
	}

	/**
	 * get user trust level
	 *
	 * @param int $userId
	 * @param int $trustLevel
	 *
	 * @return int|null trust level, or null if we couldn't get it
	 * @throws Exception
	 */
	public function getUserTrustLevel( int $userId ) {
		$res = $this->getUserByDiscourseId( $userId );

		$tl = null;

		if ( is_object( $res ) ) {
			$tl = $res->apiresult->trust_level;
		}

		return $tl;
	}

	/**
	 * log out user - by username
	 *
	 * @param string $userName username of new user
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function logoutUserByUsername( string $userName ) {
		$discourseUserId = $this->getUserByUsername( $userName )->apiresult->user->id;

		return $this->logoutUserById( $discourseUserId );
	}

	/**
	 * log out user - by user ID
	 *
	 * @param int $userId
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function logoutUserById( int $userId ) {

		return $this->_postRequest( '/admin/users/' . $userId . '/log_out', [] );
	}

	/**
	 * unsuspend user - by user ID
	 *
	 * @param int $userId
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function unsuspendUserById( int $userId ) {
		return $this->_putRequest( '/admin/users/' . $userId . '/unsuspend', [] );
	}

	/**
	 * suspend user - by user ID
	 *
	 * @param int      $userId
	 * @param DateTime $until
	 * @param string   $reason
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function suspendUserById( int $userId, DateTime $until, string $reason ) {

		// format 'c' = 2004-02-12T15:19:21+00:00
		$date = substr( $until->format( 'c' ), 0, 10 );

		$params = [
			'suspend_until' => $date,
			'reason'        => $reason,
			'message'       => '',
			'post_action'   => 'delete',
		];

		return $this->_putRequest( '/admin/users/' . $userId . '/suspend', [ $params ] );
	}

	/**
	 * createUser
	 *
	 * @param string $name         name of new user
	 * @param string $userName     username of new user
	 * @param string $emailAddress email address of new user
	 * @param string $password     password of new user
	 * @param bool   $activate     activate user immediately (no confirmation email)?
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	public function createUser(
		string $name, string $userName, string $emailAddress, string $password, bool $activate = true
	) {

		// we need to call hp.json to get a challenge string
		$challengeStringObject = $this->_getRequest( '/users/hp.json' );
		if ( $challengeStringObject->http_code !== 200 ) {
			return false;
		}

		$params = [
			'name'                  => $name,
			'username'              => $userName,
			'email'                 => $emailAddress,
			'password'              => $password,
			'challenge'             => strrev( $challengeStringObject->apiresult->challenge ),
			'password_confirmation' => $challengeStringObject->apiresult->value,
			'active'                => $activate ? 'true' : 'false',
		];

		return $this->_postRequest( '/users', [ $params ] );
	}

	/**
	 * activateUser
	 *
	 * @param int $userId id of user to activate
	 *
	 * @return mixed HTTP return code
	 * @throws Exception
	 */
	public function activateUser( int $userId ) {
		return $this->_putRequest( "/admin/users/{$userId}/activate", [] );
	}

	/**
	 * getUsernameByEmail
	 *
	 * @param string $email email of user
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getUsernameByEmail( string $email ) {
		$users = $this->_getRequest( '/admin/users/list/active.json?filter=' . urlencode( $email ) );
		foreach ( $users->apiresult as $user ) {
			if ( strtolower( $user->email ) === strtolower( $email ) ) {
				return $user->username;
			}
		}

		return false;
	}

	/**
	 * getUserByUsername
	 * this returns user_options but not full record with SSO bits
	 *
	 * @param string $userName username of user
	 *
	 * @return mixed full HTTP return code and API return object
	 * @throws Exception
	 */
	public function getUserByUsername( string $userName ) {
		return $this->_getRequest( "/users/{$userName}.json" );
	}

	/**
	 * get discourse user by their internal ID -
	 * note that this returns FULL record, including single_sign_on_record
	 * but does not include user options
	 *
	 * @param int $userId discourse (non-external) ID
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getUserByDiscourseId( int $userId ) {
		return $this->_getRequest( "/admin/users/{$userId}.json" );
	}

	/**
	 * getUserByExternalID
	 *
	 * @param int $externalID external id of sso user
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	function getUserByExternalID( int $externalID ) {
		return $this->_getRequest( "/users/by-external/{$externalID}.json" );
	}

	/**
	 * getUserIdByExternalID
	 *
	 * @param int $externalID external id of sso user
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getDiscourseUserIdFromExternalId( int $externalID ) {
		$res = $this->getDiscourseUserFromExternalId( $externalID );

		if ( $res ) {
			return $res->id;
		}

		return false;
	}

	/**
	 * get a discourse user reocrd from their external ID - by default returns "full" record (from /admin)
	 *
	 * note that NON-ADMIN record includes "ignored_users"
	 * and the ADMIN version does NOT include "ignored_users" but does include the whole single_sign_on block (external_id)
	 *
	 * @param int  $externalID external id of sso user
	 *
	 * @param bool $getFullAdminRecord
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getDiscourseUserFromExternalId( int $externalID, bool $getFullAdminRecord = true ) {
		$res        = $this->_getRequest( "/users/by-external/{$externalID}.json" );
		$userObject = $res->apiresult->user;

		if ( $getFullAdminRecord && is_object( $res ) && $res->apiresult->user->id ) {
			// now call this to get the FULL record, with single_sign_on_record if there, but this drops ignored_users
			$res        = $this->getUserByDiscourseId( $res->apiresult->user->id );
			$userObject = $res->apiresult;
		}

		return $userObject;
	}

	/**
	 * invite a user to a topic
	 *
	 * @param        $email
	 * @param        $topicId
	 * @param string $userName
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function inviteUser( $email, $topicId, $userName = 'system' ): stdClass {
		$params = [
			'email'    => $email,
			'topic_id' => $topicId,
		];

		return $this->_postRequest( '/t/' . (int) $topicId . '/invite.json', [ $params ], $userName );
	}

	/**
	 * getUserByEmail
	 *
	 * @param string $email email of user
	 *
	 * @return mixed user object
	 * @throws Exception
	 */
	public function getUserByEmail( string $email ) {
		$users = $this->_getRequest( '/admin/users/list/active.json', [
			'filter' => $email,
		] );
		foreach ( $users->apiresult as $user ) {
			if ( strtolower( $user->email ) === strtolower( $email ) ) {
				return $user;
			}
		}

		return false;
	}

	/**
	 * getUserBadgesByUsername
	 *
	 * @param string $userName username of user
	 *
	 * @return mixed HTTP return code and list of badges for given user
	 * @throws Exception
	 */
	public function getUserBadgesByUsername( string $userName ) {
		return $this->_getRequest( "/user-badges/{$userName}.json" );
	}

	///////////////  POSTS

	/**
	 * createPost
	 *
	 * @param string   $bodyText
	 * @param int      $topicId
	 * @param string   $userName
	 * @param DateTime $createDateTime create date/time for the post
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function createPost( string $bodyText, int $topicId, string $userName, DateTime $createDateTime ): stdClass {
		$params = [
			'raw'       => $bodyText,
			'archetype' => 'regular',
			'topic_id'  => $topicId,
		];

		if ( $createDateTime ) {
			// Discourse likes ISO 8601 date/time format
			$params ['created_at'] = $createDateTime->format( 'c' );
		}

		return $this->_postRequest( '/posts', [ $params ], $userName );
	}

	/**
	 * getPostsByNumber
	 *
	 * @param $topic_id
	 * @param $post_number
	 *
	 * @return mixed HTTP return code and API return object
	 * @throws Exception
	 */
	public function getPostsByNumber( $topic_id, $post_number ) {
		return $this->_getRequest( '/posts/by_number/' . $topic_id . '/' . $post_number . '.json' );
	}

	/**
	 * UpdatePost
	 *
	 * @param        $bodyHtml
	 * @param        $post_id
	 * @param string $userName
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function updatePost( $bodyHtml, $post_id, $userName = 'system', $edit_reason = '' ): stdClass {
		$bodyRaw = htmlspecialchars_decode( $bodyHtml );
		$params  = [
			'post[cooked]'      => $bodyHtml,
			'post[edit_reason]' => $edit_reason,
			'post[raw]'         => $bodyRaw,
		];

		return $this->_putRequest( '/posts/' . $post_id, [ $params ], $userName );
	}

	/**
	 * createTopic
	 *
	 * this creates a new topic, then makes the first post in the topic, from the $userName with the $bodyText
	 *
	 * @param string   $topicTitle     title of topic
	 * @param string   $bodyText       body text of topic post
	 * @param string   $categoryId     must be Discourse category ID, can't be slug!
	 * @param string   $userName       user to create topic as
	 * @param int      $replyToId      optional: post id to reply as
	 * @param DateTime $createDateTime create datetime for topic
	 *
	 * @return mixed HTTP return code and API return object
	 *
	 * @throws Exception
	 * @noinspection MoreThanThreeArgumentsInspection
	 */
	public function createTopic(
		string $topicTitle,
		string $bodyText,
		string $categoryId,
		string $userName,
		int $replyToId = 0,
		DateTime $createDateTime = null
	) {

		if ( ! $categoryId ) {
			return false;
		}

		$params = [
			'title'                => $topicTitle,
			'raw'                  => $bodyText,
			'category'             => $categoryId,
			'archetype'            => 'regular',        // not a private_message
			'reply_to_post_number' => $replyToId,
		];

		if ( $createDateTime ) {
			// Discourse likes ISO 8601 date/time format
			$params ['created_at'] = $createDateTime->format( 'c' );
		}

		// https://docs.discourse.org/#tag/Topics/paths/~1posts.json/post
		return $this->_postRequest( '/posts', [ $params ], $userName );
	}

	/**
	 * get info on a topic - by topic ID or slug
	 *
	 * @param $topicIdOrSlug
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function getTopic( $topicIdOrSlug ): stdClass {
		return $this->_getRequest( "/t/{$topicIdOrSlug}.json" );
	}

	/**
	 * change site setting
	 *
	 * @param $siteSetting
	 * @param $value
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function changeSiteSetting( $siteSetting, $value ): stdClass {
		$params = [
			$siteSetting => $value,
		];

		return $this->_putRequest( '/admin/site_settings/' . $siteSetting, [ $params ] );
	}

	/**
	 * set email digests setting for a specific user
	 * (same as user prefs option "When I don’t visit here, send me an email summary of popular topics and replies")
	 *
	 * @param string $discourseUsername
	 * @param bool   $value
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function setEmailDigestUserSetting( string $discourseUsername, bool $value ): stdClass {
		$params['email_digests'] = $value ? 'true' : 'false';

		return $this->_putRequest( '/users/' . $discourseUsername . '.json', [ $params ] );
	}


	//////////////// Private Functions

	/** @noinspection MoreThanThreeArgumentsInspection */
	/**
	 * this is the helper function that handles all GET requests, it does all the heavy lifting
	 *
	 * @param string $requestString
	 * @param array  $paramArray
	 * @param string $apiUser
	 * @param string $httpMethod
	 *
	 * @return stdClass
	 *
	 * @throws Exception
	 */
	private function _getRequest(
		string $requestString,
		array $paramArray = [],
		string $apiUser = 'system',
		string $httpMethod = 'GET'
	): stdClass {

		// always set this flag so we get email addresses back
		// https://docs.discourse.org/#tag/Users%2Fpaths%2F~1admin~1users~1list~1%7Bflag%7D.json%2Fget
		$paramArray['show_emails'] = 'true';

		// set up headers for HTTP request we're about to make
		$headers = [
			'Api-Key: ' . $this->_apiKey,
			'Api-Username: ' . $apiUser,
		];

		// build URL for curl request
		$url = sprintf( '%s://%s%s?%s',
		                $this->_protocol,
		                $this->_discourseHostname, $requestString,
		                http_build_query( $paramArray ) );

		if ( $this->debugGetRequest ) {
			echo "\nDiscourse-API DEBUG: user '" . $apiUser . "' making $httpMethod request: $url \n";
		}

		return $this->_completeCurlCall( $url, $httpMethod, $headers );
	}

	/**
	 * internal function to complete CURL call
	 *
	 * @param string       $url
	 * @param string       $httpMethod
	 * @param array        $httpHeaders
	 * @param string|array $query
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function _completeCurlCall( string $url, string $httpMethod, array $httpHeaders, $query = '' ) {
		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $httpHeaders );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $httpMethod );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		// for PUT and POST, we have a query string
		if ( $query ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $query );
		}

		$body           = curl_exec( $ch );
		$httpResultCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		// build return object
		$res            = new stdClass();
		$res->http_code = $httpResultCode;

		$json           = json_decode( $body );
		$res->apiresult = ( json_last_error() !== JSON_ERROR_NONE ) ? $body : $json;

		// throw exceptions for common problems
		if ( $res->http_code == 429 ) {
			throw new Exception(
				"Rate limit on $httpMethod request: $url " . json_encode( $query ),
				429
			);
		}

		if ( $res->http_code == 404 ) {
			throw new Exception(
				"URL endpoint not found on $httpMethod request: $url " . json_encode( $query ),
				404
			);
		}

		return $res;
	}

	/** @noinspection MoreThanThreeArgumentsInspection * */
	/**
	 * @param string $requestString
	 * @param array  $paramArray
	 * @param string $apiUser
	 * @param string $httpMethod
	 *
	 * @return stdClass
	 *
	 * @throws Exception
	 */
	private function _putpostRequest(
		string $requestString,
		array $paramArray,
		string $apiUser = 'system',
		$httpMethod = 'POST'
	): stdClass {
		// see https://stackoverflow.com/questions/4007969/application-x-www-form-urlencoded-or-multipart-form-data
		$type = 'multipart/form-data';

		// special flag for upload file!
		if ( isset( $paramArray['uploadFile'] ) ) {
			// we are trying to upload a file, so we prepare the curl POSTFIELDS a little different
			// see http://code.iamkate.com/php/sending-files-using-curl/
			$query = [];
			unset( $paramArray['uploadFile'] );
			foreach ( $paramArray as $k => $v ) {
				$query[ $k ] = $v;
			}
		} else {
			// just build normal query, no uploaded files
			$query = '';
			if ( isset( $paramArray['group'] ) && is_array( $paramArray['group'] ) ) {
				$query = http_build_query( $paramArray );
			} else {
				if ( is_array( $paramArray[0] ) ) {
					foreach ( $paramArray[0] as $param => $value ) {
						$query .= $param . '=' . urlencode( $value ) . '&';
					}
				}
			}
			$query = trim( $query, '&' );
		}

		// set up headers for HTTP request we're about to make
		$headers = [
			// see https://stackoverflow.com/questions/4007969/application-x-www-form-urlencoded-or-multipart-form-data
			'Content-Type: ' . $type,
			'Api-Key: ' . $this->_apiKey,
			'Api-Username: ' . $apiUser,
		];

		$url = sprintf( '%s://%s%s',
		                $this->_protocol,
		                $this->_discourseHostname,
		                $requestString );

		if ( $this->debugPutPostRequest ) {
			echo "\nDiscourse-API DEBUG: user '" . $apiUser . "' making $httpMethod request: $url, " . json_encode( $paramArray ) . "\n";
		}

		return $this->_completeCurlCall( $url, $httpMethod, $headers, $query );
	}

	/**
	 * do HTTP DELETE
	 *
	 * @param string $reqString
	 * @param array  $paramArray
	 * @param string $apiUser
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function _deleteRequest( string $reqString, array $paramArray, string $apiUser = 'system' ): stdClass {
		return $this->_putpostRequest( $reqString, $paramArray, $apiUser, 'DELETE' );
	}

	/**
	 * do HTTP PUT
	 *
	 * @param string $reqString
	 * @param array  $paramArray
	 * @param string $apiUser
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function _putRequest( string $reqString, array $paramArray, string $apiUser = 'system' ): stdClass {
		return $this->_putpostRequest( $reqString, $paramArray, $apiUser, 'PUT' );
	}

	/**
	 * do HTTP POST
	 *
	 * @param string $reqString
	 * @param array  $paramArray
	 * @param string $apiUser
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function _postRequest( string $reqString, array $paramArray, string $apiUser = 'system' ): stdClass {
		return $this->_putpostRequest( $reqString, $paramArray, $apiUser, 'POST' );
	}

	/**
	 * upload image to Discourse
	 * you have to do this before you can insert it into a message
	 *
	 * @param string $fullPath     path to the file on disk
	 * @param string $filename     the filename we should refer to this file (i.e. 'Kitty.jpg')
	 * @param string $mimeFileType the mime filetype - often 'image/jpeg'
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function uploadImage( string $fullPath, string $filename, string $mimeFileType ) {
		// see https://www.php.net/manual/en/class.curlfile.php
		$curlFile = new CURLFile( $fullPath, $mimeFileType, $filename ); // try adding

		$curlFile->type = 'upload';

		$params = [
			'type'       => 'upload',
			'file'       => $curlFile,
			'uploadFile' => true,
		];

		return $this->_postRequest( '/uploads.json', $params );
	}

	/**
	 * sync local site info with Discourse info - for SSO implementations only
	 *
	 * @param string $email
	 * @param string $userName
	 * @param array  $otherParameters external_id, add_groups, require_activation
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function syncSso( string $email, string $userName, array $otherParameters = [] ) {
		// Create an array of SSO parameters.
		$sso_params = [
			'email'    => $email,
			'username' => $userName,
		];

		if ( $otherParameters ) {
			$sso_params = array_merge( $sso_params, $otherParameters );
		}

		// Convert the SSO parameters into the SSO payload and generate the SSO signature.
		$sso_payload = base64_encode( http_build_query( $sso_params ) );
		$sig         = hash_hmac( 'sha256', $sso_payload, $this->sso_secret );

		$url         = 'https://forum.example.com/admin/users/sync_sso';
		$post_fields = [
			'sso' => $sso_payload,
			'sig' => $sig,
		];

		return $this->_postRequest( '/admin/users/sync_sso', [ $post_fields ] );
	}

	/**
	 * @return string
	 */
	public function getSsoSecret(): string {
		return $this->sso_secret;
	}

	/**
	 * @param string $sso_secret
	 */
	public function setSsoSecret( string $sso_secret ): void {
		$this->sso_secret = $sso_secret;
	}

	/**
	 * @param bool $debugPutPostRequest
	 */
	public function setDebugPutPostRequest( bool $debugPutPostRequest ): void {
		$this->debugPutPostRequest = $debugPutPostRequest;
	}

	/**
	 * @param bool $debugGetRequest
	 */
	public function setDebugGetRequest( bool $debugGetRequest ): void {
		$this->debugGetRequest = $debugGetRequest;
	}

	/**
	 * set a user's avatar
	 *
	 * note that this does NOT work if your site sends the avatar as part of SSO - in that case,
	 * you need to use syncSSO()
	 *
	 * @param string $userName
	 * @param int    $userId
	 * @param string $fullPath     full path so we can load the file contents
	 * @param string $mimeFileType like 'image/jpeg'
	 * @param string $filename     the filename that shows on the site
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function setAvatar( string $userName, int $userId, string $fullPath, string $mimeFileType, string $filename ) {
		// see https://meta.discourse.org/t/upload-avatar-image-with-api/55253/7

		/*
		 * POST {{base_url}}/uploads.json
			form-data:
			  api_key: {{api_key}}
			  api_username: {{api_username}}
			  type: avatar
			  user_id: 1
			  files[]: file
		 */

		// see https://www.php.net/manual/en/class.curlfile.php
		$curlFile       = new CURLFile( $fullPath, $mimeFileType, $filename ); // try adding
		$curlFile->type = 'avatar';

		$params = [
			'type'        => 'avatar',
			'user_id'     => $userId,
			'files[]'     => $curlFile,
			'synchronous' => 'true',
			'uploadFile'  => true,
		];

		// first we have to upload the file itself
		$res = $this->_postRequest( '/uploads.json', $params );

		//		$this->setDebugPutPostRequest( true );

		// did it upload successfully?
		if ( $res->apiresult->id ) {

			/*
			 PUT {{base_url}}/users/{{api_username}}/preferences/avatar/pick
				form-data:
				  api_key: {{api_key}}
				  api_username: {{api_username}}
				  upload_id: 2
				  type: uploaded
			 */

			// yes-- attach it to the user
			$params = [
				'type'      => 'uploaded',
				'upload_id' => $res->apiresult->id,
			];

			$res = $this->_putRequest( '/users/' . $userName . '/preferences/avatar/pick', [ $params ] );
		} else {
			throw new Exception( 'File upload failed' );
		}

		return $res;
	}

	/**
	 * @param $userName
	 * @param $params
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function setUserField( $userName, $params ) {
		return $this->_putRequest( '/users/' . $userName, [ $params ] );
	}

	/**
	 * anonymize a Discourse account... this is basically the same as deleting the user...
	 * it PERMANENTLY scrambles the username etc.... there is NO UNDO!
	 *
	 * @param int $userId
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	public function anonymizeAccount( int $userId ) {
		$res = $this->_putRequest( '/admin/users/' . $userId . '/anonymize', [] );

		return $res;
	}

	/**
	 * get the latest topics posted (i.e. most recent activity)
	 *
	 * @param string $userName optional - discourse username to impersonate
	 *
	 * @return array Discourse "topic" objects
	 * @throws Exception
	 */
	public function getLatestTopics( $userName = 'system' ): array {
		$res = $this->_getRequest( '/latest.json', [], $userName );

		if ( ! $res ) {
			$res = [];
		} else {
			$res = $res->apiresult->topic_list->topics;
		}

		return $res;
	}

	/**
	 * get the "hot" topics (busy topics) - default this year
	 *
	 * @param string $period   optional; can be: all, yearly, quarterly, monthly, weekly, daily
	 * @param string $userName optional - discourse username to impersonate
	 *
	 * @return array Discourse "topic" objects
	 * @throws Exception
	 */
	public function getTopTopics( string $period = 'yearly', string $userName = 'system' ): array {
		$res = $this->_getRequest( '/top/' . $period . '.json', [], $userName );
		$res = is_object( $res) && isset($res->apiresult->topic_list->topics) ? $res->apiresult->topic_list->topics : [];

		return $res;
	}
}