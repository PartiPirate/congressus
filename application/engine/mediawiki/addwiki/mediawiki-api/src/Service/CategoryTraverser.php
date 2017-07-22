<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\CategoryLoopException;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Pages;

/**
 * Category traverser.
 *
 * Note on spelling 'descendant' (from Wiktionary):
 * The adjective, "descending from a biological ancestor", may be spelt either
 * with an 'a' or with an 'e' in the final syllable. However the noun descendant,
 * "one who is the progeny of someone", may be spelt only with an 'a'.
 */
class CategoryTraverser {

	const CALLBACK_CATEGORY = 10;
	const CALLBACK_PAGE = 20;

	/**
	 * @var \Mediawiki\Api\MediawikiApi
	 */
	protected $api;

	/**
	 * @var string[]
	 */
	protected $namespaces;

	/**
	 * @var callable[]
	 */
	protected $callbacks;

	/**
	 * Used to remember the previously-visited categories when traversing.
	 * @var string[]
	 */
	protected $alreadyVisited;

	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
		$this->callbacks = [];
	}

	/**
	 * Query the remote site for the list of namespaces in use, so that later we can tell what's a
	 * category and what's not. This populates $this->namespaces, and will not re-request on
	 * repeated invocations.
	 * @return void
	 */
	protected function retrieveNamespaces() {
		if ( is_array( $this->namespaces ) ) {
			return;
		}
		$params = [ 'meta' => 'siteinfo', 'siprop' => 'namespaces' ];
		$namespaces = $this->api->getRequest( new SimpleRequest( 'query', $params ) );
		if ( isset( $namespaces['query']['namespaces'] ) ) {
			$this->namespaces = $namespaces['query']['namespaces'];
		}
	}

	/**
	 * Register a callback that will be called for each page or category visited during the
	 * traversal.
	 * @param integer $type One of the 'CALLBACK_' constants of this class.
	 * @param callable $callback A callable that takes two \Mediawiki\DataModel\Page parameters.
	 */
	public function addCallback( $type, $callback ) {
		if ( !isset( $this->callbacks[$type] ) ) {
			$this->callbacks[$type] = [];
		}
		$this->callbacks[$type][] = $callback;
	}

	/**
	 * Visit every descendant page of $rootCategoryName (which will be a Category
	 * page, because there are no desecendants of any other pages).
	 * @param Page $rootCat The full name of the page to start at.
	 * @param Page[] $currentPath Used only when recursing into this method, to track each path
	 * through the category hierarchy in case of loops.
	 * @return Pages All descendants of the given category.
	 * @throws CategoryLoopException If a category loop is detected.
	 */
	public function descend( Page $rootCat, $currentPath = null ) {
		// Make sure we know the namespace IDs.
		$this->retrieveNamespaces();

		$rootCatName = $rootCat->getPageIdentifier()->getTitle()->getText();
		if ( is_null( $currentPath ) ) {
			$this->alreadyVisited = [];
			$currentPath = new Pages();
		}
		$this->alreadyVisited[] = $rootCatName;
		$currentPath->addPage( $rootCat );

		// Start a list of child pages.
		$descendants = new Pages();
		do {
		    $pageListGetter = new PageListGetter( $this->api );
			$members = $pageListGetter->getPageListFromCategoryName( $rootCatName );
			foreach ( $members->toArray() as $member ) {
				/** @var Title */
				$memberTitle = $member->getPageIdentifier()->getTitle();

				// See if this page is a Category page.
				$isCat = false;
				if ( isset( $this->namespaces[ $memberTitle->getNs() ] ) ) {
					$ns = $this->namespaces[ $memberTitle->getNs() ];
					$isCat = ( isset( $ns['canonical'] ) && $ns['canonical'] === 'Category' );
				}
				// If it's a category, descend into it.
				if ( $isCat ) {
					// If this member has already been visited on this branch of the traversal,
					// throw an Exception with information about which categories form the loop.
					if ( $currentPath->hasPage( $member ) ) {
						$currentPath->addPage( $member );
						$loop = new CategoryLoopException();
						$loop->setCategoryPath( $currentPath );
						throw $loop;
					}
					// Don't go any further if we've already visited this member
					// (does not indicate a loop, however; we've already caught that above).
					if ( in_array( $memberTitle->getText(), $this->alreadyVisited ) ) {
						continue;
					}
					// Call any registered callbacked, and carry on to the next branch.
					$this->call( self::CALLBACK_CATEGORY, [ $member, $rootCat ] );
					$newDescendants = $this->descend( $member, $currentPath );
					$descendants->addPages( $newDescendants );
					$currentPath = new Pages(); // Re-set the path.
				} else {
				    // If it's a page, add it to the list and carry on.
					$descendants->addPage( $member );
					$this->call( self::CALLBACK_PAGE, [ $member, $rootCat ] );
				}
			}
		} while ( isset( $result['continue'] ) );
		return $descendants;
	}

	/**
	 * Call all the registered callbacks of a particular type.
	 * @param integer $type The callback type; should match one of the 'CALLBACK_' constants.
	 * @param mixed[] $params The parameters to pass to the callback function.
	 */
	protected function call( $type, $params ) {
		if ( !isset( $this->callbacks[$type] ) ) {
			return;
		}
		foreach ( $this->callbacks[$type] as $callback ) {
			if ( is_callable( $callback ) ) {
				call_user_func_array( $callback, $params );
			}
		}
	}

}
