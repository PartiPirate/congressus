<?php

namespace Mediawiki\Api;

use Exception;

/**
 * Class representing a Mediawiki Api UsageException
 *
 * @since 0.1
 *
 * @author Addshore
 */
class UsageException extends Exception {

	/**
	 * @var string
	 */
	private $apiCode;

	/**
	 * @var array
	 */
	private $result;

	/**
	 * @var string
	 */
	private $rawMessage;

	/**
	 * @since 0.1
	 *
	 * @param string $apiCode
	 * @param string $message
	 * @param array $result the result the exception was generated from
	 */
	public function __construct( $apiCode = '', $message = '', $result = [] ) {
		$this->apiCode = $apiCode;
		$this->result = $result;
		$this->rawMessage = $message;
		$message = 'Code: ' . $apiCode . PHP_EOL .
			'Message: ' . $message . PHP_EOL .
			'Result: ' . json_encode( $result );
		parent::__construct( $message, 0, null );
	}

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getApiCode() {
		return $this->apiCode;
	}

	/**
	 * @since 0.3
	 *
	 * @return array
	 */
	public function getApiResult() {
		return $this->result;
	}

	/**
	 * @since 2.3.0
	 *
	 * @return string
	 */
	public function getRawMessage() {
		return $this->rawMessage;
	}

}
