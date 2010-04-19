/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50139
 Source Host           : localhost
 Source Database       : crims

 Target Server Type    : MySQL
 Target Server Version : 50139
 File Encoding         : utf-8

 Date: 04/19/2010 20:02:37 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `auth_group`;
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `auth_group_permissions`
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
--  Table structure for `auth_message`
-- ----------------------------
DROP TABLE IF EXISTS `auth_message`;
CREATE TABLE `auth_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_message_user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_9af0b65a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `auth_permission`
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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `auth_permission`
-- ----------------------------
INSERT INTO `auth_permission` VALUES ('1', 'Can add permission', '1', 'add_permission'), ('2', 'Can change permission', '1', 'change_permission'), ('3', 'Can delete permission', '1', 'delete_permission'), ('4', 'Can add group', '2', 'add_group'), ('5', 'Can change group', '2', 'change_group'), ('6', 'Can delete group', '2', 'delete_group'), ('7', 'Can add user', '3', 'add_user'), ('8', 'Can change user', '3', 'change_user'), ('9', 'Can delete user', '3', 'delete_user'), ('10', 'Can add message', '4', 'add_message'), ('11', 'Can change message', '4', 'change_message'), ('12', 'Can delete message', '4', 'delete_message'), ('13', 'Can add content type', '5', 'add_contenttype'), ('14', 'Can change content type', '5', 'change_contenttype'), ('15', 'Can delete content type', '5', 'delete_contenttype'), ('16', 'Can add session', '6', 'add_session'), ('17', 'Can change session', '6', 'change_session'), ('18', 'Can delete session', '6', 'delete_session'), ('19', 'Can add site', '7', 'add_site'), ('20', 'Can change site', '7', 'change_site'), ('21', 'Can delete site', '7', 'delete_site'), ('22', 'Can add log entry', '8', 'add_logentry'), ('23', 'Can change log entry', '8', 'change_logentry'), ('24', 'Can delete log entry', '8', 'delete_logentry'), ('25', 'Can add registration profile', '9', 'add_registrationprofile'), ('26', 'Can change registration profile', '9', 'change_registrationprofile'), ('27', 'Can delete registration profile', '9', 'delete_registrationprofile'), ('28', 'Can add Navigation', '10', 'add_navigation'), ('29', 'Can change Navigation', '10', 'change_navigation'), ('30', 'Can delete Navigation', '10', 'delete_navigation'), ('31', 'Can add Navigation Item', '11', 'add_navigationitem'), ('32', 'Can change Navigation Item', '11', 'change_navigationitem'), ('33', 'Can delete Navigation Item', '11', 'delete_navigationitem'), ('34', 'Can add Bookmark', '12', 'add_bookmark'), ('35', 'Can change Bookmark', '12', 'change_bookmark'), ('36', 'Can delete Bookmark', '12', 'delete_bookmark'), ('37', 'Can add Bookmark Item', '13', 'add_bookmarkitem'), ('38', 'Can change Bookmark Item', '13', 'change_bookmarkitem'), ('39', 'Can delete Bookmark Item', '13', 'delete_bookmarkitem'), ('40', 'Can add Help', '14', 'add_help'), ('41', 'Can change Help', '14', 'change_help'), ('42', 'Can delete Help', '14', 'delete_help'), ('43', 'Can add Help Entry', '15', 'add_helpitem'), ('44', 'Can change Help Entry', '15', 'change_helpitem'), ('45', 'Can delete Help Entry', '15', 'delete_helpitem'), ('46', 'Can add captcha store', '16', 'add_captchastore'), ('47', 'Can change captcha store', '16', 'change_captchastore'), ('48', 'Can delete captcha store', '16', 'delete_captchastore'), ('49', 'Can add avatar', '17', 'add_avatar'), ('50', 'Can change avatar', '17', 'change_avatar'), ('51', 'Can delete avatar', '17', 'delete_avatar'), ('52', 'Can add armor', '18', 'add_armor'), ('53', 'Can change armor', '18', 'change_armor'), ('54', 'Can delete armor', '18', 'delete_armor'), ('55', 'Can add character', '19', 'add_character'), ('56', 'Can change character', '19', 'change_character'), ('57', 'Can delete character', '19', 'delete_character'), ('58', 'Can add drug', '20', 'add_drug'), ('59', 'Can change drug', '20', 'change_drug'), ('60', 'Can delete drug', '20', 'delete_drug'), ('61', 'Can add building', '21', 'add_building'), ('62', 'Can change building', '21', 'change_building'), ('63', 'Can delete building', '21', 'delete_building'), ('64', 'Can add hooker', '22', 'add_hooker'), ('65', 'Can change hooker', '22', 'change_hooker'), ('66', 'Can delete hooker', '22', 'delete_hooker'), ('67', 'Can add user armor', '23', 'add_userarmor'), ('68', 'Can change user armor', '23', 'change_userarmor'), ('69', 'Can delete user armor', '23', 'delete_userarmor'), ('70', 'Can add user building', '24', 'add_userbuilding'), ('71', 'Can change user building', '24', 'change_userbuilding'), ('72', 'Can delete user building', '24', 'delete_userbuilding'), ('73', 'Can add user drug', '25', 'add_userdrug'), ('74', 'Can change user drug', '25', 'change_userdrug'), ('75', 'Can delete user drug', '25', 'delete_userdrug'), ('76', 'Can add user hooker', '26', 'add_userhooker'), ('77', 'Can change user hooker', '26', 'change_userhooker'), ('78', 'Can delete user hooker', '26', 'delete_userhooker'), ('79', 'Can add business', '27', 'add_business'), ('80', 'Can change business', '27', 'change_business'), ('81', 'Can delete business', '27', 'delete_business'), ('82', 'Can add user business', '28', 'add_userbusiness'), ('83', 'Can change user business', '28', 'change_userbusiness'), ('84', 'Can delete user business', '28', 'delete_userbusiness'), ('85', 'Can add guard', '29', 'add_guard'), ('86', 'Can change guard', '29', 'change_guard'), ('87', 'Can delete guard', '29', 'delete_guard'), ('88', 'Can add user guard', '30', 'add_userguard'), ('89', 'Can change user guard', '30', 'change_userguard'), ('90', 'Can delete user guard', '30', 'delete_userguard'), ('91', 'Can add weapon', '31', 'add_weapon'), ('92', 'Can change weapon', '31', 'change_weapon'), ('93', 'Can delete weapon', '31', 'delete_weapon'), ('94', 'Can add user weapon', '32', 'add_userweapon'), ('95', 'Can change user weapon', '32', 'change_userweapon'), ('96', 'Can delete user weapon', '32', 'delete_userweapon'), ('97', 'Can add province', '33', 'add_province'), ('98', 'Can change province', '33', 'change_province'), ('99', 'Can delete province', '33', 'delete_province'), ('100', 'Can add event', '34', 'add_event'), ('101', 'Can change event', '34', 'change_event'), ('102', 'Can delete event', '34', 'delete_event');

-- ----------------------------
--  Table structure for `auth_user`
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
--  Records of `auth_user`
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'c-mtv@163.com', 'sha1$df48b$97ce6e8393b8ff135c3e794d6e847d6962ea6da8', '1', '1', '1', '2010-04-19 15:59:06', '2010-04-18 20:07:26');

-- ----------------------------
--  Table structure for `auth_user_groups`
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
--  Table structure for `auth_user_user_permissions`
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
--  Table structure for `captcha_captchastore`
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
--  Table structure for `django_admin_log`
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
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_admin_log`
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-04-18 20:37:51', '1', '14', '1', '帮助', '1', ''), ('2', '2010-04-18 20:38:27', '1', '15', '1', '请求人肉支援', '1', ''), ('3', '2010-04-18 20:38:42', '1', '15', '1', '请求人肉支援', '2', '已修改 body 。'), ('4', '2010-04-19 10:26:19', '1', '17', '3', 'Avatar object', '1', ''), ('5', '2010-04-19 10:34:19', '1', '17', '1', 'Avatar object', '1', ''), ('6', '2010-04-19 10:39:56', '1', '17', '1', 'e:/AppServ/pys/crims/assets/avatar/Winter.jpg', '3', ''), ('7', '2010-04-19 10:40:04', '1', '17', '2', 'avatar/Winter.jpg', '1', ''), ('8', '2010-04-19 10:52:32', '1', '17', '2', 'Avatar object', '2', '已修改 filename 。'), ('9', '2010-04-19 10:57:07', '1', '17', '3', '2010-04-19 10:57:07.109000', '1', ''), ('10', '2010-04-19 10:57:24', '1', '17', '4', '2010-04-19 10:57:24.718000', '1', ''), ('11', '2010-04-19 10:57:36', '1', '17', '5', '2010-04-19 10:57:36.250000', '1', ''), ('12', '2010-04-19 16:13:30', '1', '33', '1', '帝都', '1', ''), ('13', '2010-04-19 16:13:54', '1', '33', '1', '帝都', '3', ''), ('14', '2010-04-19 18:33:51', '1', '17', '5', 'uploads/avatar/blue_hills_thumbnail.jpg', '3', ''), ('15', '2010-04-19 18:33:51', '1', '17', '4', 'uploads/avatar/sunset_thumbnail.jpg', '3', ''), ('16', '2010-04-19 18:33:51', '1', '17', '3', 'uploads/avatar/water_lilies_thumbnail.jpg', '3', ''), ('17', '2010-04-19 18:33:51', '1', '17', '2', 'uploads/avatar/winter_thumbnail.jpg', '3', ''), ('18', '2010-04-19 18:40:13', '1', '17', '6', 'uploads/avatar/avatar_1.jpg', '1', ''), ('19', '2010-04-19 18:41:51', '1', '17', '7', 'uploads/avatar/avatar_2.jpg', '1', ''), ('20', '2010-04-19 18:42:15', '1', '17', '8', 'uploads/avatar/avatar_4.jpg', '1', ''), ('21', '2010-04-19 18:42:22', '1', '17', '9', 'uploads/avatar/avatar_5.jpg', '1', ''), ('22', '2010-04-19 18:42:28', '1', '17', '10', 'uploads/avatar/avatar_6.jpg', '1', ''), ('23', '2010-04-19 18:42:36', '1', '17', '11', 'uploads/avatar/avatar_7.jpg', '1', ''), ('24', '2010-04-19 18:42:44', '1', '17', '12', 'uploads/avatar/avatar_8.jpg', '1', ''), ('25', '2010-04-19 18:42:51', '1', '17', '13', 'uploads/avatar/avatar_9.jpg', '1', ''), ('26', '2010-04-19 18:42:59', '1', '17', '14', 'uploads/avatar/avatar_10.jpg', '1', ''), ('27', '2010-04-19 18:43:07', '1', '17', '15', 'uploads/avatar/avatar_11.jpg', '1', ''), ('28', '2010-04-19 18:43:16', '1', '17', '16', 'uploads/avatar/avatar_12.jpg', '1', ''), ('29', '2010-04-19 18:43:23', '1', '17', '17', 'uploads/avatar/avatar_13.jpg', '1', ''), ('30', '2010-04-19 18:43:32', '1', '17', '18', 'uploads/avatar/avatar_14.jpg', '1', ''), ('31', '2010-04-19 18:43:40', '1', '17', '19', 'uploads/avatar/avatar_15.jpg', '1', ''), ('32', '2010-04-19 18:43:49', '1', '17', '20', 'uploads/avatar/avatar_16.jpg', '1', ''), ('33', '2010-04-19 18:43:58', '1', '17', '21', 'uploads/avatar/avatar_17.jpg', '1', ''), ('34', '2010-04-19 18:44:10', '1', '17', '22', 'uploads/avatar/avatar_18.jpg', '1', ''), ('35', '2010-04-19 18:44:20', '1', '17', '23', 'uploads/avatar/avatar_19.jpg', '1', ''), ('36', '2010-04-19 18:44:29', '1', '17', '24', 'uploads/avatar/avatar_26.jpg', '1', ''), ('37', '2010-04-19 18:44:37', '1', '17', '25', 'uploads/avatar/avatar_27.jpg', '1', ''), ('38', '2010-04-19 18:44:46', '1', '17', '26', 'uploads/avatar/avatar_28.jpg', '1', ''), ('39', '2010-04-19 18:44:53', '1', '17', '27', 'uploads/avatar/avatar_29.jpg', '1', ''), ('40', '2010-04-19 18:45:02', '1', '17', '28', 'uploads/avatar/avatar_30.jpg', '1', ''), ('41', '2010-04-19 18:45:11', '1', '17', '29', 'uploads/avatar/avatar_31.jpg', '1', ''), ('42', '2010-04-19 18:45:19', '1', '17', '30', 'uploads/avatar/avatar_32.jpg', '1', ''), ('43', '2010-04-19 18:45:27', '1', '17', '31', 'uploads/avatar/avatar_33.jpg', '1', ''), ('44', '2010-04-19 18:45:37', '1', '17', '32', 'uploads/avatar/avatar_34.jpg', '1', ''), ('45', '2010-04-19 18:45:44', '1', '17', '33', 'uploads/avatar/avatar_35.jpg', '1', ''), ('46', '2010-04-19 18:45:51', '1', '17', '34', 'uploads/avatar/avatar_36.jpg', '1', ''), ('47', '2010-04-19 18:45:57', '1', '17', '35', 'uploads/avatar/avatar_37.jpg', '1', ''), ('48', '2010-04-19 18:46:03', '1', '17', '36', 'uploads/avatar/avatar_38.jpg', '1', ''), ('49', '2010-04-19 18:46:11', '1', '17', '37', 'uploads/avatar/avatar_39.jpg', '1', ''), ('50', '2010-04-19 18:46:17', '1', '17', '38', 'uploads/avatar/avatar_40.jpg', '1', ''), ('51', '2010-04-19 18:46:22', '1', '17', '39', 'uploads/avatar/avatar_41.jpg', '1', ''), ('52', '2010-04-19 18:46:28', '1', '17', '40', 'uploads/avatar/avatar_42.jpg', '1', ''), ('53', '2010-04-19 18:46:35', '1', '17', '41', 'uploads/avatar/avatar_43.jpg', '1', ''), ('54', '2010-04-19 18:46:41', '1', '17', '42', 'uploads/avatar/avatar_44.jpg', '1', ''), ('55', '2010-04-19 18:46:47', '1', '17', '43', 'uploads/avatar/avatar_45.jpg', '1', ''), ('56', '2010-04-19 18:46:52', '1', '17', '44', 'uploads/avatar/avatar_46.jpg', '1', ''), ('57', '2010-04-19 18:46:57', '1', '17', '45', 'uploads/avatar/avatar_47.jpg', '1', ''), ('58', '2010-04-19 18:47:02', '1', '17', '46', 'uploads/avatar/avatar_48.jpg', '1', ''), ('59', '2010-04-19 18:47:07', '1', '17', '47', 'uploads/avatar/avatar_49.jpg', '1', ''), ('60', '2010-04-19 18:47:12', '1', '17', '48', 'uploads/avatar/avatar_50.jpg', '1', ''), ('61', '2010-04-19 18:47:21', '1', '17', '49', 'uploads/avatar/avatar_51.jpg', '1', ''), ('62', '2010-04-19 18:47:27', '1', '17', '50', 'uploads/avatar/avatar_52.jpg', '1', ''), ('63', '2010-04-19 18:47:33', '1', '17', '51', 'uploads/avatar/avatar_53.jpg', '1', ''), ('64', '2010-04-19 19:05:42', '1', '18', '1', 'Disper', '1', ''), ('65', '2010-04-19 19:06:07', '1', '18', '2', '皮夹克', '1', ''), ('66', '2010-04-19 19:06:29', '1', '18', '3', '合金盔甲', '1', ''), ('67', '2010-04-19 19:07:12', '1', '18', '4', '防弹背心', '1', ''), ('68', '2010-04-19 19:07:48', '1', '18', '5', '纳米战斗衣', '1', ''), ('69', '2010-04-19 19:08:18', '1', '18', '6', '反恐特勤装', '1', ''), ('70', '2010-04-19 19:08:27', '1', '18', '1', '尿布', '2', '已修改 title 。'), ('71', '2010-04-19 19:11:53', '1', '20', '1', '香烟', '1', ''), ('72', '2010-04-19 19:12:26', '1', '20', '2', '止痛药', '1', ''), ('73', '2010-04-19 19:13:13', '1', '20', '3', '酒', '1', ''), ('74', '2010-04-19 19:13:41', '1', '20', '4', '迷幻蘑菇', '1', ''), ('75', '2010-04-19 19:14:32', '1', '20', '5', '迷奸药', '1', ''), ('76', '2010-04-19 19:15:06', '1', '20', '6', '大麻', '1', ''), ('77', '2010-04-19 19:15:42', '1', '20', '7', '迷幻药', '1', ''), ('78', '2010-04-19 19:19:41', '1', '20', '8', '迷奸药', '1', ''), ('79', '2010-04-19 19:26:26', '1', '20', '1', '止痛药', '2', '已修改 title 。'), ('80', '2010-04-19 19:26:40', '1', '20', '2', '香烟', '2', '已修改 title 。'), ('81', '2010-04-19 19:27:04', '1', '20', '5', '大麻', '2', '已修改 title 。'), ('82', '2010-04-19 19:27:32', '1', '20', '6', '迷幻药', '2', '已修改 title 。'), ('83', '2010-04-19 19:27:54', '1', '20', '7', '迷奸药', '2', '已修改 title 。'), ('84', '2010-04-19 19:28:24', '1', '20', '8', '摇头丸', '2', '已修改 title 和 price 。'), ('85', '2010-04-19 19:28:48', '1', '20', '9', '安非他命', '1', ''), ('86', '2010-04-19 19:29:06', '1', '20', '10', '鸦片', '1', ''), ('87', '2010-04-19 19:29:22', '1', '20', '11', '可卡因', '1', ''), ('88', '2010-04-19 19:29:37', '1', '20', '12', 'K他命', '1', ''), ('89', '2010-04-19 19:29:58', '1', '20', '13', '吗啡', '1', ''), ('90', '2010-04-19 19:30:17', '1', '20', '14', '海洛英', '1', ''), ('91', '2010-04-19 19:36:20', '1', '21', '1', '烟草', '1', ''), ('92', '2010-04-19 19:37:10', '1', '21', '2', '私酒', '1', ''), ('93', '2010-04-19 19:37:45', '1', '21', '3', '大麻种植地', '1', ''), ('94', '2010-04-19 19:38:21', '1', '21', '4', '酿酒厂', '1', ''), ('95', '2010-04-19 19:38:53', '1', '21', '5', '药房', '1', ''), ('96', '2010-04-19 19:39:26', '1', '21', '6', '蘑菇种植基地', '1', ''), ('97', '2010-04-19 19:40:01', '1', '21', '7', '烟草种植园', '1', ''), ('98', '2010-04-19 19:40:31', '1', '21', '8', '吗啡实验室', '1', ''), ('99', '2010-04-19 19:41:12', '1', '21', '9', '迷幻药实验室', '1', ''), ('100', '2010-04-19 19:41:45', '1', '21', '10', '摇头丸实验室', '1', ''), ('101', '2010-04-19 19:42:13', '1', '21', '11', '罂粟田', '1', ''), ('102', '2010-04-19 19:42:53', '1', '21', '12', '迷奸药实验室', '1', ''), ('103', '2010-04-19 19:43:31', '1', '21', '13', 'K他命实验室', '1', ''), ('104', '2010-04-19 19:44:05', '1', '21', '14', '可卡因工厂', '1', ''), ('105', '2010-04-19 19:44:39', '1', '21', '15', '安非他命实验室', '1', ''), ('106', '2010-04-19 19:45:15', '1', '21', '16', '海洛英工厂', '1', ''), ('107', '2010-04-19 19:50:16', '1', '27', '1', '夜总会', '1', ''), ('108', '2010-04-19 19:51:00', '1', '27', '2', '狂欢派对', '1', ''), ('109', '2010-04-19 19:51:41', '1', '27', '3', '青楼', '1', ''), ('110', '2010-04-19 19:52:14', '1', '27', '4', '怡红院', '1', ''), ('111', '2010-04-19 19:55:42', '1', '19', '1', '领班', '1', ''), ('112', '2010-04-19 19:56:13', '1', '19', '2', '杀手', '1', ''), ('113', '2010-04-19 19:56:46', '1', '19', '3', '商人', '1', ''), ('114', '2010-04-19 19:57:21', '1', '19', '4', '强盗', '1', ''), ('115', '2010-04-19 19:57:43', '1', '19', '5', '黑社会', '1', '');

