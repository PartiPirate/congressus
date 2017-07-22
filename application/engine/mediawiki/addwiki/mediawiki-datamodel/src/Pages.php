<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a collection or Page classes
 * @author Addshore
 */
class Pages {

	/**
	 * @var Page[]
	 */
	private $pages;

	/**
	 * @param Page[] $pages
	 */
	public function __construct( $pages = array() ) {
		$this->pages = array();
		$this->addPages( $pages );
	}

	/**
	 * @param Page[]|Pages $pages
	 *
	 * @throws InvalidArgumentException
	 */
	public function addPages( $pages ) {
		if( !is_array( $pages ) && !$pages instanceof Pages ) {
			throw new InvalidArgumentException( '$pages needs to either be an array or a Pages object' );
		}
		if( $pages instanceof Pages ) {
			$pages = $pages->toArray();
		}
		foreach( $pages as $page ) {
			$this->addPage( $page );
		}
	}

	/**
	 * @param Page $page
	 */
	public function addPage( Page $page ) {
		$this->pages[$page->getId()] = $page;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function hasPageWithId( $id ){
		return array_key_exists( $id, $this->pages );
	}

	/**
	 * @param Page $page
	 *
	 * @return bool
	 */
	public function hasPage( Page $page ){
		return array_key_exists( $page->getId(), $this->pages );
	}

	/**
	 * @return Page|null Page or null if there is no page
	 */
	public function getLatest() {
		if( empty( $this->pages ) ) {
			return null;
		}
		return $this->pages[ max( array_keys( $this->pages ) ) ];
	}


	/**
	 * @param int $pageid
	 *
	 * @throws RuntimeException
	 * @return Page
	 */
	public function get( $pageid ){
		if( $this->hasPageWithId( $pageid ) ){
			return $this->pages[$pageid];
		}
		throw new RuntimeException( 'No such page loaded in Pages object' );
	}

	/**
	 * @return Page[]
	 */
	public function toArray() {
		return $this->pages;
	}
}