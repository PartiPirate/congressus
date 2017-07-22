<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Content;
use PHPUnit_Framework_TestCase;

class ContentTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $data, $model ) {
		$content = new Content( $data, $model );
		$this->assertEquals( $data, $content->getData() );
		$this->assertEquals( $model, $content->getModel() );
		$this->assertTrue( is_string( $content->getHash() ) );
		$this->assertFalse( $content->hasChanged() );
	}

	public function provideValidConstruction() {
		return array(
			array( '', null ),
			array( 'foo', null ),
			array( new \stdClass(), null ),
		);
	}

}
 