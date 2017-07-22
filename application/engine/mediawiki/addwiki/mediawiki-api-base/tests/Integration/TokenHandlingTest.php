<?php

namespace Mediawiki\Api\Test\Integration;

/**
 * @author Addshore
 */
class TokenHandlingTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideTokenTypes
	 *
	 * @covers Mediawiki\Api\MediawikiApi::getToken
	 * @covers Mediawiki\Api\MediawikiSession::getToken
	 */
	public function testGetAnonUserToken() {
		$api = TestEnvironment::newInstance()->getApi();
		$this->assertEquals( '+\\', $api->getToken() );
	}

	public function provideTokenTypes() {
		return [
			[ 'csrf' ],
			[ 'edit' ],
		];
	}

}
