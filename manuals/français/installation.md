# Installation #

## Pré-requis ##

- Un serveur web (apache, nginx, ...) avec PHP
- Un serveur base de données type mysql (mysql, mariadb, ...)
  - Avec des identifiants de connexion
- Un serveur memcached
 
## Récupération de l'application ##

- Récupérez le code source sur le dépot git et mettez le contenur du répertoire **application** dans le repertoire racine de votre application web (*par exemple : /usr/share/nginx/html/congressus*).
- Lancer l'url de l'accès à cette application (*par exemple : http://127.0.0.1/congressus*)
 
À ce stade, l'application vous affiche la page d'administration et vous a créé quelques fichiers de configuration "vierges" dans le repertoire **config**.

## Configuration ##

### Base de données ###

- Entrez les cinq informations pertinentes pour faire le lien avec la base de données : 
  - L'host, 
  - le port, 
  - la nom de votre base, 
  - l'identifiant et mot de passe du compte qui va faire les transactions.
- Testez votre connexion
  - Corrigez les erreurs éventuellement remontées
  - Si votre base de données n'existe pas, l'option de création de base est proposée 
  - Si la base de données existe, ou dans juste après l'avoir créée, vous pouvez déployer les tables nécessaires au bon fonctionnement de l'application (utile aussi pour la mise à jour du schéma de base)

### Memcached ###

- Entrez les deux informations pertinentes pour faire le lien avec le serveur memcached :
  - L'host, 
  - le port.
- Testez votre connexion
  - Corrigez les erreurs éventuellement remontées
