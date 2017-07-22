<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\Title;
use OutOfBoundsException;

/**
 * @access private
 *
 * @author Addshore
 */
class PageRestorer {

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
	 * @param array $extraParams
	 *
	 * @return bool
	 */
	public function restore( Page $page, array $extraParams = [] ) {
		$this->api->postRequest(
			new SimpleRequest(
				'undelete',
				$this->getUndeleteParams( $page->getTitle(), $extraParams )
			)
		);

		return true;
	}

	/**
	 * @param Title $title
	 * @param array $extraParams
	 *
	 * @return array
	 */
	private function getUndeleteParams( Title $title, $extraParams ) {
		$params = [];

		$params['title'] = $title->getTitle();
		$params['token'] = $this->getUndeleteToken( $title );

		return array_merge( $extraParams, $params );
	}

	/**
	 * @param Title $title
	 *
	 * @throws OutOfBoundsException
	 * @returns string
	 */
	private function getUndeleteToken( Title $title ) {
		$response = $this->api->postRequest(
			new SimpleRequest(
				'query', [
				'list' => 'deletedrevs',
				'titles' => $title->getTitle(),
				'drprop' => 'token',
			]
			)
		);
		if ( array_key_exists( 'token', $response['query']['deletedrevs'][0] ) ) {
			return $response['query']['deletedrevs'][0]['token'];
		} else {
			throw new OutOfBoundsException(
				'Could not get page undelete token from list=deletedrevs query'
			);
		}
	}

}
