<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\NamespaceInfo;

/**
 * @covers \Mediawiki\DataModel\NamespaceInfo
 * @author gbirke
 */
class NamespaceInfoTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provideValidConstruction
	 * @param int $id
	 * @param string $canonicalName
	 * @param string $localName
	 * @param string $caseHandling
	 * @param null $defaultContentModel
	 * @param array $aliases
	 */
	public function testValidConstruction($id, $canonicalName, $localName, $caseHandling, $defaultContentModel = null,
                                          $aliases = [] ) {
		$namespace = new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
		$this->assertSame( $id, $namespace->getId() );
		$this->assertSame( $canonicalName, $namespace->getCanonicalName() );
		$this->assertSame( $localName, $namespace->getLocalName() );
		$this->assertSame( $caseHandling, $namespace->getCaseHandling() );
		$this->assertSame( $defaultContentModel, $namespace->getDefaultContentModel() );
		$this->assertSame( $aliases, $namespace->getAliases() );
	}

	public function provideValidConstruction() {
		return array(
			array( -2, 'Media', 'Media', 'first-letter' ),
			array( 0, '', '', 'first-letter' ),
			array( 4, 'Project', 'Wikipedia', 'first-letter' ),
			array( 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition' ),
			array( 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition', [ 'GD' ] ),
		);
	}

	/**
	 * @param $id
	 * @param $canonicalName
	 * @param $localName
	 * @param $caseHandling
	 * @param null $defaultContentModel
	 * @param array $aliases
	 *
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction($id, $canonicalName, $localName, $caseHandling, $defaultContentModel = null,
                                            $aliases = [] ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
	}

	public function provideInvalidConstruction() {
		return array(
			array( .5, 'Media', 'Media', 'first-letter' ),
			array( '0', '', '', 'first-letter' ),
			array( -2, null, 'Media', 'first-letter' ),
			array( -2, 'Media', null, 'first-letter' ),
			array( 4, 'Project', 'Wikipedia', 'first-letter', 5 ),
			array( 2302, null, 'Gadget definition', 'case-sensitive', 'GadgetDefinition' ),
			array( 4, 'Project', 'Wikipedia', 'first-letter', 5 ),
			array( 4, 'Project', 'Wikipedia', 'first-letter', 'GadgetDefinition', 'notanalias' ),
		);
	}

}
