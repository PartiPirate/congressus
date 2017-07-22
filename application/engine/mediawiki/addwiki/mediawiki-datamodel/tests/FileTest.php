<?php

namespace Mediawiki\DataModel\Test;

use Mediawiki\DataModel\File;
use Mediawiki\DataModel\PageIdentifier;

/**
 * @covers \Mediawiki\DataModel\File
 * @author Addshore
 */
class FileTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideValidConstruction
	 */
	public function testValidConstruction( $url ) {
		$file = new File(
			$url,
			new PageIdentifier( $this->newMockTitle(), 1 ),
			$this->newMockRevisions()
		);
		$this->assertEquals( $url, $file->getUrl() );
	}

	public function provideValidConstruction() {
		return array(
			array( 'http://upload.wikimedia.org/wikipedia/en/3/39/Journal_of_Geek_Studies_-_logo.jpg' ),
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