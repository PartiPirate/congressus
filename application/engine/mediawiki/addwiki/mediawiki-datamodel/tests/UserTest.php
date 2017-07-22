<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\User;

/**
 * @covers \Mediawiki\DataModel\User
 * @author Addshore
 */
class UserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $name, $id, $editcount, $registration, $groups, $rights, $gender ) {
		$user = new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
		$this->assertEquals( $name, $user->getName() );
		$this->assertEquals( $id, $user->getId() );
		$this->assertEquals( $editcount, $user->getEditcount() );
		$this->assertEquals( $registration, $user->getRegistration() );
		$this->assertEquals( $groups['groups'], $user->getGroups() );
		$this->assertEquals( $groups['implicitgroups'], $user->getGroups( 'implicitgroups' ) );
		$this->assertEquals( $rights, $user->getRights() );
		$this->assertEquals( $gender, $user->getGender() );
	}

	public function provideValidConstruction() {
		return array(
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'female' ),
			array( 'Username', 99999999, 99999997, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
		);
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $name, $id, $editcount, $registration, $groups, $rights, $gender ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new User( $name, $id, $editcount, $registration, $groups, $rights, $gender );
	}

	public function provideInvalidConstruction() {
		return array(
			array( 'Username', 1, 1, 'TIMESTAMP', 'bad', array(), 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), 'bad', 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 1 ),
			array( 'Username', 1, 1, 219279148412, array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
			array( 'Username', 1, 'bad', 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
			array( 'Username', 'bad', 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
			array( 14287941, 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'implicitgroups' => array() ), array(), 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array(), 'foo' => array() ), array(), 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array( 'groups' => array() ), array(), 'male' ),
			array( 'Username', 1, 1, 'TIMESTAMP', array(), array(), 'male' ),
		);
	}

}