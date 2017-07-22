<?php

namespace Mediawiki\Api\Test;

use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
use PHPUnit_Framework_TestCase;

/**
 * Test the \Mediawiki\Api\Service\PageListGetter class.
 */
class PageListGetterTest extends PHPUnit_Framework_TestCase {

	/** @var string */
	private $emptyCatName = 'Category:Empty category';

	/** @var string */
	private $nonemptyCatName = 'Category:Test category';

	/** @var \Mediawiki\Api\Service\PageListGetter */
	private $pageListGetter;

	/**
	 * Set up some test categories and pages.
	 */
	public function setUp() {
		$testEnvironment = TestEnvironment::newDefault();
		$factory = $testEnvironment->getFactory();

		// An empty category.
		$emptyCat = new PageIdentifier( new Title( $this->emptyCatName ) );
		$factory->newRevisionSaver()->save( new Revision( new Content( '' ), $emptyCat ) );

		// A non-empty category.
		$testCat = new PageIdentifier( new Title( $this->nonemptyCatName ) );
		$factory->newRevisionSaver()->save( new Revision( new Content( '' ), $testCat ) );

		// Some pages in the latter.
		// (Count must exceed the default categorymember result set size of 10.)
		$revisionSaver = $factory->newRevisionSaver();
		for ( $i = 1; $i <= 35; $i++ ) {
			$testCat = new PageIdentifier( new Title( "Test page $i" ) );
			// Even pages link to Main Page, odd pages transclude {{test}}.
			$mainPageLink = ( ( $i % 2 ) == 0 ) ? 'Go to [[Main Page]].' : 'This is a {{test}}.';
			$content = new Content( "$mainPageLink\n\n[[$this->nonemptyCatName]]" );
			$revisionSaver->save( new Revision( $content, $testCat ) );
		}

		// Run all jobs, to make sure everything is up to date.
		$testEnvironment->runJobs();

		$this->pageListGetter = $factory->newPageListGetter();
	}

	public function testGetPageListFromCategoryName() {
		// The empty category.
		$emptyCategory = $this->pageListGetter->getPageListFromCategoryName( $this->emptyCatName );
		$this->assertCount( 0, $emptyCategory->toArray() );

		// The nonempty category.
		$testCategory = $this->pageListGetter->getPageListFromCategoryName( $this->nonemptyCatName );
		$this->assertCount( 35, $testCategory->toArray() );
	}

	public function testGetPageListFromPageTransclusions() {
		$linksHere = $this->pageListGetter->getPageListFromPageTransclusions( 'Template:Test' );
		// Only odd-numbered test pages link to the 'Test' template.
		$this->assertCount( 18, $linksHere->toArray() );
	}

	public function testGetFromWhatLinksHere() {
		// Every even-numbered test page links to Main Page.
		$mainPageLinks = $this->pageListGetter->getFromWhatLinksHere( 'Main Page' );
		$this->assertCount( 17, $mainPageLinks->toArray() );

		// Nothing links to 'Test page 4'.
		$testPageLinks = $this->pageListGetter->getFromWhatLinksHere( 'Test page 4' );
		$this->assertCount( 0, $testPageLinks->toArray() );
	}

	public function testGetFromPrefix() {
		// Pages with this prefix should be test pages 1, & 10-15; i.e. 7 of them.
		$testPages = $this->pageListGetter->getFromPrefix( 'Test page 1' );
		$this->assertCount( 11, $testPages->toArray() );
	}

	public function testGetRandom() {
		// Default is 1.
		$randomPages1 = $this->pageListGetter->getRandom();
		$this->assertCount( 1, $randomPages1->toArray() );

		// 8 random pages.
		$randomPages2 = $this->pageListGetter->getRandom( [ 'rnlimit' => 8 ] );
		$this->assertCount( 8, $randomPages2->toArray() );
	}

}
