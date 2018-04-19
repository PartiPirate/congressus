--
-- Base de données :  `congressus`
--

-- --------------------------------------------------------

--
-- Structure de la table `agendas`
--

CREATE TABLE `agendas` (
  `age_id` bigint(20) NOT NULL,
  `age_meeting_id` bigint(20) NOT NULL,
  `age_parent_id` bigint(20) DEFAULT NULL,
  `age_order` int(11) NOT NULL DEFAULT '1',
  `age_active` tinyint(4) NOT NULL DEFAULT '0',
  `age_expected_duration` int(11) NOT NULL COMMENT 'value in minutes',
  `age_duration` int(11) DEFAULT NULL COMMENT 'value in minutes',
  `age_label` varchar(255) NOT NULL,
  `age_objects` text NOT NULL,
  `age_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `chats`
--

CREATE TABLE `chats` (
  `cha_id` bigint(20) NOT NULL,
  `cha_agenda_id` bigint(20) NOT NULL,
  `cha_motion_id` bigint(20) DEFAULT NULL COMMENT 'A chat can be attached to a motion',
  `cha_parent_id` bigint(20) DEFAULT NULL,
  `cha_member_id` bigint(20) DEFAULT NULL,
  `cha_guest_id` bigint(20) DEFAULT NULL,
  `cha_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `cha_type` enum('neutral','pro','against') NOT NULL DEFAULT 'neutral' COMMENT 'a chat can be neutral (default mode), pro or against',
  `cha_text` text NOT NULL,
  `cha_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `chat_advices`
--

CREATE TABLE `chat_advices` (
  `cad_id` bigint(20) NOT NULL,
  `cad_chat_id` bigint(20) DEFAULT NULL,
  `cad_user_id` bigint(20) DEFAULT NULL,
  `cad_advice` enum('thumb_up','thumb_down','thumb_middle') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `conclusions`
--

CREATE TABLE `conclusions` (
  `con_id` bigint(20) NOT NULL,
  `con_agenda_id` bigint(20) NOT NULL,
  `con_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `con_text` varchar(2048) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `guests`
--

CREATE TABLE `guests` (
  `gue_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `locations`
--

CREATE TABLE `locations` (
  `loc_id` bigint(20) NOT NULL,
  `loc_meeting_id` bigint(20) NOT NULL,
  `loc_principal` tinyint(1) NOT NULL DEFAULT '0',
  `loc_type` enum('mumble','irc','afk','framatalk','discord') NOT NULL,
  `loc_channel` varchar(255) NOT NULL,
  `loc_extra` varchar(2048) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `log_id` bigint(20) NOT NULL,
  `log_action` varchar(255) DEFAULT NULL,
  `log_user_id` varchar(10) DEFAULT NULL,
  `log_ip` varchar(255) DEFAULT NULL,
  `log_datetime` datetime DEFAULT NULL,
  `log_data` varchar(2048) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meetings`
--

CREATE TABLE `meetings` (
  `mee_id` bigint(20) NOT NULL,
  `mee_label` varchar(255) NOT NULL,
  `mee_type` enum('meeting','construction') NOT NULL DEFAULT 'meeting',
  `mee_class` enum('event-important','event-success','event-warning','event-info','event-inverse','event-special') NOT NULL,
  `mee_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `mee_status` enum('construction','open','closed','waiting','deleted') NOT NULL DEFAULT 'construction',
  `mee_synchro_vote` tinyint(4) NOT NULL DEFAULT '1',
  `mee_president_member_id` bigint(20) DEFAULT NULL,
  `mee_secretary_member_id` bigint(20) DEFAULT NULL,
  `mee_secretary_agenda_id` bigint(20) DEFAULT NULL COMMENT 'Current agenda id viewed by the secretary',
  `mee_meeting_type_id` bigint(20) NOT NULL,
  `mee_datetime` datetime DEFAULT NULL,
  `mee_expected_duration` int(11) NOT NULL DEFAULT '60' COMMENT 'value in minutes',
  `mee_start_time` datetime DEFAULT NULL,
  `mee_finish_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meeting_rights`
--

CREATE TABLE `meeting_rights` (
  `mri_id` bigint(20) NOT NULL,
  `mri_meeting_id` bigint(20) DEFAULT NULL,
  `mri_right` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `meeting_types`
--

CREATE TABLE `meeting_types` (
  `mty_id` bigint(20) NOT NULL,
  `mty_key` varchar(255) NOT NULL,
  `mty_default_label` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `motions`
--

CREATE TABLE `motions` (
  `mot_id` bigint(20) NOT NULL,
  `mot_author_id` bigint(20) DEFAULT NULL,
  `mot_agenda_id` bigint(20) NOT NULL,
  `mot_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `mot_status` enum('construction','voting','resolved') NOT NULL DEFAULT 'construction',
  `mot_pinned` tinyint(4) NOT NULL DEFAULT '0',
  `mot_anonymous` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'set to 1 for forcing the anymous mode during the vote',
  `mot_type` enum('yes_no','a_b_c') NOT NULL,
  `mot_win_limit` int(11) NOT NULL DEFAULT '50' COMMENT 'The percent need by a proposition for winning',
  `mot_title` varchar(255) NOT NULL,
  `mot_description` text NOT NULL,
  `mot_explanation` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `motion_propositions`
--

CREATE TABLE `motion_propositions` (
  `mpr_id` bigint(20) NOT NULL,
  `mpr_motion_id` bigint(20) NOT NULL,
  `mpr_label` varchar(255) NOT NULL,
  `mpr_winning` tinyint(4) NOT NULL DEFAULT '0',
  `mpr_neutral` tinyint(4) NOT NULL DEFAULT '0',
  `mpr_explanation` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `notices`
--

CREATE TABLE `notices` (
  `not_id` bigint(20) NOT NULL,
  `not_meeting_id` bigint(20) NOT NULL,
  `not_noticed` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Boolean marking if a group has been noticed',
  `not_target_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups','con_external','all_members','cus_users') NOT NULL,
  `not_target_id` int(11) DEFAULT NULL,
  `not_external_mails` varchar(2048) NOT NULL,
  `not_voting` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pings`
--

CREATE TABLE `pings` (
  `pin_id` bigint(20) NOT NULL,
  `pin_meeting_id` bigint(20) NOT NULL,
  `pin_datetime` datetime NOT NULL,
  `pin_first_presence_datetime` datetime DEFAULT NULL,
  `pin_noticed` tinyint(4) NOT NULL DEFAULT '0',
  `pin_member_id` int(11) DEFAULT NULL,
  `pin_guest_id` bigint(20) DEFAULT NULL,
  `pin_nickname` varchar(255) DEFAULT NULL,
  `pin_speaking` tinyint(4) NOT NULL DEFAULT '0',
  `pin_speaking_request` int(11) NOT NULL DEFAULT '0',
  `pin_speaking_time` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `sources`
--

CREATE TABLE `sources` (
  `sou_id` bigint(20) NOT NULL,
  `sou_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `sou_motion_id` bigint(20) DEFAULT NULL,
  `sou_is_default_source` tinyint(4) NOT NULL DEFAULT '0',
  `sou_type` enum('leg_text','leg_article','wiki_text','congressus_motion','forum','pdf','free') NOT NULL DEFAULT 'free',
  `sou_url` varchar(2048) DEFAULT NULL,
  `sou_title` varchar(2048) DEFAULT NULL,
  `sou_articles` varchar(2048) NOT NULL DEFAULT '[]',
  `sou_content` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `tas_id` bigint(20) NOT NULL,
  `tas_agenda_id` bigint(20) NOT NULL,
  `tas_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `tas_label` varchar(2048) NOT NULL,
  `tas_target_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups','galette_adherent','all_members','cus_users') NOT NULL,
  `tas_target_id` bigint(20) NOT NULL,
  `tas_start_datetime` datetime DEFAULT NULL,
  `tas_finish_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `votes`
--

CREATE TABLE `votes` (
  `vot_id` bigint(20) NOT NULL,
  `vot_member_id` bigint(20) NOT NULL,
  `vot_motion_proposition_id` bigint(20) NOT NULL,
  `vot_power` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`age_id`),
  ADD KEY `age_meeting_id` (`age_meeting_id`),
  ADD KEY `age_parent_id` (`age_parent_id`),
  ADD KEY `age_order` (`age_order`);

--
-- Index pour la table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`cha_id`),
  ADD KEY `cha_agenda_id` (`cha_agenda_id`),
  ADD KEY `cha_member_id` (`cha_member_id`),
  ADD KEY `cha_deleted` (`cha_deleted`),
  ADD KEY `cha_ghost_id` (`cha_guest_id`),
  ADD KEY `cha_type` (`cha_type`),
  ADD KEY `cha_motion_id` (`cha_motion_id`),
  ADD KEY `chat_parent_id` (`cha_parent_id`);

--
-- Index pour la table `chat_advices`
--
ALTER TABLE `chat_advices`
  ADD PRIMARY KEY (`cad_id`);

--
-- Index pour la table `conclusions`
--
ALTER TABLE `conclusions`
  ADD PRIMARY KEY (`con_id`),
  ADD KEY `con_agenda_id` (`con_agenda_id`),
  ADD KEY `con_deleted` (`con_deleted`);

--
-- Index pour la table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`gue_id`);

--
-- Index pour la table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`loc_id`),
  ADD KEY `loc_meeting_id` (`loc_meeting_id`),
  ADD KEY `loc_principal` (`loc_principal`);

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Index pour la table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`mee_id`),
  ADD KEY `mee_meeting_type_id` (`mee_meeting_type_id`),
  ADD KEY `mee_deleted` (`mee_deleted`),
  ADD KEY `mee_president_member_id` (`mee_president_member_id`),
  ADD KEY `mee_secretary_member_id` (`mee_secretary_member_id`),
  ADD KEY `mee_synchro_vote` (`mee_synchro_vote`),
  ADD KEY `mee_type` (`mee_type`),
  ADD KEY `mee_datetime` (`mee_datetime`),
  ADD KEY `mee_expected_duration` (`mee_expected_duration`);

--
-- Index pour la table `meeting_rights`
--
ALTER TABLE `meeting_rights`
  ADD PRIMARY KEY (`mri_id`);

--
-- Index pour la table `meeting_types`
--
ALTER TABLE `meeting_types`
  ADD PRIMARY KEY (`mty_id`);

--
-- Index pour la table `motions`
--
ALTER TABLE `motions`
  ADD PRIMARY KEY (`mot_id`),
  ADD KEY `mot_agenda_id` (`mot_agenda_id`),
  ADD KEY `mot_deleted` (`mot_deleted`),
  ADD KEY `mot_author_id` (`mot_author_id`),
  ADD KEY `mot_pinned` (`mot_pinned`);

--
-- Index pour la table `motion_propositions`
--
ALTER TABLE `motion_propositions`
  ADD PRIMARY KEY (`mpr_id`),
  ADD KEY `mpr_motion_id` (`mpr_motion_id`);

--
-- Index pour la table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`not_id`),
  ADD KEY `not_meeting_id` (`not_meeting_id`),
  ADD KEY `not_noticed` (`not_noticed`);

--
-- Index pour la table `pings`
--
ALTER TABLE `pings`
  ADD PRIMARY KEY (`pin_id`),
  ADD KEY `pin_meeting_id` (`pin_meeting_id`),
  ADD KEY `pin_member_id` (`pin_member_id`),
  ADD KEY `pin_first_presence` (`pin_first_presence_datetime`),
  ADD KEY `pin_noticed` (`pin_noticed`);

--
-- Index pour la table `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`sou_id`),
  ADD KEY `sou_type` (`sou_type`),
  ADD KEY `sou_title` (`sou_title`(767)),
  ADD KEY `sou_deleted` (`sou_deleted`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`tas_id`),
  ADD KEY `tas_agenda_id` (`tas_agenda_id`),
  ADD KEY `tas_deleted` (`tas_deleted`);

--
-- Index pour la table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vot_id`),
  ADD KEY `vot_member_id` (`vot_member_id`),
  ADD KEY `vot_motion_proposition_id` (`vot_motion_proposition_id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `agendas`
--
ALTER TABLE `agendas`
  MODIFY `age_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `chats`
--
ALTER TABLE `chats`
  MODIFY `cha_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `chat_advices`
--
ALTER TABLE `chat_advices`
  MODIFY `cad_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `conclusions`
--
ALTER TABLE `conclusions`
  MODIFY `con_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `guests`
--
ALTER TABLE `guests`
  MODIFY `gue_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `locations`
--
ALTER TABLE `locations`
  MODIFY `loc_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `mee_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `meeting_rights`
--
ALTER TABLE `meeting_rights`
  MODIFY `mri_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `meeting_types`
--
ALTER TABLE `meeting_types`
  MODIFY `mty_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `motions`
--
ALTER TABLE `motions`
  MODIFY `mot_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `motion_propositions`
--
ALTER TABLE `motion_propositions`
  MODIFY `mpr_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `notices`
--
ALTER TABLE `notices`
  MODIFY `not_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `pings`
--
ALTER TABLE `pings`
  MODIFY `pin_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `sources`
--
ALTER TABLE `sources`
  MODIFY `sou_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `tas_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `votes`
--
ALTER TABLE `votes`
  MODIFY `vot_id` bigint(20) NOT NULL AUTO_INCREMENT;
