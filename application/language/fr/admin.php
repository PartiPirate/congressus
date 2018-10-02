<?php /*
	Copyright 2015-2017 Cédric Levieux, Parti Pirate

	This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

$lang["administration_guide"] = "Gérez ici les informations de gestion de l'application";
$lang["administration_server"] = "Information serveur";
$lang["administration_server_base"] = "Base d'url";
$lang["administration_server_line"] = "Ligne du serveur";
$lang["administration_server_line_dev"] = "Développement";
$lang["administration_server_line_beta"] = "Beta";
$lang["administration_server_line_prod"] = "Production";
$lang["administration_server_timezone"] = "Fuseau horaire";
$lang["administration_server_timezone_none"] = "Aucun";
$lang["administration_server_timezone_europeparis"] = "Europe/Paris";
$lang["administration_database"] = "Information base de données";
$lang["administration_database_host"] = "Hôte BDD";
$lang["administration_database_port"] = "Port BDD";
$lang["administration_database_database"] = "BDD";
$lang["administration_database_login"] = "Login BDD";
$lang["administration_database_password"] = "Password BDD";
$lang["administration_database_galette"] = "Galette BDD";
$lang["administration_database_personae"] = "Personae BDD";
$lang["administration_memcached"] = "Information memcached";
$lang["administration_memcached_host"] = "Hôte Memcached";
$lang["administration_memcached_port"] = "Port Memcached";
$lang["administration_mail"] = "Information mail";
$lang["administration_mail_host"] = "Hôte SMTP";
$lang["administration_mail_port"] = "Port SMTP";
$lang["administration_mail_secure"] = "Sécurisation Mail";
$lang["administration_mail_secure_none"] = "Aucune";
$lang["administration_mail_secure_none_alert"] = "Les mails partent en clair et sont interceptables";
$lang["administration_mail_secure_ssl_alert"] = "Les mails partent chiffrés mais semblent interceptables";
$lang["administration_mail_secure_tls_alert"] = "Les mails partent chiffrés, c'est la bonne pratique actuelle";
$lang["administration_mail_username"] = "Username SMTP";
$lang["administration_mail_password"] = "Password SMTP";
$lang["administration_mail_from_address"] = "Adresse From";
$lang["administration_mail_from_name"] = "Nom From";
$lang["administration_account"] = "Identifiant de l'administrateur";
$lang["administration_account_login"] = "Login Administrateur";
$lang["administration_account_password"] = "Password Administrateur";
$lang["administration_ping_memcached"] = "Tester";
$lang["administration_ping_database"] = "Tester";
$lang["administration_create_database"] = "Créer";
$lang["administration_deploy_database"] = "Déployer";
$lang["administration_mail_test_adress"] = "Adresse où envoyer un mail de test : ";
$lang["administration_mail_test"] = "Envoyer";
$lang["administration_congressus_ballot_majorities"] = "Modalité de vote";

$lang["administration_alert_ok"] = "La configuration a été mise à jour avec succès";
$lang["administration_alert_memcached_ok"] = "La configuration memcached est bonne";
$lang["administration_alert_memcached_no_host"] = "Hôte inconnu";
$lang["administration_alert_ping_ok"] = "La configuration base de données est bonne";
$lang["administration_alert_ping_no_host"] = "Hôte inconnu";
$lang["administration_alert_ping_bad_credentials"] = "Mauvais compte";
$lang["administration_alert_ping_no_database"] = "Base de données inexistante";
$lang["administration_alert_create_ok"] = "Création de la base de données effectuée avec succès";
$lang["administration_alert_deploy_ok"] = "Déploiement de la base de données effectuée avec succès";
$lang["administration_alert_mail_ok"] = "La configuration mail est bonne";
$lang["administration_alert_mail_bad_credentials"] = "Mauvais compte";

$lang["administration_discourse"] = "Discourse";
$lang["administration_discourse_api_key"] = "Clef API";
$lang["administration_discourse_url"] = "Hôte";
$lang["administration_discourse_protocol"] = "Protocole";
$lang["administration_discourse_user"] = "Impersonification";
$lang["administration_discourse_base"] = "Base d'url Discourse";
$lang["administration_discourse_allowed_categories"] = "Catégories autorisées";

$lang["administration_mediawiki"] = "Mediawiki"; 
$lang["administration_mediawiki_url"] = "Hôte";
$lang["administration_mediawiki_login"] = "Identifiant";
$lang["administration_mediawiki_password"] = "Mot de passe";
$lang["administration_mediawiki_base"] = "Base d'url Mediawiki";
$lang["administration_mediawiki_categories"] = "Catégories connues";

$lang["administration_modules"] = "Modules utilisateurs";
$lang["administration_modules_authenticator"] = "Authentification";
$lang["administration_modules_authenticator_galette"] = "Galette";
$lang["administration_modules_authenticator_custom"] = "Custom";
$lang["administration_modules_groups"] = "Sources des groupes";
$lang["administration_modules_groups_personaegroups"] = "Groupes Personae";
$lang["administration_modules_groups_personaethemes"] = "Themes Personae";
$lang["administration_modules_groups_galettegroups"]  = "Groupes Galette";
$lang["administration_modules_groups_galetteallmembers"] = "Tous les membres Galette";
$lang["administration_modules_groups_customgroups"] = "Groupe custom";

?>