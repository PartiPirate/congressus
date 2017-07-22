<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Pages;
use Mediawiki\DataModel\Revisions;
use Mediawiki\DataModel\Title;

/**
 * @access private
 *
 * @author Addshore
 */
class PageListGetter {

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * Get the set of pages in a given category. Extra parameters can include:
	 *     cmtype: default 'page|subcat|file'
	 *     cmlimit: default 10, maximum 500 (5000 for bots)
	 *
	 * @link https://www.mediawiki.org/wiki/API:Categorymembers
	 * @since 0.3
	 *
	 * @param string $name
	 * @param array $extraParams
	 *
	 * @return Pages
	 */
	public function getPageListFromCategoryName( $name, array $extraParams = [] ) {
		$params = array_merge( $extraParams, [
			'list' => 'categorymembers',
			'cmtitle' => $name,
		] );
		return $this->runQuery( $params, 'cmcontinue', 'categorymembers' );
	}

	/**
	 * List pages that transclude a certain page.
	 *
	 * @link https://www.mediawiki.org/wiki/API:Embeddedin
	 * @since 0.5
	 *
	 * @param string $pageName
	 * @param array $extraParams
	 *
	 * @return Pages
	 */
	public function getPageListFromPageTransclusions( $pageName, array $extraParams = [] ) {
		$params = array_merge( $extraParams, [
			'list' => 'embeddedin',
			'eititle' => $pageName,
		] );
		return $this->runQuery( $params, 'eicontinue', 'embeddedin' );
	}

	/**
	 * Get all pages that link to the given page.
	 *
	 * @link https://www.mediawiki.org/wiki/API:Linkshere
	 * @since 0.5
	 * @uses PageListGetter::runQuery()
	 *
	 * @param string $pageName The page name
	 *
	 * @return Pages
	 */
	public function getFromWhatLinksHere( $pageName ) {
		$params = [
			'prop' => 'info',
			'generator' => 'linkshere',
			'titles' => $pageName,
		];
		return $this->runQuery( $params, 'lhcontinue', 'pages' );
	}

	/**
	 * Get all pages that have the given prefix.
	 *
	 * @link https://www.mediawiki.org/wiki/API:Allpages
	 *
	 * @param string $prefix The page title prefix.
	 *
	 * @return Pages
	 */
	public function getFromPrefix( $prefix ) {
		$params = [
			'list' => 'allpages',
		    'apprefix' => $prefix,
		];
		return $this->runQuery( $params, 'apcontinue', 'allpages' );
	}

	/**
	 * Get up to 10 random pages.
	 *
	 * @link https://www.mediawiki.org/wiki/API:Random
	 * @uses PageListGetter::runQuery()
	 *
	 * @param array $extraParams
	 *
	 * @return Pages
	 */
	public function getRandom( array $extraParams = [] ) {
		$params = array_merge( $extraParams, [ 'list' => 'random' ] );
		return $this->runQuery( $params, null, 'random', 'id', false );
	}

	/**
	 * Run a query to completion.
	 *
	 * @param string[] $params Query parameters
	 * @param string $contName Result subelement name for continue details
	 * @param string $resName Result element name for main results array
	 * @param string $pageIdName Result element name for page ID
	 * @param boolean $cont Whether to continue the query, using multiple requests
	 * @return Pages
	 */
	protected function runQuery( $params, $contName, $resName, $pageIdName = 'pageid', $cont = true ) {
		$pages = new Pages();

		do {
			// Set up continue parameter if it's been set already.
			if ( isset( $result['continue'][$contName] ) ) {
				$params[$contName] = $result['continue'][$contName];
			}

			// Run the actual query.
			$result = $this->api->getRequest( new SimpleRequest( 'query', $params ) );
			if ( !array_key_exists( 'query', $result ) ) {
				return $pages;
			}

			// Add the results to the output page list.
			foreach ( $result['query'][$resName] as $member ) {
				$pageTitle = new Title( $member['title'], $member['ns'] );
				$page = new Page( new PageIdentifier( $pageTitle, $member[$pageIdName] ) );
				$pages->addPage( $page );
			}

		} while ( $cont && isset( $result['continue'] ) );

		return $pages;
	}
}
