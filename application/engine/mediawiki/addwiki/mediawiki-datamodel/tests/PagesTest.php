<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Pages;

/**
 * @covers \Mediawiki\DataModel\Pages
 * @author Addshore
 */
class PagesTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $input, $expected ) {
		$pages = new Pages( $input );
		$this->assertEquals( $expected, $pages->toArray() );
	}

	public function provideValidConstruction() {
		$mockTitle = $this->getMockBuilder( 'Mediawiki\DataModel\Title' )
			->disableOriginalConstructor()
			->getMock();
		$mockRevisions = $this->getMockBuilder( 'Mediawiki\DataModel\Revisions' )
			->disableOriginalConstructor()
			->getMock();

		//todo mock these
		$page1 = new Page( new PageIdentifier( $mockTitle, 1 ), $mockRevisions );
		$page2 = new Page( new PageIdentifier( $mockTitle, 2 ), $mockRevisions );
		$page4 = new Page( new PageIdentifier( $mockTitle, 4 ), $mockRevisions );

		return array(
			array( array( $page1 ), array( 1 => $page1 ) ),
			array( array( $page2, $page1 ), array( 1 => $page1, 2 => $page2 ) ),
			array( array( $page4, $page1 ), array( 1 => $page1, 4 => $page4 ) ),
			array( new Pages( array( $page4, $page1 ) ), array( 1 => $page1, 4 => $page4 ) ),
		);
	}

}