-- ----------------------------
--  Table structure for `django_content_type`
-- ----------------------------
DROP TABLE IF EXISTS `django_content_type`;
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_label` (`app_label`,`model`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_content_type`
-- ----------------------------
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission'), ('2', 'group', 'auth', 'group'), ('3', 'user', 'auth', 'user'), ('4', 'message', 'auth', 'message'), ('5', 'content type', 'contenttypes', 'contenttype'), ('6', 'session', 'sessions', 'session'), ('7', 'site', 'sites', 'site'), ('8', 'log entry', 'admin', 'logentry'), ('9', 'registration profile', 'registration', 'registrationprofile'), ('10', 'Navigation', 'grappelli', 'navigation'), ('11', 'Navigation Item', 'grappelli', 'navigationitem'), ('12', 'Bookmark', 'grappelli', 'bookmark'), ('13', 'Bookmark Item', 'grappelli', 'bookmarkitem'), ('14', 'Help', 'grappelli', 'help'), ('15', 'Help Entry', 'grappelli', 'helpitem'), ('16', 'captcha store', 'captcha', 'captchastore'), ('17', 'avatar', 'system', 'avatar'), ('18', 'armor', 'system', 'armor'), ('19', 'character', 'system', 'character'), ('20', 'drug', 'system', 'drug'), ('21', 'building', 'system', 'building'), ('22', 'hooker', 'system', 'hooker'), ('23', 'user armor', 'system', 'userarmor'), ('24', 'user building', 'system', 'userbuilding'), ('25', 'user drug', 'system', 'userdrug'), ('26', 'user hooker', 'system', 'userhooker'), ('27', 'business', 'system', 'business'), ('28', 'user business', 'system', 'userbusiness'), ('29', 'guard', 'system', 'guard'), ('30', 'user guard', 'system', 'userguard'), ('31', 'weapon', 'system', 'weapon'), ('32', 'user weapon', 'system', 'userweapon'), ('33', 'province', 'system', 'province'), ('34', 'event', 'system', 'event');

