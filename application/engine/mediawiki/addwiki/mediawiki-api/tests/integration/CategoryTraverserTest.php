<?php

namespace Mediawiki\Api\Test;

use Mediawiki\Api\CategoryLoopException;
use Mediawiki\Api\Service\CategoryTraverser;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Title;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Content;

class CategoryTraverserTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Mediawiki\Api\MediawikiFactory */
	protected $factory;

	/** @var \Mediawiki\Api\Service\CategoryTraverser */
	protected $traverser;

	public function setUp() {
		parent::setUp();
		$this->factory = TestEnvironment::newDefault()->getFactory();
		$this->traverser = $this->factory->newCategoryTraverser();
	}

	/**
	 * A convenience wrapper around a PageDeleter.
	 * @param string[] $titles The titles to delete.
	 */
	public function deletePages( $titles ) {
		$deleter = $this->factory->newPageDeleter();
		foreach ( $titles as $t ) {
			// @todo Properly delete?
			// $deleter->deleteFromPageTitle( new Title( $t ) );
			$this->savePage( $t, '' );
		}
	}

	/**
	 * A convenience wrapper to a RevisionSaver.
	 * @param string $title The title of the new page.
	 * @param string $content The wikitext to save to the page.
	 * @return Page The saved Page.
	 */
	protected function savePage( $title, $content ) {
		$pageIdentifier = new PageIdentifier( new Title( $title ) );
		$revision = new Revision( new Content( $content ), $pageIdentifier );
		$this->factory->newRevisionSaver()->save( $revision );
		return $this->factory->newPageGetter()->getFromPageIdentifier( $pageIdentifier );
	}

	/**
	 * Get a list of all pages in a category or any of its descendants.
	 */
	public function testDescendants() {
		$rootCat = $this->savePage( 'Category:Root category', '' );
		$this->savePage( 'Category:Sub category B', '[[Category:Root category]]' );
		$this->savePage( 'Category:Sub category C', '[[Category:Root category]]' );
		$this->savePage( 'Test page A1', 'Testing. [[Category:Root category]]' );
		$this->savePage( 'Test page B1', 'Testing. [[Category:Sub category B]]' );
		$this->savePage( 'Test page B2', 'Testing. [[Category:Sub category B]]' );
		$this->savePage( 'Test page C1', 'Testing. [[Category:Sub category C]]' );

		$callback = function ( Page $pageInfo, Page $parentCat ) {
			$parentCatName = $parentCat->getPageIdentifier()->getTitle()->getText();
			$thisPageName = $pageInfo->getPageIdentifier()->getTitle()->getText();
			if ( $parentCatName === 'Category:Root category' ) {
				$this->assertEquals( 'Test page A1', $thisPageName );
			}
			if ( $parentCatName === 'Category:Sub category C' ) {
				$this->assertEquals( 'Test page C1', $thisPageName );
			}
		};
		$this->traverser->addCallback( CategoryTraverser::CALLBACK_PAGE, $callback );
		$decendants = $this->traverser->descend( $rootCat );
		$this->assertCount( 4, $decendants->toArray() );
		$this->deletePages( [
			'Category:Root category',
			'Category:Sub category B',
			'Category:Sub category C',
			'Test page A1',
			'Test page B1',
			'Test page B2',
			'Test page C1',
		] );
	}

	/**
	 * Make sure there aren't duplicate results when there are multiple paths to
	 * the same page.
	 */
	public function testDescendantsWithMultiplePaths() {
		$grandparent = $this->savePage( 'Category:Grandparent', '' );
		$this->savePage( 'Category:Parent 1', '[[Category:Grandparent]]' );
		$this->savePage( 'Category:Parent 2', '[[Category:Grandparent]]' );
		$this->savePage( 'Parent 1', '[[Category:Grandparent]]' );
		$this->savePage( 'Child 1', '[[Category:Parent 1]]' );
		$this->savePage( 'Child 2', '[[Category:Parent 1]]' );
		$this->savePage( 'Child 3', '[[Category:Parent 2]]' );
		$decendants = $this->traverser->descend( $grandparent );
		$this->assertCount( 4, $decendants->toArray() );
		$this->deletePages( [
			'Category:Grandparent',
			'Category:Parent 1',
			'Category:Parent 2',
			'Child 1',
			'Child 2',
			'Child 3',
		] );
	}

	/**
	 * Categories should only be traversed once. For example, in the following graph, 'C' can be
	 * reached as a child of 'A' or of 'B', but only the first arrival will proceed to 'D':
	 *
	 *     A
	 *    |  \
	 *    |   B
	 *    |  /
	 *    C
	 *    |
	 *    D
	 *
	 */
	public function testDescendantsOnlyVisitCatsOnce() {
		global $wgVisitedCats;
		$wgVisitedCats = [];
		$catA = $this->savePage( 'Category:A cat', '' );
		$this->savePage( 'Category:B cat', 'Testing. [[Category:A cat]]' );
		$this->savePage( 'Category:C cat', 'Testing. [[Category:A cat]][[Category:B cat]]' );
		$this->savePage( 'Category:D cat', 'Testing. [[Category:C cat]]' );
		$callback = function ( Page $pageInfo, Page $parentCat ) {
			global $wgVisitedCats;
			$wgVisitedCats[] = $parentCat->getPageIdentifier()->getTitle()->getText();
		};
		$this->traverser->addCallback( CategoryTraverser::CALLBACK_CATEGORY, $callback );
		$descendants = $this->traverser->descend( $catA );
		$this->assertCount( 0, $descendants->toArray() );
		$this->assertCount( 3, $wgVisitedCats );
		$this->deletePages( [
			'Category:A cat',
			'Category:B cat',
			'Category:C cat',
			'Category:D cat',
		] );
	}

	/**
	 * Category loops are caught on descent.
	 *
	 *          E
	 *        /  \
	 *       F    G
	 *     /  \
	 *    H    I
	 *    |
	 *    E    <-- throw an Exception when we get to this repetition
	 *
	 */
	public function testDescendIntoLoop() {
		$catA = $this->savePage( 'Category:E cat', '[[Category:H cat]]' );
		$catB = $this->savePage( 'Category:F cat', '[[Category:E cat]]' );
		$catC = $this->savePage( 'Category:G cat', '[[Category:E cat]]' );
		$catD = $this->savePage( 'Category:H cat', '[[Category:F cat]]' );
		$catE = $this->savePage( 'Category:I cat', '[[Category:F cat]]' );
		$haveCaught = false;
		try {
			$this->traverser->descend( $catA );
		} catch ( CategoryLoopException $ex ) {
			$haveCaught = true;
			$expectedCatLoop = [
				'Category:E cat',
				'Category:F cat',
				'Category:H cat',
			];
			// Build a simplified representation of the thrown loop pages, to get around different
			// revision IDs.
			$actualCatLoop = [];
			foreach ( $ex->getCategoryPath()->toArray() as $p ) {
				$actualCatLoop[] = $p->getPageIdentifier()->getTitle()->getText();
			}
			$this->assertEquals( $expectedCatLoop, $actualCatLoop );
		}
		$this->assertTrue( $haveCaught );
		$this->deletePages( [
			'Category:E cat',
			'Category:F cat',
			'Category:G cat',
			'Category:H cat',
			'Category:I cat',
		] );
	}

}
