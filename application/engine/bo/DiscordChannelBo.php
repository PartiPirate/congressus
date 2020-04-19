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
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class DiscordChannelBo {
	var $pdo = null;
	var $config = null;

	var $TABLE = "discord_channels";
	var $ID_FIELD = "dch_id";

	function __construct($pdo, $config) {
		$this->config = $config;
		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $config = null) {
		return new DiscordChannelBo($pdo, $config);
	}

	function create(&$discordChannel) {
		return BoHelper::create($discordChannel, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function update($discordChannel) {
		return BoHelper::update($discordChannel, $this->TABLE, $this->ID_FIELD, $this->config, $this->pdo);
	}

	function save(&$discordChannel) {
 		if (!isset($discordChannel[$this->ID_FIELD]) || !$discordChannel[$this->ID_FIELD]) {
			$this->create($discordChannel);
		}

		$this->update($discordChannel);
	}

	function delete($discordChannel) {
		$query = "	DELETE FROM $this->TABLE ";

		$query .= "	WHERE $this->ID_FIELD = :$this->ID_FIELD ";

		//		echo showQuery($query, $discordChannel);

		$args = array($this->ID_FIELD => $discordChannel[$this->ID_FIELD]);

		$statement = $this->pdo->prepare($query);
		$statement->execute($args);
	}

	function getById($id) {
		$filters = array($this->ID_FIELD => intval($id));

		$results = $this->getByFilters($filters);

		if (count($results)) {
			return $results[0];
		}

		return null;
	}

	function getByFilters($filters = null) {
		if (!$filters) $filters = array();
		$args = array();

		$queryBuilder = QueryFactory::getInstance($this->config["database"]["dialect"]);

		$queryBuilder->select($this->TABLE);
		$queryBuilder->addSelect("*");

		if (isset($filters[$this->ID_FIELD])) {
			$args[$this->ID_FIELD] = $filters[$this->ID_FIELD];
			$queryBuilder->where("$this->ID_FIELD = :$this->ID_FIELD");
		}

		if (isset($filters["dch_server_id"])) {
			$args["dch_server_id"] = $filters["dch_server_id"];
			$queryBuilder->where("dch_server_id = :dch_server_id");
		}

		if (isset($filters["dch_channel_id"])) {
			$args["dch_channel_id"] = $filters["dch_channel_id"];
			$queryBuilder->where("dch_channel_id = :dch_channel_id");
		}

		if (!isset($filters["with_deleted"])) {
			$queryBuilder->where("dch_deleted = 0");
		}

		if (!isset($filters["with_invisible"])) {
			$queryBuilder->where("dch_visible = 1");
		}

//		$queryBuilder->orderBy("dch_type")->orderBy("dch_name");
		$queryBuilder->orderBy("dch_type");

		$query = $queryBuilder->constructRequest();
		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$results = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $index => $line) {
				foreach($line as $field => $value) {
					if (is_numeric($field)) {
						unset($results[$index][$field]);
					}
				}
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $results;
	}
}