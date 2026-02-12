/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: fluxbb
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB-ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fluxbb_bans`
--

DROP TABLE IF EXISTS `fluxbb_bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `expire` int(10) unsigned DEFAULT NULL,
  `ban_creator` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fluxbb_bans_username_idx` (`username`(25))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_bans`
--

LOCK TABLES `fluxbb_bans` WRITE;
/*!40000 ALTER TABLE `fluxbb_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_categories`
--

DROP TABLE IF EXISTS `fluxbb_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(80) NOT NULL DEFAULT 'New Category',
  `disp_position` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_categories`
--

LOCK TABLES `fluxbb_categories` WRITE;
/*!40000 ALTER TABLE `fluxbb_categories` DISABLE KEYS */;
INSERT INTO `fluxbb_categories` VALUES
(1,'General',1),
(2,'Public Category 1',0),
(3,'Public Category 2',0),
(4,'Private Category 1',0);
/*!40000 ALTER TABLE `fluxbb_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_censoring`
--

DROP TABLE IF EXISTS `fluxbb_censoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_censoring` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `search_for` varchar(60) NOT NULL DEFAULT '',
  `replace_with` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_censoring`
--

LOCK TABLES `fluxbb_censoring` WRITE;
/*!40000 ALTER TABLE `fluxbb_censoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_censoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_config`
--

DROP TABLE IF EXISTS `fluxbb_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_config` (
  `conf_name` varchar(255) NOT NULL DEFAULT '',
  `conf_value` text DEFAULT NULL,
  PRIMARY KEY (`conf_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_config`
--

LOCK TABLES `fluxbb_config` WRITE;
/*!40000 ALTER TABLE `fluxbb_config` DISABLE KEYS */;
INSERT INTO `fluxbb_config` VALUES
('o_cur_version','1.5.11'),
('o_database_revision','24'),
('o_searchindex_revision','2'),
('o_parser_revision','2'),
('o_board_title','My FluxBB Forum'),
('o_board_desc','<p><span>Unfortunately no one can be told what FluxBB is - you have to see it for yourself.</span></p>'),
('o_default_timezone','0'),
('o_time_format','H:i:s'),
('o_date_format','Y-m-d'),
('o_timeout_visit','1800'),
('o_timeout_online','300'),
('o_redirect_delay','1'),
('o_show_version','0'),
('o_show_user_info','1'),
('o_show_post_count','1'),
('o_signatures','1'),
('o_smilies','1'),
('o_smilies_sig','1'),
('o_make_links','1'),
('o_default_lang','English'),
('o_default_style','Air'),
('o_default_user_group','4'),
('o_topic_review','15'),
('o_disp_topics_default','30'),
('o_disp_posts_default','25'),
('o_indent_num_spaces','4'),
('o_quote_depth','3'),
('o_quickpost','1'),
('o_users_online','1'),
('o_censoring','0'),
('o_show_dot','0'),
('o_topic_views','1'),
('o_quickjump','1'),
('o_gzip','0'),
('o_additional_navlinks','<a href=\"https://github.com/jtervone/fluxbb-archiver\">FluxBB Archiver</a>'),
('o_report_method','0'),
('o_regs_report','0'),
('o_default_email_setting','1'),
('o_mailing_list','fluxbb-archiver@this-is-invalid.email'),
('o_avatars','1'),
('o_avatars_dir','img/avatars'),
('o_avatars_width','60'),
('o_avatars_height','60'),
('o_avatars_size','10240'),
('o_search_all_forums','1'),
('o_base_url','http://localhost:8080/input'),
('o_admin_email','fluxbb-archiver@this-is-invalid.email'),
('o_webmaster_email','fluxbb-archiver@this-is-invalid.email'),
('o_forum_subscriptions','1'),
('o_topic_subscriptions','1'),
('o_smtp_host',NULL),
('o_smtp_user',NULL),
('o_smtp_pass',NULL),
('o_smtp_ssl','0'),
('o_regs_allow','1'),
('o_regs_verify','0'),
('o_announcement','0'),
('o_announcement_message','Enter your announcement here.'),
('o_rules','0'),
('o_rules_message','Enter your rules here'),
('o_maintenance','0'),
('o_maintenance_message','The forums are temporarily down for maintenance. Please try again in a few minutes.'),
('o_default_dst','0'),
('o_feed_type','2'),
('o_feed_ttl','0'),
('p_message_bbcode','1'),
('p_message_img_tag','1'),
('p_message_all_caps','1'),
('p_subject_all_caps','1'),
('p_sig_all_caps','1'),
('p_sig_bbcode','1'),
('p_sig_img_tag','0'),
('p_sig_length','400'),
('p_sig_lines','4'),
('p_allow_banned_email','1'),
('p_allow_dupe_email','0'),
('p_force_guest_email','1');
/*!40000 ALTER TABLE `fluxbb_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_forum_perms`
--

DROP TABLE IF EXISTS `fluxbb_forum_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_forum_perms` (
  `group_id` int(10) NOT NULL DEFAULT 0,
  `forum_id` int(10) NOT NULL DEFAULT 0,
  `read_forum` tinyint(1) NOT NULL DEFAULT 1,
  `post_replies` tinyint(1) NOT NULL DEFAULT 1,
  `post_topics` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`group_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_forum_perms`
--

LOCK TABLES `fluxbb_forum_perms` WRITE;
/*!40000 ALTER TABLE `fluxbb_forum_perms` DISABLE KEYS */;
INSERT INTO `fluxbb_forum_perms` VALUES
(3,5,0,0,0),
(4,5,0,0,0);
/*!40000 ALTER TABLE `fluxbb_forum_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_forum_subscriptions`
--

DROP TABLE IF EXISTS `fluxbb_forum_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_forum_subscriptions` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `forum_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`forum_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_forum_subscriptions`
--

LOCK TABLES `fluxbb_forum_subscriptions` WRITE;
/*!40000 ALTER TABLE `fluxbb_forum_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_forum_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_forums`
--

DROP TABLE IF EXISTS `fluxbb_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_name` varchar(80) NOT NULL DEFAULT 'New forum',
  `forum_desc` text DEFAULT NULL,
  `redirect_url` varchar(100) DEFAULT NULL,
  `moderators` text DEFAULT NULL,
  `num_topics` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `num_posts` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `last_post` int(10) unsigned DEFAULT NULL,
  `last_post_id` int(10) unsigned DEFAULT NULL,
  `last_poster` varchar(200) DEFAULT NULL,
  `sort_by` tinyint(1) NOT NULL DEFAULT 0,
  `disp_position` int(10) NOT NULL DEFAULT 0,
  `cat_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_forums`
--

LOCK TABLES `fluxbb_forums` WRITE;
/*!40000 ALTER TABLE `fluxbb_forums` DISABLE KEYS */;
INSERT INTO `fluxbb_forums` VALUES
(1,'Announcements','News from the community',NULL,NULL,1,3,1770920450,3,'fluxbb-member',0,1,1),
(2,'Public Forum 1.2','Public Forum 1.1 description',NULL,NULL,52,101,1770926000,106,'fluxbb-member',0,2,2),
(3,'Public Forum 2.1','Public Forum 2.1',NULL,NULL,0,0,NULL,NULL,NULL,0,0,3),
(4,'Public Forum 1.1','Public Forum 1.1 description',NULL,NULL,1,1,1770920549,4,'fluxbb-member',0,1,2),
(5,'Private Forum 1.1','Private Forum 1.1 description',NULL,NULL,0,0,NULL,NULL,NULL,0,1,4),
(6,'Public Empty Forum','Public Empty Forum description',NULL,NULL,0,0,NULL,NULL,NULL,0,3,2),
(7,'Private Empty Forum','Private Empty Forum description',NULL,NULL,1,1,1770920810,7,'fluxbb-member',0,2,4);
/*!40000 ALTER TABLE `fluxbb_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_groups`
--

DROP TABLE IF EXISTS `fluxbb_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_groups` (
  `g_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `g_title` varchar(50) NOT NULL DEFAULT '',
  `g_user_title` varchar(50) DEFAULT NULL,
  `g_promote_min_posts` int(10) unsigned NOT NULL DEFAULT 0,
  `g_promote_next_group` int(10) unsigned NOT NULL DEFAULT 0,
  `g_moderator` tinyint(1) NOT NULL DEFAULT 0,
  `g_mod_edit_users` tinyint(1) NOT NULL DEFAULT 0,
  `g_mod_rename_users` tinyint(1) NOT NULL DEFAULT 0,
  `g_mod_change_passwords` tinyint(1) NOT NULL DEFAULT 0,
  `g_mod_ban_users` tinyint(1) NOT NULL DEFAULT 0,
  `g_mod_promote_users` tinyint(1) NOT NULL DEFAULT 0,
  `g_read_board` tinyint(1) NOT NULL DEFAULT 1,
  `g_view_users` tinyint(1) NOT NULL DEFAULT 1,
  `g_post_replies` tinyint(1) NOT NULL DEFAULT 1,
  `g_post_topics` tinyint(1) NOT NULL DEFAULT 1,
  `g_edit_posts` tinyint(1) NOT NULL DEFAULT 1,
  `g_delete_posts` tinyint(1) NOT NULL DEFAULT 1,
  `g_delete_topics` tinyint(1) NOT NULL DEFAULT 1,
  `g_post_links` tinyint(1) NOT NULL DEFAULT 1,
  `g_set_title` tinyint(1) NOT NULL DEFAULT 1,
  `g_search` tinyint(1) NOT NULL DEFAULT 1,
  `g_search_users` tinyint(1) NOT NULL DEFAULT 1,
  `g_send_email` tinyint(1) NOT NULL DEFAULT 1,
  `g_post_flood` smallint(6) NOT NULL DEFAULT 30,
  `g_search_flood` smallint(6) NOT NULL DEFAULT 30,
  `g_email_flood` smallint(6) NOT NULL DEFAULT 60,
  `g_report_flood` smallint(6) NOT NULL DEFAULT 60,
  PRIMARY KEY (`g_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_groups`
--

LOCK TABLES `fluxbb_groups` WRITE;
/*!40000 ALTER TABLE `fluxbb_groups` DISABLE KEYS */;
INSERT INTO `fluxbb_groups` VALUES
(1,'Administrators','Administrator',0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0),
(2,'Moderators','Moderator',0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0),
(3,'Guests',NULL,0,0,0,0,0,0,0,0,1,1,0,0,0,0,0,1,0,1,1,0,60,30,0,0),
(4,'Members',NULL,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,0,1,1,1,60,30,60,60);
/*!40000 ALTER TABLE `fluxbb_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_online`
--

DROP TABLE IF EXISTS `fluxbb_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_online` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 1,
  `ident` varchar(200) NOT NULL DEFAULT '',
  `logged` int(10) unsigned NOT NULL DEFAULT 0,
  `idle` tinyint(1) NOT NULL DEFAULT 0,
  `last_post` int(10) unsigned DEFAULT NULL,
  `last_search` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `fluxbb_online_user_id_ident_idx` (`user_id`,`ident`(25)),
  KEY `fluxbb_online_ident_idx` (`ident`(25)),
  KEY `fluxbb_online_logged_idx` (`logged`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_online`
--

LOCK TABLES `fluxbb_online` WRITE;
/*!40000 ALTER TABLE `fluxbb_online` DISABLE KEYS */;
INSERT INTO `fluxbb_online` VALUES
(2,'fluxbb-archiver',1770921376,0,NULL,NULL);
/*!40000 ALTER TABLE `fluxbb_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_posts`
--

DROP TABLE IF EXISTS `fluxbb_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster` varchar(200) NOT NULL DEFAULT '',
  `poster_id` int(10) unsigned NOT NULL DEFAULT 1,
  `poster_ip` varchar(39) DEFAULT NULL,
  `poster_email` varchar(80) DEFAULT NULL,
  `message` mediumtext DEFAULT NULL,
  `hide_smilies` tinyint(1) NOT NULL DEFAULT 0,
  `posted` int(10) unsigned NOT NULL DEFAULT 0,
  `edited` int(10) unsigned DEFAULT NULL,
  `edited_by` varchar(200) DEFAULT NULL,
  `topic_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fluxbb_posts_topic_id_idx` (`topic_id`),
  KEY `fluxbb_posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_posts`
--

LOCK TABLES `fluxbb_posts` WRITE;
/*!40000 ALTER TABLE `fluxbb_posts` DISABLE KEYS */;
INSERT INTO `fluxbb_posts` VALUES
(1,'fluxbb-archiver',2,'172.20.0.1',NULL,'If you are looking at this (which I guess you are), the install of FluxBB appears to have worked! Now log in and head over to the administration control panel to configure your forum.',0,1770911360,NULL,NULL,1),
(2,'fluxbb-member',3,'172.20.0.1',NULL,'Great — looks like everything installed successfully! I’ll log in now and head to the admin control panel to finish setting up the forum. Thanks!',0,1770920384,NULL,NULL,1),
(3,'fluxbb-member',3,'172.20.0.1',NULL,'Looks like the install worked, but I don’t seem to have access to the administration control panel. Could you check if my account has admin permissions or let me know how to access it?',0,1770920450,NULL,NULL,1),
(4,'fluxbb-member',3,'172.20.0.1',NULL,'Hello everyone! This is a test post to make sure our new bulletin board system is working correctly.\n\nIf you can see this, feel free to reply and say hello, test formatting, or try out any features like attachments, emojis, or quotes.\n\nThanks for helping test the forum!',0,1770920549,NULL,NULL,2),
(107,'fluxbb-archiver',2,'172.20.0.1',NULL,'Welcome to the forum!\n\nPlease take a moment to read these important guidelines before posting:\n\n• Be respectful and courteous to other members• Post in the correct category• No spam, advertising, or illegal content• Keep discussions relevant and constructive• Follow moderator instructions\n\nFailure to follow the rules may result in post removal or account action.\n\nIf you have questions, contact the moderation team.Thanks for helping keep the community friendly and organized!',0,1770921368,NULL,NULL,56),
(6,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 1\nTimestamp: 2026-02-12 20:25:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.\n\nFeel free to duplicate this post and change the number to create additional entries.',0,1770920750,NULL,NULL,4),
(7,'fluxbb-member',3,'172.20.0.1',NULL,'Hello everyone,\n\nThis is a test post inside the private forum area to confirm that permissions and access controls are working correctly.\n\nIf you can read this, your private section access is active. Feel free to reply to confirm visibility or test attachments, formatting, or notifications.\n\nThanks for helping verify the private forum setup!',0,1770920810,NULL,NULL,5),
(8,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 2\nTimestamp: 2026-02-12 20:26:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770920810,NULL,NULL,4),
(9,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 3\nTimestamp: 2026-02-12 20:27:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770920870,NULL,NULL,4),
(10,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 4\nTimestamp: 2026-02-12 20:28:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770920930,NULL,NULL,4),
(11,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 5\nTimestamp: 2026-02-12 20:29:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770920990,NULL,NULL,4),
(12,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 6\nTimestamp: 2026-02-12 20:30:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921050,NULL,NULL,4),
(13,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 7\nTimestamp: 2026-02-12 20:31:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921110,NULL,NULL,4),
(14,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 8\nTimestamp: 2026-02-12 20:32:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921170,NULL,NULL,4),
(15,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 9\nTimestamp: 2026-02-12 20:33:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921230,NULL,NULL,4),
(16,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 10\nTimestamp: 2026-02-12 20:34:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921290,NULL,NULL,4),
(17,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 11\nTimestamp: 2026-02-12 20:35:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921350,NULL,NULL,4),
(18,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 12\nTimestamp: 2026-02-12 20:36:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921410,NULL,NULL,4),
(19,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 13\nTimestamp: 2026-02-12 20:37:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921470,NULL,NULL,4),
(20,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 14\nTimestamp: 2026-02-12 20:38:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921530,NULL,NULL,4),
(21,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 15\nTimestamp: 2026-02-12 20:39:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921590,NULL,NULL,4),
(22,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 16\nTimestamp: 2026-02-12 20:40:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921650,NULL,NULL,4),
(23,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 17\nTimestamp: 2026-02-12 20:41:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921710,NULL,NULL,4),
(24,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 18\nTimestamp: 2026-02-12 20:42:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921770,NULL,NULL,4),
(25,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 19\nTimestamp: 2026-02-12 20:43:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921830,NULL,NULL,4),
(26,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 20\nTimestamp: 2026-02-12 20:44:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921890,NULL,NULL,4),
(27,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 21\nTimestamp: 2026-02-12 20:45:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770921950,NULL,NULL,4),
(28,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 22\nTimestamp: 2026-02-12 20:46:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922010,NULL,NULL,4),
(29,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 23\nTimestamp: 2026-02-12 20:47:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922070,NULL,NULL,4),
(30,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 24\nTimestamp: 2026-02-12 20:48:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922130,NULL,NULL,4),
(31,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 25\nTimestamp: 2026-02-12 20:49:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922190,NULL,NULL,4),
(32,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 26\nTimestamp: 2026-02-12 20:50:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922250,NULL,NULL,4),
(33,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 27\nTimestamp: 2026-02-12 20:51:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922310,NULL,NULL,4),
(34,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 28\nTimestamp: 2026-02-12 20:52:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922370,NULL,NULL,4),
(35,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 29\nTimestamp: 2026-02-12 20:53:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922430,NULL,NULL,4),
(36,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 30\nTimestamp: 2026-02-12 20:54:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922490,NULL,NULL,4),
(37,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 31\nTimestamp: 2026-02-12 20:55:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922550,NULL,NULL,4),
(38,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 32\nTimestamp: 2026-02-12 20:56:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922610,NULL,NULL,4),
(39,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 33\nTimestamp: 2026-02-12 20:57:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922670,NULL,NULL,4),
(40,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 34\nTimestamp: 2026-02-12 20:58:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922730,NULL,NULL,4),
(41,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 35\nTimestamp: 2026-02-12 20:59:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922790,NULL,NULL,4),
(42,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 36\nTimestamp: 2026-02-12 21:00:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922850,NULL,NULL,4),
(43,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 37\nTimestamp: 2026-02-12 21:01:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922910,NULL,NULL,4),
(44,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 38\nTimestamp: 2026-02-12 21:02:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770922970,NULL,NULL,4),
(45,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 39\nTimestamp: 2026-02-12 21:03:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923030,NULL,NULL,4),
(46,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 40\nTimestamp: 2026-02-12 21:04:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923090,NULL,NULL,4),
(47,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 41\nTimestamp: 2026-02-12 21:05:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923150,NULL,NULL,4),
(48,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 42\nTimestamp: 2026-02-12 21:06:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923210,NULL,NULL,4),
(49,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 43\nTimestamp: 2026-02-12 21:07:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923270,NULL,NULL,4),
(50,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 44\nTimestamp: 2026-02-12 21:08:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923330,NULL,NULL,4),
(51,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 45\nTimestamp: 2026-02-12 21:09:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923390,NULL,NULL,4),
(52,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 46\nTimestamp: 2026-02-12 21:10:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923450,NULL,NULL,4),
(53,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 47\nTimestamp: 2026-02-12 21:11:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923510,NULL,NULL,4),
(54,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 48\nTimestamp: 2026-02-12 21:12:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923570,NULL,NULL,4),
(55,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 49\nTimestamp: 2026-02-12 21:13:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923630,NULL,NULL,4),
(56,'fluxbb-member',3,'172.20.0.1',NULL,'This is a pagination test post to help generate enough content to span multiple pages in the forum.\n\nPost number: 50\nTimestamp: 2026-02-12 21:14:00\n\nNothing important here — just testing how the forum handles page navigation, loading speed, and post ordering.',0,1770923690,NULL,NULL,4),
(57,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 1 - Forum navigation testing\". Created for pagination testing.',0,1770923060,NULL,NULL,6),
(58,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 2 - Forum navigation testing\". Created for pagination testing.',0,1770923120,NULL,NULL,7),
(59,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 3 - Forum navigation testing\". Created for pagination testing.',0,1770923180,NULL,NULL,8),
(60,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 4 - Forum navigation testing\". Created for pagination testing.',0,1770923240,NULL,NULL,9),
(61,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 5 - Forum navigation testing\". Created for pagination testing.',0,1770923300,NULL,NULL,10),
(62,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 6 - Forum navigation testing\". Created for pagination testing.',0,1770923360,NULL,NULL,11),
(63,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 7 - Forum navigation testing\". Created for pagination testing.',0,1770923420,NULL,NULL,12),
(64,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 8 - Forum navigation testing\". Created for pagination testing.',0,1770923480,NULL,NULL,13),
(65,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 9 - Forum navigation testing\". Created for pagination testing.',0,1770923540,NULL,NULL,14),
(66,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 10 - Forum navigation testing\". Created for pagination testing.',0,1770923600,NULL,NULL,15),
(67,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 11 - Forum navigation testing\". Created for pagination testing.',0,1770923660,NULL,NULL,16),
(68,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 12 - Forum navigation testing\". Created for pagination testing.',0,1770923720,NULL,NULL,17),
(69,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 13 - Forum navigation testing\". Created for pagination testing.',0,1770923780,NULL,NULL,18),
(70,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 14 - Forum navigation testing\". Created for pagination testing.',0,1770923840,NULL,NULL,19),
(71,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 15 - Forum navigation testing\". Created for pagination testing.',0,1770923900,NULL,NULL,20),
(72,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 16 - Forum navigation testing\". Created for pagination testing.',0,1770923960,NULL,NULL,21),
(73,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 17 - Forum navigation testing\". Created for pagination testing.',0,1770924020,NULL,NULL,22),
(74,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 18 - Forum navigation testing\". Created for pagination testing.',0,1770924080,NULL,NULL,23),
(75,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 19 - Forum navigation testing\". Created for pagination testing.',0,1770924140,NULL,NULL,24),
(76,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 20 - Forum navigation testing\". Created for pagination testing.',0,1770924200,NULL,NULL,25),
(77,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 21 - Forum navigation testing\". Created for pagination testing.',0,1770924260,NULL,NULL,26),
(78,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 22 - Forum navigation testing\". Created for pagination testing.',0,1770924320,NULL,NULL,27),
(79,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 23 - Forum navigation testing\". Created for pagination testing.',0,1770924380,NULL,NULL,28),
(80,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 24 - Forum navigation testing\". Created for pagination testing.',0,1770924440,NULL,NULL,29),
(81,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 25 - Forum navigation testing\". Created for pagination testing.',0,1770924500,NULL,NULL,30),
(82,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 26 - Forum navigation testing\". Created for pagination testing.',0,1770924560,NULL,NULL,31),
(83,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 27 - Forum navigation testing\". Created for pagination testing.',0,1770924620,NULL,NULL,32),
(84,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 28 - Forum navigation testing\". Created for pagination testing.',0,1770924680,NULL,NULL,33),
(85,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 29 - Forum navigation testing\". Created for pagination testing.',0,1770924740,NULL,NULL,34),
(86,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 30 - Forum navigation testing\". Created for pagination testing.',0,1770924800,NULL,NULL,35),
(87,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 31 - Forum navigation testing\". Created for pagination testing.',0,1770924860,NULL,NULL,36),
(88,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 32 - Forum navigation testing\". Created for pagination testing.',0,1770924920,NULL,NULL,37),
(89,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 33 - Forum navigation testing\". Created for pagination testing.',0,1770924980,NULL,NULL,38),
(90,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 34 - Forum navigation testing\". Created for pagination testing.',0,1770925040,NULL,NULL,39),
(91,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 35 - Forum navigation testing\". Created for pagination testing.',0,1770925100,NULL,NULL,40),
(92,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 36 - Forum navigation testing\". Created for pagination testing.',0,1770925160,NULL,NULL,41),
(93,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 37 - Forum navigation testing\". Created for pagination testing.',0,1770925220,NULL,NULL,42),
(94,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 38 - Forum navigation testing\". Created for pagination testing.',0,1770925280,NULL,NULL,43),
(95,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 39 - Forum navigation testing\". Created for pagination testing.',0,1770925340,NULL,NULL,44),
(96,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 40 - Forum navigation testing\". Created for pagination testing.',0,1770925400,NULL,NULL,45),
(97,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 41 - Forum navigation testing\". Created for pagination testing.',0,1770925460,NULL,NULL,46),
(98,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 42 - Forum navigation testing\". Created for pagination testing.',0,1770925520,NULL,NULL,47),
(99,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 43 - Forum navigation testing\". Created for pagination testing.',0,1770925580,NULL,NULL,48),
(100,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 44 - Forum navigation testing\". Created for pagination testing.',0,1770925640,NULL,NULL,49),
(101,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 45 - Forum navigation testing\". Created for pagination testing.',0,1770925700,NULL,NULL,50),
(102,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 46 - Forum navigation testing\". Created for pagination testing.',0,1770925760,NULL,NULL,51),
(103,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 47 - Forum navigation testing\". Created for pagination testing.',0,1770925820,NULL,NULL,52),
(104,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 48 - Forum navigation testing\". Created for pagination testing.',0,1770925880,NULL,NULL,53),
(105,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 49 - Forum navigation testing\". Created for pagination testing.',0,1770925940,NULL,NULL,54),
(106,'fluxbb-member',3,'172.20.0.1',NULL,'This is the first post in \"Test Topic 50 - Forum navigation testing\". Created for pagination testing.',0,1770926000,NULL,NULL,55);
/*!40000 ALTER TABLE `fluxbb_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_reports`
--

DROP TABLE IF EXISTS `fluxbb_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL DEFAULT 0,
  `topic_id` int(10) unsigned NOT NULL DEFAULT 0,
  `forum_id` int(10) unsigned NOT NULL DEFAULT 0,
  `reported_by` int(10) unsigned NOT NULL DEFAULT 0,
  `created` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `zapped` int(10) unsigned DEFAULT NULL,
  `zapped_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fluxbb_reports_zapped_idx` (`zapped`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_reports`
--

LOCK TABLES `fluxbb_reports` WRITE;
/*!40000 ALTER TABLE `fluxbb_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_search_cache`
--

DROP TABLE IF EXISTS `fluxbb_search_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_search_cache` (
  `id` int(10) unsigned NOT NULL DEFAULT 0,
  `ident` varchar(200) NOT NULL DEFAULT '',
  `search_data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fluxbb_search_cache_ident_idx` (`ident`(8))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_search_cache`
--

LOCK TABLES `fluxbb_search_cache` WRITE;
/*!40000 ALTER TABLE `fluxbb_search_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_search_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_search_matches`
--

DROP TABLE IF EXISTS `fluxbb_search_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_search_matches` (
  `post_id` int(10) unsigned NOT NULL DEFAULT 0,
  `word_id` int(10) unsigned NOT NULL DEFAULT 0,
  `subject_match` tinyint(1) NOT NULL DEFAULT 0,
  KEY `fluxbb_search_matches_word_id_idx` (`word_id`),
  KEY `fluxbb_search_matches_post_id_idx` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_search_matches`
--

LOCK TABLES `fluxbb_search_matches` WRITE;
/*!40000 ALTER TABLE `fluxbb_search_matches` DISABLE KEYS */;
INSERT INTO `fluxbb_search_matches` VALUES
(1,1,0),
(1,2,0),
(1,3,0),
(1,4,0),
(1,5,0),
(1,6,0),
(1,7,0),
(1,8,0),
(1,9,0),
(1,10,0),
(1,11,0),
(1,12,0),
(1,13,0),
(1,14,1),
(1,15,1),
(2,7,0),
(2,8,0),
(2,10,0),
(2,11,0),
(2,13,0),
(2,16,0),
(2,17,0),
(2,18,0),
(2,19,0),
(2,20,0),
(2,21,0),
(2,22,0),
(2,23,0),
(3,3,0),
(3,6,0),
(3,9,0),
(3,10,0),
(3,11,0),
(3,17,0),
(3,21,0),
(3,24,0),
(3,25,0),
(3,26,0),
(3,27,0),
(3,28,0),
(3,29,0),
(3,30,0),
(4,13,0),
(4,14,0),
(4,31,0),
(4,32,0),
(4,33,0),
(4,34,0),
(4,35,0),
(4,36,0),
(4,37,0),
(4,38,0),
(4,39,0),
(4,40,0),
(4,41,0),
(4,42,0),
(4,43,0),
(4,44,0),
(4,45,0),
(4,46,0),
(4,47,0),
(4,48,0),
(4,49,0),
(4,50,0),
(4,36,1),
(4,35,1),
(4,34,1),
(4,32,1),
(4,14,1),
(4,51,1),
(107,85,0),
(107,118,0),
(107,119,0),
(107,120,0),
(107,121,0),
(107,122,0),
(107,123,0),
(107,124,0),
(107,125,0),
(107,126,0),
(107,127,0),
(107,128,0),
(107,129,0),
(107,130,0),
(107,131,0),
(107,132,0),
(107,133,0),
(107,134,0),
(107,135,0),
(107,136,0),
(107,137,0),
(107,138,0),
(107,139,0),
(107,140,0),
(107,141,0),
(107,142,0),
(107,51,0),
(107,50,0),
(107,32,0),
(107,28,0),
(107,13,0),
(6,13,0),
(6,14,0),
(6,32,0),
(6,40,0),
(6,41,0),
(6,81,0),
(6,82,0),
(6,83,0),
(6,84,0),
(6,85,0),
(6,86,0),
(6,87,0),
(6,88,0),
(6,89,0),
(6,90,0),
(6,91,0),
(6,92,0),
(6,93,0),
(6,94,0),
(6,95,0),
(6,96,0),
(6,97,0),
(6,98,0),
(6,99,0),
(6,100,0),
(6,101,0),
(6,102,0),
(6,103,0),
(6,104,0),
(6,81,1),
(6,32,1),
(6,14,1),
(7,13,0),
(7,14,0),
(7,26,0),
(7,29,0),
(7,31,0),
(7,32,0),
(7,38,0),
(7,39,0),
(7,40,0),
(7,41,0),
(7,42,0),
(7,44,0),
(7,47,0),
(7,50,0),
(7,64,0),
(7,105,0),
(7,106,0),
(7,107,0),
(7,108,0),
(7,109,0),
(7,110,0),
(7,111,0),
(7,112,0),
(7,113,0),
(7,114,0),
(7,115,0),
(7,117,1),
(7,116,1),
(7,64,1),
(7,110,1),
(7,14,1),
(107,92,0),
(107,109,0),
(107,143,0),
(107,144,0),
(107,145,0),
(107,146,0),
(107,147,0),
(107,149,1),
(107,13,1),
(107,92,1),
(107,148,1),
(107,109,1),
(107,137,1);
/*!40000 ALTER TABLE `fluxbb_search_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_search_words`
--

DROP TABLE IF EXISTS `fluxbb_search_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_search_words` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`word`),
  KEY `fluxbb_search_words_id_idx` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_search_words`
--

LOCK TABLES `fluxbb_search_words` WRITE;
/*!40000 ALTER TABLE `fluxbb_search_words` DISABLE KEYS */;
INSERT INTO `fluxbb_search_words` VALUES
(1,'looking'),
(2,'guess'),
(3,'install'),
(4,'fluxbb'),
(5,'appears'),
(6,'worked'),
(7,'log'),
(8,'head'),
(9,'administration'),
(10,'control'),
(11,'panel'),
(12,'configure'),
(13,'forum'),
(14,'test'),
(15,'topic'),
(16,'great'),
(17,'looks'),
(18,'everything'),
(19,'installed'),
(20,'successfully'),
(21,'admin'),
(22,'finish'),
(23,'setting'),
(24,'don'),
(25,'seem'),
(26,'access'),
(27,'check'),
(28,'account'),
(29,'permissions'),
(30,'let'),
(31,'hello'),
(32,'post'),
(33,'sure'),
(34,'new'),
(35,'bulletin'),
(36,'board'),
(37,'system'),
(38,'working'),
(39,'correctly'),
(40,'feel'),
(41,'free'),
(42,'reply'),
(43,'say'),
(44,'formatting'),
(45,'try'),
(46,'features'),
(47,'attachments'),
(48,'emojis'),
(49,'quotes'),
(50,'helping'),
(51,'welcome'),
(142,'contact'),
(141,'questions'),
(140,'action'),
(139,'removal'),
(138,'result'),
(137,'rules'),
(136,'failure'),
(59,'premium'),
(135,'instructions'),
(134,'moderator'),
(133,'follow'),
(64,'private'),
(132,'constructive'),
(131,'relevant'),
(130,'discussions'),
(129,'keep'),
(128,'illegal'),
(127,'advertising'),
(126,'spam'),
(125,'category'),
(124,'correct'),
(123,'members'),
(122,'courteous'),
(121,'respectful'),
(120,'posting'),
(119,'guidelines'),
(118,'moment'),
(81,'pagination'),
(82,'help'),
(83,'generate'),
(84,'enough'),
(85,'content'),
(86,'span'),
(87,'multiple'),
(88,'pages'),
(89,'number'),
(90,'timestamp'),
(91,'2026-02-12'),
(92,'important'),
(93,'testing'),
(94,'handles'),
(95,'page'),
(96,'navigation'),
(97,'loading'),
(98,'speed'),
(99,'ordering'),
(100,'duplicate'),
(101,'change'),
(102,'create'),
(103,'additional'),
(104,'entries'),
(105,'inside'),
(106,'area'),
(107,'confirm'),
(108,'controls'),
(109,'read'),
(110,'section'),
(111,'active'),
(112,'visibility'),
(113,'notifications'),
(114,'verify'),
(115,'setup'),
(116,'member'),
(117,'check-in'),
(143,'moderation'),
(144,'team'),
(145,'community'),
(146,'friendly'),
(147,'organized'),
(148,'information'),
(149,'first');
/*!40000 ALTER TABLE `fluxbb_search_words` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_topic_subscriptions`
--

DROP TABLE IF EXISTS `fluxbb_topic_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_topic_subscriptions` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `topic_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_topic_subscriptions`
--

LOCK TABLES `fluxbb_topic_subscriptions` WRITE;
/*!40000 ALTER TABLE `fluxbb_topic_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `fluxbb_topic_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_topics`
--

DROP TABLE IF EXISTS `fluxbb_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster` varchar(200) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `posted` int(10) unsigned NOT NULL DEFAULT 0,
  `first_post_id` int(10) unsigned NOT NULL DEFAULT 0,
  `last_post` int(10) unsigned NOT NULL DEFAULT 0,
  `last_post_id` int(10) unsigned NOT NULL DEFAULT 0,
  `last_poster` varchar(200) DEFAULT NULL,
  `num_views` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `num_replies` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `closed` tinyint(1) NOT NULL DEFAULT 0,
  `sticky` tinyint(1) NOT NULL DEFAULT 0,
  `moved_to` int(10) unsigned DEFAULT NULL,
  `forum_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fluxbb_topics_forum_id_idx` (`forum_id`),
  KEY `fluxbb_topics_moved_to_idx` (`moved_to`),
  KEY `fluxbb_topics_last_post_idx` (`last_post`),
  KEY `fluxbb_topics_first_post_id_idx` (`first_post_id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_topics`
--

LOCK TABLES `fluxbb_topics` WRITE;
/*!40000 ALTER TABLE `fluxbb_topics` DISABLE KEYS */;
INSERT INTO `fluxbb_topics` VALUES
(1,'fluxbb-archiver','Test topic',1770911360,1,1770920450,3,'fluxbb-member',3,2,0,0,NULL,1),
(2,'fluxbb-member','Welcome — Test Post for Our New Bulletin Board',1770920549,4,1770920549,4,'fluxbb-member',1,0,0,0,NULL,4),
(56,'fluxbb-archiver','Forum Rules & Important Information (Read First)',1770921368,107,1770921368,107,'fluxbb-archiver',2,0,0,1,NULL,2),
(4,'fluxbb-member','Pagination Test Post #1',1770920750,6,1770923690,56,'fluxbb-member',8,49,0,0,NULL,2),
(5,'fluxbb-member','Private Section Test — Member Check-In',1770920810,7,1770920810,7,'fluxbb-member',1,0,0,0,NULL,7),
(6,'fluxbb-member','Test Topic 1 - Forum navigation testing',1770923060,57,1770923060,57,'fluxbb-member',0,0,0,0,NULL,2),
(7,'fluxbb-member','Test Topic 2 - Forum navigation testing',1770923120,58,1770923120,58,'fluxbb-member',0,0,0,0,NULL,2),
(8,'fluxbb-member','Test Topic 3 - Forum navigation testing',1770923180,59,1770923180,59,'fluxbb-member',0,0,0,0,NULL,2),
(9,'fluxbb-member','Test Topic 4 - Forum navigation testing',1770923240,60,1770923240,60,'fluxbb-member',0,0,0,0,NULL,2),
(10,'fluxbb-member','Test Topic 5 - Forum navigation testing',1770923300,61,1770923300,61,'fluxbb-member',0,0,0,0,NULL,2),
(11,'fluxbb-member','Test Topic 6 - Forum navigation testing',1770923360,62,1770923360,62,'fluxbb-member',0,0,0,0,NULL,2),
(12,'fluxbb-member','Test Topic 7 - Forum navigation testing',1770923420,63,1770923420,63,'fluxbb-member',0,0,0,0,NULL,2),
(13,'fluxbb-member','Test Topic 8 - Forum navigation testing',1770923480,64,1770923480,64,'fluxbb-member',0,0,0,0,NULL,2),
(14,'fluxbb-member','Test Topic 9 - Forum navigation testing',1770923540,65,1770923540,65,'fluxbb-member',0,0,0,0,NULL,2),
(15,'fluxbb-member','Test Topic 10 - Forum navigation testing',1770923600,66,1770923600,66,'fluxbb-member',0,0,0,0,NULL,2),
(16,'fluxbb-member','Test Topic 11 - Forum navigation testing',1770923660,67,1770923660,67,'fluxbb-member',0,0,0,0,NULL,2),
(17,'fluxbb-member','Test Topic 12 - Forum navigation testing',1770923720,68,1770923720,68,'fluxbb-member',0,0,0,0,NULL,2),
(18,'fluxbb-member','Test Topic 13 - Forum navigation testing',1770923780,69,1770923780,69,'fluxbb-member',0,0,0,0,NULL,2),
(19,'fluxbb-member','Test Topic 14 - Forum navigation testing',1770923840,70,1770923840,70,'fluxbb-member',0,0,0,0,NULL,2),
(20,'fluxbb-member','Test Topic 15 - Forum navigation testing',1770923900,71,1770923900,71,'fluxbb-member',0,0,0,0,NULL,2),
(21,'fluxbb-member','Test Topic 16 - Forum navigation testing',1770923960,72,1770923960,72,'fluxbb-member',0,0,0,0,NULL,2),
(22,'fluxbb-member','Test Topic 17 - Forum navigation testing',1770924020,73,1770924020,73,'fluxbb-member',0,0,0,0,NULL,2),
(23,'fluxbb-member','Test Topic 18 - Forum navigation testing',1770924080,74,1770924080,74,'fluxbb-member',0,0,0,0,NULL,2),
(24,'fluxbb-member','Test Topic 19 - Forum navigation testing',1770924140,75,1770924140,75,'fluxbb-member',0,0,0,0,NULL,2),
(25,'fluxbb-member','Test Topic 20 - Forum navigation testing',1770924200,76,1770924200,76,'fluxbb-member',0,0,0,0,NULL,2),
(26,'fluxbb-member','Test Topic 21 - Forum navigation testing',1770924260,77,1770924260,77,'fluxbb-member',0,0,0,0,NULL,2),
(27,'fluxbb-member','Test Topic 22 - Forum navigation testing',1770924320,78,1770924320,78,'fluxbb-member',0,0,0,0,NULL,2),
(28,'fluxbb-member','Test Topic 23 - Forum navigation testing',1770924380,79,1770924380,79,'fluxbb-member',0,0,0,0,NULL,2),
(29,'fluxbb-member','Test Topic 24 - Forum navigation testing',1770924440,80,1770924440,80,'fluxbb-member',0,0,0,0,NULL,2),
(30,'fluxbb-member','Test Topic 25 - Forum navigation testing',1770924500,81,1770924500,81,'fluxbb-member',0,0,0,0,NULL,2),
(31,'fluxbb-member','Test Topic 26 - Forum navigation testing',1770924560,82,1770924560,82,'fluxbb-member',0,0,0,0,NULL,2),
(32,'fluxbb-member','Test Topic 27 - Forum navigation testing',1770924620,83,1770924620,83,'fluxbb-member',0,0,0,0,NULL,2),
(33,'fluxbb-member','Test Topic 28 - Forum navigation testing',1770924680,84,1770924680,84,'fluxbb-member',0,0,0,0,NULL,2),
(34,'fluxbb-member','Test Topic 29 - Forum navigation testing',1770924740,85,1770924740,85,'fluxbb-member',0,0,0,0,NULL,2),
(35,'fluxbb-member','Test Topic 30 - Forum navigation testing',1770924800,86,1770924800,86,'fluxbb-member',0,0,0,0,NULL,2),
(36,'fluxbb-member','Test Topic 31 - Forum navigation testing',1770924860,87,1770924860,87,'fluxbb-member',0,0,0,0,NULL,2),
(37,'fluxbb-member','Test Topic 32 - Forum navigation testing',1770924920,88,1770924920,88,'fluxbb-member',0,0,0,0,NULL,2),
(38,'fluxbb-member','Test Topic 33 - Forum navigation testing',1770924980,89,1770924980,89,'fluxbb-member',0,0,0,0,NULL,2),
(39,'fluxbb-member','Test Topic 34 - Forum navigation testing',1770925040,90,1770925040,90,'fluxbb-member',0,0,0,0,NULL,2),
(40,'fluxbb-member','Test Topic 35 - Forum navigation testing',1770925100,91,1770925100,91,'fluxbb-member',0,0,0,0,NULL,2),
(41,'fluxbb-member','Test Topic 36 - Forum navigation testing',1770925160,92,1770925160,92,'fluxbb-member',0,0,0,0,NULL,2),
(42,'fluxbb-member','Test Topic 37 - Forum navigation testing',1770925220,93,1770925220,93,'fluxbb-member',0,0,0,0,NULL,2),
(43,'fluxbb-member','Test Topic 38 - Forum navigation testing',1770925280,94,1770925280,94,'fluxbb-member',0,0,0,0,NULL,2),
(44,'fluxbb-member','Test Topic 39 - Forum navigation testing',1770925340,95,1770925340,95,'fluxbb-member',0,0,0,0,NULL,2),
(45,'fluxbb-member','Test Topic 40 - Forum navigation testing',1770925400,96,1770925400,96,'fluxbb-member',0,0,0,0,NULL,2),
(46,'fluxbb-member','Test Topic 41 - Forum navigation testing',1770925460,97,1770925460,97,'fluxbb-member',0,0,0,0,NULL,2),
(47,'fluxbb-member','Test Topic 42 - Forum navigation testing',1770925520,98,1770925520,98,'fluxbb-member',0,0,0,0,NULL,2),
(48,'fluxbb-member','Test Topic 43 - Forum navigation testing',1770925580,99,1770925580,99,'fluxbb-member',0,0,0,0,NULL,2),
(49,'fluxbb-member','Test Topic 44 - Forum navigation testing',1770925640,100,1770925640,100,'fluxbb-member',0,0,0,0,NULL,2),
(50,'fluxbb-member','Test Topic 45 - Forum navigation testing',1770925700,101,1770925700,101,'fluxbb-member',0,0,0,0,NULL,2),
(51,'fluxbb-member','Test Topic 46 - Forum navigation testing',1770925760,102,1770925760,102,'fluxbb-member',0,0,0,0,NULL,2),
(52,'fluxbb-member','Test Topic 47 - Forum navigation testing',1770925820,103,1770925820,103,'fluxbb-member',0,0,0,0,NULL,2),
(53,'fluxbb-member','Test Topic 48 - Forum navigation testing',1770925880,104,1770925880,104,'fluxbb-member',0,0,0,0,NULL,2),
(54,'fluxbb-member','Test Topic 49 - Forum navigation testing',1770925940,105,1770925940,105,'fluxbb-member',0,0,0,0,NULL,2),
(55,'fluxbb-member','Test Topic 50 - Forum navigation testing',1770926000,106,1770926000,106,'fluxbb-member',0,0,0,0,NULL,2);
/*!40000 ALTER TABLE `fluxbb_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fluxbb_users`
--

DROP TABLE IF EXISTS `fluxbb_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fluxbb_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL DEFAULT 3,
  `username` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `title` varchar(50) DEFAULT NULL,
  `realname` varchar(40) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `jabber` varchar(80) DEFAULT NULL,
  `icq` varchar(12) DEFAULT NULL,
  `msn` varchar(80) DEFAULT NULL,
  `yahoo` varchar(30) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `disp_topics` tinyint(3) unsigned DEFAULT NULL,
  `disp_posts` tinyint(3) unsigned DEFAULT NULL,
  `email_setting` tinyint(1) NOT NULL DEFAULT 1,
  `notify_with_post` tinyint(1) NOT NULL DEFAULT 0,
  `auto_notify` tinyint(1) NOT NULL DEFAULT 0,
  `show_smilies` tinyint(1) NOT NULL DEFAULT 1,
  `show_img` tinyint(1) NOT NULL DEFAULT 1,
  `show_img_sig` tinyint(1) NOT NULL DEFAULT 1,
  `show_avatars` tinyint(1) NOT NULL DEFAULT 1,
  `show_sig` tinyint(1) NOT NULL DEFAULT 1,
  `timezone` float NOT NULL DEFAULT 0,
  `dst` tinyint(1) NOT NULL DEFAULT 0,
  `time_format` tinyint(1) NOT NULL DEFAULT 0,
  `date_format` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(25) NOT NULL DEFAULT 'English',
  `style` varchar(25) NOT NULL DEFAULT 'Air',
  `num_posts` int(10) unsigned NOT NULL DEFAULT 0,
  `last_post` int(10) unsigned DEFAULT NULL,
  `last_search` int(10) unsigned DEFAULT NULL,
  `last_email_sent` int(10) unsigned DEFAULT NULL,
  `last_report_sent` int(10) unsigned DEFAULT NULL,
  `registered` int(10) unsigned NOT NULL DEFAULT 0,
  `registration_ip` varchar(39) NOT NULL DEFAULT '0.0.0.0',
  `last_visit` int(10) unsigned NOT NULL DEFAULT 0,
  `admin_note` varchar(30) DEFAULT NULL,
  `activate_string` varchar(80) DEFAULT NULL,
  `activate_key` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fluxbb_users_username_idx` (`username`(25)),
  KEY `fluxbb_users_registered_idx` (`registered`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fluxbb_users`
--

LOCK TABLES `fluxbb_users` WRITE;
/*!40000 ALTER TABLE `fluxbb_users` DISABLE KEYS */;
INSERT INTO `fluxbb_users` VALUES
(1,3,'Guest','Guest','Guest',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,0,0,1,1,1,1,1,0,0,0,0,'English','Air',0,NULL,NULL,NULL,NULL,0,'0.0.0.0',0,NULL,NULL,NULL),
(2,1,'fluxbb-archiver','$2y$10$JFm4flGFssfBR9hdT3L.tO5yH5dqED/vmefjZdpTeKZJ.eIFZlREy','fluxbb-archiver@this-is-invalid.email','Master Archiver','FluxBB Archiver','https://github.com/jtervone/fluxbb-archiver','fluxbb-archiver-jabber','123123123','fluxbb-archiver-microsoft-account','fluxbb-archiver-yahoo-messenge','Github','I\'m FluxBB Archiver - [url]https://github.com/jtervone/fluxbb-archiver[/url] - :)',NULL,NULL,1,0,0,1,1,1,1,1,0,0,0,0,'English','Air',2,1770921368,NULL,NULL,NULL,1770911360,'172.20.0.1',1770912700,NULL,NULL,NULL),
(3,4,'fluxbb-member','$2y$10$D2FmvI07x5yI.2c3Cf3x0Oa9Bt.xmQL77/CFvC8hfKHSU7DR2ExAW','fluxbb-member@not-valid.email',NULL,'FluxBB Member','https://github.com/jtervone/fluxbb-archiver',NULL,NULL,NULL,NULL,'Github','-- \nFluxBB Member',NULL,NULL,0,0,0,1,1,1,1,1,0,0,0,0,'English','Air',6,1770920810,NULL,NULL,NULL,1770920249,'172.20.0.1',1770921229,NULL,NULL,NULL);
/*!40000 ALTER TABLE `fluxbb_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-12 19:38:33
