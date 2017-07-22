<?php

namespace Mediawiki\Api;

/**
 * @since 1.0
 *
 * @author Addshore
 */
class FluentRequest implements Request {

	/**
	 * @var array
	 */
	private $params = [];

	/**
	 * @var array
	 */
	private $headers = [];

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @since 1.0
	 *
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @since 1.0
	 *
	 * @return self
	 */
	public static function factory() {
		return new self();
	}

	/**
	 * @since 1.0
	 *
	 * @param string $action
	 *
	 * @return $this
	 */
	public function setAction( $action ) {
		$this->setParam( 'action', $action );
		return $this;
	}

	/**
	 * Totally overwrite any previously set params
	 *
	 * @since 1.0
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	public function setParams( array $params ) {
		$this->params = $params;
		return $this;
	}

	/**
	 * Totally overwrite any previously set params
	 *
	 * @since 1.0
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	public function addParams( array $params ) {
		$this->params = array_merge( $this->params, $params );
		return $this;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $param
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setParam( $param, $value ) {
		$this->params[$param] = $value;
		return $this;
	}

	/**
	 * Totally overwrite any previously set params
	 *
	 * @since 1.0
	 *
	 * @param array $headers
	 *
	 * @return $this
	 */
	public function setHeaders( $headers ) {
		$this->headers = $headers;
		return $this;
	}

}
