<?php

namespace Mediawiki\Api\Test;

use Mediawiki\Api\MediawikiFactory;

/**
 * @covers Mediawiki\Api\MediawikiFactory
 *
 * @author Addshore
 */
class MediawikiFactoryTest extends \PHPUnit_Framework_TestCase {

	public function getMockMediawikiApi() {
		return $this->getMockBuilder( 'Mediawiki\Api\MediawikiApi' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function provideFactoryMethodsTest() {
		return [
			[ 'Mediawiki\Api\Service\RevisionSaver', 'newRevisionSaver' ],
			[ 'Mediawiki\Api\Service\RevisionUndoer', 'newRevisionUndoer' ],
			[ 'Mediawiki\Api\Service\PageGetter', 'newPageGetter' ],
			[ 'Mediawiki\Api\Service\UserGetter', 'newUserGetter' ],
			[ 'Mediawiki\Api\Service\PageDeleter', 'newPageDeleter' ],
			[ 'Mediawiki\Api\Service\PageMover', 'newPageMover' ],
			[ 'Mediawiki\Api\Service\PageListGetter', 'newPageListGetter' ],
			[ 'Mediawiki\Api\Service\PageRestorer', 'newPageRestorer' ],
			[ 'Mediawiki\Api\Service\PagePurger', 'newPagePurger' ],
			[ 'Mediawiki\Api\Service\RevisionRollbacker', 'newRevisionRollbacker' ],
			[ 'Mediawiki\Api\Service\RevisionPatroller', 'newRevisionPatroller' ],
			[ 'Mediawiki\Api\Service\PageProtector', 'newPageProtector' ],
			[ 'Mediawiki\Api\Service\PageWatcher', 'newPageWatcher' ],
			[ 'Mediawiki\Api\Service\RevisionDeleter', 'newRevisionDeleter' ],
			[ 'Mediawiki\Api\Service\RevisionRestorer', 'newRevisionRestorer' ],
			[ 'Mediawiki\Api\Service\UserBlocker', 'newUserBlocker' ],
			[ 'Mediawiki\Api\Service\UserRightsChanger', 'newUserRightsChanger' ],
			[ 'Mediawiki\Api\Service\UserCreator', 'newUserCreator' ],
			[ 'Mediawiki\Api\Service\LogListGetter', 'newLogListGetter' ],
			[ 'Mediawiki\Api\Service\FileUploader', 'newFileUploader' ],
			[ 'Mediawiki\Api\Service\ImageRotator', 'newImageRotator' ],
		];
	}

	/**
	 * @dataProvider provideFactoryMethodsTest
	 */
	public function testFactoryMethod( $class, $method ) {
		$factory = new MediawikiFactory( $this->getMockMediawikiApi() );
		$this->assertInstanceOf( $class, $factory->$method() );
	}

}
