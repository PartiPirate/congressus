--
-- Structure de la table `galette_adherents`
--

CREATE TABLE `galette_adherents` (
  `id_adh` int(10) UNSIGNED NOT NULL,
  `id_statut` int(10) UNSIGNED NOT NULL DEFAULT '4',
  `nom_adh` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `prenom_adh` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email_adh` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pseudo_adh` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `login_adh` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mdp_adh` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_crea_adh` date NOT NULL DEFAULT '1901-01-01',
  `date_modif_adh` date NOT NULL DEFAULT '1901-01-01',
  `date_echeance` date DEFAULT NULL,
  `pref_lang` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'fr_FR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `galette_groups`
--

CREATE TABLE `galette_groups` (
  `id_group` int(10) NOT NULL,
  `group_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `parent_group` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `galette_groups_managers`
--

CREATE TABLE `galette_groups_managers` (
  `id_group` int(10) NOT NULL,
  `id_adh` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `galette_groups_members`
--

CREATE TABLE `galette_groups_members` (
  `id_group` int(10) NOT NULL,
  `id_adh` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `galette_pictures`
--

CREATE TABLE `galette_pictures` (
  `id_adh` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `picture` mediumblob NOT NULL,
  `format` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `galette_adherents`
--
ALTER TABLE `galette_adherents`
  ADD PRIMARY KEY (`id_adh`),
  ADD UNIQUE KEY `login_adh` (`login_adh`),
  ADD KEY `id_statut` (`id_statut`);

--
-- Index pour la table `galette_groups`
--
ALTER TABLE `galette_groups`
  ADD PRIMARY KEY (`id_group`),
  ADD UNIQUE KEY `name` (`group_name`),
  ADD KEY `parent_group` (`parent_group`);

--
-- Index pour la table `galette_groups_managers`
--
ALTER TABLE `galette_groups_managers`
  ADD PRIMARY KEY (`id_group`,`id_adh`),
  ADD KEY `id_adh` (`id_adh`);

--
-- Index pour la table `galette_groups_members`
--
ALTER TABLE `galette_groups_members`
  ADD PRIMARY KEY (`id_group`,`id_adh`),
  ADD KEY `id_adh` (`id_adh`);

--
-- Index pour la table `galette_pictures`
--
ALTER TABLE `galette_pictures`
  ADD PRIMARY KEY (`id_adh`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `galette_adherents`
--
ALTER TABLE `galette_adherents`
  MODIFY `id_adh` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `galette_groups`
--
ALTER TABLE `galette_groups`
  MODIFY `id_group` int(10) NOT NULL AUTO_INCREMENT;
