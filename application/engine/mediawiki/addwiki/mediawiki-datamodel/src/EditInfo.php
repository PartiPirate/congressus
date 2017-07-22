<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * Represents flags that can be used when edits are made
 * @author Addshore
 */
class EditInfo {

	//minor flags
	const MINOR = true;
	const NOTMINOR = false;
	//bot flags
	const BOT = true;
	const NOTBOT = false;

	/**
	 * @var EditInfo::MINOR|self::NOTMINOR
	 */
	protected $minor = false;

	/**
	 * @var EditInfo::BOT|self::NOTBOT
	 */
	protected $bot = false;

	/**
	 * @var string
	 */
	protected $summary = null;

	/**
	 * @param string $summary
	 * @param bool $minor
	 * @param bool $bot
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $summary = '', $minor = self::NOTMINOR, $bot = self::NOTBOT ) {
		if( !is_string( $summary ) ) {
			throw new InvalidArgumentException( '$summary must be a string' );
		}
		if( !is_bool( $minor ) ) {
			throw new InvalidArgumentException( '$minor must be a bool' );
		}
		if( !is_bool( $bot ) ) {
			throw new InvalidArgumentException( '$bot must be a bool' );
		}

		$this->summary = $summary;
		$this->bot = $bot;
		$this->minor = $minor;
	}

	/**
	 * @return EditInfo::BOT|self::NOTBOT
	 */
	public function getBot() {
		return $this->bot;
	}

	/**
	 * @return EditInfo::MINOR|self::NOTMINOR
	 */
	public function getMinor() {
		return $this->minor;
	}

	/**
	 * @return string
	 */
	public function getSummary() {
		return $this->summary;
	}

}