-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 21 mars 2023 à 17:20
-- Version du serveur : 10.3.38-MariaDB-0+deb10u1
-- Version de PHP : 7.3.31-1~deb10u3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `myelb1875713`
--

-- --------------------------------------------------------

--
-- Structure de la table `plans`
--

-- CREATE TABLE `plans` ifnotexist (
--   `id` bigint(20) UNSIGNED NOT NULL,
--   `plan_title` enum('Free Trial','Premium') NOT NULL,
--   `duration_time` int(11) NOT NULL,
--   `plan_type` enum('zero','Lite','All Go') NOT NULL,
--   `storage_capacity` enum('4','8','12','24','48','16','32','64') NOT NULL,
--   `price` double(8,2) NOT NULL DEFAULT 0.00,
--   `created_at` timestamp NULL DEFAULT NULL,
--   `updated_at` timestamp NULL DEFAULT NULL
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plans`
--

INSERT INTO `plans` (`id`, `plan_title`, `duration_time`, `plan_type`, `storage_capacity`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Free Trial', 1, 'zero', '8', 0.00, '2023-02-24 12:23:55', '2023-02-24 12:23:55'),
(2, 'Premium', 18, 'Lite', '8', 199.00, '2023-02-24 12:23:55', '2023-02-24 12:23:55'),
(3, 'Premium', 18, 'Lite', '16', 299.00, '2023-03-08 14:27:22', '2023-03-08 14:27:22'),
(4, 'Premium', 18, 'Lite', '32', 399.00, '2023-03-08 14:28:28', '2023-03-08 14:28:29'),
(5, 'Premium', 18, 'Lite', '64', 499.00, '2023-03-08 14:32:25', '2023-03-08 14:32:25'),
(6, 'Premium', 18, 'All Go', '8', 299.00, '2023-03-08 14:34:23', '2023-03-08 14:34:24'),
(7, 'Premium', 18, 'All Go', '16', 399.00, '2023-03-08 14:34:58', '2023-03-08 14:34:58'),
(8, 'Premium', 18, 'All Go', '32', 499.00, '2023-03-08 14:35:23', '2023-03-08 14:35:23'),
(9, 'Premium', 18, 'All Go', '64', 599.00, '2023-03-08 14:36:19', '2023-03-08 14:36:19'),
(10, 'Premium', 36, 'Lite', '8', 399.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(11, 'Premium', 36, 'Lite', '16', 599.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(12, 'Premium', 36, 'Lite', '32', 799.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(13, 'Premium', 36, 'Lite', '64', 999.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(14, 'Premium', 36, 'All Go', '8', 599.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(15, 'Premium', 36, 'All Go', '16', 799.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(16, 'Premium', 36, 'All Go', '32', 999.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(17, 'Premium', 36, 'All Go', '64', 1199.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(18, 'Premium', 60, 'Lite', '8', 599.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(19, 'Premium', 60, 'Lite', '16', 899.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(20, 'Premium', 60, 'Lite', '32', 1199.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(21, 'Premium', 60, 'Lite', '64', 1499.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(22, 'Premium', 60, 'All Go', '8', 899.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(23, 'Premium', 60, 'All Go', '16', 1199.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(24, 'Premium', 60, 'All Go', '32', 1499.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(25, 'Premium', 60, 'All Go', '64', 1799.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(27, 'Premium', 6, 'Lite', '8', 199.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(28, 'Premium', 6, 'Lite', '16', 399.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(29, 'Premium', 6, 'Lite', '32', 399.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(30, 'Premium', 6, 'Lite', '64', 499.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(32, 'Premium', 6, 'All Go', '8', 239.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(34, 'Premium', 6, 'All Go', '16', 479.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(35, 'Premium', 6, 'All Go', '32', 479.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03'),
(36, 'Premium', 6, 'All Go', '64', 599.00, '2023-03-08 14:43:02', '2023-03-08 14:43:03');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
