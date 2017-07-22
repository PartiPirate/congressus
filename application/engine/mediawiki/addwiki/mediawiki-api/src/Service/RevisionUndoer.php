<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Revision;

/**
 * @access private
 *
 * @author Addshore
 */
class RevisionUndoer {

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
	 * @param Revision $revision
	 *
	 * @return bool
	 */
	public function undo( Revision $revision ) {
		$this->api->postRequest( new SimpleRequest(
			'edit',
			$this->getParamsFromRevision( $revision )
		) );
		return true;
	}

	/**
	 * @param Revision $revision
	 *
	 * @return array
	 */
	private function getParamsFromRevision( Revision $revision ) {
		$params = [
			'undo' => $revision->getId(),
			'token' => $this->api->getToken(),
		];

		if ( !is_null( $revision->getPageIdentifier()->getId() ) ) {
			$params['pageid'] = $revision->getPageIdentifier()->getId();
		} else {
			$params['title'] = $revision->getPageIdentifier()->getTitle()->getTitle();
		}

		return $params;
	}

}
