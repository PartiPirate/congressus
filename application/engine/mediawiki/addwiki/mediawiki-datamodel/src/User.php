<?php

namespace Mediawiki\DataModel;

use InvalidArgumentException;

/**
 * Represents a mediawiki user
 * @author Addshore
 */
class User {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $editcount;

	/**
	 * @var string
	 */
	private $registration;

	/**
	 * @var array
	 */
	private $groups;

	/**
	 * @var array
	 */
	private $rights;

	/**
	 * @var string
	 */
	private $gender;

	/**
	 * @param string $name
	 * @param int $id
	 * @param int $editcount
	 * @param string $registration
	 * @param array[] $groups groups grouped by type.
	 *          Keys to use are 'groups' and 'implicitgroups' as returned by the api.
	 * @param array $rights
	 * @param string $gender
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $name, $id, $editcount, $registration, $groups, $rights, $gender ) {
		if( !is_string( $name ) || empty( $name ) ) {
			throw new InvalidArgumentException( '$name must be a string and can not be empty' );
		}
		if( !is_int( $id ) ) {
			throw new InvalidArgumentException( '$id must be an int' );
		}
		if( !is_int( $editcount ) ) {
			throw new InvalidArgumentException( '$editcount must be an int' );
		}
		if( !is_string( $registration ) ) {
			throw new InvalidArgumentException( '$registration must be a string' );
		}
		if( !is_array( $groups ) || !array_key_exists( 'groups', $groups ) || !array_key_exists( 'implicitgroups', $groups ) ) {
			throw new InvalidArgumentException( '$groups must be an array or arrays with keys "groups" and "implicitgroups"' );
		}
		if( !is_array( $rights ) ) {
			throw new InvalidArgumentException( '$rights must be an array' );
		}
		if( !is_string( $gender ) ) {
			throw new InvalidArgumentException( '$gender must be a string' );
		}

		$this->editcount = $editcount;
		$this->gender = $gender;
		$this->groups = $groups;
		$this->id = $id;
		$this->name = $name;
		$this->registration = $registration;
		$this->rights = $rights;
	}

	/**
	 * @return int
	 */
	public function getEditcount() {
		return $this->editcount;
	}

	/**
	 * @return string
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * @param string $type 'groups' or 'implicitgroups'
	 *
	 * @return array
	 */
	public function getGroups( $type = 'groups' ) {
		return $this->groups[$type];
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getRegistration() {
		return $this->registration;
	}

	/**
	 * @return array
	 */
	public function getRights() {
		return $this->rights;
	}


} 