/*
Navicat MySQL Data Transfer

Source Server         : localhost:3306
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-03-15 17:41:17
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `permission_id_refs_id_5886d21f` (`permission_id`),
  CONSTRAINT `permission_id_refs_id_5886d21f` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `auth_message_user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_650f49a6` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

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
  KEY `auth_permission_content_type_id` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_728de91f` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;

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
INSERT INTO `auth_permission` VALUES ('31', 'Can add configure', '11', 'add_configure');
INSERT INTO `auth_permission` VALUES ('32', 'Can change configure', '11', 'change_configure');
INSERT INTO `auth_permission` VALUES ('33', 'Can delete configure', '11', 'delete_configure');
INSERT INTO `auth_permission` VALUES ('34', 'Can add lottery type', '12', 'add_lotterytype');
INSERT INTO `auth_permission` VALUES ('35', 'Can change lottery type', '12', 'change_lotterytype');
INSERT INTO `auth_permission` VALUES ('36', 'Can delete lottery type', '12', 'delete_lotterytype');
INSERT INTO `auth_permission` VALUES ('37', 'Can add lottery', '13', 'add_lottery');
INSERT INTO `auth_permission` VALUES ('38', 'Can change lottery', '13', 'change_lottery');
INSERT INTO `auth_permission` VALUES ('39', 'Can delete lottery', '13', 'delete_lottery');
INSERT INTO `auth_permission` VALUES ('40', 'Can add method', '14', 'add_method');
INSERT INTO `auth_permission` VALUES ('41', 'Can change method', '14', 'change_method');
INSERT INTO `auth_permission` VALUES ('42', 'Can delete method', '14', 'delete_method');
INSERT INTO `auth_permission` VALUES ('43', 'Can add issue', '15', 'add_issue');
INSERT INTO `auth_permission` VALUES ('44', 'Can change issue', '15', 'change_issue');
INSERT INTO `auth_permission` VALUES ('45', 'Can delete issue', '15', 'delete_issue');
INSERT INTO `auth_permission` VALUES ('46', 'Can add log entry', '16', 'add_logentry');
INSERT INTO `auth_permission` VALUES ('47', 'Can change log entry', '16', 'change_logentry');
INSERT INTO `auth_permission` VALUES ('48', 'Can delete log entry', '16', 'delete_logentry');
INSERT INTO `auth_permission` VALUES ('49', 'Can add help', '17', 'add_help');
INSERT INTO `auth_permission` VALUES ('50', 'Can change help', '17', 'change_help');
INSERT INTO `auth_permission` VALUES ('51', 'Can delete help', '17', 'delete_help');
INSERT INTO `auth_permission` VALUES ('52', 'Can add notice', '18', 'add_notice');
INSERT INTO `auth_permission` VALUES ('53', 'Can change notice', '18', 'change_notice');
INSERT INTO `auth_permission` VALUES ('54', 'Can delete notice', '18', 'delete_notice');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'kirinse@gmail.com', 'sha1$2310a$e2046e61f186f06c33756ff714e61eb9d284c720', '1', '1', '1', '2010-03-15 13:34:03', '2010-03-15 13:33:01');
INSERT INTO `auth_user` VALUES ('2', 'darkmoon', '', '', '', 'sha1$1a5c6$878593b1876689b195d25f3ee08ac72808ab1fbd', '0', '1', '0', '2010-03-15 17:06:45', '2010-03-15 17:06:45');

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
  KEY `group_id_refs_id_f116770` (`group_id`),
  CONSTRAINT `group_id_refs_id_f116770` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `user_id_refs_id_7ceef80f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `permission_id_refs_id_67e79cb` (`permission_id`),
  CONSTRAINT `permission_id_refs_id_67e79cb` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `user_id_refs_id_dfbab7d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_channel
-- ----------------------------
INSERT INTO `channels_channel` VALUES ('1', '银行大厅', '/', '0', '2010-03-15 13:40:55', '2010-03-15 13:40:55');
INSERT INTO `channels_channel` VALUES ('2', '低频游戏', '/low', '0', '2010-03-15 13:41:03', '2010-03-15 13:41:03');
INSERT INTO `channels_channel` VALUES ('3', '高频游戏', '/high', '0', '2010-03-15 13:41:17', '2010-03-15 13:41:17');

-- ----------------------------
-- Table structure for `channels_channel_usersets`
-- ----------------------------
DROP TABLE IF EXISTS `channels_channel_usersets`;
CREATE TABLE `channels_channel_usersets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_id` (`channel_id`,`user_id`),
  KEY `user_id_refs_id_3bcda124` (`user_id`),
  CONSTRAINT `user_id_refs_id_3bcda124` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_7098a2fb` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_channel_usersets
-- ----------------------------

-- ----------------------------
-- Table structure for `channels_configure`
-- ----------------------------
DROP TABLE IF EXISTS `channels_configure`;
CREATE TABLE `channels_configure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `config_key` varchar(30) NOT NULL,
  `config_value` varchar(255) NOT NULL,
  `default_value` varchar(255) NOT NULL,
  `config_value_type` varchar(10) NOT NULL,
  `form_input_type` varchar(10) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channels_configure_parent_id` (`parent_id`),
  KEY `channels_configure_channel_id` (`channel_id`),
  CONSTRAINT `channel_id_refs_id_24dbfbd4` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `parent_id_refs_id_43dec625` FOREIGN KEY (`parent_id`) REFERENCES `channels_configure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_configure
-- ----------------------------

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
  KEY `django_admin_log_content_type_id` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_288599e6` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `user_id_refs_id_c8665aa` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_admin_log
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-03-15 13:40:55', '1', '10', '1', '银行大厅', '1', '');
INSERT INTO `django_admin_log` VALUES ('2', '2010-03-15 13:41:03', '1', '10', '2', '低频游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('3', '2010-03-15 13:41:17', '1', '10', '3', '高频游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('4', '2010-03-15 13:46:12', '1', '12', '1', '数字类型', '1', '');
INSERT INTO `django_admin_log` VALUES ('5', '2010-03-15 13:47:39', '1', '12', '2', '乐透分区型(蓝红球)', '1', '');
INSERT INTO `django_admin_log` VALUES ('6', '2010-03-15 13:47:50', '1', '12', '3', '乐透同区型', '1', '');
INSERT INTO `django_admin_log` VALUES ('7', '2010-03-15 13:48:02', '1', '12', '4', '基诺型', '1', '');
INSERT INTO `django_admin_log` VALUES ('8', '2010-03-15 13:48:11', '1', '12', '5', '排列型', '1', '');
INSERT INTO `django_admin_log` VALUES ('9', '2010-03-15 13:48:19', '1', '12', '6', '分组型', '1', '');
INSERT INTO `django_admin_log` VALUES ('10', '2010-03-15 16:42:56', '1', '17', '1', '低频游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('11', '2010-03-15 16:43:06', '1', '17', '1', 'sadadsad', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('12', '2010-03-15 16:47:57', '1', '17', '2', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('13', '2010-03-15 16:48:05', '1', '17', '2', '[转]上海世博至少落后广州亚运一百年！', '3', '');
INSERT INTO `django_admin_log` VALUES ('14', '2010-03-15 16:48:05', '1', '17', '1', 'sadadsad', '3', '');
INSERT INTO `django_admin_log` VALUES ('15', '2010-03-15 16:49:54', '1', '17', '3', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('16', '2010-03-15 16:50:50', '1', '17', '4', '王建硕: 关于两个机房的讨论', '1', '');
INSERT INTO `django_admin_log` VALUES ('17', '2010-03-15 16:53:26', '1', '17', '4', '王建硕: 关于两个机房的讨论', '2', '已修改 content 。');
INSERT INTO `django_admin_log` VALUES ('18', '2010-03-15 16:53:43', '1', '17', '4', '王建硕: 关于两个机房的讨论', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('19', '2010-03-15 17:06:46', '1', '3', '2', 'darkmoon', '1', '');
INSERT INTO `django_admin_log` VALUES ('20', '2010-03-15 17:08:02', '1', '17', '4', '王建硕: 关于两个机房的讨论', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('21', '2010-03-15 17:30:08', '1', '18', '1', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('22', '2010-03-15 17:30:38', '1', '18', '1', '[转]上海世博至少落后广州亚运一百年！', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('23', '2010-03-15 17:31:00', '1', '18', '2', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('24', '2010-03-15 17:31:42', '1', '18', '1', '[转]上海世博至少落后广州亚运一百年！', '3', '');
INSERT INTO `django_admin_log` VALUES ('25', '2010-03-15 17:31:55', '1', '18', '2', '[转]上海世博至少落后广州亚运一百年！', '2', '已修改 is_top 。');
INSERT INTO `django_admin_log` VALUES ('26', '2010-03-15 17:31:58', '1', '18', '2', '[转]上海世博至少落后广州亚运一百年！', '2', '已修改 is_top 。');
INSERT INTO `django_admin_log` VALUES ('27', '2010-03-15 17:32:01', '1', '18', '2', '[转]上海世博至少落后广州亚运一百年！', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('28', '2010-03-15 17:32:11', '1', '18', '3', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('29', '2010-03-15 17:35:24', '1', '18', '4', '王建硕: 关于两个机房的讨论', '1', '');
INSERT INTO `django_admin_log` VALUES ('30', '2010-03-15 17:37:47', '1', '18', '4', '王建硕: 关于两个机房的讨论', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('31', '2010-03-15 17:38:06', '1', '18', '3', '[转]上海世博至少落后广州亚运一百年！', '2', '没有字段被修改。');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_content_type` VALUES ('11', 'configure', 'channels', 'configure');
INSERT INTO `django_content_type` VALUES ('12', 'lottery type', 'lotteries', 'lotterytype');
INSERT INTO `django_content_type` VALUES ('13', 'lottery', 'lotteries', 'lottery');
INSERT INTO `django_content_type` VALUES ('14', 'method', 'lotteries', 'method');
INSERT INTO `django_content_type` VALUES ('15', 'issue', 'lotteries', 'issue');
INSERT INTO `django_content_type` VALUES ('16', 'log entry', 'admin', 'logentry');
INSERT INTO `django_content_type` VALUES ('17', 'help', 'helps', 'help');
INSERT INTO `django_content_type` VALUES ('18', 'notice', 'notices', 'notice');

-- ----------------------------
-- Table structure for `django_session`
-- ----------------------------
DROP TABLE IF EXISTS `django_session`;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_session
-- ----------------------------
INSERT INTO `django_session` VALUES ('432a7818ec0fbdf374645dfb6a91c1de', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS4zZDMyNGVhYWFkYTkwYWVhODNk\nZTI4NGY4MzIwNzgyZQ==\n', '2010-03-29 13:34:03');

-- ----------------------------
-- Table structure for `django_site`
-- ----------------------------
DROP TABLE IF EXISTS `django_site`;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_site
-- ----------------------------
INSERT INTO `django_site` VALUES ('1', 'example.com', 'example.com');

-- ----------------------------
-- Table structure for `helps_help`
-- ----------------------------
DROP TABLE IF EXISTS `helps_help`;
CREATE TABLE `helps_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `subject` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `sort` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `helps_help_channel_id` (`channel_id`),
  KEY `helps_help_author_id` (`author_id`),
  CONSTRAINT `author_id_refs_id_5a79d9ac` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_4b899895` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of helps_help
-- ----------------------------
INSERT INTO `helps_help` VALUES ('3', '1', 'bank', '[转]上海世博至少落后广州亚运一百年！', 'if not self.id:', '1', '2010-03-15 16:49:54', '0', '0');
INSERT INTO `helps_help` VALUES ('4', '1', 'bank', '王建硕: 关于两个机房的讨论', '王建硕: 关于两个机房的讨论\r\n\r\n王建硕: 关于两个机房的讨论\r\n\r\n王建硕: 关于两个机房的讨论', '1', '2010-03-15 16:50:50', '0', '0');

-- ----------------------------
-- Table structure for `lotteries_issue`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_issue`;
CREATE TABLE `lotteries_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `code` varchar(30) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `sale_start` datetime NOT NULL,
  `sale_end` datetime NOT NULL,
  `cancel_deadline` datetime NOT NULL,
  `official_time` datetime NOT NULL,
  `write_time` datetime NOT NULL,
  `write_user_id_id` int(11) NOT NULL,
  `verify_time` datetime NOT NULL,
  `verify_user_id_id` int(11) NOT NULL,
  `status_code` smallint(6) NOT NULL,
  `status_deduct` smallint(6) NOT NULL,
  `status_point` smallint(6) NOT NULL,
  `status_check_prize` smallint(6) NOT NULL,
  `status_prize` smallint(6) NOT NULL,
  `status_task_to_project` smallint(6) NOT NULL,
  `status_is_synced` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_issue_lottery_id` (`lottery_id`),
  KEY `lotteries_issue_write_user_id_id` (`write_user_id_id`),
  KEY `lotteries_issue_verify_user_id_id` (`verify_user_id_id`),
  CONSTRAINT `lottery_id_refs_id_4437a32b` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `verify_user_id_id_refs_id_69c5cf90` FOREIGN KEY (`verify_user_id_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `write_user_id_id_refs_id_69c5cf90` FOREIGN KEY (`write_user_id_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_issue
-- ----------------------------

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
  `min_commission_gap` decimal(3,3) NOT NULL,
  `min_profit` decimal(3,3) NOT NULL,
  `issue_rule` varchar(200) NOT NULL,
  `description` longtext NOT NULL,
  `number_rule` longtext NOT NULL,
  `channel_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_lottery_lotterytype_id` (`lotterytype_id`),
  KEY `lotteries_lottery_channel_id` (`channel_id`),
  CONSTRAINT `channel_id_refs_id_34b3d5bf` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `lotterytype_id_refs_id_1c8112f1` FOREIGN KEY (`lotterytype_id`) REFERENCES `lotteries_lotterytype` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lottery
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_lotterytype`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_lotterytype`;
CREATE TABLE `lotteries_lotterytype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `channel_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_lotterytype_channel_id` (`channel_id`),
  CONSTRAINT `channel_id_refs_id_6998b67b` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lotterytype
-- ----------------------------
INSERT INTO `lotteries_lotterytype` VALUES ('1', '数字类型', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('2', '乐透分区型(蓝红球)', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('3', '乐透同区型', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('4', '基诺型', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('5', '排列型', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('6', '分组型', '3');

-- ----------------------------
-- Table structure for `lotteries_method`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_method`;
CREATE TABLE `lotteries_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `function_name` varchar(20) NOT NULL,
  `init_lock_func` varchar(100) NOT NULL,
  `level_count` smallint(6) NOT NULL,
  `no_count` longtext NOT NULL,
  `description` longtext NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  `is_use_lock` tinyint(1) NOT NULL,
  `lock_table_name` varchar(30) NOT NULL,
  `max_lost` decimal(14,2) NOT NULL,
  `total_price` decimal(8,4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_method_lottery_id` (`lottery_id`),
  CONSTRAINT `lottery_id_refs_id_2e416d46` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_method
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_method_usersets`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_method_usersets`;
CREATE TABLE `lotteries_method_usersets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_id` (`method_id`,`user_id`),
  KEY `user_id_refs_id_1067f581` (`user_id`),
  CONSTRAINT `user_id_refs_id_1067f581` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `method_id_refs_id_14e2be7` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_method_usersets
-- ----------------------------

-- ----------------------------
-- Table structure for `notices_notice`
-- ----------------------------
DROP TABLE IF EXISTS `notices_notice`;
CREATE TABLE `notices_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `content` longtext NOT NULL,
  `created` datetime NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `checker_id` int(11) DEFAULT NULL,
  `channel_id` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `is_top` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notices_notice_author_id` (`author_id`),
  KEY `notices_notice_channel_id` (`channel_id`),
  CONSTRAINT `author_id_refs_id_4123a64c` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_6b37f5f5` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of notices_notice
-- ----------------------------
INSERT INTO `notices_notice` VALUES ('2', '[转]上海世博至少落后广州亚运一百年！', '[转]上海世博至少落后广州亚运一百年！', '2010-03-15 17:31:00', '1', null, '1', '0', '1');
INSERT INTO `notices_notice` VALUES ('3', '[转]上海世博至少落后广州亚运一百年！', '[转]上海世博至少落后广州亚运一百年！\r\n\r\n[转]上海世博至少落后广州亚运一百年！', '2010-03-15 17:32:11', '1', '1', '1', '0', '0');
INSERT INTO `notices_notice` VALUES ('4', '王建硕: 关于两个机房的讨论', '王建硕: 关于两个机房的讨论\r\n\r\n王建硕: 关于两个机房的讨论', '2010-03-15 17:35:24', '1', '1', '1', '0', '1');

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
  KEY `polls_choice_poll_id` (`poll_id`),
  CONSTRAINT `poll_id_refs_id_5d896c23` FOREIGN KEY (`poll_id`) REFERENCES `polls_poll` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of polls_choice
-- ----------------------------

-- ----------------------------
-- Table structure for `polls_poll`
-- ----------------------------
DROP TABLE IF EXISTS `polls_poll`;
CREATE TABLE `polls_poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(200) NOT NULL,
  `pub_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of polls_poll
-- ----------------------------
