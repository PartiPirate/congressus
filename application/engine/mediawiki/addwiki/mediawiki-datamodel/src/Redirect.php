<?php

namespace Mediawiki\DataModel;

use JsonSerializable;

class Redirect implements JsonSerializable {

	private $from;
	private $to;

	public function __construct( Title $from, Title $to ) {
		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * @return Title
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @return Title
	 */
	public function getTo() {
		return $this->to;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		return array(
			'from' => $this->from->jsonSerialize(),
			'to' => $this->to->jsonSerialize(),
		);
	}

	/**
	 * @param array $json
	 *
	 * @return self
	 */
	public static function jsonDeserialize( $json ) {
		return new self(
			Title::jsonDeserialize( $json['from'] ),
			Title::jsonDeserialize( $json['to'] )
		);
	}

}
