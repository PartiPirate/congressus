#! /bin/bash

set -x

originalDirectory=$(pwd)

if [[ $TRAVIS_PHP_VERSION == *"hhvm"* ]]
then
	PHPINI=/etc/hhvm/php.ini
	echo "hhvm.enable_zend_compat = true" >> $PHPINI
fi

mkdir ./../web
cd ./../web

wget https://github.com/wikimedia/mediawiki/archive/$MW.tar.gz
tar -zxf $MW.tar.gz
mv mediawiki-$MW w
ln -s ./w ./wiki

cd w

composer self-update
composer install

mysql -e 'CREATE DATABASE mediawiki;'
php maintenance/install.php --dbtype mysql --dbuser root --dbname mediawiki --dbpath $(pwd) --pass CIPass TravisWiki CIUser

cd $originalDirectory
