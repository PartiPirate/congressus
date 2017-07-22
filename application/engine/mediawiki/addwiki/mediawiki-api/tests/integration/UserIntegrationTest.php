<?php

namespace Mediawiki\Api\Test;

use Mediawiki\Api\ApiUser;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 */
class UserIntegrationTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ApiUser
	 */
	private static $localApiUser;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$strTime = strval( time() );
		self::$localApiUser = new ApiUser( 'TestUser - ' . strval( time() ), $strTime . '-pass' );
	}

	public function testCreateUser() {
		$factory = TestEnvironment::newDefault()->getFactory();
		$createResult = $factory->newUserCreator()->create(
			self::$localApiUser->getUsername(),
			self::$localApiUser->getPassword()
		);
		$this->assertTrue( $createResult );
	}

}
