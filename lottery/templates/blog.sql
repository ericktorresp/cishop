/*
Navicat MySQL Data Transfer

Source Server         : localhost:3306
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-03-11 18:08:55
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_group
-- ----------------------------

-- ----------------------------
-- Table structure for `auth_group_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `auth_group_permissions`;
CREATE TABLE `auth_group_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`,`permission_id`),
  KEY `permission_id_refs_id_5886d21f` (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_group_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for `auth_message`
-- ----------------------------
DROP TABLE IF EXISTS `auth_message`;
CREATE TABLE `auth_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_message_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_message
-- ----------------------------

-- ----------------------------
-- Table structure for `auth_permission`
-- ----------------------------
DROP TABLE IF EXISTS `auth_permission`;
CREATE TABLE `auth_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_type_id` (`content_type_id`,`codename`),
  KEY `auth_permission_content_type_id` (`content_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_permission
-- ----------------------------
INSERT INTO `auth_permission` VALUES ('1', 'Can add permission', '1', 'add_permission');
INSERT INTO `auth_permission` VALUES ('2', 'Can change permission', '1', 'change_permission');
INSERT INTO `auth_permission` VALUES ('3', 'Can delete permission', '1', 'delete_permission');
INSERT INTO `auth_permission` VALUES ('4', 'Can add group', '2', 'add_group');
INSERT INTO `auth_permission` VALUES ('5', 'Can change group', '2', 'change_group');
INSERT INTO `auth_permission` VALUES ('6', 'Can delete group', '2', 'delete_group');
INSERT INTO `auth_permission` VALUES ('7', 'Can add user', '3', 'add_user');
INSERT INTO `auth_permission` VALUES ('8', 'Can change user', '3', 'change_user');
INSERT INTO `auth_permission` VALUES ('9', 'Can delete user', '3', 'delete_user');
INSERT INTO `auth_permission` VALUES ('10', 'Can add message', '4', 'add_message');
INSERT INTO `auth_permission` VALUES ('11', 'Can change message', '4', 'change_message');
INSERT INTO `auth_permission` VALUES ('12', 'Can delete message', '4', 'delete_message');
INSERT INTO `auth_permission` VALUES ('13', 'Can add content type', '5', 'add_contenttype');
INSERT INTO `auth_permission` VALUES ('14', 'Can change content type', '5', 'change_contenttype');
INSERT INTO `auth_permission` VALUES ('15', 'Can delete content type', '5', 'delete_contenttype');
INSERT INTO `auth_permission` VALUES ('16', 'Can add session', '6', 'add_session');
INSERT INTO `auth_permission` VALUES ('17', 'Can change session', '6', 'change_session');
INSERT INTO `auth_permission` VALUES ('18', 'Can delete session', '6', 'delete_session');
INSERT INTO `auth_permission` VALUES ('19', 'Can add site', '7', 'add_site');
INSERT INTO `auth_permission` VALUES ('20', 'Can change site', '7', 'change_site');
INSERT INTO `auth_permission` VALUES ('21', 'Can delete site', '7', 'delete_site');
INSERT INTO `auth_permission` VALUES ('22', 'Can add poll', '8', 'add_poll');
INSERT INTO `auth_permission` VALUES ('23', 'Can change poll', '8', 'change_poll');
INSERT INTO `auth_permission` VALUES ('24', 'Can delete poll', '8', 'delete_poll');
INSERT INTO `auth_permission` VALUES ('25', 'Can add choice', '9', 'add_choice');
INSERT INTO `auth_permission` VALUES ('26', 'Can change choice', '9', 'change_choice');
INSERT INTO `auth_permission` VALUES ('27', 'Can delete choice', '9', 'delete_choice');
INSERT INTO `auth_permission` VALUES ('28', 'Can add channel', '10', 'add_channel');
INSERT INTO `auth_permission` VALUES ('29', 'Can change channel', '10', 'change_channel');
INSERT INTO `auth_permission` VALUES ('30', 'Can delete channel', '10', 'delete_channel');
INSERT INTO `auth_permission` VALUES ('31', 'Can add log entry', '11', 'add_logentry');
INSERT INTO `auth_permission` VALUES ('32', 'Can change log entry', '11', 'change_logentry');
INSERT INTO `auth_permission` VALUES ('33', 'Can delete log entry', '11', 'delete_logentry');
INSERT INTO `auth_permission` VALUES ('34', 'Can add lottery', '12', 'add_lottery');
INSERT INTO `auth_permission` VALUES ('35', 'Can change lottery', '12', 'change_lottery');
INSERT INTO `auth_permission` VALUES ('36', 'Can delete lottery', '12', 'delete_lottery');
INSERT INTO `auth_permission` VALUES ('37', 'Can add lottery type', '13', 'add_lotterytype');
INSERT INTO `auth_permission` VALUES ('38', 'Can change lottery type', '13', 'change_lotterytype');
INSERT INTO `auth_permission` VALUES ('39', 'Can delete lottery type', '13', 'delete_lotterytype');

-- ----------------------------
-- Table structure for `auth_user`
-- ----------------------------
DROP TABLE IF EXISTS `auth_user`;
CREATE TABLE `auth_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(75) NOT NULL,
  `password` varchar(128) NOT NULL,
  `is_staff` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `last_login` datetime NOT NULL,
  `date_joined` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'kirinse@gmail.com', 'sha1$bafc3$0355df6396ca2091f5d5e7dd0640b8f8fc23a7c1', '1', '1', '1', '2010-03-11 14:14:59', '2010-03-11 13:46:41');

-- ----------------------------
-- Table structure for `auth_user_groups`
-- ----------------------------
DROP TABLE IF EXISTS `auth_user_groups`;
CREATE TABLE `auth_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`),
  KEY `group_id_refs_id_f116770` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user_groups
-- ----------------------------

-- ----------------------------
-- Table structure for `auth_user_user_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `auth_user_user_permissions`;
CREATE TABLE `auth_user_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`permission_id`),
  KEY `permission_id_refs_id_67e79cb` (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user_user_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for `channels_channel`
-- ----------------------------
DROP TABLE IF EXISTS `channels_channel`;
CREATE TABLE `channels_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `path` varchar(200) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_channel
-- ----------------------------
INSERT INTO `channels_channel` VALUES ('1', '低频游戏', '/low/', '0', '2010-03-11 13:47:16', '2010-03-11 13:47:17');
INSERT INTO `channels_channel` VALUES ('2', '高频游戏', '/high/', '0', '2010-03-11 13:47:38', '2010-03-11 13:47:38');
INSERT INTO `channels_channel` VALUES ('3', '银行大厅', '/', '0', '2010-03-11 13:47:51', '2010-03-11 13:47:53');

-- ----------------------------
-- Table structure for `django_admin_log`
-- ----------------------------
DROP TABLE IF EXISTS `django_admin_log`;
CREATE TABLE `django_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_time` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_type_id` int(11) DEFAULT NULL,
  `object_id` longtext,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint(5) unsigned NOT NULL,
  `change_message` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `django_admin_log_user_id` (`user_id`),
  KEY `django_admin_log_content_type_id` (`content_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_admin_log
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-03-11 13:47:19', '1', '11', '1', '低频游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('2', '2010-03-11 13:47:39', '1', '11', '2', '高频游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('3', '2010-03-11 13:47:53', '1', '11', '3', '银行大厅', '1', '');
INSERT INTO `django_admin_log` VALUES ('4', '2010-03-11 13:48:22', '1', '8', '1', '中文应该没问题了', '1', '');
INSERT INTO `django_admin_log` VALUES ('5', '2010-03-11 15:49:52', '1', '12', '1', '重庆实时彩', '1', '');
INSERT INTO `django_admin_log` VALUES ('6', '2010-03-11 15:52:21', '1', '12', '2', '山东11运', '1', '');
INSERT INTO `django_admin_log` VALUES ('7', '2010-03-11 16:05:01', '1', '13', '1', '数字类型', '1', '');
INSERT INTO `django_admin_log` VALUES ('8', '2010-03-11 16:05:12', '1', '13', '2', '乐透分区型(蓝红球)', '1', '');
INSERT INTO `django_admin_log` VALUES ('9', '2010-03-11 16:05:19', '1', '13', '3', '乐透同区型', '1', '');
INSERT INTO `django_admin_log` VALUES ('10', '2010-03-11 16:05:24', '1', '13', '4', '基诺型', '1', '');
INSERT INTO `django_admin_log` VALUES ('11', '2010-03-11 16:05:29', '1', '13', '5', '排列型', '1', '');
INSERT INTO `django_admin_log` VALUES ('12', '2010-03-11 16:05:35', '1', '13', '6', '分组型', '1', '');
INSERT INTO `django_admin_log` VALUES ('13', '2010-03-11 16:12:50', '1', '12', '1', '重庆实时彩', '1', '');
INSERT INTO `django_admin_log` VALUES ('14', '2010-03-11 16:14:18', '1', '12', '2', '山东11运', '1', '');

-- ----------------------------
-- Table structure for `django_content_type`
-- ----------------------------
DROP TABLE IF EXISTS `django_content_type`;
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_label` (`app_label`,`model`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_content_type
-- ----------------------------
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission');
INSERT INTO `django_content_type` VALUES ('2', 'group', 'auth', 'group');
INSERT INTO `django_content_type` VALUES ('3', 'user', 'auth', 'user');
INSERT INTO `django_content_type` VALUES ('4', 'message', 'auth', 'message');
INSERT INTO `django_content_type` VALUES ('5', 'content type', 'contenttypes', 'contenttype');
INSERT INTO `django_content_type` VALUES ('6', 'session', 'sessions', 'session');
INSERT INTO `django_content_type` VALUES ('7', 'site', 'sites', 'site');
INSERT INTO `django_content_type` VALUES ('8', 'poll', 'polls', 'poll');
INSERT INTO `django_content_type` VALUES ('9', 'choice', 'polls', 'choice');
INSERT INTO `django_content_type` VALUES ('10', 'channel', 'channels', 'channel');
INSERT INTO `django_content_type` VALUES ('11', 'log entry', 'admin', 'logentry');
INSERT INTO `django_content_type` VALUES ('12', 'lottery', 'lotteries', 'lottery');
INSERT INTO `django_content_type` VALUES ('13', 'lottery type', 'lotteries', 'lotterytype');

-- ----------------------------
-- Table structure for `django_session`
-- ----------------------------
DROP TABLE IF EXISTS `django_session`;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY (`session_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_session
-- ----------------------------
INSERT INTO `django_session` VALUES ('9095c93e954d4473753dab8bb431da3a', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS4zZDMyNGVhYWFkYTkwYWVhODNk\nZTI4NGY4MzIwNzgyZQ==\n', '2010-03-25 14:14:59');

-- ----------------------------
-- Table structure for `django_site`
-- ----------------------------
DROP TABLE IF EXISTS `django_site`;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_site
-- ----------------------------
INSERT INTO `django_site` VALUES ('1', 'example.com', 'example.com');

-- ----------------------------
-- Table structure for `lotteries_lottery`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_lottery`;
CREATE TABLE `lotteries_lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `code` varchar(200) NOT NULL,
  `sort` smallint(6) NOT NULL,
  `lotterytype_id` int(11) NOT NULL,
  `issue_set` longtext NOT NULL,
  `week_cycle` smallint(6) NOT NULL,
  `yearly_break_start` date NOT NULL,
  `yearly_break_end` date NOT NULL,
  `min_commission_gap` double NOT NULL,
  `min_profit` double NOT NULL,
  `issue_rule` varchar(200) NOT NULL,
  `description` longtext NOT NULL,
  `number_rule` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_lottery_lotterytype_id` (`lotterytype_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lottery
-- ----------------------------
INSERT INTO `lotteries_lottery` VALUES ('1', '重庆实时彩', 'CQSSC', '0', '1', 'a:3:{i:0;a:9:{s:9:\"starttime\";s:8:\"00:00:00\";s:12:\"firstendtime\";s:8:\"00:05:00\";s:7:\"endtime\";s:8:\"01:55:00\";s:5:\"cycle\";i:300;s:7:\"endsale\";i:50;s:13:\"inputcodetime\";i:30;s:8:\"droptime\";i:50;s:6:\"status\";i:1;s:4:\"sort\";i:0;}i:1;a:9:{s:9:\"starttime\";s:8:\"05:00:00\";s:12:\"firstendtime\";s:8:\"10:00:00\";s:7:\"endtime\";s:8:\"22:00:00\";s:5:\"cycle\";i:600;s:7:\"endsale\";i:90;s:13:\"inputcodetime\";i:30;s:8:\"droptime\";i:90;s:6:\"status\";i:1;s:4:\"sort\";i:1;}i:2;a:9:{s:9:\"starttime\";s:8:\"22:00:00\";s:12:\"firstendtime\";s:8:\"22:05:00\";s:7:\"endtime\";s:8:\"00:00:00\";s:5:\"cycle\";i:300;s:7:\"endsale\";i:50;s:13:\"inputcodetime\";i:30;s:8:\"droptime\";i:50;s:6:\"status\";i:1;s:4:\"sort\";i:2;}}', '127', '2010-02-13', '2010-02-19', '0.005', '0.04', '(y)(M)(D)(N3)|0,1,0', 'CQSSC', 'a:3:{s:3:\"len\";s:1:\"5\";s:7:\"startno\";s:1:\"0\";s:5:\"endno\";s:1:\"9\";}');
INSERT INTO `lotteries_lottery` VALUES ('2', '山东11运', 'SD11Y', '0', '3', 'a:1:{i:0;a:9:{s:9:\"starttime\";s:8:\"05:00:00\";s:12:\"firstendtime\";s:8:\"09:07:00\";s:7:\"endtime\";s:8:\"21:55:00\";s:5:\"cycle\";i:720;s:7:\"endsale\";i:240;s:13:\"inputcodetime\";i:120;s:8:\"droptime\";i:240;s:6:\"status\";i:1;s:4:\"sort\";i:0;}}', '127', '2010-02-23', '2010-02-28', '0.01', '0.04', '(y)(M)(D)[n2]|0,1,0', '山东11运', 'a:7:{s:3:\"len\";s:1:\"5\";s:7:\"startno\";s:2:\"01\";s:5:\"endno\";s:2:\"11\";s:11:\"startrepeat\";s:0:\"\";s:9:\"spstartno\";s:0:\"\";s:7:\"spendno\";s:0:\"\";s:8:\"sprepeat\";s:0:\"\";}');

-- ----------------------------
-- Table structure for `lotteries_lotterytype`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_lotterytype`;
CREATE TABLE `lotteries_lotterytype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lotterytype
-- ----------------------------
INSERT INTO `lotteries_lotterytype` VALUES ('1', '数字类型');
INSERT INTO `lotteries_lotterytype` VALUES ('2', '乐透分区型(蓝红球)');
INSERT INTO `lotteries_lotterytype` VALUES ('3', '乐透同区型');
INSERT INTO `lotteries_lotterytype` VALUES ('4', '基诺型');
INSERT INTO `lotteries_lotterytype` VALUES ('5', '排列型');
INSERT INTO `lotteries_lotterytype` VALUES ('6', '分组型');

-- ----------------------------
-- Table structure for `polls_choice`
-- ----------------------------
DROP TABLE IF EXISTS `polls_choice`;
CREATE TABLE `polls_choice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) NOT NULL,
  `choice` varchar(200) NOT NULL,
  `votes` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `polls_choice_poll_id` (`poll_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of polls_choice
-- ----------------------------
INSERT INTO `polls_choice` VALUES ('1', '1', '是', '5');
INSERT INTO `polls_choice` VALUES ('2', '1', '否', '1');
INSERT INTO `polls_choice` VALUES ('3', '1', '有些问题', '1');

-- ----------------------------
-- Table structure for `polls_poll`
-- ----------------------------
DROP TABLE IF EXISTS `polls_poll`;
CREATE TABLE `polls_poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(200) NOT NULL,
  `pub_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of polls_poll
-- ----------------------------
INSERT INTO `polls_poll` VALUES ('1', '中文应该没问题了', '2010-03-11 13:48:09');
