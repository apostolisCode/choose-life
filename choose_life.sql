-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2024 at 07:39 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `choose_life`
--

-- --------------------------------------------------------

--
-- Table structure for table `cp_commentmeta`
--

DROP TABLE IF EXISTS `cp_commentmeta`;
CREATE TABLE IF NOT EXISTS `cp_commentmeta` (
  `meta_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cp_comments`
--

DROP TABLE IF EXISTS `cp_comments`;
CREATE TABLE IF NOT EXISTS `cp_comments` (
  `comment_ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint UNSIGNED NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'comment',
  `comment_parent` bigint UNSIGNED NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_comments`
--

INSERT INTO `cp_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`) VALUES
(1, 1, 'Ένας σχολιαστής WordPress', 'wapuu@wordpress.example', 'https://wordpress.org/', '', '2024-04-08 17:14:26', '2024-04-08 14:14:26', 'Γεια σας, αυτό είναι ένα σχόλιο.\nΓια να ξεκινήσετε με την έγκριση, επεξεργασία και διαγραφή σχολίων, παρακαλώ επισκεφθείτε την οθόνη Σχόλια στον Πίνακα Ελέγχου.\nΤα άβαταρ των σχολιαστών προέρχονται από το <a href=\"https://en.gravatar.com/\">Gravatar</a>.', 0, '1', '', 'comment', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_links`
--

DROP TABLE IF EXISTS `cp_links`;
CREATE TABLE IF NOT EXISTS `cp_links` (
  `link_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint UNSIGNED NOT NULL DEFAULT '1',
  `link_rating` int NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cp_options`
--

DROP TABLE IF EXISTS `cp_options`;
CREATE TABLE IF NOT EXISTS `cp_options` (
  `option_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=MyISAM AUTO_INCREMENT=839 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_options`
--

INSERT INTO `cp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'siteurl', 'http://localhost/choose-life', 'yes'),
(2, 'home', 'http://localhost/choose-life', 'yes'),
(3, 'blogname', 'Choose life', 'yes'),
(4, 'blogdescription', '', 'yes'),
(5, 'users_can_register', '0', 'yes'),
(6, 'admin_email', 'apostolis.kyromitis@novidea.gr', 'yes'),
(7, 'start_of_week', '1', 'yes'),
(8, 'use_balanceTags', '0', 'yes'),
(9, 'use_smilies', '1', 'yes'),
(10, 'require_name_email', '1', 'yes'),
(11, 'comments_notify', '1', 'yes'),
(12, 'posts_per_rss', '10', 'yes'),
(13, 'rss_use_excerpt', '0', 'yes'),
(14, 'mailserver_url', 'mail.example.com', 'yes'),
(15, 'mailserver_login', 'login@example.com', 'yes'),
(16, 'mailserver_pass', 'password', 'yes'),
(17, 'mailserver_port', '110', 'yes'),
(18, 'default_category', '1', 'yes'),
(19, 'default_comment_status', 'open', 'yes'),
(20, 'default_ping_status', 'open', 'yes'),
(21, 'default_pingback_flag', '0', 'yes'),
(22, 'posts_per_page', '10', 'yes'),
(23, 'date_format', 'j F Y', 'yes'),
(24, 'time_format', 'H:i', 'yes'),
(25, 'links_updated_date_format', 'd/m/Y, H:i', 'yes'),
(26, 'comment_moderation', '0', 'yes'),
(27, 'moderation_notify', '1', 'yes'),
(28, 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/', 'yes'),
(29, 'rewrite_rules', 'a:108:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:36:\"payments/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:46:\"payments/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:66:\"payments/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:61:\"payments/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:61:\"payments/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:29:\"payments/([^/]+)/trackback/?$\";s:35:\"index.php?payments=$matches[1]&tb=1\";s:37:\"payments/([^/]+)/page/?([0-9]{1,})/?$\";s:48:\"index.php?payments=$matches[1]&paged=$matches[2]\";s:44:\"payments/([^/]+)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?payments=$matches[1]&cpage=$matches[2]\";s:33:\"payments/([^/]+)(?:/([0-9]+))?/?$\";s:47:\"index.php?payments=$matches[1]&page=$matches[2]\";s:25:\"payments/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:35:\"payments/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:55:\"payments/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:50:\"payments/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:50:\"payments/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:45:\"postman_sent_mail/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:55:\"postman_sent_mail/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:75:\"postman_sent_mail/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:70:\"postman_sent_mail/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:70:\"postman_sent_mail/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:38:\"postman_sent_mail/([^/]+)/trackback/?$\";s:44:\"index.php?postman_sent_mail=$matches[1]&tb=1\";s:46:\"postman_sent_mail/([^/]+)/page/?([0-9]{1,})/?$\";s:57:\"index.php?postman_sent_mail=$matches[1]&paged=$matches[2]\";s:53:\"postman_sent_mail/([^/]+)/comment-page-([0-9]{1,})/?$\";s:57:\"index.php?postman_sent_mail=$matches[1]&cpage=$matches[2]\";s:42:\"postman_sent_mail/([^/]+)(?:/([0-9]+))?/?$\";s:56:\"index.php?postman_sent_mail=$matches[1]&page=$matches[2]\";s:34:\"postman_sent_mail/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:44:\"postman_sent_mail/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:64:\"postman_sent_mail/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:59:\"postman_sent_mail/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:59:\"postman_sent_mail/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:27:\"comment-page-([0-9]{1,})/?$\";s:38:\"index.php?&page_id=2&cpage=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:58:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:68:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:88:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:83:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/trackback/?$\";s:85:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&tb=1\";s:77:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&feed=$matches[5]\";s:65:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/page/?([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&paged=$matches[5]\";s:72:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)/comment-page-([0-9]{1,})/?$\";s:98:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&cpage=$matches[5]\";s:61:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/([^/]+)(?:/([0-9]+))?/?$\";s:97:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&page=$matches[5]\";s:47:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:57:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:77:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:72:\"[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}/[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&cpage=$matches[4]\";s:51:\"([0-9]{4})/([0-9]{1,2})/comment-page-([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&cpage=$matches[3]\";s:38:\"([0-9]{4})/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&cpage=$matches[2]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";}', 'yes'),
(30, 'hack_file', '0', 'yes'),
(31, 'blog_charset', 'UTF-8', 'yes'),
(32, 'moderation_keys', '', 'no'),
(33, 'active_plugins', 'a:16:{i:0;s:31:\"acf-country-3.x/acf-country.php\";i:1;s:34:\"advanced-custom-fields-pro/acf.php\";i:2;s:33:\"classic-editor/classic-editor.php\";i:3;s:38:\"contact-form-7-multilingual/plugin.php\";i:4;s:52:\"contact-form-7-template-support/template-support.php\";i:5;s:36:\"contact-form-7/wp-contact-form-7.php\";i:7;s:37:\"disable-comments/disable-comments.php\";i:8;s:33:\"duplicate-post/duplicate-post.php\";i:9;s:41:\"filenames-to-latin/filenames-to-latin.php\";i:10;s:59:\"intuitive-custom-post-order/intuitive-custom-post-order.php\";i:11;s:21:\"jwt-auth/jwt-auth.php\";i:12;s:31:\"jwt-whitelist/jwt-whitelist.php\";i:13;s:26:\"post-smtp/postman-smtp.php\";i:14;s:21:\"safe-svg/safe-svg.php\";i:15;s:33:\"user-switching/user-switching.php\";i:16;s:24:\"wordpress-seo/wp-seo.php\";}', 'yes'),
(34, 'category_base', '', 'yes'),
(35, 'ping_sites', 'http://rpc.pingomatic.com/', 'yes'),
(36, 'comment_max_links', '2', 'yes'),
(37, 'gmt_offset', '3', 'yes'),
(38, 'default_email_category', '1', 'yes'),
(39, 'recently_edited', '', 'no'),
(40, 'template', 'choose-life', 'yes'),
(41, 'stylesheet', 'choose-life', 'yes'),
(42, 'comment_registration', '0', 'yes'),
(43, 'html_type', 'text/html', 'yes'),
(44, 'use_trackback', '0', 'yes'),
(45, 'default_role', 'subscriber', 'yes'),
(46, 'db_version', '57155', 'yes'),
(47, 'uploads_use_yearmonth_folders', '1', 'yes'),
(48, 'upload_path', '', 'yes'),
(49, 'blog_public', '0', 'yes'),
(50, 'default_link_category', '2', 'yes'),
(51, 'show_on_front', 'page', 'yes'),
(52, 'tag_base', '', 'yes'),
(53, 'show_avatars', '1', 'yes'),
(54, 'avatar_rating', 'G', 'yes'),
(55, 'upload_url_path', '', 'yes'),
(56, 'thumbnail_size_w', '150', 'yes'),
(57, 'thumbnail_size_h', '150', 'yes'),
(58, 'thumbnail_crop', '1', 'yes'),
(59, 'medium_size_w', '300', 'yes'),
(60, 'medium_size_h', '300', 'yes'),
(61, 'avatar_default', 'mystery', 'yes'),
(62, 'large_size_w', '1024', 'yes'),
(63, 'large_size_h', '1024', 'yes'),
(64, 'image_default_link_type', 'none', 'yes'),
(65, 'image_default_size', '', 'yes'),
(66, 'image_default_align', '', 'yes'),
(67, 'close_comments_for_old_posts', '0', 'yes'),
(68, 'close_comments_days_old', '14', 'yes'),
(69, 'thread_comments', '1', 'yes'),
(70, 'thread_comments_depth', '5', 'yes'),
(71, 'page_comments', '0', 'yes'),
(72, 'comments_per_page', '50', 'yes'),
(73, 'default_comments_page', 'newest', 'yes'),
(74, 'comment_order', 'asc', 'yes'),
(75, 'sticky_posts', 'a:0:{}', 'yes'),
(76, 'widget_categories', 'a:0:{}', 'yes'),
(77, 'widget_text', 'a:0:{}', 'yes'),
(78, 'widget_rss', 'a:0:{}', 'yes'),
(79, 'uninstall_plugins', 'a:2:{s:59:\"intuitive-custom-post-order/intuitive-custom-post-order.php\";s:15:\"hicpo_uninstall\";s:24:\"wordpress-seo/wp-seo.php\";s:14:\"__return_false\";}', 'no'),
(80, 'timezone_string', '', 'yes'),
(81, 'page_for_posts', '0', 'yes'),
(82, 'page_on_front', '2', 'yes'),
(83, 'default_post_format', '0', 'yes'),
(84, 'link_manager_enabled', '0', 'yes'),
(85, 'finished_splitting_shared_terms', '1', 'yes'),
(86, 'site_icon', '0', 'yes'),
(87, 'medium_large_size_w', '768', 'yes'),
(88, 'medium_large_size_h', '0', 'yes'),
(89, 'wp_page_for_privacy_policy', '3', 'yes'),
(90, 'show_comments_cookies_opt_in', '1', 'yes'),
(91, 'admin_email_lifespan', '1728137666', 'yes'),
(92, 'disallowed_keys', '', 'no'),
(93, 'comment_previously_approved', '1', 'yes'),
(94, 'auto_plugin_theme_update_emails', 'a:0:{}', 'no'),
(95, 'auto_update_core_dev', 'enabled', 'yes'),
(96, 'auto_update_core_minor', 'enabled', 'yes'),
(97, 'auto_update_core_major', 'enabled', 'yes'),
(98, 'wp_force_deactivated_plugins', 'a:0:{}', 'yes'),
(99, 'initial_db_version', '55853', 'yes'),
(100, 'cp_user_roles', 'a:7:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:69:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;s:19:\"manage_postman_smtp\";b:1;s:19:\"manage_postman_logs\";b:1;s:20:\"wpseo_manage_options\";b:1;s:10:\"copy_posts\";b:1;s:27:\"hicpo_hicpo_load_script_css\";b:1;s:23:\"hicpo_update_menu_order\";b:1;s:28:\"hicpo_update_menu_order_tags\";b:1;s:29:\"hicpo_update_menu_order_sites\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:40:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:15:\"wpseo_bulk_edit\";b:1;s:28:\"wpseo_edit_advanced_metadata\";b:1;s:10:\"copy_posts\";b:1;s:27:\"hicpo_hicpo_load_script_css\";b:1;s:23:\"hicpo_update_menu_order\";b:1;s:28:\"hicpo_update_menu_order_tags\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}s:13:\"wpseo_manager\";a:2:{s:4:\"name\";s:11:\"SEO Manager\";s:12:\"capabilities\";a:39:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:15:\"wpseo_bulk_edit\";b:1;s:28:\"wpseo_edit_advanced_metadata\";b:1;s:20:\"wpseo_manage_options\";b:1;s:23:\"view_site_health_checks\";b:1;s:10:\"copy_posts\";b:1;}}s:12:\"wpseo_editor\";a:2:{s:4:\"name\";s:10:\"SEO Editor\";s:12:\"capabilities\";a:37:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:15:\"wpseo_bulk_edit\";b:1;s:28:\"wpseo_edit_advanced_metadata\";b:1;s:10:\"copy_posts\";b:1;}}}', 'yes'),
(101, 'fresh_site', '0', 'yes'),
(102, 'WPLANG', 'el', 'yes'),
(103, 'user_count', '3', 'no'),
(104, 'widget_block', 'a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:169:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Πρόσφατα άρθρα</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:241:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Πρόσφατα σχόλια</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Ιστορικό</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:159:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Kατηγορίες</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}', 'yes'),
(105, 'sidebars_widgets', 'a:2:{s:19:\"wp_inactive_widgets\";a:5:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";i:3;s:7:\"block-5\";i:4;s:7:\"block-6\";}s:13:\"array_version\";i:3;}', 'yes'),
(187, 'acf_version', '6.2.9', 'yes'),
(188, 'duplicate_post_show_notice', '0', 'no'),
(189, 'duplicate_post_copytitle', '1', 'yes'),
(190, 'duplicate_post_copydate', '0', 'yes'),
(191, 'duplicate_post_copystatus', '0', 'yes'),
(192, 'duplicate_post_copyslug', '0', 'yes'),
(193, 'duplicate_post_copyexcerpt', '1', 'yes'),
(194, 'duplicate_post_copycontent', '1', 'yes'),
(195, 'duplicate_post_copythumbnail', '1', 'yes'),
(196, 'duplicate_post_copytemplate', '1', 'yes'),
(197, 'duplicate_post_copyformat', '1', 'yes'),
(198, 'duplicate_post_copyauthor', '0', 'yes'),
(199, 'duplicate_post_copypassword', '0', 'yes'),
(200, 'duplicate_post_copyattachments', '0', 'yes'),
(201, 'duplicate_post_copychildren', '0', 'yes'),
(202, 'duplicate_post_copycomments', '0', 'yes'),
(203, 'duplicate_post_copymenuorder', '1', 'yes'),
(204, 'duplicate_post_taxonomies_blacklist', 'a:0:{}', 'yes'),
(205, 'duplicate_post_blacklist', '', 'yes'),
(206, 'duplicate_post_types_enabled', 'a:2:{i:0;s:4:\"post\";i:1;s:4:\"page\";}', 'yes'),
(207, 'duplicate_post_show_original_column', '0', 'yes'),
(208, 'duplicate_post_show_original_in_post_states', '0', 'yes'),
(209, 'duplicate_post_show_original_meta_box', '0', 'yes'),
(210, 'duplicate_post_show_link', 'a:3:{s:9:\"new_draft\";s:1:\"1\";s:5:\"clone\";s:1:\"1\";s:17:\"rewrite_republish\";s:1:\"1\";}', 'yes'),
(211, 'duplicate_post_show_link_in', 'a:4:{s:3:\"row\";s:1:\"1\";s:8:\"adminbar\";s:1:\"1\";s:9:\"submitbox\";s:1:\"1\";s:11:\"bulkactions\";s:1:\"1\";}', 'yes'),
(212, 'duplicate_post_version', '4.5', 'yes'),
(234, 'db_upgraded', '', 'yes'),
(229, 'pand-214af41078e3184f3bc399d28ea577f3', '1714487864', 'no'),
(233, 'wp_attachment_pages_enabled', '1', 'yes'),
(106, 'cron', 'a:10:{i:1714659500;a:2:{s:13:\"wpseo-reindex\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:31:\"wpseo_permalink_structure_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1714662866;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1714702466;a:3:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1714702508;a:1:{s:21:\"wp_update_user_counts\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1714731216;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1714745666;a:1:{s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1714745708;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1715004912;a:1:{s:30:\"wp_delete_temp_updater_backups\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}i:1715091266;a:1:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}', 'yes'),
(107, 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(108, 'widget_calendar', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(109, 'widget_archives', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(110, 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(111, 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(112, 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(113, 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(114, 'widget_meta', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(115, 'widget_search', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(116, 'widget_recent-posts', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(117, 'widget_recent-comments', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(118, 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(119, 'widget_nav_menu', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(120, 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'yes'),
(122, 'recovery_keys', 'a:0:{}', 'yes'),
(728, '_transient_timeout_acf_plugin_updates', '1714727012', 'no'),
(729, '_transient_acf_plugin_updates', 'a:5:{s:7:\"plugins\";a:0:{}s:9:\"no_update\";a:1:{s:34:\"advanced-custom-fields-pro/acf.php\";a:12:{s:4:\"slug\";s:26:\"advanced-custom-fields-pro\";s:6:\"plugin\";s:34:\"advanced-custom-fields-pro/acf.php\";s:11:\"new_version\";s:5:\"6.2.9\";s:3:\"url\";s:36:\"https://www.advancedcustomfields.com\";s:6:\"tested\";s:5:\"6.5.3\";s:7:\"package\";s:0:\"\";s:5:\"icons\";a:1:{s:7:\"default\";s:63:\"https://ps.w.org/advanced-custom-fields/assets/icon-256x256.png\";}s:7:\"banners\";a:2:{s:3:\"low\";s:77:\"https://ps.w.org/advanced-custom-fields/assets/banner-772x250.jpg?rev=1729102\";s:4:\"high\";s:78:\"https://ps.w.org/advanced-custom-fields/assets/banner-1544x500.jpg?rev=1729099\";}s:8:\"requires\";s:3:\"5.8\";s:12:\"requires_php\";s:3:\"7.0\";s:12:\"release_date\";s:8:\"20240408\";s:6:\"reason\";s:10:\"up_to_date\";}}s:10:\"expiration\";i:172800;s:6:\"status\";i:1;s:7:\"checked\";a:1:{s:34:\"advanced-custom-fields-pro/acf.php\";s:5:\"6.2.9\";}}', 'no'),
(636, 'options_enable_test_environment', '1', 'no'),
(637, '_options_enable_test_environment', 'field_662f92bd81406', 'no'),
(638, 'options_payment_mid', '90003137', 'no'),
(639, '_options_payment_mid', 'field_662f92e681407', 'no'),
(640, 'options_payment_secret', 'Cardlink1', 'no'),
(641, '_options_payment_secret', 'field_662f92ef81408', 'no'),
(642, 'options_thank_you_email_subject', '', 'no'),
(643, '_options_thank_you_email_subject', 'field_662fac3d5e023', 'no'),
(644, 'options_thank_you_email_content', '', 'no'),
(645, '_options_thank_you_email_content', 'field_662fac775e024', 'no'),
(652, 'options_checkout_page_url', '20', 'no'),
(653, '_options_checkout_page_url', 'field_6630cf2a7af52', 'no'),
(663, 'options_success_title', 'Ευχαριστούμε για τη δωρεά!', 'no'),
(664, '_options_success_title', 'field_6630d65ffcd40', 'no'),
(665, 'options_success_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.', 'no'),
(666, '_options_success_content', 'field_6630d669fcd41', 'no'),
(667, 'options_success_image', '86', 'no'),
(668, '_options_success_image', 'field_6630d674fcd42', 'no'),
(669, 'options_success', '', 'no'),
(670, '_options_success', 'field_6630d64cfcd3f', 'no'),
(671, 'options_fail_title', 'Η πληρωμή απέτυχε', 'no'),
(672, '_options_fail_title', 'field_6630d68ffcd44', 'no'),
(673, 'options_fail_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.', 'no'),
(674, '_options_fail_content', 'field_6630d68ffcd45', 'no'),
(675, 'options_fail_image', '', 'no'),
(676, '_options_fail_image', 'field_6630d68ffcd46', 'no'),
(677, 'options_fail', '', 'no'),
(678, '_options_fail', 'field_6630d68ffcd43', 'no'),
(679, 'options_payment_success_messages_title', 'Ευχαριστούμε για τη δωρεά!', 'no'),
(680, '_options_payment_success_messages_title', 'field_6630d65ffcd40', 'no'),
(681, 'options_payment_success_messages_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.', 'no'),
(682, '_options_payment_success_messages_content', 'field_6630d669fcd41', 'no'),
(683, 'options_payment_success_messages_image', '86', 'no'),
(684, '_options_payment_success_messages_image', 'field_6630d674fcd42', 'no'),
(685, 'options_payment_success_messages', '', 'no'),
(686, '_options_payment_success_messages', 'field_6630d64cfcd3f', 'no'),
(687, 'options_payment_failed_messages_title', 'Η πληρωμή απέτυχε', 'no'),
(688, '_options_payment_failed_messages_title', 'field_6630d68ffcd44', 'no'),
(689, 'options_payment_failed_messages_content', 'Παρακαλούμε δοκιμάστε ξανά.', 'no'),
(690, '_options_payment_failed_messages_content', 'field_6630d68ffcd45', 'no'),
(691, 'options_payment_failed_messages_image', '', 'no'),
(692, '_options_payment_failed_messages_image', 'field_6630d68ffcd46', 'no'),
(693, 'options_payment_failed_messages', '', 'no'),
(694, '_options_payment_failed_messages', 'field_6630d68ffcd43', 'no'),
(834, '_transient_timeout_wpseo_total_unindexed_posts_limited', '1714660179', 'no'),
(835, '_transient_wpseo_total_unindexed_posts_limited', '0', 'no'),
(836, '_transient_timeout_wpseo_total_unindexed_terms_limited', '1714660179', 'no'),
(837, '_transient_wpseo_total_unindexed_terms_limited', '0', 'no'),
(577, 'cptui_new_install', 'false', 'yes'),
(578, 'cptui_post_types', 'a:1:{s:8:\"payments\";a:34:{s:4:\"name\";s:8:\"payments\";s:5:\"label\";s:8:\"Payments\";s:14:\"singular_label\";s:7:\"Payment\";s:11:\"description\";s:0:\"\";s:6:\"public\";s:4:\"true\";s:18:\"publicly_queryable\";s:4:\"true\";s:7:\"show_ui\";s:4:\"true\";s:17:\"show_in_nav_menus\";s:5:\"false\";s:16:\"delete_with_user\";s:5:\"false\";s:12:\"show_in_rest\";s:5:\"false\";s:9:\"rest_base\";s:0:\"\";s:21:\"rest_controller_class\";s:0:\"\";s:14:\"rest_namespace\";s:0:\"\";s:11:\"has_archive\";s:5:\"false\";s:18:\"has_archive_string\";s:0:\"\";s:19:\"exclude_from_search\";s:4:\"true\";s:15:\"capability_type\";s:4:\"post\";s:12:\"hierarchical\";s:5:\"false\";s:10:\"can_export\";s:5:\"false\";s:7:\"rewrite\";s:4:\"true\";s:12:\"rewrite_slug\";s:0:\"\";s:17:\"rewrite_withfront\";s:4:\"true\";s:9:\"query_var\";s:4:\"true\";s:14:\"query_var_slug\";s:0:\"\";s:13:\"menu_position\";s:0:\"\";s:12:\"show_in_menu\";s:4:\"true\";s:19:\"show_in_menu_string\";s:0:\"\";s:9:\"menu_icon\";s:18:\"dashicons-feedback\";s:20:\"register_meta_box_cb\";N;s:8:\"supports\";a:1:{i:0;s:5:\"title\";}s:10:\"taxonomies\";a:0:{}s:6:\"labels\";a:30:{s:9:\"menu_name\";s:0:\"\";s:9:\"all_items\";s:0:\"\";s:7:\"add_new\";s:0:\"\";s:12:\"add_new_item\";s:0:\"\";s:9:\"edit_item\";s:0:\"\";s:8:\"new_item\";s:0:\"\";s:9:\"view_item\";s:0:\"\";s:10:\"view_items\";s:0:\"\";s:12:\"search_items\";s:0:\"\";s:9:\"not_found\";s:0:\"\";s:18:\"not_found_in_trash\";s:0:\"\";s:17:\"parent_item_colon\";s:0:\"\";s:14:\"featured_image\";s:0:\"\";s:18:\"set_featured_image\";s:0:\"\";s:21:\"remove_featured_image\";s:0:\"\";s:18:\"use_featured_image\";s:0:\"\";s:8:\"archives\";s:0:\"\";s:16:\"insert_into_item\";s:0:\"\";s:21:\"uploaded_to_this_item\";s:0:\"\";s:17:\"filter_items_list\";s:0:\"\";s:21:\"items_list_navigation\";s:0:\"\";s:10:\"items_list\";s:0:\"\";s:10:\"attributes\";s:0:\"\";s:14:\"name_admin_bar\";s:0:\"\";s:14:\"item_published\";s:0:\"\";s:24:\"item_published_privately\";s:0:\"\";s:22:\"item_reverted_to_draft\";s:0:\"\";s:12:\"item_trashed\";s:0:\"\";s:14:\"item_scheduled\";s:0:\"\";s:12:\"item_updated\";s:0:\"\";}s:15:\"custom_supports\";s:0:\"\";s:16:\"enter_title_here\";s:0:\"\";}}', 'yes'),
(545, 'jwt_auth_admin_notice', '1', 'yes'),
(125, 'https_detection_errors', 'a:1:{s:20:\"https_request_failed\";a:1:{i:0;s:39:\"Το αίτημα HTTPS απέτυχε.\";}}', 'yes'),
(805, '_transient_timeout_wpseo_unindexed_term_link_count', '1714727635', 'no'),
(806, '_transient_wpseo_unindexed_term_link_count', '0', 'no'),
(491, '_site_transient_update_core', 'O:8:\"stdClass\":4:{s:7:\"updates\";a:1:{i:0;O:8:\"stdClass\":10:{s:8:\"response\";s:6:\"latest\";s:8:\"download\";s:62:\"https://downloads.wordpress.org/release/el/wordpress-6.5.2.zip\";s:6:\"locale\";s:2:\"el\";s:8:\"packages\";O:8:\"stdClass\":5:{s:4:\"full\";s:62:\"https://downloads.wordpress.org/release/el/wordpress-6.5.2.zip\";s:10:\"no_content\";s:0:\"\";s:11:\"new_bundled\";s:0:\"\";s:7:\"partial\";s:0:\"\";s:8:\"rollback\";s:0:\"\";}s:7:\"current\";s:5:\"6.5.2\";s:7:\"version\";s:5:\"6.5.2\";s:11:\"php_version\";s:5:\"7.0.0\";s:13:\"mysql_version\";s:3:\"5.0\";s:11:\"new_bundled\";s:3:\"6.4\";s:15:\"partial_version\";s:0:\"\";}}s:12:\"last_checked\";i:1714659276;s:15:\"version_checked\";s:5:\"6.5.2\";s:12:\"translations\";a:0:{}}', 'no'),
(831, '_site_transient_timeout_theme_roots', '1714661077', 'no'),
(832, '_site_transient_theme_roots', 'a:1:{s:11:\"choose-life\";s:7:\"/themes\";}', 'no'),
(803, '_transient_timeout_wpseo_unindexed_post_link_count', '1714727635', 'no'),
(804, '_transient_wpseo_unindexed_post_link_count', '0', 'no'),
(801, '_transient_timeout_wpseo_total_unindexed_general_items', '1714727635', 'no'),
(802, '_transient_wpseo_total_unindexed_general_items', '0', 'no'),
(282, 'acf_pro_license', '', 'no'),
(286, '_site_transient_wp_plugin_dependencies_plugin_data', 'a:0:{}', 'no'),
(171, 'disable_comment_version', '2.4.6', 'yes'),
(172, 'hicpo_ver', '3.1.5', 'yes'),
(173, 'fs_active_plugins', 'O:8:\"stdClass\":3:{s:7:\"plugins\";a:1:{s:18:\"post-smtp/freemius\";O:8:\"stdClass\":4:{s:7:\"version\";s:6:\"2.5.10\";s:4:\"type\";s:6:\"plugin\";s:9:\"timestamp\";i:1712585899;s:11:\"plugin_path\";s:26:\"post-smtp/postman-smtp.php\";}}s:7:\"abspath\";s:26:\"C:\\wamp64\\www\\choose-life/\";s:6:\"newest\";O:8:\"stdClass\":5:{s:11:\"plugin_path\";s:26:\"post-smtp/postman-smtp.php\";s:8:\"sdk_path\";s:18:\"post-smtp/freemius\";s:7:\"version\";s:6:\"2.5.10\";s:13:\"in_activation\";b:0;s:9:\"timestamp\";i:1712585899;}}', 'yes'),
(174, 'fs_debug_mode', '', 'yes'),
(175, 'fs_accounts', 'a:6:{s:21:\"id_slug_type_path_map\";a:1:{i:10461;a:3:{s:4:\"slug\";s:9:\"post-smtp\";s:4:\"type\";s:6:\"plugin\";s:4:\"path\";s:26:\"post-smtp/postman-smtp.php\";}}s:11:\"plugin_data\";a:1:{s:9:\"post-smtp\";a:17:{s:16:\"plugin_main_file\";O:8:\"stdClass\":1:{s:4:\"path\";s:26:\"post-smtp/postman-smtp.php\";}s:20:\"is_network_activated\";b:0;s:17:\"install_timestamp\";i:1712585899;s:17:\"was_plugin_loaded\";b:1;s:21:\"is_plugin_new_install\";b:0;s:16:\"sdk_last_version\";N;s:11:\"sdk_version\";s:6:\"2.5.10\";s:16:\"sdk_upgrade_mode\";b:1;s:18:\"sdk_downgrade_mode\";b:0;s:19:\"plugin_last_version\";s:5:\"2.9.0\";s:14:\"plugin_version\";s:5:\"2.9.1\";s:19:\"plugin_upgrade_mode\";b:1;s:21:\"plugin_downgrade_mode\";b:0;s:17:\"connectivity_test\";a:6:{s:12:\"is_connected\";N;s:4:\"host\";s:9:\"localhost\";s:9:\"server_ip\";s:3:\"::1\";s:9:\"is_active\";b:1;s:9:\"timestamp\";i:1712585899;s:7:\"version\";s:6:\"2.8.13\";}s:15:\"prev_is_premium\";b:0;s:18:\"sticky_optin_added\";b:1;s:12:\"is_anonymous\";a:3:{s:2:\"is\";b:1;s:9:\"timestamp\";i:1713985923;s:7:\"version\";s:5:\"2.9.0\";}}}s:13:\"file_slug_map\";a:1:{s:26:\"post-smtp/postman-smtp.php\";s:9:\"post-smtp\";}s:7:\"plugins\";a:1:{s:9:\"post-smtp\";O:9:\"FS_Plugin\":24:{s:2:\"id\";s:5:\"10461\";s:7:\"updated\";N;s:7:\"created\";N;s:22:\"\0FS_Entity\0_is_updated\";b:1;s:10:\"public_key\";s:32:\"pk_28fcefa3d0ae86f8cdf6b7f71c0cc\";s:10:\"secret_key\";N;s:16:\"parent_plugin_id\";N;s:5:\"title\";s:9:\"Post SMTP\";s:4:\"slug\";s:9:\"post-smtp\";s:12:\"premium_slug\";s:17:\"post-smtp-premium\";s:4:\"type\";s:6:\"plugin\";s:20:\"affiliate_moderation\";b:0;s:19:\"is_wp_org_compliant\";b:1;s:22:\"premium_releases_count\";N;s:4:\"file\";s:26:\"post-smtp/postman-smtp.php\";s:7:\"version\";s:5:\"2.9.1\";s:11:\"auto_update\";N;s:4:\"info\";N;s:10:\"is_premium\";b:0;s:14:\"premium_suffix\";s:9:\"(Premium)\";s:7:\"is_live\";b:1;s:9:\"bundle_id\";s:5:\"10910\";s:17:\"bundle_public_key\";s:32:\"pk_c5110ef04ba30cd57dd970a269a1a\";s:17:\"opt_in_moderation\";N;}}s:13:\"admin_notices\";a:1:{s:9:\"post-smtp\";a:0:{}}s:9:\"unique_id\";s:32:\"33e53d1313e9ec4af1048c3c12a95713\";}', 'yes'),
(176, 'postman_db_version', '1.0.1', 'yes'),
(177, 'postman_options', 'a:1:{s:21:\"fallback_smtp_enabled\";s:2:\"no\";}', 'yes'),
(178, 'postman_state', 'a:4:{s:12:\"install_date\";i:1712585899;s:7:\"version\";s:5:\"2.9.1\";s:15:\"locking_enabled\";b:0;s:19:\"delivery_fail_total\";i:8;}', 'yes'),
(179, 'fs_api_cache', 'a:0:{}', 'no'),
(182, 'yoast_migrations_free', 'a:1:{s:7:\"version\";s:4:\"22.5\";}', 'yes'),
(183, 'wpseo', 'a:110:{s:8:\"tracking\";b:0;s:16:\"toggled_tracking\";b:0;s:22:\"license_server_version\";b:0;s:15:\"ms_defaults_set\";b:0;s:40:\"ignore_search_engines_discouraged_notice\";b:0;s:19:\"indexing_first_time\";b:1;s:16:\"indexing_started\";b:0;s:15:\"indexing_reason\";s:21:\"post_type_made_public\";s:29:\"indexables_indexing_completed\";b:1;s:13:\"index_now_key\";s:0:\"\";s:7:\"version\";s:4:\"22.5\";s:16:\"previous_version\";s:4:\"22.4\";s:20:\"disableadvanced_meta\";b:1;s:30:\"enable_headless_rest_endpoints\";b:1;s:17:\"ryte_indexability\";b:0;s:11:\"baiduverify\";s:0:\"\";s:12:\"googleverify\";s:0:\"\";s:8:\"msverify\";s:0:\"\";s:12:\"yandexverify\";s:0:\"\";s:9:\"site_type\";s:0:\"\";s:20:\"has_multiple_authors\";s:0:\"\";s:16:\"environment_type\";s:0:\"\";s:23:\"content_analysis_active\";b:1;s:23:\"keyword_analysis_active\";b:1;s:34:\"inclusive_language_analysis_active\";b:0;s:21:\"enable_admin_bar_menu\";b:1;s:26:\"enable_cornerstone_content\";b:1;s:18:\"enable_xml_sitemap\";b:1;s:24:\"enable_text_link_counter\";b:1;s:16:\"enable_index_now\";b:1;s:19:\"enable_ai_generator\";b:1;s:22:\"ai_enabled_pre_default\";b:0;s:22:\"show_onboarding_notice\";b:1;s:18:\"first_activated_on\";i:1712585900;s:13:\"myyoast-oauth\";b:0;s:26:\"semrush_integration_active\";b:1;s:14:\"semrush_tokens\";a:0:{}s:20:\"semrush_country_code\";s:2:\"us\";s:19:\"permalink_structure\";s:36:\"/%year%/%monthnum%/%day%/%postname%/\";s:8:\"home_url\";s:28:\"http://localhost/choose-life\";s:18:\"dynamic_permalinks\";b:0;s:17:\"category_base_url\";s:0:\"\";s:12:\"tag_base_url\";s:0:\"\";s:21:\"custom_taxonomy_slugs\";a:0:{}s:29:\"enable_enhanced_slack_sharing\";b:1;s:25:\"zapier_integration_active\";b:0;s:19:\"zapier_subscription\";a:0:{}s:14:\"zapier_api_key\";s:0:\"\";s:23:\"enable_metabox_insights\";b:1;s:23:\"enable_link_suggestions\";b:1;s:26:\"algolia_integration_active\";b:0;s:14:\"import_cursors\";a:0:{}s:13:\"workouts_data\";a:1:{s:13:\"configuration\";a:1:{s:13:\"finishedSteps\";a:0:{}}}s:28:\"configuration_finished_steps\";a:0:{}s:36:\"dismiss_configuration_workout_notice\";b:1;s:34:\"dismiss_premium_deactivated_notice\";b:0;s:19:\"importing_completed\";a:0:{}s:26:\"wincher_integration_active\";b:1;s:14:\"wincher_tokens\";a:0:{}s:36:\"wincher_automatically_add_keyphrases\";b:0;s:18:\"wincher_website_id\";s:0:\"\";s:28:\"wordproof_integration_active\";b:0;s:29:\"wordproof_integration_changed\";b:0;s:18:\"first_time_install\";b:1;s:34:\"should_redirect_after_install_free\";b:0;s:34:\"activation_redirect_timestamp_free\";i:1712585902;s:18:\"remove_feed_global\";b:0;s:27:\"remove_feed_global_comments\";b:0;s:25:\"remove_feed_post_comments\";b:0;s:19:\"remove_feed_authors\";b:0;s:22:\"remove_feed_categories\";b:0;s:16:\"remove_feed_tags\";b:0;s:29:\"remove_feed_custom_taxonomies\";b:0;s:22:\"remove_feed_post_types\";b:0;s:18:\"remove_feed_search\";b:0;s:21:\"remove_atom_rdf_feeds\";b:0;s:17:\"remove_shortlinks\";b:0;s:21:\"remove_rest_api_links\";b:0;s:20:\"remove_rsd_wlw_links\";b:0;s:19:\"remove_oembed_links\";b:0;s:16:\"remove_generator\";b:0;s:20:\"remove_emoji_scripts\";b:0;s:24:\"remove_powered_by_header\";b:0;s:22:\"remove_pingback_header\";b:0;s:28:\"clean_campaign_tracking_urls\";b:0;s:16:\"clean_permalinks\";b:0;s:32:\"clean_permalinks_extra_variables\";s:0:\"\";s:14:\"search_cleanup\";b:0;s:20:\"search_cleanup_emoji\";b:0;s:23:\"search_cleanup_patterns\";b:0;s:22:\"search_character_limit\";i:50;s:20:\"deny_search_crawling\";b:0;s:21:\"deny_wp_json_crawling\";b:0;s:20:\"deny_adsbot_crawling\";b:0;s:19:\"deny_ccbot_crawling\";b:0;s:29:\"deny_google_extended_crawling\";b:0;s:20:\"deny_gptbot_crawling\";b:0;s:27:\"redirect_search_pretty_urls\";b:0;s:29:\"least_readability_ignore_list\";a:0:{}s:27:\"least_seo_score_ignore_list\";a:0:{}s:23:\"most_linked_ignore_list\";a:0:{}s:24:\"least_linked_ignore_list\";a:0:{}s:28:\"indexables_page_reading_list\";a:5:{i:0;b:0;i:1;b:0;i:2;b:0;i:3;b:0;i:4;b:0;}s:25:\"indexables_overview_state\";s:21:\"dashboard-not-visited\";s:28:\"last_known_public_post_types\";a:2:{i:0;s:4:\"post\";i:1;s:4:\"page\";}s:28:\"last_known_public_taxonomies\";a:3:{i:0;s:8:\"category\";i:1;s:8:\"post_tag\";i:2;s:11:\"post_format\";}s:23:\"last_known_no_unindexed\";a:6:{s:40:\"wpseo_total_unindexed_post_type_archives\";i:1714641235;s:31:\"wpseo_unindexed_post_link_count\";i:1714641235;s:31:\"wpseo_unindexed_term_link_count\";i:1714641235;s:35:\"wpseo_total_unindexed_general_items\";i:1714641235;s:27:\"wpseo_total_unindexed_posts\";i:1712842298;s:27:\"wpseo_total_unindexed_terms\";i:1712842298;}s:14:\"new_post_types\";a:0:{}s:14:\"new_taxonomies\";a:0:{}s:34:\"show_new_content_type_notification\";b:0;}', 'yes');
INSERT INTO `cp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(184, 'wpseo_titles', 'a:129:{s:17:\"forcerewritetitle\";b:0;s:9:\"separator\";s:7:\"sc-dash\";s:16:\"title-home-wpseo\";s:42:\"%%sitename%% %%page%% %%sep%% %%sitedesc%%\";s:18:\"title-author-wpseo\";s:55:\"%%name%%, Συντάκης στο %%sitename%% %%page%%\";s:19:\"title-archive-wpseo\";s:38:\"%%date%% %%page%% %%sep%% %%sitename%%\";s:18:\"title-search-wpseo\";s:69:\"Αναζητήσατε %%searchphrase%% %%page%% %%sep%% %%sitename%%\";s:15:\"title-404-wpseo\";s:58:\"Η σελίδα δεν βρέθηκε %%sep%% %%sitename%%\";s:25:\"social-title-author-wpseo\";s:8:\"%%name%%\";s:26:\"social-title-archive-wpseo\";s:8:\"%%date%%\";s:31:\"social-description-author-wpseo\";s:0:\"\";s:32:\"social-description-archive-wpseo\";s:0:\"\";s:29:\"social-image-url-author-wpseo\";s:0:\"\";s:30:\"social-image-url-archive-wpseo\";s:0:\"\";s:28:\"social-image-id-author-wpseo\";i:0;s:29:\"social-image-id-archive-wpseo\";i:0;s:19:\"metadesc-home-wpseo\";s:0:\"\";s:21:\"metadesc-author-wpseo\";s:0:\"\";s:22:\"metadesc-archive-wpseo\";s:0:\"\";s:9:\"rssbefore\";s:0:\"\";s:8:\"rssafter\";s:83:\"Το άρθρο %%POSTLINK%% εμφανίστηκε πρώτα στο %%BLOGLINK%%.\";s:20:\"noindex-author-wpseo\";b:0;s:28:\"noindex-author-noposts-wpseo\";b:1;s:21:\"noindex-archive-wpseo\";b:1;s:14:\"disable-author\";b:0;s:12:\"disable-date\";b:0;s:19:\"disable-post_format\";b:0;s:18:\"disable-attachment\";b:1;s:20:\"breadcrumbs-404crumb\";s:55:\"Σφάλμα 404: Δεν βρέθηκε η σελίδα\";s:29:\"breadcrumbs-display-blog-page\";b:1;s:20:\"breadcrumbs-boldlast\";b:0;s:25:\"breadcrumbs-archiveprefix\";s:19:\"Αρχεία για\";s:18:\"breadcrumbs-enable\";b:1;s:16:\"breadcrumbs-home\";s:12:\"Αρχική\";s:18:\"breadcrumbs-prefix\";s:0:\"\";s:24:\"breadcrumbs-searchprefix\";s:29:\"Αναζητήσατε για\";s:15:\"breadcrumbs-sep\";s:2:\"»\";s:12:\"website_name\";s:0:\"\";s:11:\"person_name\";s:0:\"\";s:11:\"person_logo\";s:0:\"\";s:22:\"alternate_website_name\";s:0:\"\";s:12:\"company_logo\";s:0:\"\";s:12:\"company_name\";s:0:\"\";s:22:\"company_alternate_name\";s:0:\"\";s:17:\"company_or_person\";s:7:\"company\";s:25:\"company_or_person_user_id\";b:0;s:17:\"stripcategorybase\";b:0;s:26:\"open_graph_frontpage_title\";s:12:\"%%sitename%%\";s:25:\"open_graph_frontpage_desc\";s:0:\"\";s:26:\"open_graph_frontpage_image\";s:0:\"\";s:24:\"publishing_principles_id\";i:0;s:25:\"ownership_funding_info_id\";i:0;s:29:\"actionable_feedback_policy_id\";i:0;s:21:\"corrections_policy_id\";i:0;s:16:\"ethics_policy_id\";i:0;s:19:\"diversity_policy_id\";i:0;s:28:\"diversity_staffing_report_id\";i:0;s:15:\"org-description\";s:0:\"\";s:9:\"org-email\";s:0:\"\";s:9:\"org-phone\";s:0:\"\";s:14:\"org-legal-name\";s:0:\"\";s:17:\"org-founding-date\";s:0:\"\";s:20:\"org-number-employees\";s:0:\"\";s:10:\"org-vat-id\";s:0:\"\";s:10:\"org-tax-id\";s:0:\"\";s:7:\"org-iso\";s:0:\"\";s:8:\"org-duns\";s:0:\"\";s:11:\"org-leicode\";s:0:\"\";s:9:\"org-naics\";s:0:\"\";s:10:\"title-post\";s:39:\"%%title%% %%page%% %%sep%% %%sitename%%\";s:13:\"metadesc-post\";s:0:\"\";s:12:\"noindex-post\";b:0;s:23:\"display-metabox-pt-post\";b:1;s:23:\"post_types-post-maintax\";i:0;s:21:\"schema-page-type-post\";s:7:\"WebPage\";s:24:\"schema-article-type-post\";s:7:\"Article\";s:17:\"social-title-post\";s:9:\"%%title%%\";s:23:\"social-description-post\";s:0:\"\";s:21:\"social-image-url-post\";s:0:\"\";s:20:\"social-image-id-post\";i:0;s:10:\"title-page\";s:39:\"%%title%% %%page%% %%sep%% %%sitename%%\";s:13:\"metadesc-page\";s:0:\"\";s:12:\"noindex-page\";b:0;s:23:\"display-metabox-pt-page\";b:1;s:23:\"post_types-page-maintax\";i:0;s:21:\"schema-page-type-page\";s:7:\"WebPage\";s:24:\"schema-article-type-page\";s:4:\"None\";s:17:\"social-title-page\";s:9:\"%%title%%\";s:23:\"social-description-page\";s:0:\"\";s:21:\"social-image-url-page\";s:0:\"\";s:20:\"social-image-id-page\";i:0;s:16:\"title-attachment\";s:39:\"%%title%% %%page%% %%sep%% %%sitename%%\";s:19:\"metadesc-attachment\";s:0:\"\";s:18:\"noindex-attachment\";b:0;s:29:\"display-metabox-pt-attachment\";b:1;s:29:\"post_types-attachment-maintax\";i:0;s:27:\"schema-page-type-attachment\";s:7:\"WebPage\";s:30:\"schema-article-type-attachment\";s:4:\"None\";s:18:\"title-tax-category\";s:57:\"%%term_title%% Αρχεία %%page%% %%sep%% %%sitename%%\";s:21:\"metadesc-tax-category\";s:0:\"\";s:28:\"display-metabox-tax-category\";b:1;s:20:\"noindex-tax-category\";b:0;s:25:\"social-title-tax-category\";s:27:\"%%term_title%% Αρχεία\";s:31:\"social-description-tax-category\";s:0:\"\";s:29:\"social-image-url-tax-category\";s:0:\"\";s:28:\"social-image-id-tax-category\";i:0;s:26:\"taxonomy-category-ptparent\";i:0;s:18:\"title-tax-post_tag\";s:57:\"%%term_title%% Αρχεία %%page%% %%sep%% %%sitename%%\";s:21:\"metadesc-tax-post_tag\";s:0:\"\";s:28:\"display-metabox-tax-post_tag\";b:1;s:20:\"noindex-tax-post_tag\";b:0;s:25:\"social-title-tax-post_tag\";s:27:\"%%term_title%% Αρχεία\";s:31:\"social-description-tax-post_tag\";s:0:\"\";s:29:\"social-image-url-tax-post_tag\";s:0:\"\";s:28:\"social-image-id-tax-post_tag\";i:0;s:26:\"taxonomy-post_tag-ptparent\";i:0;s:21:\"title-tax-post_format\";s:57:\"%%term_title%% Αρχεία %%page%% %%sep%% %%sitename%%\";s:24:\"metadesc-tax-post_format\";s:0:\"\";s:31:\"display-metabox-tax-post_format\";b:1;s:23:\"noindex-tax-post_format\";b:1;s:28:\"social-title-tax-post_format\";s:27:\"%%term_title%% Αρχεία\";s:34:\"social-description-tax-post_format\";s:0:\"\";s:32:\"social-image-url-tax-post_format\";s:0:\"\";s:31:\"social-image-id-tax-post_format\";i:0;s:29:\"taxonomy-post_format-ptparent\";i:0;s:14:\"person_logo_id\";i:0;s:15:\"company_logo_id\";i:0;s:17:\"company_logo_meta\";b:0;s:16:\"person_logo_meta\";b:0;s:29:\"open_graph_frontpage_image_id\";i:0;}', 'yes'),
(276, '_transient_health-check-site-status-result', '{\"good\":15,\"recommended\":7,\"critical\":1}', 'yes'),
(153, 'recently_activated', 'a:2:{s:43:\"custom-post-type-ui/custom-post-type-ui.php\";i:1714397953;s:47:\"jwt-authentication-for-wp-rest-api/jwt-auth.php\";i:1714375283;}', 'yes'),
(164, 'finished_updating_comment_type', '1', 'yes'),
(170, 'disable_comments_options', 'a:8:{s:16:\"is_network_admin\";b:0;s:17:\"remove_everywhere\";b:1;s:19:\"disabled_post_types\";a:3:{i:0;s:4:\"post\";i:1;s:4:\"page\";i:2;s:10:\"attachment\";}s:22:\"enable_exclude_by_role\";s:1:\"0\";s:22:\"remove_xmlrpc_comments\";i:1;s:24:\"remove_rest_API_comments\";i:1;s:10:\"db_version\";i:7;s:14:\"settings_saved\";b:1;}', 'yes'),
(478, '_site_transient_timeout_php_check_ce267f3653936506950ae9448202043a', '1714664955', 'no'),
(479, '_site_transient_php_check_ce267f3653936506950ae9448202043a', 'a:5:{s:19:\"recommended_version\";s:3:\"7.4\";s:15:\"minimum_version\";s:3:\"7.0\";s:12:\"is_supported\";b:1;s:9:\"is_secure\";b:1;s:13:\"is_acceptable\";b:1;}', 'no'),
(799, '_transient_timeout_wpseo_total_unindexed_post_type_archives', '1714727635', 'no'),
(800, '_transient_wpseo_total_unindexed_post_type_archives', '0', 'no'),
(627, 'recovery_mode_email_last_sent', '1714469496', 'yes'),
(480, 'category_children', 'a:0:{}', 'yes'),
(481, 'options_my_account_url', '18', 'no'),
(482, '_options_my_account_url', 'field_662a7baa72a4f', 'no'),
(169, 'wpcf7', 'a:2:{s:7:\"version\";s:5:\"5.9.3\";s:13:\"bulk_validate\";a:4:{s:9:\"timestamp\";i:1712585898;s:7:\"version\";s:5:\"5.9.3\";s:11:\"count_valid\";i:1;s:13:\"count_invalid\";i:0;}}', 'yes'),
(185, 'wpseo_social', 'a:20:{s:13:\"facebook_site\";s:0:\"\";s:13:\"instagram_url\";s:0:\"\";s:12:\"linkedin_url\";s:0:\"\";s:11:\"myspace_url\";s:0:\"\";s:16:\"og_default_image\";s:0:\"\";s:19:\"og_default_image_id\";s:0:\"\";s:18:\"og_frontpage_title\";s:0:\"\";s:17:\"og_frontpage_desc\";s:0:\"\";s:18:\"og_frontpage_image\";s:0:\"\";s:21:\"og_frontpage_image_id\";s:0:\"\";s:9:\"opengraph\";b:1;s:13:\"pinterest_url\";s:0:\"\";s:15:\"pinterestverify\";s:0:\"\";s:7:\"twitter\";b:1;s:12:\"twitter_site\";s:0:\"\";s:17:\"twitter_card_type\";s:19:\"summary_large_image\";s:11:\"youtube_url\";s:0:\"\";s:13:\"wikipedia_url\";s:0:\"\";s:17:\"other_social_urls\";a:0:{}s:12:\"mastodon_url\";s:0:\"\";}', 'yes'),
(597, 'postman_release_version', '1', 'yes'),
(301, 'wpins_block_notice', 'a:1:{s:16:\"disable-comments\";s:16:\"disable-comments\";}', 'yes'),
(495, '_site_transient_timeout_browser_a16ddaab909d2cf27fce353f26dd2ff2', '1714671771', 'no'),
(496, '_site_transient_browser_a16ddaab909d2cf27fce353f26dd2ff2', 'a:10:{s:4:\"name\";s:6:\"Chrome\";s:7:\"version\";s:9:\"124.0.0.0\";s:8:\"platform\";s:7:\"Windows\";s:10:\"update_url\";s:29:\"https://www.google.com/chrome\";s:7:\"img_src\";s:43:\"http://s.w.org/images/browsers/chrome.png?1\";s:11:\"img_src_ssl\";s:44:\"https://s.w.org/images/browsers/chrome.png?1\";s:15:\"current_version\";s:2:\"18\";s:7:\"upgrade\";b:0;s:8:\"insecure\";b:0;s:6:\"mobile\";b:0;}', 'no'),
(237, 'can_compress_scripts', '1', 'yes'),
(295, 'theme_mods_twentytwentythree', 'a:1:{s:16:\"sidebars_widgets\";a:2:{s:4:\"time\";i:1712842030;s:4:\"data\";a:3:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}}}}', 'no'),
(296, 'current_theme', 'Choose life', 'yes'),
(297, 'theme_mods_choose-life', 'a:3:{i:0;b:0;s:18:\"nav_menu_locations\";a:2:{s:11:\"footer-menu\";i:2;s:9:\"main-menu\";i:3;}s:18:\"custom_css_post_id\";i:-1;}', 'yes'),
(298, 'theme_switched', '', 'yes'),
(345, 'nav_menu_options', 'a:2:{i:0;b:0;s:8:\"auto_add\";a:0:{}}', 'yes'),
(493, '_site_transient_update_themes', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1714659278;s:7:\"checked\";a:1:{s:11:\"choose-life\";s:5:\"1.0.0\";}s:8:\"response\";a:0:{}s:9:\"no_update\";a:0:{}s:12:\"translations\";a:0:{}}', 'no'),
(833, '_site_transient_update_plugins', 'O:8:\"stdClass\":5:{s:12:\"last_checked\";i:1714659279;s:8:\"response\";a:1:{s:24:\"wordpress-seo/wp-seo.php\";O:8:\"stdClass\":13:{s:2:\"id\";s:27:\"w.org/plugins/wordpress-seo\";s:4:\"slug\";s:13:\"wordpress-seo\";s:6:\"plugin\";s:24:\"wordpress-seo/wp-seo.php\";s:11:\"new_version\";s:4:\"22.6\";s:3:\"url\";s:44:\"https://wordpress.org/plugins/wordpress-seo/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/plugin/wordpress-seo.22.6.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:58:\"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=2363699\";s:3:\"svg\";s:58:\"https://ps.w.org/wordpress-seo/assets/icon.svg?rev=2363699\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/wordpress-seo/assets/banner-1544x500.png?rev=2643727\";s:2:\"1x\";s:68:\"https://ps.w.org/wordpress-seo/assets/banner-772x250.png?rev=2643727\";}s:11:\"banners_rtl\";a:2:{s:2:\"2x\";s:73:\"https://ps.w.org/wordpress-seo/assets/banner-1544x500-rtl.png?rev=2643727\";s:2:\"1x\";s:72:\"https://ps.w.org/wordpress-seo/assets/banner-772x250-rtl.png?rev=2643727\";}s:8:\"requires\";s:3:\"6.3\";s:6:\"tested\";s:5:\"6.5.2\";s:12:\"requires_php\";s:5:\"7.2.5\";s:16:\"requires_plugins\";a:0:{}}}s:12:\"translations\";a:0:{}s:9:\"no_update\";a:11:{s:33:\"classic-editor/classic-editor.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:28:\"w.org/plugins/classic-editor\";s:4:\"slug\";s:14:\"classic-editor\";s:6:\"plugin\";s:33:\"classic-editor/classic-editor.php\";s:11:\"new_version\";s:5:\"1.6.3\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/classic-editor/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/classic-editor.1.6.3.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/classic-editor/assets/icon-256x256.png?rev=1998671\";s:2:\"1x\";s:67:\"https://ps.w.org/classic-editor/assets/icon-128x128.png?rev=1998671\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:70:\"https://ps.w.org/classic-editor/assets/banner-1544x500.png?rev=1998671\";s:2:\"1x\";s:69:\"https://ps.w.org/classic-editor/assets/banner-772x250.png?rev=1998676\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"4.9\";}s:36:\"contact-form-7/wp-contact-form-7.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:28:\"w.org/plugins/contact-form-7\";s:4:\"slug\";s:14:\"contact-form-7\";s:6:\"plugin\";s:36:\"contact-form-7/wp-contact-form-7.php\";s:11:\"new_version\";s:5:\"5.9.3\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/contact-form-7/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/contact-form-7.5.9.3.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:59:\"https://ps.w.org/contact-form-7/assets/icon.svg?rev=2339255\";s:3:\"svg\";s:59:\"https://ps.w.org/contact-form-7/assets/icon.svg?rev=2339255\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/contact-form-7/assets/banner-1544x500.png?rev=860901\";s:2:\"1x\";s:68:\"https://ps.w.org/contact-form-7/assets/banner-772x250.png?rev=880427\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";}s:37:\"disable-comments/disable-comments.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:30:\"w.org/plugins/disable-comments\";s:4:\"slug\";s:16:\"disable-comments\";s:6:\"plugin\";s:37:\"disable-comments/disable-comments.php\";s:11:\"new_version\";s:5:\"2.4.6\";s:3:\"url\";s:47:\"https://wordpress.org/plugins/disable-comments/\";s:7:\"package\";s:65:\"https://downloads.wordpress.org/plugin/disable-comments.2.4.6.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:69:\"https://ps.w.org/disable-comments/assets/icon-256x256.png?rev=2509854\";s:2:\"1x\";s:69:\"https://ps.w.org/disable-comments/assets/icon-128x128.png?rev=2509854\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:72:\"https://ps.w.org/disable-comments/assets/banner-1544x500.png?rev=2509854\";s:2:\"1x\";s:71:\"https://ps.w.org/disable-comments/assets/banner-772x250.png?rev=2509854\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.0\";}s:41:\"filenames-to-latin/filenames-to-latin.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:32:\"w.org/plugins/filenames-to-latin\";s:4:\"slug\";s:18:\"filenames-to-latin\";s:6:\"plugin\";s:41:\"filenames-to-latin/filenames-to-latin.php\";s:11:\"new_version\";s:3:\"2.7\";s:3:\"url\";s:49:\"https://wordpress.org/plugins/filenames-to-latin/\";s:7:\"package\";s:65:\"https://downloads.wordpress.org/plugin/filenames-to-latin.2.7.zip\";s:5:\"icons\";a:1:{s:7:\"default\";s:69:\"https://s.w.org/plugins/geopattern-icon/filenames-to-latin_e3e3da.svg\";}s:7:\"banners\";a:1:{s:2:\"1x\";s:72:\"https://ps.w.org/filenames-to-latin/assets/banner-772x250.png?rev=835137\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"3.0\";}s:59:\"intuitive-custom-post-order/intuitive-custom-post-order.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:41:\"w.org/plugins/intuitive-custom-post-order\";s:4:\"slug\";s:27:\"intuitive-custom-post-order\";s:6:\"plugin\";s:59:\"intuitive-custom-post-order/intuitive-custom-post-order.php\";s:11:\"new_version\";s:5:\"3.1.5\";s:3:\"url\";s:58:\"https://wordpress.org/plugins/intuitive-custom-post-order/\";s:7:\"package\";s:76:\"https://downloads.wordpress.org/plugin/intuitive-custom-post-order.3.1.5.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:80:\"https://ps.w.org/intuitive-custom-post-order/assets/icon-256x256.png?rev=1078797\";s:2:\"1x\";s:80:\"https://ps.w.org/intuitive-custom-post-order/assets/icon-128x128.png?rev=1078797\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:83:\"https://ps.w.org/intuitive-custom-post-order/assets/banner-1544x500.png?rev=1209666\";s:2:\"1x\";s:82:\"https://ps.w.org/intuitive-custom-post-order/assets/banner-772x250.png?rev=1078755\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:5:\"3.5.0\";}s:21:\"jwt-auth/jwt-auth.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:22:\"w.org/plugins/jwt-auth\";s:4:\"slug\";s:8:\"jwt-auth\";s:6:\"plugin\";s:21:\"jwt-auth/jwt-auth.php\";s:11:\"new_version\";s:5:\"2.1.6\";s:3:\"url\";s:39:\"https://wordpress.org/plugins/jwt-auth/\";s:7:\"package\";s:57:\"https://downloads.wordpress.org/plugin/jwt-auth.2.1.6.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:61:\"https://ps.w.org/jwt-auth/assets/icon-256x256.png?rev=2298869\";s:2:\"1x\";s:61:\"https://ps.w.org/jwt-auth/assets/icon-256x256.png?rev=2298869\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/jwt-auth/assets/banner-1544x500.png?rev=2298891\";s:2:\"1x\";s:63:\"https://ps.w.org/jwt-auth/assets/banner-772x250.png?rev=2298883\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.2\";}s:26:\"post-smtp/postman-smtp.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:23:\"w.org/plugins/post-smtp\";s:4:\"slug\";s:9:\"post-smtp\";s:6:\"plugin\";s:26:\"post-smtp/postman-smtp.php\";s:11:\"new_version\";s:5:\"2.9.1\";s:3:\"url\";s:40:\"https://wordpress.org/plugins/post-smtp/\";s:7:\"package\";s:58:\"https://downloads.wordpress.org/plugin/post-smtp.2.9.1.zip\";s:5:\"icons\";a:1:{s:2:\"1x\";s:62:\"https://ps.w.org/post-smtp/assets/icon-128x128.gif?rev=2758621\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:65:\"https://ps.w.org/post-smtp/assets/banner-1544x500.jpg?rev=2742027\";s:2:\"1x\";s:64:\"https://ps.w.org/post-smtp/assets/banner-772x250.jpg?rev=2749492\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:5:\"5.6.0\";}s:21:\"safe-svg/safe-svg.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:22:\"w.org/plugins/safe-svg\";s:4:\"slug\";s:8:\"safe-svg\";s:6:\"plugin\";s:21:\"safe-svg/safe-svg.php\";s:11:\"new_version\";s:5:\"2.2.4\";s:3:\"url\";s:39:\"https://wordpress.org/plugins/safe-svg/\";s:7:\"package\";s:57:\"https://downloads.wordpress.org/plugin/safe-svg.2.2.4.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:53:\"https://ps.w.org/safe-svg/assets/icon.svg?rev=2779013\";s:3:\"svg\";s:53:\"https://ps.w.org/safe-svg/assets/icon.svg?rev=2779013\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:64:\"https://ps.w.org/safe-svg/assets/banner-1544x500.png?rev=2683939\";s:2:\"1x\";s:63:\"https://ps.w.org/safe-svg/assets/banner-772x250.png?rev=2683939\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.7\";}s:33:\"user-switching/user-switching.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:28:\"w.org/plugins/user-switching\";s:4:\"slug\";s:14:\"user-switching\";s:6:\"plugin\";s:33:\"user-switching/user-switching.php\";s:11:\"new_version\";s:5:\"1.7.3\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/user-switching/\";s:7:\"package\";s:63:\"https://downloads.wordpress.org/plugin/user-switching.1.7.3.zip\";s:5:\"icons\";a:2:{s:2:\"1x\";s:59:\"https://ps.w.org/user-switching/assets/icon.svg?rev=2032062\";s:3:\"svg\";s:59:\"https://ps.w.org/user-switching/assets/icon.svg?rev=2032062\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:70:\"https://ps.w.org/user-switching/assets/banner-1544x500.png?rev=2204929\";s:2:\"1x\";s:69:\"https://ps.w.org/user-switching/assets/banner-772x250.png?rev=2204929\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"5.6\";}s:33:\"duplicate-post/duplicate-post.php\";O:8:\"stdClass\":10:{s:2:\"id\";s:28:\"w.org/plugins/duplicate-post\";s:4:\"slug\";s:14:\"duplicate-post\";s:6:\"plugin\";s:33:\"duplicate-post/duplicate-post.php\";s:11:\"new_version\";s:3:\"4.5\";s:3:\"url\";s:45:\"https://wordpress.org/plugins/duplicate-post/\";s:7:\"package\";s:61:\"https://downloads.wordpress.org/plugin/duplicate-post.4.5.zip\";s:5:\"icons\";a:2:{s:2:\"2x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-256x256.png?rev=2336666\";s:2:\"1x\";s:67:\"https://ps.w.org/duplicate-post/assets/icon-128x128.png?rev=2336666\";}s:7:\"banners\";a:2:{s:2:\"2x\";s:70:\"https://ps.w.org/duplicate-post/assets/banner-1544x500.png?rev=2336666\";s:2:\"1x\";s:69:\"https://ps.w.org/duplicate-post/assets/banner-772x250.png?rev=2336666\";}s:11:\"banners_rtl\";a:0:{}s:8:\"requires\";s:3:\"6.3\";}s:34:\"advanced-custom-fields-pro/acf.php\";O:8:\"stdClass\":12:{s:4:\"slug\";s:26:\"advanced-custom-fields-pro\";s:6:\"plugin\";s:34:\"advanced-custom-fields-pro/acf.php\";s:11:\"new_version\";s:5:\"6.2.9\";s:3:\"url\";s:36:\"https://www.advancedcustomfields.com\";s:6:\"tested\";s:5:\"6.5.3\";s:7:\"package\";s:0:\"\";s:5:\"icons\";a:1:{s:7:\"default\";s:63:\"https://ps.w.org/advanced-custom-fields/assets/icon-256x256.png\";}s:7:\"banners\";a:2:{s:3:\"low\";s:77:\"https://ps.w.org/advanced-custom-fields/assets/banner-772x250.jpg?rev=1729102\";s:4:\"high\";s:78:\"https://ps.w.org/advanced-custom-fields/assets/banner-1544x500.jpg?rev=1729099\";}s:8:\"requires\";s:3:\"5.8\";s:12:\"requires_php\";s:3:\"7.0\";s:12:\"release_date\";s:8:\"20240408\";s:6:\"reason\";s:10:\"up_to_date\";}}s:7:\"checked\";a:20:{s:31:\"acf-country-3.x/acf-country.php\";s:5:\"3.0.1\";s:18:\"acfml/wpml-acf.php\";s:5:\"2.0.5\";s:34:\"advanced-custom-fields-pro/acf.php\";s:5:\"6.2.9\";s:33:\"classic-editor/classic-editor.php\";s:5:\"1.6.3\";s:36:\"contact-form-7/wp-contact-form-7.php\";s:5:\"5.9.3\";s:38:\"contact-form-7-multilingual/plugin.php\";s:5:\"1.2.1\";s:52:\"contact-form-7-template-support/template-support.php\";s:3:\"1.5\";s:37:\"disable-comments/disable-comments.php\";s:5:\"2.4.6\";s:41:\"filenames-to-latin/filenames-to-latin.php\";s:3:\"2.7\";s:59:\"intuitive-custom-post-order/intuitive-custom-post-order.php\";s:5:\"3.1.5\";s:21:\"jwt-auth/jwt-auth.php\";s:5:\"2.1.6\";s:31:\"jwt-whitelist/jwt-whitelist.php\";s:5:\"0.0.1\";s:26:\"post-smtp/postman-smtp.php\";s:5:\"2.9.1\";s:21:\"safe-svg/safe-svg.php\";s:5:\"2.2.4\";s:33:\"user-switching/user-switching.php\";s:5:\"1.7.3\";s:40:\"sitepress-multilingual-cms/sitepress.php\";s:5:\"4.6.6\";s:30:\"wp-seo-multilingual/plugin.php\";s:5:\"2.1.0\";s:34:\"wpml-string-translation/plugin.php\";s:5:\"3.2.8\";s:33:\"duplicate-post/duplicate-post.php\";s:3:\"4.5\";s:24:\"wordpress-seo/wp-seo.php\";s:4:\"22.5\";}}', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `cp_postmeta`
--

DROP TABLE IF EXISTS `cp_postmeta`;
CREATE TABLE IF NOT EXISTS `cp_postmeta` (
  `meta_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM AUTO_INCREMENT=936 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_postmeta`
--

INSERT INTO `cp_postmeta` (`meta_id`, `post_id`, `meta_key`, `meta_value`) VALUES
(1, 2, '_wp_page_template', 'templates/home.php'),
(2, 3, '_wp_page_template', 'default'),
(3, 5, '_form', '<label> Το όνομά σας\n    [text* your-name autocomplete:name] </label>\n\n<label> Το email σας\n    [email* your-email autocomplete:email] </label>\n\n<label> Θέμα\n    [text* your-subject] </label>\n\n<label> Το μήνυμά σας (προαιρετικό)\n    [textarea your-message] </label>\n\n[submit \"Υποβολή\"]'),
(4, 5, '_mail', 'a:8:{s:7:\"subject\";s:30:\"[_site_title] \"[your-subject]\"\";s:6:\"sender\";s:46:\"[_site_title] <apostolis.kyromitis@novidea.gr>\";s:4:\"body\";s:209:\"Από: [your-name] [your-email]\nΘέμα: [your-subject]\n\nΣώμα μηνύματος:\n[your-message]\n\n-- \nThis is a notification that a contact form was submitted on your website ([_site_title] [_site_url]).\";s:9:\"recipient\";s:19:\"[_site_admin_email]\";s:18:\"additional_headers\";s:22:\"Reply-To: [your-email]\";s:11:\"attachments\";s:0:\"\";s:8:\"use_html\";i:0;s:13:\"exclude_blank\";i:0;}'),
(5, 5, '_mail_2', 'a:9:{s:6:\"active\";b:0;s:7:\"subject\";s:30:\"[_site_title] \"[your-subject]\"\";s:6:\"sender\";s:46:\"[_site_title] <apostolis.kyromitis@novidea.gr>\";s:4:\"body\";s:235:\"Σώμα μηνύματος:\n[your-message]\n\n-- \nThis email is a receipt for your contact form submission on our website ([_site_title] [_site_url]) in which your email address was used. If that was not you, please ignore this message.\";s:9:\"recipient\";s:12:\"[your-email]\";s:18:\"additional_headers\";s:29:\"Reply-To: [_site_admin_email]\";s:11:\"attachments\";s:0:\"\";s:8:\"use_html\";i:0;s:13:\"exclude_blank\";i:0;}'),
(6, 5, '_messages', 'a:12:{s:12:\"mail_sent_ok\";s:97:\"Ευχαριστούμε για το μήνυμά σας. Στάλθηκε με επιτυχία.\";s:12:\"mail_sent_ng\";s:105:\"Υπήρξε σφάλμα κατά την αποστολή. Δοκιμάστε ξανά αργότερα.\";s:16:\"validation_error\";s:122:\"Ένα ή περισσότερα πεδία έχουν σφάλματα. Ελέγξτε και ξαναδοκιμάστε.\";s:4:\"spam\";s:105:\"Υπήρξε σφάλμα κατά την αποστολή. Δοκιμάστε ξανά αργότερα.\";s:12:\"accept_terms\";s:118:\"Πρέπει να αποδεχτείτε τους όρους χρήσης πριν στείλετε το μήνυμα.\";s:16:\"invalid_required\";s:65:\"Παρακαλώ συμπληρώστε αυτό το πεδίο.\";s:16:\"invalid_too_long\";s:32:\"This field has a too long input.\";s:17:\"invalid_too_short\";s:33:\"This field has a too short input.\";s:13:\"upload_failed\";s:100:\"Υπήρξε άγνωστο σφάλμα κατά τη μεταφόρτωση του αρχείου.\";s:24:\"upload_file_type_invalid\";s:97:\"Δεν επιτρέπεται η μεταφόρτωση τέτοιου τύπου αρχείου.\";s:21:\"upload_file_too_large\";s:31:\"The uploaded file is too large.\";s:23:\"upload_failed_php_error\";s:85:\"Υπήρξε σφάλμα κατά τη μεταφόρτωση του αρχείου.\";}'),
(7, 5, '_additional_settings', ''),
(8, 5, '_locale', 'el'),
(9, 5, '_hash', '5d7455df2a0e8008e5a2f83ddf76027001e61af1'),
(10, 2, '_edit_lock', '1713521472:1'),
(11, 2, '_edit_last', '1'),
(12, 2, '_yoast_wpseo_estimated-reading-time-minutes', '1'),
(13, 2, '_yoast_wpseo_wordproof_timestamp', ''),
(14, 3, '_edit_last', '1'),
(15, 3, '_edit_lock', '1713457882:1'),
(16, 9, '_menu_item_type', 'post_type'),
(17, 9, '_menu_item_menu_item_parent', '0'),
(18, 9, '_menu_item_object_id', '3'),
(19, 9, '_menu_item_object', 'page'),
(20, 9, '_menu_item_target', ''),
(21, 9, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(22, 9, '_menu_item_xfn', ''),
(23, 9, '_menu_item_url', ''),
(25, 10, '_menu_item_type', 'custom'),
(26, 10, '_menu_item_menu_item_parent', '0'),
(27, 10, '_menu_item_object_id', '10'),
(28, 10, '_menu_item_object', 'custom'),
(29, 10, '_menu_item_target', ''),
(30, 10, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(31, 10, '_menu_item_xfn', ''),
(32, 10, '_menu_item_url', '#'),
(34, 11, '_menu_item_type', 'custom'),
(35, 11, '_menu_item_menu_item_parent', '0'),
(36, 11, '_menu_item_object_id', '11'),
(37, 11, '_menu_item_object', 'custom'),
(38, 11, '_menu_item_target', ''),
(39, 11, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(40, 11, '_menu_item_xfn', ''),
(41, 11, '_menu_item_url', '#'),
(43, 12, '_menu_item_type', 'custom'),
(44, 12, '_menu_item_menu_item_parent', '0'),
(45, 12, '_menu_item_object_id', '12'),
(46, 12, '_menu_item_object', 'custom'),
(47, 12, '_menu_item_target', ''),
(48, 12, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(49, 12, '_menu_item_xfn', ''),
(50, 12, '_menu_item_url', '#'),
(139, 20, 'login_register_content', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>'),
(101, 18, '_yoast_wpseo_wordproof_timestamp', ''),
(100, 18, '_yoast_wpseo_estimated-reading-time-minutes', '0'),
(99, 18, '_wp_page_template', 'templates/my-account.php'),
(98, 18, '_edit_lock', '1714566353:1'),
(97, 18, '_edit_last', '1'),
(138, 20, '_login_register_title', 'field_662b7f7c591d5'),
(137, 20, 'login_register_title', 'Καλώς ήλθατε!'),
(90, 17, '_menu_item_object_id', '2'),
(136, 37, '_edit_last', '1'),
(135, 37, '_edit_lock', '1714391103:1'),
(134, 35, '_edit_last', '1'),
(133, 35, '_edit_lock', '1714488952:1'),
(132, 26, '_edit_last', '1'),
(131, 26, '_edit_lock', '1714393541:1'),
(682, 118, 'my_account_title', 'Ο λογαριασμός μου'),
(89, 17, '_menu_item_menu_item_parent', '0'),
(79, 16, '_menu_item_type', 'custom'),
(80, 16, '_menu_item_menu_item_parent', '0'),
(81, 16, '_menu_item_object_id', '16'),
(82, 16, '_menu_item_object', 'custom'),
(83, 16, '_menu_item_target', ''),
(84, 16, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(85, 16, '_menu_item_xfn', ''),
(86, 16, '_menu_item_url', '#'),
(88, 17, '_menu_item_type', 'post_type'),
(91, 17, '_menu_item_object', 'page'),
(92, 17, '_menu_item_target', ''),
(93, 17, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(94, 17, '_menu_item_xfn', ''),
(95, 17, '_menu_item_url', ''),
(102, 20, '_edit_last', '1'),
(103, 20, '_edit_lock', '1714391083:1'),
(104, 20, '_wp_page_template', 'templates/checkout.php'),
(105, 20, '_yoast_wpseo_estimated-reading-time-minutes', '0'),
(106, 20, '_yoast_wpseo_wordproof_timestamp', ''),
(107, 22, '_menu_item_type', 'post_type'),
(108, 22, '_menu_item_menu_item_parent', '0'),
(109, 22, '_menu_item_object_id', '20'),
(110, 22, '_menu_item_object', 'page'),
(111, 22, '_menu_item_target', ''),
(112, 22, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(113, 22, '_menu_item_xfn', ''),
(114, 22, '_menu_item_url', ''),
(116, 23, '_menu_item_type', 'post_type'),
(117, 23, '_menu_item_menu_item_parent', '0'),
(118, 23, '_menu_item_object_id', '18'),
(119, 23, '_menu_item_object', 'page'),
(120, 23, '_menu_item_target', ''),
(121, 23, '_menu_item_classes', 'a:1:{i:0;s:0:\"\";}'),
(122, 23, '_menu_item_xfn', ''),
(123, 23, '_menu_item_url', ''),
(125, 10, '_wp_old_date', '2024-04-18'),
(126, 11, '_wp_old_date', '2024-04-18'),
(127, 12, '_wp_old_date', '2024-04-18'),
(128, 17, '_wp_old_date', '2024-04-18'),
(129, 16, '_wp_old_date', '2024-04-18'),
(140, 20, '_login_register_content', 'field_662b7f8b591d6'),
(141, 41, 'login_register_title', 'Καλώς ήλθατε!'),
(142, 41, '_login_register_title', 'field_662b7f7c591d5'),
(143, 41, 'login_register_content', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>'),
(144, 41, '_login_register_content', 'field_662b7f8b591d6'),
(145, 20, 'complete_donation_title', 'Ολοκλήρωση Δωρεάς'),
(146, 20, '_complete_donation_title', 'field_662f7039678a2'),
(147, 20, 'complete_donation_content', 'Συμπληρώστε τα προσωπικά σας στοιχεία για την ολοκλήρωση της δωρεάς.'),
(148, 20, '_complete_donation_content', 'field_662f703f678a3'),
(149, 45, 'login_register_title', 'Καλώς ήλθατε!'),
(150, 45, '_login_register_title', 'field_662b7f7c591d5'),
(151, 45, 'login_register_content', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>'),
(152, 45, '_login_register_content', 'field_662b7f8b591d6'),
(153, 45, 'complete_donation_title', 'Ολοκλήρωση Δωρεάς'),
(154, 45, '_complete_donation_title', 'field_662f7039678a2'),
(155, 45, 'complete_donation_content', 'Συμπληρώστε τα προσωπικά σας στοιχεία για την ολοκλήρωση της δωρεάς.'),
(156, 45, '_complete_donation_content', 'field_662f703f678a3'),
(157, 55, '_edit_lock', '1714657582:1'),
(158, 55, '_edit_last', '1'),
(611, 104, 'donation_key', '10420240501110854000000'),
(610, 104, 'donation_status', 'completed'),
(609, 104, 'donation_amount', '50'),
(608, 104, 'donation_type', 'one-time'),
(607, 104, 'billing_country', 'GR'),
(606, 104, 'billing_postal_code', '1234'),
(605, 104, 'billing_city', 'Neverland'),
(604, 104, 'billing_address', 'My address here'),
(603, 104, 'telephone', '699 99 99 999'),
(601, 104, 'last_name', 'Doe'),
(602, 104, 'email', 'test@test.gr'),
(793, 132, 'end_date', '20280502'),
(792, 133, '_edit_lock', '1714642810:1'),
(791, 133, 'payments', 'a:1:{i:0;i:130;}'),
(789, 133, 'payment_cycle', '1'),
(790, 133, 'subscription_status', 'active'),
(788, 133, 'end_data', NULL),
(787, 133, 'start_data', '2024-05-02'),
(786, 132, '_edit_lock', '1714642804:1'),
(781, 132, 'subscription_status', 'active'),
(782, 132, 'payments', 'a:1:{i:0;i:130;}'),
(783, 131, '_wp_trash_meta_status', 'publish'),
(784, 131, '_wp_trash_meta_time', '1714642228'),
(785, 131, '_wp_desired_post_slug', 'payment-3'),
(809, 134, '_donation_status', 'field_662faf585f3fc'),
(778, 132, 'start_data', '20240502'),
(779, 132, 'end_data', NULL),
(780, 132, 'payment_cycle', '1'),
(758, 130, 'telephone', '699 99 99 999'),
(759, 130, 'billing_address', 'My address here'),
(760, 130, 'billing_city', 'Neverland'),
(761, 130, 'billing_postal_code', '1234'),
(762, 130, 'billing_country', 'GR'),
(763, 130, 'donation_type', 'recurring'),
(764, 130, 'donation_frequency', '1'),
(765, 130, 'donation_amount', '50'),
(766, 130, 'donation_status', 'completed'),
(767, 130, 'recurring_end_date', '20280502'),
(768, 130, 'donation_key', '13020240502092353000000'),
(654, 107, 'donation_key', '10720240501111107000000'),
(653, 107, 'donation_status', 'failed'),
(652, 107, 'donation_amount', '50'),
(651, 107, 'donation_type', 'one-time'),
(650, 107, 'billing_country', 'GR'),
(649, 107, 'billing_postal_code', '1234'),
(648, 107, 'billing_city', 'Neverland'),
(646, 107, 'telephone', '699 99 99 999'),
(647, 107, 'billing_address', 'My address here'),
(664, 108, 'donation_type', 'one-time'),
(663, 108, 'billing_country', 'GR'),
(662, 108, 'billing_postal_code', '1234'),
(661, 108, 'billing_city', 'Neverland'),
(660, 108, 'billing_address', 'My address here'),
(659, 108, 'telephone', '699 99 99 999'),
(656, 108, 'first_name', 'John'),
(657, 108, 'last_name', 'Doe'),
(658, 108, 'email', 'test@test.gr'),
(672, 18, 'my_account_content', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>'),
(671, 18, '_my_account_title', 'field_66322a7c43e21'),
(670, 18, 'my_account_title', 'Ο λογαριασμός μου'),
(669, 109, '_edit_last', '1'),
(668, 109, '_edit_lock', '1714563823:1'),
(667, 108, 'donation_key', '10820240501111125000000'),
(676, 18, 'recurring_donations_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.'),
(675, 18, '_recurring_donations_title', 'field_66322aaa43e24'),
(674, 18, 'recurring_donations_title', 'Επαναλαμβανόμενες Δωρεές'),
(673, 18, '_my_account_content', 'field_66322a8b43e22'),
(681, 18, '_completed_donations_content', 'field_66322af043e27'),
(680, 18, 'completed_donations_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.'),
(679, 18, '_completed_donations_title', 'field_66322acc43e26'),
(678, 18, 'completed_donations_title', 'Ολοκληρωμένες Δωρεές'),
(275, 86, '_wp_attached_file', '2024/04/426630f77882a7ef0feceab2024af785-scaled.jpg'),
(276, 86, '_wp_attachment_metadata', 'a:7:{s:5:\"width\";i:2560;s:6:\"height\";i:1126;s:4:\"file\";s:51:\"2024/04/426630f77882a7ef0feceab2024af785-scaled.jpg\";s:8:\"filesize\";i:112597;s:5:\"sizes\";a:7:{s:6:\"medium\";a:5:{s:4:\"file\";s:44:\"426630f77882a7ef0feceab2024af785-300x132.jpg\";s:5:\"width\";i:300;s:6:\"height\";i:132;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:7829;}s:5:\"large\";a:5:{s:4:\"file\";s:45:\"426630f77882a7ef0feceab2024af785-1024x451.jpg\";s:5:\"width\";i:1024;s:6:\"height\";i:451;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:35155;}s:9:\"thumbnail\";a:5:{s:4:\"file\";s:44:\"426630f77882a7ef0feceab2024af785-150x150.jpg\";s:5:\"width\";i:150;s:6:\"height\";i:150;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:6291;}s:12:\"medium_large\";a:5:{s:4:\"file\";s:44:\"426630f77882a7ef0feceab2024af785-768x338.jpg\";s:5:\"width\";i:768;s:6:\"height\";i:338;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:25134;}s:9:\"1536x1536\";a:5:{s:4:\"file\";s:45:\"426630f77882a7ef0feceab2024af785-1536x676.jpg\";s:5:\"width\";i:1536;s:6:\"height\";i:676;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:58219;}s:9:\"2048x2048\";a:5:{s:4:\"file\";s:45:\"426630f77882a7ef0feceab2024af785-2048x901.jpg\";s:5:\"width\";i:2048;s:6:\"height\";i:901;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:83635;}s:5:\"picto\";a:5:{s:4:\"file\";s:42:\"426630f77882a7ef0feceab2024af785-80x35.jpg\";s:5:\"width\";i:80;s:6:\"height\";i:35;s:9:\"mime-type\";s:10:\"image/jpeg\";s:8:\"filesize\";i:1861;}}s:10:\"image_meta\";a:12:{s:8:\"aperture\";s:1:\"0\";s:6:\"credit\";s:0:\"\";s:6:\"camera\";s:0:\"\";s:7:\"caption\";s:0:\"\";s:17:\"created_timestamp\";s:1:\"0\";s:9:\"copyright\";s:0:\"\";s:12:\"focal_length\";s:1:\"0\";s:3:\"iso\";s:1:\"0\";s:13:\"shutter_speed\";s:1:\"0\";s:5:\"title\";s:0:\"\";s:11:\"orientation\";s:1:\"0\";s:8:\"keywords\";a:0:{}}s:14:\"original_image\";s:36:\"426630f77882a7ef0feceab2024af785.jpg\";}'),
(687, 118, '_recurring_donations_title', 'field_66322aaa43e24'),
(686, 118, 'recurring_donations_title', 'Επαναλαμβανόμενες Δωρεές'),
(685, 118, '_my_account_content', 'field_66322a8b43e22'),
(684, 118, 'my_account_content', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>'),
(692, 118, 'completed_donations_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.'),
(691, 118, '_completed_donations_title', 'field_66322acc43e26'),
(690, 118, 'completed_donations_title', 'Ολοκληρωμένες Δωρεές'),
(689, 118, '_recurring_donations_content', 'field_66322aba43e25'),
(839, 136, 'billing_country', 'GR'),
(837, 136, 'billing_city', 'Neverland'),
(838, 136, 'billing_postal_code', '1234'),
(836, 136, 'billing_address', 'My address here'),
(835, 136, 'telephone', '699 99 99 999'),
(834, 136, 'email', 'test@test.gr'),
(695, 120, '_edit_last', '1'),
(693, 118, '_completed_donations_content', 'field_66322af043e27'),
(694, 120, '_edit_lock', '1714657488:1'),
(858, 137, '_wp_desired_post_slug', 'subscription'),
(857, 137, '_wp_trash_meta_time', '1714643326'),
(856, 137, '_wp_trash_meta_status', 'publish'),
(855, 136, '_edit_lock', '1714643173:1'),
(833, 136, 'last_name', 'Doe'),
(832, 136, 'first_name', 'John'),
(831, 135, '_donation_status', 'field_662faf585f3fc'),
(828, 135, 'donation_status', 'completed'),
(829, 135, 'recurring_end_date', '20280502'),
(830, 135, 'donation_key', '13520240502094330000000'),
(859, 138, 'first_name', 'John'),
(854, 137, '_edit_lock', '1714643010:1'),
(853, 136, '_subscription_id', 'field_663357ba10549'),
(852, 136, 'subscription_id', '136'),
(840, 136, 'donation_type', 'recurring'),
(841, 136, 'donation_frequency', '1'),
(842, 136, 'donation_amount', '50'),
(843, 136, 'donation_status', 'completed'),
(844, 136, 'recurring_end_date', '20280502'),
(845, 136, 'donation_key', '13620240502094528000000'),
(846, 136, '_donation_status', 'field_662faf585f3fc'),
(847, 137, 'start_data', '20240502'),
(848, 137, 'end_data', NULL),
(849, 137, 'payment_cycle', '1'),
(850, 137, 'subscription_status', 'active'),
(851, 137, 'payments', 'a:1:{i:0;i:136;}'),
(810, 133, '_wp_trash_meta_status', 'publish'),
(811, 133, '_wp_trash_meta_time', '1714642958'),
(812, 133, '_wp_desired_post_slug', 'subscription-2'),
(813, 132, '_wp_trash_meta_status', 'publish'),
(814, 132, '_wp_trash_meta_time', '1714642958'),
(815, 132, '_wp_desired_post_slug', 'subscription'),
(816, 134, '_edit_lock', '1714642882:1'),
(817, 135, 'first_name', 'John'),
(818, 135, 'last_name', 'Doe'),
(819, 135, 'email', 'test@test.gr'),
(820, 135, 'telephone', '699 99 99 999'),
(821, 135, 'billing_address', 'My address here'),
(822, 135, 'billing_city', 'Neverland'),
(823, 135, 'billing_postal_code', '1234'),
(824, 135, 'billing_country', 'GR'),
(825, 135, 'donation_type', 'recurring'),
(826, 135, 'donation_frequency', '3'),
(827, 135, 'donation_amount', '50'),
(771, 131, 'start_data', '20240502'),
(772, 131, 'end_data', NULL),
(773, 131, 'payment_cycle', '1'),
(774, 131, 'subscription_status', 'active'),
(775, 131, 'payments', 'a:1:{i:0;i:130;}'),
(776, 130, 'subscription_id', '130'),
(777, 130, '_subscription_id', 'field_663357ba10549'),
(866, 138, 'billing_country', 'GR'),
(865, 138, 'billing_postal_code', '1234'),
(864, 138, 'billing_city', 'Neverland'),
(863, 138, 'billing_address', 'My address here'),
(878, 139, 'payments', 'a:1:{i:0;i:138;}'),
(877, 139, 'subscription_status', 'active'),
(876, 139, 'payment_cycle', '1'),
(875, 139, 'end_data', '20280502'),
(874, 139, 'start_data', '20240502'),
(873, 138, '_donation_status', 'field_662faf585f3fc'),
(872, 138, 'donation_key', '13820240502094859000000'),
(870, 138, 'donation_status', 'completed'),
(871, 138, 'recurring_end_date', '20280502'),
(891, 141, 'payment_cycle', '1'),
(890, 141, 'end_date', '20280502'),
(889, 141, 'start_date', '20240502'),
(888, 140, '_edit_lock', '1714643597:1'),
(887, 140, 'payments', 'a:1:{i:0;i:138;}'),
(886, 140, 'subscription_status', 'active'),
(885, 140, 'payment_cycle', '1'),
(884, 140, 'end_data', '20280502'),
(883, 140, 'start_data', '20240502'),
(881, 139, '_edit_lock', '1714643220:1'),
(882, 138, '_edit_lock', '1714643640:1'),
(905, 142, 'donation_amount', '50'),
(904, 142, 'donation_frequency', '1'),
(903, 142, 'donation_type', 'recurring'),
(902, 142, 'billing_country', 'GR'),
(901, 142, 'billing_postal_code', '1234'),
(900, 142, 'billing_city', 'Neverland'),
(899, 142, 'billing_address', 'My address here'),
(898, 142, 'telephone', '699 99 99 999'),
(895, 142, 'first_name', 'John'),
(896, 142, 'last_name', 'Doe'),
(897, 142, 'email', 'test@test.gr'),
(935, 143, '_payments', 'field_66335097090bb'),
(934, 143, '_payment_cycle', 'field_6633502e090ba'),
(933, 143, '_end_date', 'field_66334fdf946e9'),
(932, 143, '_start_date', 'field_66334f8a946e8'),
(931, 143, '_donation_amount', 'field_663397847ff7c'),
(930, 143, 'donation_amount', '50'),
(928, 143, '_edit_last', '1'),
(929, 143, '_subscription_status', 'field_66334fed946ea'),
(927, 142, '_edit_lock', '1714643735:1'),
(926, 143, '_edit_lock', '1714659177:1'),
(925, 139, '_wp_desired_post_slug', 'subscription'),
(924, 139, '_wp_trash_meta_time', '1714643866'),
(923, 139, '_wp_trash_meta_status', 'publish'),
(922, 140, '_wp_desired_post_slug', 'subscription-2'),
(921, 140, '_wp_trash_meta_time', '1714643866'),
(920, 140, '_wp_trash_meta_status', 'publish'),
(919, 141, '_wp_desired_post_slug', 'subscription-3'),
(918, 141, '_wp_trash_meta_time', '1714643866'),
(917, 141, '_wp_trash_meta_status', 'publish'),
(909, 142, '_donation_status', 'field_662faf585f3fc'),
(910, 143, 'start_date', '20240502'),
(911, 143, 'end_date', '20280502'),
(912, 143, 'payment_cycle', '1'),
(913, 143, 'subscription_status', 'active'),
(914, 143, 'payments', 'a:1:{i:0;s:3:\"142\";}'),
(915, 142, 'subscription_id', '143'),
(916, 142, '_subscription_id', 'field_663357ba10549'),
(908, 142, 'donation_key', '14220240502095721000000'),
(907, 142, 'recurring_end_date', '20280502'),
(906, 142, 'donation_status', 'completed'),
(894, 141, '_edit_lock', '1714643618:1'),
(893, 141, 'payments', 'a:1:{i:0;i:138;}'),
(892, 141, 'subscription_status', 'active'),
(880, 138, '_subscription_id', 'field_663357ba10549'),
(879, 138, 'subscription_id', '138'),
(869, 138, 'donation_amount', '50'),
(868, 138, 'donation_frequency', '1'),
(867, 138, 'donation_type', 'recurring'),
(862, 138, 'telephone', '699 99 99 999'),
(861, 138, 'email', 'test@test.gr'),
(860, 138, 'last_name', 'Doe'),
(794, 132, '_end_date', 'field_66334fdf946e9'),
(795, 134, 'first_name', 'John'),
(796, 134, 'last_name', 'Doe'),
(797, 134, 'email', 'test@test.gr'),
(798, 134, 'telephone', '699 99 99 999'),
(799, 134, 'billing_address', 'My address here'),
(800, 134, 'billing_city', 'Neverland'),
(801, 134, 'billing_postal_code', '1234'),
(802, 134, 'billing_country', 'GR'),
(803, 134, 'donation_type', 'recurring'),
(804, 134, 'donation_frequency', '1'),
(805, 134, 'donation_amount', '50'),
(806, 134, 'donation_status', 'completed'),
(807, 134, 'recurring_end_date', '20280502'),
(688, 118, 'recurring_donations_content', 'Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.'),
(683, 118, '_my_account_title', 'field_66322a7c43e21'),
(677, 18, '_recurring_donations_content', 'field_66322aba43e25'),
(666, 108, 'donation_status', 'pending'),
(665, 108, 'donation_amount', '50'),
(655, 107, '_donation_status', 'field_662faf585f3fc'),
(645, 107, 'email', 'test@test.gr'),
(644, 107, 'last_name', 'Doe'),
(643, 107, 'first_name', 'John'),
(808, 134, 'donation_key', '13420240502094205000000'),
(769, 130, '_donation_status', 'field_662faf585f3fc'),
(770, 130, '_edit_lock', '1714642531:1'),
(757, 130, 'email', 'test@test.gr'),
(756, 130, 'last_name', 'Doe'),
(755, 130, 'first_name', 'John'),
(612, 104, '_donation_status', 'field_662faf585f3fc'),
(600, 104, 'first_name', 'John');

-- --------------------------------------------------------

--
-- Table structure for table `cp_posts`
--

DROP TABLE IF EXISTS `cp_posts`;
CREATE TABLE IF NOT EXISTS `cp_posts` (
  `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_author` bigint UNSIGNED NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint UNSIGNED NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_posts`
--

INSERT INTO `cp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(1, 1, '2024-04-08 17:14:26', '2024-04-08 14:14:26', '<!-- wp:paragraph -->\n<p>Καλωσορίσατε στο WordPress!  Αυτό είναι το πρώτο σας άρθρο.  Αλλάξτε το ή διαγράψτε το και αρχίστε να γράφετε!</p>\n<!-- /wp:paragraph -->', 'Καλημέρα κόσμε!', '', 'publish', 'open', 'open', '', 'hello-world', '', '', '2024-04-08 17:14:26', '2024-04-08 14:14:26', '', 0, 'http://localhost/choose-life/?p=1', 0, 'post', '', 1),
(2, 1, '2024-04-08 17:14:26', '2024-04-08 14:14:26', '', 'Αρχική', '', 'publish', 'closed', 'closed', '', 'home', '', '', '2024-04-11 16:28:06', '2024-04-11 13:28:06', '', 0, 'http://localhost/choose-life/?page_id=2', 0, 'page', '', 0),
(6, 1, '2024-04-11 16:27:40', '2024-04-11 13:27:40', '', 'Δείγμα σελίδας', '', 'inherit', 'closed', 'closed', '', '2-revision-v1', '', '', '2024-04-11 16:27:40', '2024-04-11 13:27:40', '', 2, 'http://localhost/choose-life/?p=6', 0, 'revision', '', 0),
(7, 1, '2024-04-11 16:28:06', '2024-04-11 13:28:06', '', 'Αρχική', '', 'inherit', 'closed', 'closed', '', '2-revision-v1', '', '', '2024-04-11 16:28:06', '2024-04-11 13:28:06', '', 2, 'http://localhost/choose-life/?p=7', 0, 'revision', '', 0),
(8, 1, '2024-04-18 19:31:22', '2024-04-18 16:31:22', '<!-- wp:heading --><h2>Ποιοί είμαστε</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Η διεύθυνση της σελίδας είναι: http://localhost/choose-life.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Σχόλια</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Όταν οι επισκέπτες αφήνουν σχόλια στον ιστότοπο, συλλέγουμε τα δεδομένα που εμφανίζονται στη φόρμα σχολίων όπως επίσης τη διεύθυνση IP του επισκέπτη και τη συμβολοσειρά του χρήστη του προγράμματος περιήγησης ώστε να βοηθήσουμε στην ανίχνευση ανεπιθύμητων μηνυμάτων.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Μια ανωνυμοποιημένη συμβολοσειρά που δημιουργήθηκε από τη διεύθυνση ηλεκτρονικού ταχυδρομείου σας (επίσης αποκαλούμενη \"hash\") ενδέχεται να παρασχεθεί στην υπηρεσία Gravatar για να δει αν τη χρησιμοποιείτε. Η πολιτική απορρήτου της υπηρεσίας Gravatar διατίθεται εδώ: https://automattic.com/privacy/. Μετά την έγκριση του σχολίου σας, η εικόνα του προφίλ σας είναι ορατή στο κοινό μέσα στο πλαίσιο του σχολίου σας.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Πολυμέσα</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν μεταφορτώνετε εικόνες στον ιστότοπο, θα πρέπει να αποφύγετε τη μεταφόρτωση εικόνων με ενσωματωμένα δεδομένα τοποθεσίας (EXIF GPS). Οι επισκέπτες του ιστότοπου μπορούν να πραγματοποιήσουν λήψη και εξαγωγή οποιωνδήποτε δεδομένων τοποθεσίας από εικόνες στον ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cookies</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Αν αφήσετε ένα σχόλιο στον ιστότοπό μας, μπορείτε να επιλέξετε να αποθηκεύσετε το όνομα, τη διεύθυνση ηλεκτρονικού ταχυδρομείου και τον ιστότοπό σας σε cookies. Αυτά είναι για την δική σας ευκολία, έτσι ώστε να μην χρειάζεται να συμπληρώσετε τα στοιχεία σας πάλι όταν αφήσετε ένα άλλο σχόλιο. Αυτά τα cookies θα διαρκέσουν για ένα έτος.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Αν έχετε έναν λογαριασμό και συνδεθείτε στον ιστότοπο, θα δημιουργήσουμε ένα προσωρινό cookie για να προσδιορίσουμε αν ο φυλλομετρητής σας δέχεται cookies. Το cookie δεν περιέχει προσωπικές πληροφορίες και θα διαγράφει μόλις κλείσετε τον φυλλομετρητή σας.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Όταν συνδεθείτε, θα δημιουργήσουμε επίσης διάφορα cookies για να αποθηκεύσετε τις πληροφορίες σύνδεσης και τις επιλογές οθόνης. Τα cookie εισόδου διαρκούν για δύο ημέρες και τα cookie επιλογών οθόνης διαρκούν για ένα χρόνο. Αν επιλέξετε &quot;Να με θυμάσαι&quot;, η σύνδεσή σας θα παραμείνει για δύο εβδομάδες. Αν αποσυνδεθείτε από το λογαριασμό σας, τα cookie σύνδεσης θα καταργηθούν.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Εάν επεξεργαστείτε ή δημοσιεύσετε ένα άρθρο, ένα επιπλέον cookie θα αποθηκευτεί στο πρόγραμμα περιήγησης. Αυτό το cookie δεν περιλαμβάνει προσωπικά δεδομένα και υποδεικνύει απλώς το post ID του άρθρου που μόλις επεξεργαστήκατε. Λήγει μετά από 1 ημέρα.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ενσωματωμένο περιεχόμενο από άλλους ιστότοπους</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Τα άρθρα σε αυτόν τον ιστότοπο ενδέχεται να περιλαμβάνουν ενσωματωμένο περιεχόμενο (π.χ. βίντεο, εικόνες, άρθρα κ.λπ.). Το ενσωματωμένο περιεχόμενο από άλλους ιστότοπους συμπεριφέρεται με τον ίδιο ακριβώς τρόπο όπως και αν ο επισκέπτης επισκέφθηκε τον άλλο ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Αυτοί οι ιστότοποι ενδέχεται να συλλέγουν δεδομένα για εσάς, χρησιμοποιούν cookies, ενσωματώνουν επιπλέον παρακολούθηση τρίτου μέρους και να παρακολουθούν την αλληλεπίδρασή σας με αυτό το περιλαμβανόμενο περιεχόμενο, συμπεριλαμβανομένης της ανίχνευσης της αλληλεπίδρασής σας με το περιλαμβανόμενο περιεχόμενο, εάν έχετε λογαριασμό και έχετε συνδεθεί στον συγκεκριμένο ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Με ποιούς μοιραζόμαστε τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Αν έχετε ζητήσει επαναπροσδιορισμό συνθηματικού, η διεύθυνση IP θα περιλαμβάνεται στο email επαναπροσδιορισμού.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Για πόσο καιρό διατηρούμε τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν αφήσετε ένα σχόλιο, το σχόλιο και τα μεταδεδομένα του διατηρούνται επ\' αόριστον. Αυτό γίνεται ώστε να μπορούμε να αναγνωρίζουμε και να εγκρίνουμε αυτόματα τα σχόλια που ακολουθούν, αντί να τα κρατάμε σε ουρά συντονισμού.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Για χρήστες που εγγράφονται στον ιστότοπο μας, αποθηκεύουμε επίσης τα προσωπικά δεδομένα που καταχωρούν στο προφίλ χρήστη τους. Όλοι οι χρήστες μπορούν να βλέπουν, να επεξεργάζονται ή να διαγράφουν τα προσωπικά δεδομένα τους ανά πάσα στιγμή (εκτός από το να μπορούν να αλλάξουν το όνομα χρήστη τους). Οι διαχειριστές του παρόντος ιστότοπου μπορεί επίσης να βλέπουν και να επεξεργάζονται αυτές τις πληροφορίες.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ποια δικαιώματα έχετε στα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν έχετε λογαριασμό σε αυτόν τον ιστότοπο ή έχετε αφήσει σχόλια, μπορείτε να ζητήσετε να λάβετε ένα εξαγόμενο αρχείο των προσωπικών δεδομένων που διατηρούμε για εσάς, συμπεριλαμβανομένων τυχόν δεδομένων που έχετε παράσχει σε εμάς. Μπορείτε επίσης να αιτηθείτε να διαγράψουμε τα προσωπικά δεδομένα που διατηρούμε για εσάς. Αυτό δεν περιλαμβάνει δεδομένα που είμαστε υποχρεωμένοι να τηρούμε για διοικητικούς, νομικούς ή λόγους ασφαλείας.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Πού αποστέλλονται τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Τα σχόλια επισκεπτών ενδέχεται να ελέγχονται μέσω ενός αυτοματοποιημένου συστήματος ανεπιθύμητης αλληλογραφίας.</p><!-- /wp:paragraph -->', 'Πολιτική απορρήτου', '', 'inherit', 'closed', 'closed', '', '3-revision-v1', '', '', '2024-04-18 19:31:22', '2024-04-18 16:31:22', '', 3, 'http://localhost/choose-life/?p=8', 0, 'revision', '', 0),
(3, 1, '2024-04-08 17:14:26', '2024-04-08 14:14:26', '<!-- wp:heading --><h2>Ποιοί είμαστε</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Η διεύθυνση της σελίδας είναι: http://localhost/choose-life.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Σχόλια</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Όταν οι επισκέπτες αφήνουν σχόλια στον ιστότοπο, συλλέγουμε τα δεδομένα που εμφανίζονται στη φόρμα σχολίων όπως επίσης τη διεύθυνση IP του επισκέπτη και τη συμβολοσειρά του χρήστη του προγράμματος περιήγησης ώστε να βοηθήσουμε στην ανίχνευση ανεπιθύμητων μηνυμάτων.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Μια ανωνυμοποιημένη συμβολοσειρά που δημιουργήθηκε από τη διεύθυνση ηλεκτρονικού ταχυδρομείου σας (επίσης αποκαλούμενη \"hash\") ενδέχεται να παρασχεθεί στην υπηρεσία Gravatar για να δει αν τη χρησιμοποιείτε. Η πολιτική απορρήτου της υπηρεσίας Gravatar διατίθεται εδώ: https://automattic.com/privacy/. Μετά την έγκριση του σχολίου σας, η εικόνα του προφίλ σας είναι ορατή στο κοινό μέσα στο πλαίσιο του σχολίου σας.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Πολυμέσα</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν μεταφορτώνετε εικόνες στον ιστότοπο, θα πρέπει να αποφύγετε τη μεταφόρτωση εικόνων με ενσωματωμένα δεδομένα τοποθεσίας (EXIF GPS). Οι επισκέπτες του ιστότοπου μπορούν να πραγματοποιήσουν λήψη και εξαγωγή οποιωνδήποτε δεδομένων τοποθεσίας από εικόνες στον ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Cookies</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Αν αφήσετε ένα σχόλιο στον ιστότοπό μας, μπορείτε να επιλέξετε να αποθηκεύσετε το όνομα, τη διεύθυνση ηλεκτρονικού ταχυδρομείου και τον ιστότοπό σας σε cookies. Αυτά είναι για την δική σας ευκολία, έτσι ώστε να μην χρειάζεται να συμπληρώσετε τα στοιχεία σας πάλι όταν αφήσετε ένα άλλο σχόλιο. Αυτά τα cookies θα διαρκέσουν για ένα έτος.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Αν έχετε έναν λογαριασμό και συνδεθείτε στον ιστότοπο, θα δημιουργήσουμε ένα προσωρινό cookie για να προσδιορίσουμε αν ο φυλλομετρητής σας δέχεται cookies. Το cookie δεν περιέχει προσωπικές πληροφορίες και θα διαγράφει μόλις κλείσετε τον φυλλομετρητή σας.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Όταν συνδεθείτε, θα δημιουργήσουμε επίσης διάφορα cookies για να αποθηκεύσετε τις πληροφορίες σύνδεσης και τις επιλογές οθόνης. Τα cookie εισόδου διαρκούν για δύο ημέρες και τα cookie επιλογών οθόνης διαρκούν για ένα χρόνο. Αν επιλέξετε &quot;Να με θυμάσαι&quot;, η σύνδεσή σας θα παραμείνει για δύο εβδομάδες. Αν αποσυνδεθείτε από το λογαριασμό σας, τα cookie σύνδεσης θα καταργηθούν.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Εάν επεξεργαστείτε ή δημοσιεύσετε ένα άρθρο, ένα επιπλέον cookie θα αποθηκευτεί στο πρόγραμμα περιήγησης. Αυτό το cookie δεν περιλαμβάνει προσωπικά δεδομένα και υποδεικνύει απλώς το post ID του άρθρου που μόλις επεξεργαστήκατε. Λήγει μετά από 1 ημέρα.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ενσωματωμένο περιεχόμενο από άλλους ιστότοπους</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Τα άρθρα σε αυτόν τον ιστότοπο ενδέχεται να περιλαμβάνουν ενσωματωμένο περιεχόμενο (π.χ. βίντεο, εικόνες, άρθρα κ.λπ.). Το ενσωματωμένο περιεχόμενο από άλλους ιστότοπους συμπεριφέρεται με τον ίδιο ακριβώς τρόπο όπως και αν ο επισκέπτης επισκέφθηκε τον άλλο ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Αυτοί οι ιστότοποι ενδέχεται να συλλέγουν δεδομένα για εσάς, χρησιμοποιούν cookies, ενσωματώνουν επιπλέον παρακολούθηση τρίτου μέρους και να παρακολουθούν την αλληλεπίδρασή σας με αυτό το περιλαμβανόμενο περιεχόμενο, συμπεριλαμβανομένης της ανίχνευσης της αλληλεπίδρασής σας με το περιλαμβανόμενο περιεχόμενο, εάν έχετε λογαριασμό και έχετε συνδεθεί στον συγκεκριμένο ιστότοπο.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Με ποιούς μοιραζόμαστε τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Αν έχετε ζητήσει επαναπροσδιορισμό συνθηματικού, η διεύθυνση IP θα περιλαμβάνεται στο email επαναπροσδιορισμού.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Για πόσο καιρό διατηρούμε τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν αφήσετε ένα σχόλιο, το σχόλιο και τα μεταδεδομένα του διατηρούνται επ\' αόριστον. Αυτό γίνεται ώστε να μπορούμε να αναγνωρίζουμε και να εγκρίνουμε αυτόματα τα σχόλια που ακολουθούν, αντί να τα κρατάμε σε ουρά συντονισμού.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Για χρήστες που εγγράφονται στον ιστότοπο μας, αποθηκεύουμε επίσης τα προσωπικά δεδομένα που καταχωρούν στο προφίλ χρήστη τους. Όλοι οι χρήστες μπορούν να βλέπουν, να επεξεργάζονται ή να διαγράφουν τα προσωπικά δεδομένα τους ανά πάσα στιγμή (εκτός από το να μπορούν να αλλάξουν το όνομα χρήστη τους). Οι διαχειριστές του παρόντος ιστότοπου μπορεί επίσης να βλέπουν και να επεξεργάζονται αυτές τις πληροφορίες.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Ποια δικαιώματα έχετε στα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Εάν έχετε λογαριασμό σε αυτόν τον ιστότοπο ή έχετε αφήσει σχόλια, μπορείτε να ζητήσετε να λάβετε ένα εξαγόμενο αρχείο των προσωπικών δεδομένων που διατηρούμε για εσάς, συμπεριλαμβανομένων τυχόν δεδομένων που έχετε παράσχει σε εμάς. Μπορείτε επίσης να αιτηθείτε να διαγράψουμε τα προσωπικά δεδομένα που διατηρούμε για εσάς. Αυτό δεν περιλαμβάνει δεδομένα που είμαστε υποχρεωμένοι να τηρούμε για διοικητικούς, νομικούς ή λόγους ασφαλείας.</p><!-- /wp:paragraph --><!-- wp:heading --><h2>Πού αποστέλλονται τα δεδομένα σας</h2><!-- /wp:heading --><!-- wp:paragraph --><p><strong class=\"privacy-policy-tutorial\">Προτεινόμενο κείμενο: </strong>Τα σχόλια επισκεπτών ενδέχεται να ελέγχονται μέσω ενός αυτοματοποιημένου συστήματος ανεπιθύμητης αλληλογραφίας.</p><!-- /wp:paragraph -->', 'Πολιτική απορρήτου', '', 'publish', 'closed', 'closed', '', '%cf%80%ce%bf%ce%bb%ce%b9%cf%84%ce%b9%ce%ba%ce%ae-%ce%b1%cf%80%ce%bf%cf%81%cf%81%ce%ae%cf%84%ce%bf%cf%85', '', '', '2024-04-18 19:31:22', '2024-04-18 16:31:22', '', 0, 'http://localhost/choose-life/?page_id=3', 0, 'page', '', 0),
(19, 1, '2024-04-19 13:13:43', '2024-04-19 10:13:43', '', 'My Account', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2024-04-19 13:13:43', '2024-04-19 10:13:43', '', 18, 'http://localhost/choose-life/?p=19', 0, 'revision', '', 0),
(5, 1, '2024-04-08 17:18:18', '2024-04-08 14:18:18', '<label> Το όνομά σας\n    [text* your-name autocomplete:name] </label>\n\n<label> Το email σας\n    [email* your-email autocomplete:email] </label>\n\n<label> Θέμα\n    [text* your-subject] </label>\n\n<label> Το μήνυμά σας (προαιρετικό)\n    [textarea your-message] </label>\n\n[submit \"Υποβολή\"]\n[_site_title] \"[your-subject]\"\n[_site_title] <apostolis.kyromitis@novidea.gr>\nΑπό: [your-name] [your-email]\nΘέμα: [your-subject]\n\nΣώμα μηνύματος:\n[your-message]\n\n-- \nThis is a notification that a contact form was submitted on your website ([_site_title] [_site_url]).\n[_site_admin_email]\nReply-To: [your-email]\n\n0\n0\n\n[_site_title] \"[your-subject]\"\n[_site_title] <apostolis.kyromitis@novidea.gr>\nΣώμα μηνύματος:\n[your-message]\n\n-- \nThis email is a receipt for your contact form submission on our website ([_site_title] [_site_url]) in which your email address was used. If that was not you, please ignore this message.\n[your-email]\nReply-To: [_site_admin_email]\n\n0\n0\nΕυχαριστούμε για το μήνυμά σας. Στάλθηκε με επιτυχία.\nΥπήρξε σφάλμα κατά την αποστολή. Δοκιμάστε ξανά αργότερα.\nΈνα ή περισσότερα πεδία έχουν σφάλματα. Ελέγξτε και ξαναδοκιμάστε.\nΥπήρξε σφάλμα κατά την αποστολή. Δοκιμάστε ξανά αργότερα.\nΠρέπει να αποδεχτείτε τους όρους χρήσης πριν στείλετε το μήνυμα.\nΠαρακαλώ συμπληρώστε αυτό το πεδίο.\nThis field has a too long input.\nThis field has a too short input.\nΥπήρξε άγνωστο σφάλμα κατά τη μεταφόρτωση του αρχείου.\nΔεν επιτρέπεται η μεταφόρτωση τέτοιου τύπου αρχείου.\nThe uploaded file is too large.\nΥπήρξε σφάλμα κατά τη μεταφόρτωση του αρχείου.', 'Φόρμα επικοινωνίας 1', '', 'publish', 'closed', 'closed', '', '%cf%86%cf%8c%cf%81%ce%bc%ce%b1-%ce%b5%cf%80%ce%b9%ce%ba%ce%bf%ce%b9%ce%bd%cf%89%ce%bd%ce%af%ce%b1%cf%82-1', '', '', '2024-04-08 17:18:18', '2024-04-08 14:18:18', '', 0, 'http://localhost/choose-life/?post_type=wpcf7_contact_form&p=5', 0, 'wpcf7_contact_form', '', 0),
(9, 1, '2024-04-18 19:31:30', '2024-04-18 16:31:30', ' ', '', '', 'publish', 'closed', 'closed', '', '9', '', '', '2024-04-18 19:31:30', '2024-04-18 16:31:30', '', 0, 'http://localhost/choose-life/?p=9', 1, 'nav_menu_item', '', 0),
(10, 1, '2024-04-19 13:46:07', '2024-04-18 16:32:41', '', 'ΣΧΕΤΙΚΑ ΜΕ ΕΜΑΣ', '', 'publish', 'closed', 'closed', '', '%cf%83%cf%87%ce%b5%cf%84%ce%b9%ce%ba%ce%b1-%ce%bc%ce%b5-%ce%b5%ce%bc%ce%b1%cf%83', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=10', 1, 'nav_menu_item', '', 0),
(11, 1, '2024-04-19 13:46:07', '2024-04-18 16:32:41', '', 'ΓΙΝΕ ΕΘΕΛΟΝΤΗΣ ΔΟΤΗΣ', '', 'publish', 'closed', 'closed', '', '%ce%b3%ce%b9%ce%bd%ce%b5-%ce%b5%ce%b8%ce%b5%ce%bb%ce%bf%ce%bd%cf%84%ce%b7%cf%83-%ce%b4%ce%bf%cf%84%ce%b7%cf%83', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=11', 2, 'nav_menu_item', '', 0),
(12, 1, '2024-04-19 13:46:07', '2024-04-18 16:32:41', '', 'ΤΟ ΙΝΣΤΙΤΟΥΤΟ', '', 'publish', 'closed', 'closed', '', '%cf%84%ce%bf-%ce%b9%ce%bd%cf%83%cf%84%ce%b9%cf%84%ce%bf%cf%85%cf%84%ce%bf', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=12', 3, 'nav_menu_item', '', 0),
(20, 1, '2024-04-19 13:13:52', '2024-04-19 10:13:52', '', 'Checkout', '', 'publish', 'closed', 'closed', '', 'checkout', '', '', '2024-04-29 13:03:33', '2024-04-29 10:03:33', '', 0, 'http://localhost/choose-life/?page_id=20', 0, 'page', '', 0),
(18, 1, '2024-04-19 13:13:43', '2024-04-19 10:13:43', '', 'My Account', '', 'publish', 'closed', 'closed', '', 'my-account', '', '', '2024-05-01 14:47:19', '2024-05-01 11:47:19', '', 0, 'http://localhost/choose-life/?page_id=18', 0, 'page', '', 0),
(119, 1, '2024-05-02 11:25:27', '0000-00-00 00:00:00', '', 'Auto Draft', '', 'auto-draft', 'closed', 'closed', '', '', '', '', '2024-05-02 11:25:27', '0000-00-00 00:00:00', '', 0, 'http://localhost/choose-life/?p=119', 0, 'post', '', 0),
(116, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Completed donations title', 'completed_donations_title', 'publish', 'closed', 'closed', '', 'field_66322acc43e26', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=116', 6, 'acf-field', '', 0),
(16, 1, '2024-04-19 13:46:07', '2024-04-18 16:33:08', '', 'ΕΠΙΚΟΙΝΩΝΙΑ', '', 'publish', 'closed', 'closed', '', '%ce%b5%cf%80%ce%b9%ce%ba%ce%bf%ce%b9%ce%bd%cf%89%ce%bd%ce%b9%ce%b1', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=16', 5, 'nav_menu_item', '', 0),
(118, 1, '2024-05-01 14:47:19', '2024-05-01 11:47:19', '', 'My Account', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2024-05-01 14:47:19', '2024-05-01 11:47:19', '', 18, 'http://localhost/choose-life/?p=118', 0, 'revision', '', 0),
(17, 1, '2024-04-19 13:46:07', '2024-04-18 16:40:11', '', 'ΚΑΝΕ ΜΙΑ ΔΩΡΕΑ', '', 'publish', 'closed', 'closed', '', '%ce%ba%ce%b1%ce%bd%ce%b5-%ce%bc%ce%b9%ce%b1-%ce%b4%cf%89%cf%81%ce%b5%ce%b1-2', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=17', 4, 'nav_menu_item', '', 0),
(21, 1, '2024-04-19 13:13:52', '2024-04-19 10:13:52', '', 'Checkout', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2024-04-19 13:13:52', '2024-04-19 10:13:52', '', 20, 'http://localhost/choose-life/?p=21', 0, 'revision', '', 0),
(22, 1, '2024-04-19 13:46:07', '2024-04-19 10:46:07', ' ', '', '', 'publish', 'closed', 'closed', '', '22', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=22', 6, 'nav_menu_item', '', 0),
(23, 1, '2024-04-19 13:46:07', '2024-04-19 10:46:07', ' ', '', '', 'publish', 'closed', 'closed', '', '23', '', '', '2024-04-19 13:46:07', '2024-04-19 10:46:07', '', 0, 'http://localhost/choose-life/?p=23', 7, 'nav_menu_item', '', 0),
(25, 1, '2024-04-23 17:36:55', '2024-04-23 14:36:55', '<p style=\"text-align: center;\">Lorem ipsum dolor sit amet consectetur. Gravida senectus nec sem tincidunt leo amet ultricies molestie erat. Amet urna ipsum phasellus hac vitae sed. Nisl duis.</p>', 'My Account', '', 'inherit', 'closed', 'closed', '', '18-revision-v1', '', '', '2024-04-23 17:36:55', '2024-04-23 14:36:55', '', 18, 'http://localhost/choose-life/?p=25', 0, 'revision', '', 0),
(26, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"user_role\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:3:\"all\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'User fields', 'user-fields', 'publish', 'closed', 'closed', '', 'group_66291a6082d5f', '', '', '2024-04-29 15:02:07', '2024-04-29 12:02:07', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=26', 0, 'acf-field-group', '', 0),
(27, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'First name', 'first_name', 'publish', 'closed', 'closed', '', 'field_66291a6165fac', '', '', '2024-04-24 17:50:19', '2024-04-24 14:50:19', '', 26, 'http://localhost/choose-life/?post_type=acf-field&p=27', 0, 'acf-field', '', 0),
(28, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Last name', 'last_name', 'publish', 'closed', 'closed', '', 'field_66291ad165fad', '', '', '2024-04-24 17:50:19', '2024-04-24 14:50:19', '', 26, 'http://localhost/choose-life/?post_type=acf-field&p=28', 1, 'acf-field', '', 0),
(29, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Telephone', 'telephone', 'publish', 'closed', 'closed', '', 'field_66291af065fae', '', '', '2024-04-24 18:17:54', '2024-04-24 15:17:54', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=29', 2, 'acf-field', '', 0),
(30, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing address', 'billing_address', 'publish', 'closed', 'closed', '', 'field_66291afa65faf', '', '', '2024-04-24 18:17:54', '2024-04-24 15:17:54', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=30', 3, 'acf-field', '', 0),
(31, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing city', 'billing_city', 'publish', 'closed', 'closed', '', 'field_66291b1a65fb0', '', '', '2024-04-24 18:17:54', '2024-04-24 15:17:54', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=31', 4, 'acf-field', '', 0),
(32, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing postal code', 'billing_postal_code', 'publish', 'closed', 'closed', '', 'field_66291b2b65fb1', '', '', '2024-04-24 18:17:54', '2024-04-24 15:17:54', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=32', 5, 'acf-field', '', 0),
(33, 1, '2024-04-24 17:50:19', '2024-04-24 14:50:19', 'a:15:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"country\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:249:{s:2:\"SH\";s:19:\"Αγία Ελένη\";s:2:\"LC\";s:21:\"Αγία Λουκία\";s:2:\"BL\";s:35:\"Άγιος Βαρθολομαίος\";s:2:\"VC\";s:57:\"Άγιος Βικέντιος και Γρεναδίνες\";s:2:\"SM\";s:25:\"Άγιος Μαρίνος\";s:2:\"MF\";s:55:\"Άγιος Μαρτίνος (Γαλλικό τμήμα)\";s:2:\"SX\";s:59:\"Άγιος Μαρτίνος (Ολλανδικό τμήμα)\";s:2:\"AO\";s:12:\"Αγκόλα\";s:2:\"AZ\";s:24:\"Αζερμπαϊτζάν\";s:2:\"EG\";s:16:\"Αίγυπτος\";s:2:\"ET\";s:16:\"Αιθιοπία\";s:2:\"HT\";s:8:\"Αϊτή\";s:2:\"CI\";s:33:\"Ακτή Ελεφαντοστού\";s:2:\"AL\";s:14:\"Αλβανία\";s:2:\"DZ\";s:14:\"Αλγερία\";s:2:\"VI\";s:52:\"Αμερικανικές Παρθένες Νήσοι\";s:2:\"AS\";s:33:\"Αμερικανική Σαμόα\";s:2:\"AI\";s:18:\"Ανγκουίλα\";s:2:\"AD\";s:12:\"Ανδόρα\";s:2:\"AQ\";s:20:\"Ανταρκτική\";s:2:\"AG\";s:48:\"Αντίγκουα και Μπαρμπούντα\";s:2:\"UM\";s:50:\"Απομακρυσμένες Νησίδες ΗΠΑ\";s:2:\"AR\";s:18:\"Αργεντινή\";s:2:\"AM\";s:14:\"Αρμενία\";s:2:\"AW\";s:14:\"Αρούμπα\";s:2:\"AU\";s:18:\"Αυστραλία\";s:2:\"AT\";s:14:\"Αυστρία\";s:2:\"AF\";s:20:\"Αφγανιστάν\";s:2:\"VU\";s:18:\"Βανουάτου\";s:2:\"VA\";s:16:\"Βατικανό\";s:2:\"BE\";s:12:\"Βέλγιο\";s:2:\"VE\";s:20:\"Βενεζουέλα\";s:2:\"BM\";s:18:\"Βερμούδες\";s:2:\"VN\";s:14:\"Βιετνάμ\";s:2:\"BO\";s:14:\"Βολιβία\";s:2:\"KP\";s:23:\"Βόρεια Κορέα\";s:2:\"MK\";s:31:\"Βόρεια Μακεδονία\";s:2:\"BA\";s:35:\"Βοσνία - Ερζεγοβίνη\";s:2:\"BG\";s:18:\"Βουλγαρία\";s:2:\"BR\";s:16:\"Βραζιλία\";s:2:\"IO\";s:59:\"Βρετανικά Εδάφη Ινδικού Ωκεανού\";s:2:\"VG\";s:48:\"Βρετανικές Παρθένες Νήσοι\";s:2:\"FR\";s:12:\"Γαλλία\";s:2:\"TF\";s:36:\"Γαλλικά Νότια Εδάφη\";s:2:\"GF\";s:29:\"Γαλλική Γουιάνα\";s:2:\"PF\";s:33:\"Γαλλική Πολυνησία\";s:2:\"DE\";s:16:\"Γερμανία\";s:2:\"GE\";s:14:\"Γεωργία\";s:2:\"GI\";s:18:\"Γιβραλτάρ\";s:2:\"GM\";s:14:\"Γκάμπια\";s:2:\"GA\";s:14:\"Γκαμπόν\";s:2:\"GH\";s:10:\"Γκάνα\";s:2:\"GG\";s:14:\"Γκέρνζι\";s:2:\"GU\";s:12:\"Γκουάμ\";s:2:\"GP\";s:22:\"Γουαδελούπη\";s:2:\"WF\";s:38:\"Γουάλις και Φουτούνα\";s:2:\"GT\";s:20:\"Γουατεμάλα\";s:2:\"GY\";s:14:\"Γουιάνα\";s:2:\"GN\";s:14:\"Γουινέα\";s:2:\"GW\";s:29:\"Γουινέα Μπισάου\";s:2:\"GD\";s:14:\"Γρενάδα\";s:2:\"GL\";s:20:\"Γροιλανδία\";s:2:\"DK\";s:10:\"Δανία\";s:2:\"DO\";s:41:\"Δομινικανή Δημοκρατία\";s:2:\"EH\";s:25:\"Δυτική Σαχάρα\";s:2:\"SV\";s:21:\"Ελ Σαλβαδόρ\";s:2:\"CH\";s:14:\"Ελβετία\";s:2:\"GR\";s:12:\"Ελλάδα\";s:2:\"ER\";s:16:\"Ερυθραία\";s:2:\"EE\";s:14:\"Εσθονία\";s:2:\"ZM\";s:12:\"Ζάμπια\";s:2:\"ZW\";s:20:\"Ζιμπάμπουε\";s:2:\"AE\";s:44:\"Ηνωμένα Αραβικά Εμιράτα\";s:2:\"US\";s:35:\"Ηνωμένες Πολιτείες\";s:2:\"GB\";s:31:\"Ηνωμένο Βασίλειο\";s:2:\"JP\";s:14:\"Ιαπωνία\";s:2:\"IN\";s:10:\"Ινδία\";s:2:\"ID\";s:18:\"Ινδονησία\";s:2:\"JO\";s:16:\"Ιορδανία\";s:2:\"IQ\";s:8:\"Ιράκ\";s:2:\"IR\";s:8:\"Ιράν\";s:2:\"IE\";s:16:\"Ιρλανδία\";s:2:\"GQ\";s:33:\"Ισημερινή Γουινέα\";s:2:\"EC\";s:20:\"Ισημερινός\";s:2:\"IS\";s:16:\"Ισλανδία\";s:2:\"ES\";s:14:\"Ισπανία\";s:2:\"IL\";s:12:\"Ισραήλ\";s:2:\"IT\";s:12:\"Ιταλία\";s:2:\"KZ\";s:18:\"Καζακστάν\";s:2:\"CM\";s:16:\"Καμερούν\";s:2:\"KH\";s:16:\"Καμπότζη\";s:2:\"CA\";s:14:\"Καναδάς\";s:2:\"QA\";s:10:\"Κατάρ\";s:2:\"CF\";s:53:\"Κεντροαφρικανική Δημοκρατία\";s:2:\"KE\";s:10:\"Κένυα\";s:2:\"CN\";s:8:\"Κίνα\";s:2:\"KG\";s:18:\"Κιργιστάν\";s:2:\"KI\";s:18:\"Κιριμπάτι\";s:2:\"CO\";s:16:\"Κολομβία\";s:2:\"KM\";s:14:\"Κομόρες\";s:2:\"CD\";s:29:\"Κονγκό - Κινσάσα\";s:2:\"CG\";s:33:\"Κονγκό - Μπραζαβίλ\";s:2:\"CR\";s:19:\"Κόστα Ρίκα\";s:2:\"CU\";s:10:\"Κούβα\";s:2:\"KW\";s:14:\"Κουβέιτ\";s:2:\"CW\";s:16:\"Κουρασάο\";s:2:\"HR\";s:14:\"Κροατία\";s:2:\"CY\";s:12:\"Κύπρος\";s:2:\"LA\";s:8:\"Λάος\";s:2:\"LS\";s:12:\"Λεσότο\";s:2:\"LV\";s:14:\"Λετονία\";s:2:\"BY\";s:20:\"Λευκορωσία\";s:2:\"LB\";s:14:\"Λίβανος\";s:2:\"LR\";s:14:\"Λιβερία\";s:2:\"LY\";s:10:\"Λιβύη\";s:2:\"LT\";s:18:\"Λιθουανία\";s:2:\"LI\";s:22:\"Λιχτενστάιν\";s:2:\"LU\";s:24:\"Λουξεμβούργο\";s:2:\"YT\";s:12:\"Μαγιότ\";s:2:\"MG\";s:22:\"Μαδαγασκάρη\";s:2:\"MO\";s:28:\"Μακάο ΕΔΠ Κίνας\";s:2:\"MY\";s:16:\"Μαλαισία\";s:2:\"MW\";s:14:\"Μαλάουι\";s:2:\"MV\";s:16:\"Μαλδίβες\";s:2:\"ML\";s:8:\"Μάλι\";s:2:\"MT\";s:10:\"Μάλτα\";s:2:\"MA\";s:12:\"Μαρόκο\";s:2:\"MQ\";s:18:\"Μαρτινίκα\";s:2:\"MU\";s:18:\"Μαυρίκιος\";s:2:\"MR\";s:20:\"Μαυριτανία\";s:2:\"ME\";s:22:\"Μαυροβούνιο\";s:2:\"MX\";s:12:\"Μεξικό\";s:2:\"MM\";s:33:\"Μιανμάρ (Βιρμανία)\";s:2:\"FM\";s:20:\"Μικρονησία\";s:2:\"MN\";s:16:\"Μογγολία\";s:2:\"MZ\";s:18:\"Μοζαμβίκη\";s:2:\"MD\";s:16:\"Μολδαβία\";s:2:\"MC\";s:12:\"Μονακό\";s:2:\"MS\";s:16:\"Μονσεράτ\";s:2:\"BD\";s:24:\"Μπανγκλαντές\";s:2:\"BB\";s:24:\"Μπαρμπέιντος\";s:2:\"BS\";s:16:\"Μπαχάμες\";s:2:\"BH\";s:16:\"Μπαχρέιν\";s:2:\"BZ\";s:12:\"Μπελίζ\";s:2:\"BJ\";s:12:\"Μπενίν\";s:2:\"BW\";s:20:\"Μποτσουάνα\";s:2:\"BF\";s:27:\"Μπουρκίνα Φάσο\";s:2:\"BI\";s:20:\"Μπουρούντι\";s:2:\"BT\";s:14:\"Μπουτάν\";s:2:\"BN\";s:16:\"Μπρουνέι\";s:2:\"NA\";s:16:\"Ναμίμπια\";s:2:\"NR\";s:14:\"Ναουρού\";s:2:\"NZ\";s:23:\"Νέα Ζηλανδία\";s:2:\"NC\";s:25:\"Νέα Καληδονία\";s:2:\"NP\";s:10:\"Νεπάλ\";s:2:\"MP\";s:42:\"Νήσοι Βόρειες Μαριάνες\";s:2:\"KY\";s:23:\"Νήσοι Κέιμαν\";s:2:\"CC\";s:38:\"Νήσοι Κόκος (Κίλινγκ)\";s:2:\"CK\";s:19:\"Νήσοι Κουκ\";s:2:\"MH\";s:23:\"Νήσοι Μάρσαλ\";s:2:\"GS\";s:75:\"Νήσοι Νότια Γεωργία και Νότιες Σάντουιτς\";s:2:\"AX\";s:21:\"Νήσοι Όλαντ\";s:2:\"PN\";s:25:\"Νήσοι Πίτκερν\";s:2:\"SB\";s:31:\"Νήσοι Σολομώντος\";s:2:\"TC\";s:41:\"Νήσοι Τερκς και Κάικος\";s:2:\"FO\";s:23:\"Νήσοι Φερόες\";s:2:\"FK\";s:25:\"Νήσοι Φόκλαντ\";s:2:\"HM\";s:51:\"Νήσοι Χερντ και Μακντόναλντ\";s:2:\"BV\";s:23:\"Νήσος Μπουβέ\";s:2:\"NF\";s:25:\"Νήσος Νόρφολκ\";s:2:\"IM\";s:24:\"Νήσος του Μαν\";s:2:\"CX\";s:44:\"Νήσος των Χριστουγέννων\";s:2:\"NE\";s:14:\"Νίγηρας\";s:2:\"NG\";s:14:\"Νιγηρία\";s:2:\"NI\";s:20:\"Νικαράγουα\";s:2:\"NU\";s:10:\"Νιούε\";s:2:\"NO\";s:16:\"Νορβηγία\";s:2:\"ZA\";s:23:\"Νότια Αφρική\";s:2:\"KR\";s:21:\"Νότια Κορέα\";s:2:\"SS\";s:23:\"Νότιο Σουδάν\";s:2:\"DM\";s:18:\"Ντομίνικα\";s:2:\"NL\";s:16:\"Ολλανδία\";s:2:\"BQ\";s:37:\"Ολλανδία Καραϊβικής\";s:2:\"OM\";s:8:\"Ομάν\";s:2:\"HN\";s:14:\"Ονδούρα\";s:2:\"HU\";s:16:\"Ουγγαρία\";s:2:\"UG\";s:16:\"Ουγκάντα\";s:2:\"UZ\";s:24:\"Ουζμπεκιστάν\";s:2:\"UA\";s:16:\"Ουκρανία\";s:2:\"UY\";s:20:\"Ουρουγουάη\";s:2:\"PK\";s:16:\"Πακιστάν\";s:2:\"PS\";s:37:\"Παλαιστινιακά Εδάφη\";s:2:\"PW\";s:12:\"Παλάου\";s:2:\"PA\";s:14:\"Παναμάς\";s:2:\"PG\";s:34:\"Παπούα Νέα Γουινέα\";s:2:\"PY\";s:18:\"Παραγουάη\";s:2:\"PE\";s:10:\"Περού\";s:2:\"PL\";s:14:\"Πολωνία\";s:2:\"PT\";s:20:\"Πορτογαλία\";s:2:\"PR\";s:23:\"Πουέρτο Ρίκο\";s:2:\"CV\";s:33:\"Πράσινο Ακρωτήριο\";s:2:\"RE\";s:14:\"Ρεϊνιόν\";s:2:\"RW\";s:14:\"Ρουάντα\";s:2:\"RO\";s:16:\"Ρουμανία\";s:2:\"RU\";s:10:\"Ρωσία\";s:2:\"WS\";s:10:\"Σαμόα\";s:2:\"ST\";s:39:\"Σάο Τομέ και Πρίνσιπε\";s:2:\"SA\";s:29:\"Σαουδική Αραβία\";s:2:\"SJ\";s:49:\"Σβάλμπαρντ και Γιαν Μαγιέν\";s:2:\"KN\";s:33:\"Σεν Κιτς και Νέβις\";s:2:\"PM\";s:37:\"Σεν Πιερ και Μικελόν\";s:2:\"SN\";s:16:\"Σενεγάλη\";s:2:\"RS\";s:12:\"Σερβία\";s:2:\"SC\";s:18:\"Σεϋχέλλες\";s:2:\"SG\";s:20:\"Σιγκαπούρη\";s:2:\"SL\";s:21:\"Σιέρα Λεόνε\";s:2:\"SK\";s:16:\"Σλοβακία\";s:2:\"SI\";s:16:\"Σλοβενία\";s:2:\"SO\";s:14:\"Σομαλία\";s:2:\"SZ\";s:22:\"Σουαζιλάνδη\";s:2:\"SD\";s:12:\"Σουδάν\";s:2:\"SE\";s:14:\"Σουηδία\";s:2:\"SR\";s:16:\"Σουρινάμ\";s:2:\"LK\";s:17:\"Σρι Λάνκα\";s:2:\"SY\";s:10:\"Συρία\";s:2:\"TW\";s:12:\"Ταϊβάν\";s:2:\"TH\";s:16:\"Ταϊλάνδη\";s:2:\"TZ\";s:16:\"Τανζανία\";s:2:\"TJ\";s:22:\"Τατζικιστάν\";s:2:\"JM\";s:16:\"Τζαμάικα\";s:2:\"JE\";s:12:\"Τζέρζι\";s:2:\"DJ\";s:18:\"Τζιμπουτί\";s:2:\"TL\";s:21:\"Τιμόρ-Λέστε\";s:2:\"TG\";s:10:\"Τόγκο\";s:2:\"TK\";s:16:\"Τοκελάου\";s:2:\"TO\";s:12:\"Τόνγκα\";s:2:\"TV\";s:16:\"Τουβαλού\";s:2:\"TR\";s:14:\"Τουρκία\";s:2:\"TM\";s:26:\"Τουρκμενιστάν\";s:2:\"TT\";s:44:\"Τρινιντάντ και Τομπάγκο\";s:2:\"TD\";s:10:\"Τσαντ\";s:2:\"CZ\";s:12:\"Τσεχία\";s:2:\"TN\";s:14:\"Τυνησία\";s:2:\"YE\";s:12:\"Υεμένη\";s:2:\"PH\";s:20:\"Φιλιππίνες\";s:2:\"FI\";s:18:\"Φινλανδία\";s:2:\"FJ\";s:10:\"Φίτζι\";s:2:\"CL\";s:8:\"Χιλή\";s:2:\"HK\";s:39:\"Χονγκ Κονγκ ΕΔΠ Κίνας\";}s:13:\"default_value\";b:0;s:13:\"return_format\";s:5:\"value\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;s:2:\"ui\";i:0;s:6:\"layout\";s:8:\"vertical\";s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}', 'Billing country', 'billing_country', 'publish', 'closed', 'closed', '', 'field_66291b4f65fb2', '', '', '2024-04-24 19:55:22', '2024-04-24 16:55:22', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=33', 6, 'acf-field', '', 0),
(35, 1, '2024-04-25 18:50:47', '2024-04-25 15:50:47', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:12:\"options_page\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:22:\"theme-general-settings\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'General Options', 'general-options', 'publish', 'closed', 'closed', '', 'group_662a7baa20ef2', '', '', '2024-04-30 14:47:15', '2024-04-30 11:47:15', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=35', 0, 'acf-field-group', '', 0),
(36, 1, '2024-04-25 18:50:47', '2024-04-25 15:50:47', 'a:12:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:9:\"page_link\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"post_type\";a:1:{i:0;s:4:\"page\";}s:11:\"post_status\";s:0:\"\";s:8:\"taxonomy\";s:0:\"\";s:14:\"allow_archives\";i:0;s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;}', 'My account page', 'my_account_url', 'publish', 'closed', 'closed', '', 'field_662a7baa72a4f', '', '', '2024-04-29 15:30:53', '2024-04-29 12:30:53', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=36', 1, 'acf-field', '', 0),
(37, 1, '2024-04-26 13:20:26', '2024-04-26 10:20:26', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:13:\"post_template\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:22:\"templates/checkout.php\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'Template -- Checkout', 'template-checkout', 'publish', 'closed', 'closed', '', 'group_662b7f5733c1a', '', '', '2024-04-29 13:03:04', '2024-04-29 10:03:04', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=37', 0, 'acf-field-group', '', 0),
(38, 1, '2024-04-26 13:20:26', '2024-04-26 10:20:26', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Login / Register', 'login', 'publish', 'closed', 'closed', '', 'field_662b7f57591d4', '', '', '2024-04-26 13:20:26', '2024-04-26 10:20:26', '', 37, 'http://localhost/choose-life/?post_type=acf-field&p=38', 0, 'acf-field', '', 0),
(39, 1, '2024-04-26 13:20:26', '2024-04-26 10:20:26', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Title', 'login_register_title', 'publish', 'closed', 'closed', '', 'field_662b7f7c591d5', '', '', '2024-04-26 13:20:26', '2024-04-26 10:20:26', '', 37, 'http://localhost/choose-life/?post_type=acf-field&p=39', 1, 'acf-field', '', 0),
(40, 1, '2024-04-26 13:20:26', '2024-04-26 10:20:26', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:0;s:5:\"delay\";i:0;}', 'Content', 'login_register_content', 'publish', 'closed', 'closed', '', 'field_662b7f8b591d6', '', '', '2024-04-29 13:03:04', '2024-04-29 10:03:04', '', 37, 'http://localhost/choose-life/?post_type=acf-field&#038;p=40', 2, 'acf-field', '', 0),
(41, 1, '2024-04-26 13:46:43', '2024-04-26 10:46:43', '', 'Checkout', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2024-04-26 13:46:43', '2024-04-26 10:46:43', '', 20, 'http://localhost/choose-life/?p=41', 0, 'revision', '', 0),
(42, 1, '2024-04-29 13:03:04', '2024-04-29 10:03:04', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:3:\"top\";s:8:\"endpoint\";i:0;}', 'Complete Donation', 'complete_donation', 'publish', 'closed', 'closed', '', 'field_662f7028678a1', '', '', '2024-04-29 13:03:04', '2024-04-29 10:03:04', '', 37, 'http://localhost/choose-life/?post_type=acf-field&p=42', 3, 'acf-field', '', 0),
(43, 1, '2024-04-29 13:03:04', '2024-04-29 10:03:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Title', 'complete_donation_title', 'publish', 'closed', 'closed', '', 'field_662f7039678a2', '', '', '2024-04-29 13:03:04', '2024-04-29 10:03:04', '', 37, 'http://localhost/choose-life/?post_type=acf-field&p=43', 4, 'acf-field', '', 0),
(44, 1, '2024-04-29 13:03:04', '2024-04-29 10:03:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:0;s:5:\"delay\";i:0;}', 'Content', 'complete_donation_content', 'publish', 'closed', 'closed', '', 'field_662f703f678a3', '', '', '2024-04-29 13:03:04', '2024-04-29 10:03:04', '', 37, 'http://localhost/choose-life/?post_type=acf-field&p=44', 5, 'acf-field', '', 0),
(45, 1, '2024-04-29 13:03:33', '2024-04-29 10:03:33', '', 'Checkout', '', 'inherit', 'closed', 'closed', '', '20-revision-v1', '', '', '2024-04-29 13:03:33', '2024-04-29 10:03:33', '', 20, 'http://localhost/choose-life/?p=45', 0, 'revision', '', 0),
(46, 1, '2024-04-29 14:47:55', '2024-04-29 11:47:55', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:10:\"true_false\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"message\";s:0:\"\";s:13:\"default_value\";i:0;s:2:\"ui\";i:0;s:10:\"ui_on_text\";s:0:\"\";s:11:\"ui_off_text\";s:0:\"\";}', 'Marketing acceptance', 'marketing_acceptance', 'publish', 'closed', 'closed', '', 'field_662f88d568819', '', '', '2024-04-29 15:02:07', '2024-04-29 12:02:07', '', 26, 'http://localhost/choose-life/?post_type=acf-field&#038;p=46', 7, 'acf-field', '', 0),
(47, 1, '2024-04-29 15:30:53', '2024-04-29 12:30:53', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'General', '', 'publish', 'closed', 'closed', '', 'field_662f92a881404', '', '', '2024-04-29 15:31:15', '2024-04-29 12:31:15', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=47', 0, 'acf-field', '', 0),
(48, 1, '2024-04-29 15:30:53', '2024-04-29 12:30:53', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Payment', '', 'publish', 'closed', 'closed', '', 'field_662f92b581405', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=48', 3, 'acf-field', '', 0),
(49, 1, '2024-04-29 15:30:53', '2024-04-29 12:30:53', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:10:\"true_false\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"message\";s:15:\"Check to enable\";s:13:\"default_value\";i:0;s:2:\"ui\";i:0;s:10:\"ui_on_text\";s:0:\"\";s:11:\"ui_off_text\";s:0:\"\";}', 'Enable test environment', 'enable_test_environment', 'publish', 'closed', 'closed', '', 'field_662f92bd81406', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=49', 4, 'acf-field', '', 0),
(50, 1, '2024-04-29 15:30:53', '2024-04-29 12:30:53', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'MID', 'payment_mid', 'publish', 'closed', 'closed', '', 'field_662f92e681407', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=50', 5, 'acf-field', '', 0),
(51, 1, '2024-04-29 15:30:53', '2024-04-29 12:30:53', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Secret', 'payment_secret', 'publish', 'closed', 'closed', '', 'field_662f92ef81408', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=51', 6, 'acf-field', '', 0),
(52, 1, '2024-04-29 17:19:48', '2024-04-29 14:19:48', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Success donation email', 'success_donation_email', 'publish', 'closed', 'closed', '', 'field_662fac225e022', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=52', 7, 'acf-field', '', 0),
(53, 1, '2024-04-29 17:19:48', '2024-04-29 14:19:48', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Subject', 'thank_you_email_subject', 'publish', 'closed', 'closed', '', 'field_662fac3d5e023', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=53', 8, 'acf-field', '', 0),
(54, 1, '2024-04-29 17:19:48', '2024-04-29 14:19:48', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:1;s:5:\"delay\";i:0;}', 'Content', 'thank_you_email_content', 'publish', 'closed', 'closed', '', 'field_662fac775e024', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=54', 9, 'acf-field', '', 0),
(55, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:9:\"donations\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'Donation', 'donation', 'publish', 'closed', 'closed', '', 'group_662fae3b5b982', '', '', '2024-05-02 16:47:06', '2024-05-02 13:47:06', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=55', 0, 'acf-field-group', '', 0),
(56, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:14:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"select\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:3:{s:9:\"completed\";s:17:\"Payment completed\";s:7:\"pending\";s:15:\"Pending payment\";s:6:\"failed\";s:14:\"Payment failed\";}s:13:\"default_value\";s:7:\"pending\";s:13:\"return_format\";s:5:\"value\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;s:2:\"ui\";i:0;s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}', 'Status', 'donation_status', 'publish', 'closed', 'closed', '', 'field_662faf585f3fc', '', '', '2024-05-01 13:54:05', '2024-05-01 10:54:05', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=56', 0, 'acf-field', '', 0),
(57, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:14:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"select\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:2:{s:8:\"one-time\";s:16:\"One time payment\";s:9:\"recurring\";s:9:\"Recurring\";}s:13:\"default_value\";s:8:\"one-time\";s:13:\"return_format\";s:5:\"value\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;s:2:\"ui\";i:0;s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}', 'Type', 'donation_type', 'publish', 'closed', 'closed', '', 'field_662faf2a5f3fb', '', '', '2024-04-30 12:46:58', '2024-04-30 09:46:58', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=57', 1, 'acf-field', '', 0);
INSERT INTO `cp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES
(58, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:13:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"number\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:4:\"step\";s:0:\"\";s:7:\"prepend\";s:3:\"€\";s:6:\"append\";s:0:\"\";}', 'Donation amount', 'donation_amount', 'publish', 'closed', 'closed', '', 'field_662faf0c5f3fa', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=58', 5, 'acf-field', '', 0),
(59, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Email', 'email', 'publish', 'closed', 'closed', '', 'field_662fae585f3f4', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=59', 6, 'acf-field', '', 0),
(60, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'First name', 'first_name', 'publish', 'closed', 'closed', '', 'field_662fae3b5f3f2', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=60', 7, 'acf-field', '', 0),
(61, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Last name', 'last_name', 'publish', 'closed', 'closed', '', 'field_662fae515f3f3', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=61', 8, 'acf-field', '', 0),
(62, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Telephone', 'telephone', 'publish', 'closed', 'closed', '', 'field_662fae5d5f3f5', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=62', 9, 'acf-field', '', 0),
(63, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing address', 'billing_address', 'publish', 'closed', 'closed', '', 'field_662fae665f3f6', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=63', 10, 'acf-field', '', 0),
(64, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing city', 'billing_city', 'publish', 'closed', 'closed', '', 'field_662faec65f3f7', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=64', 11, 'acf-field', '', 0),
(65, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Billing postal code', 'billing_postal_code', 'publish', 'closed', 'closed', '', 'field_662faed85f3f8', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=65', 12, 'acf-field', '', 0),
(66, 1, '2024-04-29 17:32:45', '2024-04-29 14:32:45', 'a:15:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"country\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:249:{s:2:\"SH\";s:19:\"Αγία Ελένη\";s:2:\"LC\";s:21:\"Αγία Λουκία\";s:2:\"BL\";s:35:\"Άγιος Βαρθολομαίος\";s:2:\"VC\";s:57:\"Άγιος Βικέντιος και Γρεναδίνες\";s:2:\"SM\";s:25:\"Άγιος Μαρίνος\";s:2:\"MF\";s:55:\"Άγιος Μαρτίνος (Γαλλικό τμήμα)\";s:2:\"SX\";s:59:\"Άγιος Μαρτίνος (Ολλανδικό τμήμα)\";s:2:\"AO\";s:12:\"Αγκόλα\";s:2:\"AZ\";s:24:\"Αζερμπαϊτζάν\";s:2:\"EG\";s:16:\"Αίγυπτος\";s:2:\"ET\";s:16:\"Αιθιοπία\";s:2:\"HT\";s:8:\"Αϊτή\";s:2:\"CI\";s:33:\"Ακτή Ελεφαντοστού\";s:2:\"AL\";s:14:\"Αλβανία\";s:2:\"DZ\";s:14:\"Αλγερία\";s:2:\"VI\";s:52:\"Αμερικανικές Παρθένες Νήσοι\";s:2:\"AS\";s:33:\"Αμερικανική Σαμόα\";s:2:\"AI\";s:18:\"Ανγκουίλα\";s:2:\"AD\";s:12:\"Ανδόρα\";s:2:\"AQ\";s:20:\"Ανταρκτική\";s:2:\"AG\";s:48:\"Αντίγκουα και Μπαρμπούντα\";s:2:\"UM\";s:50:\"Απομακρυσμένες Νησίδες ΗΠΑ\";s:2:\"AR\";s:18:\"Αργεντινή\";s:2:\"AM\";s:14:\"Αρμενία\";s:2:\"AW\";s:14:\"Αρούμπα\";s:2:\"AU\";s:18:\"Αυστραλία\";s:2:\"AT\";s:14:\"Αυστρία\";s:2:\"AF\";s:20:\"Αφγανιστάν\";s:2:\"VU\";s:18:\"Βανουάτου\";s:2:\"VA\";s:16:\"Βατικανό\";s:2:\"BE\";s:12:\"Βέλγιο\";s:2:\"VE\";s:20:\"Βενεζουέλα\";s:2:\"BM\";s:18:\"Βερμούδες\";s:2:\"VN\";s:14:\"Βιετνάμ\";s:2:\"BO\";s:14:\"Βολιβία\";s:2:\"KP\";s:23:\"Βόρεια Κορέα\";s:2:\"MK\";s:31:\"Βόρεια Μακεδονία\";s:2:\"BA\";s:35:\"Βοσνία - Ερζεγοβίνη\";s:2:\"BG\";s:18:\"Βουλγαρία\";s:2:\"BR\";s:16:\"Βραζιλία\";s:2:\"IO\";s:59:\"Βρετανικά Εδάφη Ινδικού Ωκεανού\";s:2:\"VG\";s:48:\"Βρετανικές Παρθένες Νήσοι\";s:2:\"FR\";s:12:\"Γαλλία\";s:2:\"TF\";s:36:\"Γαλλικά Νότια Εδάφη\";s:2:\"GF\";s:29:\"Γαλλική Γουιάνα\";s:2:\"PF\";s:33:\"Γαλλική Πολυνησία\";s:2:\"DE\";s:16:\"Γερμανία\";s:2:\"GE\";s:14:\"Γεωργία\";s:2:\"GI\";s:18:\"Γιβραλτάρ\";s:2:\"GM\";s:14:\"Γκάμπια\";s:2:\"GA\";s:14:\"Γκαμπόν\";s:2:\"GH\";s:10:\"Γκάνα\";s:2:\"GG\";s:14:\"Γκέρνζι\";s:2:\"GU\";s:12:\"Γκουάμ\";s:2:\"GP\";s:22:\"Γουαδελούπη\";s:2:\"WF\";s:38:\"Γουάλις και Φουτούνα\";s:2:\"GT\";s:20:\"Γουατεμάλα\";s:2:\"GY\";s:14:\"Γουιάνα\";s:2:\"GN\";s:14:\"Γουινέα\";s:2:\"GW\";s:29:\"Γουινέα Μπισάου\";s:2:\"GD\";s:14:\"Γρενάδα\";s:2:\"GL\";s:20:\"Γροιλανδία\";s:2:\"DK\";s:10:\"Δανία\";s:2:\"DO\";s:41:\"Δομινικανή Δημοκρατία\";s:2:\"EH\";s:25:\"Δυτική Σαχάρα\";s:2:\"SV\";s:21:\"Ελ Σαλβαδόρ\";s:2:\"CH\";s:14:\"Ελβετία\";s:2:\"GR\";s:12:\"Ελλάδα\";s:2:\"ER\";s:16:\"Ερυθραία\";s:2:\"EE\";s:14:\"Εσθονία\";s:2:\"ZM\";s:12:\"Ζάμπια\";s:2:\"ZW\";s:20:\"Ζιμπάμπουε\";s:2:\"AE\";s:44:\"Ηνωμένα Αραβικά Εμιράτα\";s:2:\"US\";s:35:\"Ηνωμένες Πολιτείες\";s:2:\"GB\";s:31:\"Ηνωμένο Βασίλειο\";s:2:\"JP\";s:14:\"Ιαπωνία\";s:2:\"IN\";s:10:\"Ινδία\";s:2:\"ID\";s:18:\"Ινδονησία\";s:2:\"JO\";s:16:\"Ιορδανία\";s:2:\"IQ\";s:8:\"Ιράκ\";s:2:\"IR\";s:8:\"Ιράν\";s:2:\"IE\";s:16:\"Ιρλανδία\";s:2:\"GQ\";s:33:\"Ισημερινή Γουινέα\";s:2:\"EC\";s:20:\"Ισημερινός\";s:2:\"IS\";s:16:\"Ισλανδία\";s:2:\"ES\";s:14:\"Ισπανία\";s:2:\"IL\";s:12:\"Ισραήλ\";s:2:\"IT\";s:12:\"Ιταλία\";s:2:\"KZ\";s:18:\"Καζακστάν\";s:2:\"CM\";s:16:\"Καμερούν\";s:2:\"KH\";s:16:\"Καμπότζη\";s:2:\"CA\";s:14:\"Καναδάς\";s:2:\"QA\";s:10:\"Κατάρ\";s:2:\"CF\";s:53:\"Κεντροαφρικανική Δημοκρατία\";s:2:\"KE\";s:10:\"Κένυα\";s:2:\"CN\";s:8:\"Κίνα\";s:2:\"KG\";s:18:\"Κιργιστάν\";s:2:\"KI\";s:18:\"Κιριμπάτι\";s:2:\"CO\";s:16:\"Κολομβία\";s:2:\"KM\";s:14:\"Κομόρες\";s:2:\"CD\";s:29:\"Κονγκό - Κινσάσα\";s:2:\"CG\";s:33:\"Κονγκό - Μπραζαβίλ\";s:2:\"CR\";s:19:\"Κόστα Ρίκα\";s:2:\"CU\";s:10:\"Κούβα\";s:2:\"KW\";s:14:\"Κουβέιτ\";s:2:\"CW\";s:16:\"Κουρασάο\";s:2:\"HR\";s:14:\"Κροατία\";s:2:\"CY\";s:12:\"Κύπρος\";s:2:\"LA\";s:8:\"Λάος\";s:2:\"LS\";s:12:\"Λεσότο\";s:2:\"LV\";s:14:\"Λετονία\";s:2:\"BY\";s:20:\"Λευκορωσία\";s:2:\"LB\";s:14:\"Λίβανος\";s:2:\"LR\";s:14:\"Λιβερία\";s:2:\"LY\";s:10:\"Λιβύη\";s:2:\"LT\";s:18:\"Λιθουανία\";s:2:\"LI\";s:22:\"Λιχτενστάιν\";s:2:\"LU\";s:24:\"Λουξεμβούργο\";s:2:\"YT\";s:12:\"Μαγιότ\";s:2:\"MG\";s:22:\"Μαδαγασκάρη\";s:2:\"MO\";s:28:\"Μακάο ΕΔΠ Κίνας\";s:2:\"MY\";s:16:\"Μαλαισία\";s:2:\"MW\";s:14:\"Μαλάουι\";s:2:\"MV\";s:16:\"Μαλδίβες\";s:2:\"ML\";s:8:\"Μάλι\";s:2:\"MT\";s:10:\"Μάλτα\";s:2:\"MA\";s:12:\"Μαρόκο\";s:2:\"MQ\";s:18:\"Μαρτινίκα\";s:2:\"MU\";s:18:\"Μαυρίκιος\";s:2:\"MR\";s:20:\"Μαυριτανία\";s:2:\"ME\";s:22:\"Μαυροβούνιο\";s:2:\"MX\";s:12:\"Μεξικό\";s:2:\"MM\";s:33:\"Μιανμάρ (Βιρμανία)\";s:2:\"FM\";s:20:\"Μικρονησία\";s:2:\"MN\";s:16:\"Μογγολία\";s:2:\"MZ\";s:18:\"Μοζαμβίκη\";s:2:\"MD\";s:16:\"Μολδαβία\";s:2:\"MC\";s:12:\"Μονακό\";s:2:\"MS\";s:16:\"Μονσεράτ\";s:2:\"BD\";s:24:\"Μπανγκλαντές\";s:2:\"BB\";s:24:\"Μπαρμπέιντος\";s:2:\"BS\";s:16:\"Μπαχάμες\";s:2:\"BH\";s:16:\"Μπαχρέιν\";s:2:\"BZ\";s:12:\"Μπελίζ\";s:2:\"BJ\";s:12:\"Μπενίν\";s:2:\"BW\";s:20:\"Μποτσουάνα\";s:2:\"BF\";s:27:\"Μπουρκίνα Φάσο\";s:2:\"BI\";s:20:\"Μπουρούντι\";s:2:\"BT\";s:14:\"Μπουτάν\";s:2:\"BN\";s:16:\"Μπρουνέι\";s:2:\"NA\";s:16:\"Ναμίμπια\";s:2:\"NR\";s:14:\"Ναουρού\";s:2:\"NZ\";s:23:\"Νέα Ζηλανδία\";s:2:\"NC\";s:25:\"Νέα Καληδονία\";s:2:\"NP\";s:10:\"Νεπάλ\";s:2:\"MP\";s:42:\"Νήσοι Βόρειες Μαριάνες\";s:2:\"KY\";s:23:\"Νήσοι Κέιμαν\";s:2:\"CC\";s:38:\"Νήσοι Κόκος (Κίλινγκ)\";s:2:\"CK\";s:19:\"Νήσοι Κουκ\";s:2:\"MH\";s:23:\"Νήσοι Μάρσαλ\";s:2:\"GS\";s:75:\"Νήσοι Νότια Γεωργία και Νότιες Σάντουιτς\";s:2:\"AX\";s:21:\"Νήσοι Όλαντ\";s:2:\"PN\";s:25:\"Νήσοι Πίτκερν\";s:2:\"SB\";s:31:\"Νήσοι Σολομώντος\";s:2:\"TC\";s:41:\"Νήσοι Τερκς και Κάικος\";s:2:\"FO\";s:23:\"Νήσοι Φερόες\";s:2:\"FK\";s:25:\"Νήσοι Φόκλαντ\";s:2:\"HM\";s:51:\"Νήσοι Χερντ και Μακντόναλντ\";s:2:\"BV\";s:23:\"Νήσος Μπουβέ\";s:2:\"NF\";s:25:\"Νήσος Νόρφολκ\";s:2:\"IM\";s:24:\"Νήσος του Μαν\";s:2:\"CX\";s:44:\"Νήσος των Χριστουγέννων\";s:2:\"NE\";s:14:\"Νίγηρας\";s:2:\"NG\";s:14:\"Νιγηρία\";s:2:\"NI\";s:20:\"Νικαράγουα\";s:2:\"NU\";s:10:\"Νιούε\";s:2:\"NO\";s:16:\"Νορβηγία\";s:2:\"ZA\";s:23:\"Νότια Αφρική\";s:2:\"KR\";s:21:\"Νότια Κορέα\";s:2:\"SS\";s:23:\"Νότιο Σουδάν\";s:2:\"DM\";s:18:\"Ντομίνικα\";s:2:\"NL\";s:16:\"Ολλανδία\";s:2:\"BQ\";s:37:\"Ολλανδία Καραϊβικής\";s:2:\"OM\";s:8:\"Ομάν\";s:2:\"HN\";s:14:\"Ονδούρα\";s:2:\"HU\";s:16:\"Ουγγαρία\";s:2:\"UG\";s:16:\"Ουγκάντα\";s:2:\"UZ\";s:24:\"Ουζμπεκιστάν\";s:2:\"UA\";s:16:\"Ουκρανία\";s:2:\"UY\";s:20:\"Ουρουγουάη\";s:2:\"PK\";s:16:\"Πακιστάν\";s:2:\"PS\";s:37:\"Παλαιστινιακά Εδάφη\";s:2:\"PW\";s:12:\"Παλάου\";s:2:\"PA\";s:14:\"Παναμάς\";s:2:\"PG\";s:34:\"Παπούα Νέα Γουινέα\";s:2:\"PY\";s:18:\"Παραγουάη\";s:2:\"PE\";s:10:\"Περού\";s:2:\"PL\";s:14:\"Πολωνία\";s:2:\"PT\";s:20:\"Πορτογαλία\";s:2:\"PR\";s:23:\"Πουέρτο Ρίκο\";s:2:\"CV\";s:33:\"Πράσινο Ακρωτήριο\";s:2:\"RE\";s:14:\"Ρεϊνιόν\";s:2:\"RW\";s:14:\"Ρουάντα\";s:2:\"RO\";s:16:\"Ρουμανία\";s:2:\"RU\";s:10:\"Ρωσία\";s:2:\"WS\";s:10:\"Σαμόα\";s:2:\"ST\";s:39:\"Σάο Τομέ και Πρίνσιπε\";s:2:\"SA\";s:29:\"Σαουδική Αραβία\";s:2:\"SJ\";s:49:\"Σβάλμπαρντ και Γιαν Μαγιέν\";s:2:\"KN\";s:33:\"Σεν Κιτς και Νέβις\";s:2:\"PM\";s:37:\"Σεν Πιερ και Μικελόν\";s:2:\"SN\";s:16:\"Σενεγάλη\";s:2:\"RS\";s:12:\"Σερβία\";s:2:\"SC\";s:18:\"Σεϋχέλλες\";s:2:\"SG\";s:20:\"Σιγκαπούρη\";s:2:\"SL\";s:21:\"Σιέρα Λεόνε\";s:2:\"SK\";s:16:\"Σλοβακία\";s:2:\"SI\";s:16:\"Σλοβενία\";s:2:\"SO\";s:14:\"Σομαλία\";s:2:\"SZ\";s:22:\"Σουαζιλάνδη\";s:2:\"SD\";s:12:\"Σουδάν\";s:2:\"SE\";s:14:\"Σουηδία\";s:2:\"SR\";s:16:\"Σουρινάμ\";s:2:\"LK\";s:17:\"Σρι Λάνκα\";s:2:\"SY\";s:10:\"Συρία\";s:2:\"TW\";s:12:\"Ταϊβάν\";s:2:\"TH\";s:16:\"Ταϊλάνδη\";s:2:\"TZ\";s:16:\"Τανζανία\";s:2:\"TJ\";s:22:\"Τατζικιστάν\";s:2:\"JM\";s:16:\"Τζαμάικα\";s:2:\"JE\";s:12:\"Τζέρζι\";s:2:\"DJ\";s:18:\"Τζιμπουτί\";s:2:\"TL\";s:21:\"Τιμόρ-Λέστε\";s:2:\"TG\";s:10:\"Τόγκο\";s:2:\"TK\";s:16:\"Τοκελάου\";s:2:\"TO\";s:12:\"Τόνγκα\";s:2:\"TV\";s:16:\"Τουβαλού\";s:2:\"TR\";s:14:\"Τουρκία\";s:2:\"TM\";s:26:\"Τουρκμενιστάν\";s:2:\"TT\";s:44:\"Τρινιντάντ και Τομπάγκο\";s:2:\"TD\";s:10:\"Τσαντ\";s:2:\"CZ\";s:12:\"Τσεχία\";s:2:\"TN\";s:14:\"Τυνησία\";s:2:\"YE\";s:12:\"Υεμένη\";s:2:\"PH\";s:20:\"Φιλιππίνες\";s:2:\"FI\";s:18:\"Φινλανδία\";s:2:\"FJ\";s:10:\"Φίτζι\";s:2:\"CL\";s:8:\"Χιλή\";s:2:\"HK\";s:39:\"Χονγκ Κονγκ ΕΔΠ Κίνας\";}s:13:\"default_value\";b:0;s:13:\"return_format\";s:5:\"value\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;s:2:\"ui\";i:0;s:6:\"layout\";s:8:\"vertical\";s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}', 'Billing country', 'billing_country', 'publish', 'closed', 'closed', '', 'field_662faeeb5f3f9', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=66', 13, 'acf-field', '', 0),
(130, 2, '2024-05-02 12:23:53', '2024-05-02 09:23:53', '', '#130 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-2', '', '', '2024-05-02 12:23:53', '2024-05-02 09:23:53', '', 0, 'http://localhost/choose-life/donations/payment-2/', 0, 'donations', '', 0),
(131, 2, '2024-05-02 12:28:40', '2024-05-02 09:28:40', '', '#131', '', 'trash', 'closed', 'closed', '', 'payment-3__trashed', '', '', '2024-05-02 12:30:28', '2024-05-02 09:30:28', '', 0, 'http://localhost/choose-life/donations/payment-3/', 0, 'donations', '', 0),
(107, 2, '2024-05-01 14:11:07', '2024-05-01 11:11:07', '', '#107 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-4', '', '', '2024-05-01 14:11:07', '2024-05-01 11:11:07', '', 0, 'http://localhost/choose-life/donations/payment-4/', 0, 'donations', '', 0),
(109, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:13:\"page_template\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:24:\"templates/my-account.php\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'My Account', 'my-account', 'publish', 'closed', 'closed', '', 'group_66322a5a8beb4', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=109', 0, 'acf-field-group', '', 0),
(110, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Login', 'login', 'publish', 'closed', 'closed', '', 'field_66322a5a43e20', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=110', 0, 'acf-field', '', 0),
(74, 1, '2024-04-30 14:00:07', '2024-04-30 11:00:07', 'a:12:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:9:\"page_link\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"post_type\";a:1:{i:0;s:4:\"page\";}s:11:\"post_status\";s:0:\"\";s:8:\"taxonomy\";s:0:\"\";s:14:\"allow_archives\";i:0;s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;}', 'Checkout page', 'checkout_page_url', 'publish', 'closed', 'closed', '', 'field_6630cf2a7af52', '', '', '2024-04-30 14:00:07', '2024-04-30 11:00:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&p=74', 2, 'acf-field', '', 0),
(111, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Title', 'my_account_title', 'publish', 'closed', 'closed', '', 'field_66322a7c43e21', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=111', 1, 'acf-field', '', 0),
(77, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Thank you page messages', 'thank_you_page_messages', 'publish', 'closed', 'closed', '', 'field_6630d63afcd3e', '', '', '2024-04-30 14:32:07', '2024-04-30 11:32:07', '', 35, 'http://localhost/choose-life/?post_type=acf-field&p=77', 10, 'acf-field', '', 0),
(78, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:5:\"group\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:6:\"layout\";s:5:\"block\";s:10:\"sub_fields\";a:0:{}}', 'Payment success messages', 'payment_success_messages', 'publish', 'closed', 'closed', '', 'field_6630d64cfcd3f', '', '', '2024-04-30 14:37:15', '2024-04-30 11:37:15', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=78', 11, 'acf-field', '', 0),
(79, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Title', 'title', 'publish', 'closed', 'closed', '', 'field_6630d65ffcd40', '', '', '2024-04-30 14:32:07', '2024-04-30 11:32:07', '', 78, 'http://localhost/choose-life/?post_type=acf-field&p=79', 0, 'acf-field', '', 0),
(80, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:1;s:5:\"delay\";i:0;}', 'Content', 'content', 'publish', 'closed', 'closed', '', 'field_6630d669fcd41', '', '', '2024-04-30 14:32:07', '2024-04-30 11:32:07', '', 78, 'http://localhost/choose-life/?post_type=acf-field&p=80', 1, 'acf-field', '', 0),
(81, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:16:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:5:\"image\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"return_format\";s:3:\"url\";s:7:\"library\";s:3:\"all\";s:9:\"min_width\";s:0:\"\";s:10:\"min_height\";s:0:\"\";s:8:\"min_size\";s:0:\"\";s:9:\"max_width\";s:0:\"\";s:10:\"max_height\";s:0:\"\";s:8:\"max_size\";s:0:\"\";s:10:\"mime_types\";s:0:\"\";s:12:\"preview_size\";s:5:\"large\";}', 'Image', 'image', 'publish', 'closed', 'closed', '', 'field_6630d674fcd42', '', '', '2024-04-30 14:47:15', '2024-04-30 11:47:15', '', 78, 'http://localhost/choose-life/?post_type=acf-field&#038;p=81', 2, 'acf-field', '', 0),
(82, 1, '2024-04-30 14:32:07', '2024-04-30 11:32:07', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:5:\"group\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:6:\"layout\";s:5:\"block\";s:10:\"sub_fields\";a:0:{}}', 'Payment failed messages', 'payment_failed_messages', 'publish', 'closed', 'closed', '', 'field_6630d68ffcd43', '', '', '2024-04-30 14:37:15', '2024-04-30 11:37:15', '', 35, 'http://localhost/choose-life/?post_type=acf-field&#038;p=82', 12, 'acf-field', '', 0),
(83, 1, '2024-04-30 14:32:08', '2024-04-30 11:32:08', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Title', 'title', 'publish', 'closed', 'closed', '', 'field_6630d68ffcd44', '', '', '2024-04-30 14:32:08', '2024-04-30 11:32:08', '', 82, 'http://localhost/choose-life/?post_type=acf-field&p=83', 0, 'acf-field', '', 0),
(84, 1, '2024-04-30 14:32:08', '2024-04-30 11:32:08', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:1;s:5:\"delay\";i:0;}', 'Content', 'content', 'publish', 'closed', 'closed', '', 'field_6630d68ffcd45', '', '', '2024-04-30 14:32:08', '2024-04-30 11:32:08', '', 82, 'http://localhost/choose-life/?post_type=acf-field&p=84', 1, 'acf-field', '', 0),
(85, 1, '2024-04-30 14:32:08', '2024-04-30 11:32:08', 'a:16:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:5:\"image\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"return_format\";s:3:\"url\";s:7:\"library\";s:3:\"all\";s:9:\"min_width\";s:0:\"\";s:10:\"min_height\";s:0:\"\";s:8:\"min_size\";s:0:\"\";s:9:\"max_width\";s:0:\"\";s:10:\"max_height\";s:0:\"\";s:8:\"max_size\";s:0:\"\";s:10:\"mime_types\";s:0:\"\";s:12:\"preview_size\";s:5:\"large\";}', 'Image', 'image', 'publish', 'closed', 'closed', '', 'field_6630d68ffcd46', '', '', '2024-04-30 14:47:15', '2024-04-30 11:47:15', '', 82, 'http://localhost/choose-life/?post_type=acf-field&#038;p=85', 2, 'acf-field', '', 0),
(86, 1, '2024-04-30 14:33:39', '2024-04-30 11:33:39', '', '426630f77882a7ef0feceab2024af785', '', 'inherit', 'closed', 'closed', '', '426630f77882a7ef0feceab2024af785', '', '', '2024-04-30 14:33:39', '2024-04-30 11:33:39', '', 0, 'http://localhost/choose-life/wp-content/uploads/2024/04/426630f77882a7ef0feceab2024af785.jpg', 0, 'attachment', 'image/jpeg', 0),
(112, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:0;s:5:\"delay\";i:0;}', 'Content', 'my_account_content', 'publish', 'closed', 'closed', '', 'field_66322a8b43e22', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=112', 2, 'acf-field', '', 0),
(92, 1, '2024-04-30 18:35:05', '2024-04-30 15:35:05', 'a:9:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:11:\"date_picker\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"field\";s:19:\"field_662faf2a5f3fb\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:9:\"recurring\";}}}s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:14:\"display_format\";s:6:\"F j, Y\";s:13:\"return_format\";s:3:\"Ymd\";s:9:\"first_day\";i:1;}', 'Donation end date', 'recurring_end_date', 'publish', 'closed', 'closed', '', 'field_66310f22288cb', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=92', 2, 'acf-field', '', 0),
(93, 1, '2024-04-30 18:35:05', '2024-04-30 15:35:05', 'a:13:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"number\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"field\";s:19:\"field_662faf2a5f3fb\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:9:\"recurring\";}}}s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:4:\"step\";s:0:\"\";s:7:\"prepend\";s:6:\"Months\";s:6:\"append\";s:0:\"\";}', 'Payment occurs every x Months', 'donation_frequency', 'publish', 'closed', 'closed', '', 'field_66310f6f288cc', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=93', 3, 'acf-field', '', 0),
(114, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:4:\"text\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:9:\"maxlength\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:7:\"prepend\";s:0:\"\";s:6:\"append\";s:0:\"\";}', 'Recurring donations title', 'recurring_donations_title', 'publish', 'closed', 'closed', '', 'field_66322aaa43e24', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=114', 4, 'acf-field', '', 0),
(113, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:8:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:3:\"tab\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";b:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"placement\";s:4:\"left\";s:8:\"endpoint\";i:0;}', 'Donations', 'donations', 'publish', 'closed', 'closed', '', 'field_66322a9d43e23', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=113', 3, 'acf-field', '', 0),
(115, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:0;s:5:\"delay\";i:0;}', 'Recurring donations content', 'recurring_donations_content', 'publish', 'closed', 'closed', '', 'field_66322aba43e25', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=115', 5, 'acf-field', '', 0),
(108, 2, '2024-05-01 14:11:25', '2024-05-01 11:11:25', '', '#108 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-5', '', '', '2024-05-01 14:11:25', '2024-05-01 11:11:25', '', 0, 'http://localhost/choose-life/donations/payment-5/', 0, 'donations', '', 0),
(104, 2, '2024-05-01 14:08:54', '2024-05-01 11:08:54', '', '#104 John Doe', '', 'publish', 'closed', 'closed', '', 'payment', '', '', '2024-05-01 14:08:54', '2024-05-01 11:08:54', '', 0, 'http://localhost/choose-life/donations/payment/', 0, 'donations', '', 0),
(117, 1, '2024-05-01 14:46:04', '2024-05-01 11:46:04', 'a:11:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:7:\"wysiwyg\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:4:\"tabs\";s:3:\"all\";s:7:\"toolbar\";s:4:\"full\";s:12:\"media_upload\";i:0;s:5:\"delay\";i:0;}', 'Completed donations content', 'completed_donations_content', 'publish', 'closed', 'closed', '', 'field_66322af043e27', '', '', '2024-05-01 14:46:04', '2024-05-01 11:46:04', '', 109, 'http://localhost/choose-life/?post_type=acf-field&p=117', 7, 'acf-field', '', 0),
(120, 1, '2024-05-02 11:34:43', '2024-05-02 08:34:43', 'a:8:{s:8:\"location\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"param\";s:9:\"post_type\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:13:\"subscriptions\";}}}s:8:\"position\";s:6:\"normal\";s:5:\"style\";s:7:\"default\";s:15:\"label_placement\";s:3:\"top\";s:21:\"instruction_placement\";s:5:\"label\";s:14:\"hide_on_screen\";s:0:\"\";s:11:\"description\";s:0:\"\";s:12:\"show_in_rest\";i:0;}', 'Subscription', 'subscription', 'publish', 'closed', 'closed', '', 'group_66334f89e9ba6', '', '', '2024-05-02 16:47:10', '2024-05-02 13:47:10', '', 0, 'http://localhost/choose-life/?post_type=acf-field-group&#038;p=120', 0, 'acf-field-group', '', 0),
(121, 1, '2024-05-02 11:34:43', '2024-05-02 08:34:43', 'a:14:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"select\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:7:\"choices\";a:2:{s:6:\"active\";s:6:\"Active\";s:8:\"inactive\";s:8:\"Inactive\";}s:13:\"default_value\";s:8:\"inactive\";s:13:\"return_format\";s:5:\"value\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:0;s:2:\"ui\";i:0;s:4:\"ajax\";i:0;s:11:\"placeholder\";s:0:\"\";}', 'Status', 'subscription_status', 'publish', 'closed', 'closed', '', 'field_66334fed946ea', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&#038;p=121', 0, 'acf-field', '', 0),
(122, 1, '2024-05-02 11:34:43', '2024-05-02 08:34:43', 'a:9:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:11:\"date_picker\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:14:\"display_format\";s:6:\"F j, Y\";s:13:\"return_format\";s:3:\"Ymd\";s:9:\"first_day\";i:1;}', 'Start date', 'start_date', 'publish', 'closed', 'closed', '', 'field_66334f8a946e8', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&#038;p=122', 2, 'acf-field', '', 0),
(123, 1, '2024-05-02 11:34:43', '2024-05-02 08:34:43', 'a:9:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:11:\"date_picker\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:14:\"display_format\";s:6:\"F j, Y\";s:13:\"return_format\";s:3:\"Ymd\";s:9:\"first_day\";i:1;}', 'End date', 'end_date', 'publish', 'closed', 'closed', '', 'field_66334fdf946e9', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&#038;p=123', 3, 'acf-field', '', 0),
(124, 1, '2024-05-02 11:38:08', '2024-05-02 08:38:08', 'a:13:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"number\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:4:\"step\";s:0:\"\";s:7:\"prepend\";s:8:\"Month(s)\";s:6:\"append\";s:0:\"\";}', 'Payment cycle', 'payment_cycle', 'publish', 'closed', 'closed', '', 'field_6633502e090ba', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&#038;p=124', 4, 'acf-field', '', 0),
(125, 1, '2024-05-02 11:38:08', '2024-05-02 08:38:08', 'a:15:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:11:\"post_object\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:0:\"\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"post_type\";a:1:{i:0;s:9:\"donations\";}s:11:\"post_status\";s:0:\"\";s:8:\"taxonomy\";s:0:\"\";s:13:\"return_format\";s:2:\"id\";s:8:\"multiple\";i:1;s:10:\"allow_null\";i:1;s:13:\"bidirectional\";i:0;s:2:\"ui\";i:1;s:20:\"bidirectional_target\";a:0:{}}', 'Payments', 'payments', 'publish', 'closed', 'closed', '', 'field_66335097090bb', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&#038;p=125', 5, 'acf-field', '', 0),
(133, 2, '2024-05-02 12:37:33', '2024-05-02 09:37:33', '', '#133', '', 'trash', 'closed', 'closed', '', 'subscription-2__trashed', '', '', '2024-05-02 12:42:38', '2024-05-02 09:42:38', '', 0, 'http://localhost/choose-life/subscriptions/subscription-2/', 0, 'subscriptions', '', 0),
(134, 2, '2024-05-02 12:42:05', '2024-05-02 09:42:05', '', '#134 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-3', '', '', '2024-05-02 12:42:05', '2024-05-02 09:42:05', '', 0, 'http://localhost/choose-life/donations/payment-3/', 0, 'donations', '', 0),
(135, 2, '2024-05-02 12:43:29', '2024-05-02 09:43:29', '', '#135 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-6', '', '', '2024-05-02 12:43:30', '2024-05-02 09:43:30', '', 0, 'http://localhost/choose-life/donations/payment-6/', 0, 'donations', '', 0),
(129, 1, '2024-05-02 12:08:01', '2024-05-02 09:08:01', 'a:15:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:11:\"post_object\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";a:1:{i:0;a:1:{i:0;a:3:{s:5:\"field\";s:19:\"field_662faf2a5f3fb\";s:8:\"operator\";s:2:\"==\";s:5:\"value\";s:9:\"recurring\";}}}s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"33\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:9:\"post_type\";a:1:{i:0;s:13:\"subscriptions\";}s:11:\"post_status\";s:0:\"\";s:8:\"taxonomy\";s:0:\"\";s:13:\"return_format\";s:2:\"id\";s:8:\"multiple\";i:0;s:10:\"allow_null\";i:1;s:13:\"bidirectional\";i:0;s:2:\"ui\";i:1;s:20:\"bidirectional_target\";a:0:{}}', 'Subscription', 'subscription_id', 'publish', 'closed', 'closed', '', 'field_663357ba10549', '', '', '2024-05-02 12:08:36', '2024-05-02 09:08:36', '', 55, 'http://localhost/choose-life/?post_type=acf-field&#038;p=129', 4, 'acf-field', '', 0),
(132, 2, '2024-05-02 12:30:17', '2024-05-02 09:30:17', '', '#132', '', 'trash', 'closed', 'closed', '', 'subscription__trashed', '', '', '2024-05-02 12:42:38', '2024-05-02 09:42:38', '', 0, 'http://localhost/choose-life/subscriptions/subscription/', 0, 'subscriptions', '', 0),
(136, 2, '2024-05-02 12:45:28', '2024-05-02 09:45:28', '', '#136 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-7', '', '', '2024-05-02 12:45:28', '2024-05-02 09:45:28', '', 0, 'http://localhost/choose-life/donations/payment-7/', 0, 'donations', '', 0),
(137, 2, '2024-05-02 12:45:43', '2024-05-02 09:45:43', '', '#137', '', 'trash', 'closed', 'closed', '', 'subscription__trashed-2', '', '', '2024-05-02 12:48:46', '2024-05-02 09:48:46', '', 0, 'http://localhost/choose-life/subscriptions/subscription/', 0, 'subscriptions', '', 0),
(138, 2, '2024-05-02 12:48:59', '2024-05-02 09:48:59', '', '#138 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-8', '', '', '2024-05-02 12:48:59', '2024-05-02 09:48:59', '', 0, 'http://localhost/choose-life/donations/payment-8/', 0, 'donations', '', 0),
(139, 2, '2024-05-02 12:49:15', '2024-05-02 09:49:15', '', '#139', '', 'trash', 'closed', 'closed', '', 'subscription__trashed-3', '', '', '2024-05-02 12:57:46', '2024-05-02 09:57:46', '', 0, 'http://localhost/choose-life/subscriptions/subscription/', 0, 'subscriptions', '', 0),
(140, 2, '2024-05-02 12:54:30', '2024-05-02 09:54:30', '', '#140 John Doe', '', 'trash', 'closed', 'closed', '', 'subscription-2__trashed-2', '', '', '2024-05-02 12:57:46', '2024-05-02 09:57:46', '', 0, 'http://localhost/choose-life/subscriptions/subscription-2/', 0, 'subscriptions', '', 0),
(141, 2, '2024-05-02 12:55:38', '2024-05-02 09:55:38', '', '#141 John Doe', '', 'trash', 'closed', 'closed', '', 'subscription-3__trashed', '', '', '2024-05-02 12:57:46', '2024-05-02 09:57:46', '', 0, 'http://localhost/choose-life/subscriptions/subscription-3/', 0, 'subscriptions', '', 0),
(142, 2, '2024-05-02 12:57:21', '2024-05-02 09:57:21', '', '#142 John Doe', '', 'publish', 'closed', 'closed', '', 'payment-9', '', '', '2024-05-02 12:57:21', '2024-05-02 09:57:21', '', 0, 'http://localhost/choose-life/donations/payment-9/', 0, 'donations', '', 0),
(143, 2, '2024-05-02 12:57:36', '2024-05-02 09:57:36', '', '#143 John Doe', '', 'publish', 'closed', 'closed', '', 'subscription-4', '', '', '2024-05-02 17:15:08', '2024-05-02 14:15:08', '', 0, 'http://localhost/choose-life/subscriptions/subscription-4/', 0, 'subscriptions', '', 0),
(144, 1, '2024-05-02 16:40:25', '2024-05-02 13:40:25', 'a:13:{s:10:\"aria-label\";s:0:\"\";s:4:\"type\";s:6:\"number\";s:12:\"instructions\";s:0:\"\";s:8:\"required\";i:0;s:17:\"conditional_logic\";i:0;s:7:\"wrapper\";a:3:{s:5:\"width\";s:2:\"50\";s:5:\"class\";s:0:\"\";s:2:\"id\";s:0:\"\";}s:13:\"default_value\";s:0:\"\";s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:11:\"placeholder\";s:0:\"\";s:4:\"step\";s:0:\"\";s:7:\"prepend\";s:3:\"€\";s:6:\"append\";s:0:\"\";}', 'Donation amount', 'donation_amount', 'publish', 'closed', 'closed', '', 'field_663397847ff7c', '', '', '2024-05-02 16:40:25', '2024-05-02 13:40:25', '', 120, 'http://localhost/choose-life/?post_type=acf-field&p=144', 1, 'acf-field', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_post_smtp_logmeta`
--

DROP TABLE IF EXISTS `cp_post_smtp_logmeta`;
CREATE TABLE IF NOT EXISTS `cp_post_smtp_logmeta` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `log_id` bigint NOT NULL,
  `meta_key` longtext COLLATE utf8mb4_unicode_520_ci,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cp_post_smtp_logs`
--

DROP TABLE IF EXISTS `cp_post_smtp_logs`;
CREATE TABLE IF NOT EXISTS `cp_post_smtp_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `solution` longtext COLLATE utf8mb4_unicode_520_ci,
  `success` longtext COLLATE utf8mb4_unicode_520_ci,
  `from_header` longtext COLLATE utf8mb4_unicode_520_ci,
  `to_header` longtext COLLATE utf8mb4_unicode_520_ci,
  `cc_header` longtext COLLATE utf8mb4_unicode_520_ci,
  `bcc_header` longtext COLLATE utf8mb4_unicode_520_ci,
  `reply_to_header` longtext COLLATE utf8mb4_unicode_520_ci,
  `transport_uri` longtext COLLATE utf8mb4_unicode_520_ci,
  `original_to` longtext COLLATE utf8mb4_unicode_520_ci,
  `original_subject` longtext COLLATE utf8mb4_unicode_520_ci,
  `original_message` longtext COLLATE utf8mb4_unicode_520_ci,
  `original_headers` longtext COLLATE utf8mb4_unicode_520_ci,
  `session_transcript` longtext COLLATE utf8mb4_unicode_520_ci,
  `time` bigint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_post_smtp_logs`
--

INSERT INTO `cp_post_smtp_logs` (`id`, `solution`, `success`, `from_header`, `to_header`, `cc_header`, `bcc_header`, `reply_to_header`, `transport_uri`, `original_to`, `original_subject`, `original_message`, `original_headers`, `session_transcript`, `time`) VALUES
(1, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'apostolis.kyromitis@novidea.gr', '', '', '', 'smtp:none:none://localhost:25', 'apostolis.kyromitis@novidea.gr', '[Choose life] Εγγραφή νέου χρήστη', 'Ένας νέος χρήστης έκανε εγγραφή στον ιστότοπό σας Choose life:\r\n\r\nΌνομα χρήστη: test\r\n\r\nEmail: test@test.gr\r\n', '', 'smtp:none:none://localhost:25\n\n', 1713898826),
(2, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'test@test.gr', '', '', '', 'smtp:none:none://localhost:25', 'test@test.gr', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε test@test.gr2121 .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από test@test.gr\n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996371),
(3, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'test@test.gr2121', '', '', '', 'smtp:none:none://localhost:25', 'test@test.gr2121', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε test@test.gr1 .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από test@test.gr2121\n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996390),
(4, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'test@test.gr1', '', '', '', 'smtp:none:none://localhost:25', 'test@test.gr1', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε  .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από test@test.gr1\n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996620),
(5, 'Not found, check status column for more info.', 'Missing To addresses', 'WordPress <wordpress@localhost>', '', '', '', '', 'smtp:none:none://localhost:25', '', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε test@test.gr .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από \n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996697),
(6, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'test@test.gr', '', '', '', 'smtp:none:none://localhost:25', 'test@test.gr', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε test@test.gr1 .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από test@test.gr\n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996711),
(7, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'test@test.gr1', '', '', '', 'smtp:none:none://localhost:25', 'test@test.gr1', '[Choose life] Το Email Άλλαξε', 'Γεια σου test,\n\nΗ ειδοποίηση αυτή επιβεβαιώνει ότι η ηλ. διεύθυνση σας άλλαξε στο Choose life σε test@test.gr .\n\nΕάν δεν αλλάξατε εσείς την ηλ. διεύθυνση, παρακαλώ επικοινωνήστε με το Διαχειριστή του ιστότοπου στη\napostolis.kyromitis@novidea.gr\n\nΤο ηλ. μήνυμα στάλθηκε από test@test.gr1\n\nΜε εκτίμηση,\nΌλοι στο Choose life\nhttp://localhost/choose-life', '', 'smtp:none:none://localhost:25\n\n', 1713996864),
(8, 'Not found, check status column for more info.', 'Unable to send mail. mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', 'WordPress <wordpress@localhost>', 'apostolis.kyromitis@novidea.gr', '', '', '', 'smtp:none:none://localhost:25', 'apostolis.kyromitis@novidea.gr', '[Choose life] Ο Ιστότοπος σας Αντιμετωπίζει ένα Τεχνικό Ζήτημα', 'Πως πάει!\n \nΑπό την έκδοση WordPress 5.2, υπάρχει μια ενσωματωμένη λειτουργία που εντοπίζει πότε ένα πρόσθετο ή θέμα δημιουργεί ένα κρίσιμο σφάλμα στον ιστότοπό σας, και σας ειδοποιεί με αυτό το αυτοματοποιημένο email.\n \nΣε αυτή την περίπτωση, το WordPress αντιμετώπισε ένα σφάλμα με το θέμα σας, Choose life.\n\nΠρώτα, επισκεφθείτε τον ιστότοπό σας (http://localhost/choose-life/) και ελέγξτε για τυχόν ορατά προβλήματα. Μετά, επισκεφθείτε την σελίδα στην οποία παρατηρήθηκε το σφάλμα (http://localhost/choose-life/choose-life/wp-admin/admin-ajax.php) και ελέγξτε για τυχόν ορατά σφάλματα.\n \n Παρακαλώ επικοινωνήστε με τον πάροχο σας για περαιτέρω διερεύνηση του ζητήματος σας.\n \nΑν ο ιστότοπός σας, εμφανίζεται χαλασμένος και δεν μπορείτε να έχετε, κανονικά, πρόσβαση στη σελίδα διαχείρισης, το WordPress βρίσκεται σε ειδική \"λειτουργία επαναφοράς\". Αυτή σας επιτρέπει, με ασφάλεια, να συνδεθείτε στη σελίδα διαχείρισης και να ερευνήσετε περαιτέρω.\n \n http://localhost/choose-life/wp-login.php?action=enter_recovery_mode&rm_token=hDxW4Hd7VWzuyHFiVNGJ8p&rm_key=YfwssB4qMtoTv6coeW4xbR\n \nΓια να κρατήσουμε ασφαλή τον ιστότοπό σας, αυτό ο σύνδεσμος θα λήξει σε 1 ημέρα. Όμως, μην ανησυχείτε για αυτό. Ένας νέος σύνδεσμος θα σας σταλεί μέσω email, σε περίπτωση που το σφάλμα ξαναπαρουσιαστεί μετά τη λήξη αυτού.\n\nΌταν ζητήσετε βοήθεια σχετικά με αυτό το ζήτημα, μπορεί να σας ζητηθούν ορισμένες από τις παρακάτω πληροφορίες:\nWordPress έκδοση 6.5.2\r\nΕνεργό θέμα: Choose life (έκδοση 1.0.0)\r\nΤρέχων πρόσθετο:  (έκδοση )\r\nΈκδοση PHP 8.2.0\n\n\n\nΛεπτομέρειες σφάλματος\n===========================================\nΈνα σφάλμα τύπου E_COMPILE_ERROR εντοπίστηκε στη γραμμή 34 του αρχείου C:\\wamp64\\www\\choose-life\\wp-content\\themes\\choose-life\\includes\\class-admin.php. Κωδικός σφάλματος: Cannot redeclare Inc_Admin::custom_donations_column()', '', 'smtp:none:none://localhost:25\n\n', 1714480301);

-- --------------------------------------------------------

--
-- Table structure for table `cp_termmeta`
--

DROP TABLE IF EXISTS `cp_termmeta`;
CREATE TABLE IF NOT EXISTS `cp_termmeta` (
  `meta_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `term_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cp_terms`
--

DROP TABLE IF EXISTS `cp_terms`;
CREATE TABLE IF NOT EXISTS `cp_terms` (
  `term_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint NOT NULL DEFAULT '0',
  `term_order` int DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_terms`
--

INSERT INTO `cp_terms` (`term_id`, `name`, `slug`, `term_group`, `term_order`) VALUES
(1, 'Χωρίς κατηγορία', '%ce%b1%cf%84%ce%b1%ce%be%ce%b9%ce%bd%cf%8c%ce%bc%ce%b7%cf%84%ce%b1', 0, 0),
(2, 'Footer menu', 'footer-menu', 0, 0),
(3, 'Main menu', 'main-menu', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_term_relationships`
--

DROP TABLE IF EXISTS `cp_term_relationships`;
CREATE TABLE IF NOT EXISTS `cp_term_relationships` (
  `object_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `term_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_term_relationships`
--

INSERT INTO `cp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(1, 1, 0),
(9, 2, 0),
(10, 3, 0),
(11, 3, 0),
(12, 3, 0),
(22, 3, 0),
(16, 3, 0),
(17, 3, 0),
(23, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_term_taxonomy`
--

DROP TABLE IF EXISTS `cp_term_taxonomy`;
CREATE TABLE IF NOT EXISTS `cp_term_taxonomy` (
  `term_taxonomy_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `term_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint UNSIGNED NOT NULL DEFAULT '0',
  `count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_term_taxonomy`
--

INSERT INTO `cp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1, 1, 'category', '', 0, 1),
(2, 2, 'nav_menu', '', 0, 1),
(3, 3, 'nav_menu', '', 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `cp_usermeta`
--

DROP TABLE IF EXISTS `cp_usermeta`;
CREATE TABLE IF NOT EXISTS `cp_usermeta` (
  `umeta_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_usermeta`
--

INSERT INTO `cp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 1, 'nickname', 'apostolis'),
(2, 1, 'first_name', 'Αποστόλης'),
(3, 1, 'last_name', 'Κυρο'),
(4, 1, 'description', ''),
(5, 1, 'rich_editing', 'true'),
(6, 1, 'syntax_highlighting', 'true'),
(7, 1, 'comment_shortcuts', 'false'),
(8, 1, 'admin_color', 'fresh'),
(9, 1, 'use_ssl', '0'),
(10, 1, 'show_admin_bar_front', 'false'),
(11, 1, 'locale', 'en_US'),
(12, 1, 'cp_capabilities', 'a:1:{s:13:\"administrator\";b:1;}'),
(13, 1, 'cp_user_level', '10'),
(14, 1, 'dismissed_wp_pointers', ''),
(15, 1, 'show_welcome_panel', '0'),
(16, 1, 'session_tokens', 'a:2:{s:64:\"a99e9ec973bc81600e22811d05df5761f1214b42e28a3605646345c648956b3d\";a:4:{s:10:\"expiration\";i:1714727621;s:2:\"ip\";s:3:\"::1\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36\";s:5:\"login\";i:1714554821;}s:64:\"122ff89dc64d1268d7dac07672dbc4e87ad24d4b708a9f1d75e81c3f7724b888\";a:4:{s:10:\"expiration\";i:1714811126;s:2:\"ip\";s:3:\"::1\";s:2:\"ua\";s:111:\"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36\";s:5:\"login\";i:1714638326;}}'),
(17, 1, 'cp_dashboard_quick_press_last_post_id', '119'),
(125, 1, 'cp_yoast_notifications', 'a:1:{i:0;a:2:{s:7:\"message\";s:482:\"<p><strong>Huge SEO Issue: You&#039;re blocking access to robots.</strong> If you want search engines to show this site in their results, you must <a href=\"http://localhost/choose-life/wp-admin/options-reading.php\">go to your Reading Settings</a> and uncheck the box for Search Engine Visibility. <button type=\"button\" id=\"robotsmessage-dismiss-button\" class=\"button-link hide-if-no-js\" data-nonce=\"59612dec99\">I don&#039;t want this site to show in the search results.</button></p>\";s:7:\"options\";a:10:{s:4:\"type\";s:5:\"error\";s:2:\"id\";s:32:\"wpseo-search-engines-discouraged\";s:7:\"user_id\";i:1;s:5:\"nonce\";N;s:8:\"priority\";i:1;s:9:\"data_json\";a:0:{}s:13:\"dismissal_key\";N;s:12:\"capabilities\";s:20:\"wpseo_manage_options\";s:16:\"capability_check\";s:3:\"all\";s:14:\"yoast_branding\";b:0;}}}'),
(99, 2, 'email', 'test@test.gr2'),
(123, 1, 'manageedit-acf-ui-options-pagecolumnshidden', 'a:1:{i:0;s:7:\"acf-key\";}'),
(124, 1, 'acf_user_settings', 'a:1:{s:23:\"options-pages-first-run\";b:1;}'),
(38, 1, 'managenav-menuscolumnshidden', 'a:5:{i:0;s:11:\"link-target\";i:1;s:11:\"css-classes\";i:2;s:3:\"xfn\";i:3;s:11:\"description\";i:4;s:15:\"title-attribute\";}'),
(39, 1, 'metaboxhidden_nav-menus', 'a:1:{i:0;s:12:\"add-post_tag\";}'),
(40, 1, 'nav_menu_recently_edited', '3'),
(41, 2, 'nickname', 'test'),
(42, 2, 'first_name', 'John'),
(43, 2, 'last_name', 'Doe'),
(44, 2, 'description', ''),
(45, 2, 'rich_editing', 'true'),
(46, 2, 'syntax_highlighting', 'true'),
(19, 1, 'closedpostboxes_dashboard', 'a:0:{}'),
(20, 1, 'metaboxhidden_dashboard', 'a:3:{i:0;s:24:\"wpseo-dashboard-overview\";i:1;s:32:\"wpseo-wincher-dashboard-overview\";i:2;s:17:\"dashboard_primary\";}'),
(21, 1, '_yoast_wpseo_profile_updated', '1713981852'),
(22, 1, 'wpseo_title', ''),
(23, 1, 'wpseo_metadesc', ''),
(24, 1, 'wpseo_noindex_author', ''),
(25, 1, 'wpseo_content_analysis_disable', ''),
(26, 1, 'wpseo_keyword_analysis_disable', ''),
(27, 1, 'wpseo_inclusive_language_analysis_disable', ''),
(28, 1, 'facebook', ''),
(29, 1, 'instagram', ''),
(30, 1, 'linkedin', ''),
(31, 1, 'myspace', ''),
(32, 1, 'pinterest', ''),
(33, 1, 'soundcloud', ''),
(34, 1, 'tumblr', ''),
(35, 1, 'twitter', ''),
(36, 1, 'youtube', ''),
(37, 1, 'wikipedia', ''),
(47, 2, 'comment_shortcuts', 'false'),
(48, 2, 'admin_color', 'fresh'),
(49, 2, 'use_ssl', '0'),
(50, 2, 'show_admin_bar_front', 'true'),
(51, 2, 'locale', ''),
(52, 2, 'cp_capabilities', 'a:1:{s:10:\"subscriber\";b:1;}'),
(53, 2, 'cp_user_level', '0'),
(54, 2, 'jwt_auth_pass', '751c1b3d402b5befc3ba24806b8b39a2'),
(55, 2, '_yoast_wpseo_profile_updated', '1713985586'),
(56, 2, 'dismissed_wp_pointers', ''),
(126, 1, 'cp_user-settings', 'libraryContent=browse'),
(127, 1, 'cp_user-settings-time', '1714476826'),
(57, 1, '_first_name', 'field_66291a6165fac'),
(58, 1, '_last_name', 'field_66291ad165fad'),
(59, 1, 'telephone', '6999999999'),
(60, 1, '_telephone', 'field_66291af065fae'),
(61, 1, 'billing_address', 'My address'),
(62, 1, '_billing_address', 'field_66291afa65faf'),
(63, 1, 'billing_city', 'Athens'),
(64, 1, '_billing_city', 'field_66291b1a65fb0'),
(65, 1, 'billing_postal_code', '12345'),
(66, 1, '_billing_postal_code', 'field_66291b2b65fb1'),
(67, 1, 'billing_country', 'GR'),
(68, 1, '_billing_country', 'field_66291b4f65fb2'),
(69, 2, 'wpseo_title', ''),
(70, 2, 'wpseo_metadesc', ''),
(71, 2, 'wpseo_noindex_author', ''),
(72, 2, 'wpseo_content_analysis_disable', ''),
(73, 2, 'wpseo_keyword_analysis_disable', ''),
(74, 2, 'wpseo_inclusive_language_analysis_disable', ''),
(75, 2, 'facebook', ''),
(76, 2, 'instagram', ''),
(77, 2, 'linkedin', ''),
(78, 2, 'myspace', ''),
(79, 2, 'pinterest', ''),
(80, 2, 'soundcloud', ''),
(81, 2, 'tumblr', ''),
(82, 2, 'twitter', ''),
(83, 2, 'youtube', ''),
(84, 2, 'wikipedia', ''),
(85, 2, '_first_name', 'field_66291a6165fac'),
(86, 2, '_last_name', 'field_66291ad165fad'),
(87, 2, 'telephone', '699 99 99 999'),
(88, 2, '_telephone', 'field_66291af065fae'),
(89, 2, 'billing_address', 'My address here'),
(90, 2, '_billing_address', 'field_66291afa65faf'),
(91, 2, 'billing_city', 'Neverland'),
(92, 2, '_billing_city', 'field_66291b1a65fb0'),
(93, 2, 'billing_postal_code', '1234'),
(94, 2, '_billing_postal_code', 'field_66291b2b65fb1'),
(95, 2, 'billing_country', 'GR'),
(96, 2, '_billing_country', 'field_66291b4f65fb2'),
(97, 1, 'jwt_auth_pass', '7f14e60c1bdaaeb71bda4d057103d91e'),
(122, 2, 'marketing_acceptance', '1'),
(100, 1, 'meta-box-order_page', 'a:4:{s:15:\"acf_after_title\";s:0:\"\";s:4:\"side\";s:23:\"submitdiv,pageparentdiv\";s:6:\"normal\";s:52:\"acf-group_662b7f5733c1a,wpseo_meta,slugdiv,authordiv\";s:8:\"advanced\";s:0:\"\";}'),
(101, 1, 'screen_layout_page', '2'),
(102, 3, 'nickname', 'test1'),
(103, 3, 'first_name', 'τεστ '),
(104, 3, 'last_name', 'τεστ'),
(105, 3, 'description', ''),
(106, 3, 'rich_editing', 'true'),
(107, 3, 'syntax_highlighting', 'true'),
(108, 3, 'comment_shortcuts', 'false'),
(109, 3, 'admin_color', 'fresh'),
(110, 3, 'use_ssl', '0'),
(111, 3, 'show_admin_bar_front', 'true'),
(112, 3, 'locale', ''),
(113, 3, 'cp_capabilities', 'a:1:{s:10:\"subscriber\";b:1;}'),
(114, 3, 'cp_user_level', '0'),
(115, 3, 'jwt_auth_pass', '0135e2b97f4c29cc62b9f4f3979d2192'),
(116, 3, '_yoast_wpseo_profile_updated', '1714144262'),
(117, 3, 'telephone', '6999999999'),
(118, 3, 'billing_country', 'GR'),
(119, 3, 'billing_address', 'πφφφφφφ'),
(120, 3, 'billing_city', 'Αθήνα'),
(121, 3, 'billing_postal_code', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `cp_users`
--

DROP TABLE IF EXISTS `cp_users`;
CREATE TABLE IF NOT EXISTS `cp_users` (
  `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_users`
--

INSERT INTO `cp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(1, 'apostolis', '$P$BeCDD3vzZcCLtLyYuCwL5tgrXHKV670', 'apostolis', 'apostolis.kyromitis@novidea.gr', '', '2024-04-08 14:14:26', '', 0, 'apostolis'),
(2, 'test', '$P$ByIO8y4EjHDI20KAMWuwsakQah8zDW.', 'test', 'test@test.gr', '', '2024-04-23 16:00:22', '', 0, 'test'),
(3, 'test1', '$P$Bamh34byfVOKAyNEfuyo6G9PmtI7RJ.', 'test1', 'test1@test.gr', '', '2024-04-26 15:11:02', '', 0, 'test1');

-- --------------------------------------------------------

--
-- Table structure for table `cp_yoast_indexable`
--

DROP TABLE IF EXISTS `cp_yoast_indexable`;
CREATE TABLE IF NOT EXISTS `cp_yoast_indexable` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `permalink` longtext COLLATE utf8mb4_unicode_520_ci,
  `permalink_hash` varchar(40) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `object_id` bigint DEFAULT NULL,
  `object_type` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `object_sub_type` varchar(32) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `author_id` bigint DEFAULT NULL,
  `post_parent` bigint DEFAULT NULL,
  `title` text COLLATE utf8mb4_unicode_520_ci,
  `description` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `breadcrumb_title` text COLLATE utf8mb4_unicode_520_ci,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT NULL,
  `is_protected` tinyint(1) DEFAULT '0',
  `has_public_posts` tinyint(1) DEFAULT NULL,
  `number_of_pages` int UNSIGNED DEFAULT NULL,
  `canonical` longtext COLLATE utf8mb4_unicode_520_ci,
  `primary_focus_keyword` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `primary_focus_keyword_score` int DEFAULT NULL,
  `readability_score` int DEFAULT NULL,
  `is_cornerstone` tinyint(1) DEFAULT '0',
  `is_robots_noindex` tinyint(1) DEFAULT '0',
  `is_robots_nofollow` tinyint(1) DEFAULT '0',
  `is_robots_noarchive` tinyint(1) DEFAULT '0',
  `is_robots_noimageindex` tinyint(1) DEFAULT '0',
  `is_robots_nosnippet` tinyint(1) DEFAULT '0',
  `twitter_title` text COLLATE utf8mb4_unicode_520_ci,
  `twitter_image` longtext COLLATE utf8mb4_unicode_520_ci,
  `twitter_description` longtext COLLATE utf8mb4_unicode_520_ci,
  `twitter_image_id` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `twitter_image_source` text COLLATE utf8mb4_unicode_520_ci,
  `open_graph_title` text COLLATE utf8mb4_unicode_520_ci,
  `open_graph_description` longtext COLLATE utf8mb4_unicode_520_ci,
  `open_graph_image` longtext COLLATE utf8mb4_unicode_520_ci,
  `open_graph_image_id` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `open_graph_image_source` text COLLATE utf8mb4_unicode_520_ci,
  `open_graph_image_meta` mediumtext COLLATE utf8mb4_unicode_520_ci,
  `link_count` int DEFAULT NULL,
  `incoming_link_count` int DEFAULT NULL,
  `prominent_words_version` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blog_id` bigint NOT NULL DEFAULT '1',
  `language` varchar(32) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `region` varchar(32) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `schema_page_type` varchar(64) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `schema_article_type` varchar(64) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `has_ancestors` tinyint(1) DEFAULT '0',
  `estimated_reading_time_minutes` int DEFAULT NULL,
  `version` int DEFAULT '1',
  `object_last_modified` datetime DEFAULT NULL,
  `object_published_at` datetime DEFAULT NULL,
  `inclusive_language_score` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `object_type_and_sub_type` (`object_type`,`object_sub_type`),
  KEY `object_id_and_type` (`object_id`,`object_type`),
  KEY `permalink_hash_and_object_type` (`permalink_hash`,`object_type`),
  KEY `subpages` (`post_parent`,`object_type`,`post_status`,`object_id`),
  KEY `prominent_words` (`prominent_words_version`,`object_type`,`object_sub_type`,`post_status`),
  KEY `published_sitemap_index` (`object_published_at`,`is_robots_noindex`,`object_type`,`object_sub_type`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_yoast_indexable`
--

INSERT INTO `cp_yoast_indexable` (`id`, `permalink`, `permalink_hash`, `object_id`, `object_type`, `object_sub_type`, `author_id`, `post_parent`, `title`, `description`, `breadcrumb_title`, `post_status`, `is_public`, `is_protected`, `has_public_posts`, `number_of_pages`, `canonical`, `primary_focus_keyword`, `primary_focus_keyword_score`, `readability_score`, `is_cornerstone`, `is_robots_noindex`, `is_robots_nofollow`, `is_robots_noarchive`, `is_robots_noimageindex`, `is_robots_nosnippet`, `twitter_title`, `twitter_image`, `twitter_description`, `twitter_image_id`, `twitter_image_source`, `open_graph_title`, `open_graph_description`, `open_graph_image`, `open_graph_image_id`, `open_graph_image_source`, `open_graph_image_meta`, `link_count`, `incoming_link_count`, `prominent_words_version`, `created_at`, `updated_at`, `blog_id`, `language`, `region`, `schema_page_type`, `schema_article_type`, `has_ancestors`, `estimated_reading_time_minutes`, `version`, `object_last_modified`, `object_published_at`, `inclusive_language_score`) VALUES
(1, 'http://localhost/choose-life/%cf%80%ce%bf%ce%bb%ce%b9%cf%84%ce%b9%ce%ba%ce%ae-%ce%b1%cf%80%ce%bf%cf%81%cf%81%ce%ae%cf%84%ce%bf%cf%85/', '133:0018c9a8d3acf97863b6b6cc22f1eb82', 3, 'post', 'page', 1, 0, NULL, NULL, 'Πολιτική απορρήτου', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-08 15:20:52', '2024-04-18 13:31:22', 1, NULL, NULL, NULL, NULL, 0, NULL, 2, '2024-04-18 16:31:22', '2024-04-08 14:14:26', 0),
(2, 'http://localhost/choose-life/author/apostolis/', '46:2a31c0de00c5129ca665d2e15fb436e1', 1, 'user', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'https://2.gravatar.com/avatar/5054e4708300f59954fa5e61e1da58bb?s=500&d=mm&r=g', NULL, NULL, 'gravatar-image', NULL, NULL, 'https://2.gravatar.com/avatar/5054e4708300f59954fa5e61e1da58bb?s=500&d=mm&r=g', NULL, 'gravatar-image', NULL, NULL, NULL, NULL, '2024-04-08 15:20:52', '2024-05-01 08:47:20', 1, NULL, NULL, NULL, NULL, 0, NULL, 2, '2024-05-01 11:47:19', '2024-04-08 14:14:26', NULL),
(3, 'http://localhost/choose-life/', '29:422ba77233772097d18998e5def1f152', 2, 'post', 'page', 1, 0, NULL, NULL, 'Αρχική', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-08 15:20:52', '2024-04-11 10:28:06', 1, NULL, NULL, NULL, NULL, 0, 1, 2, '2024-04-11 13:28:06', '2024-04-08 14:14:26', 0),
(4, 'http://localhost/choose-life/2024/04/08/hello-world/', '52:001880e2ce7600493060df6fc5a13ab3', 1, 'post', 'post', 1, 0, NULL, NULL, 'Καλημέρα κόσμε!', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-08 15:20:52', '2024-04-08 12:20:52', 1, NULL, NULL, NULL, NULL, 0, NULL, 2, '2024-04-08 14:14:26', '2024-04-08 14:14:26', 0),
(5, 'http://localhost/choose-life/category/%ce%b1%cf%84%ce%b1%ce%be%ce%b9%ce%bd%cf%8c%ce%bc%ce%b7%cf%84%ce%b1/', '105:f87f1733f1e0285b67bf741216e26792', 1, 'term', 'category', NULL, NULL, NULL, NULL, 'Χωρίς κατηγορία', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-08 15:20:52', '2024-04-08 12:20:52', 1, NULL, NULL, NULL, NULL, 0, NULL, 2, '2024-04-08 14:14:26', '2024-04-08 14:14:26', NULL),
(6, NULL, NULL, NULL, 'system-page', '404', NULL, NULL, 'Η σελίδα δεν βρέθηκε %%sep%% %%sitename%%', NULL, 'Σφάλμα 404: Δεν βρέθηκε η σελίδα', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-08 15:20:52', '2024-04-08 12:20:52', 1, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, NULL),
(7, NULL, NULL, NULL, 'system-page', 'search-result', NULL, NULL, 'Αναζητήσατε %%searchphrase%% %%page%% %%sep%% %%sitename%%', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-08 15:20:52', '2024-04-08 12:20:52', 1, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, NULL),
(8, NULL, NULL, NULL, 'date-archive', NULL, NULL, NULL, '%%date%% %%page%% %%sep%% %%sitename%%', '', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-04-08 15:20:52', '2024-04-08 12:20:52', 1, NULL, NULL, NULL, NULL, 0, NULL, 1, NULL, NULL, NULL),
(9, 'http://localhost/choose-life/', '29:422ba77233772097d18998e5def1f152', NULL, 'home-page', NULL, NULL, NULL, '%%sitename%% %%page%% %%sep%% %%sitedesc%%', '', 'Αρχική', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, '%%sitename%%', '', '', '0', NULL, NULL, NULL, NULL, NULL, '2024-04-08 15:20:52', '2024-05-01 08:47:20', 1, NULL, NULL, NULL, NULL, 0, NULL, 2, '2024-05-01 11:47:19', '2024-04-08 14:14:26', NULL),
(10, 'http://localhost/choose-life/my-account/', '40:194a46358da1f13f3a0ecbd9262cb125', 18, 'post', 'page', 1, 0, NULL, NULL, 'My Account', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-19 10:13:41', '2024-05-01 08:47:20', 1, NULL, NULL, NULL, NULL, 0, 0, 2, '2024-05-01 11:47:19', '2024-04-19 10:13:43', 0),
(11, 'http://localhost/choose-life/checkout/', '38:1f9645fcc16b44d04167bccc7e651076', 20, 'post', 'page', 1, 0, NULL, NULL, 'Checkout', 'publish', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2024-04-19 10:13:50', '2024-04-29 07:03:33', 1, NULL, NULL, NULL, NULL, 0, 0, 2, '2024-04-29 10:03:33', '2024-04-19 10:13:52', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cp_yoast_indexable_hierarchy`
--

DROP TABLE IF EXISTS `cp_yoast_indexable_hierarchy`;
CREATE TABLE IF NOT EXISTS `cp_yoast_indexable_hierarchy` (
  `indexable_id` int UNSIGNED NOT NULL,
  `ancestor_id` int UNSIGNED NOT NULL,
  `depth` int UNSIGNED DEFAULT NULL,
  `blog_id` bigint NOT NULL DEFAULT '1',
  PRIMARY KEY (`indexable_id`,`ancestor_id`),
  KEY `indexable_id` (`indexable_id`),
  KEY `ancestor_id` (`ancestor_id`),
  KEY `depth` (`depth`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_yoast_indexable_hierarchy`
--

INSERT INTO `cp_yoast_indexable_hierarchy` (`indexable_id`, `ancestor_id`, `depth`, `blog_id`) VALUES
(1, 0, 0, 1),
(3, 0, 0, 1),
(4, 0, 0, 1),
(5, 0, 0, 1),
(10, 0, 0, 1),
(11, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cp_yoast_migrations`
--

DROP TABLE IF EXISTS `cp_yoast_migrations`;
CREATE TABLE IF NOT EXISTS `cp_yoast_migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(191) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cp_yoast_migrations_version` (`version`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `cp_yoast_migrations`
--

INSERT INTO `cp_yoast_migrations` (`id`, `version`) VALUES
(1, '20171228151840'),
(2, '20171228151841'),
(3, '20190529075038'),
(4, '20191011111109'),
(5, '20200408101900'),
(6, '20200420073606'),
(7, '20200428123747'),
(8, '20200428194858'),
(9, '20200429105310'),
(10, '20200430075614'),
(11, '20200430150130'),
(12, '20200507054848'),
(13, '20200513133401'),
(14, '20200609154515'),
(15, '20200616130143'),
(16, '20200617122511'),
(17, '20200702141921'),
(18, '20200728095334'),
(19, '20201202144329'),
(20, '20201216124002'),
(21, '20201216141134'),
(22, '20210817092415'),
(23, '20211020091404'),
(24, '20230417083836');

-- --------------------------------------------------------

--
-- Table structure for table `cp_yoast_primary_term`
--

DROP TABLE IF EXISTS `cp_yoast_primary_term`;
CREATE TABLE IF NOT EXISTS `cp_yoast_primary_term` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` bigint DEFAULT NULL,
  `term_id` bigint DEFAULT NULL,
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `blog_id` bigint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `post_taxonomy` (`post_id`,`taxonomy`),
  KEY `post_term` (`post_id`,`term_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cp_yoast_seo_links`
--

DROP TABLE IF EXISTS `cp_yoast_seo_links`;
CREATE TABLE IF NOT EXISTS `cp_yoast_seo_links` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `post_id` bigint UNSIGNED DEFAULT NULL,
  `target_post_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(8) DEFAULT NULL,
  `indexable_id` int UNSIGNED DEFAULT NULL,
  `target_indexable_id` int UNSIGNED DEFAULT NULL,
  `height` int UNSIGNED DEFAULT NULL,
  `width` int UNSIGNED DEFAULT NULL,
  `size` int UNSIGNED DEFAULT NULL,
  `language` varchar(32) DEFAULT NULL,
  `region` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_direction` (`post_id`,`type`),
  KEY `indexable_link_direction` (`indexable_id`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
