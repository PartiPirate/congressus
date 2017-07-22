Page Lists
==========

The Page List Getter allows you to retrieve lists of pages based on various criteria.
It takes care of continuing queries where they span multiple requests,
ensuring that you get all pages in your result set.
This means that for some lists of pages a great many requests will be sent,
and you should account for this possible performance problem when you request these lists
(e.g. by running these as a background process and caching the results).

To use it, first get a new PageListGetter object from the factory:

.. code-block:: php

   $api = new \Mediawiki\Api\MediawikiApi( 'http://localhost/w/api.php' );
   $services = new \Mediawiki\Api\MediawikiFactory( $api );
   $pageListGetter = $services->newPageListGetter();

The examples below all use this ``$pageListGetter`` object.

All methods of the PageListGetter return ``Page`` objects;
this class is part of the `addwiki/mediawiki-datamodel`_ package,
and is documented in `that page's documentation`_.

.. _addwiki/mediawiki-datamodel: https://packagist.org/packages/addwiki/mediawiki-datamodel
.. _that page's documentation: http://addwiki.readthedocs.io/projects/mediawiki-datamodel/

All pages in a category
-----------------------

Note that the category name as provided should also include the 'Category' namespace prefix
(in the language of the wiki, or in canonical English form).

.. code-block:: php

   $examplePages = $pageListGetter->getPageListFromCategoryName( 'Category:Example pages' );
   foreach ( $examplePages->asArray() as $exPage ) {
       echo $exPage->getTitle()->getText();
   }

Pages that transclude a template
--------------------------------

Although generally it is templates that are transcluded,
any page may be and so any page title can be passed to this method.

.. code-block:: php

   $usingTestTemplate = $pageListGetter->getPageListFromPageTransclusions( 'Template:Test' );

Pages that link to a given page
-------------------------------

Get the list of pages that link to a particular page.

.. code-block:: php

   $backLinks = $pageListGetter->getFromWhatLinksHere( 'Test page' );

Pages with a given prefix
-------------------------

Find pages that have a particular prefix to their title.
This can also be used to find subpages of any page.

.. code-block:: php

   $backLinks = $pageListGetter->getFromPrefix( 'A page/' );

Random pages
------------

Get up to ten random pages at a time.
This method takes the same arguments as the API `list=random`_ query.

.. _list=random: https://www.mediawiki.org/wiki/API:Random

* ``rnlimit`` How many pages to get. No more than 10 (20 for bots) allowed. Default: 1.
* ``rnnamespace`` Pipe-separate list of namespace IDs.
* ``rnfilterredir`` How to filter for redirects. Possible values: ``all``, ``redirects``, ``nonredirects``. Default: ``nonredirects``.

.. code-block:: php

   $backLinks = $pageListGetter->getRandom( [
       'rnlimit' => 7,
       'rnnamespace' => '3|5|6',
       'rnfilterredir' => 'all',
   ] );