-- ----------------------------
--  Table structure for `django_session`
-- ----------------------------
DROP TABLE IF EXISTS `django_session`;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_session`
-- ----------------------------
INSERT INTO `django_session` VALUES ('23d64587c0070ce0dc9dae8aa27f6053', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21'), ('5286edf50ae8d76c4766ee5d9ed97aac', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21'), ('72428d3f08e0427f6a8850ba47a4f659', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:12:55'), ('86cac61cc2a7f010c3d465c913cab99a', 'gAJ9cQFVCnRlc3Rjb29raWVxAlUGd29ya2VkcQNzLmJmMjBjODVkMTkwZTExMWEzMTkxMjFhNzIz\nZWI2NGJm\n', '2010-05-02 20:09:30'), ('93809efc9d4685d520b0c9272d2f9931', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-03 15:59:06'), ('9f005bf1fd11cc2e8ff354a75ee459b1', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UHbWVzc2FnZXEEXXEFKFUHc3VjY2Vzc3EGWBwAAABTaXRl\nIHdhcyBhZGRlZCB0byBCb29rbWFya3MucQdlc1UNX2F1dGhfdXNlcl9pZHEIigEBVRJfYXV0aF91\nc2VyX2JhY2tlbmRxCVUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRx\nCnUuMGI4YzhlNzg1MzY0NTc2ZjJlZDQyNTgzMzZkMmNlNmU=\n', '2010-05-02 20:39:45'), ('d81e95756a970d5eba5f0681d1d55287', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:09:08');

-- ----------------------------
--  Table structure for `django_site`
-- ----------------------------
DROP TABLE IF EXISTS `django_site`;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_site`
-- ----------------------------
INSERT INTO `django_site` VALUES ('1', 'example.com', 'example.com');

