<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;

/**
 * @access private
 *
 * @author Addshore
 */
class PageDeleter {

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
	 * @since 0.2
	 *
	 * @param Page $page
	 * @param array $extraParams
	 *
	 * @return bool
	 */
	public function delete( Page $page, array $extraParams = [] ) {
		$this->api->postRequest( new SimpleRequest(
			'delete',
			$this->getDeleteParams( $page->getPageIdentifier(), $extraParams )
		) );
		return true;
	}

	/**
	 * @since 0.2
	 *
	 * @param Revision $revision
	 * @param array $extraParams
	 *
	 * @return bool
	 */
	public function deleteFromRevision( Revision $revision, array $extraParams = [] ) {
		$this->api->postRequest( new SimpleRequest(
			'delete',
			$this->getDeleteParams( $revision->getPageIdentifier(), $extraParams )
		) );
		return true;
	}

	/**
	 * @since 0.2
	 *
	 * @param int $pageid
	 * @param array $extraParams
	 *
	 * @return bool
	 */
	public function deleteFromPageId( $pageid, array $extraParams = [] ) {
		$this->api->postRequest( new SimpleRequest(
			'delete',
			$this->getDeleteParams( new PageIdentifier( null, $pageid ), $extraParams )
		) );
		return true;
	}

	/**
	 * @since 0.5
	 *
	 * @param Title|string $title
	 * @param array $extraParams
	 *
	 * @return bool
	 */
	public function deleteFromPageTitle( $title, array $extraParams = [] ) {
		if ( is_string( $title ) ) {
			$title = new Title( $title );
		}
		$this->api->postRequest( new SimpleRequest(
			'delete',
			$this->getDeleteParams( new PageIdentifier( $title ), $extraParams )
		) );
		return true;
	}

	/**
	 * @param PageIdentifier $identifier
	 * @param array $extraParams
	 *
	 * @return array
	 */
	private function getDeleteParams( PageIdentifier $identifier, $extraParams ) {
		$params = [];

		if ( !is_null( $identifier->getId() ) ) {
			$params['pageid'] = $identifier->getId();
		} else {
			$params['title'] = $identifier->getTitle()->getTitle();
		}

		$params['token'] = $this->api->getToken( 'delete' );

		return array_merge( $extraParams, $params );
	}

}
