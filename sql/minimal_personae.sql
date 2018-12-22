-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Client :  192.168.0.100:3306
-- Généré le :  Jeu 13 Décembre 2018 à 20:17
-- Version du serveur :  10.1.11-MariaDB-1~jessie
-- Version de PHP :  5.6.4-4ubuntu6.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de données :  `personae`
--

-- --------------------------------------------------------

--
-- Structure de la table `dlp_candidates`
--

CREATE TABLE `dlp_candidates` (
  `can_id` int(11) NOT NULL,
  `can_member_id` int(11) NOT NULL,
  `can_theme_id` int(11) NOT NULL,
  `can_status` enum('neutral','candidate','anti') NOT NULL DEFAULT 'candidate',
  `can_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_delegations`
--

CREATE TABLE `dlp_delegations` (
  `del_id` int(11) NOT NULL,
  `del_member_from` int(11) NOT NULL,
  `del_member_to` int(11) NOT NULL,
  `del_theme_id` int(11) NOT NULL,
  `del_theme_type` enum('dlp_themes','dlp_groups') NOT NULL,
  `del_power` int(11) NOT NULL DEFAULT '0',
  `del_delegation_condition_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_delegation_conditions`
--

CREATE TABLE `dlp_delegation_conditions` (
  `dco_id` bigint(20) NOT NULL,
  `dco_theme_id` int(11) NOT NULL,
  `dco_theme_type` enum('dlp_themes','dlp_groups') NOT NULL,
  `dco_member_from` bigint(20) NOT NULL,
  `dco_order` int(11) NOT NULL DEFAULT '0',
  `dco_end_of_delegation` tinyint(4) NOT NULL DEFAULT '0',
  `dco_conditions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_fixations`
--

CREATE TABLE `dlp_fixations` (
  `fix_id` int(11) NOT NULL,
  `fix_until_date` date DEFAULT NULL,
  `fix_theme_id` int(11) NOT NULL,
  `fix_theme_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_fixation_members`
--

CREATE TABLE `dlp_fixation_members` (
  `fme_fixation_id` int(11) NOT NULL,
  `fme_member_id` int(11) NOT NULL,
  `fme_power` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_groups`
--

CREATE TABLE `dlp_groups` (
  `gro_id` int(11) NOT NULL,
  `gro_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `gro_label` varchar(255) NOT NULL,
  `gro_contact_type` enum('mail','discourse_group','discourse_category','none') DEFAULT NULL,
  `gro_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_group_admins`
--

CREATE TABLE `dlp_group_admins` (
  `gad_group_id` int(11) NOT NULL,
  `gad_member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_group_authoritatives`
--

CREATE TABLE `dlp_group_authoritatives` (
  `gau_group_id` int(11) NOT NULL,
  `gau_authoritative_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_group_themes`
--

CREATE TABLE `dlp_group_themes` (
  `gth_group_id` int(11) NOT NULL,
  `gth_theme_id` int(11) NOT NULL,
  `gth_theme_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups') NOT NULL,
  `gth_power` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_themes`
--

CREATE TABLE `dlp_themes` (
  `the_id` int(11) NOT NULL,
  `the_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `the_label` varchar(255) NOT NULL,
  `the_discourse_group_labels` varchar(2048) NOT NULL DEFAULT '[]' COMMENT 'Contains the group labels (they are uniques)',
  `the_discord_export` tinyint(4) NOT NULL DEFAULT '0',
  `the_min_members` int(11) NOT NULL DEFAULT '1',
  `the_max_members` int(11) DEFAULT NULL,
  `the_dilution` int(11) NOT NULL DEFAULT '100' COMMENT 'The dilution value for each step of delegation',
  `the_max_delegations` int(11) NOT NULL DEFAULT '0' COMMENT 'The maximum of delegation, 0 for unlimited delegations',
  `the_current_fixation_id` int(11) NOT NULL,
  `the_type_date` enum('date','periodicity') NOT NULL DEFAULT 'date',
  `the_next_fixation_date` date NOT NULL,
  `the_next_fixed_until_date` date DEFAULT NULL,
  `the_periodicity` enum('hour','day','month','year') DEFAULT NULL,
  `the_secret_until_fixation` tinyint(4) NOT NULL DEFAULT '0',
  `the_voting_group_id` int(11) DEFAULT NULL,
  `the_voting_group_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups') DEFAULT NULL,
  `the_voting_power` int(11) NOT NULL DEFAULT '1',
  `the_voting_method` enum('demliq','sort','external_results') NOT NULL DEFAULT 'demliq',
  `the_eligible_group_id` int(11) DEFAULT NULL,
  `the_eligible_group_type` enum('galette_adherents','galette_groups','dlp_themes','dlp_groups') DEFAULT NULL,
  `the_delegate_only` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 for delegation only (no mandat)',
  `the_delegation_closed` tinyint(4) NOT NULL DEFAULT '0',
  `the_free_fixed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_theme_admins`
--

CREATE TABLE `dlp_theme_admins` (
  `tad_theme_id` int(11) NOT NULL,
  `tad_member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dlp_user_properties`
--

CREATE TABLE `dlp_user_properties` (
  `upr_id` bigint(20) NOT NULL,
  `upr_user_id` bigint(20) NOT NULL,
  `upr_property` varchar(255) NOT NULL,
  `upr_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `dlp_candidates`
--
ALTER TABLE `dlp_candidates`
  ADD PRIMARY KEY (`can_id`),
  ADD UNIQUE KEY `can_member_id_2` (`can_member_id`,`can_theme_id`),
  ADD KEY `can_member_id` (`can_member_id`),
  ADD KEY `can_theme_id` (`can_theme_id`),
  ADD KEY `can_status` (`can_status`);

--
-- Index pour la table `dlp_delegations`
--
ALTER TABLE `dlp_delegations`
  ADD PRIMARY KEY (`del_id`),
  ADD KEY `del_theme_id` (`del_theme_id`,`del_theme_type`),
  ADD KEY `del_delegation_condition_id` (`del_delegation_condition_id`),
  ADD KEY `del_member_from` (`del_member_from`),
  ADD KEY `del_member_to` (`del_member_to`);

--
-- Index pour la table `dlp_delegation_conditions`
--
ALTER TABLE `dlp_delegation_conditions`
  ADD PRIMARY KEY (`dco_id`),
  ADD KEY `dco_order` (`dco_order`),
  ADD KEY `dco_member_from` (`dco_member_from`),
  ADD KEY `dco_theme_id` (`dco_theme_id`),
  ADD KEY `dco_theme_type` (`dco_theme_type`);

--
-- Index pour la table `dlp_fixations`
--
ALTER TABLE `dlp_fixations`
  ADD PRIMARY KEY (`fix_id`),
  ADD KEY `fix_theme_id` (`fix_theme_id`,`fix_theme_type`);

--
-- Index pour la table `dlp_fixation_members`
--
ALTER TABLE `dlp_fixation_members`
  ADD PRIMARY KEY (`fme_fixation_id`,`fme_member_id`);

--
-- Index pour la table `dlp_groups`
--
ALTER TABLE `dlp_groups`
  ADD PRIMARY KEY (`gro_id`),
  ADD KEY `gro_deleted` (`gro_deleted`);

--
-- Index pour la table `dlp_group_admins`
--
ALTER TABLE `dlp_group_admins`
  ADD PRIMARY KEY (`gad_group_id`,`gad_member_id`);

--
-- Index pour la table `dlp_group_authoritatives`
--
ALTER TABLE `dlp_group_authoritatives`
  ADD PRIMARY KEY (`gau_group_id`,`gau_authoritative_id`);

--
-- Index pour la table `dlp_group_themes`
--
ALTER TABLE `dlp_group_themes`
  ADD PRIMARY KEY (`gth_group_id`,`gth_theme_id`,`gth_theme_type`),
  ADD KEY `gth_target_id` (`gth_theme_id`,`gth_theme_type`);

--
-- Index pour la table `dlp_themes`
--
ALTER TABLE `dlp_themes`
  ADD PRIMARY KEY (`the_id`),
  ADD KEY `the_voting_group_id` (`the_voting_group_id`,`the_voting_group_type`),
  ADD KEY `the_eligible_group_id` (`the_eligible_group_id`,`the_eligible_group_type`),
  ADD KEY `the_discourse_group_labels` (`the_discourse_group_labels`(767)),
  ADD KEY `the_periodicity` (`the_periodicity`),
  ADD KEY `the_discord_export` (`the_discord_export`);

--
-- Index pour la table `dlp_theme_admins`
--
ALTER TABLE `dlp_theme_admins`
  ADD PRIMARY KEY (`tad_theme_id`,`tad_member_id`);

--
-- Index pour la table `dlp_user_properties`
--
ALTER TABLE `dlp_user_properties`
  ADD PRIMARY KEY (`upr_id`),
  ADD UNIQUE KEY `upr_user_id_property_unique` (`upr_user_id`,`upr_property`) USING BTREE;

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `dlp_candidates`
--
ALTER TABLE `dlp_candidates`
  MODIFY `can_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;
--
-- AUTO_INCREMENT pour la table `dlp_delegations`
--
ALTER TABLE `dlp_delegations`
  MODIFY `del_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=467;
--
-- AUTO_INCREMENT pour la table `dlp_delegation_conditions`
--
ALTER TABLE `dlp_delegation_conditions`
  MODIFY `dco_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;
--
-- AUTO_INCREMENT pour la table `dlp_fixations`
--
ALTER TABLE `dlp_fixations`
  MODIFY `fix_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;
--
-- AUTO_INCREMENT pour la table `dlp_groups`
--
ALTER TABLE `dlp_groups`
  MODIFY `gro_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT pour la table `dlp_themes`
--
ALTER TABLE `dlp_themes`
  MODIFY `the_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
--
-- AUTO_INCREMENT pour la table `dlp_user_properties`
--
ALTER TABLE `dlp_user_properties`
  MODIFY `upr_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
