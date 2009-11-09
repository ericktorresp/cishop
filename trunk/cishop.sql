/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50139
 Source Host           : localhost
 Source Database       : cishop

 Target Server Type    : MySQL
 Target Server Version : 50139
 File Encoding         : utf-8

 Date: 11/09/2009 23:00:23 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `cs_acl_actions`
-- ----------------------------
DROP TABLE IF EXISTS `cs_acl_actions`;
CREATE TABLE `cs_acl_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(254) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `cs_acl_groups`
-- ----------------------------
DROP TABLE IF EXISTS `cs_acl_groups`;
CREATE TABLE `cs_acl_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lft` int(10) unsigned NOT NULL DEFAULT '0',
  `rgt` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(254) NOT NULL,
  `link` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_acl_groups`
-- ----------------------------
INSERT INTO `cs_acl_groups` VALUES ('1', '1', '14', 'Member', null), ('2', '2', '3', 'Administrator', null), ('5', '12', '13', 'Services', '0'), ('6', '10', '11', 'Finance', '0'), ('7', '8', '9', 'Depot', '0'), ('8', '6', '7', 'Develop', '0'), ('9', '4', '5', 'Operate', '0');

-- ----------------------------
--  Table structure for `cs_acl_permission_actions`
-- ----------------------------
DROP TABLE IF EXISTS `cs_acl_permission_actions`;
CREATE TABLE `cs_acl_permission_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `access_id` int(10) unsigned NOT NULL DEFAULT '0',
  `axo_id` int(10) unsigned NOT NULL DEFAULT '0',
  `allow` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_id` (`access_id`),
  KEY `axo_id` (`axo_id`),
  CONSTRAINT `cs_acl_permission_actions_ibfk_1` FOREIGN KEY (`access_id`) REFERENCES `cs_acl_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cs_acl_permission_actions_ibfk_2` FOREIGN KEY (`axo_id`) REFERENCES `cs_acl_actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `cs_acl_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `cs_acl_permissions`;
CREATE TABLE `cs_acl_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) unsigned NOT NULL DEFAULT '0',
  `aco_id` int(10) unsigned NOT NULL DEFAULT '0',
  `allow` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aro_id` (`aro_id`),
  KEY `aco_id` (`aco_id`),
  CONSTRAINT `cs_acl_permissions_ibfk_1` FOREIGN KEY (`aro_id`) REFERENCES `cs_acl_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cs_acl_permissions_ibfk_2` FOREIGN KEY (`aco_id`) REFERENCES `cs_acl_resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_acl_permissions`
-- ----------------------------
INSERT INTO `cs_acl_permissions` VALUES ('1', '2', '1', 'Y'), ('11', '9', '44', 'Y'), ('14', '9', '2', 'Y'), ('15', '9', '3', 'N');

-- ----------------------------
--  Table structure for `cs_acl_resources`
-- ----------------------------
DROP TABLE IF EXISTS `cs_acl_resources`;
CREATE TABLE `cs_acl_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lft` int(10) unsigned NOT NULL DEFAULT '0',
  `rgt` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(254) NOT NULL,
  `link` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_acl_resources`
-- ----------------------------
INSERT INTO `cs_acl_resources` VALUES ('1', '1', '26', 'Site', null), ('2', '2', '25', 'Control Panel', null), ('3', '7', '24', 'System', null), ('4', '18', '19', 'Members', null), ('5', '8', '17', 'Access Control', null), ('6', '20', '21', 'Settings', null), ('7', '22', '23', 'Utilities', null), ('8', '15', '16', 'Permissions', null), ('9', '13', '14', 'Groups', null), ('10', '11', '12', 'Resources', null), ('11', '9', '10', 'Actions', null), ('43', '3', '6', 'Product', '0'), ('44', '4', '5', 'Styles', '0');

-- ----------------------------
--  Table structure for `cs_groups`
-- ----------------------------
DROP TABLE IF EXISTS `cs_groups`;
CREATE TABLE `cs_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `cs_groups_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cs_acl_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_groups`
-- ----------------------------
INSERT INTO `cs_groups` VALUES ('1', '1', '0'), ('2', '1', '0'), ('5', '0', '0'), ('6', '0', '0'), ('7', '0', '0'), ('8', '0', '0'), ('9', '0', '0');

