<?php /*
    Copyright 2015 Cédric Levieux, Parti Pirate

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

class GroupBo {
	var $pdo = null;
	var $galetteDatabase = "";
	var $personaeDatabase = "";

	function __construct($pdo, $galetteDatabase) {
		if (is_string($galetteDatabase)) {
			$this->galetteDatabase = $galetteDatabase . ".";
		}
		else if (is_array($galetteDatabase)) {
			$this->galetteDatabase = $galetteDatabase["galette"]["db"] . ".";
			$this->personaeDatabase = $galetteDatabase["personae"]["db"] . ".";
		}

		$this->pdo = $pdo;
	}

	static function newInstance($pdo, $database) {
		return new GroupBo($pdo, $database);
	}

	function create(&$group) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_groups () VALUES ()	";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		try {
			$statement->execute(array("gro_id" => $group["gro_id"]));
			$group["gro_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($group) {
		$query = "	UPDATE ".$this->personaeDatabase."dlp_groups SET ";

		$separator = "";
		foreach($group as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE gro_id = :gro_id ";

//		echo showQuery($query, $group);

		$statement = $this->pdo->prepare($query);
		$statement->execute($group);
	}

	function save(&$group) {
		$isCreated = false;
		
 		if (!isset($group["gro_id"]) || !$group["gro_id"]) {
			$this->create($group);
			$isCreated = true;
 		}

		$this->update($group);
		
		return $isCreated;
	}

	function getMyGroups($filters) {
		$args = array();

		$state = $filters["state"];

		$filterField = "dlp_themes.the_voting_group_";
		if ($state != "voting") {
			$filterField = "dlp_themes.the_eligible_group_";
		}

		$query = "	SELECT ".$this->personaeDatabase."dlp_groups.*, ".$this->personaeDatabase."dlp_themes.*,
						_ggc.id_type_cotis AS gga_id_type_cotis, gga.id_adh AS gga_id_adh, gga.email_adh AS gga_email_adh, gga.pseudo_adh AS gga_pseudo_adh, gga.nom_adh AS gga_nom_adh, gga.prenom_adh AS gga_prenom_adh, ggc.can_status AS ggc_can_status, ggc.can_text AS ggc_can_text,
						_dgc.id_type_cotis AS dga_id_type_cotis, dga.id_adh AS dga_id_adh, dga.email_adh AS dga_email_adh, dga.pseudo_adh AS dga_pseudo_adh, dga.nom_adh AS dga_nom_adh, dga.prenom_adh AS dga_prenom_adh, dgc.can_status AS dgc_can_status, dgc.can_text AS dgc_can_text,
						_gc.id_type_cotis AS ga_id_type_cotis, ga.id_adh AS ga_id_adh, ga.email_adh AS ga_email_adh, ga.pseudo_adh AS ga_pseudo_adh, ga.nom_adh AS ga_nom_adh, ga.prenom_adh AS ga_prenom_adh, gc.can_status AS gc_can_status, gc.can_text AS gc_can_text,
						_tgc.id_type_cotis AS tga_id_type_cotis, tga.id_adh AS tga_id_adh, tga.email_adh AS tga_email_adh, tga.pseudo_adh AS tga_pseudo_adh, tga.nom_adh AS tga_nom_adh, tga.prenom_adh AS tga_prenom_adh, tgc.can_status AS tgc_can_status, tgc.can_text AS tgc_can_text
					FROM  ".$this->personaeDatabase."dlp_groups
					RIGHT JOIN ".$this->personaeDatabase."dlp_group_themes ON gth_group_id = gro_id
					RIGHT JOIN ".$this->personaeDatabase."dlp_themes ON gth_theme_id = the_id AND gth_theme_type = 'dlp_themes'

					-- The source of voting is maybe a galette group
					LEFT JOIN ".$this->galetteDatabase."galette_groups ggg ON ggg.id_group = ".$filterField."id AND ".$filterField."type = 'galette_groups'
					LEFT JOIN ".$this->galetteDatabase."galette_groups_members ggm ON ggg.id_group = ggm.id_group
					LEFT JOIN ".$this->galetteDatabase."galette_adherents gga ON gga.id_adh = ggm.id_adh

					-- The source of voting is maybe a galette adh
					LEFT JOIN ".$this->galetteDatabase."galette_adherents ga ON ".$filterField."type = 'galette_adherents'

					-- The source of voting is maybe a congressus group
					LEFT JOIN ".$this->personaeDatabase."dlp_groups dg ON dg.gro_id = ".$filterField."id AND ".$filterField."type = 'dlp_groups'
					LEFT JOIN ".$this->personaeDatabase."dlp_group_themes dtg ON dg.gro_id = dtg.gth_group_id
					LEFT JOIN ".$this->personaeDatabase."dlp_themes dt ON dt.the_id = dtg.gth_theme_id
					LEFT JOIN ".$this->personaeDatabase."dlp_fixations df ON df.fix_id = dt.the_current_fixation_id
					LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members dfm ON dfm.fme_fixation_id = df.fix_id
					LEFT JOIN ".$this->galetteDatabase."galette_adherents dga ON dga.id_adh = dfm.fme_member_id

					-- The source of voting is maybe a personae theme
					LEFT JOIN ".$this->personaeDatabase."dlp_themes tt ON tt.the_id = ".$filterField."id AND ".$filterField."type = 'dlp_themes'
					LEFT JOIN ".$this->personaeDatabase."dlp_fixations tf ON tf.fix_id = tt.the_current_fixation_id
					LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members tfm ON tfm.fme_fixation_id = tf.fix_id
					LEFT JOIN ".$this->galetteDatabase."galette_adherents tga ON tga.id_adh = tfm.fme_member_id

					-- The candidate status
					LEFT JOIN ".$this->personaeDatabase."dlp_candidates ggc ON ggc.can_member_id = gga.id_adh AND ".$this->personaeDatabase."dlp_themes.the_id = ggc.can_theme_id
					LEFT JOIN ".$this->personaeDatabase."dlp_candidates gc ON gc.can_member_id = ga.id_adh AND ".$this->personaeDatabase."dlp_themes.the_id = gc.can_theme_id
					LEFT JOIN ".$this->personaeDatabase."dlp_candidates dgc ON dgc.can_member_id = dga.id_adh AND ".$this->personaeDatabase."dlp_themes.the_id = dgc.can_theme_id
					LEFT JOIN ".$this->personaeDatabase."dlp_candidates tgc ON tgc.can_member_id = tga.id_adh AND ".$this->personaeDatabase."dlp_themes.the_id = tgc.can_theme_id ";

		if (true) {

			$query .= "
					LEFT JOIN ".$this->galetteDatabase."galette_cotisations _gc ON ga.id_adh = _gc.id_adh AND _gc.info_cotis = '' AND _gc.date_fin_cotis > NOW()
					LEFT JOIN ".$this->galetteDatabase."galette_types_cotisation gtc ON _gc.id_type_cotis = gtc.id_type_cotis AND gtc.cotis_extension = 1

					LEFT JOIN ".$this->galetteDatabase."galette_cotisations _dgc ON dga.id_adh = _dgc.id_adh AND _dgc.info_cotis = '' AND _dgc.date_fin_cotis > NOW()
					LEFT JOIN ".$this->galetteDatabase."galette_types_cotisation dgtc ON _dgc.id_type_cotis = dgtc.id_type_cotis AND dgtc.cotis_extension = 1

					LEFT JOIN ".$this->galetteDatabase."galette_cotisations _ggc ON gga.id_adh = _ggc.id_adh AND _ggc.info_cotis = '' AND _ggc.date_fin_cotis > NOW()
					LEFT JOIN ".$this->galetteDatabase."galette_types_cotisation ggtc ON _ggc.id_type_cotis = ggtc.id_type_cotis AND ggtc.cotis_extension = 1 

					LEFT JOIN ".$this->galetteDatabase."galette_cotisations _tgc ON tga.id_adh = _tgc.id_adh AND _tgc.info_cotis = '' AND _tgc.date_fin_cotis > NOW()
					LEFT JOIN ".$this->galetteDatabase."galette_types_cotisation tgtc ON _tgc.id_type_cotis = tgtc.id_type_cotis AND tgtc.cotis_extension = 1 ";
		}

		$query .= " WHERE 1 = 1 AND (dt.the_deleted = 0 OR dt.the_deleted IS NULL) ";

		if (isset($filters["the_id"])) {
			$args["the_id"] = $filters["the_id"];
			$query .= "	AND ".$this->personaeDatabase."dlp_themes.the_id = :the_id ";
		}

		if (!isset($filters["with_deleted"])) {
			$query .= " AND ".$this->personaeDatabase."dlp_themes.the_deleted = 0 \n";
		}

		if (isset($filters["userId"])) {
			$userId = $filters["userId"];
			$query .= "	-- TEST membering
						AND (gga.id_adh = :id_adh OR ga.id_adh = :id_adh OR dga.id_adh = :id_adh)";
			$args["id_adh"] = $userId;
		}

		$query .= "	ORDER BY ".$this->personaeDatabase."dlp_groups.gro_label, ".$this->personaeDatabase."dlp_themes.the_label ";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$groups = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $line) {
				$groupId = 0;
				if ($line["gro_id"]) {
					$groupId = $line["gro_id"];
				}

				$groups[$groupId]["gro_id"] = $line["gro_id"];
				$groups[$groupId]["gro_label"] = $line["gro_label"];
				if (!isset($groups[$groupId]["gro_themes"])) {
					$groups[$groupId]["gro_themes"] = array();
				}

				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_id"] = $line["the_id"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_label"] = $line["the_label"];

				$memberId = $line["gga_id_adh"] ? $line["gga_id_adh"] : ($line["dga_id_adh"] ? $line["dga_id_adh"] : $line["ga_id_adh"]);
				$memberMail = $line["gga_email_adh"] ? $line["gga_email_adh"] : ($line["dga_email_adh"] ? $line["dga_email_adh"] : $line["ga_email_adh"]);
				$memberPseudo = $line["gga_pseudo_adh"] ? $line["gga_pseudo_adh"] : ($line["dga_pseudo_adh"] ? $line["dga_pseudo_adh"] : $line["ga_pseudo_adh"]);
				$memberNom = $line["gga_nom_adh"] ? $line["gga_nom_adh"] : ($line["dga_nom_adh"] ? $line["dga_nom_adh"] : $line["ga_nom_adh"]);
				$memberPrenom = $line["gga_prenom_adh"] ? $line["gga_prenom_adh"] : ($line["dga_prenom_adh"] ? $line["dga_prenom_adh"] : $line["ga_prenom_adh"]);

				$candidateStatus = $line["ggc_can_status"] ? $line["ggc_can_status"] : ($line["dgc_can_status"] ? $line["dgc_can_status"] : ($line["gc_can_status"] ? $line["gc_can_status"] : "neutral"));
				$candidateText = $line["ggc_can_text"] ? $line["ggc_can_text"] : ($line["dgc_can_text"] ? $line["dgc_can_text"] : ($line["gc_can_text"] ? $line["gc_can_text"] : ""));
				$isTodayAdherent = $line["gga_id_type_cotis"] ? $line["gga_id_type_cotis"] : ($line["dga_id_type_cotis"] ? $line["dga_id_type_cotis"] : ($line["tga_id_type_cotis"] ? $line["tga_id_type_cotis"] : ($line["ga_id_type_cotis"] ? $line["ga_id_type_cotis"] : "")));

				if (!$memberId) continue;

				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["id_adh"] = $memberId;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["email_adh"] = $memberMail;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["pseudo_adh"] = $memberPseudo;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["nom_adh"] = $memberNom;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["prenom_adh"] = $memberPrenom;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["can_status"] = $candidateStatus;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["can_text"] = $candidateText;
				$groups[$groupId]["gro_themes"][$line["the_id"]]["members"][$memberId]["id_type_cotis"] = $isTodayAdherent;
			}

			return $groups;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return array();
	}

	function getGroup($groupId, $withDeleted = false) {
		$filters = array("gro_id" => $groupId);
		if ($withDeleted) {
			$filters["with_deleted"] = true;
		}
		$groups = $this->getGroups($filters);

		foreach($groups as $groupId => $group) return $group;

		return null;
	}

	function getGroups($filters = null) {
		$args = array();

		$query = "	SELECT *
					FROM  ".$this->personaeDatabase."dlp_groups
					RIGHT JOIN ".$this->personaeDatabase."dlp_group_themes ON gth_group_id = gro_id
					RIGHT JOIN ".$this->personaeDatabase."dlp_themes ON gth_theme_id = the_id AND gth_theme_type = 'dlp_themes'
					LEFT JOIN ".$this->personaeDatabase."dlp_fixations ON fix_id = the_current_fixation_id
					LEFT JOIN ".$this->personaeDatabase."dlp_fixation_members ON fix_id = fme_fixation_id
					LEFT JOIN ".$this->galetteDatabase."galette_adherents ON fme_member_id = id_adh
					WHERE
						1 = 1 \n";

 		if (!isset($filters["with_deleted"])) {
 			$query .= " AND the_deleted = 0 \n";
 			$query .= " AND (gro_deleted = 0 OR gro_id IS NULL) \n";
 		}

// 		if (isset($filters["sig_like_logo"])) {
// 			$args["sig_like_logo"] = "%" . $filters["sig_like_logo"] . "%";
// 			$query .= " AND sig_logo LIKE :sig_like_logo \n";
// 		}

		if (isset($filters["gro_id"])) {
			$args["gro_id"] = $filters["gro_id"];
			$query .= " AND gro_id = :gro_id \n";
		}

		if (isset($filters["the_id"])) {
			$args["the_id"] = $filters["the_id"];
			$query .= " AND the_id = :the_id \n";
		}

		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		$groups = array();

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $line) {
				$groupId = 0;
				if ($line["gro_id"]) {
					$groupId = $line["gro_id"];
				}

				$groups[$groupId]["gro_id"] 				= $line["gro_id"];
				$groups[$groupId]["gro_label"]				= $line["gro_label"];
				$groups[$groupId]["gro_contact"]			= $line["gro_contact"];
				$groups[$groupId]["gro_contact_type"]		= $line["gro_contact_type"];
				$groups[$groupId]["gro_tasker_type"]		= $line["gro_tasker_type"];
				$groups[$groupId]["gro_tasker_project_id"]	= $line["gro_tasker_project_id"];

				if (!isset($groups[$groupId]["gro_themes"])) {
					$groups[$groupId]["gro_themes"] = array();
				}

				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_id"]					= $line["the_id"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_label"]				= $line["the_label"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_voting_power"]		= $line["the_voting_power"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_voting_method"]		= $line["the_voting_method"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_delegate_only"]		= $line["the_delegate_only"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_tasker_type"] 		= $line["the_tasker_type"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["the_tasker_project_id"]	= $line["the_tasker_project_id"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["gth_power"]				= $line["gth_power"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["fix_until_date"] = $line["fix_until_date"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["id_adh"]		= $line["fme_member_id"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["pseudo_adh"]	= $line["pseudo_adh"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["nom_adh"]		= $line["nom_adh"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["prenom_adh"]	= $line["prenom_adh"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["email_adh"]	= $line["email_adh"];
				$groups[$groupId]["gro_themes"][$line["the_id"]]["fixation"]["members"][$line["fme_member_id"]]["fme_power"]	= $line["fme_power"];
			}

			return $groups;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return array();
	}

	function addTheme($group, $theme, $power = 1) {
		$args = array("gth_group_id" => $group["gro_id"], "gth_power" => $power);

		if (isset($theme["the_id"])) {
			$args["gth_theme_type"] = "dlp_themes";
			$args["gth_theme_id"] = $theme["the_id"];
		}

		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_group_themes
						(gth_group_id, gth_theme_id, gth_theme_type, gth_power)
					VALUES
						(:gth_group_id, :gth_theme_id, :gth_theme_type, :gth_power) \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($args);
	}

	function updateTheme($group, $theme, $power = 1) {
		$args = array("gth_group_id" => $group["gro_id"], "gth_power" => $power);

		$query = "	UPDATE ".$this->personaeDatabase."dlp_group_themes
		SET gth_power = :gth_power
		WHERE gth_group_id = :gth_group_id \n";

		if (isset($theme["the_id"])) {
			$args["gth_theme_type"] = "dlp_themes";
			$args["gth_theme_id"] = $theme["the_id"];

			$query .= "	AND gth_theme_id = :gth_theme_id AND gth_theme_type = :gth_theme_type \n";
		}

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($args);
	}

	function excludeTheme($group, $theme) {
		$args = array("gth_group_id" => $group["gro_id"]);

		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_group_themes
					WHERE gth_group_id = :gth_group_id \n";

		if (isset($theme["the_id"])) {
			$args["gth_theme_type"] = "dlp_themes";
			$args["gth_theme_id"] = $theme["the_id"];

			$query .= "	AND gth_theme_id = :gth_theme_id AND gth_theme_type = :gth_theme_type \n";
		}

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($args);
	}

	// Admin part

	function addMemberAdmin($admin) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_group_admins
						(gad_group_id, gad_member_id)
					VALUES
						(:gad_group_id, :gad_member_id) \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($admin);
	}

	function removeMemberAdmin($admin) {
		$query = "	DELETE FROM  ".$this->personaeDatabase."dlp_group_admins
					WHERE
						gad_group_id = :gad_group_id
					AND gad_member_id = :gad_member_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($admin);
	}

	function isMemberAdmin($group, $memberId) {
		$args = array();
		$args["gad_group_id"] = $group["gro_id"];
		$args["gad_member_id"] = $memberId;

		$query = "	SELECT *
					FROM  ".$this->personaeDatabase."dlp_group_admins
					WHERE
						gad_group_id = :gad_group_id
					AND gad_member_id = :gad_member_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//echo showQuery($query, $args);

		$statement->execute($args);
		$results = $statement->fetchAll();

		return count($results) > 0;
	}

	function getMemberAdmins($group) {
		$args = array();
		$args["gad_group_id"] = $group["gro_id"];

		$query = "	SELECT *
					FROM ".$this->personaeDatabase."dlp_group_admins
					JOIN ".$this->galetteDatabase."galette_adherents ON id_adh = gad_member_id
					WHERE
						gad_group_id = :gad_group_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($args);
		$results = $statement->fetchAll();

		return $results;
	}

		// Authority part
	
	// Admin part

	function addAuthorityAdmin($admin) {
		$query = "	INSERT INTO ".$this->personaeDatabase."dlp_group_authoritatives
						(gau_group_id, gau_authoritative_id)
					VALUES
						(:gau_group_id, :gau_authoritative_id) \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($admin);
	}

	function removeAuthorityAdmin($admin) {
		$query = "	DELETE FROM ".$this->personaeDatabase."dlp_group_authoritatives
					WHERE
						gau_group_id = :gau_group_id
					AND gau_authoritative_id = :gau_authoritative_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($admin);
	}
	
	function getAuthorityAdmins($group) {
		$args = array();
		$args["gau_group_id"] = $group["gro_id"];

		$query = "	SELECT *, group_name as gau_authoritative_name
					FROM ".$this->personaeDatabase."dlp_group_authoritatives
					LEFT JOIN ".$this->galetteDatabase."galette_groups ON id_group = gau_authoritative_id
					WHERE
						gau_group_id = :gau_group_id \n";

		//		$query .= "	ORDER BY gro_label, the_label ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		$statement->execute($args);
		$results = $statement->fetchAll();

		return $results;
	}

}