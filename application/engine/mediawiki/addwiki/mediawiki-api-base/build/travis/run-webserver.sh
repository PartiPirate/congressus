#! /bin/bash

set -x

# HHVM doesn't have a built in web server
# We can't guarantee any single PHP version is always installed on Travis hosts
# So list the versions and try to pick the latest PHP version
# Also the web server on 7.1 seems to have issues, so don't use that?
if [[ $TRAVIS_PHP_VERSION == *"hhvm"* ]] || [[ $TRAVIS_PHP_VERSION == *"7.1"* ]]
then
	WEBSERVERPHPVERSION=`phpenv versions | grep -v system | grep -v hhvm | grep -v 7.1 | tail -n 1 | xargs`
	phpenv global $WEBSERVERPHPVERSION
	php --version
fi

# Run a web server for MediaWiki and wait until it is up
nohup php -S 0.0.0.0:8080 -t ./../web > /dev/null 2>&1 &
until curl -s localhost:8080; do true; done > /dev/null 2>&1

# Switch back to the actual php version requested for this build if needed
if [ -v $WEBSERVERPHPVERSION ]
then
	phpenv global $TRAVIS_PHP_VERSION
	php --version
fi
