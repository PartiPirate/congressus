<?php

namespace Mediawiki\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Mediawiki\DataModel\NamespaceInfo;

/**
 * @access private
 *
 * @author gbirke
 */
class NamespaceGetter
{
	private $api;

	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	/**
	 * Find a namespace by its canonical name
	 *
	 * @param string $canonicalName
	 * @return NamespaceInfo|null
	 */
	public function getNamespaceByCanonicalName( $canonicalName ) {
		$result = $this->getNamespaceResult()['query'];
		foreach ( $result['namespaces'] as $nsInfo ) {
			if ( !empty( $nsInfo['canonical'] ) && $nsInfo['canonical'] === $canonicalName ) {
				return $this->createNamespaceFromQuery( $nsInfo, $result['namespacealiases'] );
			}
		}
		return null;
	}

	/**
	 * Find a namespace by its canonical name, local name or namespace alias
	 *
	 * @param string $name
	 * @return NamespaceInfo|null
	 */
	public function getNamespaceByName( $name ) {
		$result = $this->getNamespaceResult()['query'];
		foreach ( $result['namespaces'] as $nsInfo ) {
			if ( ( !empty( $nsInfo['canonical'] ) && $nsInfo['canonical'] === $name ) ||
				$nsInfo['*'] === $name ) {
				return $this->createNamespaceFromQuery( $nsInfo, $result['namespacealiases'] );
			}
		}
		foreach ( $result['namespacealiases'] as $alias ) {
			if ( $alias['*'] === $name && !empty( $result['namespaces'][$alias['id']] ) ) {
				return $this->createNamespaceFromQuery(
					$result['namespaces'][$alias['id']],
					$result['namespacealiases']
				);
			}
		}
		return null;
	}

	/**
	 * @return NamespaceInfo[]
	 */
	public function getNamespaces() {
		$namespaces = [];
		$result =  $this->getNamespaceResult()['query'];
		foreach ( $result['namespaces'] as $nsInfo ) {
			$namespaces[$nsInfo['id']] = $this->createNamespaceFromQuery(
				$nsInfo, $result['namespacealiases']
			);
		}
		return $namespaces;
	}

	private function createNamespaceFromQuery( $nsInfo, $namespaceAliases ) {
		return new NamespaceInfo(
			$nsInfo['id'],
			empty( $nsInfo['canonical'] ) ? '' : $nsInfo['canonical'],
			$nsInfo['*'],
			$nsInfo['case'],
			empty( $nsInfo['defaultcontentmodel'] ) ? null : $nsInfo['defaultcontentmodel'],
			$this->getAliases( $nsInfo['id'], $namespaceAliases )
		);
	}

	/**
	 * @param int $id
	 * @param array $namespaceAliases Alias list, as returned by the API
	 * @return string[]
	 */
	private function getAliases( $id, $namespaceAliases ) {
		$aliases = [];
		foreach ( $namespaceAliases as $alias ) {
			if ( $alias['id'] === $id ) {
				$aliases[] = $alias['*'];
			}
		}
		return $aliases;
	}

	/**
	 * @return array
	 */
	private function getNamespaceResult() {
		return $this->api->getRequest( new SimpleRequest(
			'query', [
				'meta' => 'siteinfo',
				'siprop' => 'namespaces|namespacealiases'
			]
		) );
	}

}