-- ----------------------------
--  Table structure for `grappelli_bookmark`
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
--  Records of `grappelli_bookmark`
-- ----------------------------
INSERT INTO `grappelli_bookmark` VALUES ('1', '1');

-- ----------------------------
--  Table structure for `grappelli_bookmarkitem`
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
--  Records of `grappelli_bookmarkitem`
-- ----------------------------
INSERT INTO `grappelli_bookmarkitem` VALUES ('1', '1', '站点管理', '/admin/', '0');

-- ----------------------------
--  Table structure for `grappelli_help`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_help`;
CREATE TABLE `grappelli_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `grappelli_help`
-- ----------------------------
INSERT INTO `grappelli_help` VALUES ('1', '帮助', '0');

-- ----------------------------
--  Table structure for `grappelli_helpitem`
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
--  Records of `grappelli_helpitem`
-- ----------------------------
INSERT INTO `grappelli_helpitem` VALUES ('1', '1', '请求人肉支援', '/admin/', '<p>这里究竟是什么样子的？</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', '0');

-- ----------------------------
--  Table structure for `grappelli_navigation`
-- ----------------------------
DROP TABLE IF EXISTS `grappelli_navigation`;
CREATE TABLE `grappelli_navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `grappelli_navigation`
-- ----------------------------
INSERT INTO `grappelli_navigation` VALUES ('1', 'Media Management', '0'), ('2', 'Contents', '1'), ('3', 'External', '2'), ('4', 'Documentation', '3');

-- ----------------------------
--  Table structure for `grappelli_navigationitem`
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
--  Records of `grappelli_navigationitem`
-- ----------------------------
INSERT INTO `grappelli_navigationitem` VALUES ('1', '1', 'FileBrowser', '/admin/filebrowser/browse/', '1', '0'), ('2', '2', 'Main', '/admin/', '1', '0'), ('3', '2', 'Grappelli', '/admin/grappelli/', '1', '1'), ('4', '3', 'Grappelli GoogleCode', 'http://code.google.com/p/django-grappelli/', '2', '0'), ('5', '4', 'CMS Help', '/grappelli/help/', '1', '0');

-- ----------------------------
--  Table structure for `grappelli_navigationitem_groups`
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
--  Table structure for `grappelli_navigationitem_users`
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
--  Table structure for `registration_registrationprofile`
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
--  Table structure for `system_armor`
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_armor`
-- ----------------------------
INSERT INTO `system_armor` VALUES ('1', '尿布', '8', '150', 'uploads/armor/diper.jpg', '2010-04-19 19:05:42'), ('2', '皮夹克', '32', '1250', 'uploads/armor/leather_jacket.jpg', '2010-04-19 19:06:07'), ('3', '合金盔甲', '120', '15000', 'uploads/armor/shining_body_armor.jpg', '2010-04-19 19:06:29'), ('4', '防弹背心', '400', '2100000', 'uploads/armor/body_armour.jpg', '2010-04-19 19:07:12'), ('5', '纳米战斗衣', '1200', '6200000', 'uploads/armor/nano_fiber_combat_jacket.jpg', '2010-04-19 19:07:48'), ('6', '反恐特勤装', '2000', '10000000', 'uploads/armor/nomex_plated_armour.jpg', '2010-04-19 19:08:18');

