<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\User;

/**
 * @access private
 *
 * @author Addshore
 */
class UserGetter {

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * @param string $username
	 *
	 * @returns User
	 */
	public function getFromUsername( $username ) {
		$result = $this->api->getRequest(
			new SimpleRequest(
				'query', [
				'list' => 'users',
				'ususers' => $username,
				'usprop' => 'gender|emailable|registration|editcount|rights|implicitgroups|groups|blockinfo',
			]
			)
		);

		return $this->newUserFromListUsersResult( array_shift( $result['query']['users'] ) );
	}

	/**
	 * @param array $array
	 *
	 * @return User
	 */
	private function newUserFromListUsersResult( $array ) {
		if ( array_key_exists( 'userid', $array ) ) {
			return new User(
				$array['name'],
				$array['userid'],
				$array['editcount'],
				$array['registration'],
				[ 'groups' => $array['groups'], 'implicitgroups' => $array['implicitgroups'] ],
				$array['rights'],
				$array['gender']
			);
		} else {
			return new User(
				$array['name'],
				0,
				0,
				'',
				[ 'groups' => [], 'implicitgroups' => [] ],
				[],
				''
			);
		}

	}

}
