<?php

namespace Mediawiki\Api;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @since 0.1
 *
 * @author Addshore
 */
class MediawikiSession implements LoggerAwareInterface {

	/**
	 * @var array
	 */
	private $tokens = [];

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @var bool if this session is running against mediawiki version pre 1.25
	 */
	private $usePre125TokensModule = false;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
		$this->logger = new NullLogger();
	}

	/**
	 * Sets a logger instance on the object
	 *
	 * @since 1.1
	 *
	 * @param LoggerInterface $logger
	 *
	 * @return null
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Tries to get the specified token from the API
	 *
	 * @since 0.1
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getToken( $type = 'csrf' ) {
		// If we don't already have the token that we want
		if ( !array_key_exists( $type, $this->tokens ) ) {

			$this->logger->log( LogLevel::DEBUG, 'Getting fresh token', [ 'type' => $type ] );

			// If we know that we don't have the new module mw<1.25
			if ( $this->usePre125TokensModule ) {
				return $this->reallyGetPre125Token( $type );
			} else {
				return $this->reallyGetToken( $type );
			}

		}

		return $this->tokens[$type];
	}

	private function reallyGetPre125Token( $type ) {
		// Suppress deprecation warning
		$result = @$this->api->postRequest( // @codingStandardsIgnoreLine
			new SimpleRequest( 'tokens', [ 'type' => $this->getOldTokenType( $type ) ] )
		);
		$this->tokens[$type] = array_pop( $result['tokens'] );

		return $this->tokens[$type];
	}

	private function reallyGetToken( $type ) {
		// We suppress errors on this call so the user doesn't get get a warning that isn't their fault.
		$result = @$this->api->postRequest( // @codingStandardsIgnoreLine
			new SimpleRequest( 'query', [
				'meta' => 'tokens',
				'type' => $this->getNewTokenType( $type ),
				'continue' => '',
			] )
		);
		// If mw<1.25 (no new module)
		$metaWarning = "Unrecognized value for parameter 'meta': tokens";
		if ( isset( $result['warnings']['query']['*'] )
			&& false !== strpos( $result['warnings']['query']['*'], $metaWarning ) ) {
			$this->usePre125TokensModule = true;
			$this->logger->log( LogLevel::DEBUG, 'Falling back to pre 1.25 token system' );
			$this->tokens[$type] = $this->reallyGetPre125Token( $type );
		} else {
			$this->tokens[$type] = array_pop( $result['query']['tokens'] );
		}

		return $this->tokens[$type];
	}

	/**
	 * Tries to guess a new token type from an old token type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function getNewTokenType( $type ) {
		switch ( $type ) {
			case 'edit':
			case 'delete':
			case 'protect':
			case 'move':
			case 'block':
			case 'unblock':
			case 'email':
			case 'import':
			case 'options':
				return 'csrf';
		}
		// Return the same type, don't know what to do with this..
		return $type;
	}

	/**
	 * Tries to guess an old token type from a new token type
	 *
	 * @param $type
	 *
	 * @return string
	 */
	private function getOldTokenType( $type ) {
		switch ( $type ) {
			// Guess that we want an edit token, this may not always work as we might be trying to
			// use it for something else...
			case 'csrf':
				return 'edit';
		}
		return $type;
	}

	/**
	 * Clears all tokens stored by the api
	 *
	 * @since 0.2
	 */
	public function clearTokens() {
		$this->logger->log( LogLevel::DEBUG, 'Clearing session tokens', [ 'tokens' => $this->tokens ] );
		$this->tokens = [];
	}

}
