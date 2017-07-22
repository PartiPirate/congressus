<?php

namespace Mediawiki\DataModel;

/**
 * Class representing metadata about a MediaWiki namespace
 *
 * @author gbirke
 */
class NamespaceInfo
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var string
	 */
	private $canonicalName;

	/**
	 * @var string
	 */
	private $localName;

	/**
	 * @var string
	 */
	private $caseHandling;

	/**
	 * @var string
	 */
	private $defaultContentModel;

	/**
	 * @var array
	 */
	private $aliases;

	/**
	 * NamespaceInfo constructor.
	 * @param int $id
	 * @param string $canonicalName
	 * @param string $localName
	 * @param string $caseHandling
	 * @param string $defaultContentModel
	 * @param array $aliases
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel = null, $aliases = [] )
	{
		if( !is_int( $id ) ) {
			throw new \InvalidArgumentException( '$id must be an integer' );
		}
		if ( !is_string( $canonicalName ) ) {
			throw new \InvalidArgumentException( '$canonicalName must be a string' );
		}
		if ( !is_string( $localName ) ) {
			throw new \InvalidArgumentException( '$localName must be a string' );
		}
		if ( !is_string( $caseHandling ) ) {
			throw new \InvalidArgumentException( '$caseHandling must be a string' );
		}
		if ( !is_null( $defaultContentModel) && !is_string( $defaultContentModel ) ) {
			throw new \InvalidArgumentException( '$canonicalName must be a string' );
		}

		if ( !is_array( $aliases ) ) {
			throw new \InvalidArgumentException( '$aliases must be an array' );
		}

		$this->id = $id;
		$this->canonicalName = $canonicalName;
		$this->localName = $localName;
		$this->caseHandling = $caseHandling;
		$this->defaultContentModel = $defaultContentModel;
		$this->aliases = $aliases;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getCanonicalName()
	{
		return $this->canonicalName;
	}

	/**
	 * @return string
	 */
	public function getLocalName()
	{
		return $this->localName;
	}

	/**
	 * @return string
	 */
	public function getCaseHandling()
	{
		return $this->caseHandling;
	}

	/**
	 * @return string
	 */
	public function getDefaultContentModel()
	{
		return $this->defaultContentModel;
	}

	/**
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

}