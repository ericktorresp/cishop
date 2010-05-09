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

 Date: 05/09/2010 18:06:58 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `permission_id_refs_id_a7792de1` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `auth_permission`
-- ----------------------------
INSERT INTO `auth_permission` VALUES ('1', 'Can add permission', '1', 'add_permission'), ('2', 'Can change permission', '1', 'change_permission'), ('3', 'Can delete permission', '1', 'delete_permission'), ('4', 'Can add group', '2', 'add_group'), ('5', 'Can change group', '2', 'change_group'), ('6', 'Can delete group', '2', 'delete_group'), ('7', 'Can add user', '3', 'add_user'), ('8', 'Can change user', '3', 'change_user'), ('9', 'Can delete user', '3', 'delete_user'), ('10', 'Can add message', '4', 'add_message'), ('11', 'Can change message', '4', 'change_message'), ('12', 'Can delete message', '4', 'delete_message'), ('13', 'Can add content type', '5', 'add_contenttype'), ('14', 'Can change content type', '5', 'change_contenttype'), ('15', 'Can delete content type', '5', 'delete_contenttype'), ('16', 'Can add session', '6', 'add_session'), ('17', 'Can change session', '6', 'change_session'), ('18', 'Can delete session', '6', 'delete_session'), ('19', 'Can add site', '7', 'add_site'), ('20', 'Can change site', '7', 'change_site'), ('21', 'Can delete site', '7', 'delete_site'), ('22', 'Can add log entry', '8', 'add_logentry'), ('23', 'Can change log entry', '8', 'change_logentry'), ('24', 'Can delete log entry', '8', 'delete_logentry'), ('25', 'Can add registration profile', '9', 'add_registrationprofile'), ('26', 'Can change registration profile', '9', 'change_registrationprofile'), ('27', 'Can delete registration profile', '9', 'delete_registrationprofile'), ('28', 'Can add Navigation', '10', 'add_navigation'), ('29', 'Can change Navigation', '10', 'change_navigation'), ('30', 'Can delete Navigation', '10', 'delete_navigation'), ('31', 'Can add Navigation Item', '11', 'add_navigationitem'), ('32', 'Can change Navigation Item', '11', 'change_navigationitem'), ('33', 'Can delete Navigation Item', '11', 'delete_navigationitem'), ('34', 'Can add Bookmark', '12', 'add_bookmark'), ('35', 'Can change Bookmark', '12', 'change_bookmark'), ('36', 'Can delete Bookmark', '12', 'delete_bookmark'), ('37', 'Can add Bookmark Item', '13', 'add_bookmarkitem'), ('38', 'Can change Bookmark Item', '13', 'change_bookmarkitem'), ('39', 'Can delete Bookmark Item', '13', 'delete_bookmarkitem'), ('40', 'Can add Help', '14', 'add_help'), ('41', 'Can change Help', '14', 'change_help'), ('42', 'Can delete Help', '14', 'delete_help'), ('43', 'Can add Help Entry', '15', 'add_helpitem'), ('44', 'Can change Help Entry', '15', 'change_helpitem'), ('45', 'Can delete Help Entry', '15', 'delete_helpitem'), ('46', 'Can add captcha store', '16', 'add_captchastore'), ('47', 'Can change captcha store', '16', 'change_captchastore'), ('48', 'Can delete captcha store', '16', 'delete_captchastore'), ('49', 'Can add avatar', '17', 'add_avatar'), ('50', 'Can change avatar', '17', 'change_avatar'), ('51', 'Can delete avatar', '17', 'delete_avatar'), ('52', 'Can add armor', '18', 'add_armor'), ('53', 'Can change armor', '18', 'change_armor'), ('54', 'Can delete armor', '18', 'delete_armor'), ('55', 'Can add character', '19', 'add_character'), ('56', 'Can change character', '19', 'change_character'), ('57', 'Can delete character', '19', 'delete_character'), ('58', 'Can add drug', '20', 'add_drug'), ('59', 'Can change drug', '20', 'change_drug'), ('60', 'Can delete drug', '20', 'delete_drug'), ('61', 'Can add building', '21', 'add_building'), ('62', 'Can change building', '21', 'change_building'), ('63', 'Can delete building', '21', 'delete_building'), ('64', 'Can add hooker', '22', 'add_hooker'), ('65', 'Can change hooker', '22', 'change_hooker'), ('66', 'Can delete hooker', '22', 'delete_hooker'), ('67', 'Can add user armor', '23', 'add_userarmor'), ('68', 'Can change user armor', '23', 'change_userarmor'), ('69', 'Can delete user armor', '23', 'delete_userarmor'), ('70', 'Can add user building', '24', 'add_userbuilding'), ('71', 'Can change user building', '24', 'change_userbuilding'), ('72', 'Can delete user building', '24', 'delete_userbuilding'), ('73', 'Can add user drug', '25', 'add_userdrug'), ('74', 'Can change user drug', '25', 'change_userdrug'), ('75', 'Can delete user drug', '25', 'delete_userdrug'), ('76', 'Can add user hooker', '26', 'add_userhooker'), ('77', 'Can change user hooker', '26', 'change_userhooker'), ('78', 'Can delete user hooker', '26', 'delete_userhooker'), ('79', 'Can add business', '27', 'add_business'), ('80', 'Can change business', '27', 'change_business'), ('81', 'Can delete business', '27', 'delete_business'), ('82', 'Can add user business', '28', 'add_userbusiness'), ('83', 'Can change user business', '28', 'change_userbusiness'), ('84', 'Can delete user business', '28', 'delete_userbusiness'), ('85', 'Can add guard', '29', 'add_guard'), ('86', 'Can change guard', '29', 'change_guard'), ('87', 'Can delete guard', '29', 'delete_guard'), ('88', 'Can add user guard', '30', 'add_userguard'), ('89', 'Can change user guard', '30', 'change_userguard'), ('90', 'Can delete user guard', '30', 'delete_userguard'), ('91', 'Can add weapon', '31', 'add_weapon'), ('92', 'Can change weapon', '31', 'change_weapon'), ('93', 'Can delete weapon', '31', 'delete_weapon'), ('94', 'Can add user weapon', '32', 'add_userweapon'), ('95', 'Can change user weapon', '32', 'change_userweapon'), ('96', 'Can delete user weapon', '32', 'delete_userweapon'), ('97', 'Can add province', '33', 'add_province'), ('98', 'Can change province', '33', 'change_province'), ('99', 'Can delete province', '33', 'delete_province'), ('100', 'Can add event', '34', 'add_event'), ('101', 'Can change event', '34', 'change_event'), ('102', 'Can delete event', '34', 'delete_event'), ('103', 'Can add benefit', '35', 'add_benefit'), ('104', 'Can change benefit', '35', 'change_benefit'), ('105', 'Can delete benefit', '35', 'delete_benefit'), ('106', 'Can add hospital', '36', 'add_hospital'), ('107', 'Can change hospital', '36', 'change_hospital'), ('108', 'Can delete hospital', '36', 'delete_hospital'), ('109', 'Can add random event', '37', 'add_randomevent'), ('110', 'Can change random event', '37', 'change_randomevent'), ('111', 'Can delete random event', '37', 'delete_randomevent'), ('112', 'Can add random event question', '38', 'add_randomeventquestion'), ('113', 'Can change random event question', '38', 'change_randomeventquestion'), ('114', 'Can delete random event question', '38', 'delete_randomeventquestion'), ('115', 'Can add random event choice', '39', 'add_randomeventchoice'), ('116', 'Can change random event choice', '39', 'change_randomeventchoice'), ('117', 'Can delete random event choice', '39', 'delete_randomeventchoice'), ('118', 'Can add Robberies', '40', 'add_robbery'), ('119', 'Can change Robberies', '40', 'change_robbery'), ('120', 'Can delete Robberies', '40', 'delete_robbery'), ('121', 'Can add Message', '41', 'add_message'), ('122', 'Can change Message', '41', 'change_message'), ('123', 'Can delete Message', '41', 'delete_message'), ('124', 'Can add Gang', '42', 'add_gang'), ('125', 'Can change Gang', '42', 'change_gang'), ('126', 'Can delete Gang', '42', 'delete_gang'), ('136', 'Can add Sabotage', '46', 'add_sabotage'), ('137', 'Can change Sabotage', '46', 'change_sabotage'), ('138', 'Can delete Sabotage', '46', 'delete_sabotage'), ('139', 'Can add Bounty', '47', 'add_bounty'), ('140', 'Can change Bounty', '47', 'change_bounty'), ('141', 'Can delete Bounty', '47', 'delete_bounty'), ('142', 'Can add Bank', '48', 'add_bank'), ('143', 'Can change Bank', '48', 'change_bank'), ('144', 'Can delete Bank', '48', 'delete_bank'), ('148', 'Can add contact', '50', 'add_contact'), ('149', 'Can change contact', '50', 'change_contact'), ('150', 'Can delete contact', '50', 'delete_contact'), ('151', 'Can add friendship', '51', 'add_friendship'), ('152', 'Can change friendship', '51', 'change_friendship'), ('153', 'Can delete friendship', '51', 'delete_friendship'), ('154', 'Can add join invitation', '52', 'add_joininvitation'), ('155', 'Can change join invitation', '52', 'change_joininvitation'), ('156', 'Can delete join invitation', '52', 'delete_joininvitation'), ('157', 'Can add friendship invitation', '53', 'add_friendshipinvitation'), ('158', 'Can change friendship invitation', '53', 'change_friendshipinvitation'), ('159', 'Can delete friendship invitation', '53', 'delete_friendshipinvitation'), ('160', 'Can add friendship invitation history', '54', 'add_friendshipinvitationhistory'), ('161', 'Can change friendship invitation history', '54', 'change_friendshipinvitationhistory'), ('162', 'Can delete friendship invitation history', '54', 'delete_friendshipinvitationhistory'), ('163', 'Can add notice type', '55', 'add_noticetype'), ('164', 'Can change notice type', '55', 'change_noticetype'), ('165', 'Can delete notice type', '55', 'delete_noticetype'), ('166', 'Can add notice setting', '56', 'add_noticesetting'), ('167', 'Can change notice setting', '56', 'change_noticesetting'), ('168', 'Can delete notice setting', '56', 'delete_noticesetting'), ('169', 'Can add notice', '57', 'add_notice'), ('170', 'Can change notice', '57', 'change_notice'), ('171', 'Can delete notice', '57', 'delete_notice'), ('172', 'Can add notice queue batch', '58', 'add_noticequeuebatch'), ('173', 'Can change notice queue batch', '58', 'change_noticequeuebatch'), ('174', 'Can delete notice queue batch', '58', 'delete_noticequeuebatch'), ('175', 'Can add observed item', '59', 'add_observeditem'), ('176', 'Can change observed item', '59', 'change_observeditem'), ('177', 'Can delete observed item', '59', 'delete_observeditem'), ('178', 'Can add Relationship status', '60', 'add_relationshipstatus'), ('179', 'Can change Relationship status', '60', 'change_relationshipstatus'), ('180', 'Can delete Relationship status', '60', 'delete_relationshipstatus'), ('181', 'Can add Relationship', '61', 'add_relationship'), ('182', 'Can change Relationship', '61', 'change_relationship'), ('183', 'Can delete Relationship', '61', 'delete_relationship'), ('187', 'Can add chat', '63', 'add_chat'), ('188', 'Can change chat', '63', 'change_chat'), ('189', 'Can delete chat', '63', 'delete_chat'), ('199', 'Can add invite', '67', 'add_ganginvite'), ('200', 'Can change invite', '67', 'change_ganginvite'), ('201', 'Can delete invite', '67', 'delete_ganginvite'), ('202', 'Can add member', '68', 'add_gangmember'), ('203', 'Can change member', '68', 'change_gangmember'), ('204', 'Can delete member', '68', 'delete_gangmember'), ('205', 'Can add news', '69', 'add_gangnews'), ('206', 'Can change news', '69', 'change_gangnews'), ('207', 'Can delete news', '69', 'delete_gangnews'), ('208', 'Can add robbery', '70', 'add_gangrobbery'), ('209', 'Can change robbery', '70', 'change_gangrobbery'), ('210', 'Can delete robbery', '70', 'delete_gangrobbery'), ('211', 'Can add assault', '71', 'add_gangassault'), ('212', 'Can change assault', '71', 'change_gangassault'), ('213', 'Can delete assault', '71', 'delete_gangassault'), ('214', 'Can add user business drugs', '72', 'add_userbusinessdrug'), ('215', 'Can change user business drugs', '72', 'change_userbusinessdrug'), ('216', 'Can delete user business drugs', '72', 'delete_userbusinessdrug'), ('217', 'Can add guestbook', '73', 'add_guestbook'), ('218', 'Can change guestbook', '73', 'change_guestbook'), ('219', 'Can delete guestbook', '73', 'delete_guestbook'), ('220', 'Can add prison', '74', 'add_prison'), ('221', 'Can change prison', '74', 'change_prison'), ('222', 'Can delete prison', '74', 'delete_prison'), ('223', 'Can add rip', '75', 'add_rip'), ('224', 'Can change rip', '75', 'change_rip'), ('225', 'Can delete rip', '75', 'delete_rip'), ('226', 'Can add profile', '76', 'add_userprofile'), ('227', 'Can change profile', '76', 'change_userprofile'), ('228', 'Can delete profile', '76', 'delete_userprofile'), ('229', 'Can add game data', '77', 'add_userdata'), ('230', 'Can change game data', '77', 'change_userdata'), ('231', 'Can delete game data', '77', 'delete_userdata');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `auth_user`
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'c-mtv@163.com', 'sha1$df48b$97ce6e8393b8ff135c3e794d6e847d6962ea6da8', '1', '1', '1', '2010-05-09 16:28:31', '2010-04-18 20:07:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=483 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_admin_log`
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-04-18 20:37:51', '1', '14', '1', '帮助', '1', ''), ('2', '2010-04-18 20:38:27', '1', '15', '1', '请求人肉支援', '1', ''), ('3', '2010-04-18 20:38:42', '1', '15', '1', '请求人肉支援', '2', '已修改 body 。'), ('4', '2010-04-19 10:26:19', '1', '17', '3', 'Avatar object', '1', ''), ('5', '2010-04-19 10:34:19', '1', '17', '1', 'Avatar object', '1', ''), ('6', '2010-04-19 10:39:56', '1', '17', '1', 'e:/AppServ/pys/crims/assets/avatar/Winter.jpg', '3', ''), ('7', '2010-04-19 10:40:04', '1', '17', '2', 'avatar/Winter.jpg', '1', ''), ('8', '2010-04-19 10:52:32', '1', '17', '2', 'Avatar object', '2', '已修改 filename 。'), ('9', '2010-04-19 10:57:07', '1', '17', '3', '2010-04-19 10:57:07.109000', '1', ''), ('10', '2010-04-19 10:57:24', '1', '17', '4', '2010-04-19 10:57:24.718000', '1', ''), ('11', '2010-04-19 10:57:36', '1', '17', '5', '2010-04-19 10:57:36.250000', '1', ''), ('12', '2010-04-19 16:13:30', '1', '33', '1', '帝都', '1', ''), ('13', '2010-04-19 16:13:54', '1', '33', '1', '帝都', '3', ''), ('14', '2010-04-19 18:33:51', '1', '17', '5', 'uploads/avatar/blue_hills_thumbnail.jpg', '3', ''), ('15', '2010-04-19 18:33:51', '1', '17', '4', 'uploads/avatar/sunset_thumbnail.jpg', '3', ''), ('16', '2010-04-19 18:33:51', '1', '17', '3', 'uploads/avatar/water_lilies_thumbnail.jpg', '3', ''), ('17', '2010-04-19 18:33:51', '1', '17', '2', 'uploads/avatar/winter_thumbnail.jpg', '3', ''), ('18', '2010-04-19 18:40:13', '1', '17', '6', 'uploads/avatar/avatar_1.jpg', '1', ''), ('19', '2010-04-19 18:41:51', '1', '17', '7', 'uploads/avatar/avatar_2.jpg', '1', ''), ('20', '2010-04-19 18:42:15', '1', '17', '8', 'uploads/avatar/avatar_4.jpg', '1', ''), ('21', '2010-04-19 18:42:22', '1', '17', '9', 'uploads/avatar/avatar_5.jpg', '1', ''), ('22', '2010-04-19 18:42:28', '1', '17', '10', 'uploads/avatar/avatar_6.jpg', '1', ''), ('23', '2010-04-19 18:42:36', '1', '17', '11', 'uploads/avatar/avatar_7.jpg', '1', ''), ('24', '2010-04-19 18:42:44', '1', '17', '12', 'uploads/avatar/avatar_8.jpg', '1', ''), ('25', '2010-04-19 18:42:51', '1', '17', '13', 'uploads/avatar/avatar_9.jpg', '1', ''), ('26', '2010-04-19 18:42:59', '1', '17', '14', 'uploads/avatar/avatar_10.jpg', '1', ''), ('27', '2010-04-19 18:43:07', '1', '17', '15', 'uploads/avatar/avatar_11.jpg', '1', ''), ('28', '2010-04-19 18:43:16', '1', '17', '16', 'uploads/avatar/avatar_12.jpg', '1', ''), ('29', '2010-04-19 18:43:23', '1', '17', '17', 'uploads/avatar/avatar_13.jpg', '1', ''), ('30', '2010-04-19 18:43:32', '1', '17', '18', 'uploads/avatar/avatar_14.jpg', '1', ''), ('31', '2010-04-19 18:43:40', '1', '17', '19', 'uploads/avatar/avatar_15.jpg', '1', ''), ('32', '2010-04-19 18:43:49', '1', '17', '20', 'uploads/avatar/avatar_16.jpg', '1', ''), ('33', '2010-04-19 18:43:58', '1', '17', '21', 'uploads/avatar/avatar_17.jpg', '1', ''), ('34', '2010-04-19 18:44:10', '1', '17', '22', 'uploads/avatar/avatar_18.jpg', '1', ''), ('35', '2010-04-19 18:44:20', '1', '17', '23', 'uploads/avatar/avatar_19.jpg', '1', ''), ('36', '2010-04-19 18:44:29', '1', '17', '24', 'uploads/avatar/avatar_26.jpg', '1', ''), ('37', '2010-04-19 18:44:37', '1', '17', '25', 'uploads/avatar/avatar_27.jpg', '1', ''), ('38', '2010-04-19 18:44:46', '1', '17', '26', 'uploads/avatar/avatar_28.jpg', '1', ''), ('39', '2010-04-19 18:44:53', '1', '17', '27', 'uploads/avatar/avatar_29.jpg', '1', ''), ('40', '2010-04-19 18:45:02', '1', '17', '28', 'uploads/avatar/avatar_30.jpg', '1', ''), ('41', '2010-04-19 18:45:11', '1', '17', '29', 'uploads/avatar/avatar_31.jpg', '1', ''), ('42', '2010-04-19 18:45:19', '1', '17', '30', 'uploads/avatar/avatar_32.jpg', '1', ''), ('43', '2010-04-19 18:45:27', '1', '17', '31', 'uploads/avatar/avatar_33.jpg', '1', ''), ('44', '2010-04-19 18:45:37', '1', '17', '32', 'uploads/avatar/avatar_34.jpg', '1', ''), ('45', '2010-04-19 18:45:44', '1', '17', '33', 'uploads/avatar/avatar_35.jpg', '1', ''), ('46', '2010-04-19 18:45:51', '1', '17', '34', 'uploads/avatar/avatar_36.jpg', '1', ''), ('47', '2010-04-19 18:45:57', '1', '17', '35', 'uploads/avatar/avatar_37.jpg', '1', ''), ('48', '2010-04-19 18:46:03', '1', '17', '36', 'uploads/avatar/avatar_38.jpg', '1', ''), ('49', '2010-04-19 18:46:11', '1', '17', '37', 'uploads/avatar/avatar_39.jpg', '1', ''), ('50', '2010-04-19 18:46:17', '1', '17', '38', 'uploads/avatar/avatar_40.jpg', '1', ''), ('51', '2010-04-19 18:46:22', '1', '17', '39', 'uploads/avatar/avatar_41.jpg', '1', ''), ('52', '2010-04-19 18:46:28', '1', '17', '40', 'uploads/avatar/avatar_42.jpg', '1', ''), ('53', '2010-04-19 18:46:35', '1', '17', '41', 'uploads/avatar/avatar_43.jpg', '1', ''), ('54', '2010-04-19 18:46:41', '1', '17', '42', 'uploads/avatar/avatar_44.jpg', '1', ''), ('55', '2010-04-19 18:46:47', '1', '17', '43', 'uploads/avatar/avatar_45.jpg', '1', ''), ('56', '2010-04-19 18:46:52', '1', '17', '44', 'uploads/avatar/avatar_46.jpg', '1', ''), ('57', '2010-04-19 18:46:57', '1', '17', '45', 'uploads/avatar/avatar_47.jpg', '1', ''), ('58', '2010-04-19 18:47:02', '1', '17', '46', 'uploads/avatar/avatar_48.jpg', '1', ''), ('59', '2010-04-19 18:47:07', '1', '17', '47', 'uploads/avatar/avatar_49.jpg', '1', ''), ('60', '2010-04-19 18:47:12', '1', '17', '48', 'uploads/avatar/avatar_50.jpg', '1', ''), ('61', '2010-04-19 18:47:21', '1', '17', '49', 'uploads/avatar/avatar_51.jpg', '1', ''), ('62', '2010-04-19 18:47:27', '1', '17', '50', 'uploads/avatar/avatar_52.jpg', '1', ''), ('63', '2010-04-19 18:47:33', '1', '17', '51', 'uploads/avatar/avatar_53.jpg', '1', ''), ('64', '2010-04-19 19:05:42', '1', '18', '1', 'Disper', '1', ''), ('65', '2010-04-19 19:06:07', '1', '18', '2', '皮夹克', '1', ''), ('66', '2010-04-19 19:06:29', '1', '18', '3', '合金盔甲', '1', ''), ('67', '2010-04-19 19:07:12', '1', '18', '4', '防弹背心', '1', ''), ('68', '2010-04-19 19:07:48', '1', '18', '5', '纳米战斗衣', '1', ''), ('69', '2010-04-19 19:08:18', '1', '18', '6', '反恐特勤装', '1', ''), ('70', '2010-04-19 19:08:27', '1', '18', '1', '尿布', '2', '已修改 title 。'), ('71', '2010-04-19 19:11:53', '1', '20', '1', '香烟', '1', ''), ('72', '2010-04-19 19:12:26', '1', '20', '2', '止痛药', '1', ''), ('73', '2010-04-19 19:13:13', '1', '20', '3', '酒', '1', ''), ('74', '2010-04-19 19:13:41', '1', '20', '4', '迷幻蘑菇', '1', ''), ('75', '2010-04-19 19:14:32', '1', '20', '5', '迷奸药', '1', ''), ('76', '2010-04-19 19:15:06', '1', '20', '6', '大麻', '1', ''), ('77', '2010-04-19 19:15:42', '1', '20', '7', '迷幻药', '1', ''), ('78', '2010-04-19 19:19:41', '1', '20', '8', '迷奸药', '1', ''), ('79', '2010-04-19 19:26:26', '1', '20', '1', '止痛药', '2', '已修改 title 。'), ('80', '2010-04-19 19:26:40', '1', '20', '2', '香烟', '2', '已修改 title 。'), ('81', '2010-04-19 19:27:04', '1', '20', '5', '大麻', '2', '已修改 title 。'), ('82', '2010-04-19 19:27:32', '1', '20', '6', '迷幻药', '2', '已修改 title 。'), ('83', '2010-04-19 19:27:54', '1', '20', '7', '迷奸药', '2', '已修改 title 。'), ('84', '2010-04-19 19:28:24', '1', '20', '8', '摇头丸', '2', '已修改 title 和 price 。'), ('85', '2010-04-19 19:28:48', '1', '20', '9', '安非他命', '1', ''), ('86', '2010-04-19 19:29:06', '1', '20', '10', '鸦片', '1', ''), ('87', '2010-04-19 19:29:22', '1', '20', '11', '可卡因', '1', ''), ('88', '2010-04-19 19:29:37', '1', '20', '12', 'K他命', '1', ''), ('89', '2010-04-19 19:29:58', '1', '20', '13', '吗啡', '1', ''), ('90', '2010-04-19 19:30:17', '1', '20', '14', '海洛英', '1', ''), ('91', '2010-04-19 19:36:20', '1', '21', '1', '烟草', '1', ''), ('92', '2010-04-19 19:37:10', '1', '21', '2', '私酒', '1', ''), ('93', '2010-04-19 19:37:45', '1', '21', '3', '大麻种植地', '1', ''), ('94', '2010-04-19 19:38:21', '1', '21', '4', '酿酒厂', '1', ''), ('95', '2010-04-19 19:38:53', '1', '21', '5', '药房', '1', ''), ('96', '2010-04-19 19:39:26', '1', '21', '6', '蘑菇种植基地', '1', ''), ('97', '2010-04-19 19:40:01', '1', '21', '7', '烟草种植园', '1', ''), ('98', '2010-04-19 19:40:31', '1', '21', '8', '吗啡实验室', '1', ''), ('99', '2010-04-19 19:41:12', '1', '21', '9', '迷幻药实验室', '1', ''), ('100', '2010-04-19 19:41:45', '1', '21', '10', '摇头丸实验室', '1', ''), ('101', '2010-04-19 19:42:13', '1', '21', '11', '罂粟田', '1', ''), ('102', '2010-04-19 19:42:53', '1', '21', '12', '迷奸药实验室', '1', ''), ('103', '2010-04-19 19:43:31', '1', '21', '13', 'K他命实验室', '1', ''), ('104', '2010-04-19 19:44:05', '1', '21', '14', '可卡因工厂', '1', ''), ('105', '2010-04-19 19:44:39', '1', '21', '15', '安非他命实验室', '1', ''), ('106', '2010-04-19 19:45:15', '1', '21', '16', '海洛英工厂', '1', ''), ('107', '2010-04-19 19:50:16', '1', '27', '1', '夜总会', '1', ''), ('108', '2010-04-19 19:51:00', '1', '27', '2', '狂欢派对', '1', ''), ('109', '2010-04-19 19:51:41', '1', '27', '3', '青楼', '1', ''), ('110', '2010-04-19 19:52:14', '1', '27', '4', '怡红院', '1', ''), ('111', '2010-04-19 19:55:42', '1', '19', '1', '领班', '1', ''), ('112', '2010-04-19 19:56:13', '1', '19', '2', '杀手', '1', ''), ('113', '2010-04-19 19:56:46', '1', '19', '3', '商人', '1', ''), ('114', '2010-04-19 19:57:21', '1', '19', '4', '强盗', '1', ''), ('115', '2010-04-19 19:57:43', '1', '19', '5', '黑社会', '1', ''), ('116', '2010-04-20 09:34:22', '1', '12', '1', 'root', '2', '没有字段被修改。'), ('117', '2010-04-21 10:36:03', '1', '35', '1', '妓女赚钱加速', '1', ''), ('118', '2010-04-21 10:36:33', '1', '35', '2', '武器增强器', '1', ''), ('119', '2010-04-21 10:37:06', '1', '35', '3', '产量加速器', '1', ''), ('120', '2010-04-21 10:37:19', '1', '35', '4', '避孕套', '1', ''), ('121', '2010-04-21 10:45:33', '1', '29', '1', '恶狗', '1', ''), ('122', '2010-04-21 10:46:07', '1', '29', '2', '杀手', '1', ''), ('123', '2010-04-21 10:46:28', '1', '29', '3', '疯狂守卫', '1', ''), ('124', '2010-04-21 10:46:56', '1', '29', '4', '俄罗斯前特种兵', '1', ''), ('125', '2010-04-21 10:47:19', '1', '29', '5', '专业保镖', '1', ''), ('126', '2010-04-21 10:47:38', '1', '29', '6', '终极保镖', '1', ''), ('127', '2010-04-21 10:52:57', '1', '22', '1', 'Dolly', '1', ''), ('128', '2010-04-21 10:54:13', '1', '22', '2', 'Heinrich', '1', ''), ('129', '2010-04-21 10:56:35', '1', '22', '3', 'Britney', '1', ''), ('130', '2010-04-21 10:57:27', '1', '22', '4', 'Mount Tse Tung', '1', ''), ('131', '2010-04-21 10:58:14', '1', '22', '5', 'Marilyn', '1', ''), ('132', '2010-04-21 10:59:17', '1', '22', '6', 'Candy', '1', ''), ('133', '2010-04-21 11:00:20', '1', '22', '7', 'Bell', '1', ''), ('134', '2010-04-21 11:01:04', '1', '22', '8', 'Patricia', '1', ''), ('135', '2010-04-21 11:03:16', '1', '22', '9', 'Claire', '1', ''), ('136', '2010-04-21 11:03:55', '1', '22', '10', 'Crystal', '1', ''), ('137', '2010-04-21 11:04:54', '1', '22', '11', 'Valerie', '1', ''), ('138', '2010-04-21 11:06:38', '1', '22', '12', 'Chessy', '1', ''), ('139', '2010-04-21 11:07:33', '1', '22', '13', 'Denim Daisy', '1', ''), ('140', '2010-04-21 11:08:24', '1', '22', '14', 'Head Nurse', '1', ''), ('141', '2010-04-21 11:09:09', '1', '22', '15', 'Cindy', '1', ''), ('142', '2010-04-21 11:09:53', '1', '22', '16', 'George', '1', ''), ('143', '2010-04-21 11:10:31', '1', '22', '17', 'Gothic Goddess', '1', ''), ('144', '2010-04-21 11:11:10', '1', '22', '18', 'Pearl', '1', ''), ('145', '2010-04-21 11:12:15', '1', '22', '19', 'Miss FBI', '1', ''), ('146', '2010-04-21 11:13:31', '1', '22', '20', 'French Maid Fifi', '1', ''), ('147', '2010-04-21 11:14:12', '1', '22', '21', 'Darling Devil', '1', ''), ('148', '2010-04-21 11:14:51', '1', '22', '22', 'Sergeant Sexy', '1', ''), ('149', '2010-04-21 11:15:22', '1', '22', '23', 'Jessica', '1', ''), ('150', '2010-04-21 11:15:53', '1', '22', '24', 'Leonard', '1', ''), ('151', '2010-04-21 11:16:25', '1', '22', '25', 'Bunnie', '1', ''), ('152', '2010-04-21 11:17:22', '1', '22', '26', 'Mrs. Robinson', '1', ''), ('153', '2010-04-21 11:18:21', '1', '22', '27', 'Mr Love', '1', ''), ('154', '2010-04-21 11:19:25', '1', '22', '28', 'Lill &  Jill', '1', ''), ('155', '2010-04-21 11:20:05', '1', '22', '29', 'The Twins', '1', ''), ('156', '2010-04-21 11:20:43', '1', '22', '30', 'Slim Susy', '1', ''), ('157', '2010-04-21 11:21:15', '1', '22', '31', 'SM Babe', '1', ''), ('158', '2010-04-21 11:22:23', '1', '22', '32', 'Miss Blonde', '1', ''), ('159', '2010-04-21 11:23:13', '1', '22', '33', 'Bobbi', '1', ''), ('160', '2010-04-21 11:24:31', '1', '22', '34', 'Woman of Wonder', '1', ''), ('161', '2010-04-21 11:25:23', '1', '22', '35', 'Rhinogirl', '1', ''), ('162', '2010-04-21 11:31:34', '1', '31', '1', '球棒', '1', ''), ('163', '2010-04-21 11:34:37', '1', '31', '1', '球棒', '2', '已修改 type 。'), ('164', '2010-04-21 11:35:34', '1', '31', '2', '匕首', '1', ''), ('165', '2010-04-21 11:36:29', '1', '31', '3', '剑', '1', ''), ('166', '2010-04-21 11:37:20', '1', '31', '4', '链锯', '1', ''), ('167', '2010-04-21 11:38:16', '1', '31', '5', '格洛克', '1', ''), ('168', '2010-04-21 11:39:13', '1', '31', '6', '散弹枪', '1', ''), ('169', '2010-04-21 11:40:44', '1', '31', '7', 'MP5冲锋枪', '1', ''), ('170', '2010-04-21 11:41:45', '1', '31', '8', 'AK 47', '1', ''), ('171', '2010-04-21 11:42:30', '1', '31', '9', '乌兹冲锋枪', '1', ''), ('172', '2010-04-21 11:43:30', '1', '31', '10', 'M4A1', '1', ''), ('173', '2010-04-21 11:44:48', '1', '31', '11', '沙漠之鹰', '1', ''), ('174', '2010-04-21 11:45:50', '1', '31', '12', '重型狙击枪', '1', ''), ('175', '2010-04-21 11:47:14', '1', '31', '13', '激光枪', '1', ''), ('176', '2010-04-21 11:48:07', '1', '31', '14', '重机枪', '1', ''), ('177', '2010-04-21 11:49:27', '1', '31', '15', '火箭筒', '1', ''), ('178', '2010-04-21 11:50:31', '1', '31', '16', '盖利步枪', '1', ''), ('179', '2010-04-21 11:51:24', '1', '31', '17', '多管连发手枪', '1', ''), ('180', '2010-04-21 11:52:20', '1', '31', '18', '地狱火神炮', '1', ''), ('181', '2010-04-21 13:44:58', '1', '34', '1', 'POLICE RAID', '1', ''), ('182', '2010-04-21 13:46:13', '1', '34', '2', 'DRUG PRICES DOWN', '1', ''), ('183', '2010-04-21 13:53:25', '1', '34', '3', 'WARREN BUFFET ON QUICK VISIT!', '1', ''), ('184', '2010-04-21 13:56:03', '1', '34', '4', 'DRUG PRICES UP', '1', ''), ('185', '2010-04-21 13:57:32', '1', '34', '5', 'CARNIVAL!', '1', ''), ('186', '2010-04-21 14:00:33', '1', '34', '6', 'OIL TYCOON OF DUBAI IS IN TOWN!', '1', ''), ('187', '2010-04-21 14:01:39', '1', '34', '7', 'BILL GATES IN TOWN!', '1', ''), ('188', '2010-04-21 14:08:59', '1', '34', '2', 'DRUG PRICES DOWN', '2', '已修改 drug 。'), ('189', '2010-04-21 14:09:19', '1', '34', '4', 'DRUG PRICES UP', '2', '已修改 drug 。'), ('190', '2010-04-21 14:09:53', '1', '34', '7', 'BILL GATES IN TOWN!', '2', '没有字段被修改。'), ('191', '2010-04-21 14:10:32', '1', '34', '8', 'HARBOUR ACTIVITY', '1', ''), ('192', '2010-04-21 14:15:25', '1', '33', '1', '北京', '1', ''), ('193', '2010-04-21 14:15:33', '1', '33', '2', '天津', '1', ''), ('194', '2010-04-21 14:15:50', '1', '33', '3', '河北', '1', ''), ('195', '2010-04-21 14:15:58', '1', '33', '4', '山西', '1', ''), ('196', '2010-04-21 14:16:08', '1', '33', '5', '内蒙古自治区', '1', ''), ('197', '2010-04-21 14:16:17', '1', '33', '6', '辽宁', '1', ''), ('198', '2010-04-21 14:16:26', '1', '33', '7', '吉林', '1', ''), ('199', '2010-04-21 14:16:34', '1', '33', '8', '黑龙江', '1', ''), ('200', '2010-04-21 14:16:43', '1', '33', '9', '上海', '1', ''), ('201', '2010-04-21 14:16:52', '1', '33', '10', '江苏', '1', ''), ('202', '2010-04-21 14:17:01', '1', '33', '11', '浙江', '1', ''), ('203', '2010-04-21 14:17:08', '1', '33', '12', '安徽', '1', ''), ('204', '2010-04-21 14:17:17', '1', '33', '13', '福建', '1', ''), ('205', '2010-04-21 14:17:24', '1', '33', '14', '江西', '1', ''), ('206', '2010-04-21 14:17:32', '1', '33', '15', '山东', '1', ''), ('207', '2010-04-21 14:17:42', '1', '33', '16', '河南', '1', ''), ('208', '2010-04-21 14:17:49', '1', '33', '17', '湖北', '1', ''), ('209', '2010-04-21 14:17:57', '1', '33', '18', '湖南', '1', ''), ('210', '2010-04-21 14:18:05', '1', '33', '19', '广东', '1', ''), ('211', '2010-04-21 14:18:13', '1', '33', '20', '广西', '1', ''), ('212', '2010-04-21 14:18:23', '1', '33', '21', '海南', '1', ''), ('213', '2010-04-21 14:18:31', '1', '33', '22', '重庆', '1', ''), ('214', '2010-04-21 14:18:39', '1', '33', '23', '四川', '1', ''), ('215', '2010-04-21 14:18:47', '1', '33', '24', '贵州', '1', ''), ('216', '2010-04-21 14:18:55', '1', '33', '25', '云南', '1', ''), ('217', '2010-04-21 14:19:06', '1', '33', '26', '西藏', '1', ''), ('218', '2010-04-21 14:19:14', '1', '33', '27', '陕西', '1', ''), ('219', '2010-04-21 14:19:23', '1', '33', '28', '甘肃', '1', ''), ('220', '2010-04-21 14:19:33', '1', '33', '29', '青海', '1', ''), ('221', '2010-04-21 14:19:42', '1', '33', '30', '宁夏', '1', ''), ('222', '2010-04-21 14:19:55', '1', '33', '31', '新疆', '1', ''), ('223', '2010-04-21 14:20:03', '1', '33', '32', '香港', '1', ''), ('224', '2010-04-21 14:20:11', '1', '33', '33', '澳门', '1', ''), ('225', '2010-04-21 14:20:19', '1', '33', '34', '台湾', '1', ''), ('226', '2010-04-21 14:20:27', '1', '33', '35', '其它', '1', ''), ('227', '2010-04-21 14:48:55', '1', '36', '1', '脑激素', '1', ''), ('228', '2010-04-21 14:49:11', '1', '36', '2', '性激素', '1', ''), ('229', '2010-04-21 14:49:26', '1', '36', '3', '肌肉生长素', '1', ''), ('230', '2010-04-21 14:49:38', '1', '36', '4', '类固醇', '1', ''), ('231', '2010-04-21 14:49:56', '1', '36', '5', '美沙酮', '1', ''), ('232', '2010-04-21 18:51:59', '1', '37', '1', '一个警察不怀好心的档着你的路', '1', ''), ('233', '2010-04-21 18:52:19', '1', '37', '2', '一个醉汉高举酒瓶档着你的路。', '1', ''), ('234', '2010-04-21 18:52:34', '1', '37', '3', '一只毒虫拿着针头挡住你的路', '1', ''), ('235', '2010-04-21 18:52:53', '1', '37', '4', '一个老伯步履蹒跚拦下你。', '1', ''), ('236', '2010-04-21 18:54:07', '1', '38', '1', '我想我认得样子，你说呢？', '1', ''), ('237', '2010-04-21 18:54:17', '1', '38', '2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '1', ''), ('238', '2010-04-21 18:54:28', '1', '38', '3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '1', ''), ('239', '2010-04-21 18:54:37', '1', '38', '4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '1', ''), ('240', '2010-04-21 18:54:46', '1', '38', '5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '1', ''), ('241', '2010-04-21 18:54:57', '1', '38', '6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '1', ''), ('242', '2010-04-21 18:58:32', '1', '38', '1', '我想我认得样子，你说呢？', '2', '已添加 random event choice \"非洲\". 已添加 random event choice \"欧洲\". 已添加 random event choice \"大洋洲\". 已添加 random event choice \"南美洲\". 已添加 random event choice \"亚洲\".'), ('243', '2010-04-21 19:02:21', '1', '38', '2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '2', '已添加 random event choice \"3:10\". 已添加 random event choice \"5:00 \". 已添加 random event choice \"7:53\". 已添加 random event choice \"10:30\". 已添加 random event choice \"11:55\".'), ('244', '2010-04-21 19:04:09', '1', '38', '3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '2', '已添加 random event choice \"猴子\". 已添加 random event choice \"鸟\". 已添加 random event choice \"狗\". 已添加 random event choice \"老虎\". 已添加 random event choice \"耗子\".'), ('245', '2010-04-21 19:06:50', '1', '38', '4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '2', '已添加 random event choice \"沙漠\". 已添加 random event choice \"海洋\". 已添加 random event choice \"森林\". 已添加 random event choice \"城市\". 已添加 random event choice \"北极\".'), ('246', '2010-04-21 19:08:25', '1', '38', '5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '2', '已添加 random event choice \"渡船\". 已添加 random event choice \"火车\". 已添加 random event choice \"公共汽车\". 已添加 random event choice \"飞机\".'), ('247', '2010-04-21 19:10:14', '1', '38', '6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '2', '已添加 random event choice \"尺\". 已添加 random event choice \"笔\". 已添加 random event choice \"计算器\". 已添加 random event choice \"橡皮\". 已添加 random event choice \"笔记本计算机\".'), ('248', '2010-04-22 14:39:34', '1', '10', '1', '媒体管理', '2', '已修改 title 。 已变更 title for 导航条目 \"文件浏览\".'), ('249', '2010-04-22 14:44:42', '1', '12', '1', 'root', '2', '已添加 书签条目 \"站点管理\".'), ('250', '2010-04-22 14:44:54', '1', '12', '1', 'root', '2', '已删除 书签条目 \"站点管理\".'), ('251', '2010-04-22 14:53:37', '1', '10', '2', '内容', '2', '已修改 title 。 已变更 title for 导航条目 \"主页\".'), ('252', '2010-04-22 14:53:49', '1', '10', '3', '外部', '2', '已修改 title 。'), ('253', '2010-04-22 14:54:10', '1', '10', '4', '文档', '2', '已修改 title 。 已变更 title for 导航条目 \"帮助\".'), ('254', '2010-04-22 14:55:13', '1', '38', '1', '我想我认得样子，你说呢？', '2', '没有字段被修改。'), ('255', '2010-04-22 14:56:11', '1', '27', '1', '夜总会', '2', '没有字段被修改。'), ('256', '2010-04-22 15:04:43', '1', '27', '1', '夜总会', '2', '没有字段被修改。'), ('257', '2010-04-22 15:05:00', '1', '38', '1', '我想我认得样子，你说呢？', '2', '没有字段被修改。'), ('258', '2010-04-22 15:10:36', '1', '17', '51', 'uploads/avatar/avatar_53.jpg', '2', '没有字段被修改。'), ('259', '2010-04-22 15:14:16', '1', '37', '1', '一个警察不怀好心的档着你的路', '2', '没有字段被修改。'), ('260', '2010-04-28 19:06:21', '1', '40', '1', '偷窃', '1', ''), ('261', '2010-04-28 19:09:36', '1', '40', '2', '抢老妇人', '1', ''), ('262', '2010-04-28 19:10:33', '1', '40', '3', '偷汽車', '1', ''), ('263', '2010-04-28 19:11:17', '1', '40', '4', '抢出租车', '1', ''), ('264', '2010-04-28 19:12:11', '1', '40', '5', '提款机', '1', ''), ('265', '2010-04-28 19:13:02', '1', '40', '6', '民宅', '1', ''), ('266', '2010-04-28 19:13:45', '1', '40', '7', '加油站', '1', ''), ('267', '2010-04-28 19:14:25', '1', '40', '8', '戏院', '1', ''), ('268', '2010-04-28 19:15:15', '1', '40', '9', '杂货店', '1', ''), ('269', '2010-04-28 19:15:53', '1', '40', '10', '24小时便利商店', '1', ''), ('270', '2010-04-28 19:16:41', '1', '40', '11', '绑架', '1', ''), ('271', '2010-04-28 19:17:24', '1', '40', '12', '珠宝店', '1', ''), ('272', '2010-04-28 19:18:07', '1', '40', '13', '保险箱', '1', ''), ('273', '2010-04-28 19:18:51', '1', '40', '14', '小银行', '1', ''), ('274', '2010-04-28 19:19:29', '1', '40', '15', '帮派小头目', '1', ''), ('275', '2010-04-28 19:20:12', '1', '40', '16', '汽车沙龙', '1', ''), ('276', '2010-04-28 19:21:00', '1', '40', '17', 'PayPal', '1', ''), ('277', '2010-04-28 19:21:41', '1', '40', '18', '地痞', '1', ''), ('278', '2010-04-28 19:22:21', '1', '40', '19', '小药头', '1', ''), ('279', '2010-04-28 19:22:50', '1', '40', '20', '赌场', '1', ''), ('280', '2010-04-28 19:23:18', '1', '40', '21', '狂欢会', '1', ''), ('281', '2010-04-28 19:23:56', '1', '40', '22', '超级市场', '1', ''), ('282', '2010-04-28 19:24:41', '1', '40', '23', '博物馆', '1', ''), ('283', '2010-04-28 19:25:18', '1', '40', '24', '俄国药王', '1', ''), ('284', '2010-04-28 19:26:13', '1', '40', '25', '外币', '1', ''), ('285', '2010-04-28 19:27:04', '1', '40', '26', '银行', '1', ''), ('286', '2010-04-28 19:27:44', '1', '40', '27', '运钞车', '1', ''), ('287', '2010-04-28 19:28:43', '1', '40', '28', '中央储备银行', '1', ''), ('288', '2010-04-28 19:29:31', '1', '40', '29', '杜月笙', '1', ''), ('289', '2010-04-28 19:30:11', '1', '40', '30', '操纵股市', '1', ''), ('290', '2010-04-28 19:30:54', '1', '40', '31', '黄金荣', '1', ''), ('291', '2010-04-28 19:31:37', '1', '40', '32', '他妈的烂地方', '1', ''), ('292', '2010-04-28 19:32:20', '1', '40', '33', '黄埔军校', '1', ''), ('293', '2010-04-28 19:42:05', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。'), ('294', '2010-04-29 09:24:59', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。'), ('295', '2010-04-29 10:36:28', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('296', '2010-04-29 10:36:44', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。'), ('297', '2010-04-29 10:47:57', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('298', '2010-04-29 11:16:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('299', '2010-04-29 11:18:02', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('300', '2010-04-29 11:22:36', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('301', '2010-04-29 11:27:32', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('302', '2010-04-29 11:27:58', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。'), ('303', '2010-04-29 11:36:15', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('304', '2010-04-29 11:38:06', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('305', '2010-04-29 11:54:48', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('306', '2010-04-29 11:56:53', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('307', '2010-04-29 11:57:18', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('308', '2010-04-29 11:58:35', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('309', '2010-04-29 12:00:20', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。'), ('310', '2010-04-29 12:03:52', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('311', '2010-04-29 14:11:20', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。'), ('312', '2010-04-29 14:12:27', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。'), ('313', '2010-04-29 14:12:44', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。'), ('314', '2010-04-29 14:12:54', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('315', '2010-04-29 14:48:11', '1', '40', '2', '抢老妇人', '2', '已修改 attribute_range 和 cash_range 。'), ('316', '2010-04-29 15:13:25', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('317', '2010-04-29 15:15:21', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('318', '2010-04-29 15:15:48', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('319', '2010-04-29 15:17:20', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('320', '2010-04-29 15:17:29', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('321', '2010-04-29 15:20:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('322', '2010-04-29 15:21:11', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('323', '2010-04-29 15:23:17', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('324', '2010-04-29 15:25:00', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('325', '2010-04-29 15:29:26', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('326', '2010-04-29 15:31:58', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('327', '2010-04-29 15:38:39', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('328', '2010-04-29 15:39:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。'), ('329', '2010-04-29 15:41:09', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('330', '2010-04-29 15:42:40', '1', '40', '3', '偷汽車', '2', '已修改 attribute_range 和 cash_range 。'), ('331', '2010-04-29 15:43:07', '1', '40', '4', '抢出租车', '2', '已修改 attribute_range 和 cash_range 。'), ('332', '2010-04-29 15:43:19', '1', '40', '5', '提款机', '2', '已修改 attribute_range 和 cash_range 。'), ('333', '2010-04-29 15:43:34', '1', '40', '6', '民宅', '2', '已修改 attribute_range 和 cash_range 。'), ('334', '2010-04-29 15:43:48', '1', '40', '7', '加油站', '2', '已修改 attribute_range 和 cash_range 。'), ('335', '2010-04-29 15:44:03', '1', '40', '8', '戏院', '2', '已修改 attribute_range 和 cash_range 。'), ('336', '2010-04-29 15:44:18', '1', '40', '9', '杂货店', '2', '已修改 attribute_range 和 cash_range 。'), ('337', '2010-04-29 15:44:35', '1', '40', '10', '24小时便利商店', '2', '已修改 attribute_range 和 cash_range 。'), ('338', '2010-04-29 15:44:50', '1', '40', '11', '绑架', '2', '已修改 attribute_range 和 cash_range 。'), ('339', '2010-04-29 15:45:13', '1', '40', '12', '珠宝店', '2', '已修改 attribute_range 和 cash_range 。'), ('340', '2010-04-29 15:45:27', '1', '40', '13', '保险箱', '2', '已修改 attribute_range 和 cash_range 。'), ('341', '2010-04-29 15:45:53', '1', '40', '14', '小银行', '2', '已修改 attribute_range 和 cash_range 。'), ('342', '2010-04-29 15:46:08', '1', '40', '15', '帮派小头目', '2', '已修改 attribute_range 和 cash_range 。'), ('343', '2010-04-29 15:46:24', '1', '40', '16', '汽车沙龙', '2', '已修改 attribute_range 和 cash_range 。'), ('344', '2010-04-29 15:46:42', '1', '40', '17', 'PayPal', '2', '已修改 attribute_range 和 cash_range 。'), ('345', '2010-04-29 15:46:55', '1', '40', '18', '地痞', '2', '已修改 attribute_range 和 cash_range 。'), ('346', '2010-04-29 15:47:15', '1', '40', '19', '小药头', '2', '已修改 attribute_range 和 cash_range 。'), ('347', '2010-04-29 15:47:30', '1', '40', '20', '赌场', '2', '已修改 attribute_range 和 cash_range 。'), ('348', '2010-04-29 15:47:43', '1', '40', '21', '狂欢会', '2', '已修改 attribute_range 和 cash_range 。'), ('349', '2010-04-29 15:48:06', '1', '40', '22', '超级市场', '2', '已修改 attribute_range 和 cash_range 。'), ('350', '2010-04-29 15:48:26', '1', '40', '23', '博物馆', '2', '已修改 attribute_range 和 cash_range 。'), ('351', '2010-04-29 15:48:42', '1', '40', '24', '俄国药王', '2', '已修改 attribute_range 和 cash_range 。'), ('352', '2010-04-29 15:48:56', '1', '40', '25', '外币', '2', '已修改 attribute_range 和 cash_range 。'), ('353', '2010-04-29 15:49:16', '1', '40', '26', '银行', '2', '已修改 attribute_range 和 cash_range 。'), ('354', '2010-04-29 15:49:30', '1', '40', '27', '运钞车', '2', '已修改 attribute_range 和 cash_range 。'), ('355', '2010-04-29 15:49:50', '1', '40', '28', '中央储备银行', '2', '已修改 attribute_range 和 cash_range 。'), ('356', '2010-04-29 15:50:05', '1', '40', '29', '杜月笙', '2', '已修改 attribute_range 和 cash_range 。'), ('357', '2010-04-29 15:50:21', '1', '40', '30', '操纵股市', '2', '已修改 attribute_range 和 cash_range 。'), ('358', '2010-04-29 15:50:37', '1', '40', '31', '黄金荣', '2', '已修改 attribute_range 和 cash_range 。'), ('359', '2010-04-29 15:50:56', '1', '40', '32', '他妈的烂地方', '2', '已修改 attribute_range 和 cash_range 。'), ('360', '2010-04-29 15:51:12', '1', '40', '33', '黄埔军校', '2', '已修改 attribute_range 和 cash_range 。'), ('361', '2010-04-29 16:04:07', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('362', '2010-04-29 16:04:39', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('363', '2010-04-29 16:05:36', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('364', '2010-04-29 16:06:41', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('365', '2010-04-29 16:06:49', '1', '40', '1', '偷窃', '2', '没有字段被修改。'), ('366', '2010-04-30 11:42:16', '1', '2', '1', '系统管理员', '1', ''), ('367', '2010-04-30 11:43:01', '1', '3', '2', 'darkmoon', '1', ''), ('368', '2010-04-30 11:43:28', '1', '3', '2', 'darkmoon', '2', '已修改 first_name, last_name, email 和 groups 。'), ('369', '2010-04-30 11:45:04', '1', '2', '1', '系统管理员', '3', ''), ('370', '2010-04-30 11:45:38', '1', '3', '2', 'darkmoon', '2', '已修改 is_staff 和 user_permissions 。'), ('372', '2010-04-30 14:11:54', '1', '42', '1', 'CQSSC', '1', ''), ('373', '2010-04-30 14:45:57', '1', '42', '2', 'CQSSC', '1', ''), ('374', '2010-04-30 15:31:56', '1', '42', '2', 'CQSSC', '2', '没有字段被修改。'), ('375', '2010-04-30 15:41:41', '1', '42', '2', 'CQSSC', '3', ''), ('376', '2010-04-30 15:43:07', '1', '42', '3', 'CQSSC', '1', ''), ('377', '2010-04-30 15:43:33', '1', '42', '3', 'CQSSC', '3', ''), ('378', '2010-04-30 15:49:42', '1', '42', '1', 'CQSSC', '1', ''), ('379', '2010-04-30 16:31:11', '1', '42', '1', 'CQSSC', '1', ''), ('380', '2010-04-30 16:31:25', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。'), ('381', '2010-04-30 16:31:55', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。'), ('382', '2010-04-30 16:32:04', '1', '42', '1', 'CQSSC', '3', ''), ('383', '2010-04-30 16:33:56', '1', '42', '2', 'CQSSC', '1', ''), ('384', '2010-04-30 16:34:08', '1', '42', '2', 'CQSSC', '2', '没有字段被修改。'), ('385', '2010-04-30 16:36:08', '1', '42', '2', 'CQSSC', '3', ''), ('386', '2010-04-30 16:56:49', '1', '42', '1', 'CQSSC', '1', ''), ('387', '2010-04-30 17:00:09', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。'), ('388', '2010-04-30 17:13:22', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。'), ('389', '2010-04-30 17:13:32', '1', '42', '1', 'CQSSC', '2', '已添加 帮会成员 \"root\".'), ('390', '2010-04-30 17:14:35', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。 已删除 帮会成员 \"root\".'), ('393', '2010-04-30 17:37:50', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。'), ('394', '2010-05-04 10:28:37', '1', '46', '1', '炸了他的工厂', '1', ''), ('395', '2010-05-04 10:28:58', '1', '46', '2', '抢走他的小姐', '1', ''), ('396', '2010-05-04 10:29:24', '1', '46', '3', '砸了他的场子', '1', ''), ('397', '2010-05-04 11:05:11', '1', '47', '4', 'root put bounty on darkmoon, price is 10 credits.', '1', ''), ('398', '2010-05-04 11:07:13', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '没有字段被修改。'), ('399', '2010-05-04 11:10:32', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '已修改 completed 。'), ('400', '2010-05-04 11:10:38', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '已修改 completed 。'), ('401', '2010-05-04 11:11:41', '1', '47', '4', 'root 悬赏 10 银元干掉  darkmoon。', '2', '没有字段被修改。'), ('402', '2010-05-04 11:38:09', '1', '48', '1', 'root\'s bank account.', '1', ''), ('403', '2010-05-04 11:38:22', '1', '48', '2', 'darkmoon\'s bank account.', '1', ''), ('413', '2010-05-04 14:12:33', '1', '51', '1', 'Friendship object', '1', ''), ('414', '2010-05-04 14:12:44', '1', '51', '2', 'Friendship object', '1', ''), ('415', '2010-05-04 14:13:35', '1', '51', '2', 'Friendship object', '3', ''), ('416', '2010-05-04 14:13:35', '1', '51', '1', 'Friendship object', '3', ''), ('417', '2010-05-04 14:27:19', '1', '51', '3', 'Friendship object', '1', ''), ('418', '2010-05-04 14:27:42', '1', '51', '4', 'Friendship object', '1', ''), ('419', '2010-05-04 14:43:23', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to darkmoon\".'), ('420', '2010-05-04 14:44:27', '1', '3', '2', 'darkmoon', '2', '已添加 用户关系 \"Relationship from darkmoon to root\".'), ('421', '2010-05-04 14:47:01', '1', '3', '1', 'root', '2', '已删除 用户关系 \"Relationship from root to darkmoon\".'), ('422', '2010-05-04 14:55:16', '1', '7', '1', 'crims.info', '2', '已修改 domain 和 name 。'), ('423', '2010-05-04 14:55:29', '1', '7', '2', 'hxxps.com', '1', ''), ('424', '2010-05-04 14:55:47', '1', '7', '3', 'hxxps.info', '1', ''), ('425', '2010-05-04 15:27:29', '1', '60', '3', '黑名单', '1', ''), ('426', '2010-05-04 15:27:44', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to darkmoon\".'), ('427', '2010-05-04 15:28:09', '1', '3', '2', 'darkmoon', '2', '已变更 status for 用户关系 \"Relationship from darkmoon to root\".'), ('428', '2010-05-04 15:29:53', '1', '60', '3', '黑名单', '3', ''), ('429', '2010-05-04 15:30:08', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to root\".'), ('430', '2010-05-04 15:30:15', '1', '3', '1', 'root', '2', '已变更 to_user for 用户关系 \"Relationship from root to darkmoon\".'), ('431', '2010-05-04 15:30:25', '1', '3', '2', 'darkmoon', '2', '已添加 用户关系 \"Relationship from darkmoon to root\".'), ('432', '2010-05-04 16:16:23', '1', '3', '1', 'root', '2', '已删除 银行 \"root 的银行账户\".'), ('433', '2010-05-04 16:16:46', '1', '3', '1', 'root', '2', '没有字段被修改。'), ('434', '2010-05-04 16:17:05', '1', '3', '1', 'root', '2', '已添加 银行 \"root 的银行账户\".'), ('435', '2010-05-04 16:17:19', '1', '3', '1', 'root', '2', '已变更 amount for 银行 \"root 的银行账户\".'), ('436', '2010-05-04 16:18:19', '1', '3', '1', 'root', '2', '已删除 银行 \"root 的银行账户\".'), ('437', '2010-05-04 16:19:25', '1', '3', '1', 'root', '2', '没有字段被修改。'), ('438', '2010-05-04 16:19:44', '1', '3', '1', 'root', '2', '已添加 银行 \"root 的银行账户\".'), ('440', '2010-05-04 16:42:31', '1', '3', '2', 'darkmoon', '3', ''), ('441', '2010-05-04 16:42:56', '1', '3', '3', 'darkmoon', '1', ''), ('442', '2010-05-04 16:43:29', '1', '3', '3', 'darkmoon', '2', '已修改 first_name, last_name 和 email 。 已添加 银行 \"darkmoon 的银行账户\".'), ('443', '2010-05-04 16:50:56', '1', '3', '3', 'darkmoon', '2', '已变更 amount for 银行 \"darkmoon 的银行账户\".'), ('444', '2010-05-04 16:51:04', '1', '3', '3', 'darkmoon', '2', '已变更 amount for 银行 \"darkmoon 的银行账户\".'), ('445', '2010-05-04 17:15:13', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".'), ('446', '2010-05-04 17:23:44', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".'), ('447', '2010-05-04 17:24:09', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".'), ('448', '2010-05-04 17:53:53', '1', '3', '3', 'darkmoon', '2', '已添加 用户盔甲 \"darkmoon\'s尿布\".'), ('449', '2010-05-04 18:34:07', '1', '2', '1', 'Discuz! Board', '1', ''), ('450', '2010-05-04 18:34:15', '1', '2', '1', 'Discuz! Board', '3', ''), ('451', '2010-05-04 18:37:26', '1', '42', '2', 'CQSSC', '1', ''), ('452', '2010-05-05 09:20:09', '1', '3', '3', 'darkmoon', '2', '已添加 用户毒品 \"darkmoon\'s 止痛药\".'), ('453', '2010-05-05 09:25:42', '1', '3', '3', 'darkmoon', '2', '已添加 用户妓女 \"darkmoon\'s Dolly\".'), ('454', '2010-05-05 09:49:25', '1', '3', '3', 'darkmoon', '2', '已添加 用户物业 \"烟草\".'), ('455', '2010-05-05 10:59:51', '1', '42', '2', 'CQSSC', '2', '已添加 聊天 \"first chat for test.\".'), ('456', '2010-05-05 11:21:41', '1', '47', '5', 'darkmoon 悬赏 10 银元干掉  root。', '1', ''), ('457', '2010-05-05 11:56:25', '1', '42', '2', 'CQSSC', '2', '已变更 type for 聊天 \"first chat for test.\". 已添加 抢劫 \"外币\".'), ('458', '2010-05-05 11:57:36', '1', '42', '2', 'CQSSC', '2', '已添加 帮会成员 \"darkmoon\".'), ('459', '2010-05-05 14:46:54', '1', '42', '2', 'CQSSC', '2', '已删除 成员 \"darkmoon\".'), ('460', '2010-05-05 17:22:23', '1', '42', '2', 'CQSSC', '2', '已添加 成员 \"darkmoon\".'), ('461', '2010-05-05 17:23:57', '1', '42', '2', 'CQSSC', '2', '已删除 成员 \"darkmoon\".'), ('462', '2010-05-05 19:20:26', '1', '42', '1', 'CQSSC', '1', ''), ('463', '2010-05-05 19:25:55', '1', '42', '1', 'CQSSC', '1', ''), ('464', '2010-05-05 19:31:59', '1', '42', '1', 'CQSSC', '1', ''), ('465', '2010-05-05 19:42:42', '1', '42', '1', 'CQSSC', '1', ''), ('466', '2010-05-05 19:48:58', '1', '42', '1', 'CQSSC', '2', '已添加 成员 \"darkmoon\".'), ('467', '2010-05-05 20:07:01', '1', '42', '1', 'CQSSC', '2', '已删除 成员 \"darkmoon\".'), ('468', '2010-05-05 20:07:20', '1', '42', '1', 'CQSSC', '2', '已添加 新闻 \"test news...\".'), ('469', '2010-05-06 16:22:21', '1', '3', '3', 'darkmoon', '2', '已添加 用户生意 \"darkmoon\'s club\".'), ('470', '2010-05-06 16:56:46', '1', '3', '3', 'darkmoon', '2', '已删除 用户生意 \"darkmoon\'s club\".'), ('471', '2010-05-06 16:59:56', '1', '73', '1', 'darkmoon', '1', ''), ('472', '2010-05-06 17:32:33', '1', '50', '1', 'c-mtv@163.com (darkmoon\'s contact)', '1', ''), ('473', '2010-05-07 16:02:38', '1', '3', '3', 'darkmoon', '2', '没有字段被修改。'), ('474', '2010-05-07 16:04:07', '1', '3', '3', 'darkmoon', '2', '已添加 基本资料 \"darkmoon\".'), ('475', '2010-05-07 16:05:19', '1', '3', '3', 'darkmoon', '2', '已添加 游戏数据 \"darkmoon的游戏数据\".'), ('476', '2010-05-07 16:08:07', '1', '3', '3', 'darkmoon', '2', '已变更 avatar for 基本资料 \"darkmoon\".'), ('477', '2010-05-07 16:21:40', '1', '3', '3', 'darkmoon', '2', '已删除 游戏数据 \"darkmoon的游戏数据\".'), ('478', '2010-05-07 16:22:47', '1', '3', '3', 'darkmoon', '3', ''), ('479', '2010-05-09 16:28:57', '1', '42', '1', 'CQSSC', '2', '已添加 聊天 \"hahahahahahahahha\".'), ('480', '2010-05-09 16:32:51', '1', '42', '1', 'CQSSC', '2', '已添加 聊天 \"gang chat from admin, should push to front.\".'), ('481', '2010-05-09 16:33:29', '1', '42', '1', 'CQSSC', '2', '已添加 聊天 \"接下来，测试中文。。。\".'), ('482', '2010-05-09 16:35:30', '1', '42', '1', 'CQSSC', '2', '已添加 聊天 \"现在，应该出现发言人的名字了。\".');

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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_content_type`
-- ----------------------------
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission'), ('2', 'group', 'auth', 'group'), ('3', 'user', 'auth', 'user'), ('4', 'message', 'auth', 'message'), ('5', 'content type', 'contenttypes', 'contenttype'), ('6', 'session', 'sessions', 'session'), ('7', 'site', 'sites', 'site'), ('8', 'log entry', 'admin', 'logentry'), ('9', 'registration profile', 'registration', 'registrationprofile'), ('10', 'Navigation', 'grappelli', 'navigation'), ('11', 'Navigation Item', 'grappelli', 'navigationitem'), ('12', 'Bookmark', 'grappelli', 'bookmark'), ('13', 'Bookmark Item', 'grappelli', 'bookmarkitem'), ('14', 'Help', 'grappelli', 'help'), ('15', 'Help Entry', 'grappelli', 'helpitem'), ('16', 'captcha store', 'captcha', 'captchastore'), ('17', 'avatar', 'system', 'avatar'), ('18', 'armor', 'system', 'armor'), ('19', 'character', 'system', 'character'), ('20', 'drug', 'system', 'drug'), ('21', 'building', 'system', 'building'), ('22', 'hooker', 'system', 'hooker'), ('23', 'user armor', 'system', 'userarmor'), ('24', 'user building', 'system', 'userbuilding'), ('25', 'user drug', 'system', 'userdrug'), ('26', 'user hooker', 'system', 'userhooker'), ('27', 'business', 'system', 'business'), ('28', 'user business', 'system', 'userbusiness'), ('29', 'guard', 'system', 'guard'), ('30', 'user guard', 'system', 'userguard'), ('31', 'weapon', 'system', 'weapon'), ('32', 'user weapon', 'system', 'userweapon'), ('33', 'province', 'system', 'province'), ('34', 'event', 'system', 'event'), ('35', 'benefit', 'system', 'benefit'), ('36', 'hospital', 'system', 'hospital'), ('37', 'random event', 'system', 'randomevent'), ('38', 'random event question', 'system', 'randomeventquestion'), ('39', 'random event choice', 'system', 'randomeventchoice'), ('40', 'Robberies', 'system', 'robbery'), ('41', 'Message', 'messages', 'message'), ('42', 'Gang', 'game', 'gang'), ('46', 'Sabotage', 'system', 'sabotage'), ('47', 'Bounty', 'game', 'bounty'), ('48', 'Bank', 'game', 'bank'), ('50', 'contact', 'friends', 'contact'), ('51', 'friendship', 'friends', 'friendship'), ('52', 'join invitation', 'friends', 'joininvitation'), ('53', 'friendship invitation', 'friends', 'friendshipinvitation'), ('54', 'friendship invitation history', 'friends', 'friendshipinvitationhistory'), ('55', 'notice type', 'notification', 'noticetype'), ('56', 'notice setting', 'notification', 'noticesetting'), ('57', 'notice', 'notification', 'notice'), ('58', 'notice queue batch', 'notification', 'noticequeuebatch'), ('59', 'observed item', 'notification', 'observeditem'), ('60', 'Relationship status', 'relationships', 'relationshipstatus'), ('61', 'Relationship', 'relationships', 'relationship'), ('63', 'chat', 'game', 'chat'), ('67', 'invite', 'game', 'ganginvite'), ('68', 'member', 'game', 'gangmember'), ('69', 'news', 'game', 'gangnews'), ('70', 'robbery', 'game', 'gangrobbery'), ('71', 'assault', 'game', 'gangassault'), ('72', 'user business drugs', 'system', 'userbusinessdrug'), ('73', 'guestbook', 'game', 'guestbook'), ('74', 'prison', 'game', 'prison'), ('75', 'rip', 'game', 'rip'), ('76', 'profile', 'account', 'userprofile'), ('77', 'game data', 'account', 'userdata');

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
INSERT INTO `django_session` VALUES ('1f09faf546ea25a3ab010adc2e585bea', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVRJfYXV0aF91c2Vy\nX2JhY2tlbmRxBlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRxB1UN\nX2F1dGhfdXNlcl9pZHEIigEBdS42YzFiMDI5MGJhNjYzMjY0YzE1NWVhMWU3NmMwOTEyMQ==\n', '2010-05-19 16:03:19'), ('23d64587c0070ce0dc9dae8aa27f6053', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21'), ('5286edf50ae8d76c4766ee5d9ed97aac', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21'), ('72428d3f08e0427f6a8850ba47a4f659', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-12 18:51:41'), ('86cac61cc2a7f010c3d465c913cab99a', 'gAJ9cQFVCnRlc3Rjb29raWVxAlUGd29ya2VkcQNzLmJmMjBjODVkMTkwZTExMWEzMTkxMjFhNzIz\nZWI2NGJm\n', '2010-05-02 20:09:30'), ('97884902b0c6a2d5360cacf5aac93d7a', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-23 16:28:31'), ('9f005bf1fd11cc2e8ff354a75ee459b1', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UHbWVzc2FnZXEEXXEFKFUHc3VjY2Vzc3EGWBwAAABTaXRl\nIHdhcyBhZGRlZCB0byBCb29rbWFya3MucQdlc1UNX2F1dGhfdXNlcl9pZHEIigEBVRJfYXV0aF91\nc2VyX2JhY2tlbmRxCVUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRx\nCnUuMGI4YzhlNzg1MzY0NTc2ZjJlZDQyNTgzMzZkMmNlNmU=\n', '2010-05-02 20:39:45'), ('d01d78ab0e8391f7e31e22691afee012', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVRJfYXV0aF91c2Vy\nX2JhY2tlbmRxBlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRxB1UN\nX2F1dGhfdXNlcl9pZHEIigEBdS42YzFiMDI5MGJhNjYzMjY0YzE1NWVhMWU3NmMwOTEyMQ==\n', '2010-05-22 16:17:26'), ('d71173bd46cd82de4d4b4aa96fc80479', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-18 14:55:50'), ('d81e95756a970d5eba5f0681d1d55287', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:09:08'), ('edc466f72a1ac08f5718509fa95aabfd', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-21 16:40:34'), ('f417b8d6e61fa5f7ef863c861034ad10', 'gAJ9cQFVCnRlc3Rjb29raWVxAlUGd29ya2VkcQNzLmJmMjBjODVkMTkwZTExMWEzMTkxMjFhNzIz\nZWI2NGJm\n', '2010-05-22 18:59:56');

-- ----------------------------
--  Table structure for `django_site`
-- ----------------------------
DROP TABLE IF EXISTS `django_site`;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_site`
-- ----------------------------
INSERT INTO `django_site` VALUES ('1', 'crims.info', 'crims.info'), ('2', 'hxxps.com', 'hxxps.com'), ('3', 'hxxps.info', 'hxxps.info');

-- ----------------------------
--  Table structure for `friends_contact`
-- ----------------------------
DROP TABLE IF EXISTS `friends_contact`;
CREATE TABLE `friends_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(75) NOT NULL,
  `added` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `friends_contact_user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_4dde736d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `friends_contact_users`
-- ----------------------------
DROP TABLE IF EXISTS `friends_contact_users`;
CREATE TABLE `friends_contact_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_id` (`contact_id`,`user_id`),
  KEY `user_id_refs_id_49de6f86` (`user_id`),
  CONSTRAINT `user_id_refs_id_49de6f86` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `contact_id_refs_id_70681d66` FOREIGN KEY (`contact_id`) REFERENCES `friends_contact` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `friends_friendship`
-- ----------------------------
DROP TABLE IF EXISTS `friends_friendship`;
CREATE TABLE `friends_friendship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `added` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `to_user_id` (`to_user_id`,`from_user_id`),
  KEY `friends_friendship_to_user_id` (`to_user_id`),
  KEY `friends_friendship_from_user_id` (`from_user_id`),
  CONSTRAINT `from_user_id_refs_id_1796f00e` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `to_user_id_refs_id_1796f00e` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `friends_friendshipinvitation`
-- ----------------------------
DROP TABLE IF EXISTS `friends_friendshipinvitation`;
CREATE TABLE `friends_friendshipinvitation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `sent` date NOT NULL,
  `status` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `friends_friendshipinvitation_from_user_id` (`from_user_id`),
  KEY `friends_friendshipinvitation_to_user_id` (`to_user_id`),
  CONSTRAINT `from_user_id_refs_id_666f7ee7` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `to_user_id_refs_id_666f7ee7` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `friends_friendshipinvitationhistory`
-- ----------------------------
DROP TABLE IF EXISTS `friends_friendshipinvitationhistory`;
CREATE TABLE `friends_friendshipinvitationhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `sent` date NOT NULL,
  `status` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `friends_friendshipinvitationhistory_from_user_id` (`from_user_id`),
  KEY `friends_friendshipinvitationhistory_to_user_id` (`to_user_id`),
  CONSTRAINT `from_user_id_refs_id_2df65a84` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `to_user_id_refs_id_2df65a84` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `friends_joininvitation`
-- ----------------------------
DROP TABLE IF EXISTS `friends_joininvitation`;
CREATE TABLE `friends_joininvitation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `sent` date NOT NULL,
  `status` varchar(1) NOT NULL,
  `confirmation_key` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `friends_joininvitation_from_user_id` (`from_user_id`),
  KEY `friends_joininvitation_contact_id` (`contact_id`),
  CONSTRAINT `contact_id_refs_id_2f4af9a5` FOREIGN KEY (`contact_id`) REFERENCES `friends_contact` (`id`),
  CONSTRAINT `from_user_id_refs_id_47d988c5` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_bank`
-- ----------------------------
DROP TABLE IF EXISTS `game_bank`;
CREATE TABLE `game_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_1e4ea07a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `game_bank`
-- ----------------------------
INSERT INTO `game_bank` VALUES ('4', '1', '100', '2010-05-04 16:19:44', '2010-05-04 16:19:44');

-- ----------------------------
--  Table structure for `game_bounty`
-- ----------------------------
DROP TABLE IF EXISTS `game_bounty`;
CREATE TABLE `game_bounty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sponsor_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `credits` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  `completed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_bounty_sponsor_id` (`sponsor_id`),
  KEY `game_bounty_target_id` (`target_id`),
  CONSTRAINT `sponsor_id_refs_id_68ac4ee1` FOREIGN KEY (`sponsor_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `target_id_refs_id_68ac4ee1` FOREIGN KEY (`target_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_chat`
-- ----------------------------
DROP TABLE IF EXISTS `game_chat`;
CREATE TABLE `game_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `gang_id` int(11) DEFAULT NULL,
  `userbusiness_id` int(11) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `content` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_chat_sender_id` (`sender_id`),
  KEY `game_chat_gang_id` (`gang_id`),
  KEY `game_chat_userbusiness_id` (`userbusiness_id`),
  CONSTRAINT `sender_id_refs_id_24895e4e` FOREIGN KEY (`sender_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `userbusiness_id_refs_id_58167c50` FOREIGN KEY (`userbusiness_id`) REFERENCES `user_business` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `game_chat`
-- ----------------------------
INSERT INTO `game_chat` VALUES ('7', '1', '1', null, null, 'hahahahahahahahha', '2010-05-09 16:28:57'), ('8', '1', '1', null, null, 'gang chat from admin, should push to front.', '2010-05-09 16:32:51'), ('9', '1', '1', null, 'square', '接下来，测试中文。。。', '2010-05-09 16:33:29'), ('10', '1', '1', null, 'square', '现在，应该出现发言人的名字了。', '2010-05-09 16:35:30');

-- ----------------------------
--  Table structure for `game_gang`
-- ----------------------------
DROP TABLE IF EXISTS `game_gang`;
CREATE TABLE `game_gang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `presentation` longtext NOT NULL,
  `photo` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `province_id` int(11) NOT NULL,
  `creater_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `vice_leader_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_gang_province_id` (`province_id`),
  KEY `game_gang_creater_id` (`creater_id`),
  KEY `game_gang_leader_id` (`leader_id`),
  KEY `game_gang_vice_leader_id` (`vice_leader_id`),
  CONSTRAINT `creater_id_refs_id_45b3afd3` FOREIGN KEY (`creater_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `leader_id_refs_id_45b3afd3` FOREIGN KEY (`leader_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `province_id_refs_id_3b567f1f` FOREIGN KEY (`province_id`) REFERENCES `system_province` (`id`),
  CONSTRAINT `vice_leader_id_refs_id_45b3afd3` FOREIGN KEY (`vice_leader_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `game_gang`
-- ----------------------------
INSERT INTO `game_gang` VALUES ('1', 'CQSSC', 'wd', 'uploads/gang/11____.png', '2010-05-05 19:42:42', '1', '1', '1', null);

-- ----------------------------
--  Table structure for `game_gangassault`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangassault`;
CREATE TABLE `game_gangassault` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_id` int(11) NOT NULL,
  `victim_id` int(11) NOT NULL,
  `initiator_id` int(11) NOT NULL,
  `status` varchar(10) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_gangassault_gang_id` (`gang_id`),
  KEY `game_gangassault_victim_id` (`victim_id`),
  KEY `game_gangassault_initiator_id` (`initiator_id`),
  CONSTRAINT `gang_id_refs_id_39b44f1f` FOREIGN KEY (`gang_id`) REFERENCES `game_gang` (`id`),
  CONSTRAINT `initiator_id_refs_id_4f9f4601` FOREIGN KEY (`initiator_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `victim_id_refs_id_4f9f4601` FOREIGN KEY (`victim_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_gangassault_accepted`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangassault_accepted`;
CREATE TABLE `game_gangassault_accepted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gangassault_id` int(11) NOT NULL,
  `gangmember_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gangassault_id` (`gangassault_id`,`gangmember_id`),
  KEY `gangmember_id_refs_id_e025b32` (`gangmember_id`),
  CONSTRAINT `gangmember_id_refs_id_e025b32` FOREIGN KEY (`gangmember_id`) REFERENCES `game_gangmember` (`id`),
  CONSTRAINT `gangassault_id_refs_id_67af8be2` FOREIGN KEY (`gangassault_id`) REFERENCES `game_gangassault` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_gangassault_declined`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangassault_declined`;
CREATE TABLE `game_gangassault_declined` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gangassault_id` int(11) NOT NULL,
  `gangmember_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gangassault_id` (`gangassault_id`,`gangmember_id`),
  KEY `gangmember_id_refs_id_96342c79` (`gangmember_id`),
  CONSTRAINT `gangmember_id_refs_id_96342c79` FOREIGN KEY (`gangmember_id`) REFERENCES `game_gangmember` (`id`),
  CONSTRAINT `gangassault_id_refs_id_dedd7985` FOREIGN KEY (`gangassault_id`) REFERENCES `game_gangassault` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_ganginvite`
-- ----------------------------
DROP TABLE IF EXISTS `game_ganginvite`;
CREATE TABLE `game_ganginvite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inviter_id` int(11) NOT NULL,
  `accepter_id` int(11) NOT NULL,
  `gang_id` int(11) NOT NULL,
  `accepted` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_ganginvite_inviter_id` (`inviter_id`),
  KEY `game_ganginvite_accepter_id` (`accepter_id`),
  KEY `game_ganginvite_gang_id` (`gang_id`),
  CONSTRAINT `accepter_id_refs_id_45300064` FOREIGN KEY (`accepter_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `gang_id_refs_id_e9e394bc` FOREIGN KEY (`gang_id`) REFERENCES `game_gang` (`id`),
  CONSTRAINT `inviter_id_refs_id_45300064` FOREIGN KEY (`inviter_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_gangmember`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangmember`;
CREATE TABLE `game_gangmember` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `gang_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`gang_id`),
  KEY `game_gangmember_user_id` (`user_id`),
  KEY `game_gangmember_gang_id` (`gang_id`),
  CONSTRAINT `gang_id_refs_id_5dcd1bfd` FOREIGN KEY (`gang_id`) REFERENCES `game_gang` (`id`),
  CONSTRAINT `user_id_refs_id_678d86dd` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `game_gangmember`
-- ----------------------------
INSERT INTO `game_gangmember` VALUES ('2', '1', '1', '2010-05-05 19:42:42');

-- ----------------------------
--  Table structure for `game_gangnews`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangnews`;
CREATE TABLE `game_gangnews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) NOT NULL,
  `body` longtext NOT NULL,
  `gang_id` int(11) NOT NULL,
  `writer_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_gangnews_gang_id` (`gang_id`),
  KEY `game_gangnews_writer_id` (`writer_id`),
  CONSTRAINT `gang_id_refs_id_36e815e8` FOREIGN KEY (`gang_id`) REFERENCES `game_gang` (`id`),
  CONSTRAINT `writer_id_refs_id_d5faf4f8` FOREIGN KEY (`writer_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `game_gangnews`
-- ----------------------------
INSERT INTO `game_gangnews` VALUES ('1', 'test news...', 'test news...', '1', '1', '2010-05-05 20:07:20');

-- ----------------------------
--  Table structure for `game_gangrobbery`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangrobbery`;
CREATE TABLE `game_gangrobbery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_id` int(11) NOT NULL,
  `robbery_id` int(11) NOT NULL,
  `initiator_id` int(11) NOT NULL,
  `status` varchar(10) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_gangrobbery_gang_id` (`gang_id`),
  KEY `game_gangrobbery_robbery_id` (`robbery_id`),
  KEY `game_gangrobbery_initiator_id` (`initiator_id`),
  CONSTRAINT `gang_id_refs_id_3811f6d5` FOREIGN KEY (`gang_id`) REFERENCES `game_gang` (`id`),
  CONSTRAINT `initiator_id_refs_id_38d4a1b5` FOREIGN KEY (`initiator_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `robbery_id_refs_id_1775d033` FOREIGN KEY (`robbery_id`) REFERENCES `system_robbery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_gangrobbery_accepted`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangrobbery_accepted`;
CREATE TABLE `game_gangrobbery_accepted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gangrobbery_id` int(11) NOT NULL,
  `gangmember_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gangrobbery_id` (`gangrobbery_id`,`gangmember_id`),
  KEY `gangmember_id_refs_id_d342ef22` (`gangmember_id`),
  CONSTRAINT `gangmember_id_refs_id_d342ef22` FOREIGN KEY (`gangmember_id`) REFERENCES `game_gangmember` (`id`),
  CONSTRAINT `gangrobbery_id_refs_id_a5d322da` FOREIGN KEY (`gangrobbery_id`) REFERENCES `game_gangrobbery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_gangrobbery_declined`
-- ----------------------------
DROP TABLE IF EXISTS `game_gangrobbery_declined`;
CREATE TABLE `game_gangrobbery_declined` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gangrobbery_id` int(11) NOT NULL,
  `gangmember_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gangrobbery_id` (`gangrobbery_id`,`gangmember_id`),
  KEY `gangmember_id_refs_id_e697565` (`gangmember_id`),
  CONSTRAINT `gangmember_id_refs_id_e697565` FOREIGN KEY (`gangmember_id`) REFERENCES `game_gangmember` (`id`),
  CONSTRAINT `gangrobbery_id_refs_id_74b7dab3` FOREIGN KEY (`gangrobbery_id`) REFERENCES `game_gangrobbery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_guestbook`
-- ----------------------------
DROP TABLE IF EXISTS `game_guestbook`;
CREATE TABLE `game_guestbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_guestbook_owner_id` (`owner_id`),
  KEY `game_guestbook_author_id` (`author_id`),
  CONSTRAINT `author_id_refs_id_1f0a3dde` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `owner_id_refs_id_1f0a3dde` FOREIGN KEY (`owner_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_prison`
-- ----------------------------
DROP TABLE IF EXISTS `game_prison`;
CREATE TABLE `game_prison` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prisoner_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  `escaped` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game_prison_prisoner_id` (`prisoner_id`),
  CONSTRAINT `prisoner_id_refs_id_1338fd9b` FOREIGN KEY (`prisoner_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game_rip`
-- ----------------------------
DROP TABLE IF EXISTS `game_rip`;
CREATE TABLE `game_rip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  `escaped` tinyint(1) NOT NULL,
  `reason` varchar(20) NOT NULL,
  `victim_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_rip_user_id` (`user_id`),
  KEY `game_rip_victim_id` (`victim_id`),
  CONSTRAINT `user_id_refs_id_49542852` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `victim_id_refs_id_49542852` FOREIGN KEY (`victim_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
INSERT INTO `grappelli_navigation` VALUES ('1', '媒体管理', '0'), ('2', '内容', '1'), ('3', '外部', '2'), ('4', '文档', '3');

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
INSERT INTO `grappelli_navigationitem` VALUES ('1', '1', '文件浏览', '/admin/filebrowser/browse/', '1', '0'), ('2', '2', '主页', '/admin/', '1', '0'), ('3', '2', 'Grappelli', '/admin/grappelli/', '1', '1'), ('4', '3', 'Grappelli GoogleCode', 'http://code.google.com/p/django-grappelli/', '2', '0'), ('5', '4', '帮助', '/grappelli/help/', '1', '0');

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
  CONSTRAINT `navigationitem_id_refs_id_fbdd09fc` FOREIGN KEY (`navigationitem_id`) REFERENCES `grappelli_navigationitem` (`id`),
  CONSTRAINT `user_id_refs_id_c396c6b7` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `messages_message`
-- ----------------------------
DROP TABLE IF EXISTS `messages_message`;
CREATE TABLE `messages_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(120) NOT NULL,
  `body` longtext NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `parent_msg_id` int(11) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `sender_deleted_at` datetime DEFAULT NULL,
  `recipient_deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_message_sender_id` (`sender_id`),
  KEY `messages_message_recipient_id` (`recipient_id`),
  KEY `messages_message_parent_msg_id` (`parent_msg_id`),
  CONSTRAINT `parent_msg_id_refs_id_54f43543` FOREIGN KEY (`parent_msg_id`) REFERENCES `messages_message` (`id`),
  CONSTRAINT `recipient_id_refs_id_3acf8bb8` FOREIGN KEY (`recipient_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `sender_id_refs_id_3acf8bb8` FOREIGN KEY (`sender_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `messages_message`
-- ----------------------------
INSERT INTO `messages_message` VALUES ('1', 'test', 'test', '1', '1', null, '2010-04-29 17:44:16', '2010-04-29 17:44:28', '2010-04-29 17:44:35', null, null), ('2', '回复: test', 'root 写道:\r\n> test\r\n\r\nsfdsf', '1', '1', '1', '2010-04-29 17:44:35', '2010-04-29 17:44:41', null, null, null);

-- ----------------------------
--  Table structure for `notification_notice`
-- ----------------------------
DROP TABLE IF EXISTS `notification_notice`;
CREATE TABLE `notification_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `notice_type_id` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `unseen` tinyint(1) NOT NULL,
  `archived` tinyint(1) NOT NULL,
  `on_site` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_notice_user_id` (`user_id`),
  KEY `notification_notice_notice_type_id` (`notice_type_id`),
  CONSTRAINT `notice_type_id_refs_id_212d5727` FOREIGN KEY (`notice_type_id`) REFERENCES `notification_noticetype` (`id`),
  CONSTRAINT `user_id_refs_id_690c45d1` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `notification_noticequeuebatch`
-- ----------------------------
DROP TABLE IF EXISTS `notification_noticequeuebatch`;
CREATE TABLE `notification_noticequeuebatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pickled_data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `notification_noticesetting`
-- ----------------------------
DROP TABLE IF EXISTS `notification_noticesetting`;
CREATE TABLE `notification_noticesetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notice_type_id` int(11) NOT NULL,
  `medium` varchar(1) NOT NULL,
  `send` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`notice_type_id`,`medium`),
  KEY `notification_noticesetting_user_id` (`user_id`),
  KEY `notification_noticesetting_notice_type_id` (`notice_type_id`),
  CONSTRAINT `notice_type_id_refs_id_1024de5c` FOREIGN KEY (`notice_type_id`) REFERENCES `notification_noticetype` (`id`),
  CONSTRAINT `user_id_refs_id_8c53966` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `notification_noticetype`
-- ----------------------------
DROP TABLE IF EXISTS `notification_noticetype`;
CREATE TABLE `notification_noticetype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(40) NOT NULL,
  `display` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `default` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `notification_noticetype`
-- ----------------------------
INSERT INTO `notification_noticetype` VALUES ('1', 'messages_received', 'Message Received', 'you have received a message', '2'), ('2', 'messages_sent', 'Message Sent', 'you have sent a message', '1'), ('3', 'messages_replied', 'Message Replied', 'you have replied to a message', '1'), ('4', 'messages_reply_received', 'Reply Received', 'you have received a reply to a message', '2'), ('5', 'messages_deleted', 'Message Deleted', 'you have deleted a message', '1'), ('6', 'messages_recovered', 'Message Recovered', 'you have undeleted a message', '1'), ('7', 'friends_invite', 'Invitation Received', 'you have received an invitation', '2'), ('8', 'friends_invite_sent', 'Invitation Sent', 'you have sent an invitation', '1'), ('9', 'friends_accept', 'Acceptance Received', 'an invitation you sent has been accepted', '2'), ('10', 'friends_accept_sent', 'Acceptance Sent', 'you have accepted an invitation you received', '1'), ('11', 'friends_otherconnect', 'Other Connection', 'one of your friends has a new connection', '2'), ('12', 'join_accept', 'Join Invitation Accepted', 'an invitation you sent to join this site has been accepted', '2');

-- ----------------------------
--  Table structure for `notification_observeditem`
-- ----------------------------
DROP TABLE IF EXISTS `notification_observeditem`;
CREATE TABLE `notification_observeditem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `notice_type_id` int(11) NOT NULL,
  `added` datetime NOT NULL,
  `signal` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_observeditem_user_id` (`user_id`),
  KEY `notification_observeditem_content_type_id` (`content_type_id`),
  KEY `notification_observeditem_notice_type_id` (`notice_type_id`),
  CONSTRAINT `content_type_id_refs_id_6c21f628` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `notice_type_id_refs_id_4b098f3e` FOREIGN KEY (`notice_type_id`) REFERENCES `notification_noticetype` (`id`),
  CONSTRAINT `user_id_refs_id_7555f7d4` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
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
--  Table structure for `relationships_relationship`
-- ----------------------------
DROP TABLE IF EXISTS `relationships_relationship`;
CREATE TABLE `relationships_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from_user_id` (`from_user_id`,`to_user_id`,`status_id`),
  KEY `relationships_relationship_from_user_id` (`from_user_id`),
  KEY `relationships_relationship_to_user_id` (`to_user_id`),
  KEY `relationships_relationship_status_id` (`status_id`),
  KEY `relationships_relationship_site_id` (`site_id`),
  CONSTRAINT `from_user_id_refs_id_59227730` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `site_id_refs_id_78983d9` FOREIGN KEY (`site_id`) REFERENCES `django_site` (`id`),
  CONSTRAINT `status_id_refs_id_4507d703` FOREIGN KEY (`status_id`) REFERENCES `relationships_relationshipstatus` (`id`),
  CONSTRAINT `to_user_id_refs_id_59227730` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `relationships_relationshipstatus`
-- ----------------------------
DROP TABLE IF EXISTS `relationships_relationshipstatus`;
CREATE TABLE `relationships_relationshipstatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `verb` varchar(100) NOT NULL,
  `from_slug` varchar(100) NOT NULL,
  `to_slug` varchar(100) NOT NULL,
  `symmetrical_slug` varchar(100) NOT NULL,
  `login_required` tinyint(1) NOT NULL,
  `private` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `relationships_relationshipstatus`
-- ----------------------------
INSERT INTO `relationships_relationshipstatus` VALUES ('1', 'Following', 'follow', 'following', 'followers', 'friends', '0', '0'), ('2', 'Blocking', 'block', 'blocking', 'blockers', '!', '1', '1');

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
--  Table structure for `system_benefit`
-- ----------------------------
DROP TABLE IF EXISTS `system_benefit`;
CREATE TABLE `system_benefit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  `type` varchar(100) NOT NULL,
  `credits` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_benefit`
-- ----------------------------
INSERT INTO `system_benefit` VALUES ('1', '妓女赚钱加速', '看看我的这些超级药丸啊。他们的混合了荷尔蒙，避孕药跟春药，你的小姐每个都会变成名模身材海咪咪。这些药片将会增加你10% 的收入，只需要花你 10 个点数。这些药片将会在你的小姐体内持续 28 个游戏日。', 'hooker', '10', '2010-04-21 10:36:03'), ('2', '武器增强器', '想不想要来点大家伙?我这边有些烫手的，可以增加你30%的武器威力.像我手上这件增强器可以让你 28天内威力增强不少,心动了吗?我只需要少少的 10点游戏点数.如果愿意买的话我另外免费给你个杀必速,这个润滑剂将可以让你的武器增加 30% 的威力.', 'weapon', '10', '2010-04-21 10:36:33'), ('3', '产量加速器', '如果你想的话我可以提供一些廉价劳力给你，这些孤儿只要提供给他们住所，他们将会为你工作。他们工作差些，但他们会增加你的10%的毒品产量 。你只需要 10 游戏点数就可以租用它们 28天。不要担心，如果不小心他们死掉的话，安家费已经算在里面了。', 'building', '10', '2010-04-21 10:37:06'), ('4', '避孕套', '过来！您这肮脏，低俗的一名男子。你能相信吗？我这边有提供卫生套，想想看得到了性病不仅会让你的生活受到影响，连日常打里事务都没办法了，如果为了你的身体健康与名声着想，多保护一下自己吧。我这边有贩卖无敌防护卫生套，它的效用远胜于市面上所有的牌子，可以完全隔绝性病对你的影响，在，你每次使用一个套子，可以让你28个游戏天内不会得到性病，每次你可以跟我买 100个，而这些只需要花你 10点的点数。', 'condom', '10', '2010-04-21 10:37:19');

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
  `description` longtext NOT NULL,
  `section` varchar(20) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `change` decimal(4,4) NOT NULL,
  `drug_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_event_drug_id` (`drug_id`),
  CONSTRAINT `drug_id_refs_id_46069610` FOREIGN KEY (`drug_id`) REFERENCES `system_drug` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_event`
-- ----------------------------
INSERT INTO `system_event` VALUES ('1', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.', 'robbery', 'uploads/event/newspaper_raid.jpg', '0.1000', null), ('2', 'DRUG PRICES DOWN', 'ads', 'drug', 'uploads/event/newspaper_drugs.jpg', '-0.2000', '9'), ('3', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.', 'building', 'uploads/event/warren_buffet.jpg', '0.1000', null), ('4', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.', 'drug', 'uploads/event/newspaper_drugs.jpg', '0.2000', '9'), ('5', 'CARNIVAL!', 'Today it is carnival day in The Bund!', 'drug', 'uploads/event/carnival.jpg', '-0.2000', null), ('6', 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!', 'robbery', 'uploads/event/oil_sheikh.jpg', '0.0000', null), ('7', 'BILL GATES IN TOWN!', 'Bill Gates is visiting the city today to promote his new software and try some new bugs.', 'business', 'uploads/event/bill_gates.jpg', '0.0000', null), ('8', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.', 'dock', 'uploads/event/newspaper_ship.jpg', '0.2000', '13');

-- ----------------------------
--  Table structure for `system_event_bk`
-- ----------------------------
DROP TABLE IF EXISTS `system_event_bk`;
CREATE TABLE `system_event_bk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `section` varchar(20) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `change` decimal(4,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_event_bk`
-- ----------------------------
INSERT INTO `system_event_bk` VALUES ('1', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.', 'robbery', 'uploads/event/newspaper_raid.jpg', '0.1000'), ('2', 'DRUG PRICES DOWN', 'ads', 'drug', 'uploads/event/newspaper_drugs.jpg', '-0.2000'), ('3', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.', 'building', 'uploads/event/warren_buffet.jpg', '0.1000'), ('4', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.', 'drug', 'uploads/event/newspaper_drugs.jpg', '0.2000'), ('5', 'CARNIVAL!', 'Today it is carnival day in The Bund!', 'drug', 'uploads/event/carnival.jpg', '-0.2000'), ('6', 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!', 'robbery', 'uploads/event/oil_sheikh.jpg', '0.0000'), ('7', 'BILL GATES IN TOWN!', 'Bill Gates is visiting the city today to promote his new software and try some new bugs.', 'business', 'uploads/event/bill_gates.jpg', '0.0000');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_guard`
-- ----------------------------
INSERT INTO `system_guard` VALUES ('1', '恶狗', '20', 'uploads/guard/evil_dog.jpg', '3000', '2010-04-21 10:45:33'), ('2', '杀手', '80', 'uploads/guard/guard5.jpg', '15000', '2010-04-21 10:46:07'), ('3', '疯狂守卫', '200', 'uploads/guard/guard4.jpg', '45000', '2010-04-21 10:46:28'), ('4', '俄罗斯前特种兵', '350', 'uploads/guard/guard6.jpg', '90000', '2010-04-21 10:46:56'), ('5', '专业保镖', '500', 'uploads/guard/guard3.jpg', '200000', '2010-04-21 10:47:19'), ('6', '终极保镖', '800', 'uploads/guard/guard.jpg', '600000', '2010-04-21 10:47:38');

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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_hooker`
-- ----------------------------
INSERT INTO `system_hooker` VALUES ('1', 'Dolly', 'uploads/hooker/dolly.jpg', '400', '30', '6', '0.0100', '0', '10', '1', '2010-04-21 10:52:57', '2010-04-21 10:52:57'), ('2', 'Heinrich', 'uploads/hooker/heinrich.jpg', '600', '50', '10', '0.0100', '0', '20', '1', '2010-04-21 10:54:13', '2010-04-21 10:54:13'), ('3', 'Britney', 'uploads/hooker/hooker2.jpg', '800', '60', '15', '0.0100', '0', '30', '1', '2010-04-21 10:56:35', '2010-04-21 10:56:35'), ('4', 'Mount Tse Tung', 'uploads/hooker/hooker4.jpg', '1200', '90', '24', '0.0100', '0', '50', '1', '2010-04-21 10:57:27', '2010-04-21 10:57:27'), ('5', 'Marilyn', 'uploads/hooker/hooker3.jpg', '3500', '270', '46', '0.0100', '0', '80', '1', '2010-04-21 10:58:14', '2010-04-21 10:58:14'), ('6', 'Candy', 'uploads/hooker/hooker1.jpg', '3900', '300', '55', '0.0100', '0', '100', '2', '2010-04-21 10:59:17', '2010-04-21 10:59:17'), ('7', 'Bell', 'uploads/hooker/bell.jpg', '4000', '330', '66', '0.0100', '0', '100', '2', '2010-04-21 11:00:20', '2010-04-21 11:00:20'), ('8', 'Patricia', 'uploads/hooker/patricia.jpg', '4200', '360', '77', '0.0100', '0', '100', '2', '2010-04-21 11:01:04', '2010-04-21 11:01:04'), ('9', 'Claire', 'uploads/hooker/claire.jpg', '4500', '390', '86', '0.0100', '0', '100', '2', '2010-04-21 11:03:16', '2010-04-21 11:03:16'), ('10', 'Crystal', 'uploads/hooker/crystal.jpg', '5200', '450', '96', '0.0100', '0', '100', '2', '2010-04-21 11:03:55', '2010-04-21 11:03:55'), ('11', 'Valerie', 'uploads/hooker/valerie.jpg', '7000', '600', '116', '0.0100', '0', '100', '2', '2010-04-21 11:04:54', '2010-04-21 11:04:54'), ('12', 'Chessy', 'uploads/hooker/chessy.jpg', '8400', '720', '130', '0.0100', '0', '100', '2', '2010-04-21 11:06:38', '2010-04-21 11:06:38'), ('13', 'Denim Daisy', 'uploads/hooker/denim_daisy.jpg', '9500', '810', '142', '0.0100', '0', '100', '2', '2010-04-21 11:07:33', '2010-04-21 11:07:33'), ('14', 'Head Nurse', 'uploads/hooker/head_nurse.jpg', '12500', '1050', '168', '0.0100', '0', '100', '2', '2010-04-21 11:08:24', '2010-04-21 11:08:24'), ('15', 'Cindy', 'uploads/hooker/cindy.jpg', '14000', '1170', '182', '0.0100', '0', '100', '2', '2010-04-21 11:09:09', '2010-04-21 11:09:09'), ('16', 'George', 'uploads/hooker/george.jpg', '14500', '1200', '195', '0.0100', '0', '100', '2', '2010-04-21 11:09:53', '2010-04-21 11:09:53'), ('17', 'Gothic Goddess', 'uploads/hooker/gothic_goddess.jpg', '15000', '1350', '208', '0.0100', '0', '100', '2', '2010-04-21 11:10:31', '2010-04-21 11:10:31'), ('18', 'Pearl', 'uploads/hooker/pearl.jpg', '16500', '1500', '223', '0.0100', '0', '100', '2', '2010-04-21 11:11:10', '2010-04-21 11:11:10'), ('19', 'Miss FBI', 'uploads/hooker/miss_fbi.jpg', '24500', '2100', '270', '0.0100', '0', '100', '2', '2010-04-21 11:12:15', '2010-04-21 11:12:15'), ('20', 'French Maid Fifi', 'uploads/hooker/fifi_french_maid.jpg', '27500', '2400', '295', '0.0100', '0', '100', '2', '2010-04-21 11:13:31', '2010-04-21 11:13:31'), ('21', 'Darling Devil', 'uploads/hooker/darling_devil.jpg', '30000', '3000', '315', '0.0100', '0', '100', '2', '2010-04-21 11:14:12', '2010-04-21 11:14:12'), ('22', 'Sergeant Sexy', 'uploads/hooker/sergeant_sexy.jpg', '55000', '4500', '447', '0.0100', '0', '100', '2', '2010-04-21 11:14:51', '2010-04-21 11:14:51'), ('23', 'Jessica', 'uploads/hooker/jessica.jpg', '63000', '5400', '497', '0.0100', '0', '100', '2', '2010-04-21 11:15:22', '2010-04-21 11:15:22'), ('24', 'Leonard', 'uploads/hooker/leonard.jpg', '70000', '6600', '543', '0.0100', '0', '100', '2', '2010-04-21 11:15:53', '2010-04-21 11:15:53'), ('25', 'Bunnie', 'uploads/hooker/bunnie.jpg', '80000', '7500', '603', '0.0100', '0', '100', '2', '2010-04-21 11:16:25', '2010-04-21 11:16:25'), ('26', 'Mrs. Robinson', 'uploads/hooker/mrs_robinson.jpg', '100000', '9000', '710', '0.0100', '0', '100', '2', '2010-04-21 11:17:22', '2010-04-21 11:17:22'), ('27', 'Mr Love', 'uploads/hooker/mr_love.jpg', '150000', '12000', '967', '0.0100', '0', '100', '2', '2010-04-21 11:18:21', '2010-04-21 11:18:21'), ('28', 'Lill &  Jill', 'uploads/hooker/lill_jill.jpg', '240000', '21000', '1428', '0.0100', '0', '100', '2', '2010-04-21 11:19:25', '2010-04-21 11:19:25'), ('29', 'The Twins', 'uploads/hooker/the_twins.jpg', '430000', '30000', '2395', '0.0100', '0', '100', '2', '2010-04-21 11:20:05', '2010-04-21 11:20:05'), ('30', 'Slim Susy', 'uploads/hooker/slimsusy.jpg', '650000', '42000', '3513', '0.0100', '0', '100', '2', '2010-04-21 11:20:43', '2010-04-21 11:20:43'), ('31', 'SM Babe', 'uploads/hooker/smbabe.jpg', '800000', '54000', '4308', '0.0100', '0', '100', '2', '2010-04-21 11:21:15', '2010-04-21 11:21:15'), ('32', 'Miss Blonde', 'uploads/hooker/missblonde.jpg', '1200000', '72000', '6350', '0.0100', '0', '100', '2', '2010-04-21 11:22:23', '2010-04-21 11:22:23'), ('33', 'Bobbi', 'uploads/hooker/bobbi.jpg', '0', '450', '96', '0.0100', '1', '100', '2', '2010-04-21 11:23:13', '2010-04-21 11:23:13'), ('34', 'Woman of Wonder', 'uploads/hooker/woman_of_wonder.jpg', '9000', '650', '120', '0.0100', '1', '100', '2', '2010-04-21 11:24:31', '2010-04-21 11:24:31'), ('35', 'Rhinogirl', 'uploads/hooker/rhinogirl.jpg', '10000', '720', '150', '0.0100', '1', '100', '20', '2010-04-21 11:25:23', '2010-04-21 11:25:23');

-- ----------------------------
--  Table structure for `system_hospital`
-- ----------------------------
DROP TABLE IF EXISTS `system_hospital`;
CREATE TABLE `system_hospital` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `price` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_hospital`
-- ----------------------------
INSERT INTO `system_hospital` VALUES ('1', '脑激素', 'intelligence', '23', '2010-04-21 14:48:55'), ('2', '性激素', 'charisma', '23', '2010-04-21 14:49:11'), ('3', '肌肉生长素', 'tolerance', '23', '2010-04-21 14:49:26'), ('4', '类固醇', 'strength', '23', '2010-04-21 14:49:38'), ('5', '美沙酮', 'addiction', '500', '2010-04-21 14:49:56');

-- ----------------------------
--  Table structure for `system_province`
-- ----------------------------
DROP TABLE IF EXISTS `system_province`;
CREATE TABLE `system_province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `icon` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_province`
-- ----------------------------
INSERT INTO `system_province` VALUES ('1', '北京', '京'), ('2', '天津', '津'), ('3', '河北', '冀'), ('4', '山西', '晋'), ('5', '内蒙古自治区', '蒙'), ('6', '辽宁', '辽'), ('7', '吉林', '吉'), ('8', '黑龙江', '黑'), ('9', '上海', '沪'), ('10', '江苏', '苏'), ('11', '浙江', '浙'), ('12', '安徽', '皖'), ('13', '福建', '闽'), ('14', '江西', '赣'), ('15', '山东', '鲁'), ('16', '河南', '豫'), ('17', '湖北', '鄂'), ('18', '湖南', '湘'), ('19', '广东', '粤'), ('20', '广西', '桂'), ('21', '海南', '琼'), ('22', '重庆', '渝'), ('23', '四川', '川'), ('24', '贵州', '贵'), ('25', '云南', '滇'), ('26', '西藏', '藏'), ('27', '陕西', '陕'), ('28', '甘肃', '甘'), ('29', '青海', '青'), ('30', '宁夏', '宁'), ('31', '新疆', '新'), ('32', '香港', '港'), ('33', '澳门', '澳'), ('34', '台湾', '台'), ('35', '其它', '?');

-- ----------------------------
--  Table structure for `system_randomevent`
-- ----------------------------
DROP TABLE IF EXISTS `system_randomevent`;
CREATE TABLE `system_randomevent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_randomevent`
-- ----------------------------
INSERT INTO `system_randomevent` VALUES ('1', '一个警察不怀好心的档着你的路', 'uploads/random/title/1.jpg', '2010-04-21 18:51:59'), ('2', '一个醉汉高举酒瓶档着你的路。', 'uploads/random/title/2.jpg', '2010-04-21 18:52:19'), ('3', '一只毒虫拿着针头挡住你的路', 'uploads/random/title/3.jpg', '2010-04-21 18:52:34'), ('4', '一个老伯步履蹒跚拦下你。', 'uploads/random/title/4.jpg', '2010-04-21 18:52:53');

-- ----------------------------
--  Table structure for `system_randomeventchoice`
-- ----------------------------
DROP TABLE IF EXISTS `system_randomeventchoice`;
CREATE TABLE `system_randomeventchoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `randomeventquestion_id` int(11) NOT NULL,
  `answer` varchar(100) NOT NULL,
  `photo` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `system_randomeventchoice_randomeventquestion_id` (`randomeventquestion_id`),
  CONSTRAINT `randomeventquestion_id_refs_id_57cd4132` FOREIGN KEY (`randomeventquestion_id`) REFERENCES `system_randomeventquestion` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_randomeventchoice`
-- ----------------------------
INSERT INTO `system_randomeventchoice` VALUES ('6', '1', '非洲', 'uploads/random/choice/africa.gif'), ('7', '1', '欧洲', 'uploads/random/choice/europe.gif'), ('8', '1', '大洋洲', 'uploads/random/choice/oceania.jpg'), ('9', '1', '南美洲', 'uploads/random/choice/south_america.gif'), ('10', '1', '亚洲', 'uploads/random/choice/asia.gif'), ('11', '2', '3:10', 'uploads/random/choice/03-10.gif'), ('12', '2', '5:00 ', 'uploads/random/choice/05-00.jpg'), ('13', '2', '7:53', 'uploads/random/choice/07-53.gif'), ('14', '2', '10:30', 'uploads/random/choice/10-30.png'), ('15', '2', '11:55', 'uploads/random/choice/11-55.jpg'), ('16', '3', '猴子', 'uploads/random/choice/monkey.jpg'), ('17', '3', '鸟', 'uploads/random/choice/bird.jpg'), ('18', '3', '狗', 'uploads/random/choice/dog.jpg'), ('19', '3', '老虎', 'uploads/random/choice/tiger.jpg'), ('20', '3', '耗子', 'uploads/random/choice/hamster.jpg'), ('21', '4', '沙漠', 'uploads/random/choice/desert.jpg'), ('22', '4', '海洋', 'uploads/random/choice/ocean.jpg'), ('23', '4', '森林', 'uploads/random/choice/forest.jpg'), ('24', '4', '城市', 'uploads/random/choice/city.jpg'), ('25', '4', '北极', 'uploads/random/choice/arctic.gif'), ('26', '5', '渡船', 'uploads/random/choice/ferry.jpg'), ('27', '5', '火车', 'uploads/random/choice/train.jpg'), ('28', '5', '公共汽车', 'uploads/random/choice/bus.jpg'), ('29', '5', '飞机', 'uploads/random/choice/airplane.png'), ('30', '6', '尺', 'uploads/random/choice/ruler.jpg'), ('31', '6', '笔', 'uploads/random/choice/pen.jpg'), ('32', '6', '计算器', 'uploads/random/choice/calculator.jpg'), ('33', '6', '橡皮', 'uploads/random/choice/eraser.jpg'), ('34', '6', '笔记本计算机', 'uploads/random/choice/notebook.jpg');

-- ----------------------------
--  Table structure for `system_randomeventquestion`
-- ----------------------------
DROP TABLE IF EXISTS `system_randomeventquestion`;
CREATE TABLE `system_randomeventquestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_randomeventquestion`
-- ----------------------------
INSERT INTO `system_randomeventquestion` VALUES ('1', '我想我认得样子，你说呢？', '2010-04-21 18:54:07'), ('2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '2010-04-21 18:54:17'), ('3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '2010-04-21 18:54:28'), ('4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '2010-04-21 18:54:37'), ('5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '2010-04-21 18:54:46'), ('6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '2010-04-21 18:54:57');

-- ----------------------------
--  Table structure for `system_robbery`
-- ----------------------------
DROP TABLE IF EXISTS `system_robbery`;
CREATE TABLE `system_robbery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `stamina` smallint(6) NOT NULL,
  `difficulty` smallint(6) NOT NULL,
  `type` varchar(10) NOT NULL,
  `intelligence_min` decimal(10,4) NOT NULL,
  `intelligence_max` decimal(10,4) NOT NULL,
  `strength_min` decimal(10,4) NOT NULL,
  `strength_max` decimal(10,4) NOT NULL,
  `charisma_min` decimal(10,4) NOT NULL,
  `charisma_max` decimal(10,4) NOT NULL,
  `tolerance_min` decimal(10,4) NOT NULL,
  `tolerance_max` decimal(10,4) NOT NULL,
  `cash_min` float NOT NULL,
  `cash_max` float NOT NULL,
  `cash_range` varchar(50) DEFAULT NULL,
  `attribute_range` varchar(50) DEFAULT NULL,
  `members` smallint(6) DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_robbery`
-- ----------------------------
INSERT INTO `system_robbery` VALUES ('1', '偷窃', '5', '3', 'single', '0.2000', '0.5000', '0.2000', '0.5000', '0.2000', '0.5000', '0.2000', '0.5000', '1', '10', '1,10', '0.2,0.5', '0', '2010-04-28 19:06:21'), ('2', '抢老妇人', '10', '10', 'single', '0.5000', '1.5000', '0.5000', '1.5000', '0.5000', '1.5000', '0.5000', '1.5000', '10', '65', '10,65', '0.5,1.5', '0', '2010-04-28 19:09:36'), ('3', '偷汽車', '10', '15', 'single', '0.5000', '2.0000', '0.5000', '2.0000', '0.5000', '2.0000', '0.5000', '2.0000', '40', '110', '40,110', '0.5,2', '0', '2010-04-28 19:10:33'), ('4', '抢出租车', '10', '25', 'single', '1.0000', '4.0000', '1.0000', '4.0000', '1.0000', '4.0000', '1.0000', '4.0000', '90', '200', '90,200', '1,4', '0', '2010-04-28 19:11:17'), ('5', '提款机', '10', '40', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '50', '250', '50,250', '1.5,5.5', '0', '2010-04-28 19:12:11'), ('6', '民宅', '12', '45', 'single', '1.2000', '5.0000', '1.2000', '5.0000', '1.2000', '5.0000', '1.2000', '5.0000', '140', '360', '140,360', '1.2,5', '0', '2010-04-28 19:13:02'), ('7', '加油站', '14', '55', 'single', '1.4000', '4.5000', '1.4000', '4.5000', '1.4000', '4.5000', '1.4000', '4.5000', '150', '550', '150,550', '1.4,4.5', '0', '2010-04-28 19:13:45'), ('8', '戏院', '15', '65', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '300', '700', '300,700', '1.5,5.5', '0', '2010-04-28 19:14:25'), ('9', '杂货店', '16', '70', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '300', '900', '300,900', '1.5,5.5', '0', '2010-04-28 19:15:15'), ('10', '24小时便利商店', '18', '100', 'single', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '400', '1400', '400,1400', '1.5,6', '0', '2010-04-28 19:15:53'), ('11', '绑架', '20', '170', 'single', '0.4000', '1.5000', '0.4000', '1.5000', '0.4000', '1.5000', '0.4000', '1.5000', '1000', '2500', '1000,2500', '0.4,1.5', '0', '2010-04-28 19:16:41'), ('12', '珠宝店', '25', '250', 'single', '0.7000', '5.0000', '0.7000', '5.0000', '0.7000', '5.0000', '0.7000', '5.0000', '1200', '4500', '1200,4500', '0.7,5', '0', '2010-04-28 19:17:24'), ('13', '保险箱', '27', '300', 'single', '0.8000', '3.2000', '0.8000', '3.2000', '0.8000', '3.2000', '0.8000', '3.2000', '2800', '5800', '2800,5800', '0.8,3.2', '0', '2010-04-28 19:18:07'), ('14', '小银行', '30', '370', 'single', '1.0000', '2.1000', '1.0000', '2.1000', '1.0000', '2.1000', '1.0000', '2.1000', '2400', '6500', '2400,6500', '1,2.1', '0', '2010-04-28 19:18:51'), ('15', '帮派小头目', '35', '480', 'single', '1.5000', '3.0000', '1.5000', '3.0000', '1.5000', '3.0000', '1.5000', '3.0000', '5000', '12000', '5000,12000', '1.5,3', '0', '2010-04-28 19:19:29'), ('16', '汽车沙龙', '40', '570', 'single', '1.5000', '5.7000', '1.5000', '5.7000', '1.5000', '5.7000', '1.5000', '5.7000', '5000', '17000', '5000,17000', '1.5,5.7', '0', '2010-04-28 19:20:12'), ('17', 'PayPal', '45', '640', 'single', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '5500', '15000', '5500,15000', '1.5,6', '0', '2010-04-28 19:21:00'), ('18', '地痞', '50', '770', 'single', '4.0000', '6.0000', '4.0000', '6.0000', '4.0000', '6.0000', '4.0000', '6.0000', '8000', '10000', '8000,10000', '4,6', '0', '2010-04-28 19:21:41'), ('19', '小药头', '60', '880', 'single', '5.0000', '6.0000', '5.0000', '6.0000', '5.0000', '6.0000', '5.0000', '6.0000', '10000', '15000', '10000,15000', '5,6', '0', '2010-04-28 19:22:21'), ('20', '赌场', '65', '980', 'single', '5.0000', '7.0000', '5.0000', '7.0000', '5.0000', '7.0000', '5.0000', '7.0000', '15000', '30000', '15000,30000', '5,7', '0', '2010-04-28 19:22:50'), ('21', '狂欢会', '70', '1150', 'single', '6.0000', '7.0000', '6.0000', '7.0000', '6.0000', '7.0000', '6.0000', '7.0000', '30000', '50000', '30000,50000', '6,7', '0', '2010-04-28 19:23:18'), ('22', '超级市场', '75', '1430', 'single', '1.2000', '6.0000', '1.2000', '6.0000', '1.2000', '6.0000', '1.2000', '6.0000', '35000', '80000', '35000,80000', '1.2,6', '0', '2010-04-28 19:23:56'), ('23', '博物馆', '80', '2700', 'single', '8.0000', '15.0000', '8.0000', '15.0000', '8.0000', '15.0000', '8.0000', '15.0000', '80000', '150000', '80000,150000', '8,15', '0', '2010-04-28 19:24:41'), ('24', '俄国药王', '80', '3200', 'single', '10.0000', '20.0000', '10.0000', '20.0000', '10.0000', '20.0000', '10.0000', '20.0000', '150000', '300000', '150000,300000', '10,20', '0', '2010-04-28 19:25:18'), ('25', '外币', '30', '70', 'gang', '0.5000', '3.5000', '0.5000', '3.5000', '0.5000', '3.5000', '0.5000', '3.5000', '4000', '14000', '4000,14000', '0.5,3.5', '2', '2010-04-28 19:26:13'), ('26', '银行', '30', '160', 'gang', '5.0000', '10.0000', '5.0000', '10.0000', '5.0000', '10.0000', '5.0000', '10.0000', '8000', '11000', '8000,11000', '5,10', '4', '2010-04-28 19:27:04'), ('27', '运钞车', '30', '300', 'gang', '6.0000', '12.0000', '6.0000', '12.0000', '6.0000', '12.0000', '6.0000', '12.0000', '12000', '15000', '12000,15000', '6,12', '3', '2010-04-28 19:27:44'), ('28', '中央储备银行', '30', '900', 'gang', '25.0000', '40.0000', '25.0000', '40.0000', '25.0000', '40.0000', '25.0000', '40.0000', '100000', '150000', '100000,150000', '25,40', '6', '2010-04-28 19:28:43'), ('29', '杜月笙', '30', '2000', 'gang', '40.0000', '65.0000', '40.0000', '65.0000', '40.0000', '65.0000', '40.0000', '65.0000', '30000', '50000', '30000,50000', '40,65', '7', '2010-04-28 19:29:31'), ('30', '操纵股市', '30', '2500', 'gang', '50.0000', '80.0000', '50.0000', '80.0000', '50.0000', '80.0000', '50.0000', '80.0000', '50000', '80000', '50000,80000', '50,80', '9', '2010-04-28 19:30:11'), ('31', '黄金荣', '80', '4500', 'gang', '80.0000', '150.0000', '80.0000', '150.0000', '80.0000', '150.0000', '80.0000', '150.0000', '80000', '150000', '80000,150000', '80,150', '10', '2010-04-28 19:30:54'), ('32', '他妈的烂地方', '80', '8000', 'gang', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '150000', '300000', '150000,300000', '100,200', '14', '2010-04-28 19:31:37'), ('33', '黄埔军校', '80', '15000', 'gang', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '150000', '300000', '150000,300000', '100,200', '17', '2010-04-28 19:32:20');

-- ----------------------------
--  Table structure for `system_sabotage`
-- ----------------------------
DROP TABLE IF EXISTS `system_sabotage`;
CREATE TABLE `system_sabotage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `stamina` smallint(6) NOT NULL,
  `difficulty` smallint(6) NOT NULL,
  `expend` smallint(6) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_sabotage`
-- ----------------------------
INSERT INTO `system_sabotage` VALUES ('1', '炸了他的工厂', '15', '1000', '500', '2010-05-04 10:28:37'), ('2', '抢走他的小姐', '10', '200', '100', '2010-05-04 10:28:58'), ('3', '砸了他的场子', '20', '2500', '1250', '2010-05-04 10:29:24');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `system_weapon`
-- ----------------------------
INSERT INTO `system_weapon` VALUES ('1', '球棒', 'uploads/weapon/monkeybat.jpg', '120', '8', '10', '1', '1', 'melee', '12', '2010-04-21 11:31:34'), ('2', '匕首', 'uploads/weapon/knife.jpg', '300', '8', '20', '1', '10', 'melee', '30', '2010-04-21 11:35:34'), ('3', '剑', 'uploads/weapon/sword.jpg', '600', '15', '25', '1', '30', 'melee', '60', '2010-04-21 11:36:29'), ('4', '链锯', 'uploads/weapon/chainsaw.jpg', '660', '12', '30', '10', '50', 'melee', '66', '2010-04-21 11:37:20'), ('5', '格洛克', 'uploads/weapon/glock.jpg', '1350', '20', '40', '20', '40', 'handgun', '135', '2010-04-21 11:38:16'), ('6', '散弹枪', 'uploads/weapon/shotgun.jpg', '3100', '26', '65', '100', '50', 'rifle', '310', '2010-04-21 11:39:13'), ('7', 'MP5冲锋枪', 'uploads/weapon/mp5.jpg', '4700', '42', '70', '150', '100', 'handgun', '470', '2010-04-21 11:40:44'), ('8', 'AK 47', 'uploads/weapon/ak47.jpg', '5400', '45', '75', '200', '130', 'rifle', '540', '2010-04-21 11:41:45'), ('9', '乌兹冲锋枪', 'uploads/weapon/uzi.jpg', '6300', '30', '100', '250', '200', 'handgun', '630', '2010-04-21 11:42:30'), ('10', 'M4A1', 'uploads/weapon/coltm4a1.jpg', '6800', '45', '90', '300', '220', 'rifle', '680', '2010-04-21 11:43:30'), ('11', '沙漠之鹰', 'uploads/weapon/deagle.jpg', '8800', '68', '85', '400', '300', 'handgun', '880', '2010-04-21 11:44:48'), ('12', '重型狙击枪', 'uploads/weapon/sniper.jpg', '18000', '110', '110', '700', '500', 'rifle', '180', '2010-04-21 11:45:50'), ('13', '激光枪', 'uploads/weapon/raygun.jpg', '85000', '180', '300', '1000', '800', 'handgun', '850', '2010-04-21 11:47:14'), ('14', '重机枪', 'uploads/weapon/machine_gun.jpg', '210000', '250', '500', '1500', '800', 'heavy', '210', '2010-04-21 11:48:07'), ('15', '火箭筒', 'uploads/weapon/bazooka.jpg', '695000', '560', '800', '2000', '1500', 'heavy', '695', '2010-04-21 11:49:27'), ('16', '盖利步枪', 'uploads/weapon/gail.jpg', '1560000', '840', '1200', '2800', '1500', 'rifle', '1560', '2010-04-21 11:50:31'), ('17', '多管连发手枪', 'uploads/weapon/bfg.jpg', '2400000', '720', '1800', '3000', '1750', 'handgun', '2400', '2010-04-21 11:51:24'), ('18', '地狱火神炮', 'uploads/weapon/extreme.jpg', '4400000', '1400', '2000', '4500', '2200', 'heavy', '4400', '2010-04-21 11:52:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_business_user_drug`
-- ----------------------------
DROP TABLE IF EXISTS `user_business_user_drug`;
CREATE TABLE `user_business_user_drug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userbusiness_id` int(11) NOT NULL,
  `userdrug_id` int(11) NOT NULL,
  `price` smallint(6) NOT NULL,
  `sold` smallint(6) NOT NULL,
  `removed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_business_user_drug_userbusiness_id` (`userbusiness_id`),
  KEY `user_business_user_drug_userdrug_id` (`userdrug_id`),
  CONSTRAINT `userbusiness_id_refs_id_752cf6a8` FOREIGN KEY (`userbusiness_id`) REFERENCES `user_business` (`id`),
  CONSTRAINT `userdrug_id_refs_id_24c92c50` FOREIGN KEY (`userdrug_id`) REFERENCES `user_drug` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_data`
-- ----------------------------
DROP TABLE IF EXISTS `user_data`;
CREATE TABLE `user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `respect` double NOT NULL,
  `spirit` smallint(6) NOT NULL,
  `intelligence` double NOT NULL,
  `strength` double NOT NULL,
  `charisma` double NOT NULL,
  `tolerance` double NOT NULL,
  `stamina` smallint(6) NOT NULL,
  `cash` int(11) NOT NULL,
  `credits` int(11) NOT NULL,
  `kills` smallint(6) NOT NULL,
  `tickets` smallint(6) NOT NULL,
  `skill` varchar(100) NOT NULL,
  `proficiency` varchar(100) NOT NULL,
  `new_status` varchar(100) NOT NULL,
  `stamina_start_time` datetime DEFAULT NULL,
  `drug_deals` smallint(6) NOT NULL,
  `yen` smallint(6) NOT NULL,
  `in_where` varchar(10) DEFAULT NULL,
  `release_time` datetime DEFAULT NULL,
  `change_character_id` int(11) DEFAULT NULL,
  `change_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `user_data_change_character_id` (`change_character_id`),
  CONSTRAINT `change_character_id_refs_id_5e98066b` FOREIGN KEY (`change_character_id`) REFERENCES `system_character` (`id`),
  CONSTRAINT `user_id_refs_id_76277af7` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_profile`
-- ----------------------------
DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(1) NOT NULL,
  `presentation` longtext NOT NULL,
  `gb_open` tinyint(1) NOT NULL,
  `language` varchar(10) NOT NULL,
  `visitors` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  `character_id` int(11) NOT NULL,
  `rabbit_mode` tinyint(1) NOT NULL,
  `step` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `user_profile_province_id` (`province_id`),
  KEY `user_profile_character_id` (`character_id`),
  CONSTRAINT `character_id_refs_id_41dbc63b` FOREIGN KEY (`character_id`) REFERENCES `system_character` (`id`),
  CONSTRAINT `province_id_refs_id_71e726bb` FOREIGN KEY (`province_id`) REFERENCES `system_province` (`id`),
  CONSTRAINT `user_id_refs_id_5f4bba6f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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

