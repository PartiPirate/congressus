--
-- Base de données :  `congressus`
--
CREATE DATABASE IF NOT EXISTS `congressus` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `congressus`;

-- --------------------------------------------------------

--
-- Structure de la table `agendas`
--

CREATE TABLE IF NOT EXISTS `agendas` (
  `age_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `age_meeting_id` bigint(20) NOT NULL,
  `age_parent_id` bigint(20) DEFAULT NULL,
  `age_order` int(11) NOT NULL DEFAULT '1',
  `age_active` tinyint(4) NOT NULL DEFAULT '0',
  `age_expected_duration` int(11) NOT NULL COMMENT 'value in minutes',
  `age_duration` int(11) DEFAULT NULL COMMENT 'value in minutes',
  `age_label` varchar(255) NOT NULL,
  `age_objects` text NOT NULL,
  `age_description` text NOT NULL,
  PRIMARY KEY (`age_id`),
  KEY `age_meeting_id` (`age_meeting_id`),
  KEY `age_parent_id` (`age_parent_id`),
  KEY `age_order` (`age_order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `chats`
--

CREATE TABLE IF NOT EXISTS `chats` (
  `cha_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cha_agenda_id` bigint(20) NOT NULL,
  `cha_member_id` bigint(20) DEFAULT NULL,
  `cha_guest_id` bigint(20) DEFAULT NULL,
  `cha_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `cha_text` varchar(2048) NOT NULL,
  `cha_datetime` datetime NOT NULL,
  PRIMARY KEY (`cha_id`),
  KEY `cha_agenda_id` (`cha_agenda_id`),
  KEY `cha_member_id` (`cha_member_id`),
  KEY `cha_deleted` (`cha_deleted`),
  KEY `cha_ghost_id` (`cha_guest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `conclusions`
--

CREATE TABLE IF NOT EXISTS `conclusions` (
  `con_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `con_agenda_id` bigint(20) NOT NULL,
  `con_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `con_text` varchar(2048) NOT NULL,
  PRIMARY KEY (`con_id`),
  KEY `con_agenda_id` (`con_agenda_id`),
  KEY `con_deleted` (`con_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `guests`
--

CREATE TABLE IF NOT EXISTS `guests` (
  `gue_id` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`gue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `loc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `loc_meeting_id` bigint(20) NOT NULL,
  `loc_principal` tinyint(1) NOT NULL DEFAULT '0',
  `loc_type` enum('mumble','irc','afk') NOT NULL,
  `loc_extra` varchar(2048) NOT NULL,
  PRIMARY KEY (`loc_id`),
  KEY `loc_meeting_id` (`loc_meeting_id`),
  KEY `loc_principal` (`loc_principal`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meetings`
--

CREATE TABLE IF NOT EXISTS `meetings` (
  `mee_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mee_label` varchar(255) NOT NULL,
  `mee_class` enum('event-important','event-success','event-warning','event-info','event-inverse','event-special') NOT NULL,
  `mee_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `mee_status` enum('construction','open','closed','waiting') NOT NULL DEFAULT 'construction',
  `mee_president_member_id` bigint(20) DEFAULT NULL,
  `mee_secretary_member_id` bigint(20) DEFAULT NULL,
  `mee_secretary_agenda_id` bigint(20) DEFAULT NULL COMMENT 'Current agenda id viewed by the secretary',
  `mee_meeting_type_id` bigint(20) NOT NULL,
  `mee_datetime` datetime DEFAULT NULL,
  `mee_expected_duration` int(11) NOT NULL DEFAULT '60' COMMENT 'value in minutes',
  `mee_start_time` datetime DEFAULT NULL,
  `mee_finish_time` datetime DEFAULT NULL,
  PRIMARY KEY (`mee_id`),
  KEY `mee_meeting_type_id` (`mee_meeting_type_id`),
  KEY `mee_deleted` (`mee_deleted`),
  KEY `mee_president_member_id` (`mee_president_member_id`),
  KEY `mee_secretary_member_id` (`mee_secretary_member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meeting_rights`
--

CREATE TABLE IF NOT EXISTS `meeting_rights` (
  `mri_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mri_meeting_id` bigint(20) DEFAULT NULL,
  `mri_right` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`mri_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meeting_types`
--

CREATE TABLE IF NOT EXISTS `meeting_types` (
  `mty_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mty_key` varchar(255) NOT NULL,
  `mty_default_label` varchar(255) NOT NULL,
  PRIMARY KEY (`mty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `motions`
--

CREATE TABLE IF NOT EXISTS `motions` (
  `mot_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mot_agenda_id` bigint(20) NOT NULL,
  `mot_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `mot_status` enum('construction','voting','resolved') NOT NULL DEFAULT 'construction',
  `mot_type` enum('yes_no','a_b_c') NOT NULL,
  `mot_win_limit` int(11) NOT NULL DEFAULT '50' COMMENT 'The percent need by a proposition for winning',
  `mot_title` varchar(255) NOT NULL,
  `mot_description` text NOT NULL,
  PRIMARY KEY (`mot_id`),
  KEY `mot_agenda_id` (`mot_agenda_id`),
  KEY `mot_deleted` (`mot_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `motion_propositions`
--

CREATE TABLE IF NOT EXISTS `motion_propositions` (
  `mpr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mpr_motion_id` bigint(20) NOT NULL,
  `mpr_label` varchar(255) NOT NULL,
  `mpr_winning` tinyint(4) NOT NULL DEFAULT '0',
  `mpr_neutral` tinyint(4) NOT NULL DEFAULT '0',
  `mpr_explanation` text NOT NULL,
  PRIMARY KEY (`mpr_id`),
  KEY `mpr_motion_id` (`mpr_motion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `notices`
--

CREATE TABLE IF NOT EXISTS `notices` (
  `not_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `not_meeting_id` bigint(20) NOT NULL,
  `not_noticed` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Boolean marking if a group has been noticed',
  `not_target_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups','con_external') NOT NULL,
  `not_target_id` int(11) DEFAULT NULL,
  `not_external_mails` varchar(2048) NOT NULL,
  `not_voting` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`not_id`),
  KEY `not_meeting_id` (`not_meeting_id`),
  KEY `not_noticed` (`not_noticed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pings`
--

CREATE TABLE IF NOT EXISTS `pings` (
  `pin_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pin_meeting_id` bigint(20) NOT NULL,
  `pin_datetime` datetime NOT NULL,
  `pin_first_presence_datetime` datetime DEFAULT NULL,
  `pin_noticed` tinyint(4) NOT NULL DEFAULT '0',
  `pin_member_id` int(11) DEFAULT NULL,
  `pin_guest_id` bigint(20) DEFAULT NULL,
  `pin_nickname` varchar(255) DEFAULT NULL,
  `pin_speaking` tinyint(4) NOT NULL DEFAULT '0',
  `pin_speaking_request` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pin_id`),
  KEY `pin_meeting_id` (`pin_meeting_id`),
  KEY `pin_member_id` (`pin_member_id`),
  KEY `pin_first_presence` (`pin_first_presence_datetime`),
  KEY `pin_noticed` (`pin_noticed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `tas_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tas_agenda_id` bigint(20) NOT NULL,
  `tas_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `tas_label` varchar(2048) NOT NULL,
  `tas_target_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups','galette_adherent') NOT NULL,
  `tas_target_id` bigint(20) NOT NULL,
  `tas_start_datetime` datetime DEFAULT NULL,
  `tas_finish_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`tas_id`),
  KEY `tas_agenda_id` (`tas_agenda_id`),
  KEY `tas_deleted` (`tas_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

CREATE TABLE IF NOT EXISTS `votes` (
  `vot_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `vot_member_id` bigint(20) NOT NULL,
  `vot_motion_proposition_id` bigint(20) NOT NULL,
  `vot_power` int(11) NOT NULL,
  PRIMARY KEY (`vot_id`),
  KEY `vot_member_id` (`vot_member_id`),
  KEY `vot_motion_proposition_id` (`vot_motion_proposition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Structure de la table `chat_advices`
--

CREATE TABLE `chat_advices` (
  `cad_id` bigint(20) NOT NULL,
  `cad_chat_id` bigint(20) DEFAULT NULL,
  `cad_user_id` bigint(20) DEFAULT NULL,
  `cad_advice` enum('thumb_up','thumb_down') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;