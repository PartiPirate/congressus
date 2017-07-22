<?php

namespace Mediawiki\DataModel;

use LogicException;

/**
 * Class Representing the content of a revision
 * @author Addshore
 */
class Content {

	/**
	 * @var string sha1 hash of the object content upon creation
	 */
	private $initialHash;

	/**
	 * @var mixed
	 */
	private $data;

	/**
	 * @var string|null
	 */
	private $model;

	/**
	 * Should always be called AFTER overriding constructors so a hash can be created
	 *
	 * @param mixed $data
	 * @param string|null $model
	 */
	public function __construct( $data, $model = null ) {
		$this->data = $data;
		$this->model = $model;
		$this->initialHash = $this->getHash();
	}

	/**
	 * @return string
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * Returns a sha1 hash of the content
	 *
	 * @throws LogicException
	 * @return string
	 */
	public function getHash() {
		$data = $this->getData();
		if( is_object( $data ) ) {
			if( method_exists( $data, 'getHash' ) ) {
				return $data->getHash();
			} else {
				return sha1( serialize( $data ) );
			}
		}
		if( is_string( $data ) ) {
			return sha1( $data );
		}
		throw new LogicException( "Cant get hash for data of type: " . gettype( $data ) );
	}

	/**
	 * Has the content been changed since object construction (this shouldn't happen!)
	 * @return bool
	 */
	public function hasChanged() {
		return $this->initialHash !== $this->getHash();
	}

	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

} 