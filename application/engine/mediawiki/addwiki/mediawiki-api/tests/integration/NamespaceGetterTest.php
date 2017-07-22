<?php

namespace Mediawiki\Api\Test;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\Service\NamespaceGetter;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\NamespaceInfo;

class NamespaceGetterTest extends \PHPUnit_Framework_TestCase
{
	public function testGetNamespaceByCanonicalNameReturnsNullIfNamespaceWasNotFound() {
		$nsGetter = new NamespaceGetter( $this->getApi() );
		$this->assertNull( $nsGetter->getNamespaceByCanonicalName( 'Dummy' ) );
	}

	public function testGetNamespaceByCanonicalNameReturnsNamespaceIfNamespaceWasFound() {
		$nsGetter = new NamespaceGetter( $this->getApi() );
		$expectedNamespace = new NamespaceInfo( 1, 'Talk', 'Diskussion', 'first-letter' );
		$this->assertEquals( $expectedNamespace, $nsGetter->getNamespaceByCanonicalName( 'Talk' ) );
	}

	public function testGetNamespaceByNameTriesAllNames() {
		$nsGetter = new NamespaceGetter( $this->getApi() );
		$expectedNamespace = new NamespaceInfo( 1, 'Talk', 'Diskussion', 'first-letter' );
		$this->assertEquals( $expectedNamespace, $nsGetter->getNamespaceByName( 'Talk' ) );
		$this->assertEquals( $expectedNamespace, $nsGetter->getNamespaceByName( 'Diskussion' ) );
	}

	public function testGetNamespaceByNameTriesAliases() {
		$nsGetter = new NamespaceGetter( $this->getApi() );
		$expectedNamespace = new NamespaceInfo(
			3,
			'User talk',
			'Benutzer Diskussion',
			'first-letter',
			null,
			[ 'BD', 'Benutzerin Diskussion' ]
		);
		$this->assertEquals( $expectedNamespace, $nsGetter->getNamespaceByName(
			'Benutzerin Diskussion'
		) );
		$this->assertEquals( $expectedNamespace, $nsGetter->getNamespaceByName( 'BD' ) );
	}

	public function testGetNamespacesReturnsAllNamespaces() {
		$nsGetter = new NamespaceGetter( $this->getApi() );
		$talkNamespace = new NamespaceInfo( 1, 'Talk', 'Diskussion', 'first-letter' );
		$gadgetNamespace = new NamespaceInfo(
			2302,
			'Gadget definition',
			'Gadget-Definition',
			'case-sensitive',
			'GadgetDefinition'
		);
		$namespaces = $nsGetter->getNamespaces();
		$this->assertCount( 27, $namespaces );
		$this->assertArrayHasKey( 1, $namespaces );
		$this->assertEquals( $talkNamespace, $namespaces[1] );
		$this->assertArrayHasKey( 2302, $namespaces );
		$this->assertEquals( $gadgetNamespace, $namespaces[2302] );
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|MediawikiApi
	 */
	private function getApi() {
		$api = $this->getMockBuilder( MediawikiApi::class )->disableOriginalConstructor()->getMock();
		$api->expects( $this->any() )
			->method( 'getRequest' )
			->with( $this->getRequest() )
			->willReturn( $this->getNamespaceFixture() );
		return $api;
	}

	private function getRequest() {

		return new SimpleRequest(
			'query', [
			'meta' => 'siteinfo',
			'siprop' => 'namespaces|namespacealiases'
		] );
	}

	private function getNamespaceFixture() {

		return json_decode( file_get_contents( __DIR__ . '/../fixtures/namespaces.json' ), true );
	}
}
