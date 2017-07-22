Page Purger
===========

``PagePurger`` allows you to purge a single ``Page`` or multiple ``Pages``.
You can also check whether the ``Page`` or ``Pages`` have been purged successfully.

To get started you need to create ``PagePurger`` object:

.. code-block:: php

$api = new \Mediawiki\Api\MediawikiApi( 'http://localhost/w/api.php' );
$pagePurger = new \Mediawiki\Api\Service\PagePurger( $api );

Purge
-----

Purge a single ``Page``. It will return a ``boolean`` that indicates if the purge operation was successful.

Example:

.. code-block:: php

$page = new \Mediawiki\DataModel\Page(...);
$pagePurger->purge( $page );

PurgePages
----------

Purges every ``Page`` in the ``Pages`` object at once. It will return a new ``Pages`` object *with the purged ``Page``(s) only!*

.. code-block:: php

$pages = new \Mediawiki\DataModel\Pages(...);
$pagePurger->purgePages( $pages );
