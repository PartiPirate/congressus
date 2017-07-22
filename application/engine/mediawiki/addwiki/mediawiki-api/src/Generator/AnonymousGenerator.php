<?php

namespace Mediawiki\Api\Generator;

/**
 * @access public
 *
 * @author Addshore
 *
 * @since 0.5.1
 */
class AnonymousGenerator implements ApiGenerator {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @param string $name
	 * @param array $params including 'g' prefix keys
	 */
	public function __construct( $name, array $params ) {
		$this->name = $name;
		$this->params = $params;
	}

	public function getParams() {
		$params = $this->params;
		$params['generator'] = $this->name;
		return $params;
	}
}
