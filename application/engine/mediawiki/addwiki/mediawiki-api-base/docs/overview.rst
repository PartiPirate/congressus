========
Overview
========

addwiki/mediawiki-api-base is a PHP HTTP client wrapped around guzzle that makes it easy to interest with a mediawiki installation.

#. Uses PSR-3 interfaces for logging
#. Handles Mediawiki login, sessions, cookies and tokens
#. Handles response errors by throwing catchable UsageExceptions
#. Retries failed requests where possible
#. Allows Async requests

Requirements
========================

#. PHP 5.5.0
#. Guzzle HTTP library ~6.0

.. _installation:

Installation
========================

The recommended way to install this library is with
`Composer <http://getcomposer.org>`_. Composer is a dependency management tool
for PHP that allows you to declare the dependencies your project needs and
installs them into your project.

.. code-block:: bash

    # Install Composer
    curl -sS https://getcomposer.org/installer | php

You can add addwiki/mediawiki-api-base as a dependency using the composer.phar CLI:

.. code-block:: bash

    php composer.phar require addwiki/mediawiki-api-base:~2.0

Alternatively, you can specify addwiki/mediawiki-api-base as a dependency in your project's
existing composer.json file:

.. code-block:: js

    {
      "require": {
         "addwiki/mediawiki-api-base": "~2.0"
      }
   }

After installing, you need to require Composer's autoloader:

.. code-block:: php

    require 'vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and
other best-practices for defining dependencies at `getcomposer.org <http://getcomposer.org>`_.


Bleeding edge
--------------------------

During your development, you can keep up with the latest changes on the master
branch by setting the version requirement for addwiki/mediawiki-api-base to ``~2.0@dev``.

.. code-block:: js

   {
      "require": {
         "addwiki/mediawiki-api-base": "~2.0@dev"
      }
   }


License
===================

Licensed using the `GPL-2.0+ <https://opensource.org/licenses/GPL-2.0>`_.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Contributing
========================

Running the tests
-----------------

In order to contribute, you'll need to checkout the source from GitHub and
install the dependencies using Composer:

.. code-block:: bash

    git clone https://github.com/addwiki/mediawiki-api-base.git
    cd mediawiki-api-base
    curl -s http://getcomposer.org/installer | php
    ./composer.phar install --dev

The library is tested with a combination of linters and phpunit. Run all of the tests as follows:

.. code-block:: bash

    ./composer.phar test

You can choose to run each part of the whole test suite individually using the following commands:

.. code-block:: bash

    # Run the linting only
    ./composer.phar lint
    # Run phpunit only
    ./composer.phar phpunit
    # Run only the phpunit unit tests
    ./composer.phar phpunit-unit
    # Run only the phpunit integration tests
    ./composer.phar phpunit-integration