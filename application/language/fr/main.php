<?php /*
	Copyright 2014-2015 Cédric Levieux, Jérémy Collot, ArmagNet

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

$lang["date_format"] = "d/m/Y";
$lang["time_format"] = "H:i";
$lang["fulldate_format"] = "dddd DD/MM/YYYY";
$lang["datetime_format"] = "le {date} à {time}";

$lang["common_validate"] = "Valider";
$lang["common_create"] = "Créer";
$lang["common_delete"] = "Supprimer";
$lang["common_fork"] = "Copier";
$lang["common_reject"] = "Rejeter";
$lang["common_connect"] = "Connecter";
$lang["common_ask_for_modification"] = "Demander modification";

$lang["language_fr"] = "Français";
$lang["language_en"] = "Anglais";
$lang["language_de"] = "Allemand";

$lang["congressus_title"] = "Congressus";

$lang["menu_language"] = "Langue : {language}";
$lang["menu_index"] = "Accueil";
$lang["menu_createMeeting"] = "Créer une réunion";
$lang["menu_myMeetings"] = "Mes réunions";
$lang["menu_groupMeetings"] = "Réunions d'instance";
$lang["menu_mypreferences"] = "Mes préférences";
$lang["menu_logout"] = "Se déconnecter";
$lang["menu_login"] = "Se connecter";

$lang["login_title"] = "Identifiez vous";
$lang["login_loginInput"] = "Identifiant";
$lang["login_passwordInput"] = "Mot de passe";
$lang["login_button"] = "Me connecter";
$lang["login_rememberMe"] = "Se souvenir de moi";
$lang["register_link"] = "ou m'enregistrer";
$lang["forgotten_link"] = "j'ai oublié mon mot de passe";

$lang["breadcrumb_index"] = "Accueil";
$lang["breadcrumb_createMeeting"] = "Créer une réunion";
$lang["breadcrumb_myMeetings"] = "Mes réunions";
$lang["breadcrumb_groupMeetings"] = "Réunions d'instance";
$lang["breadcrumb_mypreferences"] = "Mes préférences";
$lang["breadcrumb_forgotten"] = "J'ai oublié mon mot de passe";
$lang["breadcrumb_about"] = "À Propos";
$lang["breadcrumb_search"] = "Rechercher";
$lang["breadcrumb_connect"] = "Se connecter";

$lang["index_guide"] = "<p>Congressus vous permet de gérer des réunions en fonction des différents groupes que peut
fournir l'infrastructure Galette + Personae.</p>
<p>Ainsi vous pouvez définir qui est convoqué aux réunions, qui a le droit de vote, définir des motions, ...</p>
<p>Un outil de recherche permet de rechercher tout ou parti d'un propos, d'une motion, d'une proposition... et
ainsi de retrouver rapidement un point de détail.</p>";
$lang["index_connect_button"] = "Se connecter";

$lang["connect_guide"] = "Bienvenue sur l'écran de connexion de Congressus";
$lang["connect_form_legend"] = "Connexion";
$lang["connect_form_loginInput"] = "Identifiant";
$lang["connect_form_loginHelp"] = "Votre identifiant Galette ou votre email";
$lang["connect_form_passwordInput"] = "Mot de passe";
$lang["connect_form_passwordHelp"] = "Votre mot de passe Galette";

$lang["meeting_noticed_people"] = "Personnes convoqués";
$lang["meeting_visitors"] = "Visiteurs";
$lang["meeting_agenda"] = "Ordre du jour";
$lang["meeting_agenda_point"] = "Point en cours : ";
$lang["meeting_rights"] = "Gestion des droits";

$lang["myMeetings_waiting"] = "Prochaines réunions";
$lang["myMeetings_construction"] = "Réunions en construction";
$lang["myMeetings_open"] = "Réunions en cours";
$lang["myMeetings_closed"] = "Réunions terminés";

$lang["loc_type_mumble"] = "Sur mumble";
$lang["loc_type_afk"] = "En espace réel";
$lang["loc_type_irc"] = "Sur irc";
$lang["loc_type_unknown"] = "à un endroit inconnu";

$lang["notice_mail_subject"] = "[CONGRESSUS] Convocation à la réunion \"{meeting_label}\"";
$lang["notice_mail_content"] = "Bonjour,

Vous êtes convoqué-e à la réunion \"{meeting_label}\".

Vous pouvez consulter l'ordre du jour et participer à la réunion  en cliquant sur le lien ci-dessous :
{meeting_link}

L'espace de rencontre se fera {location_type} à cette adresse :
{location_extra}

Congressus";


$lang["mypreferences_guide"] = "Changer mes préférences.";
$lang["mypreferences_form_legend"] = "Configuration de vos accès";
$lang["mypreferences_form_passwordInput"] = "Nouveau mot de passe";
$lang["mypreferences_form_passwordPlaceholder"] = "votre nouveau mot de passe de connexion";
$lang["mypreferences_form_oldInput"] = "Mot de passe actuel";
$lang["mypreferences_form_oldPlaceholder"] = "votre mot de passe de connexion actuel";
$lang["mypreferences_form_confirmationInput"] = "Confirmation";
$lang["mypreferences_form_confirmationPlaceholder"] = "confirmation de votre nouveau mot de passe";
$lang["mypreferences_form_languageInput"] = "Langage";
$lang["mypreferences_form_notificationInput"] = "Notification pour validation";
$lang["mypreferences_form_notification_none"] = "Aucune";
$lang["mypreferences_form_notification_mail"] = "Par mail";
$lang["mypreferences_form_notification_simpledm"] = "Par simple DM";
$lang["mypreferences_form_notification_dm"] = "DM multiple";
$lang["mypreferences_validation_mail_empty"] = "Le champ mail ne peut être vide";
$lang["mypreferences_validation_mail_not_valid"] = "Cette adresse mail n'est pas une adresse valide";
$lang["mypreferences_validation_mail_already_taken"] = "Cette adresse mail est déjà prise";
$lang["mypreferences_form_mailInput"] = "Adresse mail";
$lang["mypreferences_save"] = "Sauver mes préférences";

$lang["forgotten_guide"] = "Vous avez oublié votre mot de passe, bienvenue sur la page qui vour permettra de récuperer un accès";
$lang["forgotten_form_legend"] = "Récupération d'accès";
$lang["forgotten_form_mailInput"] = "Adresse mail";
$lang["forgotten_save"] = "Envoyez moi un mail !";
$lang["forgotten_success_title"] = "Récupération en cours";
$lang["forgotten_success_information"] = "Un mail vous a été envoyé.<br>Ce mail contient un nouveau mot de passe. Veillez à le changer aussitôt que possible.";
$lang["forgotten_mail_subject"] = "[CONGRESSUS] J'ai oublié mon mot de passe";
$lang["forgotten_mail_content"] = "Bonjour,

Il semblerait que vous ayez oublié votre mot de passe sur Congressus. Votre nouveau mot de passe est {password} .
Veuillez le changer aussitôt que vous serez connecté.

L'équipe @Congressus";

$lang["error_cant_change_password"] = "Le changement de mot de passe a échoué";
$lang["ok_operation_success"] = "Opération réussie";
$lang["error_passwords_not_equal"] = "Votre mot de passe et sa confirmation sont différents";
$lang["error_cant_delete_files"] = "Congressus n'arrive pas à supprimer les fichiers d'installation";
$lang["error_cant_connect"] = "Impossible de se connecter à la base de données";
$lang["error_database_already_exists"] = "La base de données existe déjà";
$lang["error_database_dont_exist"] = "La base de données n'existe pas";
$lang["error_login_ban"] = "Votre IP a été bloquée pour 10mn.";
$lang["error_login_bad"] = "Vérifier vos identifiants, l'identification a échouée.";

$lang["install_guide"] = "Bienvenue sur la page d'installation de Congressus.";
$lang["install_tabs_database"] = "Base de données";
$lang["install_tabs_mail"] = "Mail";
$lang["install_tabs_application"] = "Application";
$lang["install_tabs_final"] = "Finalisation";
$lang["install_tabs_license"] = "Licence";
$lang["install_database_form_legend"] = "Configuration des accès base de données";
$lang["install_database_hostInput"] = "Hôte";
$lang["install_database_hostPlaceholder"] = "l'adresse du serveur de base de données";
$lang["install_database_portInput"] = "Port";
$lang["install_database_portPlaceholder"] = "le port du serveur de base de données";
$lang["install_database_loginInput"] = "Identifiant";
$lang["install_database_loginPlaceholder"] = "l'identifiant de connexion";
$lang["install_database_loginHelp"] = "On évite l'utilisateur <em>root</em>";
$lang["install_database_passwordInput"] = "Mot de passe";
$lang["install_database_passwordPlaceholder"] = "le mot de passe de connexion";
$lang["install_database_databaseInput"] = "Base de données";
$lang["install_database_databasePlaceholder"] = "nom de la base de données";
$lang["install_database_operations"] = "Opérations";
$lang["install_database_saveButton"] = "Sauver la configuration";
$lang["install_database_pingButton"] = "Ping";
$lang["install_database_createButton"] = "Créer";
$lang["install_database_deployButton"] = "Déployer";
$lang["install_mail_form_legend"] = "Configuration des accès mail";
$lang["install_mail_hostInput"] = "Hôte";
$lang["install_mail_hostPlaceholder"] = "l'adresse du serveur de mail";
$lang["install_mail_portInput"] = "Port";
$lang["install_mail_portPlaceholder"] = "le port du serveur de mail";
$lang["install_mail_usernameInput"] = "Nom Utilisateur";
$lang["install_mail_usernamePlaceholder"] = "l'identifiant de connexion";
$lang["install_mail_passwordInput"] = "Mot de passe";
$lang["install_mail_passwordPlaceholder"] = "le mot de passe de connexion";
$lang["install_mail_fromMailInput"] = "Adresse émettrice";
$lang["install_mail_fromMailPlaceholder"] = "l'adresse d'émission";
$lang["install_mail_fromNameInput"] = "Nom émetteur";
$lang["install_mail_fromNamePlaceholder"] = "le nom de l'émetteur";
$lang["install_mail_testMailInput"] = "Adresse de test";
$lang["install_mail_testMailPlaceholder"] = "non sauvegardée";
$lang["install_mail_operation"] = "Opérations";
$lang["install_mail_saveButton"] = "Sauver la configuration";
$lang["install_mail_pingButton"] = "Ping";
$lang["install_application_form_legend"] = "Configuration de l'application";
$lang["install_application_baseUrlInput"] = "Url de base de l'application";
$lang["install_application_cronEnabledInput"] = "Autoriser l'envoi de tweet de manière différée";
$lang["install_application_cronEnabledHelp"] = "Veuillez rajouter dans votre table cron la commande <pre>* * * * * cd {path} && php do_cron.php</pre>";
$lang["install_application_saltInput"] = "Sel";
$lang["install_application_saltPlaceholder"] = "sel de l'application pour chiffrement et hachage";
$lang["install_application_defaultLanguageInput"] = "Langue par défaut";
$lang["install_application_operation"] = "Opérations";
$lang["install_application_saveButton"] = "Sauver la configuration";
$lang["install_autodestruct_guide"] = "Vous avez tout testé, tout configuré ? Alors un clic sur <em>autodestruction</em> pour supprimer cet installateur.";
$lang["install_autodestruct"] = "Autodestruction";

$lang["about_footer"] = "À Propos";
$lang["congressus_footer"] = "<a href=\"https://www.congressus.net/\" target=\"_blank\">Congressus</a> est une application fournie par <a href=\"https://www.partipirate.org\" target=\"_blank\">le Parti Pirate</a>";
?>