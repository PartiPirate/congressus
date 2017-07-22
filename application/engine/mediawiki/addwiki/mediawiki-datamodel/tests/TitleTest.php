<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Title;

/**
 * @covers \Mediawiki\DataModel\Title
 * @author Addshore
 */
class TitleTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $title, $ns ) {
		$titleObj = new Title( $title, $ns );
		$this->assertEquals( $title, $titleObj->getText() );
		$this->assertEquals( $title, $titleObj->getTitle() );
		$this->assertEquals( $ns, $titleObj->getNs() );
	}

	public function provideValidConstruction() {
		return array(
			array( 'fooo', 0 ),
			array( 'Foo:Bar', 15 ),
			array( 'FooBar:Bar', 9999 ),
		);
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $title, $ns ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new Title( $title, $ns );
	}

	public function provideInvalidConstruction() {
		return array(
			array( array(), array() ),
			array( 'foo', array() ),
			array( array(), 1 ),
			array( null, 1 ),
			array( null, null ),
			array( 'foo', null ),
		);
	}

	public function testJsonRoundTrip() {
		$title = new Title( 'Foo', 19 );
		$json = $title->jsonSerialize();
		$this->assertEquals( $title, Title::jsonDeserialize( $json ) );
	}

} 