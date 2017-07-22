<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;

/**
 * @access private
 *
 * @author Addshore
 */
class RevisionRollbacker {

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
	 * @param Revision $revision
	 * @param Title $title if using MW 1.24 of lower (https://gerrit.wikimedia.org/r/#/c/133063/)
	 *
	 * @return bool
	 */
	public function rollback( Revision $revision, Title $title = null ) {
		$this->api->postRequest(
			new SimpleRequest( 'rollback', $this->getRollbackParams( $revision, $title ) )
		);

		return true;
	}

	/**
	 * @param Revision $revision
	 * @param Title|null $title
	 *
	 * @return array
	 */
	private function getRollbackParams( Revision $revision, $title ) {
		$params = [];
		if ( !is_null( $title ) ) {
			// This is needed prior to https://gerrit.wikimedia.org/r/#/c/133063/
			$params['title'] = $title->getTitle();
		} else {
			// This will work after https://gerrit.wikimedia.org/r/#/c/133063/
			$params['pageid'] = $revision->getPageId();
		}
		$params['user'] = $revision->getUser();
		$params['token'] = $this->getTokenForRevision( $revision );

		return $params;
	}

	/**
	 * @param Revision $revision
	 *
	 * @returns string
	 */
	private function getTokenForRevision( Revision $revision ) {
		$result = $this->api->postRequest(
			new SimpleRequest(
				'query', [
				'prop' => 'revisions',
				'revids' => $revision->getId(),
				'rvtoken' => 'rollback',
			]
			)
		);
		$result = array_shift( $result['query']['pages'] );

		return $result['revisions'][0]['rollbacktoken'];
	}

}
