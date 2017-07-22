<?php

namespace Mediawiki\Api\Test;

use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
use PHPUnit_Framework_TestCase;

/**
 * @author Addshore
 */
class PageIntegrationTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var PageIdentifier
	 */
	private static $localPageIdentifier;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$title = new Title( 'TestPage - ' . strval( time() ) );
		self::$localPageIdentifier = new PageIdentifier( $title );
	}

	public function testCreatePage() {
		$factory = TestEnvironment::newDefault()->getFactory();
		$this->assertTrue(
			$factory->newRevisionSaver()->save(
				new Revision(
					new Content( 'testCreatePage_content' ),
					self::$localPageIdentifier
				)
			),
			'Failed to Create Page ' . self::$localPageIdentifier->getTitle()->getText()
		);
	}

	/**
	 * This is testGetPageUsingTitle as currently we only know the title
	 * @depends testCreatePage
	 */
	public function testGetPageUsingTitle() {
		$factory = TestEnvironment::newDefault()->getFactory();
		$page = $factory->newPageGetter()->getFromPageIdentifier( self::$localPageIdentifier );
		$this->assertTrue( is_int( $page->getPageIdentifier()->getId() ) );
		$title = $page->getPageIdentifier()->getTitle();
		$this->assertEquals( self::$localPageIdentifier->getTitle(), $title );
		$content = $page->getRevisions()->getLatest()->getContent()->getData();
		$this->assertEquals( 'testCreatePage_content', $content );
		self::$localPageIdentifier = $page->getPageIdentifier();
	}

	/**
	 * @depends testGetPageUsingTitle
	 */
	public function testGetPageUsingId() {
		$factory = TestEnvironment::newDefault()->getFactory();
		$page = $factory->newPageGetter()->getFromPageId( self::$localPageIdentifier->getId() );
		$this->assertEquals( self::$localPageIdentifier->getId(), $page->getPageIdentifier()->getId() );
		$title = $page->getPageIdentifier()->getTitle();
		$this->assertEquals( self::$localPageIdentifier->getTitle(), $title );
	}

}
