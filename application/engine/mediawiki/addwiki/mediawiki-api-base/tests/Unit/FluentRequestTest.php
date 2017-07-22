<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\FluentRequest;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\FluentRequest
 */
class FluentRequestTest extends PHPUnit_Framework_TestCase {

	public function testFactory() {
		$this->assertInstanceOf( 'Mediawiki\Api\FluentRequest', FluentRequest::factory() );
	}

	public function testConstructionDefaults() {
		$request = new FluentRequest();

		$this->assertEquals( [], $request->getParams() );
		$this->assertEquals( [], $request->getHeaders() );
	}

	public function testSetParams() {
		$request = new FluentRequest();

		$params = [ 'foo', 'bar' ];
		$request->setParams( $params );

		$this->assertEquals( $params, $request->getParams() );
	}

	public function testSetParam() {
		$request = new FluentRequest();

		$request->setParam( 'paramName', 'fooValue' );

		$this->assertEquals( [ 'paramName' => 'fooValue' ], $request->getParams() );
	}

	public function testAddParams() {
		$request = new FluentRequest();

		$params = [ 'a'=> 'foo', 'b' => 'bar' ];
		$request->addParams( $params );

		$this->assertEquals( $params, $request->getParams() );
	}

	public function testSetHeaders() {
		$request = new FluentRequest();

		$params = [ 'foo', 'bar' ];
		$request->setHeaders( $params );

		$this->assertEquals( $params, $request->getHeaders() );
	}

	public function testSetAction() {
		$request = new FluentRequest();

		$request->setAction( 'fooAction' );

		$this->assertEquals( [ 'action' => 'fooAction' ], $request->getParams() );
	}

}
