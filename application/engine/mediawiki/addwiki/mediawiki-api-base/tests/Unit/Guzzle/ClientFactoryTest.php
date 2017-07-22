<?php

namespace Mediawiki\Api\Test\Unit\Guzzle;

use GuzzleHttp\HandlerStack;
use Mediawiki\Api\Guzzle\ClientFactory;
use Psr\Http\Message\RequestInterface;

/**
 * @author Christian Schmidt
 *
 * @covers Mediawiki\Api\Guzzle\ClientFactory
 */
class ClientFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testNoConfig() {
		$clientFactory = new ClientFactory();

		$client = $clientFactory->getClient();

		$this->assertSame( $client, $clientFactory->getClient() );

		$config = $client->getConfig();
		$this->assertEquals( $config['headers']['User-Agent'], 'Addwiki - mediawiki-api-base' );

		$this->assertFalse( empty( $config['cookies'] ) );
	}

	public function testUserAgent() {
		$clientFactory = new ClientFactory( [ 'user-agent' => 'Foobar' ] );

		$client = $clientFactory->getClient();

		$this->assertNull( $client->getConfig( 'user-agent' ) );

		$config = $client->getConfig();
		$this->assertEquals( $config['headers']['User-Agent'], 'Foobar' );
	}

	public function testHeaders() {
		$clientFactory = new ClientFactory( [
			'headers' => [
				'User-Agent' => 'Foobar',
				'X-Foo' => 'Bar',
			]
		] );

		$client = $clientFactory->getClient();

		$headers = $client->getConfig( 'headers' );
		$this->assertCount( 2, $headers );
		$this->assertEquals( $headers['User-Agent'], 'Foobar' );
		$this->assertEquals( $headers['X-Foo'], 'Bar' );
	}

	public function testHandler() {
		$handler = HandlerStack::create();

		$clientFactory = new ClientFactory( [ 'handler' => $handler ] );

		$client = $clientFactory->getClient();

		$this->assertSame( $handler, $client->getConfig( 'handler' ) );
	}

	public function testMiddleware() {
		$invoked = false;
		$middleware = function() use ( &$invoked ) {
			return function() use ( &$invoked ) {
				$invoked = true;
			};
		};

		$clientFactory = new ClientFactory( [ 'middleware' => [ $middleware ] ] );

		$client = $clientFactory->getClient();

		$this->assertNull( $client->getConfig( 'middleware' ) );

		$request = $this->getMockBuilder( RequestInterface::class )->getMock();

		$handler = $client->getConfig( 'handler' );
		$handler->remove( 'http_errors' );
		$handler->remove( 'allow_redirects' );
		$handler->remove( 'cookies' );
		$handler->remove( 'prepare_body' );
		$handler( $request, [] );

		$this->assertTrue( $invoked );
	}
}
