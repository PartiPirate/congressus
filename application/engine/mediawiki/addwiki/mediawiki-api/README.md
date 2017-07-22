mediawiki-api
==================
[![Build Status](https://travis-ci.org/addwiki/mediawiki-api.png?branch=master)](https://travis-ci.org/addwiki/mediawiki-api)
[![Code Coverage](https://scrutinizer-ci.com/g/addwiki/mediawiki-api/badges/coverage.png?s=5bce1c1f0939d278ac715c7846b679a61401b1de)](https://scrutinizer-ci.com/g/addwiki/mediawiki-api/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/addwiki/mediawiki-api/badges/quality-score.png?s=4182ebaf18fb0b22af9bc3e7941fd4e3524c932e)](https://scrutinizer-ci.com/g/addwiki/mediawiki-api/)
[![Dependency Status](https://www.versioneye.com/user/projects/54b92f798d55087422000030/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54b92f798d55087422000030)

On Packagist:
[![Latest Stable Version](https://poser.pugx.org/addwiki/mediawiki-api/version.png)](https://packagist.org/packages/addwiki/mediawiki-api)
[![Download count](https://poser.pugx.org/addwiki/mediawiki-api/d/total.png)](https://packagist.org/packages/addwiki/mediawiki-api)

Issue tracker: https://phabricator.wikimedia.org/project/profile/1490/

## Installation

Use composer to install the library and all its dependencies:

    composer require "addwiki/mediawiki-api:~0.7.0"

## Example Usage

```php
// Load all the stuff
require_once( __DIR__ . '/vendor/autoload.php' );

// Log in to a wiki
$api = new \Mediawiki\Api\MediawikiApi( 'http://localhost/w/api.php' );
$api->login( new \Mediawiki\Api\ApiUser( 'username', 'password' ) );
$services = new \Mediawiki\Api\MediawikiFactory( $api );

// Get a page
$page = $services->newPageGetter()->getFromTitle( 'Foo' );

// Edit a page
$content = new \Mediawiki\DataModel\Content( 'New Text' );
$revision = new \Mediawiki\DataModel\Revision( $content, $page->getPageIdentifier() );
$services->newRevisionSaver()->save( $revision );

// Move a page
$services->newPageMover()->move(
	$services->newPageGetter()->getFromTitle( 'FooBar' ),
	new Title( 'FooBar' )
);

// Delete a page
$services->newPageDeleter()->delete(
	$services->newPageGetter()->getFromTitle( 'DeleteMe!' ),
	array( 'reason' => 'Reason for Deletion' )
);

// Create a new page
$newContent = new \Mediawiki\DataModel\Content( 'Hello World' );
$title = new \Mediawiki\DataModel\Title( 'New Page' );
$identifier = new \Mediawiki\DataModel\PageIdentifier( $title );
$revision = new \Mediawiki\DataModel\Revision( $newContent, $identifier );
$services->newRevisionSaver()->save( $revision );

// List all pages in a category
$pages = $services->newPageListGetter()->getPageListFromCategoryName( 'Category:Cat name' );
```

## Running the integration tests

To run the integration tests, you need to have a running MediaWiki instance. The tests will create pages and categories without using a user account so it's best if you use a test instance. Furthermore you need to turn off rate limiting by adding the line

   $wgGroupPermissions['*']['noratelimit'] = true;

to the `LocalSettings.php` of your MediaWiki.

By default, the tests will use the URL `http://localhost/w/api.php` as the API endpoint. If you have a different URL (e.g. `http://localhost:8080/w/api.php`), you need to configure the URL as an environemnt variable before running the tests. Example:

    export MEDIAWIKI_API_URL='http://localhost:8080/w/api.php'

**Warning:** Running the integration tests can take a long time to complete.
