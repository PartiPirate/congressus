<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;
use RuntimeException;

/**
 * Represents a collection or revisions
 * @author Addshore
 */
class Revisions {

	/**
	 * @var Revision[]
	 */
	private $revisions;

	/**
	 * @param Revisions[] $revisions
	 */
	public function __construct( $revisions = array() ) {
		$this->revisions = array();
		$this->addRevisions( $revisions );
	}

	/**
	 * @param Revision[]|Revisions $revisions
	 *
	 * @throws InvalidArgumentException
	 */
	public function addRevisions( $revisions ) {
		if( !is_array( $revisions ) && !$revisions instanceof Revisions ) {
			throw new InvalidArgumentException( '$revisions needs to either be an array or a Revisions object' );
		}
		if( $revisions instanceof Revisions ) {
			$revisions = $revisions->toArray();
		}
		foreach( $revisions as $revision ) {
			$this->addRevision( $revision );
		}
	}

	/**
	 * @param Revision $revision
	 */
	public function addRevision( Revision $revision ) {
		$this->revisions[$revision->getId()] = $revision;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public function hasRevisionWithId( $id ){
		return array_key_exists( $id, $this->revisions );
	}

	/**
	 * @param Revision $revision
	 *
	 * @return bool
	 */
	public function hasRevision( Revision $revision ){
		return array_key_exists( $revision->getId(), $this->revisions );
	}

	/**
	 * @return Revision|null Revision or null if there is no revision
	 */
	public function getLatest() {
		if( empty( $this->revisions ) ) {
			return null;
		}
		return $this->revisions[ max( array_keys( $this->revisions ) ) ];
	}

	/**
	 * @param int $revid
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @return Revision
	 */
	public function get( $revid ){
		if( !is_int( $revid ) ) {
			throw new InvalidArgumentException( '$revid needs to be an int' );
		}
		if( $this->hasRevisionWithId( $revid ) ){
			return $this->revisions[$revid];
		}
		throw new RuntimeException( 'No such revision loaded in Revisions object' );
	}

	/**
	 * @return Revision[]
	 */
	public function toArray() {
		return $this->revisions;
	}
}