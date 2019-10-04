# Installation #

## Voraussetzungen ##

- Ein Webserver mit (apache, nginx, ...) und PHP mit folgenden aktivierten Plugins :
  - mbstring
  - gd
- Ein Datenbankserver, der mit MySQL kompatibel ist (zB.: mysql, mariadb, ...)
- memcached server
 
## Hol dir die Anwendung ##

- Downloade den Quellcode der Anwendung von https://github.com/PartiPirate/congressus/archive/master.zip und entpacke den Inhalt außerhalb deines Benutzer-Ordners (zB.: */usr/share/nginx/html/congressus/*).
- Stelle sicher, dass dein Webserver User Schreibrechte für den config Ordner besitzt (zB.: *chgrp www-data application/config/ && chmod 775 application/config/* )
- Stelle den Hauptordner deines Webservers auf den Ordner **application** um (zB.: */usr/share/nginx/html/congressus/application*).
- Starte deinen Webserver neu, damit die Änderungen wirksam werden (zB.: *service nginx restart*)
- Rufe in deinem Browser folgende URL auf (zB.: *http://127.0.0.1/*)
- Nun sollte die Anwendung die Administrationsseite anzeigen und es werden "*leere*" Konfigurationsdateien im **config** Ordner angelegt.

## Konfiguration ##

### Datenbank ###

- Du benötigst folgende Informationen, um die Datenbank anzulegen : 
  - Hostname der Datenbank, 
  - Port, 
  - Datenbankname, 
  - Logindaten um dich zur Datenbank zu verbinden.
- Überprüfe deine Verbindung
  - Die Anwendung bietet dir an, die Datenbank für dich anzulegen, sofern sie noch nicht existiert.
  - Sobald die Datenbank erstellt wurde, kannst du die Tabellen automatisch erstellen (Damit wird auch das Datenbank-Schema geupdatet)

### Memcached ###

- Benötigte Informationen :
  - Memcache host, 
  - Memcache port.

### SMTP ###

- Benötigte Informationen :
  - SMTP Hostname
  - SMTP port
  - Art der Verschlüsselung
  - Logindaten um Mails zu senden
  - Email von & Name
- Versuche eine Testmail zu senden, um deine Einstellungen zu überprüfen
