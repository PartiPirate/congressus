<?php

namespace Mediawiki\Api\Test\Unit;

use Mediawiki\Api\ApiUser;

/**
 * @author Addshore
 *
 * @covers Mediawiki\Api\ApiUser
 */
class ApiUserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $user, $pass, $domain = null ) {
		$apiUser = new ApiUser( $user, $pass, $domain );
		$this->assertSame( $user, $apiUser->getUsername() );
		$this->assertSame( $pass, $apiUser->getPassword() );
		$this->assertSame( $domain, $apiUser->getDomain() );
	}

	public function provideValidConstruction() {
		return [
			[ 'user', 'pass' ],
			[ 'user', 'pass', 'domain' ],
		];
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $user, $pass, $domain = null ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		 new ApiUser( $user, $pass, $domain );
	}

	public function provideInvalidConstruction() {
		return [
			[ 'user', '' ],
			[ '', 'pass' ],
			[ '', '' ],
			[ 'user', [] ],
			[ 'user', 455667 ],
			[ 34567, 'pass' ],
			[ [], 'pass' ],
			[ 'user', 'pass', [] ],
		];
	}

	/**
	 * @dataProvider provideTestEquals
	 */
	public function testEquals( ApiUser $user1, ApiUser $user2, $shouldEqual ) {
		$this->assertSame( $shouldEqual, $user1->equals( $user2 ) );
		$this->assertSame( $shouldEqual, $user2->equals( $user1 ) );
	}

	public function provideTestEquals() {
		return [
			[ new ApiUser( 'usera', 'passa' ), new ApiUser( 'usera', 'passa' ), true ],
			[ new ApiUser( 'usera', 'passa', 'domain' ), new ApiUser( 'usera', 'passa', 'domain' ), true ],
			[ new ApiUser( 'DIFF', 'passa' ), new ApiUser( 'usera', 'passa' ), false ],
			[ new ApiUser( 'usera', 'DIFF' ), new ApiUser( 'usera', 'passa' ), false ],
			[ new ApiUser( 'usera', 'passa' ), new ApiUser( 'DIFF', 'passa' ), false ],
			[ new ApiUser( 'usera', 'passa' ), new ApiUser( 'usera', 'DIFF' ), false ],
		];
	}

}