-- ----------------------------
--  Table structure for `cs_preferences`
-- ----------------------------
DROP TABLE IF EXISTS `cs_preferences`;
CREATE TABLE `cs_preferences` (
  `name` varchar(254) CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1 NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_preferences`
-- ----------------------------
INSERT INTO `cs_preferences` VALUES ('default_user_group', '1'), ('smtp_host', ''), ('keep_error_logs_for', '30'), ('email_protocol', 'sendmail'), ('use_registration_captcha', '0'), ('page_debug', '0'), ('automated_from_name', 'BackendPro'), ('allow_user_registration', '1'), ('use_login_captcha', '0'), ('site_name', 'cishop'), ('automated_from_email', 'noreply@backendpro.co.uk'), ('account_activation_time', '7'), ('allow_user_profiles', '1'), ('activation_method', 'email'), ('autologin_period', '30'), ('min_password_length', '6'), ('smtp_user', ''), ('smtp_pass', ''), ('email_mailpath', '/usr/sbin/sendmail'), ('smtp_port', '25'), ('smtp_timeout', '5'), ('email_wordwrap', '1'), ('email_wrapchars', '76'), ('email_mailtype', 'text'), ('email_charset', 'utf-8'), ('bcc_batch_mode', '0'), ('bcc_batch_size', '200'), ('login_field', 'email');

-- ----------------------------
--  Table structure for `cs_resources`
-- ----------------------------
DROP TABLE IF EXISTS `cs_resources`;
CREATE TABLE `cs_resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `cs_resources_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cs_acl_resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_resources`
-- ----------------------------
INSERT INTO `cs_resources` VALUES ('1', '1'), ('2', '1'), ('3', '1'), ('4', '1'), ('5', '1'), ('6', '1'), ('7', '1'), ('8', '1'), ('9', '1'), ('10', '1'), ('11', '1'), ('43', '0'), ('44', '0');

-- ----------------------------
--  Table structure for `cs_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `cs_sessions`;
CREATE TABLE `cs_sessions` (
  `session_id` varchar(40) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `ip_address` varchar(16) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `user_agent` varchar(50) CHARACTER SET latin1 NOT NULL,
  `user_data` text NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_sessions`
-- ----------------------------
INSERT INTO `cs_sessions` VALUES ('649b000fcc2a626378c91b3a240be3aa', '0.0.0.0', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; zh', '', '1257778308');

-- ----------------------------
--  Table structure for `cs_style`
-- ----------------------------
DROP TABLE IF EXISTS `cs_style`;
CREATE TABLE `cs_style` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(21) NOT NULL,
  `name` varchar(121) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `cs_user_profiles`
-- ----------------------------
DROP TABLE IF EXISTS `cs_user_profiles`;
CREATE TABLE `cs_user_profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `gender` char(1) DEFAULT 'm',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `cs_user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cs_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_user_profiles`
-- ----------------------------
INSERT INTO `cs_user_profiles` VALUES ('1', 'm'), ('2', 'm');

-- ----------------------------
--  Table structure for `cs_users`
-- ----------------------------
DROP TABLE IF EXISTS `cs_users`;
CREATE TABLE `cs_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(254) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `group` int(10) unsigned DEFAULT NULL,
  `activation_key` varchar(32) DEFAULT NULL,
  `last_visit` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `password` (`password`),
  KEY `group` (`group`),
  CONSTRAINT `cs_users_ibfk_1` FOREIGN KEY (`group`) REFERENCES `cs_acl_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `cs_users`
-- ----------------------------
INSERT INTO `cs_users` VALUES ('1', 'root', '0ccaccc35c30d4a60e0c8738a9123534c8b37865', 'c-mtv@163.com', '1', '2', null, '2009-11-09 21:09:47', '2009-11-03 10:31:35', '2009-11-03 11:04:15'), ('2', '运营', 'd741f0a8d0867b9f0f18a86a0f211a4bde38a8c5', 'floyd@f-club.cn', '1', '9', null, '2009-11-08 02:42:27', '2009-11-07 22:10:48', null);

