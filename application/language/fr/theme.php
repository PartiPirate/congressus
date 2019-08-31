<?php /*
	Copyright 2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/

$lang["menu_groups"]                     = "Délégations";
$lang["breadcrumb_groups"]               = "Délégations";
$lang["breadcrumb_group_administration"] = "Administration";
$lang["breadcrumb_theme_administration"] = "Administration";

$lang["groups_guide"] = "Cliquez sur un groupe pour en connaitre la composition et distribuer vos délégations.";

$lang["theme_mandates_label"] = "Mandats en cours";
$lang["theme_mandates_name"] = "Nom";
$lang["theme_mandates_power"] = "Pouvoir";

$lang["theme_demliq_label"] = "Délégation par démocratie liquide";
$lang["theme_demliq_delegation_standard"] = "Délégations standards";
$lang["theme_demliq_delegation_advanced"] = "Délégations avancées";

$lang["theme_random_label"] = "Délégation par tirage au sort";

$lang["theme_delegation_label"] = "Moi, délégué·e…";
$lang["theme_delegation_candidate"] = "J'accepte les délégations";
$lang["theme_delegation_anti"] = "Je refuse les délégations";
$lang["theme_delegation_neutral"] = "Je laisse les gens choisir";

$lang["theme_free_entry"] = "Entrer librement";
$lang["theme_free_exit"] = "Sortir librement";

$lang["theme_admin_fixation_label"] = "Gestion de la fixation";
$lang["theme_admin_fixation_new_button"] = "Nouvelle fixation";
$lang["theme_admin_fixation_end_date_button"] = "Changer date de fin";
$lang["theme_admin_fixation_in_office"] = "En poste";

$lang["theme_admin_fixation_add_label"] = "Utilisateur qui a du pouvoir :";
$lang["theme_admin_fixation_add_identity"] = "email ou pseudo";
$lang["theme_admin_fixation_add_power"] = "pouvoir";
$lang["theme_admin_fixation_add_button"] = "Ajouter";
$lang["theme_admin_fixation_remove_user_button"] = "Retirer cet utilisateur";

$lang["theme_giver"] = "+{points} de <a href='member.php?id={giver_id}'>{giver}</a>";
$lang["theme_giver_has_givers"] = " <button class='btn btn-xs btn-default btn-open-givers' data-id='{uuid}' id='button-{uuid}'><i class='fa fa-plus' aria-hidden='true'></i></button><span id='span-{uuid}' class='span-close-minus' style='display: none;'> qui avait : <button class='btn btn-xs btn-default btn-close-givers' data-id='{uuid}'><i class='fa fa-minus' aria-hidden='true'></i></button></span>";

$lang["group_admin_remove_button"] = "Supprimer le groupe";
$lang["group_admin_add_theme_button"] = "Ajouter un thème";
$lang["group_admin_remove_dialog_title"] = "Supprimer le groupe &laquo;<span class='gro_label'></span>&raquo;";
$lang["group_admin_remove"] = "Supprimer";
$lang["theme_admin_remove_button"] = "Supprimer le thème";
$lang["theme_admin_remove_dialog_title"] = "Supprimer le thème &laquo;<span class='the_label'></span>&raquo;";
$lang["theme_admin_remove"] = "Supprimer";

$lang["success_theme_candidate"] = "Votre candidature a été mise à jour";
$lang["success_theme_voting"] = "Votre délégation de pouvoir a été mise à jour";
$lang["success_theme_theme"] = "Le thème a été mis à jour";
$lang["success_theme_fixation"] = "La fixation a été mise à jour";
$lang["success_group_group"] = "Le groupe a été mis à jour";

$lang["error_voting_cycling"] = "Cette délégation n'est pas possible car vous recevez déjà sa délégation";
$lang["error_max_delegations"] = "Cette délégation n'est pas possible car la personne recevant déjà assez de délégations";

?>