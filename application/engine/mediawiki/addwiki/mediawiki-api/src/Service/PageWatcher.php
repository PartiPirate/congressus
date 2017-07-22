<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;

/**
 * @access private
 *
 * @author Addshore
 */
class PageWatcher {

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
	 * @param Page $page
	 *
	 * @returns bool
	 */
	public function watch( Page $page ) {
		$params = [
			'token' => $this->api->getToken( 'watch' ),
		];
		if ( !is_null( $page->getPageIdentifier()->getId() ) ) {
			$params['pageids'] = $page->getPageIdentifier()->getId();
		} elseif ( !is_null( $page->getPageIdentifier()->getTitle() ) ) {
			$params['titles'] = $page->getPageIdentifier()->getTitle()->getTitle();
		} elseif ( !is_null( $page->getRevisions()->getLatest() ) ) {
			$params['revids'] = $page->getRevisions()->getLatest()->getId();
		}

		$this->api->postRequest( new SimpleRequest( 'watch', $params ) );

		return true;
	}

}
