<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;

class Page {

	/**
	 * @var Revisions
	 */
	private $revisions;

	/**
	 * @var PageIdentifier
	 */
	private $pageIdentifier;

	/**
	 * @param PageIdentifier $pageIdentifier
	 * @param Revisions|null $revisions
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( PageIdentifier $pageIdentifier = null , Revisions $revisions = null ) {
		if( is_null( $revisions ) ) {
			$revisions = new Revisions();
		}
		$this->revisions = $revisions;
		$this->pageIdentifier = $pageIdentifier;
	}

	/**
	 * @deprecated since 0.5
	 * @return int
	 */
	public function getId() {
		return $this->pageIdentifier->getId();
	}

	/**
	 * @return Revisions
	 */
	public function getRevisions() {
		return $this->revisions;
	}

	/**
	 * @deprecated since 0.5
	 * @return Title
	 */
	public function getTitle() {
		return $this->pageIdentifier->getTitle();
	}

	/**
	 * @return PageIdentifier
	 */
	public function getPageIdentifier() {
		return $this->pageIdentifier;
	}

} 