-- ----------------------------
--  Table structure for `system_avatar`
-- ----------------------------
DROP TABLE IF EXISTS `system_avatar`;
CREATE TABLE `system_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_avatar`
-- ----------------------------
INSERT INTO `system_avatar` VALUES ('6', 'uploads/avatar/avatar_1.jpg', '2010-04-19 18:40:13'), ('7', 'uploads/avatar/avatar_2.jpg', '2010-04-19 18:41:51'), ('8', 'uploads/avatar/avatar_4.jpg', '2010-04-19 18:42:15'), ('9', 'uploads/avatar/avatar_5.jpg', '2010-04-19 18:42:22'), ('10', 'uploads/avatar/avatar_6.jpg', '2010-04-19 18:42:28'), ('11', 'uploads/avatar/avatar_7.jpg', '2010-04-19 18:42:36'), ('12', 'uploads/avatar/avatar_8.jpg', '2010-04-19 18:42:44'), ('13', 'uploads/avatar/avatar_9.jpg', '2010-04-19 18:42:51'), ('14', 'uploads/avatar/avatar_10.jpg', '2010-04-19 18:42:59'), ('15', 'uploads/avatar/avatar_11.jpg', '2010-04-19 18:43:07'), ('16', 'uploads/avatar/avatar_12.jpg', '2010-04-19 18:43:16'), ('17', 'uploads/avatar/avatar_13.jpg', '2010-04-19 18:43:23'), ('18', 'uploads/avatar/avatar_14.jpg', '2010-04-19 18:43:32'), ('19', 'uploads/avatar/avatar_15.jpg', '2010-04-19 18:43:40'), ('20', 'uploads/avatar/avatar_16.jpg', '2010-04-19 18:43:49'), ('21', 'uploads/avatar/avatar_17.jpg', '2010-04-19 18:43:58'), ('22', 'uploads/avatar/avatar_18.jpg', '2010-04-19 18:44:10'), ('23', 'uploads/avatar/avatar_19.jpg', '2010-04-19 18:44:20'), ('24', 'uploads/avatar/avatar_26.jpg', '2010-04-19 18:44:29'), ('25', 'uploads/avatar/avatar_27.jpg', '2010-04-19 18:44:37'), ('26', 'uploads/avatar/avatar_28.jpg', '2010-04-19 18:44:46'), ('27', 'uploads/avatar/avatar_29.jpg', '2010-04-19 18:44:53'), ('28', 'uploads/avatar/avatar_30.jpg', '2010-04-19 18:45:02'), ('29', 'uploads/avatar/avatar_31.jpg', '2010-04-19 18:45:11'), ('30', 'uploads/avatar/avatar_32.jpg', '2010-04-19 18:45:19'), ('31', 'uploads/avatar/avatar_33.jpg', '2010-04-19 18:45:27'), ('32', 'uploads/avatar/avatar_34.jpg', '2010-04-19 18:45:37'), ('33', 'uploads/avatar/avatar_35.jpg', '2010-04-19 18:45:44'), ('34', 'uploads/avatar/avatar_36.jpg', '2010-04-19 18:45:51'), ('35', 'uploads/avatar/avatar_37.jpg', '2010-04-19 18:45:57'), ('36', 'uploads/avatar/avatar_38.jpg', '2010-04-19 18:46:03'), ('37', 'uploads/avatar/avatar_39.jpg', '2010-04-19 18:46:11'), ('38', 'uploads/avatar/avatar_40.jpg', '2010-04-19 18:46:17'), ('39', 'uploads/avatar/avatar_41.jpg', '2010-04-19 18:46:22'), ('40', 'uploads/avatar/avatar_42.jpg', '2010-04-19 18:46:28'), ('41', 'uploads/avatar/avatar_43.jpg', '2010-04-19 18:46:35'), ('42', 'uploads/avatar/avatar_44.jpg', '2010-04-19 18:46:41'), ('43', 'uploads/avatar/avatar_45.jpg', '2010-04-19 18:46:47'), ('44', 'uploads/avatar/avatar_46.jpg', '2010-04-19 18:46:52'), ('45', 'uploads/avatar/avatar_47.jpg', '2010-04-19 18:46:57'), ('46', 'uploads/avatar/avatar_48.jpg', '2010-04-19 18:47:02'), ('47', 'uploads/avatar/avatar_49.jpg', '2010-04-19 18:47:07'), ('48', 'uploads/avatar/avatar_50.jpg', '2010-04-19 18:47:12'), ('49', 'uploads/avatar/avatar_51.jpg', '2010-04-19 18:47:21'), ('50', 'uploads/avatar/avatar_52.jpg', '2010-04-19 18:47:27'), ('51', 'uploads/avatar/avatar_53.jpg', '2010-04-19 18:47:33');

