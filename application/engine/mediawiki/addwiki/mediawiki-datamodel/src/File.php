<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * @author Addshore
 */
class File extends Page {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param string $url
	 * @param PageIdentifier $pageIdentifier
	 * @param Revisions $revisions
	 */
	public function __construct( $url, PageIdentifier $pageIdentifier = null, Revisions $revisions = null ) {
		parent::__construct( $pageIdentifier, $revisions );
		if( !is_string( $url ) ) {
			throw new InvalidArgumentException( '$url must be a string' );
		}
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}

}