<?php

namespace Mediawiki\Api\Service;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;

/**
 * @access private
 *
 * @author Addshore
 */
class UserCreator {

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
	 * @param string $password
	 * @param string|null $email
	 *
	 * @return bool
	 */
	public function create( $username, $password, $email = null ) {
		if ( !is_string( $username ) ) {
			throw new InvalidArgumentException( '$username should be a string' );
		}
		if ( !is_string( $password ) ) {
			throw new InvalidArgumentException( '$password should be a string' );
		}
		if ( !is_string( $email ) && !is_null( $email ) ) {
			throw new InvalidArgumentException( '$email should be a string or null' );
		}

		$params = [
			'name' => $username,
			'password' => $password,
		];

		if ( !is_null( $email ) ) {
			$params['email'] = $email;
		}

		$result = $this->api->postRequest( new SimpleRequest( 'createaccount', $params ) );
		if ( $result['createaccount']['result'] == 'NeedToken' ) {
			$result = $this->api->postRequest(
				new SimpleRequest(
					'createaccount',
					array_merge( [ 'token' => $result['createaccount']['token'] ], $params )
				)
			);
		}
		if ( $result['createaccount']['result'] === 'Success' ) {
			return true;
		}

		return false;
	}

}
