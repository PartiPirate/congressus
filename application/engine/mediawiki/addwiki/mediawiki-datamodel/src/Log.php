<?php

namespace Mediawiki\DataModel;

use JsonSerializable;

/**
 * @since 0.5
 */
class Log implements JsonSerializable {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * @var string
	 */
	private $timestamp;

	/**
	 * @var string
	 */
	private $user;

	/**
	 * @var string
	 */
	private $comment;

	/**
	 * @var PageIdentifier
	 */
	private $pageIdentifier;

	/**
	 * @var array
	 */
	private $details;

	/**
	 * @param int $id
	 * @param string $type
	 * @param string $action
	 * @param string $timestamp
	 * @param string $user
	 * @param PageIdentifier $pageIdentifier
	 * @param string $comment
	 * @param array $details
	 */
	public function __construct( $id, $type, $action, $timestamp, $user, $pageIdentifier, $comment, $details ) {
		$this->id = $id;
		$this->type = $type;
		$this->action = $action;
		$this->timestamp = $timestamp;
		$this->user = $user;
		$this->pageIdentifier = $pageIdentifier;
		$this->comment = $comment;
		$this->details = $details;
	}

	/**
	 * @since 0.5
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @since 0.5
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @since 0.5
	 * @return string
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @since 0.5
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @since 0.6
	 * @return PageIdentifier
	 */
	public function getPageIdentifier() {
		return $this->pageIdentifier;
	}

	/**
	 * @since 0.5
	 * @return string
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @since 0.5
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @since 0.5
	 * @return array
	 */
	public function getDetails() {
		return $this->details;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		return array(
			'id' => $this->id,
			'type' => $this->type,
			'action' => $this->action,
			'timestamp' => $this->timestamp,
			'user' => $this->user,
			'pageidentifier' => $this->pageIdentifier,
			'comment' => $this->comment,
			'details' => $this->details,
		);
	}

	/**
	 * @param array $json
	 *
	 * @return self
	 */
	public static function jsonDeserialize( $json ) {
		return new self(
			$json['id'],
			$json['type'],
			$json['action'],
			$json['timestamp'],
			$json['user'],
			PageIdentifier::jsonDeserialize( $json['pageidentifier'] ),
			$json['comment'],
			$json['details']
		);
	}

}