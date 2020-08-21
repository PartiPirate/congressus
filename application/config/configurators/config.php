<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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

if(!isset($configurators)) {
	$configurators = array();
}

function computeSalt() {
	$chars = array();
	for($index = 0; $index < 26; $index++) {
		if ($index < 10) {
			$chars[] = $index;
		}
		$chars[] = chr(65 + $index);
		$chars[] = chr(97 + $index);
	}

	$nbChars = count($chars);

	$salt = "";
	for($index = 0; $index < 32; $index++) {
		$salt .= $chars[rand(0, $nbChars - 1)];
	}

	return $salt;	
}

if (!@$config["salt"]) {
    $config["salt"] = computeSalt();
}

$configurator = array("file" => "config.php", "panels" => array());

$panel = array("label" => "administration_account", "id" => "account", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_account_login", "type" => "text", "id" => "administrator_login_input", "path" => "administrator/login", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_account_password", "type" => "text", "id" => "administrator_password_input", "path" => "administrator/password", "position" => "");
$panel["fields"][] = $field;

$configurator["panels"][] = $panel;

// Panel database

$panel = array("label" => "administration_database", "id" => "database", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_database_host", "type" => "text", "id" => "database_host_input", "path" => "database/host", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_database_port", "type" => "number", "id" => "database_port_input", "path" => "database/port", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_database_database", "type" => "text", "id" => "database_database_input", "path" => "database/database", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_database_dialect", "type" => "select", "id" => "database_dialect_input", "path" => "database/dialect", "position" => "", "values" => array());
$field["values"][] = array("value" => "mysql",    "label" => "MySQL");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_database_login", "type" => "text", "id" => "database_login_input", "path" => "database/login", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_database_password", "type" => "text", "id" => "database_password_input", "path" => "database/password", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_database_galette", "type" => "text", "id" => "galette_db_input", "path" => "galette/db", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_database_personae", "type" => "text", "id" => "personae_db_input", "path" => "personae/db", "position" => "");
$panel["fields"][] = $field;

$button = array("id" => "btn-ping-database", "class" => "", "label" => "administration_ping_database");
$panel["buttons"][] = $button;
$button = array("id" => "btn-create-database", "class" => "", "label" => "administration_create_database");
$panel["buttons"][] = $button;
$button = array("id" => "btn-test-database", "class" => "", "label" => "administration_test_database");
$panel["buttons"][] = $button;
$button = array("id" => "btn-deploy-database", "class" => "btn-deploy-database", "label" => "administration_deploy_database");
$panel["buttons"][] = $button;

$configurator["panels"][] = $panel;

// Panel server

$panel = array("label" => "administration_server", "id" => "server", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_server_base", "type" => "text", "id" => "server_base_input", "path" => "server/base", "position" => "");
$panel["fields"][] = $field;

$field = array("label" => "administration_server_salt", "type" => "text", "id" => "salt_input", "path" => "salt", "position" => "");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_server_line", "type" => "select", "id" => "server_line_input", "path" => "server/line", "position" => "", "values" => array());
$field["values"][] = array("value" => "dev",    "label" => "administration_server_line_dev");
$field["values"][] = array("value" => "beta",   "label" => "administration_server_line_beta");
$field["values"][] = array("value" => "",       "label" => "administration_server_line_prod");
$panel["fields"][] = $field;

$field = array("label" => "administration_server_timezone", "type" => "select", "id" => "server_timezone_input", "path" => "server/timezone", "position" => "", "values" => array());
$field["values"][] = array("value" => "",               "label" => "administration_server_timezone_none");
$field["values"][] = array("value" => "Europe/Paris",   "label" => "administration_server_timezone_europeparis");
$panel["fields"][] = $field;

$panel["fields"][] = array("type" => "separator");

$field = array("label" => "administration_congressus_ballot_majorities", "width" => 10, "type" => "checkboxes", "id" => "congressus_ballot_majorities_input[]", "path" => "congressus/ballot_majorities", "position" => "", "values" => array());
$field["values"][] = array("value" => "-4",    "label" => "motion_ballot_majority_-4");
$field["values"][] = array("value" => "-3",    "label" => "motion_ballot_majority_-3");
$field["values"][] = array("value" => "-2",    "label" => "motion_ballot_majority_-2");
$field["values"][] = array("value" => "-1",    "label" => "motion_ballot_majority_-1");
$field["values"][] = array("value" =>  "0",    "label" => "motion_ballot_majority_0");
$field["values"][] = array("value" => "50",    "label" => "motion_ballot_majority_50");
$field["values"][] = array("value" => "66",    "label" => "motion_ballot_majority_66");
$field["values"][] = array("value" => "80",    "label" => "motion_ballot_majority_80");
$panel["fields"][] = $field;

$field = array("label" => "administration_congressus_ballot_majority_judgment", "width" => 10, "type" => "checkboxes", "id" => "congressus_ballot_majority_judgment_input[]", "path" => "congressus/ballot_majority_judgment", "position" => "", "values" => array());
$field["values"][] = array("value" => "1",    "label" => "motion_majorityJudgment_1");
$field["values"][] = array("value" => "2",    "label" => "motion_majorityJudgment_2");
$field["values"][] = array("value" => "3",    "label" => "motion_majorityJudgment_3");
$field["values"][] = array("value" => "4",    "label" => "motion_majorityJudgment_4");
$field["values"][] = array("value" => "5",    "label" => "motion_majorityJudgment_5");
$field["values"][] = array("value" => "6",    "label" => "motion_majorityJudgment_6");
$panel["fields"][] = $field;

$configurator["panels"][] = $panel;

// Panel Memcached

$panel = array("label" => "administration_memcached", "id" => "memcached", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_memcached_host", "type" => "text", "id" => "memcached_host_input", "path" => "memcached/host", "position" => "");
$panel["fields"][] = $field;
$field = array("label" => "administration_memcached_port", "type" => "number", "id" => "memcached_port_input", "path" => "memcached/port", "position" => "");
$panel["fields"][] = $field;

$button = array("id" => "btn-ping-memcached", "class" => "", "label" => "administration_ping_memcached");
$panel["buttons"][] = $button;

$configurator["panels"][] = $panel;

/*
$panel = array("label" => "administration_account", "id" => "account", "fields" => array(), "buttons" => array());

$field = array("label" => "administration_account_login", "type" => "text", "id" => "administrator_login_input", "path" => "administrator/login", "position" => "");
$panel["fields"][] = $field;

$configurator["panels"][] = $panel;
*/

$configurators[] = $configurator;

if (isset($api)) return;
?>

<style>
@media (min-width: 1024px) {
  #check-database-modal .modal-dialog {
      width: 900px;
  }
}

@media (min-width: 1600px) {
  #check-database-modal .modal-dialog {
      width: 1300px;
  }
}
</style>

<div class="modal fade" tabindex="-1" role="dialog" id="check-database-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo lang("common_close"); ?>"><span aria-hidden="true">&times;</span></button>
                
                <h4 class="modal-title"><?php echo lang("administration_test_database_title"); ?>...</h4>
            </div>
            <div class="modal-body">
            
                <form class="form-horizontal">
                    <fieldset id="check-database-fieldset">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Table</th>
                                    <th>Colonne</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="check-database-tbody"></tbody>
                        </table>
                    </fieldset>
                </form>          
            
            </div>
            <div class="modal-footer">
            
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("common_close"); ?></button>
            <button type="button" class="btn btn-primary btn-deploy-database"><?php echo lang("administration_deploy_database"); ?></button>
            
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
