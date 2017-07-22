<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;

/**
 * @covers \Mediawiki\DataModel\Revision
 * @author Addshore
 */
class RevisionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $content, $pageIdentifier, $id, $editInfo, $user, $timestamp ) {
		$rev = new Revision( $content, $pageIdentifier, $id, $editInfo, $user, $timestamp );
		$this->assertEquals( $content, $rev->getContent() );
		if( !is_null( $pageIdentifier ) ) {
			$this->assertEquals( $pageIdentifier, $rev->getPageIdentifier() );
		} else {
			$this->assertInstanceOf( '\Mediawiki\DataModel\PageIdentifier', $rev->getPageIdentifier() );
		}

		$this->assertEquals( $id, $rev->getId() );
		if( !is_null( $editInfo ) ) {
			$this->assertEquals( $editInfo, $rev->getEditInfo() );
		} else {
			$this->assertInstanceOf( '\Mediawiki\DataModel\EditInfo', $rev->getEditInfo() );
		}
		$this->assertEquals( $user, $rev->getUser() );
		$this->assertEquals( $timestamp, $rev->getTimestamp() );
	}

	public function provideValidConstruction() {
		$mockContent = $this->getMockBuilder( 'Mediawiki\DataModel\Content' )
			->disableOriginalConstructor()
			->getMock();
		$mockEditInfo = $this->getMockBuilder( '\Mediawiki\DataModel\EditInfo' )
			->disableOriginalConstructor()
			->getMock();
		$mockTitle = $this->getMockBuilder( 'Mediawiki\DataModel\Title' )
			->disableOriginalConstructor()
			->getMock();

		return array(
			array( $mockContent, null, null, null, null, null ),
			array( $mockContent, new PageIdentifier( null, 1 ), null , null, null,null ),
			array( $mockContent, new PageIdentifier( null, 1 ), 1 , null, null, null ),
			array( $mockContent, new PageIdentifier( null, 2 ), 1 , $mockEditInfo, null, null ),
			array( $mockContent, new PageIdentifier( $mockTitle ), 1 , $mockEditInfo, 'foo', null ),
			array( $mockContent, new PageIdentifier( $mockTitle, 3 ), 1 , $mockEditInfo, 'foo', '20141212121212' ),
		);
	}

} 