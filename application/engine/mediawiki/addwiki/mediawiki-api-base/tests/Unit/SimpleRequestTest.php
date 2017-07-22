<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\SimpleRequest;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\SimpleRequest
 */
class SimpleRequestTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $action, $params, $expected, $headers = [] ) {
		$request = new SimpleRequest( $action, $params, $headers );
		$this->assertEquals( $expected, $request->getParams() );
		$this->assertEquals( $headers, $request->getHeaders() );
	}

	public function provideValidConstruction() {
		return [
			[ 'action', [], [ 'action' => 'action' ] ],
			[ '1123', [], [ 'action' => '1123' ] ],
			[ 'a', [ 'b' => 'c' ], [ 'action' => 'a', 'b' => 'c' ] ],
			[ 'a', [ 'b' => 'c', 'd' => 'e' ], [ 'action' => 'a', 'b' => 'c', 'd' => 'e' ] ],
			[ 'a', [ 'b' => 'c|d|e|f' ], [ 'action' => 'a', 'b' => 'c|d|e|f' ] ],
			[ 'foo', [], [ 'action' => 'foo' ] ,[ 'foo' => 'bar' ] ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $action, $params ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new SimpleRequest( $action, $params );
	}

	public function provideInvalidConstruction() {
		return [
			[ [], [] ],
		];
	}

}
