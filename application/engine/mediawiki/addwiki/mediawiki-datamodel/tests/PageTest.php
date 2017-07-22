<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;

/**
 * @covers \Mediawiki\DataModel\Page
 * @author Addshore
 */
class PageTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $pageIdentifier, $revisions ) {
		$page = new Page( $pageIdentifier, $revisions );
		$this->assertEquals( $pageIdentifier, $page->getPageIdentifier() );
		if( is_null( $revisions ) ) {
			$this->assertInstanceOf( 'Mediawiki\DataModel\Revisions', $page->getRevisions() );
		} else {
			$this->assertEquals( $revisions, $page->getRevisions() );
		}
	}

	public function provideValidConstruction() {
		return array(
			array( null, null ),
			array( null, $this->newMockRevisions() ),
			array( new PageIdentifier( $this->newMockTitle(), 1 ), $this->newMockRevisions() ),
			array( new PageIdentifier( $this->newMockTitle(), 123 ), null ),
		);
	}

	private function newMockTitle() {
		return $this->getMockBuilder( '\Mediawiki\DataModel\Title' )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newMockRevisions() {
		return $this->getMockBuilder( '\Mediawiki\DataModel\Revisions' )
			->disableOriginalConstructor()
			->getMock();
	}

} 