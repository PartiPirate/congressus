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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once "engine/utils/TaskHook.php";
require_once "task_hooks/redmine/autoload.php";

class RedmineHook implements TaskHook {

    public $configuration = null;

    function __construct() {
//        $json = file_get_contents("task_hooks/redmine.json");
        // This if statement only exists to avoid breaking existing deployments
        if(file_exists("config/redmine.config.php"))
          include("config/redmine.config.php");
        else
          include("task_hooks/redmine/config.php");
//        $json = file_get_contents("task_hooks/redmine.json");
        $this->configuration = json_decode($json, true);
    }

    function getRedmineClient() {
        $client = new Redmine\Client($this->configuration["url"], $this->configuration["token"]);
        
        return $client;
    }

    /**
     * @returns the type of the plugin
     */
    function getType() {
        return "redmine";
    }

    /**
     * @returns a json object : {type, id}
     */
    function createTask($task, $projectId) {
        $redmineTask = array();

        $redmineTask["project_id"] = $projectId;
        $redmineTask["subject"] = $task["subject"];
        $redmineTask["description"] = $task["description"];
        $redmineTask["tracker_id"] = 3;
        $redmineTask["watcher_user_ids"] = []; // upgrade

        $redmineIssue = $this->getRedmineClient()->issue->create($redmineTask);

        $id = $redmineIssue->{"id"};

        return array("type" => $this->getType(), "id" => "$id");
    }

    function updateTask($task) {
        $redmineTask = array();

        foreach($task as $key => $value) {
            if ($key == "id") continue;
            if ($key == "type") continue;

            $redmineTask[$key] = $value;
        }
        
        $redmineIssue = $this->getRedmineClient()->issue->update($task["id"], $redmineTask);
    }


    /**
     */
    function setTaskDone($task) {
        $this->getRedmineClient()->issue->setIssueStatus($task["id"], 'Terminé');
    }

    /**
     */
    function setTaskCanceled($task) {
        $this->getRedmineClient()->issue->setIssueStatus($task["id"], 'Annulé');
    }

    /**
     */
    function getTaskInformation($task) {
        $redmineIssue = $this->getRedmineClient()->issue->show($task["id"]);

        $redmineIssue = $redmineIssue["issue"];
        $information = array(   "url" => $this->configuration["base_url"] . "issues/" . $task["id"], 
                                "subject" => $redmineIssue["subject"], 
                                "projectId" => $redmineIssue["project"]["id"], 
                                "project" => $redmineIssue["project"]["name"], 
                                "projectUrl" => $this->configuration["base_url"] . "projects/" . $redmineIssue["project"]["id"], 
                                "status" => $redmineIssue["status"]["name"], 
                                "progress" => $redmineIssue["done_ratio"], 
                                "assigned" => (isset($redmineIssue["assigned_to"]["name"]) ? $redmineIssue["assigned_to"]["name"] : "Personne"));

        return $information;
    }

    static function projectOnNameSorter($projectA, $projectB) {
        return strcmp($projectA["name"], $projectB["name"]);
    }

    function getProjects() {
        $redmineProjects = $this->getRedmineClient()->project->all(array('limit' => 1000, 'sort' => 'name'));
        $redmineProjects = $redmineProjects["projects"];
        // order them according to the "name"

        usort($redmineProjects, array('RedmineHook', 'projectOnNameSorter'));

        return $redmineProjects;
    }

}

global $taskHooks;

if (!$taskHooks) $taskHooks = array();

$taskHooks["rh"] = new RedmineHook();

?>