Release Notes
=============

These are the release notes for [mediawiki-api](http://addwiki.readthedocs.io/projects/mediawiki-api/).

## Version 0.7.1 (8th March 2017)

* Fixed dependancy on addwiki/mediawiki-datamodel

## Version 0.7 (March 2017)

* Documentation! This package now has a
  [dedicated documentation website](https://addwiki.readthedocs.io/projects/mediawiki-api/).
* A new NamespaceGetter service with which you can get all namespaces,
  or a single namespace by localised name, alias, or canonical name
  ([#39](https://github.com/addwiki/mediawiki-api/pull/39), [#41](https://github.com/addwiki/mediawiki-api/pull/41)).
* A new CategoryTraverser service for descending (all levels of) category trees
  and either retrieving all pages or performing some action on each page.
* A new method to PagePurger for purging multiple pages at once ([#36](https://github.com/addwiki/mediawiki-api/pull/36)). 
* All methods of the PageListGetter now continue their queries where the first request doesn't retrieve the whole result set
  ([#31](https://github.com/addwiki/mediawiki-api/pull/31)).
* Bug [#40](https://github.com/addwiki/mediawiki-api/pull/40) fixed with `RevisionSaver::save()` overwriting EditInfo if null.
* Integration tests: more documentation about how to run integration tests locally,
  and the tests are running on Travis CI.
* Lots of fixes to coding-standards and in-code documentation.

## Version 0.6 (3 August 2016)

* Adds newParser method to factory
* Use the new API continuation mode
* Fix ignored bot assertion in EditInfo

## Version 0.5.1 (7 September 2015)

* Adds ApiGenerator interface
* Adds AnonymousGenerator implementation of Generator
* Adds FluentGenerator implementation of Generator

## Version 0.5 (4 September 2015)

####Breaks

* LogListGetter now requires mediawiki verison 1.25 or above
* PageListGetter now requires mediawiki verison 1.25 or above
* Removed ALL Options objects

####Additions

* Introduces RevisionUndoer service
* Introduces UserCreator service
* Introduces FileUploader service
* Introduces ImageRotator service

####Libs

* Using mediawiki-api-base 1.0
* Using mediawiki-datamodel 0.6

## Version 0.4 (13 January 2015)

* Issue#8 PageListGetter methods now construct pages with a Title object rather than string
* Page(Deleter|ListGetter|Mover|Protector|Restorer), User(Blocker|RightsChanger) service methods now require an *Options object rather than a selection of parameters.
* Implemented PageListGetter::getRandom

## Version 0.3 (2014-06-24)

* Removes NewEditInfo and NewRevision
* Moved basic api functionality to a separate base lib (mediawiki-api-base)
* Repos renamed to Getters
* PageGetter, RevisionSaver and UserGetter moved to the Service Namespace
* Introduces MediawikiFactory
* Introduces PageDeleter service
* Introduces PageListGetter service
* Introduces PageProtector service
* Introduces PagePurger service
* Introduces RevisionDeleter service
* Introduces RevisionPatroller service
* Introduces RevisionRollbacker service
* Introduces UserBlocker service
* Introduces UserRightsChanger service
* Introduces PageRestorer service
* Introduces RevisionRestorer service
* Correctly handle non existent users in UserGetter


## Version 0.2 (2014-02-23)

* Altered everything for changed in mediawiki-datamodel
* Removed Edit << action class
* Introduces NewEditInfo and NewRevision


## Version 0.1 (2014-02-23)

Initial release with the following features:

* MediawikiApi
* ApiUser
* MediawikiSession
* UsageExceptions
* PageRepo
* UserRepo
* EditSaver
* Edit << action
