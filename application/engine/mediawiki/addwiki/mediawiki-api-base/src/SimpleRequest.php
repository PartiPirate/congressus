<?php

namespace Mediawiki\Api;

use InvalidArgumentException;

/**
 * Please consider using a FluentRequest object
 *
 * @since 0.2
 *
 * @author Addshore
 */
class SimpleRequest implements Request {

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * @param string $action
	 * @param array $params
	 * @param array $headers
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		$action,
		array $params = [],
		array $headers = []
	) {
		if ( !is_string( $action ) ) {
			throw new InvalidArgumentException( '$action must be string' );
		}
		$this->action = $action;
		$this->params = $params;
		$this->headers = $headers;
	}

	public function getParams() {
		return array_merge( [ 'action' => $this->action ], $this->params );
	}

	public function getHeaders() {
		return $this->headers;
	}

}
