<?php

namespace Mediawiki\Api\Service;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\User;

/**
 * @access private
 *
 * @author Addshore
 */
class UserBlocker {

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
	 * @since 0.3
	 *
	 * @param User|string $user
	 * @param array $extraParams
	 *
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function block( $user, array $extraParams = [] ) {
		if ( !$user instanceof User && !is_string( $user ) ) {
			throw new InvalidArgumentException( '$user must be either a string or User object' );
		}

		if ( $user instanceof User ) {
			$user = $user->getName();
		}

		$params = [
			'user' => $user,
			'token' => $this->api->getToken( 'block' ),
		];

		$params = array_merge( $extraParams, $params );

		$this->api->postRequest( new SimpleRequest( 'block', $params ) );
		return true;
	}

}
