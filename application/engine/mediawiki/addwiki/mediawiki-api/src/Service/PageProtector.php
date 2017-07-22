<?php

namespace Mediawiki\Api\Service;

use InvalidArgumentException;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;

/**
 * @access private
 *
 * @author Addshore
 */
class PageProtector {

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
	 * @param Page $page
	 * @param string[] $protections where the 'key' is the action and the 'value' is the group
	 * @param array $extraParams
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 */
	public function protect( Page $page, $protections, array $extraParams = [] ) {
		if ( !is_array( $protections ) || empty( $protections ) ) {
			throw new InvalidArgumentException(
				'$protections must be an array with keys and values'
			);
		}

		$params = [
			'pageid' => $page->getId(),
			'token' => $this->api->getToken( 'protect' ),
		];
		$protectionsString = '';
		foreach ( $protections as $action => $value ) {
			if ( !is_string( $action ) || !is_string( $value ) ) {
				throw new InvalidArgumentException(
					'All keys and elements of $protections must be strings'
				);
			}
			$protectionsString = $action . '=' . $value . '|';
		}
		$params['protections'] = rtrim( $protectionsString, '|' );

		$this->api->postRequest(
			new SimpleRequest( 'protect', array_merge( $extraParams, $params ) )
		);

		return true;
	}

}