-- ----------------------------
--  Table structure for `system_building`
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_building`
-- ----------------------------
INSERT INTO `system_building` VALUES ('1', '烟草', '15', '12', '4500', 'uploads/building/weedplant.jpg', '2', '2010-04-19 19:36:20'), ('2', '私酒', '10', '10', '5000', 'uploads/building/moonshiner.jpg', '3', '2010-04-19 19:37:10'), ('3', '大麻种植地', '45', '20', '6500', 'uploads/building/hashplant.jpg', '5', '2010-04-19 19:37:45'), ('4', '酿酒厂', '1100', '150', '40000', 'uploads/building/brewery.jpg', '3', '2010-04-19 19:38:21'), ('5', '药房', '1300', '250', '50000', 'uploads/building/pharmacy.jpg', '1', '2010-04-19 19:38:53'), ('6', '蘑菇种植基地', '800', '320', '60000', 'uploads/building/mushrooms.jpg', '4', '2010-04-19 19:39:26'), ('7', '烟草种植园', '1600', '350', '75000', 'uploads/building/weedfield.jpg', '2', '2010-04-19 19:40:01'), ('8', '吗啡实验室', '60', '900', '80000', 'uploads/building/morphinelab.jpg', '13', '2010-04-19 19:40:31'), ('9', '迷幻药实验室', '590', '500', '100000', 'uploads/building/lsd_lab.jpg', '6', '2010-04-19 19:41:12'), ('10', '摇头丸实验室', '340', '700', '110000', 'uploads/building/ecstacy_lab.jpg', '8', '2010-04-19 19:41:45'), ('11', '罂粟田', '240', '1020', '150000', 'uploads/building/opiumfield.jpg', '10', '2010-04-19 19:42:13'), ('12', '迷奸药实验室', '650', '800', '150000', 'uploads/building/ghblab.jpg', '7', '2010-04-19 19:42:53'), ('13', 'K他命实验室', '165', '650', '180000', 'uploads/building/specialklab.jpg', '12', '2010-04-19 19:43:31'), ('14', '可卡因工厂', '120', '1500', '250000', 'uploads/building/cocaine.jpg', '11', '2010-04-19 19:44:05'), ('15', '安非他命实验室', '160', '2000', '300000', 'uploads/building/amphetamine.jpg', '9', '2010-04-19 19:44:39'), ('16', '海洛英工厂', '60', '5000', '500000', 'uploads/building/heroine_lab.jpg', '14', '2010-04-19 19:45:15');

-- ----------------------------
--  Table structure for `system_business`
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_business`
-- ----------------------------
INSERT INTO `system_business` VALUES ('1', '夜总会', 'uploads/business/nightclub.jpg', '25', '10000', '50', 'bar', '3', '2010-04-19 19:50:16'), ('2', '狂欢派对', 'uploads/business/rave.jpg', '100', '25000', '100', 'bar', '10', '2010-04-19 19:51:00'), ('3', '青楼', 'uploads/business/whorehouse.jpg', '10', '40000', '80', 'club', '5', '2010-04-19 19:51:41'), ('4', '怡红院', 'uploads/business/hooker_manison.jpg', '30', '110000', '230', 'club', '15', '2010-04-19 19:52:14');

