# Installation #

## Requirements ##

- A Web server with (apache, nginx, ...) and PHP setup
- A database server compatible with mysql (Ex: mysql, mariadb, ...)
- memcached server
 
## Get the application ##

- Download the source code of the application from https://github.com/PartiPirate/congressus/archive/master.zip and extract the content (outside of the user folder (Ex : */usr/share/nginx/html/congressus/*).
- Make sure that the web server user have the write permition to the config folder (Ex: *chgrp www-data application/config/ && chmod 775 application/config/* )
- Point the root folder of your web server to the folder **application** (Ex : */usr/share/nginx/html/congressus/application*).
- Reload the webserver to take the chnages into account (Ex: *service nginx restart*)
- Point your web browser to the following URL (EX: *http://127.0.0.1/*)
- At this stage, the application will display the administration page and will create some "*empty*" config files in the **config** folder.

## Configuration ##

### Database ###

- You will need the following informations to setup the database : 
  - Database host name, 
  - Port, 
  - Database name, 
  - Credentials to connect to the database.
- Test your connexion
  - The application will offer you to create the database if it doesn't exist.
  - Once the database is created, you can create the tables automaticaly (This will be use as well to update the database schema)

### Memcached ###

- Informations required :
  - Memcache host, 
  - Memcache port.

### SMTP ###

- Informations required :
  - SMTP Hos name
  - SMTP port
  - Encryption type
  - Credentials needed to send email
  - Email from & name
- Try to send a test email to verify your settings
