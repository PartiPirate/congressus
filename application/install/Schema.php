<?php /*
	Copyright 2018-2019 Cédric Levieux, Parti Pirate

	This file is part of Installer.

    Installer is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Installer is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Installer.  If not, see <http://www.gnu.org/licenses/>.
*/

// This file contains the schema of the bdd, used for deploy or update of the database

$schema = array("version" => filemtime("install/Schema.php"), "tables" => array());

$schema["tables"]["agendas"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["agendas"]["fields"]["age_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["agendas"]["fields"]["age_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["agendas"]["fields"]["age_parent_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["agendas"]["fields"]["age_order"] = array("type" => "bigint", "size" => 20, "null" => false, "default" => "1");
$schema["tables"]["agendas"]["fields"]["age_active"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["agendas"]["fields"]["age_expected_duration"] = array("type" => "int", "size" => 11, "null" => false, "default" => "60", "comment" => "value in minutes");
$schema["tables"]["agendas"]["fields"]["age_duration"] = array("type" => "int", "size" => 11, "null" => false, "default" => "0", "comment" => "value in minutes");
$schema["tables"]["agendas"]["fields"]["age_label"] = array("type" => "varchar", "size" => 255, "null" => false, "default" => "");
$schema["tables"]["agendas"]["fields"]["age_objects"] = array("type" => "text", "null" => true);
$schema["tables"]["agendas"]["fields"]["age_description"] = array("type" => "text", "null" => false, "default" => "");
$schema["tables"]["agendas"]["indexes"]["age_meeting_id"] = array("age_meeting_id");
$schema["tables"]["agendas"]["indexes"]["age_parent_id"] = array("age_parent_id");
$schema["tables"]["agendas"]["indexes"]["age_order"] = array("age_order");

$schema["tables"]["chats"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["chats"]["fields"]["cha_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["chats"]["fields"]["cha_agenda_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chats"]["fields"]["cha_motion_id"] = array("type" => "bigint", "size" => 20, "null" => true, "comment" => 'A chat can be attached to a motion');
$schema["tables"]["chats"]["fields"]["cha_parent_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chats"]["fields"]["cha_member_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chats"]["fields"]["cha_guest_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chats"]["fields"]["cha_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["chats"]["fields"]["cha_type"] = array("type" => "enum", "size" => "'neutral','pro','against'", "null" => false, "default" => "neutral", "comment" => 'a chat can be neutral (default mode), pro or against');
$schema["tables"]["chats"]["fields"]["cha_text"] = array("type" => "text", "null" => true);
$schema["tables"]["chats"]["fields"]["cha_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["chats"]["indexes"]["cha_agenda_id"] = array("cha_agenda_id");
$schema["tables"]["chats"]["indexes"]["cha_motion_id"] = array("cha_motion_id");
$schema["tables"]["chats"]["indexes"]["cha_parent_id"] = array("cha_parent_id");
$schema["tables"]["chats"]["indexes"]["cha_member_id"] = array("cha_member_id");
$schema["tables"]["chats"]["indexes"]["cha_deleted"] = array("cha_deleted");
$schema["tables"]["chats"]["indexes"]["cha_guest_id"] = array("cha_guest_id");
$schema["tables"]["chats"]["indexes"]["cha_type"] = array("cha_type");
$schema["tables"]["chats"]["indexes"]["cha_datetime"] = array("cha_datetime");

$schema["tables"]["chat_advices"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["chat_advices"]["fields"]["cat_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["chat_advices"]["fields"]["cad_chat_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chat_advices"]["fields"]["cad_user_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["chat_advices"]["fields"]["cad_advice"] = array("type" => "enum", "size" => "'thumb_up','thumb_down','thumb_middle'", "null" => true);
$schema["tables"]["chat_advices"]["indexes"]["cad_chat_id"] = array("cad_chat_id");
$schema["tables"]["chat_advices"]["indexes"]["cad_user_id"] = array("cad_user_id");

$schema["tables"]["co_authors"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["co_authors"]["fields"]["con_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["co_authors"]["fields"]["cad_user_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["co_authors"]["fields"]["cau_object_type"] = array("type" => "enum", "size" => "'motion'", "null" => true);
$schema["tables"]["co_authors"]["fields"]["cau_object_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["co_authors"]["indexes"]["cau_user_id"] = array("cau_user_id");
$schema["tables"]["co_authors"]["indexes"]["cau_object_type"] = array("cau_object_type");
$schema["tables"]["co_authors"]["indexes"]["cau_object_id"] = array("cau_object_id");

$schema["tables"]["conclusions"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["conclusions"]["fields"]["con_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["conclusions"]["fields"]["con_agenda_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["conclusions"]["fields"]["con_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["conclusions"]["fields"]["con_text"] = array("type" => "text", "null" => true);
$schema["tables"]["conclusions"]["indexes"]["con_agenda_id"] = array("con_agenda_id");
$schema["tables"]["conclusions"]["indexes"]["con_deleted"] = array("con_deleted");

$schema["tables"]["guests"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["guests"]["fields"]["gue_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);

$schema["tables"]["locations"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["locations"]["fields"]["loc_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["locations"]["fields"]["loc_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["locations"]["fields"]["loc_principal"] = array("type" => "tinyint", "size" => 1, "null" => false, "default" => "0");
$schema["tables"]["locations"]["fields"]["loc_type"] = array("type" => "enum", "size" => "'mumble','irc','afk','framatalk','discord'", "null" => true);
$schema["tables"]["locations"]["fields"]["loc_channel"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["locations"]["fields"]["loc_extra"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["locations"]["indexes"]["loc_meeting_id"] = array("loc_meeting_id");
$schema["tables"]["locations"]["indexes"]["loc_principal"] = array("loc_principal");

$schema["tables"]["logs"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["logs"]["fields"]["log_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["logs"]["fields"]["log_action"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["logs"]["fields"]["log_user_id"] = array("type" => "varchar", "size" => 10, "null" => true);
$schema["tables"]["logs"]["fields"]["log_ip"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["logs"]["fields"]["log_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["logs"]["fields"]["log_data"] = array("type" => "varchar", "size" => 2048, "null" => true);

$schema["tables"]["meetings"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["meetings"]["fields"]["mee_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["meetings"]["fields"]["mee_label"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_type"] = array("type" => "enum", "size" => "'meeting','construction'", "null" => false, "default" => "meeting");
$schema["tables"]["meetings"]["fields"]["mee_class"] = array("type" => "enum", "size" => "'event-important','event-success','event-warning','event-info','event-inverse','event-special'", "null" => false);
$schema["tables"]["meetings"]["fields"]["mee_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["meetings"]["fields"]["mee_status"] = array("type" => "enum", "size" => "'construction','open','closed','waiting','deleted','template'", "null" => false, "default" => "construction");
$schema["tables"]["meetings"]["fields"]["mee_quorum"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_synchro_vote"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "1");
$schema["tables"]["meetings"]["fields"]["mee_president_member_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_secretary_member_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_secretary_agenda_id"] = array("type" => "bigint", "size" => 20, "null" => true, "comment" => 'Current agenda id viewed by the secretary');
$schema["tables"]["meetings"]["fields"]["mee_meeting_type_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_expected_duration"] = array("type" => "int", "size" => 11, "null" => false, "default" => "60", "comment" => 'value in minutes');
$schema["tables"]["meetings"]["fields"]["mee_start_time"] = array("type" => "datetime", "null" => true);
$schema["tables"]["meetings"]["fields"]["mee_finish_time"] = array("type" => "datetime", "null" => true);
$schema["tables"]["meetings"]["indexes"]["mee_meeting_type_id"] = array("mee_meeting_type_id");
$schema["tables"]["meetings"]["indexes"]["mee_deleted"] = array("mee_deleted");
$schema["tables"]["meetings"]["indexes"]["mee_president_member_id"] = array("mee_president_member_id");
$schema["tables"]["meetings"]["indexes"]["mee_secretary_member_id"] = array("mee_secretary_member_id");
$schema["tables"]["meetings"]["indexes"]["mee_synchro_vote"] = array("mee_synchro_vote");
$schema["tables"]["meetings"]["indexes"]["mee_type"] = array("mee_type");
$schema["tables"]["meetings"]["indexes"]["mee_datetime"] = array("mee_datetime");
$schema["tables"]["meetings"]["indexes"]["mee_expected_duration"] = array("mee_expected_duration");

$schema["tables"]["meeting_rights"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["meeting_rights"]["fields"]["mri_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["meeting_rights"]["fields"]["mri_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["meeting_rights"]["fields"]["mri_right"] = array("type" => "varchar", "size" => 255, "null" => true);

$schema["tables"]["meeting_types"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["meeting_types"]["fields"]["mty_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["meeting_types"]["fields"]["mty_key"] = array("type" => "varchar", "size" => 255, "null" => false);
$schema["tables"]["meeting_types"]["fields"]["mty_default_label"] = array("type" => "varchar", "size" => 255, "null" => false);

$schema["tables"]["motions"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["motions"]["fields"]["mot_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["motions"]["fields"]["mot_author_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["motions"]["fields"]["mot_agenda_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["motions"]["fields"]["mot_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["motions"]["fields"]["mot_status"] = array("type" => "enum", "size" => "'construction','voting','resolved'", "null" => false, "default" => "construction");
$schema["tables"]["motions"]["fields"]["mot_deadline"] = array("type" => "datetime", "null" => true);
$schema["tables"]["motions"]["fields"]["mot_tag_ids"] = array("type" => "varchar", "size" => 2048, "null" => false, "default" => "[]");
$schema["tables"]["motions"]["fields"]["mot_pinned"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0", "comment" => 'set to 1 to force the anymous mode during the vote');
$schema["tables"]["motions"]["fields"]["mot_anonymous"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["motions"]["fields"]["mot_type"] = array("type" => "enum", "size" => "'yes_no','a_b_c'", "null" => false, "default" => "yes_no");
$schema["tables"]["motions"]["fields"]["mot_win_limit"] = array("type" => "int", "size" => 11, "null" => false, "default" => "50", "comment" => "The percent needed by a proposition to win");
$schema["tables"]["motions"]["fields"]["mot_title"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["motions"]["fields"]["mot_description"] = array("type" => "text", "null" => true);
$schema["tables"]["motions"]["fields"]["mot_explanation"] = array("type" => "text", "null" => true);
$schema["tables"]["motions"]["fields"]["mot_trashed"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["motions"]["fields"]["mot_trash_explanation"] = array("type" => "text", "null" => true);
$schema["tables"]["motions"]["indexes"]["mot_author_id"] = array("mot_author_id");
$schema["tables"]["motions"]["indexes"]["mot_deleted"] = array("mot_deleted");
$schema["tables"]["motions"]["indexes"]["mot_agenda_id"] = array("mot_agenda_id");
$schema["tables"]["motions"]["indexes"]["mot_pinned"] = array("mot_pinned");
$schema["tables"]["motions"]["indexes"]["mot_trashed"] = array("mot_trashed");
$schema["tables"]["motions"]["indexes"]["mot_tag_ids"] = array("mot_tag_ids");

$schema["tables"]["motion_propositions"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["motion_propositions"]["fields"]["mpr_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["motion_propositions"]["fields"]["mpr_motion_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["motion_propositions"]["fields"]["mpr_label"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["motion_propositions"]["fields"]["mpr_winning"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["motion_propositions"]["fields"]["mpr_neutral"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["motion_propositions"]["fields"]["mpr_explanation"] = array("type" => "text", "null" => true);
$schema["tables"]["motion_propositions"]["indexes"]["mpr_motion_id"] = array("age_meeting_id");

$schema["tables"]["notices"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["notices"]["fields"]["not_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["notices"]["fields"]["not_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["notices"]["fields"]["not_noticed"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0", "comment" => 'Boolean marking if a group has been noticed');
$schema["tables"]["notices"]["fields"]["not_target_type"] = array("type" => "enum", "size" => "'galette_adherents','galette_groups','dlp_themes','dlp_groups','con_external','all_members','cus_users'", "null" => true);
$schema["tables"]["notices"]["fields"]["not_target_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["notices"]["fields"]["not_external_mails"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["notices"]["fields"]["not_voting"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["notices"]["indexes"]["not_meeting_id"] = array("not_meeting_id");
$schema["tables"]["notices"]["indexes"]["not_noticed"] = array("not_noticed");
$schema["tables"]["notices"]["indexes"]["not_voting"] = array("not_voting");

$schema["tables"]["pings"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["pings"]["fields"]["pin_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["pings"]["fields"]["pin_meeting_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["pings"]["fields"]["pin_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["pings"]["fields"]["pin_first_presence_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["pings"]["fields"]["pin_noticed"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["pings"]["fields"]["pin_member_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["pings"]["fields"]["pin_guest_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["pings"]["fields"]["pin_nickname"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["pings"]["fields"]["pin_speaking"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["pings"]["fields"]["pin_speaking_request"] = array("type" => "int", "size" => 11, "null" => false, "default" => "0");
$schema["tables"]["pings"]["fields"]["pin_speaking_start"] = array("type" => "datetime", "null" => true);
$schema["tables"]["pings"]["fields"]["pin_speaking_time"] = array("type" => "bigint", "size" => 20, "null" => false, "default" => "0");
$schema["tables"]["pings"]["indexes"]["pin_meeting_id"] = array("pin_meeting_id");
$schema["tables"]["pings"]["indexes"]["pin_member_id"] = array("pin_member_id");
$schema["tables"]["pings"]["indexes"]["pin_noticed"] = array("pin_noticed");
$schema["tables"]["pings"]["indexes"]["pin_first_presence_datetime"] = array("pin_first_presence_datetime");

$schema["tables"]["sources"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["sources"]["fields"]["sou_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["sources"]["fields"]["sou_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["sources"]["fields"]["sou_motion_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["sources"]["fields"]["sou_is_default_source"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["sources"]["fields"]["sou_type"] = array("type" => "enum", "size" => "'leg_text','leg_article','wiki_text','congressus_motion','forum','pdf','free'", "null" => false, "default" => "free");
$schema["tables"]["sources"]["fields"]["sou_url"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["sources"]["fields"]["sou_title"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["sources"]["fields"]["sou_articles"] = array("type" => "varchar", "size" => 2048, "null" => false, "default" => "[]");
$schema["tables"]["sources"]["fields"]["sou_content"] = array("type" => "longtext", "null" => true);
$schema["tables"]["sources"]["indexes"]["sou_deleted"] = array("sou_deleted");
$schema["tables"]["sources"]["indexes"]["sou_motion_id"] = array("sou_motion_id");
$schema["tables"]["sources"]["indexes"]["sou_is_default_source"] = array("sou_is_default_source");
$schema["tables"]["sources"]["indexes"]["sou_title"] = array("sou_title");

$schema["tables"]["tasks"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["tasks"]["fields"]["tas_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["tasks"]["fields"]["tas_agenda_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["tasks"]["fields"]["tas_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["tasks"]["fields"]["tas_label"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["tasks"]["fields"]["tas_status"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["tasks"]["fields"]["tas_target_type"] = array("type" => "enum", "size" => "'galette_adherents','galette_groups','dlp_themes','dlp_groups','con_external','all_members','cus_users'", "null" => false);
$schema["tables"]["tasks"]["fields"]["tas_target_id"] = array("type" => "bigint", "size" => 20, "null" => false);
$schema["tables"]["tasks"]["fields"]["tas_start_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["tasks"]["fields"]["tas_finish_datetime"] = array("type" => "datetime", "null" => true);
$schema["tables"]["tasks"]["indexes"]["tas_agenda_id"] = array("tas_agenda_id");
$schema["tables"]["tasks"]["indexes"]["tas_deleted"] = array("tas_deleted");
$schema["tables"]["tasks"]["indexes"]["tas_target_type"] = array("tas_target_type");
$schema["tables"]["tasks"]["indexes"]["tas_target_id"] = array("tas_target_id");

$schema["tables"]["user_properties"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["user_properties"]["fields"]["upr_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["user_properties"]["fields"]["upr_user_id"] = array("type" => "bigint", "size" => 20, "null" => false);
$schema["tables"]["user_properties"]["fields"]["upr_property"] = array("type" => "varchar", "size" => 255, "null" => false);
$schema["tables"]["user_properties"]["fields"]["upr_value"] = array("type" => "varchar", "size" => 255, "null" => false);
$schema["tables"]["user_properties"]["uniques"]["upr_user_id_property_unique"] = array("upr_user_id", "upr_property");

$schema["tables"]["votes"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["votes"]["fields"]["vot_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["votes"]["fields"]["vot_member_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["votes"]["fields"]["vot_motion_proposition_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["votes"]["fields"]["vot_power"] = array("type" => "int", "size" => 11, "null" => true);
$schema["tables"]["votes"]["indexes"]["vot_member_id"] = array("vot_member_id");
$schema["tables"]["votes"]["indexes"]["vot_motion_proposition_id"] = array("vot_motion_proposition_id");

$schema["tables"]["customizers"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["customizers"]["fields"]["cus_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["customizers"]["fields"]["cus_server_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["customizers"]["fields"]["cus_label"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["customizers"]["fields"]["cus_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["customizers"]["indexes"]["cus_server_id"] = array("cus_server_id");
$schema["tables"]["customizers"]["indexes"]["cus_deleted"] = array("cus_deleted");

$schema["tables"]["customizer_properties"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["customizer_properties"]["fields"]["cpr_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["customizer_properties"]["fields"]["cpr_customizer_id"] = array("type" => "bigint", "size" => 20, "null" => true);
$schema["tables"]["customizer_properties"]["fields"]["cpr_key"] = array("type" => "varchar", "size" => 2048, "null" => true);
$schema["tables"]["customizer_properties"]["fields"]["cpr_value"] = array("type" => "text", "null" => true);
$schema["tables"]["customizer_properties"]["indexes"]["cpr_customizer_id"] = array("cpr_customizer_id");
$schema["tables"]["customizer_properties"]["indexes"]["cpr_key"] = array("cpr_key");

$schema["tables"]["tags"] = array("fields" => array(), "indexes" => array());
$schema["tables"]["tags"]["fields"]["tag_id"] = array("type" => "bigint", "size" => 20, "null" => false, "primary" => true, "autoincrement" => 1);
$schema["tables"]["tags"]["fields"]["tag_server_id"] = array("type" => "bigint", "size" => 20, "null" => false, "default" => "0");
$schema["tables"]["tags"]["fields"]["tag_deleted"] = array("type" => "tinyint", "size" => 4, "null" => false, "default" => "0");
$schema["tables"]["tags"]["fields"]["tag_label"] = array("type" => "varchar", "size" => 255, "null" => true);
$schema["tables"]["tags"]["indexes"]["tag_server_id"] = array("tag_server_id");
$schema["tables"]["tags"]["indexes"]["tag_deleted"] = array("tag_deleted");

?>