-- ----------------------------
--  Table structure for `system_character`
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_character`
-- ----------------------------
INSERT INTO `system_character` VALUES ('1', '领班', 'uploads/character/pimp_head.png', 'uploads/character/pimp_head.png', '4', '4', '6', '6', '2010-04-19 19:55:42'), ('2', '杀手', 'uploads/character/hitman_head.png', 'uploads/character/hitman_head.png', '4', '6', '4', '6', '2010-04-19 19:56:13'), ('3', '商人', 'uploads/character/biz_head.png', 'uploads/character/biz_head.png', '6', '4', '4', '6', '2010-04-19 19:56:46'), ('4', '强盗', 'uploads/character/robber_head.png', 'uploads/character/robber_head.png', '6', '6', '4', '4', '2010-04-19 19:57:21'), ('5', '黑社会', 'uploads/character/gangster_head.png', 'uploads/character/gangster_head.png', '5', '5', '5', '5', '2010-04-19 19:57:43');

-- ----------------------------
--  Table structure for `system_drug`
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_drug`
-- ----------------------------
INSERT INTO `system_drug` VALUES ('1', '止痛药', '6', '0', '99', '1', '2010-04-19 19:11:53', '2010-04-19 19:26:26'), ('2', '香烟', '5', '0', '99', '1', '2010-04-19 19:12:26', '2010-04-19 19:26:40'), ('3', '酒', '7', '0', '50', '1', '2010-04-19 19:13:13', '2010-04-19 19:13:13'), ('4', '迷幻蘑菇', '10', '0', '50', '1', '2010-04-19 19:13:41', '2010-04-19 19:13:41'), ('5', '大麻', '12', '0', '35', '2', '2010-04-19 19:14:32', '2010-04-19 19:27:04'), ('6', '迷幻药', '13', '0', '35', '2', '2010-04-19 19:15:06', '2010-04-19 19:27:32'), ('7', '迷奸药', '10', '0', '25', '3', '2010-04-19 19:15:42', '2010-04-19 19:27:54'), ('8', '摇头丸', '29', '0', '25', '3', '2010-04-19 19:19:41', '2010-04-19 19:28:24'), ('9', '安非他命', '60', '0', '20', '4', '2010-04-19 19:28:48', '2010-04-19 19:28:48'), ('10', '鸦片', '37', '0', '20', '4', '2010-04-19 19:29:06', '2010-04-19 19:29:06'), ('11', '可卡因', '72', '0', '15', '4', '2010-04-19 19:29:22', '2010-04-19 19:29:22'), ('12', 'K他命', '99', '0', '15', '5', '2010-04-19 19:29:37', '2010-04-19 19:29:37'), ('13', '吗啡', '117', '0', '13', '5', '2010-04-19 19:29:58', '2010-04-19 19:29:58'), ('14', '海洛英', '238', '0', '13', '5', '2010-04-19 19:30:17', '2010-04-19 19:30:17');

-- ----------------------------
--  Table structure for `system_event`
-- ----------------------------
DROP TABLE IF EXISTS `system_event`;
CREATE TABLE `system_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` varchar(255) NOT NULL,
  `section` varchar(20) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `change` decimal(4,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `system_guard`
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
--  Table structure for `system_hooker`
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
--  Table structure for `system_province`
-- ----------------------------
DROP TABLE IF EXISTS `system_province`;
CREATE TABLE `system_province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `icon` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `system_weapon`
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
--  Table structure for `user_armor`
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
--  Table structure for `user_building`
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
--  Table structure for `user_business`
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
--  Table structure for `user_drug`
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
--  Table structure for `user_guard`
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
--  Table structure for `user_hooker`
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
--  Table structure for `user_weapon`
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

