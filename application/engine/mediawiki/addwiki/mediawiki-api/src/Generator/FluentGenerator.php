<?php

namespace Mediawiki\Api\Generator;

/**
 * @access public
 *
 * @author Addshore
 *
 * @since 0.5.1
 */
class FluentGenerator implements ApiGenerator {

	private $name;
	private $params;

	/**
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Convenience method for using this fluidly
	 *
	 * @param string $name
	 *
	 * @return FluentGenerator
	 */
	public static function factory( $name ) {
		return new self( $name );
	}

	public function getParams() {
		$params = $this->params;
		$params['generator'] = $this->name;
		return $params;
	}

	/**
	 * @param string $key optionally with the 'g' prefix
	 * @param string $value
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {
		$key = $this->addKeyprefixIfNeeded( $key );
		$this->params[$key] = $value;
		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function addKeyPrefixIfNeeded( $key ) {
		if ( strtolower( substr( $key, 0, 1 ) ) === 'g' ) {
			return $key;
		}
		return 'g' . $key;
	}

}
