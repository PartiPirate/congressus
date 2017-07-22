<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;

/**
 * @access private
 *
 * @author Addshore
 */
class FileUploader {

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
	 * @param string $targetName
	 * @param string $location Can be local path or remote url
	 *
	 * @return bool
	 */
	public function upload( $targetName, $location ) {
		$params = [
			'filename' => $targetName,
			'token' => $this->api->getToken( 'edit' ),
		];
		$headers = [];

		if ( is_file( $location ) ) {
			$params['file'] = fopen( $location, 'r' );
			$headers['Content-Type'] = 'multipart/form-data';
		} else {
			$params['url'] = $location;
		}

		$this->api->postRequest( new SimpleRequest(
			'upload',
			$params,
			$headers
		) );

		return true;
	}
}
