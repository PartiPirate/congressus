These are the release notes for the [mediawiki-api-base](README.md) library.

## Version 2.3.1 (3 May 2017)
* Don't fail on libxml errors if the RSD URL can still be found. PR [#35](https://github.com/addwiki/mediawiki-api-base/pull/35), Fixes [T163527](https://phabricator.wikimedia.org/T163527).

## Version 2.3.0 (27 April 2017)

* All guzzle configuration settings can now be overridden in `ClientFactory`. [#27](https://github.com/addwiki/mediawiki-api-base/pull/27)
* Requests that fail due to maxlag will be automatically retried. [#28](https://github.com/addwiki/mediawiki-api-base/pull/28). Fixes [T143193](https://phabricator.wikimedia.org/T143193).
* Added `MediawikiApi::getApiUrl`. [#24](https://github.com/addwiki/mediawiki-api-base/pull/24)
* Debugging infomation now logged when login fails. [#26](https://github.com/addwiki/mediawiki-api-base/pull/26)
* UsageException messages now include the error code and result the API returned. [#31](https://github.com/addwiki/mediawiki-api-base/pull/31)
* Both formatversion=2 and old style API results supported [#33](https://github.com/addwiki/mediawiki-api-base/pull/33)
* Fix [MediawikiApi::newFromPage() fails on non-XML HTML](https://phabricator.wikimedia.org/T163527). [#34](https://github.com/addwiki/mediawiki-api-base/pull/34)
* Various CI improvements.

## Version 2.2.1 (3 August 2016)

* Cast SimpleXMLElements attributes as string in `MediawikiApi::newFromPage()`

## Version 2.2.0 (18 January 2016)

* Added `MediawikiApiInterface`, now implemented by `MediawikiApi`
* Added `ApiRequester`, now implemented by `MediawikiApi`
* Added `AsyncApiRequester`, now implemented by `MediawikiApi`
* The constructor of `MediawikiApi` was made package public

## Version 2.1.0 (29 December 2015)

* Retry throttled actions that return a failed-save code and anti-abuse message
* Added delay between retried requests
* Added and used `Guzzle/ClientFactory`

## Version 2.0.0 (18 December 2015)

* Added `MediawikiApi::newFromApiEndpoint` and `MediawikiApi::newFromPage`
* MediawikiApi constructor access marked as private (please use static factory methods)
* Added async methods to MediawikiApi `getRequestAsync` & `postRequestAsync`
* Requires "guzzlehttp/guzzle": "~6.0" ( From "guzzle/guzzle": "~5.2" )
* Requires "guzzlehttp/promises": "~1.0"

## Version 1.1.0 (5 September 2015)

* Requests that encounter a connection exception are now retried
* Requests that result in non blocking mediawiki api error codes are now retried (ratelimited, readonly, internal_api_error_DBQueryError)
* MediawikiApi now implements PSR-3 LoggerAwareInterface
* MediawikiSession now implements PSR-3 LoggerAwareInterface
* MediawikiApi no longer raises PHP warnings, instead it logs warnings

## Version 1.0.0 (23 August 2015)

* Added `FluentRequest` object
* Requires "guzzlehttp/retry-subscriber": "~2.0"

## Version 0.3 (1 June 2015)

* UsageExceptions can now contain the full api result array
* No longer uses addwiki/guzzle-mediawiki-client
* Now using "guzzlehttp/guzzle": "~5.0" ( From "guzzle/guzzle": "~3.2" )
* Added getHeaders method to Request interface
* ApiUser now accepts a domain

## Version 0.2 (13 January 2015)

### Compatibility changes

* Session objects now use action=query&meta=tokens to get tokens when possible.
NOTE: [Token names have changed between versions](//www.mediawiki.org/wiki/API:Tokens)

### Deprecations

* MediawikiApi getAction and postAction methods have been deprecated in favour of getRequest and postRequest

### New features

* If warnings are present in API results E_USER_WARNING errors are triggered
* The Request interface and SimpleRequest class have been added
* MediawikiApi now has a getRequest and postRequest method
* MediawikiApi now has a getVersion method
* Unsuccessful logins now throw a UsageException with extra details

## Version 0.1.2 (25 May 2014)

* Fix issue where API tokens were not returned

## Version 0.1 (12 May 2014)

* Initial release after split from mediawiki-api lib
