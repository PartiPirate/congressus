<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;

/**
 * @author Addshore
 */
class Title implements JsonSerializable {

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var int
	 */
	private $ns;

	/**
	 * @param string $title
	 * @param int $ns
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $title, $ns = 0 ) {
		if( !is_string( $title ) ) {
			throw new InvalidArgumentException( '$title must be a string' );
		}
		if( !is_int( $ns ) ) {
			throw new InvalidArgumentException( '$ns must be an int' );
		}
		$this->title = $title;
		$this->ns = $ns;
	}

	/**
	 * @return int
	 * @since 0.1
	 */
	public function getNs() {
		return $this->ns;
	}

	/**
	 * @return string
	 * @since 0.6
	 */
	public function getText() {
		return $this->title;
	}

	/**
	 * @return string
	 * @deprecated in 0.6 use getText (makes things look cleaner)
	 */
	public function getTitle() {
		return $this->getText();
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		return array(
			'title' => $this->title,
			'ns' => $this->ns,
		);
	}

	/**
	 * @param array $json
	 *
	 * @return self
	 */
	public static function jsonDeserialize( $json ) {
		return new self( $json['title'], $json['ns'] );
	}

}
