-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 13, 2026 at 01:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ArtisanConnect` 
--

-- --------------------------------------------------------

--
-- Table structure for table `acheteurs` 
--

CREATE TABLE `acheteurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `adresse_livraison` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`adresse_livraison`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administrateurs` 
--

CREATE TABLE `administrateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `niveau_acces` enum('super_admin','admin','moderateur') DEFAULT 'moderateur',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `artisans` 
--

CREATE TABLE `artisans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `biographie` text DEFAULT NULL,
  `specialite` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `adresse_atelier` text DEFAULT NULL,
  `compte_valide` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `avis` 
--

CREATE TABLE `avis` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `acheteur_id` bigint(20) UNSIGNED NOT NULL,
  `oeuvre_id` bigint(20) UNSIGNED NOT NULL,
  `artisan_id` bigint(20) UNSIGNED NOT NULL,
  `note` tinyint(3) UNSIGNED NOT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('publie','en_attente','refuse') DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `categories` 
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories` 
--

INSERT INTO `categories` (`id`, `nom`, `slug`, `description`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Sculpture', 'sculpture', 'Sculptures en bois, bronze, pierre', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(2, 'Tissage', 'tissage', 'Tissus traditionnels et tapisseries', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(3, 'Poterie', 'poterie', 'Céramiques et vases artisanaux', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(4, 'Vannerie', 'vannerie', 'Paniers et objets tressés', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(5, 'Bijouterie', 'bijouterie', 'Bijoux traditionnels', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(6, 'Maroquinerie', 'maroquinerie', 'Articles en cuir', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(7, 'Peinture', 'peinture', 'Tableaux et peintures', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10'),
(8, 'Masques', 'masques', 'Masques traditionnels', NULL, '2026-02-13 12:34:10', '2026-02-13 12:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `favoris` 
--

CREATE TABLE `favoris` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `acheteur_id` bigint(20) UNSIGNED NOT NULL,
  `oeuvre_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images` 
--

CREATE TABLE `images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `oeuvre_id` bigint(20) UNSIGNED NOT NULL,
  `chemin` varchar(500) NOT NULL,
  `type` enum('principale','secondaire') DEFAULT 'secondaire',
  `ordre` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications` 
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `utilisateur_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `lue` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oeuvres` 
--

CREATE TABLE `oeuvres` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `artisan_id` bigint(20) UNSIGNED NOT NULL,
  `categorie_id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `quantite_disponible` int(10) UNSIGNED DEFAULT 1,
  `dimensions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dimensions`)),
  `materiaux` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`materiaux`)),
  `statut` enum('brouillon','en_attente','validee','refusee','epuisee') DEFAULT 'brouillon',
  `motif_refus` text DEFAULT NULL,
  `date_validation` timestamp NULL DEFAULT NULL,
  `validateur_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions` 
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(50) NOT NULL,
  `acheteur_id` bigint(20) UNSIGNED NOT NULL,
  `oeuvre_id` bigint(20) UNSIGNED NOT NULL,
  `artisan_id` bigint(20) UNSIGNED NOT NULL,
  `quantite` int(10) UNSIGNED DEFAULT 1,
  `montant_total` decimal(12,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL,
  `montant_artisan` decimal(12,2) NOT NULL,
  `statut` enum('en_attente','payee','livree','annulee') DEFAULT 'en_attente',
  `mode_paiement` enum('mobile_money','carte_bancaire','virement') NOT NULL,
  `adresse_livraison` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`adresse_livraison`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs` 
--

CREATE TABLE `utilisateurs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('artisan','acheteur','administrateur') NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `email_verifie_le` timestamp NULL DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acheteurs` 
--
ALTER TABLE `acheteurs` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `administrateurs` 
--
ALTER TABLE `administrateurs` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `artisans` 
--
ALTER TABLE `artisans` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Indexes for table `avis` 
--
ALTER TABLE `avis` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `acheteur_id` (`acheteur_id`),
  ADD KEY `oeuvre_id` (`oeuvre_id`),
  ADD KEY `artisan_id` (`artisan_id`);

--
-- Indexes for table `categories` 
--
ALTER TABLE `categories` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indexes for table `favoris` 
--
ALTER TABLE `favoris` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favori` (`acheteur_id`,`oeuvre_id`),
  ADD KEY `oeuvre_id` (`oeuvre_id`);

--
-- Indexes for table `images` 
--
ALTER TABLE `images` 
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oeuvre` (`oeuvre_id`);

--
-- Indexes for table `notifications` 
--

ALTER TABLE `notifications` 
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_lue` (`lue`);

--
-- Indexes for table `oeuvres` 
--
ALTER TABLE `oeuvres` 
  ADD PRIMARY KEY (`id`),
  ADD KEY `validateur_id` (`validateur_id`),
  ADD KEY `idx_artisan` (`artisan_id`),
  ADD KEY `idx_categorie` (`categorie_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Indexes for table `transactions` 
--
ALTER TABLE `transactions` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `artisan_id` (`artisan_id`),
  ADD KEY `idx_acheteur` (`acheteur_id`),
  ADD KEY `idx_oeuvre` (`oeuvre_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Indexes for table `utilisateurs` 
--
ALTER TABLE `utilisateurs` 
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acheteurs` 
--
ALTER TABLE `acheteurs` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `administrateurs` 
--
ALTER TABLE `administrateurs` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `artisans` 
--
ALTER TABLE `artisans` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `avis` 
--
ALTER TABLE `avis` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories` 
--
ALTER TABLE `categories` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `favoris` 
--
ALTER TABLE `favoris` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images` 
--
ALTER TABLE `images` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications` 
--
ALTER TABLE `notifications` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oeuvres` 
--
ALTER TABLE `oeuvres` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions` 
--
ALTER TABLE `transactions` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateurs` 
--
ALTER TABLE `utilisateurs` 
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acheteurs` 
--
ALTER TABLE `acheteurs` 
  ADD CONSTRAINT `acheteurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `administrateurs` 
--
ALTER TABLE `administrateurs` 
  ADD CONSTRAINT `administrateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `artisans` 
--
ALTER TABLE `artisans` 
  ADD CONSTRAINT `artisans_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `avis` 
--
ALTER TABLE `avis` 
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`acheteur_id`) REFERENCES `acheteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_3` FOREIGN KEY (`oeuvre_id`) REFERENCES `oeuvres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_4` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories` 
--
ALTER TABLE `categories` 
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `favoris` 
--
ALTER TABLE `favoris` 
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`acheteur_id`) REFERENCES `acheteurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`oeuvre_id`) REFERENCES `oeuvres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `images` 
--
ALTER TABLE `images` 
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`oeuvre_id`) REFERENCES `oeuvres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications` 
--
ALTER TABLE `notifications` 
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oeuvres` 
--
ALTER TABLE `oeuvres` 
  ADD CONSTRAINT `oeuvres_ibfk_1` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oeuvres_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `oeuvres_ibfk_3` FOREIGN KEY (`validateur_id`) REFERENCES `administrateurs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions` 
--
ALTER TABLE `transactions` 
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`acheteur_id`) REFERENCES `acheteurs` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`oeuvre_id`) REFERENCES `oeuvres` (`id`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`artisan_id`) REFERENCES `artisans` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
