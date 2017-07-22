<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;

/**
 * @covers Mediawiki\DataModel\PageIdentifier
 */
class PageIdentifierTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $title, $pageid, $identifiesPage ) {
		$pageIdentifier = new PageIdentifier( $title, $pageid );
		if( is_string( $title ) ) {
			$this->assertEquals( new Title( $title ), $pageIdentifier->getTitle() );
		} else {
			$this->assertEquals( $title, $pageIdentifier->getTitle() );
		}
		$this->assertEquals( $pageid, $pageIdentifier->getId() );
		$this->assertEquals( $identifiesPage, $pageIdentifier->identifiesPage() );
	}

	public function provideValidConstruction() {
		return array(
			array( null, null, false ),
			array( new Title( 'Foo' ), null, true ),
			array( new Title( 'Foo', 2 ), null, true ),
			array( null, 3, true ),
		);
	}

	public function provideRoundTripObjects() {
		return array(
			array( new PageIdentifier( null, null ) ),
			array( new PageIdentifier( null, 44 ) ),
			array( new PageIdentifier( new Title( 'someTitle', 12 ), null ) ),
			array( new PageIdentifier( new Title( 'someTitle', 55 ), 99 ) ),
		);
	}

	/**
	 * @dataProvider provideRoundTripObjects
	 */
	public function testJsonRoundTrip( PageIdentifier $identifierObject ) {
		$json = $identifierObject->jsonSerialize();
		$this->assertEquals( $identifierObject, PageIdentifier::jsonDeserialize( $json ) );
	}

}
 