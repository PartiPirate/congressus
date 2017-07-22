<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\EditInfo;
use PHPUnit_Framework_TestCase;

/**
 * @covers \Mediawiki\DataModel\EditInfo
 * @author Addshore
 */
class EditInfoTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $sum, $minor, $bot ) {
		$flags = new EditInfo( $sum, $minor, $bot );
		$this->assertEquals( $sum, $flags->getSummary() );
		$this->assertEquals( $minor, $flags->getMinor() );
		$this->assertEquals( $bot, $flags->getBot() );
	}

	public function provideValidConstruction() {
		return array(
			array( '', EditInfo::MINOR, EditInfo::BOT ),
			array( '', EditInfo::MINOR, EditInfo::NOTBOT ),
			array( '', EditInfo::NOTMINOR, EditInfo::BOT ),
			array( '', EditInfo::NOTMINOR, EditInfo::NOTBOT ),
			array( 'FOO', EditInfo::NOTMINOR, EditInfo::NOTBOT ),
		);
	}

	/**
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $sum, $minor, $bot ) {
		$this->setExpectedException( 'InvalidArgumentException' );
		new EditInfo( $sum, $minor, $bot );
	}

	public function provideInvalidConstruction() {
		return array(
			array( 1, 2, 3 ),
			array( "foo", false, 3 ),
			array( "foo", 3, false ),
			array( array(), true, false ),
		);
	}

} 