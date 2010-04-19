/*
Navicat MySQL Data Transfer

Source Server         : localhost:3306
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : crims

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-04-19 15:47:10
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
  KEY `permission_id_refs_id_a7792de1` (`permission_id`),
  CONSTRAINT `permission_id_refs_id_a7792de1` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
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
  CONSTRAINT `user_id_refs_id_9af0b65a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;

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
INSERT INTO `auth_permission` VALUES ('22', 'Can add log entry', '8', 'add_logentry');
INSERT INTO `auth_permission` VALUES ('23', 'Can change log entry', '8', 'change_logentry');
INSERT INTO `auth_permission` VALUES ('24', 'Can delete log entry', '8', 'delete_logentry');
INSERT INTO `auth_permission` VALUES ('25', 'Can add registration profile', '9', 'add_registrationprofile');
INSERT INTO `auth_permission` VALUES ('26', 'Can change registration profile', '9', 'change_registrationprofile');
INSERT INTO `auth_permission` VALUES ('27', 'Can delete registration profile', '9', 'delete_registrationprofile');
INSERT INTO `auth_permission` VALUES ('28', 'Can add Navigation', '10', 'add_navigation');
INSERT INTO `auth_permission` VALUES ('29', 'Can change Navigation', '10', 'change_navigation');
INSERT INTO `auth_permission` VALUES ('30', 'Can delete Navigation', '10', 'delete_navigation');
INSERT INTO `auth_permission` VALUES ('31', 'Can add Navigation Item', '11', 'add_navigationitem');
INSERT INTO `auth_permission` VALUES ('32', 'Can change Navigation Item', '11', 'change_navigationitem');
INSERT INTO `auth_permission` VALUES ('33', 'Can delete Navigation Item', '11', 'delete_navigationitem');
INSERT INTO `auth_permission` VALUES ('34', 'Can add Bookmark', '12', 'add_bookmark');
INSERT INTO `auth_permission` VALUES ('35', 'Can change Bookmark', '12', 'change_bookmark');
INSERT INTO `auth_permission` VALUES ('36', 'Can delete Bookmark', '12', 'delete_bookmark');
INSERT INTO `auth_permission` VALUES ('37', 'Can add Bookmark Item', '13', 'add_bookmarkitem');
INSERT INTO `auth_permission` VALUES ('38', 'Can change Bookmark Item', '13', 'change_bookmarkitem');
INSERT INTO `auth_permission` VALUES ('39', 'Can delete Bookmark Item', '13', 'delete_bookmarkitem');
INSERT INTO `auth_permission` VALUES ('40', 'Can add Help', '14', 'add_help');
INSERT INTO `auth_permission` VALUES ('41', 'Can change Help', '14', 'change_help');
INSERT INTO `auth_permission` VALUES ('42', 'Can delete Help', '14', 'delete_help');
INSERT INTO `auth_permission` VALUES ('43', 'Can add Help Entry', '15', 'add_helpitem');
INSERT INTO `auth_permission` VALUES ('44', 'Can change Help Entry', '15', 'change_helpitem');
INSERT INTO `auth_permission` VALUES ('45', 'Can delete Help Entry', '15', 'delete_helpitem');
INSERT INTO `auth_permission` VALUES ('46', 'Can add captcha store', '16', 'add_captchastore');
INSERT INTO `auth_permission` VALUES ('47', 'Can change captcha store', '16', 'change_captchastore');
INSERT INTO `auth_permission` VALUES ('48', 'Can delete captcha store', '16', 'delete_captchastore');
INSERT INTO `auth_permission` VALUES ('49', 'Can add avatar', '17', 'add_avatar');
INSERT INTO `auth_permission` VALUES ('50', 'Can change avatar', '17', 'change_avatar');
INSERT INTO `auth_permission` VALUES ('51', 'Can delete avatar', '17', 'delete_avatar');
INSERT INTO `auth_permission` VALUES ('52', 'Can add armor', '18', 'add_armor');
INSERT INTO `auth_permission` VALUES ('53', 'Can change armor', '18', 'change_armor');
INSERT INTO `auth_permission` VALUES ('54', 'Can delete armor', '18', 'delete_armor');
INSERT INTO `auth_permission` VALUES ('55', 'Can add character', '19', 'add_character');
INSERT INTO `auth_permission` VALUES ('56', 'Can change character', '19', 'change_character');
INSERT INTO `auth_permission` VALUES ('57', 'Can delete character', '19', 'delete_character');
INSERT INTO `auth_permission` VALUES ('58', 'Can add drug', '20', 'add_drug');
INSERT INTO `auth_permission` VALUES ('59', 'Can change drug', '20', 'change_drug');
INSERT INTO `auth_permission` VALUES ('60', 'Can delete drug', '20', 'delete_drug');
INSERT INTO `auth_permission` VALUES ('61', 'Can add building', '21', 'add_building');
INSERT INTO `auth_permission` VALUES ('62', 'Can change building', '21', 'change_building');
INSERT INTO `auth_permission` VALUES ('63', 'Can delete building', '21', 'delete_building');
INSERT INTO `auth_permission` VALUES ('64', 'Can add hooker', '22', 'add_hooker');
INSERT INTO `auth_permission` VALUES ('65', 'Can change hooker', '22', 'change_hooker');
INSERT INTO `auth_permission` VALUES ('66', 'Can delete hooker', '22', 'delete_hooker');
INSERT INTO `auth_permission` VALUES ('67', 'Can add user armor', '23', 'add_userarmor');
INSERT INTO `auth_permission` VALUES ('68', 'Can change user armor', '23', 'change_userarmor');
INSERT INTO `auth_permission` VALUES ('69', 'Can delete user armor', '23', 'delete_userarmor');
INSERT INTO `auth_permission` VALUES ('70', 'Can add user building', '24', 'add_userbuilding');
INSERT INTO `auth_permission` VALUES ('71', 'Can change user building', '24', 'change_userbuilding');
INSERT INTO `auth_permission` VALUES ('72', 'Can delete user building', '24', 'delete_userbuilding');
INSERT INTO `auth_permission` VALUES ('73', 'Can add user drug', '25', 'add_userdrug');
INSERT INTO `auth_permission` VALUES ('74', 'Can change user drug', '25', 'change_userdrug');
INSERT INTO `auth_permission` VALUES ('75', 'Can delete user drug', '25', 'delete_userdrug');
INSERT INTO `auth_permission` VALUES ('76', 'Can add user hooker', '26', 'add_userhooker');
INSERT INTO `auth_permission` VALUES ('77', 'Can change user hooker', '26', 'change_userhooker');
INSERT INTO `auth_permission` VALUES ('78', 'Can delete user hooker', '26', 'delete_userhooker');
INSERT INTO `auth_permission` VALUES ('79', 'Can add business', '27', 'add_business');
INSERT INTO `auth_permission` VALUES ('80', 'Can change business', '27', 'change_business');
INSERT INTO `auth_permission` VALUES ('81', 'Can delete business', '27', 'delete_business');
INSERT INTO `auth_permission` VALUES ('82', 'Can add user business', '28', 'add_userbusiness');
INSERT INTO `auth_permission` VALUES ('83', 'Can change user business', '28', 'change_userbusiness');
INSERT INTO `auth_permission` VALUES ('84', 'Can delete user business', '28', 'delete_userbusiness');
INSERT INTO `auth_permission` VALUES ('85', 'Can add guard', '29', 'add_guard');
INSERT INTO `auth_permission` VALUES ('86', 'Can change guard', '29', 'change_guard');
INSERT INTO `auth_permission` VALUES ('87', 'Can delete guard', '29', 'delete_guard');
INSERT INTO `auth_permission` VALUES ('88', 'Can add user guard', '30', 'add_userguard');
INSERT INTO `auth_permission` VALUES ('89', 'Can change user guard', '30', 'change_userguard');
INSERT INTO `auth_permission` VALUES ('90', 'Can delete user guard', '30', 'delete_userguard');
INSERT INTO `auth_permission` VALUES ('91', 'Can add weapon', '31', 'add_weapon');
INSERT INTO `auth_permission` VALUES ('92', 'Can change weapon', '31', 'change_weapon');
INSERT INTO `auth_permission` VALUES ('93', 'Can delete weapon', '31', 'delete_weapon');
INSERT INTO `auth_permission` VALUES ('94', 'Can add user weapon', '32', 'add_userweapon');
INSERT INTO `auth_permission` VALUES ('95', 'Can change user weapon', '32', 'change_userweapon');
INSERT INTO `auth_permission` VALUES ('96', 'Can delete user weapon', '32', 'delete_userweapon');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'c-mtv@163.com', 'sha1$df48b$97ce6e8393b8ff135c3e794d6e847d6962ea6da8', '1', '1', '1', '2010-04-19 09:55:31', '2010-04-18 20:07:26');

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
  KEY `group_id_refs_id_f0ee9890` (`group_id`),
  CONSTRAINT `group_id_refs_id_f0ee9890` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `user_id_refs_id_831107f1` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
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
  CONSTRAINT `user_id_refs_id_f2045483` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user_user_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for `captcha_captchastore`
-- ----------------------------
DROP TABLE IF EXISTS `captcha_captchastore`;
CREATE TABLE `captcha_captchastore` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challenge` varchar(32) NOT NULL,
  `response` varchar(32) NOT NULL,
  `hashkey` varchar(40) NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashkey` (`hashkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of captcha_captchastore
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_admin_log
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-04-18 20:37:51', '1', '14', '1', '帮助', '1', '');
INSERT INTO `django_admin_log` VALUES ('2', '2010-04-18 20:38:27', '1', '15', '1', '请求人肉支援', '1', '');
INSERT INTO `django_admin_log` VALUES ('3', '2010-04-18 20:38:42', '1', '15', '1', '请求人肉支援', '2', '已修改 body 。');
INSERT INTO `django_admin_log` VALUES ('4', '2010-04-19 10:26:19', '1', '17', '3', 'Avatar object', '1', '');
INSERT INTO `django_admin_log` VALUES ('5', '2010-04-19 10:34:19', '1', '17', '1', 'Avatar object', '1', '');
INSERT INTO `django_admin_log` VALUES ('6', '2010-04-19 10:39:56', '1', '17', '1', 'e:/AppServ/pys/crims/assets/avatar/Winter.jpg', '3', '');
INSERT INTO `django_admin_log` VALUES ('7', '2010-04-19 10:40:04', '1', '17', '2', 'avatar/Winter.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('8', '2010-04-19 10:52:32', '1', '17', '2', 'Avatar object', '2', '已修改 filename 。');
INSERT INTO `django_admin_log` VALUES ('9', '2010-04-19 10:57:07', '1', '17', '3', '2010-04-19 10:57:07.109000', '1', '');
INSERT INTO `django_admin_log` VALUES ('10', '2010-04-19 10:57:24', '1', '17', '4', '2010-04-19 10:57:24.718000', '1', '');
INSERT INTO `django_admin_log` VALUES ('11', '2010-04-19 10:57:36', '1', '17', '5', '2010-04-19 10:57:36.250000', '1', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_content_type` VALUES ('8', 'log entry', 'admin', 'logentry');
INSERT INTO `django_content_type` VALUES ('9', 'registration profile', 'registration', 'registrationprofile');
INSERT INTO `django_content_type` VALUES ('10', 'Navigation', 'grappelli', 'navigation');
INSERT INTO `django_content_type` VALUES ('11', 'Navigation Item', 'grappelli', 'navigationitem');
INSERT INTO `django_content_type` VALUES ('12', 'Bookmark', 'grappelli', 'bookmark');
INSERT INTO `django_content_type` VALUES ('13', 'Bookmark Item', 'grappelli', 'bookmarkitem');
INSERT INTO `django_content_type` VALUES ('14', 'Help', 'grappelli', 'help');
INSERT INTO `django_content_type` VALUES ('15', 'Help Entry', 'grappelli', 'helpitem');
INSERT INTO `django_content_type` VALUES ('16', 'captcha store', 'captcha', 'captchastore');
INSERT INTO `django_content_type` VALUES ('17', 'avatar', 'system', 'avatar');
INSERT INTO `django_content_type` VALUES ('18', 'armor', 'system', 'armor');
INSERT INTO `django_content_type` VALUES ('19', 'character', 'system', 'character');
INSERT INTO `django_content_type` VALUES ('20', 'drug', 'system', 'drug');
INSERT INTO `django_content_type` VALUES ('21', 'building', 'system', 'building');
INSERT INTO `django_content_type` VALUES ('22', 'hooker', 'system', 'hooker');
INSERT INTO `django_content_type` VALUES ('23', 'user armor', 'system', 'userarmor');
INSERT INTO `django_content_type` VALUES ('24', 'user building', 'system', 'userbuilding');
INSERT INTO `django_content_type` VALUES ('25', 'user drug', 'system', 'userdrug');
INSERT INTO `django_content_type` VALUES ('26', 'user hooker', 'system', 'userhooker');
INSERT INTO `django_content_type` VALUES ('27', 'business', 'system', 'business');
INSERT INTO `django_content_type` VALUES ('28', 'user business', 'system', 'userbusiness');
INSERT INTO `django_content_type` VALUES ('29', 'guard', 'system', 'guard');
INSERT INTO `django_content_type` VALUES ('30', 'user guard', 'system', 'userguard');
INSERT INTO `django_content_type` VALUES ('31', 'weapon', 'system', 'weapon');
INSERT INTO `django_content_type` VALUES ('32', 'user weapon', 'system', 'userweapon');

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
INSERT INTO `django_session` VALUES ('23d64587c0070ce0dc9dae8aa27f6053', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21');
INSERT INTO `django_session` VALUES ('5286edf50ae8d76c4766ee5d9ed97aac', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21');
INSERT INTO `django_session` VALUES ('72428d3f08e0427f6a8850ba47a4f659', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:12:55');
INSERT INTO `django_session` VALUES ('86cac61cc2a7f010c3d465c913cab99a', 'gAJ9cQFVCnRlc3Rjb29raWVxAlUGd29ya2VkcQNzLmJmMjBjODVkMTkwZTExMWEzMTkxMjFhNzIz\nZWI2NGJm\n', '2010-05-02 20:09:30');
INSERT INTO `django_session` VALUES ('9f005bf1fd11cc2e8ff354a75ee459b1', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UHbWVzc2FnZXEEXXEFKFUHc3VjY2Vzc3EGWBwAAABTaXRl\nIHdhcyBhZGRlZCB0byBCb29rbWFya3MucQdlc1UNX2F1dGhfdXNlcl9pZHEIigEBVRJfYXV0aF91\nc2VyX2JhY2tlbmRxCVUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRx\nCnUuMGI4YzhlNzg1MzY0NTc2ZjJlZDQyNTgzMzZkMmNlNmU=\n', '2010-05-02 20:39:45');
INSERT INTO `django_session` VALUES ('d81e95756a970d5eba5f0681d1d55287', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:09:08');
INSERT INTO `django_session` VALUES ('f517e253a8eb629ee198c74f2258d978', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-03 09:55:31');

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
-- Table structure for `grappelli_bookmark`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_bookmark`;
CREATE TABLE `grappelli_bookmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grappelli_bookmark_user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_ca562ed7` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_bookmark
-- ----------------------------
INSERT INTO `grappelli_bookmark` VALUES ('1', '1');

-- ----------------------------
-- Table structure for `grappelli_bookmarkitem`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_bookmarkitem`;
CREATE TABLE `grappelli_bookmarkitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bookmark_id` int(11) NOT NULL,
  `title` varchar(80) NOT NULL,
  `link` varchar(200) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grappelli_bookmarkitem_bookmark_id` (`bookmark_id`),
  CONSTRAINT `bookmark_id_refs_id_a9bea054` FOREIGN KEY (`bookmark_id`) REFERENCES `grappelli_bookmark` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_bookmarkitem
-- ----------------------------
INSERT INTO `grappelli_bookmarkitem` VALUES ('1', '1', '站点管理', '/admin/', '0');

-- ----------------------------
-- Table structure for `grappelli_help`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_help`;
CREATE TABLE `grappelli_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_help
-- ----------------------------
INSERT INTO `grappelli_help` VALUES ('1', '帮助', '0');

-- ----------------------------
-- Table structure for `grappelli_helpitem`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_helpitem`;
CREATE TABLE `grappelli_helpitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `help_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `link` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grappelli_helpitem_help_id` (`help_id`),
  CONSTRAINT `help_id_refs_id_b6db1672` FOREIGN KEY (`help_id`) REFERENCES `grappelli_help` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_helpitem
-- ----------------------------
INSERT INTO `grappelli_helpitem` VALUES ('1', '1', '请求人肉支援', '/admin/', '<p>这里究竟是什么样子的？</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '0');

-- ----------------------------
-- Table structure for `grappelli_navigation`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_navigation`;
CREATE TABLE `grappelli_navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_navigation
-- ----------------------------
INSERT INTO `grappelli_navigation` VALUES ('1', 'Media Management', '0');
INSERT INTO `grappelli_navigation` VALUES ('2', 'Contents', '1');
INSERT INTO `grappelli_navigation` VALUES ('3', 'External', '2');
INSERT INTO `grappelli_navigation` VALUES ('4', 'Documentation', '3');

-- ----------------------------
-- Table structure for `grappelli_navigationitem`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_navigationitem`;
CREATE TABLE `grappelli_navigationitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigation_id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `link` varchar(200) NOT NULL,
  `category` varchar(1) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grappelli_navigationitem_navigation_id` (`navigation_id`),
  CONSTRAINT `navigation_id_refs_id_d37cf34` FOREIGN KEY (`navigation_id`) REFERENCES `grappelli_navigation` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_navigationitem
-- ----------------------------
INSERT INTO `grappelli_navigationitem` VALUES ('1', '1', 'FileBrowser', '/admin/filebrowser/browse/', '1', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('2', '2', 'Main', '/admin/', '1', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('3', '2', 'Grappelli', '/admin/grappelli/', '1', '1');
INSERT INTO `grappelli_navigationitem` VALUES ('4', '3', 'Grappelli GoogleCode', 'http://code.google.com/p/django-grappelli/', '2', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('5', '4', 'CMS Help', '/grappelli/help/', '1', '0');

-- ----------------------------
-- Table structure for `grappelli_navigationitem_groups`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_navigationitem_groups`;
CREATE TABLE `grappelli_navigationitem_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigationitem_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `navigationitem_id` (`navigationitem_id`,`group_id`),
  KEY `group_id_refs_id_6daaacb1` (`group_id`),
  CONSTRAINT `group_id_refs_id_6daaacb1` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `navigationitem_id_refs_id_8f2cd403` FOREIGN KEY (`navigationitem_id`) REFERENCES `grappelli_navigationitem` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_navigationitem_groups
-- ----------------------------

-- ----------------------------
-- Table structure for `grappelli_navigationitem_users`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_navigationitem_users`;
CREATE TABLE `grappelli_navigationitem_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navigationitem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `navigationitem_id` (`navigationitem_id`,`user_id`),
  KEY `user_id_refs_id_c396c6b7` (`user_id`),
  CONSTRAINT `user_id_refs_id_c396c6b7` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `navigationitem_id_refs_id_fbdd09fc` FOREIGN KEY (`navigationitem_id`) REFERENCES `grappelli_navigationitem` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_navigationitem_users
-- ----------------------------

-- ----------------------------
-- Table structure for `registration_registrationprofile`
-- ----------------------------
DROP TABLE IF EXISTS `registration_registrationprofile`;
CREATE TABLE `registration_registrationprofile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activation_key` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_cecd7f3c` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of registration_registrationprofile
-- ----------------------------

-- ----------------------------
-- Table structure for `system_armor`
-- ----------------------------
DROP TABLE IF EXISTS `system_armor`;
CREATE TABLE `system_armor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `tolerance` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_armor
-- ----------------------------

-- ----------------------------
-- Table structure for `system_avatar`
-- ----------------------------
DROP TABLE IF EXISTS `system_avatar`;
CREATE TABLE `system_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_avatar
-- ----------------------------
INSERT INTO `system_avatar` VALUES ('2', 'uploads/avatar/winter_thumbnail.jpg', '2010-04-19 10:40:04');
INSERT INTO `system_avatar` VALUES ('3', 'uploads/avatar/water_lilies_thumbnail.jpg', '2010-04-19 10:57:07');
INSERT INTO `system_avatar` VALUES ('4', 'uploads/avatar/sunset_thumbnail.jpg', '2010-04-19 10:57:24');
INSERT INTO `system_avatar` VALUES ('5', 'uploads/avatar/blue_hills_thumbnail.jpg', '2010-04-19 10:57:36');

-- ----------------------------
-- Table structure for `system_building`
-- ----------------------------
DROP TABLE IF EXISTS `system_building`;
CREATE TABLE `system_building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `output` int(11) NOT NULL,
  `expend` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `system_building_drug_id` (`drug_id`),
  CONSTRAINT `drug_id_refs_id_2fa06277` FOREIGN KEY (`drug_id`) REFERENCES `system_drug` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_building
-- ----------------------------

-- ----------------------------
-- Table structure for `system_business`
-- ----------------------------
DROP TABLE IF EXISTS `system_business`;
CREATE TABLE `system_business` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `max_vistors` smallint(6) NOT NULL,
  `price` int(11) NOT NULL,
  `expend` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `limit` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_business
-- ----------------------------

-- ----------------------------
-- Table structure for `system_character`
-- ----------------------------
DROP TABLE IF EXISTS `system_character`;
CREATE TABLE `system_character` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `avatar` varchar(200) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `intelligence` int(11) NOT NULL,
  `strength` int(11) NOT NULL,
  `charisma` int(11) NOT NULL,
  `tolerance` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_character
-- ----------------------------

-- ----------------------------
-- Table structure for `system_drug`
-- ----------------------------
DROP TABLE IF EXISTS `system_drug`;
CREATE TABLE `system_drug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `stamina` smallint(6) NOT NULL,
  `spirit` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_drug
-- ----------------------------

-- ----------------------------
-- Table structure for `system_guard`
-- ----------------------------
DROP TABLE IF EXISTS `system_guard`;
CREATE TABLE `system_guard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `strength` smallint(6) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `price` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_guard
-- ----------------------------

-- ----------------------------
-- Table structure for `system_hooker`
-- ----------------------------
DROP TABLE IF EXISTS `system_hooker`;
CREATE TABLE `system_hooker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `price` int(11) NOT NULL,
  `expend` int(11) NOT NULL,
  `visitprice` int(11) NOT NULL,
  `sickprobability` decimal(4,4) NOT NULL,
  `is_random` tinyint(1) NOT NULL,
  `stamina` int(11) NOT NULL,
  `spirit` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_hooker
-- ----------------------------

-- ----------------------------
-- Table structure for `system_weapon`
-- ----------------------------
DROP TABLE IF EXISTS `system_weapon`;
CREATE TABLE `system_weapon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `price` int(11) NOT NULL,
  `damage_min` smallint(6) NOT NULL,
  `damage_max` smallint(6) NOT NULL,
  `skill` smallint(6) NOT NULL,
  `proficiency` smallint(6) NOT NULL,
  `type` varchar(20) NOT NULL,
  `durability` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_weapon
-- ----------------------------

-- ----------------------------
-- Table structure for `user_armor`
-- ----------------------------
DROP TABLE IF EXISTS `user_armor`;
CREATE TABLE `user_armor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `armor_id` int(11) NOT NULL,
  `actived` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_armor_user_id` (`user_id`),
  KEY `user_armor_armor_id` (`armor_id`),
  CONSTRAINT `armor_id_refs_id_633aab41` FOREIGN KEY (`armor_id`) REFERENCES `system_armor` (`id`),
  CONSTRAINT `user_id_refs_id_7e8d3c95` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_armor
-- ----------------------------

-- ----------------------------
-- Table structure for `user_building`
-- ----------------------------
DROP TABLE IF EXISTS `user_building`;
CREATE TABLE `user_building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `units` int(11) NOT NULL,
  `outputs` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_building_user_id` (`user_id`),
  KEY `user_building_building_id` (`building_id`),
  CONSTRAINT `building_id_refs_id_1f4b4931` FOREIGN KEY (`building_id`) REFERENCES `system_building` (`id`),
  CONSTRAINT `user_id_refs_id_7f9fd675` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_building
-- ----------------------------

-- ----------------------------
-- Table structure for `user_business`
-- ----------------------------
DROP TABLE IF EXISTS `user_business`;
CREATE TABLE `user_business` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `max_respect` smallint(6) NOT NULL,
  `entrance_fee` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `income` int(11) NOT NULL,
  `closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_business_user_id` (`user_id`),
  KEY `user_business_business_id` (`business_id`),
  CONSTRAINT `business_id_refs_id_8200e8d` FOREIGN KEY (`business_id`) REFERENCES `system_business` (`id`),
  CONSTRAINT `user_id_refs_id_7dad1f59` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_business
-- ----------------------------

-- ----------------------------
-- Table structure for `user_drug`
-- ----------------------------
DROP TABLE IF EXISTS `user_drug`;
CREATE TABLE `user_drug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `units` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_drug_user_id` (`user_id`),
  KEY `user_drug_drug_id` (`drug_id`),
  CONSTRAINT `drug_id_refs_id_773fa5e7` FOREIGN KEY (`drug_id`) REFERENCES `system_drug` (`id`),
  CONSTRAINT `user_id_refs_id_7d9848b1` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_drug
-- ----------------------------

-- ----------------------------
-- Table structure for `user_guard`
-- ----------------------------
DROP TABLE IF EXISTS `user_guard`;
CREATE TABLE `user_guard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `guard_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_guard_user_id` (`user_id`),
  KEY `user_guard_guard_id` (`guard_id`),
  CONSTRAINT `guard_id_refs_id_498fd62f` FOREIGN KEY (`guard_id`) REFERENCES `system_guard` (`id`),
  CONSTRAINT `user_id_refs_id_7923991d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_guard
-- ----------------------------

-- ----------------------------
-- Table structure for `user_hooker`
-- ----------------------------
DROP TABLE IF EXISTS `user_hooker`;
CREATE TABLE `user_hooker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hooker_id` int(11) NOT NULL,
  `visitprice` smallint(6) NOT NULL,
  `expend` smallint(6) NOT NULL,
  `income` int(11) NOT NULL,
  `freetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_hooker_user_id` (`user_id`),
  KEY `user_hooker_hooker_id` (`hooker_id`),
  CONSTRAINT `hooker_id_refs_id_3d1d1967` FOREIGN KEY (`hooker_id`) REFERENCES `system_hooker` (`id`),
  CONSTRAINT `user_id_refs_id_42aafdc5` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_hooker
-- ----------------------------

-- ----------------------------
-- Table structure for `user_weapon`
-- ----------------------------
DROP TABLE IF EXISTS `user_weapon`;
CREATE TABLE `user_weapon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `weapon_id` int(11) NOT NULL,
  `actived` tinyint(1) NOT NULL,
  `used` int(11) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_weapon_user_id` (`user_id`),
  KEY `user_weapon_weapon_id` (`weapon_id`),
  CONSTRAINT `user_id_refs_id_9dba3fd` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `weapon_id_refs_id_10147f1b` FOREIGN KEY (`weapon_id`) REFERENCES `system_weapon` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_weapon
-- ----------------------------
