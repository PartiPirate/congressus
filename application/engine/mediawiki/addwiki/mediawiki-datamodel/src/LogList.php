<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

/**
 * Represents a collection of Log classes
 * @author Addshore
 */
class LogList implements JsonSerializable {

	/**
	 * @var Log[]
	 */
	private $logs;

	/**
	 * @param Log[] $logs
	 */
	public function __construct( $logs = array() ) {
		$this->logs = array();
		$this->addLogs( $logs );
	}

	/**
	 * @param Log[]|LogList $logs
	 *
	 * @throws InvalidArgumentException
	 */
	public function addLogs( $logs ) {
		if( !is_array( $logs ) && !$logs instanceof LogList ) {
			throw new InvalidArgumentException( '$logs needs to either be an array or a LogList object' );
		}
		if( $logs instanceof LogList ) {
			$logs = $logs->toArray();
		}
		foreach( $logs as $log ) {
			$this->addLog( $log );
		}
	}

	/**
	 * @param Log $log
	 */
	public function addLog( Log $log ) {
		$this->logs[$log->getId()] = $log;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function hasLogWithId( $id ){
		return array_key_exists( $id, $this->logs );
	}

	/**
	 * @param Log $log
	 *
	 * @return bool
	 */
	public function hasLog( Log $log ){
		return array_key_exists( $log->getId(), $this->logs );
	}

	/**
	 * @return Log|null Log or null if there is no log
	 */
	public function getLatest() {
		if( empty( $this->logs ) ) {
			return null;
		}
		return $this->logs[ max( array_keys( $this->logs ) ) ];
	}

	/**
	 * @since 0.6
	 * @return Log|null Log or null if there is no log
	 */
	public function getOldest() {
		if( empty( $this->logs ) ) {
			return null;
		}
		return $this->logs[ min( array_keys( $this->logs ) ) ];
	}

	/**
	 * @since 0.6
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->logs );
	}

	/**
	 * @param int $id
	 *
	 * @throws RuntimeException
	 * @return Log
	 */
	public function get( $id ){
		if( $this->hasLogWithId( $id ) ){
			return $this->logs[$id];
		}
		throw new RuntimeException( 'No such Log loaded in LogList object' );
	}

	/**
	 * @return Log[]
	 */
	public function toArray() {
		return $this->logs;
	}

	/**
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * @param array $json
	 *
	 * @return self
	 */
	public static function jsonDeserialize( $json ) {
		$self = new LogList();
		foreach ( $json as $logJson ) {
			$self->addLog( Log::jsonDeserialize( $logJson ) );
		}
		return $self;
	}
}