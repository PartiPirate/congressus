<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\Api\UsageException;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\MediawikiApi
 */
class MediawikiApiTest extends PHPUnit_Framework_TestCase {

	public function provideValidConstruction() {
		return [
			[ 'localhost' ],
			[ 'http://en.wikipedia.org/w/api.php' ],
			[ '127.0.0.1/foo/bar/wwwwwwwww/api.php' ],
		];
	}

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $apiLocation ) {
		new MediawikiApi( $apiLocation );
		$this->assertTrue( true );
	}

	public function provideInvalidConstruction() {
		return [
			[ null ],
			[ 12345678 ],
			[ [] ],
			[ new stdClass() ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $apiLocation ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new MediawikiApi( $apiLocation );
	}

	private function getMockClient() {
		return $this->getMock( 'GuzzleHttp\ClientInterface' );
	}

	private function getMockResponse( $responseValue ) {
		$mock = $this->getMock( 'Psr\Http\Message\ResponseInterface' );
		$mock->expects( $this->any() )
			->method( 'getBody' )
			->will( $this->returnValue( json_encode( $responseValue ) ) );
		return $mock;
	}

	private function getExpectedRequestOpts( $params, $paramsLocation ) {
		return [
			$paramsLocation => array_merge( $params, [ 'format' => 'json' ] ),
			'headers' => [ 'User-Agent' => 'addwiki-mediawiki-client' ],
		];
	}

	public function testGetRequestThrowsUsageExceptionOnError() {
		$client = $this->getMockClient();
		$client->expects( $this->once() )
			->method( 'request' )
			->will( $this->returnValue(
				$this->getMockResponse( [ 'error' => [
					'code' => 'imacode',
					'info' => 'imamsg',
				] ] )
			) );
		$api = new MediawikiApi( '', $client );

		try{
			$api->getRequest( new SimpleRequest( 'foo' ) );
			$this->fail( 'No Usage Exception Thrown' );
		}
		catch ( UsageException $e ) {
			$this->assertEquals( 'imacode', $e->getApiCode() );
			$this->assertEquals( 'imamsg', $e->getRawMessage() );
		}
	}

	public function testPostRequestThrowsUsageExceptionOnError() {
		$client = $this->getMockClient();
		$client->expects( $this->once() )
			->method( 'request' )
			->will( $this->returnValue(
				$this->getMockResponse( [ 'error' => [
					'code' => 'imacode',
					'info' => 'imamsg',
				] ] )
			) );
		$api = new MediawikiApi( '', $client );

		try{
			$api->postRequest( new SimpleRequest( 'foo' ) );
			$this->fail( 'No Usage Exception Thrown' );
		}
		catch ( UsageException $e ) {
			$this->assertSame( 'imacode', $e->getApiCode() );
			$this->assertSame( 'imamsg', $e->getRawMessage() );
		}
	}

	/**
	 * @dataProvider provideActionsParamsResults
	 */
	public function testGetActionReturnsResult( $expectedResult, $action, $params = [] ) {
		$client = $this->getMockClient();
		$params = array_merge( [ 'action' => $action ], $params );
		$client->expects( $this->once() )
			->method( 'request' )
			->with( 'GET', null, $this->getExpectedRequestOpts( $params, 'query' ) )
			->will( $this->returnValue( $this->getMockResponse( $expectedResult ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->getRequest( new SimpleRequest( $action, $params ) );

		$this->assertEquals( $expectedResult, $result );
	}

	/**
	 * @dataProvider provideActionsParamsResults
	 */
	public function testPostActionReturnsResult( $expectedResult, $action, $params = [] ) {
		$client = $this->getMockClient();
		$params = array_merge( [ 'action' => $action ], $params );
		$client->expects( $this->once() )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( $expectedResult ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->postRequest( new SimpleRequest( $action, $params ) );

		$this->assertEquals( $expectedResult, $result );
	}

	private function getNullFilePointer() {
		if ( !file_exists( '/dev/null' ) ) {
			// windows
			return fopen( 'NUL', 'r' );
		}
		return fopen( '/dev/null', 'r' );
	}

	public function testPostActionWithFileReturnsResult() {

		$dummyFile = $this->getNullFilePointer();
		$params = [
			'filename' => 'foo.jpg',
			'file' => $dummyFile,
		];
		$client = $this->getMockClient();
		$client->expects( $this->once() )->method( 'request' )->with(
				'POST',
				null,
				[
					'multipart' => [
						[ 'name' => 'action', 'contents' => 'upload' ],
						[ 'name' => 'filename', 'contents' => 'foo.jpg' ],
						[ 'name' => 'file', 'contents' => $dummyFile ],
						[ 'name' => 'format', 'contents' => 'json' ],
					],
					'headers' => [ 'User-Agent' => 'addwiki-mediawiki-client' ],
				]
			)->will( $this->returnValue( $this->getMockResponse( [ 'success ' => 1 ] ) ) );
		$api = new MediawikiApi( '', $client );

		$result = $api->postRequest( new SimpleRequest( 'upload', $params ) );

		$this->assertEquals( [ 'success ' => 1 ], $result );
	}

	public function provideActionsParamsResults() {
		return [
			[ [ 'key' => 'value' ], 'logout' ],
			[ [ 'key' => 'value' ], 'logout', [ 'param1' => 'v1' ] ],
			[ [ 'key' => 'value', 'key2' => 1212, [] ], 'logout' ],
		];
	}

	public function testGoodLoginSequence() {
		$client = $this->getMockClient();
		$user = new ApiUser( 'U1', 'P1' );
		$eq1 = [
			'action' => 'login',
			'lgname' => 'U1',
			'lgpassword' => 'P1',
		];
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $eq1, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( [ 'login' => [
				'result' => 'NeedToken',
				'token' => 'IamLoginTK',
			] ] ) ) );
		$params = array_merge( $eq1, [ 'lgtoken' => 'IamLoginTK' ] );
		$response = $this->getMockResponse( [ 'login' => [ 'result' => 'Success' ] ] );
		$client->expects( $this->at( 1 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $response ) );
		$api = new MediawikiApi( '', $client );

		$this->assertTrue( $api->login( $user ) );
		$this->assertSame( 'U1', $api->isLoggedin() );
	}

	public function testBadLoginSequence() {
		$client = $this->getMockClient();
		$user = new ApiUser( 'U1', 'P1' );
		$eq1 = [
			'action' => 'login',
			'lgname' => 'U1',
			'lgpassword' => 'P1',
		];
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $eq1, 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( [ 'login' => [
				'result' => 'NeedToken',
				'token' => 'IamLoginTK',
			] ] ) ) );
		$params = array_merge( $eq1, [ 'lgtoken' => 'IamLoginTK' ] );
		$response = $this->getMockResponse( [ 'login' => [ 'result' => 'BADTOKENorsmthin' ] ] );
		$client->expects( $this->at( 1 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( $params, 'form_params' ) )
			->will( $this->returnValue( $response ) );
		$api = new MediawikiApi( '', $client );

		$this->setExpectedException( 'Mediawiki\Api\UsageException' );
		$api->login( $user );
	}

	public function testLogout() {
		$client = $this->getMockClient();
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( [ 'action' => 'logout' ], 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( [] ) ) );
		$api = new MediawikiApi( '', $client );

		$this->assertTrue( $api->logout() );
	}

	public function testLogoutOnFailure() {
		$client = $this->getMockClient();
		$client->expects( $this->at( 0 ) )
			->method( 'request' )
			->with( 'POST', null, $this->getExpectedRequestOpts( [ 'action' => 'logout' ], 'form_params' ) )
			->will( $this->returnValue( $this->getMockResponse( null ) ) );
		$api = new MediawikiApi( '', $client );

		$this->assertFalse( $api->logout() );
	}

	/**
	 * @dataProvider provideVersions
	 */
	public function testGetVersion( $apiValue, $expectedVersion ) {
		$client = $this->getMockClient();
		$params = [ 'action' => 'query', 'meta' => 'siteinfo', 'continue' => '' ];
		$client->expects( $this->exactly( 1 ) )
			->method( 'request' )
			->with( 'GET', null, $this->getExpectedRequestOpts( $params, 'query' ) )
			->will( $this->returnValue( $this->getMockResponse( [
				'query' => [
					'general' => [
						'generator' => $apiValue,
					],
				],
			] ) ) );
		$api = new MediawikiApi( '', $client );
		$this->assertEquals( $expectedVersion, $api->getVersion() );
	}

	public function provideVersions() {
		return [
			[ 'MediaWiki 1.25wmf13', '1.25' ],
			[ 'MediaWiki 1.24.1', '1.24.1' ],
			[ 'MediaWiki 1.19', '1.19' ],
			[ 'MediaWiki 1.0.0', '1.0.0' ],
		];
	}
}
