/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : crims

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-05-07 17:33:35
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `permission_id_refs_id_a7792de1` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8;

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
INSERT INTO `auth_permission` VALUES ('97', 'Can add province', '33', 'add_province');
INSERT INTO `auth_permission` VALUES ('98', 'Can change province', '33', 'change_province');
INSERT INTO `auth_permission` VALUES ('99', 'Can delete province', '33', 'delete_province');
INSERT INTO `auth_permission` VALUES ('100', 'Can add event', '34', 'add_event');
INSERT INTO `auth_permission` VALUES ('101', 'Can change event', '34', 'change_event');
INSERT INTO `auth_permission` VALUES ('102', 'Can delete event', '34', 'delete_event');
INSERT INTO `auth_permission` VALUES ('103', 'Can add benefit', '35', 'add_benefit');
INSERT INTO `auth_permission` VALUES ('104', 'Can change benefit', '35', 'change_benefit');
INSERT INTO `auth_permission` VALUES ('105', 'Can delete benefit', '35', 'delete_benefit');
INSERT INTO `auth_permission` VALUES ('106', 'Can add hospital', '36', 'add_hospital');
INSERT INTO `auth_permission` VALUES ('107', 'Can change hospital', '36', 'change_hospital');
INSERT INTO `auth_permission` VALUES ('108', 'Can delete hospital', '36', 'delete_hospital');
INSERT INTO `auth_permission` VALUES ('109', 'Can add random event', '37', 'add_randomevent');
INSERT INTO `auth_permission` VALUES ('110', 'Can change random event', '37', 'change_randomevent');
INSERT INTO `auth_permission` VALUES ('111', 'Can delete random event', '37', 'delete_randomevent');
INSERT INTO `auth_permission` VALUES ('112', 'Can add random event question', '38', 'add_randomeventquestion');
INSERT INTO `auth_permission` VALUES ('113', 'Can change random event question', '38', 'change_randomeventquestion');
INSERT INTO `auth_permission` VALUES ('114', 'Can delete random event question', '38', 'delete_randomeventquestion');
INSERT INTO `auth_permission` VALUES ('115', 'Can add random event choice', '39', 'add_randomeventchoice');
INSERT INTO `auth_permission` VALUES ('116', 'Can change random event choice', '39', 'change_randomeventchoice');
INSERT INTO `auth_permission` VALUES ('117', 'Can delete random event choice', '39', 'delete_randomeventchoice');
INSERT INTO `auth_permission` VALUES ('118', 'Can add Robberies', '40', 'add_robbery');
INSERT INTO `auth_permission` VALUES ('119', 'Can change Robberies', '40', 'change_robbery');
INSERT INTO `auth_permission` VALUES ('120', 'Can delete Robberies', '40', 'delete_robbery');
INSERT INTO `auth_permission` VALUES ('121', 'Can add Message', '41', 'add_message');
INSERT INTO `auth_permission` VALUES ('122', 'Can change Message', '41', 'change_message');
INSERT INTO `auth_permission` VALUES ('123', 'Can delete Message', '41', 'delete_message');
INSERT INTO `auth_permission` VALUES ('124', 'Can add Gang', '42', 'add_gang');
INSERT INTO `auth_permission` VALUES ('125', 'Can change Gang', '42', 'change_gang');
INSERT INTO `auth_permission` VALUES ('126', 'Can delete Gang', '42', 'delete_gang');
INSERT INTO `auth_permission` VALUES ('136', 'Can add Sabotage', '46', 'add_sabotage');
INSERT INTO `auth_permission` VALUES ('137', 'Can change Sabotage', '46', 'change_sabotage');
INSERT INTO `auth_permission` VALUES ('138', 'Can delete Sabotage', '46', 'delete_sabotage');
INSERT INTO `auth_permission` VALUES ('139', 'Can add Bounty', '47', 'add_bounty');
INSERT INTO `auth_permission` VALUES ('140', 'Can change Bounty', '47', 'change_bounty');
INSERT INTO `auth_permission` VALUES ('141', 'Can delete Bounty', '47', 'delete_bounty');
INSERT INTO `auth_permission` VALUES ('142', 'Can add Bank', '48', 'add_bank');
INSERT INTO `auth_permission` VALUES ('143', 'Can change Bank', '48', 'change_bank');
INSERT INTO `auth_permission` VALUES ('144', 'Can delete Bank', '48', 'delete_bank');
INSERT INTO `auth_permission` VALUES ('148', 'Can add contact', '50', 'add_contact');
INSERT INTO `auth_permission` VALUES ('149', 'Can change contact', '50', 'change_contact');
INSERT INTO `auth_permission` VALUES ('150', 'Can delete contact', '50', 'delete_contact');
INSERT INTO `auth_permission` VALUES ('151', 'Can add friendship', '51', 'add_friendship');
INSERT INTO `auth_permission` VALUES ('152', 'Can change friendship', '51', 'change_friendship');
INSERT INTO `auth_permission` VALUES ('153', 'Can delete friendship', '51', 'delete_friendship');
INSERT INTO `auth_permission` VALUES ('154', 'Can add join invitation', '52', 'add_joininvitation');
INSERT INTO `auth_permission` VALUES ('155', 'Can change join invitation', '52', 'change_joininvitation');
INSERT INTO `auth_permission` VALUES ('156', 'Can delete join invitation', '52', 'delete_joininvitation');
INSERT INTO `auth_permission` VALUES ('157', 'Can add friendship invitation', '53', 'add_friendshipinvitation');
INSERT INTO `auth_permission` VALUES ('158', 'Can change friendship invitation', '53', 'change_friendshipinvitation');
INSERT INTO `auth_permission` VALUES ('159', 'Can delete friendship invitation', '53', 'delete_friendshipinvitation');
INSERT INTO `auth_permission` VALUES ('160', 'Can add friendship invitation history', '54', 'add_friendshipinvitationhistory');
INSERT INTO `auth_permission` VALUES ('161', 'Can change friendship invitation history', '54', 'change_friendshipinvitationhistory');
INSERT INTO `auth_permission` VALUES ('162', 'Can delete friendship invitation history', '54', 'delete_friendshipinvitationhistory');
INSERT INTO `auth_permission` VALUES ('163', 'Can add notice type', '55', 'add_noticetype');
INSERT INTO `auth_permission` VALUES ('164', 'Can change notice type', '55', 'change_noticetype');
INSERT INTO `auth_permission` VALUES ('165', 'Can delete notice type', '55', 'delete_noticetype');
INSERT INTO `auth_permission` VALUES ('166', 'Can add notice setting', '56', 'add_noticesetting');
INSERT INTO `auth_permission` VALUES ('167', 'Can change notice setting', '56', 'change_noticesetting');
INSERT INTO `auth_permission` VALUES ('168', 'Can delete notice setting', '56', 'delete_noticesetting');
INSERT INTO `auth_permission` VALUES ('169', 'Can add notice', '57', 'add_notice');
INSERT INTO `auth_permission` VALUES ('170', 'Can change notice', '57', 'change_notice');
INSERT INTO `auth_permission` VALUES ('171', 'Can delete notice', '57', 'delete_notice');
INSERT INTO `auth_permission` VALUES ('172', 'Can add notice queue batch', '58', 'add_noticequeuebatch');
INSERT INTO `auth_permission` VALUES ('173', 'Can change notice queue batch', '58', 'change_noticequeuebatch');
INSERT INTO `auth_permission` VALUES ('174', 'Can delete notice queue batch', '58', 'delete_noticequeuebatch');
INSERT INTO `auth_permission` VALUES ('175', 'Can add observed item', '59', 'add_observeditem');
INSERT INTO `auth_permission` VALUES ('176', 'Can change observed item', '59', 'change_observeditem');
INSERT INTO `auth_permission` VALUES ('177', 'Can delete observed item', '59', 'delete_observeditem');
INSERT INTO `auth_permission` VALUES ('178', 'Can add Relationship status', '60', 'add_relationshipstatus');
INSERT INTO `auth_permission` VALUES ('179', 'Can change Relationship status', '60', 'change_relationshipstatus');
INSERT INTO `auth_permission` VALUES ('180', 'Can delete Relationship status', '60', 'delete_relationshipstatus');
INSERT INTO `auth_permission` VALUES ('181', 'Can add Relationship', '61', 'add_relationship');
INSERT INTO `auth_permission` VALUES ('182', 'Can change Relationship', '61', 'change_relationship');
INSERT INTO `auth_permission` VALUES ('183', 'Can delete Relationship', '61', 'delete_relationship');
INSERT INTO `auth_permission` VALUES ('187', 'Can add chat', '63', 'add_chat');
INSERT INTO `auth_permission` VALUES ('188', 'Can change chat', '63', 'change_chat');
INSERT INTO `auth_permission` VALUES ('189', 'Can delete chat', '63', 'delete_chat');
INSERT INTO `auth_permission` VALUES ('199', 'Can add invite', '67', 'add_ganginvite');
INSERT INTO `auth_permission` VALUES ('200', 'Can change invite', '67', 'change_ganginvite');
INSERT INTO `auth_permission` VALUES ('201', 'Can delete invite', '67', 'delete_ganginvite');
INSERT INTO `auth_permission` VALUES ('202', 'Can add member', '68', 'add_gangmember');
INSERT INTO `auth_permission` VALUES ('203', 'Can change member', '68', 'change_gangmember');
INSERT INTO `auth_permission` VALUES ('204', 'Can delete member', '68', 'delete_gangmember');
INSERT INTO `auth_permission` VALUES ('205', 'Can add news', '69', 'add_gangnews');
INSERT INTO `auth_permission` VALUES ('206', 'Can change news', '69', 'change_gangnews');
INSERT INTO `auth_permission` VALUES ('207', 'Can delete news', '69', 'delete_gangnews');
INSERT INTO `auth_permission` VALUES ('208', 'Can add robbery', '70', 'add_gangrobbery');
INSERT INTO `auth_permission` VALUES ('209', 'Can change robbery', '70', 'change_gangrobbery');
INSERT INTO `auth_permission` VALUES ('210', 'Can delete robbery', '70', 'delete_gangrobbery');
INSERT INTO `auth_permission` VALUES ('211', 'Can add assault', '71', 'add_gangassault');
INSERT INTO `auth_permission` VALUES ('212', 'Can change assault', '71', 'change_gangassault');
INSERT INTO `auth_permission` VALUES ('213', 'Can delete assault', '71', 'delete_gangassault');
INSERT INTO `auth_permission` VALUES ('214', 'Can add user business drugs', '72', 'add_userbusinessdrug');
INSERT INTO `auth_permission` VALUES ('215', 'Can change user business drugs', '72', 'change_userbusinessdrug');
INSERT INTO `auth_permission` VALUES ('216', 'Can delete user business drugs', '72', 'delete_userbusinessdrug');
INSERT INTO `auth_permission` VALUES ('217', 'Can add guestbook', '73', 'add_guestbook');
INSERT INTO `auth_permission` VALUES ('218', 'Can change guestbook', '73', 'change_guestbook');
INSERT INTO `auth_permission` VALUES ('219', 'Can delete guestbook', '73', 'delete_guestbook');
INSERT INTO `auth_permission` VALUES ('220', 'Can add prison', '74', 'add_prison');
INSERT INTO `auth_permission` VALUES ('221', 'Can change prison', '74', 'change_prison');
INSERT INTO `auth_permission` VALUES ('222', 'Can delete prison', '74', 'delete_prison');
INSERT INTO `auth_permission` VALUES ('223', 'Can add rip', '75', 'add_rip');
INSERT INTO `auth_permission` VALUES ('224', 'Can change rip', '75', 'change_rip');
INSERT INTO `auth_permission` VALUES ('225', 'Can delete rip', '75', 'delete_rip');
INSERT INTO `auth_permission` VALUES ('226', 'Can add profile', '76', 'add_userprofile');
INSERT INTO `auth_permission` VALUES ('227', 'Can change profile', '76', 'change_userprofile');
INSERT INTO `auth_permission` VALUES ('228', 'Can delete profile', '76', 'delete_userprofile');
INSERT INTO `auth_permission` VALUES ('229', 'Can add game data', '77', 'add_userdata');
INSERT INTO `auth_permission` VALUES ('230', 'Can change game data', '77', 'change_userdata');
INSERT INTO `auth_permission` VALUES ('231', 'Can delete game data', '77', 'delete_userdata');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'c-mtv@163.com', 'sha1$df48b$97ce6e8393b8ff135c3e794d6e847d6962ea6da8', '1', '1', '1', '2010-05-07 16:40:33', '2010-04-18 20:07:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=289 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=479 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_admin_log` VALUES ('12', '2010-04-19 16:13:30', '1', '33', '1', '帝都', '1', '');
INSERT INTO `django_admin_log` VALUES ('13', '2010-04-19 16:13:54', '1', '33', '1', '帝都', '3', '');
INSERT INTO `django_admin_log` VALUES ('14', '2010-04-19 18:33:51', '1', '17', '5', 'uploads/avatar/blue_hills_thumbnail.jpg', '3', '');
INSERT INTO `django_admin_log` VALUES ('15', '2010-04-19 18:33:51', '1', '17', '4', 'uploads/avatar/sunset_thumbnail.jpg', '3', '');
INSERT INTO `django_admin_log` VALUES ('16', '2010-04-19 18:33:51', '1', '17', '3', 'uploads/avatar/water_lilies_thumbnail.jpg', '3', '');
INSERT INTO `django_admin_log` VALUES ('17', '2010-04-19 18:33:51', '1', '17', '2', 'uploads/avatar/winter_thumbnail.jpg', '3', '');
INSERT INTO `django_admin_log` VALUES ('18', '2010-04-19 18:40:13', '1', '17', '6', 'uploads/avatar/avatar_1.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('19', '2010-04-19 18:41:51', '1', '17', '7', 'uploads/avatar/avatar_2.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('20', '2010-04-19 18:42:15', '1', '17', '8', 'uploads/avatar/avatar_4.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('21', '2010-04-19 18:42:22', '1', '17', '9', 'uploads/avatar/avatar_5.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('22', '2010-04-19 18:42:28', '1', '17', '10', 'uploads/avatar/avatar_6.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('23', '2010-04-19 18:42:36', '1', '17', '11', 'uploads/avatar/avatar_7.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('24', '2010-04-19 18:42:44', '1', '17', '12', 'uploads/avatar/avatar_8.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('25', '2010-04-19 18:42:51', '1', '17', '13', 'uploads/avatar/avatar_9.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('26', '2010-04-19 18:42:59', '1', '17', '14', 'uploads/avatar/avatar_10.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('27', '2010-04-19 18:43:07', '1', '17', '15', 'uploads/avatar/avatar_11.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('28', '2010-04-19 18:43:16', '1', '17', '16', 'uploads/avatar/avatar_12.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('29', '2010-04-19 18:43:23', '1', '17', '17', 'uploads/avatar/avatar_13.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('30', '2010-04-19 18:43:32', '1', '17', '18', 'uploads/avatar/avatar_14.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('31', '2010-04-19 18:43:40', '1', '17', '19', 'uploads/avatar/avatar_15.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('32', '2010-04-19 18:43:49', '1', '17', '20', 'uploads/avatar/avatar_16.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('33', '2010-04-19 18:43:58', '1', '17', '21', 'uploads/avatar/avatar_17.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('34', '2010-04-19 18:44:10', '1', '17', '22', 'uploads/avatar/avatar_18.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('35', '2010-04-19 18:44:20', '1', '17', '23', 'uploads/avatar/avatar_19.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('36', '2010-04-19 18:44:29', '1', '17', '24', 'uploads/avatar/avatar_26.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('37', '2010-04-19 18:44:37', '1', '17', '25', 'uploads/avatar/avatar_27.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('38', '2010-04-19 18:44:46', '1', '17', '26', 'uploads/avatar/avatar_28.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('39', '2010-04-19 18:44:53', '1', '17', '27', 'uploads/avatar/avatar_29.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('40', '2010-04-19 18:45:02', '1', '17', '28', 'uploads/avatar/avatar_30.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('41', '2010-04-19 18:45:11', '1', '17', '29', 'uploads/avatar/avatar_31.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('42', '2010-04-19 18:45:19', '1', '17', '30', 'uploads/avatar/avatar_32.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('43', '2010-04-19 18:45:27', '1', '17', '31', 'uploads/avatar/avatar_33.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('44', '2010-04-19 18:45:37', '1', '17', '32', 'uploads/avatar/avatar_34.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('45', '2010-04-19 18:45:44', '1', '17', '33', 'uploads/avatar/avatar_35.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('46', '2010-04-19 18:45:51', '1', '17', '34', 'uploads/avatar/avatar_36.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('47', '2010-04-19 18:45:57', '1', '17', '35', 'uploads/avatar/avatar_37.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('48', '2010-04-19 18:46:03', '1', '17', '36', 'uploads/avatar/avatar_38.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('49', '2010-04-19 18:46:11', '1', '17', '37', 'uploads/avatar/avatar_39.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('50', '2010-04-19 18:46:17', '1', '17', '38', 'uploads/avatar/avatar_40.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('51', '2010-04-19 18:46:22', '1', '17', '39', 'uploads/avatar/avatar_41.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('52', '2010-04-19 18:46:28', '1', '17', '40', 'uploads/avatar/avatar_42.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('53', '2010-04-19 18:46:35', '1', '17', '41', 'uploads/avatar/avatar_43.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('54', '2010-04-19 18:46:41', '1', '17', '42', 'uploads/avatar/avatar_44.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('55', '2010-04-19 18:46:47', '1', '17', '43', 'uploads/avatar/avatar_45.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('56', '2010-04-19 18:46:52', '1', '17', '44', 'uploads/avatar/avatar_46.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('57', '2010-04-19 18:46:57', '1', '17', '45', 'uploads/avatar/avatar_47.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('58', '2010-04-19 18:47:02', '1', '17', '46', 'uploads/avatar/avatar_48.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('59', '2010-04-19 18:47:07', '1', '17', '47', 'uploads/avatar/avatar_49.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('60', '2010-04-19 18:47:12', '1', '17', '48', 'uploads/avatar/avatar_50.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('61', '2010-04-19 18:47:21', '1', '17', '49', 'uploads/avatar/avatar_51.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('62', '2010-04-19 18:47:27', '1', '17', '50', 'uploads/avatar/avatar_52.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('63', '2010-04-19 18:47:33', '1', '17', '51', 'uploads/avatar/avatar_53.jpg', '1', '');
INSERT INTO `django_admin_log` VALUES ('64', '2010-04-19 19:05:42', '1', '18', '1', 'Disper', '1', '');
INSERT INTO `django_admin_log` VALUES ('65', '2010-04-19 19:06:07', '1', '18', '2', '皮夹克', '1', '');
INSERT INTO `django_admin_log` VALUES ('66', '2010-04-19 19:06:29', '1', '18', '3', '合金盔甲', '1', '');
INSERT INTO `django_admin_log` VALUES ('67', '2010-04-19 19:07:12', '1', '18', '4', '防弹背心', '1', '');
INSERT INTO `django_admin_log` VALUES ('68', '2010-04-19 19:07:48', '1', '18', '5', '纳米战斗衣', '1', '');
INSERT INTO `django_admin_log` VALUES ('69', '2010-04-19 19:08:18', '1', '18', '6', '反恐特勤装', '1', '');
INSERT INTO `django_admin_log` VALUES ('70', '2010-04-19 19:08:27', '1', '18', '1', '尿布', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('71', '2010-04-19 19:11:53', '1', '20', '1', '香烟', '1', '');
INSERT INTO `django_admin_log` VALUES ('72', '2010-04-19 19:12:26', '1', '20', '2', '止痛药', '1', '');
INSERT INTO `django_admin_log` VALUES ('73', '2010-04-19 19:13:13', '1', '20', '3', '酒', '1', '');
INSERT INTO `django_admin_log` VALUES ('74', '2010-04-19 19:13:41', '1', '20', '4', '迷幻蘑菇', '1', '');
INSERT INTO `django_admin_log` VALUES ('75', '2010-04-19 19:14:32', '1', '20', '5', '迷奸药', '1', '');
INSERT INTO `django_admin_log` VALUES ('76', '2010-04-19 19:15:06', '1', '20', '6', '大麻', '1', '');
INSERT INTO `django_admin_log` VALUES ('77', '2010-04-19 19:15:42', '1', '20', '7', '迷幻药', '1', '');
INSERT INTO `django_admin_log` VALUES ('78', '2010-04-19 19:19:41', '1', '20', '8', '迷奸药', '1', '');
INSERT INTO `django_admin_log` VALUES ('79', '2010-04-19 19:26:26', '1', '20', '1', '止痛药', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('80', '2010-04-19 19:26:40', '1', '20', '2', '香烟', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('81', '2010-04-19 19:27:04', '1', '20', '5', '大麻', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('82', '2010-04-19 19:27:32', '1', '20', '6', '迷幻药', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('83', '2010-04-19 19:27:54', '1', '20', '7', '迷奸药', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('84', '2010-04-19 19:28:24', '1', '20', '8', '摇头丸', '2', '已修改 title 和 price 。');
INSERT INTO `django_admin_log` VALUES ('85', '2010-04-19 19:28:48', '1', '20', '9', '安非他命', '1', '');
INSERT INTO `django_admin_log` VALUES ('86', '2010-04-19 19:29:06', '1', '20', '10', '鸦片', '1', '');
INSERT INTO `django_admin_log` VALUES ('87', '2010-04-19 19:29:22', '1', '20', '11', '可卡因', '1', '');
INSERT INTO `django_admin_log` VALUES ('88', '2010-04-19 19:29:37', '1', '20', '12', 'K他命', '1', '');
INSERT INTO `django_admin_log` VALUES ('89', '2010-04-19 19:29:58', '1', '20', '13', '吗啡', '1', '');
INSERT INTO `django_admin_log` VALUES ('90', '2010-04-19 19:30:17', '1', '20', '14', '海洛英', '1', '');
INSERT INTO `django_admin_log` VALUES ('91', '2010-04-19 19:36:20', '1', '21', '1', '烟草', '1', '');
INSERT INTO `django_admin_log` VALUES ('92', '2010-04-19 19:37:10', '1', '21', '2', '私酒', '1', '');
INSERT INTO `django_admin_log` VALUES ('93', '2010-04-19 19:37:45', '1', '21', '3', '大麻种植地', '1', '');
INSERT INTO `django_admin_log` VALUES ('94', '2010-04-19 19:38:21', '1', '21', '4', '酿酒厂', '1', '');
INSERT INTO `django_admin_log` VALUES ('95', '2010-04-19 19:38:53', '1', '21', '5', '药房', '1', '');
INSERT INTO `django_admin_log` VALUES ('96', '2010-04-19 19:39:26', '1', '21', '6', '蘑菇种植基地', '1', '');
INSERT INTO `django_admin_log` VALUES ('97', '2010-04-19 19:40:01', '1', '21', '7', '烟草种植园', '1', '');
INSERT INTO `django_admin_log` VALUES ('98', '2010-04-19 19:40:31', '1', '21', '8', '吗啡实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('99', '2010-04-19 19:41:12', '1', '21', '9', '迷幻药实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('100', '2010-04-19 19:41:45', '1', '21', '10', '摇头丸实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('101', '2010-04-19 19:42:13', '1', '21', '11', '罂粟田', '1', '');
INSERT INTO `django_admin_log` VALUES ('102', '2010-04-19 19:42:53', '1', '21', '12', '迷奸药实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('103', '2010-04-19 19:43:31', '1', '21', '13', 'K他命实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('104', '2010-04-19 19:44:05', '1', '21', '14', '可卡因工厂', '1', '');
INSERT INTO `django_admin_log` VALUES ('105', '2010-04-19 19:44:39', '1', '21', '15', '安非他命实验室', '1', '');
INSERT INTO `django_admin_log` VALUES ('106', '2010-04-19 19:45:15', '1', '21', '16', '海洛英工厂', '1', '');
INSERT INTO `django_admin_log` VALUES ('107', '2010-04-19 19:50:16', '1', '27', '1', '夜总会', '1', '');
INSERT INTO `django_admin_log` VALUES ('108', '2010-04-19 19:51:00', '1', '27', '2', '狂欢派对', '1', '');
INSERT INTO `django_admin_log` VALUES ('109', '2010-04-19 19:51:41', '1', '27', '3', '青楼', '1', '');
INSERT INTO `django_admin_log` VALUES ('110', '2010-04-19 19:52:14', '1', '27', '4', '怡红院', '1', '');
INSERT INTO `django_admin_log` VALUES ('111', '2010-04-19 19:55:42', '1', '19', '1', '领班', '1', '');
INSERT INTO `django_admin_log` VALUES ('112', '2010-04-19 19:56:13', '1', '19', '2', '杀手', '1', '');
INSERT INTO `django_admin_log` VALUES ('113', '2010-04-19 19:56:46', '1', '19', '3', '商人', '1', '');
INSERT INTO `django_admin_log` VALUES ('114', '2010-04-19 19:57:21', '1', '19', '4', '强盗', '1', '');
INSERT INTO `django_admin_log` VALUES ('115', '2010-04-19 19:57:43', '1', '19', '5', '黑社会', '1', '');
INSERT INTO `django_admin_log` VALUES ('116', '2010-04-20 09:34:22', '1', '12', '1', 'root', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('117', '2010-04-21 10:36:03', '1', '35', '1', '妓女赚钱加速', '1', '');
INSERT INTO `django_admin_log` VALUES ('118', '2010-04-21 10:36:33', '1', '35', '2', '武器增强器', '1', '');
INSERT INTO `django_admin_log` VALUES ('119', '2010-04-21 10:37:06', '1', '35', '3', '产量加速器', '1', '');
INSERT INTO `django_admin_log` VALUES ('120', '2010-04-21 10:37:19', '1', '35', '4', '避孕套', '1', '');
INSERT INTO `django_admin_log` VALUES ('121', '2010-04-21 10:45:33', '1', '29', '1', '恶狗', '1', '');
INSERT INTO `django_admin_log` VALUES ('122', '2010-04-21 10:46:07', '1', '29', '2', '杀手', '1', '');
INSERT INTO `django_admin_log` VALUES ('123', '2010-04-21 10:46:28', '1', '29', '3', '疯狂守卫', '1', '');
INSERT INTO `django_admin_log` VALUES ('124', '2010-04-21 10:46:56', '1', '29', '4', '俄罗斯前特种兵', '1', '');
INSERT INTO `django_admin_log` VALUES ('125', '2010-04-21 10:47:19', '1', '29', '5', '专业保镖', '1', '');
INSERT INTO `django_admin_log` VALUES ('126', '2010-04-21 10:47:38', '1', '29', '6', '终极保镖', '1', '');
INSERT INTO `django_admin_log` VALUES ('127', '2010-04-21 10:52:57', '1', '22', '1', 'Dolly', '1', '');
INSERT INTO `django_admin_log` VALUES ('128', '2010-04-21 10:54:13', '1', '22', '2', 'Heinrich', '1', '');
INSERT INTO `django_admin_log` VALUES ('129', '2010-04-21 10:56:35', '1', '22', '3', 'Britney', '1', '');
INSERT INTO `django_admin_log` VALUES ('130', '2010-04-21 10:57:27', '1', '22', '4', 'Mount Tse Tung', '1', '');
INSERT INTO `django_admin_log` VALUES ('131', '2010-04-21 10:58:14', '1', '22', '5', 'Marilyn', '1', '');
INSERT INTO `django_admin_log` VALUES ('132', '2010-04-21 10:59:17', '1', '22', '6', 'Candy', '1', '');
INSERT INTO `django_admin_log` VALUES ('133', '2010-04-21 11:00:20', '1', '22', '7', 'Bell', '1', '');
INSERT INTO `django_admin_log` VALUES ('134', '2010-04-21 11:01:04', '1', '22', '8', 'Patricia', '1', '');
INSERT INTO `django_admin_log` VALUES ('135', '2010-04-21 11:03:16', '1', '22', '9', 'Claire', '1', '');
INSERT INTO `django_admin_log` VALUES ('136', '2010-04-21 11:03:55', '1', '22', '10', 'Crystal', '1', '');
INSERT INTO `django_admin_log` VALUES ('137', '2010-04-21 11:04:54', '1', '22', '11', 'Valerie', '1', '');
INSERT INTO `django_admin_log` VALUES ('138', '2010-04-21 11:06:38', '1', '22', '12', 'Chessy', '1', '');
INSERT INTO `django_admin_log` VALUES ('139', '2010-04-21 11:07:33', '1', '22', '13', 'Denim Daisy', '1', '');
INSERT INTO `django_admin_log` VALUES ('140', '2010-04-21 11:08:24', '1', '22', '14', 'Head Nurse', '1', '');
INSERT INTO `django_admin_log` VALUES ('141', '2010-04-21 11:09:09', '1', '22', '15', 'Cindy', '1', '');
INSERT INTO `django_admin_log` VALUES ('142', '2010-04-21 11:09:53', '1', '22', '16', 'George', '1', '');
INSERT INTO `django_admin_log` VALUES ('143', '2010-04-21 11:10:31', '1', '22', '17', 'Gothic Goddess', '1', '');
INSERT INTO `django_admin_log` VALUES ('144', '2010-04-21 11:11:10', '1', '22', '18', 'Pearl', '1', '');
INSERT INTO `django_admin_log` VALUES ('145', '2010-04-21 11:12:15', '1', '22', '19', 'Miss FBI', '1', '');
INSERT INTO `django_admin_log` VALUES ('146', '2010-04-21 11:13:31', '1', '22', '20', 'French Maid Fifi', '1', '');
INSERT INTO `django_admin_log` VALUES ('147', '2010-04-21 11:14:12', '1', '22', '21', 'Darling Devil', '1', '');
INSERT INTO `django_admin_log` VALUES ('148', '2010-04-21 11:14:51', '1', '22', '22', 'Sergeant Sexy', '1', '');
INSERT INTO `django_admin_log` VALUES ('149', '2010-04-21 11:15:22', '1', '22', '23', 'Jessica', '1', '');
INSERT INTO `django_admin_log` VALUES ('150', '2010-04-21 11:15:53', '1', '22', '24', 'Leonard', '1', '');
INSERT INTO `django_admin_log` VALUES ('151', '2010-04-21 11:16:25', '1', '22', '25', 'Bunnie', '1', '');
INSERT INTO `django_admin_log` VALUES ('152', '2010-04-21 11:17:22', '1', '22', '26', 'Mrs. Robinson', '1', '');
INSERT INTO `django_admin_log` VALUES ('153', '2010-04-21 11:18:21', '1', '22', '27', 'Mr Love', '1', '');
INSERT INTO `django_admin_log` VALUES ('154', '2010-04-21 11:19:25', '1', '22', '28', 'Lill &  Jill', '1', '');
INSERT INTO `django_admin_log` VALUES ('155', '2010-04-21 11:20:05', '1', '22', '29', 'The Twins', '1', '');
INSERT INTO `django_admin_log` VALUES ('156', '2010-04-21 11:20:43', '1', '22', '30', 'Slim Susy', '1', '');
INSERT INTO `django_admin_log` VALUES ('157', '2010-04-21 11:21:15', '1', '22', '31', 'SM Babe', '1', '');
INSERT INTO `django_admin_log` VALUES ('158', '2010-04-21 11:22:23', '1', '22', '32', 'Miss Blonde', '1', '');
INSERT INTO `django_admin_log` VALUES ('159', '2010-04-21 11:23:13', '1', '22', '33', 'Bobbi', '1', '');
INSERT INTO `django_admin_log` VALUES ('160', '2010-04-21 11:24:31', '1', '22', '34', 'Woman of Wonder', '1', '');
INSERT INTO `django_admin_log` VALUES ('161', '2010-04-21 11:25:23', '1', '22', '35', 'Rhinogirl', '1', '');
INSERT INTO `django_admin_log` VALUES ('162', '2010-04-21 11:31:34', '1', '31', '1', '球棒', '1', '');
INSERT INTO `django_admin_log` VALUES ('163', '2010-04-21 11:34:37', '1', '31', '1', '球棒', '2', '已修改 type 。');
INSERT INTO `django_admin_log` VALUES ('164', '2010-04-21 11:35:34', '1', '31', '2', '匕首', '1', '');
INSERT INTO `django_admin_log` VALUES ('165', '2010-04-21 11:36:29', '1', '31', '3', '剑', '1', '');
INSERT INTO `django_admin_log` VALUES ('166', '2010-04-21 11:37:20', '1', '31', '4', '链锯', '1', '');
INSERT INTO `django_admin_log` VALUES ('167', '2010-04-21 11:38:16', '1', '31', '5', '格洛克', '1', '');
INSERT INTO `django_admin_log` VALUES ('168', '2010-04-21 11:39:13', '1', '31', '6', '散弹枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('169', '2010-04-21 11:40:44', '1', '31', '7', 'MP5冲锋枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('170', '2010-04-21 11:41:45', '1', '31', '8', 'AK 47', '1', '');
INSERT INTO `django_admin_log` VALUES ('171', '2010-04-21 11:42:30', '1', '31', '9', '乌兹冲锋枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('172', '2010-04-21 11:43:30', '1', '31', '10', 'M4A1', '1', '');
INSERT INTO `django_admin_log` VALUES ('173', '2010-04-21 11:44:48', '1', '31', '11', '沙漠之鹰', '1', '');
INSERT INTO `django_admin_log` VALUES ('174', '2010-04-21 11:45:50', '1', '31', '12', '重型狙击枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('175', '2010-04-21 11:47:14', '1', '31', '13', '激光枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('176', '2010-04-21 11:48:07', '1', '31', '14', '重机枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('177', '2010-04-21 11:49:27', '1', '31', '15', '火箭筒', '1', '');
INSERT INTO `django_admin_log` VALUES ('178', '2010-04-21 11:50:31', '1', '31', '16', '盖利步枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('179', '2010-04-21 11:51:24', '1', '31', '17', '多管连发手枪', '1', '');
INSERT INTO `django_admin_log` VALUES ('180', '2010-04-21 11:52:20', '1', '31', '18', '地狱火神炮', '1', '');
INSERT INTO `django_admin_log` VALUES ('181', '2010-04-21 13:44:58', '1', '34', '1', 'POLICE RAID', '1', '');
INSERT INTO `django_admin_log` VALUES ('182', '2010-04-21 13:46:13', '1', '34', '2', 'DRUG PRICES DOWN', '1', '');
INSERT INTO `django_admin_log` VALUES ('183', '2010-04-21 13:53:25', '1', '34', '3', 'WARREN BUFFET ON QUICK VISIT!', '1', '');
INSERT INTO `django_admin_log` VALUES ('184', '2010-04-21 13:56:03', '1', '34', '4', 'DRUG PRICES UP', '1', '');
INSERT INTO `django_admin_log` VALUES ('185', '2010-04-21 13:57:32', '1', '34', '5', 'CARNIVAL!', '1', '');
INSERT INTO `django_admin_log` VALUES ('186', '2010-04-21 14:00:33', '1', '34', '6', 'OIL TYCOON OF DUBAI IS IN TOWN!', '1', '');
INSERT INTO `django_admin_log` VALUES ('187', '2010-04-21 14:01:39', '1', '34', '7', 'BILL GATES IN TOWN!', '1', '');
INSERT INTO `django_admin_log` VALUES ('188', '2010-04-21 14:08:59', '1', '34', '2', 'DRUG PRICES DOWN', '2', '已修改 drug 。');
INSERT INTO `django_admin_log` VALUES ('189', '2010-04-21 14:09:19', '1', '34', '4', 'DRUG PRICES UP', '2', '已修改 drug 。');
INSERT INTO `django_admin_log` VALUES ('190', '2010-04-21 14:09:53', '1', '34', '7', 'BILL GATES IN TOWN!', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('191', '2010-04-21 14:10:32', '1', '34', '8', 'HARBOUR ACTIVITY', '1', '');
INSERT INTO `django_admin_log` VALUES ('192', '2010-04-21 14:15:25', '1', '33', '1', '北京', '1', '');
INSERT INTO `django_admin_log` VALUES ('193', '2010-04-21 14:15:33', '1', '33', '2', '天津', '1', '');
INSERT INTO `django_admin_log` VALUES ('194', '2010-04-21 14:15:50', '1', '33', '3', '河北', '1', '');
INSERT INTO `django_admin_log` VALUES ('195', '2010-04-21 14:15:58', '1', '33', '4', '山西', '1', '');
INSERT INTO `django_admin_log` VALUES ('196', '2010-04-21 14:16:08', '1', '33', '5', '内蒙古自治区', '1', '');
INSERT INTO `django_admin_log` VALUES ('197', '2010-04-21 14:16:17', '1', '33', '6', '辽宁', '1', '');
INSERT INTO `django_admin_log` VALUES ('198', '2010-04-21 14:16:26', '1', '33', '7', '吉林', '1', '');
INSERT INTO `django_admin_log` VALUES ('199', '2010-04-21 14:16:34', '1', '33', '8', '黑龙江', '1', '');
INSERT INTO `django_admin_log` VALUES ('200', '2010-04-21 14:16:43', '1', '33', '9', '上海', '1', '');
INSERT INTO `django_admin_log` VALUES ('201', '2010-04-21 14:16:52', '1', '33', '10', '江苏', '1', '');
INSERT INTO `django_admin_log` VALUES ('202', '2010-04-21 14:17:01', '1', '33', '11', '浙江', '1', '');
INSERT INTO `django_admin_log` VALUES ('203', '2010-04-21 14:17:08', '1', '33', '12', '安徽', '1', '');
INSERT INTO `django_admin_log` VALUES ('204', '2010-04-21 14:17:17', '1', '33', '13', '福建', '1', '');
INSERT INTO `django_admin_log` VALUES ('205', '2010-04-21 14:17:24', '1', '33', '14', '江西', '1', '');
INSERT INTO `django_admin_log` VALUES ('206', '2010-04-21 14:17:32', '1', '33', '15', '山东', '1', '');
INSERT INTO `django_admin_log` VALUES ('207', '2010-04-21 14:17:42', '1', '33', '16', '河南', '1', '');
INSERT INTO `django_admin_log` VALUES ('208', '2010-04-21 14:17:49', '1', '33', '17', '湖北', '1', '');
INSERT INTO `django_admin_log` VALUES ('209', '2010-04-21 14:17:57', '1', '33', '18', '湖南', '1', '');
INSERT INTO `django_admin_log` VALUES ('210', '2010-04-21 14:18:05', '1', '33', '19', '广东', '1', '');
INSERT INTO `django_admin_log` VALUES ('211', '2010-04-21 14:18:13', '1', '33', '20', '广西', '1', '');
INSERT INTO `django_admin_log` VALUES ('212', '2010-04-21 14:18:23', '1', '33', '21', '海南', '1', '');
INSERT INTO `django_admin_log` VALUES ('213', '2010-04-21 14:18:31', '1', '33', '22', '重庆', '1', '');
INSERT INTO `django_admin_log` VALUES ('214', '2010-04-21 14:18:39', '1', '33', '23', '四川', '1', '');
INSERT INTO `django_admin_log` VALUES ('215', '2010-04-21 14:18:47', '1', '33', '24', '贵州', '1', '');
INSERT INTO `django_admin_log` VALUES ('216', '2010-04-21 14:18:55', '1', '33', '25', '云南', '1', '');
INSERT INTO `django_admin_log` VALUES ('217', '2010-04-21 14:19:06', '1', '33', '26', '西藏', '1', '');
INSERT INTO `django_admin_log` VALUES ('218', '2010-04-21 14:19:14', '1', '33', '27', '陕西', '1', '');
INSERT INTO `django_admin_log` VALUES ('219', '2010-04-21 14:19:23', '1', '33', '28', '甘肃', '1', '');
INSERT INTO `django_admin_log` VALUES ('220', '2010-04-21 14:19:33', '1', '33', '29', '青海', '1', '');
INSERT INTO `django_admin_log` VALUES ('221', '2010-04-21 14:19:42', '1', '33', '30', '宁夏', '1', '');
INSERT INTO `django_admin_log` VALUES ('222', '2010-04-21 14:19:55', '1', '33', '31', '新疆', '1', '');
INSERT INTO `django_admin_log` VALUES ('223', '2010-04-21 14:20:03', '1', '33', '32', '香港', '1', '');
INSERT INTO `django_admin_log` VALUES ('224', '2010-04-21 14:20:11', '1', '33', '33', '澳门', '1', '');
INSERT INTO `django_admin_log` VALUES ('225', '2010-04-21 14:20:19', '1', '33', '34', '台湾', '1', '');
INSERT INTO `django_admin_log` VALUES ('226', '2010-04-21 14:20:27', '1', '33', '35', '其它', '1', '');
INSERT INTO `django_admin_log` VALUES ('227', '2010-04-21 14:48:55', '1', '36', '1', '脑激素', '1', '');
INSERT INTO `django_admin_log` VALUES ('228', '2010-04-21 14:49:11', '1', '36', '2', '性激素', '1', '');
INSERT INTO `django_admin_log` VALUES ('229', '2010-04-21 14:49:26', '1', '36', '3', '肌肉生长素', '1', '');
INSERT INTO `django_admin_log` VALUES ('230', '2010-04-21 14:49:38', '1', '36', '4', '类固醇', '1', '');
INSERT INTO `django_admin_log` VALUES ('231', '2010-04-21 14:49:56', '1', '36', '5', '美沙酮', '1', '');
INSERT INTO `django_admin_log` VALUES ('232', '2010-04-21 18:51:59', '1', '37', '1', '一个警察不怀好心的档着你的路', '1', '');
INSERT INTO `django_admin_log` VALUES ('233', '2010-04-21 18:52:19', '1', '37', '2', '一个醉汉高举酒瓶档着你的路。', '1', '');
INSERT INTO `django_admin_log` VALUES ('234', '2010-04-21 18:52:34', '1', '37', '3', '一只毒虫拿着针头挡住你的路', '1', '');
INSERT INTO `django_admin_log` VALUES ('235', '2010-04-21 18:52:53', '1', '37', '4', '一个老伯步履蹒跚拦下你。', '1', '');
INSERT INTO `django_admin_log` VALUES ('236', '2010-04-21 18:54:07', '1', '38', '1', '我想我认得样子，你说呢？', '1', '');
INSERT INTO `django_admin_log` VALUES ('237', '2010-04-21 18:54:17', '1', '38', '2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '1', '');
INSERT INTO `django_admin_log` VALUES ('238', '2010-04-21 18:54:28', '1', '38', '3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '1', '');
INSERT INTO `django_admin_log` VALUES ('239', '2010-04-21 18:54:37', '1', '38', '4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '1', '');
INSERT INTO `django_admin_log` VALUES ('240', '2010-04-21 18:54:46', '1', '38', '5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '1', '');
INSERT INTO `django_admin_log` VALUES ('241', '2010-04-21 18:54:57', '1', '38', '6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '1', '');
INSERT INTO `django_admin_log` VALUES ('242', '2010-04-21 18:58:32', '1', '38', '1', '我想我认得样子，你说呢？', '2', '已添加 random event choice \"非洲\". 已添加 random event choice \"欧洲\". 已添加 random event choice \"大洋洲\". 已添加 random event choice \"南美洲\". 已添加 random event choice \"亚洲\".');
INSERT INTO `django_admin_log` VALUES ('243', '2010-04-21 19:02:21', '1', '38', '2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '2', '已添加 random event choice \"3:10\". 已添加 random event choice \"5:00 \". 已添加 random event choice \"7:53\". 已添加 random event choice \"10:30\". 已添加 random event choice \"11:55\".');
INSERT INTO `django_admin_log` VALUES ('244', '2010-04-21 19:04:09', '1', '38', '3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '2', '已添加 random event choice \"猴子\". 已添加 random event choice \"鸟\". 已添加 random event choice \"狗\". 已添加 random event choice \"老虎\". 已添加 random event choice \"耗子\".');
INSERT INTO `django_admin_log` VALUES ('245', '2010-04-21 19:06:50', '1', '38', '4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '2', '已添加 random event choice \"沙漠\". 已添加 random event choice \"海洋\". 已添加 random event choice \"森林\". 已添加 random event choice \"城市\". 已添加 random event choice \"北极\".');
INSERT INTO `django_admin_log` VALUES ('246', '2010-04-21 19:08:25', '1', '38', '5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '2', '已添加 random event choice \"渡船\". 已添加 random event choice \"火车\". 已添加 random event choice \"公共汽车\". 已添加 random event choice \"飞机\".');
INSERT INTO `django_admin_log` VALUES ('247', '2010-04-21 19:10:14', '1', '38', '6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '2', '已添加 random event choice \"尺\". 已添加 random event choice \"笔\". 已添加 random event choice \"计算器\". 已添加 random event choice \"橡皮\". 已添加 random event choice \"笔记本计算机\".');
INSERT INTO `django_admin_log` VALUES ('248', '2010-04-22 14:39:34', '1', '10', '1', '媒体管理', '2', '已修改 title 。 已变更 title for 导航条目 \"文件浏览\".');
INSERT INTO `django_admin_log` VALUES ('249', '2010-04-22 14:44:42', '1', '12', '1', 'root', '2', '已添加 书签条目 \"站点管理\".');
INSERT INTO `django_admin_log` VALUES ('250', '2010-04-22 14:44:54', '1', '12', '1', 'root', '2', '已删除 书签条目 \"站点管理\".');
INSERT INTO `django_admin_log` VALUES ('251', '2010-04-22 14:53:37', '1', '10', '2', '内容', '2', '已修改 title 。 已变更 title for 导航条目 \"主页\".');
INSERT INTO `django_admin_log` VALUES ('252', '2010-04-22 14:53:49', '1', '10', '3', '外部', '2', '已修改 title 。');
INSERT INTO `django_admin_log` VALUES ('253', '2010-04-22 14:54:10', '1', '10', '4', '文档', '2', '已修改 title 。 已变更 title for 导航条目 \"帮助\".');
INSERT INTO `django_admin_log` VALUES ('254', '2010-04-22 14:55:13', '1', '38', '1', '我想我认得样子，你说呢？', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('255', '2010-04-22 14:56:11', '1', '27', '1', '夜总会', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('256', '2010-04-22 15:04:43', '1', '27', '1', '夜总会', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('257', '2010-04-22 15:05:00', '1', '38', '1', '我想我认得样子，你说呢？', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('258', '2010-04-22 15:10:36', '1', '17', '51', 'uploads/avatar/avatar_53.jpg', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('259', '2010-04-22 15:14:16', '1', '37', '1', '一个警察不怀好心的档着你的路', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('260', '2010-04-28 19:06:21', '1', '40', '1', '偷窃', '1', '');
INSERT INTO `django_admin_log` VALUES ('261', '2010-04-28 19:09:36', '1', '40', '2', '抢老妇人', '1', '');
INSERT INTO `django_admin_log` VALUES ('262', '2010-04-28 19:10:33', '1', '40', '3', '偷汽車', '1', '');
INSERT INTO `django_admin_log` VALUES ('263', '2010-04-28 19:11:17', '1', '40', '4', '抢出租车', '1', '');
INSERT INTO `django_admin_log` VALUES ('264', '2010-04-28 19:12:11', '1', '40', '5', '提款机', '1', '');
INSERT INTO `django_admin_log` VALUES ('265', '2010-04-28 19:13:02', '1', '40', '6', '民宅', '1', '');
INSERT INTO `django_admin_log` VALUES ('266', '2010-04-28 19:13:45', '1', '40', '7', '加油站', '1', '');
INSERT INTO `django_admin_log` VALUES ('267', '2010-04-28 19:14:25', '1', '40', '8', '戏院', '1', '');
INSERT INTO `django_admin_log` VALUES ('268', '2010-04-28 19:15:15', '1', '40', '9', '杂货店', '1', '');
INSERT INTO `django_admin_log` VALUES ('269', '2010-04-28 19:15:53', '1', '40', '10', '24小时便利商店', '1', '');
INSERT INTO `django_admin_log` VALUES ('270', '2010-04-28 19:16:41', '1', '40', '11', '绑架', '1', '');
INSERT INTO `django_admin_log` VALUES ('271', '2010-04-28 19:17:24', '1', '40', '12', '珠宝店', '1', '');
INSERT INTO `django_admin_log` VALUES ('272', '2010-04-28 19:18:07', '1', '40', '13', '保险箱', '1', '');
INSERT INTO `django_admin_log` VALUES ('273', '2010-04-28 19:18:51', '1', '40', '14', '小银行', '1', '');
INSERT INTO `django_admin_log` VALUES ('274', '2010-04-28 19:19:29', '1', '40', '15', '帮派小头目', '1', '');
INSERT INTO `django_admin_log` VALUES ('275', '2010-04-28 19:20:12', '1', '40', '16', '汽车沙龙', '1', '');
INSERT INTO `django_admin_log` VALUES ('276', '2010-04-28 19:21:00', '1', '40', '17', 'PayPal', '1', '');
INSERT INTO `django_admin_log` VALUES ('277', '2010-04-28 19:21:41', '1', '40', '18', '地痞', '1', '');
INSERT INTO `django_admin_log` VALUES ('278', '2010-04-28 19:22:21', '1', '40', '19', '小药头', '1', '');
INSERT INTO `django_admin_log` VALUES ('279', '2010-04-28 19:22:50', '1', '40', '20', '赌场', '1', '');
INSERT INTO `django_admin_log` VALUES ('280', '2010-04-28 19:23:18', '1', '40', '21', '狂欢会', '1', '');
INSERT INTO `django_admin_log` VALUES ('281', '2010-04-28 19:23:56', '1', '40', '22', '超级市场', '1', '');
INSERT INTO `django_admin_log` VALUES ('282', '2010-04-28 19:24:41', '1', '40', '23', '博物馆', '1', '');
INSERT INTO `django_admin_log` VALUES ('283', '2010-04-28 19:25:18', '1', '40', '24', '俄国药王', '1', '');
INSERT INTO `django_admin_log` VALUES ('284', '2010-04-28 19:26:13', '1', '40', '25', '外币', '1', '');
INSERT INTO `django_admin_log` VALUES ('285', '2010-04-28 19:27:04', '1', '40', '26', '银行', '1', '');
INSERT INTO `django_admin_log` VALUES ('286', '2010-04-28 19:27:44', '1', '40', '27', '运钞车', '1', '');
INSERT INTO `django_admin_log` VALUES ('287', '2010-04-28 19:28:43', '1', '40', '28', '中央储备银行', '1', '');
INSERT INTO `django_admin_log` VALUES ('288', '2010-04-28 19:29:31', '1', '40', '29', '杜月笙', '1', '');
INSERT INTO `django_admin_log` VALUES ('289', '2010-04-28 19:30:11', '1', '40', '30', '操纵股市', '1', '');
INSERT INTO `django_admin_log` VALUES ('290', '2010-04-28 19:30:54', '1', '40', '31', '黄金荣', '1', '');
INSERT INTO `django_admin_log` VALUES ('291', '2010-04-28 19:31:37', '1', '40', '32', '他妈的烂地方', '1', '');
INSERT INTO `django_admin_log` VALUES ('292', '2010-04-28 19:32:20', '1', '40', '33', '黄埔军校', '1', '');
INSERT INTO `django_admin_log` VALUES ('293', '2010-04-28 19:42:05', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。');
INSERT INTO `django_admin_log` VALUES ('294', '2010-04-29 09:24:59', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。');
INSERT INTO `django_admin_log` VALUES ('295', '2010-04-29 10:36:28', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('296', '2010-04-29 10:36:44', '1', '40', '1', '偷窃', '2', '已修改 preperty_range 。');
INSERT INTO `django_admin_log` VALUES ('297', '2010-04-29 10:47:57', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('298', '2010-04-29 11:16:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('299', '2010-04-29 11:18:02', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('300', '2010-04-29 11:22:36', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('301', '2010-04-29 11:27:32', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('302', '2010-04-29 11:27:58', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('303', '2010-04-29 11:36:15', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('304', '2010-04-29 11:38:06', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('305', '2010-04-29 11:54:48', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('306', '2010-04-29 11:56:53', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('307', '2010-04-29 11:57:18', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('308', '2010-04-29 11:58:35', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('309', '2010-04-29 12:00:20', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('310', '2010-04-29 12:03:52', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('311', '2010-04-29 14:11:20', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('312', '2010-04-29 14:12:27', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('313', '2010-04-29 14:12:44', '1', '40', '1', '偷窃', '2', '已修改 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('314', '2010-04-29 14:12:54', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('315', '2010-04-29 14:48:11', '1', '40', '2', '抢老妇人', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('316', '2010-04-29 15:13:25', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('317', '2010-04-29 15:15:21', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('318', '2010-04-29 15:15:48', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('319', '2010-04-29 15:17:20', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('320', '2010-04-29 15:17:29', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('321', '2010-04-29 15:20:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('322', '2010-04-29 15:21:11', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('323', '2010-04-29 15:23:17', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('324', '2010-04-29 15:25:00', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('325', '2010-04-29 15:29:26', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('326', '2010-04-29 15:31:58', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('327', '2010-04-29 15:38:39', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('328', '2010-04-29 15:39:56', '1', '40', '1', '偷窃', '2', '已修改 attribute_range 。');
INSERT INTO `django_admin_log` VALUES ('329', '2010-04-29 15:41:09', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('330', '2010-04-29 15:42:40', '1', '40', '3', '偷汽車', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('331', '2010-04-29 15:43:07', '1', '40', '4', '抢出租车', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('332', '2010-04-29 15:43:19', '1', '40', '5', '提款机', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('333', '2010-04-29 15:43:34', '1', '40', '6', '民宅', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('334', '2010-04-29 15:43:48', '1', '40', '7', '加油站', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('335', '2010-04-29 15:44:03', '1', '40', '8', '戏院', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('336', '2010-04-29 15:44:18', '1', '40', '9', '杂货店', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('337', '2010-04-29 15:44:35', '1', '40', '10', '24小时便利商店', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('338', '2010-04-29 15:44:50', '1', '40', '11', '绑架', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('339', '2010-04-29 15:45:13', '1', '40', '12', '珠宝店', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('340', '2010-04-29 15:45:27', '1', '40', '13', '保险箱', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('341', '2010-04-29 15:45:53', '1', '40', '14', '小银行', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('342', '2010-04-29 15:46:08', '1', '40', '15', '帮派小头目', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('343', '2010-04-29 15:46:24', '1', '40', '16', '汽车沙龙', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('344', '2010-04-29 15:46:42', '1', '40', '17', 'PayPal', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('345', '2010-04-29 15:46:55', '1', '40', '18', '地痞', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('346', '2010-04-29 15:47:15', '1', '40', '19', '小药头', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('347', '2010-04-29 15:47:30', '1', '40', '20', '赌场', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('348', '2010-04-29 15:47:43', '1', '40', '21', '狂欢会', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('349', '2010-04-29 15:48:06', '1', '40', '22', '超级市场', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('350', '2010-04-29 15:48:26', '1', '40', '23', '博物馆', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('351', '2010-04-29 15:48:42', '1', '40', '24', '俄国药王', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('352', '2010-04-29 15:48:56', '1', '40', '25', '外币', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('353', '2010-04-29 15:49:16', '1', '40', '26', '银行', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('354', '2010-04-29 15:49:30', '1', '40', '27', '运钞车', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('355', '2010-04-29 15:49:50', '1', '40', '28', '中央储备银行', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('356', '2010-04-29 15:50:05', '1', '40', '29', '杜月笙', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('357', '2010-04-29 15:50:21', '1', '40', '30', '操纵股市', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('358', '2010-04-29 15:50:37', '1', '40', '31', '黄金荣', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('359', '2010-04-29 15:50:56', '1', '40', '32', '他妈的烂地方', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('360', '2010-04-29 15:51:12', '1', '40', '33', '黄埔军校', '2', '已修改 attribute_range 和 cash_range 。');
INSERT INTO `django_admin_log` VALUES ('361', '2010-04-29 16:04:07', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('362', '2010-04-29 16:04:39', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('363', '2010-04-29 16:05:36', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('364', '2010-04-29 16:06:41', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('365', '2010-04-29 16:06:49', '1', '40', '1', '偷窃', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('366', '2010-04-30 11:42:16', '1', '2', '1', '系统管理员', '1', '');
INSERT INTO `django_admin_log` VALUES ('367', '2010-04-30 11:43:01', '1', '3', '2', 'darkmoon', '1', '');
INSERT INTO `django_admin_log` VALUES ('368', '2010-04-30 11:43:28', '1', '3', '2', 'darkmoon', '2', '已修改 first_name, last_name, email 和 groups 。');
INSERT INTO `django_admin_log` VALUES ('369', '2010-04-30 11:45:04', '1', '2', '1', '系统管理员', '3', '');
INSERT INTO `django_admin_log` VALUES ('370', '2010-04-30 11:45:38', '1', '3', '2', 'darkmoon', '2', '已修改 is_staff 和 user_permissions 。');
INSERT INTO `django_admin_log` VALUES ('372', '2010-04-30 14:11:54', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('373', '2010-04-30 14:45:57', '1', '42', '2', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('374', '2010-04-30 15:31:56', '1', '42', '2', 'CQSSC', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('375', '2010-04-30 15:41:41', '1', '42', '2', 'CQSSC', '3', '');
INSERT INTO `django_admin_log` VALUES ('376', '2010-04-30 15:43:07', '1', '42', '3', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('377', '2010-04-30 15:43:33', '1', '42', '3', 'CQSSC', '3', '');
INSERT INTO `django_admin_log` VALUES ('378', '2010-04-30 15:49:42', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('379', '2010-04-30 16:31:11', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('380', '2010-04-30 16:31:25', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('381', '2010-04-30 16:31:55', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('382', '2010-04-30 16:32:04', '1', '42', '1', 'CQSSC', '3', '');
INSERT INTO `django_admin_log` VALUES ('383', '2010-04-30 16:33:56', '1', '42', '2', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('384', '2010-04-30 16:34:08', '1', '42', '2', 'CQSSC', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('385', '2010-04-30 16:36:08', '1', '42', '2', 'CQSSC', '3', '');
INSERT INTO `django_admin_log` VALUES ('386', '2010-04-30 16:56:49', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('387', '2010-04-30 17:00:09', '1', '42', '1', 'CQSSC', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('388', '2010-04-30 17:13:22', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。');
INSERT INTO `django_admin_log` VALUES ('389', '2010-04-30 17:13:32', '1', '42', '1', 'CQSSC', '2', '已添加 帮会成员 \"root\".');
INSERT INTO `django_admin_log` VALUES ('390', '2010-04-30 17:14:35', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。 已删除 帮会成员 \"root\".');
INSERT INTO `django_admin_log` VALUES ('393', '2010-04-30 17:37:50', '1', '42', '1', 'CQSSC', '2', '已修改 vice_leader 。');
INSERT INTO `django_admin_log` VALUES ('394', '2010-05-04 10:28:37', '1', '46', '1', '炸了他的工厂', '1', '');
INSERT INTO `django_admin_log` VALUES ('395', '2010-05-04 10:28:58', '1', '46', '2', '抢走他的小姐', '1', '');
INSERT INTO `django_admin_log` VALUES ('396', '2010-05-04 10:29:24', '1', '46', '3', '砸了他的场子', '1', '');
INSERT INTO `django_admin_log` VALUES ('397', '2010-05-04 11:05:11', '1', '47', '4', 'root put bounty on darkmoon, price is 10 credits.', '1', '');
INSERT INTO `django_admin_log` VALUES ('398', '2010-05-04 11:07:13', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('399', '2010-05-04 11:10:32', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '已修改 completed 。');
INSERT INTO `django_admin_log` VALUES ('400', '2010-05-04 11:10:38', '1', '47', '4', 'root 悬赏 10 银圆干掉  darkmoon。', '2', '已修改 completed 。');
INSERT INTO `django_admin_log` VALUES ('401', '2010-05-04 11:11:41', '1', '47', '4', 'root 悬赏 10 银元干掉  darkmoon。', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('402', '2010-05-04 11:38:09', '1', '48', '1', 'root\'s bank account.', '1', '');
INSERT INTO `django_admin_log` VALUES ('403', '2010-05-04 11:38:22', '1', '48', '2', 'darkmoon\'s bank account.', '1', '');
INSERT INTO `django_admin_log` VALUES ('413', '2010-05-04 14:12:33', '1', '51', '1', 'Friendship object', '1', '');
INSERT INTO `django_admin_log` VALUES ('414', '2010-05-04 14:12:44', '1', '51', '2', 'Friendship object', '1', '');
INSERT INTO `django_admin_log` VALUES ('415', '2010-05-04 14:13:35', '1', '51', '2', 'Friendship object', '3', '');
INSERT INTO `django_admin_log` VALUES ('416', '2010-05-04 14:13:35', '1', '51', '1', 'Friendship object', '3', '');
INSERT INTO `django_admin_log` VALUES ('417', '2010-05-04 14:27:19', '1', '51', '3', 'Friendship object', '1', '');
INSERT INTO `django_admin_log` VALUES ('418', '2010-05-04 14:27:42', '1', '51', '4', 'Friendship object', '1', '');
INSERT INTO `django_admin_log` VALUES ('419', '2010-05-04 14:43:23', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('420', '2010-05-04 14:44:27', '1', '3', '2', 'darkmoon', '2', '已添加 用户关系 \"Relationship from darkmoon to root\".');
INSERT INTO `django_admin_log` VALUES ('421', '2010-05-04 14:47:01', '1', '3', '1', 'root', '2', '已删除 用户关系 \"Relationship from root to darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('422', '2010-05-04 14:55:16', '1', '7', '1', 'crims.info', '2', '已修改 domain 和 name 。');
INSERT INTO `django_admin_log` VALUES ('423', '2010-05-04 14:55:29', '1', '7', '2', 'hxxps.com', '1', '');
INSERT INTO `django_admin_log` VALUES ('424', '2010-05-04 14:55:47', '1', '7', '3', 'hxxps.info', '1', '');
INSERT INTO `django_admin_log` VALUES ('425', '2010-05-04 15:27:29', '1', '60', '3', '黑名单', '1', '');
INSERT INTO `django_admin_log` VALUES ('426', '2010-05-04 15:27:44', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('427', '2010-05-04 15:28:09', '1', '3', '2', 'darkmoon', '2', '已变更 status for 用户关系 \"Relationship from darkmoon to root\".');
INSERT INTO `django_admin_log` VALUES ('428', '2010-05-04 15:29:53', '1', '60', '3', '黑名单', '3', '');
INSERT INTO `django_admin_log` VALUES ('429', '2010-05-04 15:30:08', '1', '3', '1', 'root', '2', '已添加 用户关系 \"Relationship from root to root\".');
INSERT INTO `django_admin_log` VALUES ('430', '2010-05-04 15:30:15', '1', '3', '1', 'root', '2', '已变更 to_user for 用户关系 \"Relationship from root to darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('431', '2010-05-04 15:30:25', '1', '3', '2', 'darkmoon', '2', '已添加 用户关系 \"Relationship from darkmoon to root\".');
INSERT INTO `django_admin_log` VALUES ('432', '2010-05-04 16:16:23', '1', '3', '1', 'root', '2', '已删除 银行 \"root 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('433', '2010-05-04 16:16:46', '1', '3', '1', 'root', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('434', '2010-05-04 16:17:05', '1', '3', '1', 'root', '2', '已添加 银行 \"root 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('435', '2010-05-04 16:17:19', '1', '3', '1', 'root', '2', '已变更 amount for 银行 \"root 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('436', '2010-05-04 16:18:19', '1', '3', '1', 'root', '2', '已删除 银行 \"root 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('437', '2010-05-04 16:19:25', '1', '3', '1', 'root', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('438', '2010-05-04 16:19:44', '1', '3', '1', 'root', '2', '已添加 银行 \"root 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('440', '2010-05-04 16:42:31', '1', '3', '2', 'darkmoon', '3', '');
INSERT INTO `django_admin_log` VALUES ('441', '2010-05-04 16:42:56', '1', '3', '3', 'darkmoon', '1', '');
INSERT INTO `django_admin_log` VALUES ('442', '2010-05-04 16:43:29', '1', '3', '3', 'darkmoon', '2', '已修改 first_name, last_name 和 email 。 已添加 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('443', '2010-05-04 16:50:56', '1', '3', '3', 'darkmoon', '2', '已变更 amount for 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('444', '2010-05-04 16:51:04', '1', '3', '3', 'darkmoon', '2', '已变更 amount for 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('445', '2010-05-04 17:15:13', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('446', '2010-05-04 17:23:44', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('447', '2010-05-04 17:24:09', '1', '3', '3', 'darkmoon', '2', '已删除 银行 \"darkmoon 的银行账户\".');
INSERT INTO `django_admin_log` VALUES ('448', '2010-05-04 17:53:53', '1', '3', '3', 'darkmoon', '2', '已添加 用户盔甲 \"darkmoon\'s尿布\".');
INSERT INTO `django_admin_log` VALUES ('449', '2010-05-04 18:34:07', '1', '2', '1', 'Discuz! Board', '1', '');
INSERT INTO `django_admin_log` VALUES ('450', '2010-05-04 18:34:15', '1', '2', '1', 'Discuz! Board', '3', '');
INSERT INTO `django_admin_log` VALUES ('451', '2010-05-04 18:37:26', '1', '42', '2', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('452', '2010-05-05 09:20:09', '1', '3', '3', 'darkmoon', '2', '已添加 用户毒品 \"darkmoon\'s 止痛药\".');
INSERT INTO `django_admin_log` VALUES ('453', '2010-05-05 09:25:42', '1', '3', '3', 'darkmoon', '2', '已添加 用户妓女 \"darkmoon\'s Dolly\".');
INSERT INTO `django_admin_log` VALUES ('454', '2010-05-05 09:49:25', '1', '3', '3', 'darkmoon', '2', '已添加 用户物业 \"烟草\".');
INSERT INTO `django_admin_log` VALUES ('455', '2010-05-05 10:59:51', '1', '42', '2', 'CQSSC', '2', '已添加 聊天 \"first chat for test.\".');
INSERT INTO `django_admin_log` VALUES ('456', '2010-05-05 11:21:41', '1', '47', '5', 'darkmoon 悬赏 10 银元干掉  root。', '1', '');
INSERT INTO `django_admin_log` VALUES ('457', '2010-05-05 11:56:25', '1', '42', '2', 'CQSSC', '2', '已变更 type for 聊天 \"first chat for test.\". 已添加 抢劫 \"外币\".');
INSERT INTO `django_admin_log` VALUES ('458', '2010-05-05 11:57:36', '1', '42', '2', 'CQSSC', '2', '已添加 帮会成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('459', '2010-05-05 14:46:54', '1', '42', '2', 'CQSSC', '2', '已删除 成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('460', '2010-05-05 17:22:23', '1', '42', '2', 'CQSSC', '2', '已添加 成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('461', '2010-05-05 17:23:57', '1', '42', '2', 'CQSSC', '2', '已删除 成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('462', '2010-05-05 19:20:26', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('463', '2010-05-05 19:25:55', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('464', '2010-05-05 19:31:59', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('465', '2010-05-05 19:42:42', '1', '42', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('466', '2010-05-05 19:48:58', '1', '42', '1', 'CQSSC', '2', '已添加 成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('467', '2010-05-05 20:07:01', '1', '42', '1', 'CQSSC', '2', '已删除 成员 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('468', '2010-05-05 20:07:20', '1', '42', '1', 'CQSSC', '2', '已添加 新闻 \"test news...\".');
INSERT INTO `django_admin_log` VALUES ('469', '2010-05-06 16:22:21', '1', '3', '3', 'darkmoon', '2', '已添加 用户生意 \"darkmoon\'s club\".');
INSERT INTO `django_admin_log` VALUES ('470', '2010-05-06 16:56:46', '1', '3', '3', 'darkmoon', '2', '已删除 用户生意 \"darkmoon\'s club\".');
INSERT INTO `django_admin_log` VALUES ('471', '2010-05-06 16:59:56', '1', '73', '1', 'darkmoon', '1', '');
INSERT INTO `django_admin_log` VALUES ('472', '2010-05-06 17:32:33', '1', '50', '1', 'c-mtv@163.com (darkmoon\'s contact)', '1', '');
INSERT INTO `django_admin_log` VALUES ('473', '2010-05-07 16:02:38', '1', '3', '3', 'darkmoon', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('474', '2010-05-07 16:04:07', '1', '3', '3', 'darkmoon', '2', '已添加 基本资料 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('475', '2010-05-07 16:05:19', '1', '3', '3', 'darkmoon', '2', '已添加 游戏数据 \"darkmoon的游戏数据\".');
INSERT INTO `django_admin_log` VALUES ('476', '2010-05-07 16:08:07', '1', '3', '3', 'darkmoon', '2', '已变更 avatar for 基本资料 \"darkmoon\".');
INSERT INTO `django_admin_log` VALUES ('477', '2010-05-07 16:21:40', '1', '3', '3', 'darkmoon', '2', '已删除 游戏数据 \"darkmoon的游戏数据\".');
INSERT INTO `django_admin_log` VALUES ('478', '2010-05-07 16:22:47', '1', '3', '3', 'darkmoon', '3', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_content_type` VALUES ('33', 'province', 'system', 'province');
INSERT INTO `django_content_type` VALUES ('34', 'event', 'system', 'event');
INSERT INTO `django_content_type` VALUES ('35', 'benefit', 'system', 'benefit');
INSERT INTO `django_content_type` VALUES ('36', 'hospital', 'system', 'hospital');
INSERT INTO `django_content_type` VALUES ('37', 'random event', 'system', 'randomevent');
INSERT INTO `django_content_type` VALUES ('38', 'random event question', 'system', 'randomeventquestion');
INSERT INTO `django_content_type` VALUES ('39', 'random event choice', 'system', 'randomeventchoice');
INSERT INTO `django_content_type` VALUES ('40', 'Robberies', 'system', 'robbery');
INSERT INTO `django_content_type` VALUES ('41', 'Message', 'messages', 'message');
INSERT INTO `django_content_type` VALUES ('42', 'Gang', 'game', 'gang');
INSERT INTO `django_content_type` VALUES ('46', 'Sabotage', 'system', 'sabotage');
INSERT INTO `django_content_type` VALUES ('47', 'Bounty', 'game', 'bounty');
INSERT INTO `django_content_type` VALUES ('48', 'Bank', 'game', 'bank');
INSERT INTO `django_content_type` VALUES ('50', 'contact', 'friends', 'contact');
INSERT INTO `django_content_type` VALUES ('51', 'friendship', 'friends', 'friendship');
INSERT INTO `django_content_type` VALUES ('52', 'join invitation', 'friends', 'joininvitation');
INSERT INTO `django_content_type` VALUES ('53', 'friendship invitation', 'friends', 'friendshipinvitation');
INSERT INTO `django_content_type` VALUES ('54', 'friendship invitation history', 'friends', 'friendshipinvitationhistory');
INSERT INTO `django_content_type` VALUES ('55', 'notice type', 'notification', 'noticetype');
INSERT INTO `django_content_type` VALUES ('56', 'notice setting', 'notification', 'noticesetting');
INSERT INTO `django_content_type` VALUES ('57', 'notice', 'notification', 'notice');
INSERT INTO `django_content_type` VALUES ('58', 'notice queue batch', 'notification', 'noticequeuebatch');
INSERT INTO `django_content_type` VALUES ('59', 'observed item', 'notification', 'observeditem');
INSERT INTO `django_content_type` VALUES ('60', 'Relationship status', 'relationships', 'relationshipstatus');
INSERT INTO `django_content_type` VALUES ('61', 'Relationship', 'relationships', 'relationship');
INSERT INTO `django_content_type` VALUES ('63', 'chat', 'game', 'chat');
INSERT INTO `django_content_type` VALUES ('67', 'invite', 'game', 'ganginvite');
INSERT INTO `django_content_type` VALUES ('68', 'member', 'game', 'gangmember');
INSERT INTO `django_content_type` VALUES ('69', 'news', 'game', 'gangnews');
INSERT INTO `django_content_type` VALUES ('70', 'robbery', 'game', 'gangrobbery');
INSERT INTO `django_content_type` VALUES ('71', 'assault', 'game', 'gangassault');
INSERT INTO `django_content_type` VALUES ('72', 'user business drugs', 'system', 'userbusinessdrug');
INSERT INTO `django_content_type` VALUES ('73', 'guestbook', 'game', 'guestbook');
INSERT INTO `django_content_type` VALUES ('74', 'prison', 'game', 'prison');
INSERT INTO `django_content_type` VALUES ('75', 'rip', 'game', 'rip');
INSERT INTO `django_content_type` VALUES ('76', 'profile', 'account', 'userprofile');
INSERT INTO `django_content_type` VALUES ('77', 'game data', 'account', 'userdata');

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
INSERT INTO `django_session` VALUES ('1f09faf546ea25a3ab010adc2e585bea', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVRJfYXV0aF91c2Vy\nX2JhY2tlbmRxBlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRxB1UN\nX2F1dGhfdXNlcl9pZHEIigEBdS42YzFiMDI5MGJhNjYzMjY0YzE1NWVhMWU3NmMwOTEyMQ==\n', '2010-05-19 16:03:19');
INSERT INTO `django_session` VALUES ('23d64587c0070ce0dc9dae8aa27f6053', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21');
INSERT INTO `django_session` VALUES ('5286edf50ae8d76c4766ee5d9ed97aac', 'gAJ9cQEuODg5ZDEzMzAzNmNlMzJkNDIzZGQzMGM1ZWJhYmFlMGQ=\n', '2010-05-02 20:19:21');
INSERT INTO `django_session` VALUES ('72428d3f08e0427f6a8850ba47a4f659', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-12 18:51:41');
INSERT INTO `django_session` VALUES ('86cac61cc2a7f010c3d465c913cab99a', 'gAJ9cQFVCnRlc3Rjb29raWVxAlUGd29ya2VkcQNzLmJmMjBjODVkMTkwZTExMWEzMTkxMjFhNzIz\nZWI2NGJm\n', '2010-05-02 20:09:30');
INSERT INTO `django_session` VALUES ('9f005bf1fd11cc2e8ff354a75ee459b1', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UHbWVzc2FnZXEEXXEFKFUHc3VjY2Vzc3EGWBwAAABTaXRl\nIHdhcyBhZGRlZCB0byBCb29rbWFya3MucQdlc1UNX2F1dGhfdXNlcl9pZHEIigEBVRJfYXV0aF91\nc2VyX2JhY2tlbmRxCVUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5kcy5Nb2RlbEJhY2tlbmRx\nCnUuMGI4YzhlNzg1MzY0NTc2ZjJlZDQyNTgzMzZkMmNlNmU=\n', '2010-05-02 20:39:45');
INSERT INTO `django_session` VALUES ('d01d78ab0e8391f7e31e22691afee012', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-19 18:32:12');
INSERT INTO `django_session` VALUES ('d71173bd46cd82de4d4b4aa96fc80479', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-18 14:55:50');
INSERT INTO `django_session` VALUES ('d81e95756a970d5eba5f0681d1d55287', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS5jYTU4MjIzN2FhYzE0NGI0ZWMw\nMDIxNzMwY2ZhZTQ3Mw==\n', '2010-05-02 20:09:08');
INSERT INTO `django_session` VALUES ('edc466f72a1ac08f5718509fa95aabfd', 'gAJ9cQEoVQlncmFwcGVsbGlxAn1xA1UEaG9tZXEEWAcAAAAvYWRtaW4vcQVzVQ1fYXV0aF91c2Vy\nX2lkcQaKAQFVEl9hdXRoX3VzZXJfYmFja2VuZHEHVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tl\nbmRzLk1vZGVsQmFja2VuZHEIdS45NzY0OGNkZmI3YzMwY2EzM2UyYjlkYWE1OWJkZjBjYw==\n', '2010-05-21 16:40:34');

-- ----------------------------
-- Table structure for `django_site`
-- ----------------------------
DROP TABLE IF EXISTS `django_site`;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_site
-- ----------------------------
INSERT INTO `django_site` VALUES ('1', 'crims.info', 'crims.info');
INSERT INTO `django_site` VALUES ('2', 'hxxps.com', 'hxxps.com');
INSERT INTO `django_site` VALUES ('3', 'hxxps.info', 'hxxps.info');

-- ----------------------------
-- Table structure for `friends_contact`
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
-- Records of friends_contact
-- ----------------------------

-- ----------------------------
-- Table structure for `friends_contact_users`
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
-- Records of friends_contact_users
-- ----------------------------

-- ----------------------------
-- Table structure for `friends_friendship`
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
-- Records of friends_friendship
-- ----------------------------

-- ----------------------------
-- Table structure for `friends_friendshipinvitation`
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
-- Records of friends_friendshipinvitation
-- ----------------------------

-- ----------------------------
-- Table structure for `friends_friendshipinvitationhistory`
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
-- Records of friends_friendshipinvitationhistory
-- ----------------------------

-- ----------------------------
-- Table structure for `friends_joininvitation`
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
-- Records of friends_joininvitation
-- ----------------------------

-- ----------------------------
-- Table structure for `game_bank`
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
-- Records of game_bank
-- ----------------------------
INSERT INTO `game_bank` VALUES ('4', '1', '100', '2010-05-04 16:19:44', '2010-05-04 16:19:44');

-- ----------------------------
-- Table structure for `game_bounty`
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
-- Records of game_bounty
-- ----------------------------

-- ----------------------------
-- Table structure for `game_chat`
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of game_chat
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gang`
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
-- Records of game_gang
-- ----------------------------
INSERT INTO `game_gang` VALUES ('1', 'CQSSC', 'wd', 'uploads/gang/11____.png', '2010-05-05 19:42:42', '1', '1', '1', null);

-- ----------------------------
-- Table structure for `game_gangassault`
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
-- Records of game_gangassault
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gangassault_accepted`
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
-- Records of game_gangassault_accepted
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gangassault_declined`
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
-- Records of game_gangassault_declined
-- ----------------------------

-- ----------------------------
-- Table structure for `game_ganginvite`
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
-- Records of game_ganginvite
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gangmember`
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
-- Records of game_gangmember
-- ----------------------------
INSERT INTO `game_gangmember` VALUES ('2', '1', '1', '2010-05-05 19:42:42');

-- ----------------------------
-- Table structure for `game_gangnews`
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
-- Records of game_gangnews
-- ----------------------------
INSERT INTO `game_gangnews` VALUES ('1', 'test news...', 'test news...', '1', '1', '2010-05-05 20:07:20');

-- ----------------------------
-- Table structure for `game_gangrobbery`
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
-- Records of game_gangrobbery
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gangrobbery_accepted`
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
-- Records of game_gangrobbery_accepted
-- ----------------------------

-- ----------------------------
-- Table structure for `game_gangrobbery_declined`
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
-- Records of game_gangrobbery_declined
-- ----------------------------

-- ----------------------------
-- Table structure for `game_guestbook`
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
-- Records of game_guestbook
-- ----------------------------

-- ----------------------------
-- Table structure for `game_prison`
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
-- Records of game_prison
-- ----------------------------

-- ----------------------------
-- Table structure for `game_rip`
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
-- Records of game_rip
-- ----------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_bookmarkitem
-- ----------------------------

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
INSERT INTO `grappelli_navigation` VALUES ('1', '媒体管理', '0');
INSERT INTO `grappelli_navigation` VALUES ('2', '内容', '1');
INSERT INTO `grappelli_navigation` VALUES ('3', '外部', '2');
INSERT INTO `grappelli_navigation` VALUES ('4', '文档', '3');

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
INSERT INTO `grappelli_navigationitem` VALUES ('1', '1', '文件浏览', '/admin/filebrowser/browse/', '1', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('2', '2', '主页', '/admin/', '1', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('3', '2', 'Grappelli', '/admin/grappelli/', '1', '1');
INSERT INTO `grappelli_navigationitem` VALUES ('4', '3', 'Grappelli GoogleCode', 'http://code.google.com/p/django-grappelli/', '2', '0');
INSERT INTO `grappelli_navigationitem` VALUES ('5', '4', '帮助', '/grappelli/help/', '1', '0');

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
  CONSTRAINT `navigationitem_id_refs_id_fbdd09fc` FOREIGN KEY (`navigationitem_id`) REFERENCES `grappelli_navigationitem` (`id`),
  CONSTRAINT `user_id_refs_id_c396c6b7` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of grappelli_navigationitem_users
-- ----------------------------

-- ----------------------------
-- Table structure for `messages_message`
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
-- Records of messages_message
-- ----------------------------
INSERT INTO `messages_message` VALUES ('1', 'test', 'test', '1', '1', null, '2010-04-29 17:44:16', '2010-04-29 17:44:28', '2010-04-29 17:44:35', null, null);
INSERT INTO `messages_message` VALUES ('2', '回复: test', 'root 写道:\r\n> test\r\n\r\nsfdsf', '1', '1', '1', '2010-04-29 17:44:35', '2010-04-29 17:44:41', null, null, null);

-- ----------------------------
-- Table structure for `notification_notice`
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
-- Records of notification_notice
-- ----------------------------

-- ----------------------------
-- Table structure for `notification_noticequeuebatch`
-- ----------------------------
DROP TABLE IF EXISTS `notification_noticequeuebatch`;
CREATE TABLE `notification_noticequeuebatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pickled_data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of notification_noticequeuebatch
-- ----------------------------

-- ----------------------------
-- Table structure for `notification_noticesetting`
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
-- Records of notification_noticesetting
-- ----------------------------

-- ----------------------------
-- Table structure for `notification_noticetype`
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
-- Records of notification_noticetype
-- ----------------------------
INSERT INTO `notification_noticetype` VALUES ('1', 'messages_received', 'Message Received', 'you have received a message', '2');
INSERT INTO `notification_noticetype` VALUES ('2', 'messages_sent', 'Message Sent', 'you have sent a message', '1');
INSERT INTO `notification_noticetype` VALUES ('3', 'messages_replied', 'Message Replied', 'you have replied to a message', '1');
INSERT INTO `notification_noticetype` VALUES ('4', 'messages_reply_received', 'Reply Received', 'you have received a reply to a message', '2');
INSERT INTO `notification_noticetype` VALUES ('5', 'messages_deleted', 'Message Deleted', 'you have deleted a message', '1');
INSERT INTO `notification_noticetype` VALUES ('6', 'messages_recovered', 'Message Recovered', 'you have undeleted a message', '1');
INSERT INTO `notification_noticetype` VALUES ('7', 'friends_invite', 'Invitation Received', 'you have received an invitation', '2');
INSERT INTO `notification_noticetype` VALUES ('8', 'friends_invite_sent', 'Invitation Sent', 'you have sent an invitation', '1');
INSERT INTO `notification_noticetype` VALUES ('9', 'friends_accept', 'Acceptance Received', 'an invitation you sent has been accepted', '2');
INSERT INTO `notification_noticetype` VALUES ('10', 'friends_accept_sent', 'Acceptance Sent', 'you have accepted an invitation you received', '1');
INSERT INTO `notification_noticetype` VALUES ('11', 'friends_otherconnect', 'Other Connection', 'one of your friends has a new connection', '2');
INSERT INTO `notification_noticetype` VALUES ('12', 'join_accept', 'Join Invitation Accepted', 'an invitation you sent to join this site has been accepted', '2');

-- ----------------------------
-- Table structure for `notification_observeditem`
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
-- Records of notification_observeditem
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
-- Table structure for `relationships_relationship`
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
-- Records of relationships_relationship
-- ----------------------------

-- ----------------------------
-- Table structure for `relationships_relationshipstatus`
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
-- Records of relationships_relationshipstatus
-- ----------------------------
INSERT INTO `relationships_relationshipstatus` VALUES ('1', 'Following', 'follow', 'following', 'followers', 'friends', '0', '0');
INSERT INTO `relationships_relationshipstatus` VALUES ('2', 'Blocking', 'block', 'blocking', 'blockers', '!', '1', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_armor
-- ----------------------------
INSERT INTO `system_armor` VALUES ('1', '尿布', '8', '150', 'uploads/armor/diper.jpg', '2010-04-19 19:05:42');
INSERT INTO `system_armor` VALUES ('2', '皮夹克', '32', '1250', 'uploads/armor/leather_jacket.jpg', '2010-04-19 19:06:07');
INSERT INTO `system_armor` VALUES ('3', '合金盔甲', '120', '15000', 'uploads/armor/shining_body_armor.jpg', '2010-04-19 19:06:29');
INSERT INTO `system_armor` VALUES ('4', '防弹背心', '400', '2100000', 'uploads/armor/body_armour.jpg', '2010-04-19 19:07:12');
INSERT INTO `system_armor` VALUES ('5', '纳米战斗衣', '1200', '6200000', 'uploads/armor/nano_fiber_combat_jacket.jpg', '2010-04-19 19:07:48');
INSERT INTO `system_armor` VALUES ('6', '反恐特勤装', '2000', '10000000', 'uploads/armor/nomex_plated_armour.jpg', '2010-04-19 19:08:18');

-- ----------------------------
-- Table structure for `system_avatar`
-- ----------------------------
DROP TABLE IF EXISTS `system_avatar`;
CREATE TABLE `system_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_avatar
-- ----------------------------
INSERT INTO `system_avatar` VALUES ('6', 'uploads/avatar/avatar_1.jpg', '2010-04-19 18:40:13');
INSERT INTO `system_avatar` VALUES ('7', 'uploads/avatar/avatar_2.jpg', '2010-04-19 18:41:51');
INSERT INTO `system_avatar` VALUES ('8', 'uploads/avatar/avatar_4.jpg', '2010-04-19 18:42:15');
INSERT INTO `system_avatar` VALUES ('9', 'uploads/avatar/avatar_5.jpg', '2010-04-19 18:42:22');
INSERT INTO `system_avatar` VALUES ('10', 'uploads/avatar/avatar_6.jpg', '2010-04-19 18:42:28');
INSERT INTO `system_avatar` VALUES ('11', 'uploads/avatar/avatar_7.jpg', '2010-04-19 18:42:36');
INSERT INTO `system_avatar` VALUES ('12', 'uploads/avatar/avatar_8.jpg', '2010-04-19 18:42:44');
INSERT INTO `system_avatar` VALUES ('13', 'uploads/avatar/avatar_9.jpg', '2010-04-19 18:42:51');
INSERT INTO `system_avatar` VALUES ('14', 'uploads/avatar/avatar_10.jpg', '2010-04-19 18:42:59');
INSERT INTO `system_avatar` VALUES ('15', 'uploads/avatar/avatar_11.jpg', '2010-04-19 18:43:07');
INSERT INTO `system_avatar` VALUES ('16', 'uploads/avatar/avatar_12.jpg', '2010-04-19 18:43:16');
INSERT INTO `system_avatar` VALUES ('17', 'uploads/avatar/avatar_13.jpg', '2010-04-19 18:43:23');
INSERT INTO `system_avatar` VALUES ('18', 'uploads/avatar/avatar_14.jpg', '2010-04-19 18:43:32');
INSERT INTO `system_avatar` VALUES ('19', 'uploads/avatar/avatar_15.jpg', '2010-04-19 18:43:40');
INSERT INTO `system_avatar` VALUES ('20', 'uploads/avatar/avatar_16.jpg', '2010-04-19 18:43:49');
INSERT INTO `system_avatar` VALUES ('21', 'uploads/avatar/avatar_17.jpg', '2010-04-19 18:43:58');
INSERT INTO `system_avatar` VALUES ('22', 'uploads/avatar/avatar_18.jpg', '2010-04-19 18:44:10');
INSERT INTO `system_avatar` VALUES ('23', 'uploads/avatar/avatar_19.jpg', '2010-04-19 18:44:20');
INSERT INTO `system_avatar` VALUES ('24', 'uploads/avatar/avatar_26.jpg', '2010-04-19 18:44:29');
INSERT INTO `system_avatar` VALUES ('25', 'uploads/avatar/avatar_27.jpg', '2010-04-19 18:44:37');
INSERT INTO `system_avatar` VALUES ('26', 'uploads/avatar/avatar_28.jpg', '2010-04-19 18:44:46');
INSERT INTO `system_avatar` VALUES ('27', 'uploads/avatar/avatar_29.jpg', '2010-04-19 18:44:53');
INSERT INTO `system_avatar` VALUES ('28', 'uploads/avatar/avatar_30.jpg', '2010-04-19 18:45:02');
INSERT INTO `system_avatar` VALUES ('29', 'uploads/avatar/avatar_31.jpg', '2010-04-19 18:45:11');
INSERT INTO `system_avatar` VALUES ('30', 'uploads/avatar/avatar_32.jpg', '2010-04-19 18:45:19');
INSERT INTO `system_avatar` VALUES ('31', 'uploads/avatar/avatar_33.jpg', '2010-04-19 18:45:27');
INSERT INTO `system_avatar` VALUES ('32', 'uploads/avatar/avatar_34.jpg', '2010-04-19 18:45:37');
INSERT INTO `system_avatar` VALUES ('33', 'uploads/avatar/avatar_35.jpg', '2010-04-19 18:45:44');
INSERT INTO `system_avatar` VALUES ('34', 'uploads/avatar/avatar_36.jpg', '2010-04-19 18:45:51');
INSERT INTO `system_avatar` VALUES ('35', 'uploads/avatar/avatar_37.jpg', '2010-04-19 18:45:57');
INSERT INTO `system_avatar` VALUES ('36', 'uploads/avatar/avatar_38.jpg', '2010-04-19 18:46:03');
INSERT INTO `system_avatar` VALUES ('37', 'uploads/avatar/avatar_39.jpg', '2010-04-19 18:46:11');
INSERT INTO `system_avatar` VALUES ('38', 'uploads/avatar/avatar_40.jpg', '2010-04-19 18:46:17');
INSERT INTO `system_avatar` VALUES ('39', 'uploads/avatar/avatar_41.jpg', '2010-04-19 18:46:22');
INSERT INTO `system_avatar` VALUES ('40', 'uploads/avatar/avatar_42.jpg', '2010-04-19 18:46:28');
INSERT INTO `system_avatar` VALUES ('41', 'uploads/avatar/avatar_43.jpg', '2010-04-19 18:46:35');
INSERT INTO `system_avatar` VALUES ('42', 'uploads/avatar/avatar_44.jpg', '2010-04-19 18:46:41');
INSERT INTO `system_avatar` VALUES ('43', 'uploads/avatar/avatar_45.jpg', '2010-04-19 18:46:47');
INSERT INTO `system_avatar` VALUES ('44', 'uploads/avatar/avatar_46.jpg', '2010-04-19 18:46:52');
INSERT INTO `system_avatar` VALUES ('45', 'uploads/avatar/avatar_47.jpg', '2010-04-19 18:46:57');
INSERT INTO `system_avatar` VALUES ('46', 'uploads/avatar/avatar_48.jpg', '2010-04-19 18:47:02');
INSERT INTO `system_avatar` VALUES ('47', 'uploads/avatar/avatar_49.jpg', '2010-04-19 18:47:07');
INSERT INTO `system_avatar` VALUES ('48', 'uploads/avatar/avatar_50.jpg', '2010-04-19 18:47:12');
INSERT INTO `system_avatar` VALUES ('49', 'uploads/avatar/avatar_51.jpg', '2010-04-19 18:47:21');
INSERT INTO `system_avatar` VALUES ('50', 'uploads/avatar/avatar_52.jpg', '2010-04-19 18:47:27');
INSERT INTO `system_avatar` VALUES ('51', 'uploads/avatar/avatar_53.jpg', '2010-04-19 18:47:33');

-- ----------------------------
-- Table structure for `system_benefit`
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
-- Records of system_benefit
-- ----------------------------
INSERT INTO `system_benefit` VALUES ('1', '妓女赚钱加速', '看看我的这些超级药丸啊。他们的混合了荷尔蒙，避孕药跟春药，你的小姐每个都会变成名模身材海咪咪。这些药片将会增加你10% 的收入，只需要花你 10 个点数。这些药片将会在你的小姐体内持续 28 个游戏日。', 'hooker', '10', '2010-04-21 10:36:03');
INSERT INTO `system_benefit` VALUES ('2', '武器增强器', '想不想要来点大家伙?我这边有些烫手的，可以增加你30%的武器威力.像我手上这件增强器可以让你 28天内威力增强不少,心动了吗?我只需要少少的 10点游戏点数.如果愿意买的话我另外免费给你个杀必速,这个润滑剂将可以让你的武器增加 30% 的威力.', 'weapon', '10', '2010-04-21 10:36:33');
INSERT INTO `system_benefit` VALUES ('3', '产量加速器', '如果你想的话我可以提供一些廉价劳力给你，这些孤儿只要提供给他们住所，他们将会为你工作。他们工作差些，但他们会增加你的10%的毒品产量 。你只需要 10 游戏点数就可以租用它们 28天。不要担心，如果不小心他们死掉的话，安家费已经算在里面了。', 'building', '10', '2010-04-21 10:37:06');
INSERT INTO `system_benefit` VALUES ('4', '避孕套', '过来！您这肮脏，低俗的一名男子。你能相信吗？我这边有提供卫生套，想想看得到了性病不仅会让你的生活受到影响，连日常打里事务都没办法了，如果为了你的身体健康与名声着想，多保护一下自己吧。我这边有贩卖无敌防护卫生套，它的效用远胜于市面上所有的牌子，可以完全隔绝性病对你的影响，在，你每次使用一个套子，可以让你28个游戏天内不会得到性病，每次你可以跟我买 100个，而这些只需要花你 10点的点数。', 'condom', '10', '2010-04-21 10:37:19');

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_building
-- ----------------------------
INSERT INTO `system_building` VALUES ('1', '烟草', '15', '12', '4500', 'uploads/building/weedplant.jpg', '2', '2010-04-19 19:36:20');
INSERT INTO `system_building` VALUES ('2', '私酒', '10', '10', '5000', 'uploads/building/moonshiner.jpg', '3', '2010-04-19 19:37:10');
INSERT INTO `system_building` VALUES ('3', '大麻种植地', '45', '20', '6500', 'uploads/building/hashplant.jpg', '5', '2010-04-19 19:37:45');
INSERT INTO `system_building` VALUES ('4', '酿酒厂', '1100', '150', '40000', 'uploads/building/brewery.jpg', '3', '2010-04-19 19:38:21');
INSERT INTO `system_building` VALUES ('5', '药房', '1300', '250', '50000', 'uploads/building/pharmacy.jpg', '1', '2010-04-19 19:38:53');
INSERT INTO `system_building` VALUES ('6', '蘑菇种植基地', '800', '320', '60000', 'uploads/building/mushrooms.jpg', '4', '2010-04-19 19:39:26');
INSERT INTO `system_building` VALUES ('7', '烟草种植园', '1600', '350', '75000', 'uploads/building/weedfield.jpg', '2', '2010-04-19 19:40:01');
INSERT INTO `system_building` VALUES ('8', '吗啡实验室', '60', '900', '80000', 'uploads/building/morphinelab.jpg', '13', '2010-04-19 19:40:31');
INSERT INTO `system_building` VALUES ('9', '迷幻药实验室', '590', '500', '100000', 'uploads/building/lsd_lab.jpg', '6', '2010-04-19 19:41:12');
INSERT INTO `system_building` VALUES ('10', '摇头丸实验室', '340', '700', '110000', 'uploads/building/ecstacy_lab.jpg', '8', '2010-04-19 19:41:45');
INSERT INTO `system_building` VALUES ('11', '罂粟田', '240', '1020', '150000', 'uploads/building/opiumfield.jpg', '10', '2010-04-19 19:42:13');
INSERT INTO `system_building` VALUES ('12', '迷奸药实验室', '650', '800', '150000', 'uploads/building/ghblab.jpg', '7', '2010-04-19 19:42:53');
INSERT INTO `system_building` VALUES ('13', 'K他命实验室', '165', '650', '180000', 'uploads/building/specialklab.jpg', '12', '2010-04-19 19:43:31');
INSERT INTO `system_building` VALUES ('14', '可卡因工厂', '120', '1500', '250000', 'uploads/building/cocaine.jpg', '11', '2010-04-19 19:44:05');
INSERT INTO `system_building` VALUES ('15', '安非他命实验室', '160', '2000', '300000', 'uploads/building/amphetamine.jpg', '9', '2010-04-19 19:44:39');
INSERT INTO `system_building` VALUES ('16', '海洛英工厂', '60', '5000', '500000', 'uploads/building/heroine_lab.jpg', '14', '2010-04-19 19:45:15');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_business
-- ----------------------------
INSERT INTO `system_business` VALUES ('1', '夜总会', 'uploads/business/nightclub.jpg', '25', '10000', '50', 'bar', '3', '2010-04-19 19:50:16');
INSERT INTO `system_business` VALUES ('2', '狂欢派对', 'uploads/business/rave.jpg', '100', '25000', '100', 'bar', '10', '2010-04-19 19:51:00');
INSERT INTO `system_business` VALUES ('3', '青楼', 'uploads/business/whorehouse.jpg', '10', '40000', '80', 'club', '5', '2010-04-19 19:51:41');
INSERT INTO `system_business` VALUES ('4', '怡红院', 'uploads/business/hooker_manison.jpg', '30', '110000', '230', 'club', '15', '2010-04-19 19:52:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_character
-- ----------------------------
INSERT INTO `system_character` VALUES ('1', '领班', 'uploads/character/pimp_head.png', 'uploads/character/pimp_head.png', '4', '4', '6', '6', '2010-04-19 19:55:42');
INSERT INTO `system_character` VALUES ('2', '杀手', 'uploads/character/hitman_head.png', 'uploads/character/hitman_head.png', '4', '6', '4', '6', '2010-04-19 19:56:13');
INSERT INTO `system_character` VALUES ('3', '商人', 'uploads/character/biz_head.png', 'uploads/character/biz_head.png', '6', '4', '4', '6', '2010-04-19 19:56:46');
INSERT INTO `system_character` VALUES ('4', '强盗', 'uploads/character/robber_head.png', 'uploads/character/robber_head.png', '6', '6', '4', '4', '2010-04-19 19:57:21');
INSERT INTO `system_character` VALUES ('5', '黑社会', 'uploads/character/gangster_head.png', 'uploads/character/gangster_head.png', '5', '5', '5', '5', '2010-04-19 19:57:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_drug
-- ----------------------------
INSERT INTO `system_drug` VALUES ('1', '止痛药', '6', '0', '99', '1', '2010-04-19 19:11:53', '2010-04-19 19:26:26');
INSERT INTO `system_drug` VALUES ('2', '香烟', '5', '0', '99', '1', '2010-04-19 19:12:26', '2010-04-19 19:26:40');
INSERT INTO `system_drug` VALUES ('3', '酒', '7', '0', '50', '1', '2010-04-19 19:13:13', '2010-04-19 19:13:13');
INSERT INTO `system_drug` VALUES ('4', '迷幻蘑菇', '10', '0', '50', '1', '2010-04-19 19:13:41', '2010-04-19 19:13:41');
INSERT INTO `system_drug` VALUES ('5', '大麻', '12', '0', '35', '2', '2010-04-19 19:14:32', '2010-04-19 19:27:04');
INSERT INTO `system_drug` VALUES ('6', '迷幻药', '13', '0', '35', '2', '2010-04-19 19:15:06', '2010-04-19 19:27:32');
INSERT INTO `system_drug` VALUES ('7', '迷奸药', '10', '0', '25', '3', '2010-04-19 19:15:42', '2010-04-19 19:27:54');
INSERT INTO `system_drug` VALUES ('8', '摇头丸', '29', '0', '25', '3', '2010-04-19 19:19:41', '2010-04-19 19:28:24');
INSERT INTO `system_drug` VALUES ('9', '安非他命', '60', '0', '20', '4', '2010-04-19 19:28:48', '2010-04-19 19:28:48');
INSERT INTO `system_drug` VALUES ('10', '鸦片', '37', '0', '20', '4', '2010-04-19 19:29:06', '2010-04-19 19:29:06');
INSERT INTO `system_drug` VALUES ('11', '可卡因', '72', '0', '15', '4', '2010-04-19 19:29:22', '2010-04-19 19:29:22');
INSERT INTO `system_drug` VALUES ('12', 'K他命', '99', '0', '15', '5', '2010-04-19 19:29:37', '2010-04-19 19:29:37');
INSERT INTO `system_drug` VALUES ('13', '吗啡', '117', '0', '13', '5', '2010-04-19 19:29:58', '2010-04-19 19:29:58');
INSERT INTO `system_drug` VALUES ('14', '海洛英', '238', '0', '13', '5', '2010-04-19 19:30:17', '2010-04-19 19:30:17');

-- ----------------------------
-- Table structure for `system_event`
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
-- Records of system_event
-- ----------------------------
INSERT INTO `system_event` VALUES ('1', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.', 'robbery', 'uploads/event/newspaper_raid.jpg', '0.1000', null);
INSERT INTO `system_event` VALUES ('2', 'DRUG PRICES DOWN', 'ads', 'drug', 'uploads/event/newspaper_drugs.jpg', '-0.2000', '9');
INSERT INTO `system_event` VALUES ('3', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.', 'building', 'uploads/event/warren_buffet.jpg', '0.1000', null);
INSERT INTO `system_event` VALUES ('4', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.', 'drug', 'uploads/event/newspaper_drugs.jpg', '0.2000', '9');
INSERT INTO `system_event` VALUES ('5', 'CARNIVAL!', 'Today it is carnival day in The Bund!', 'drug', 'uploads/event/carnival.jpg', '-0.2000', null);
INSERT INTO `system_event` VALUES ('6', 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!', 'robbery', 'uploads/event/oil_sheikh.jpg', '0.0000', null);
INSERT INTO `system_event` VALUES ('7', 'BILL GATES IN TOWN!', 'Bill Gates is visiting the city today to promote his new software and try some new bugs.', 'business', 'uploads/event/bill_gates.jpg', '0.0000', null);
INSERT INTO `system_event` VALUES ('8', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.', 'dock', 'uploads/event/newspaper_ship.jpg', '0.2000', '13');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_guard
-- ----------------------------
INSERT INTO `system_guard` VALUES ('1', '恶狗', '20', 'uploads/guard/evil_dog.jpg', '3000', '2010-04-21 10:45:33');
INSERT INTO `system_guard` VALUES ('2', '杀手', '80', 'uploads/guard/guard5.jpg', '15000', '2010-04-21 10:46:07');
INSERT INTO `system_guard` VALUES ('3', '疯狂守卫', '200', 'uploads/guard/guard4.jpg', '45000', '2010-04-21 10:46:28');
INSERT INTO `system_guard` VALUES ('4', '俄罗斯前特种兵', '350', 'uploads/guard/guard6.jpg', '90000', '2010-04-21 10:46:56');
INSERT INTO `system_guard` VALUES ('5', '专业保镖', '500', 'uploads/guard/guard3.jpg', '200000', '2010-04-21 10:47:19');
INSERT INTO `system_guard` VALUES ('6', '终极保镖', '800', 'uploads/guard/guard.jpg', '600000', '2010-04-21 10:47:38');

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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_hooker
-- ----------------------------
INSERT INTO `system_hooker` VALUES ('1', 'Dolly', 'uploads/hooker/dolly.jpg', '400', '30', '6', '0.0100', '0', '10', '1', '2010-04-21 10:52:57', '2010-04-21 10:52:57');
INSERT INTO `system_hooker` VALUES ('2', 'Heinrich', 'uploads/hooker/heinrich.jpg', '600', '50', '10', '0.0100', '0', '20', '1', '2010-04-21 10:54:13', '2010-04-21 10:54:13');
INSERT INTO `system_hooker` VALUES ('3', 'Britney', 'uploads/hooker/hooker2.jpg', '800', '60', '15', '0.0100', '0', '30', '1', '2010-04-21 10:56:35', '2010-04-21 10:56:35');
INSERT INTO `system_hooker` VALUES ('4', 'Mount Tse Tung', 'uploads/hooker/hooker4.jpg', '1200', '90', '24', '0.0100', '0', '50', '1', '2010-04-21 10:57:27', '2010-04-21 10:57:27');
INSERT INTO `system_hooker` VALUES ('5', 'Marilyn', 'uploads/hooker/hooker3.jpg', '3500', '270', '46', '0.0100', '0', '80', '1', '2010-04-21 10:58:14', '2010-04-21 10:58:14');
INSERT INTO `system_hooker` VALUES ('6', 'Candy', 'uploads/hooker/hooker1.jpg', '3900', '300', '55', '0.0100', '0', '100', '2', '2010-04-21 10:59:17', '2010-04-21 10:59:17');
INSERT INTO `system_hooker` VALUES ('7', 'Bell', 'uploads/hooker/bell.jpg', '4000', '330', '66', '0.0100', '0', '100', '2', '2010-04-21 11:00:20', '2010-04-21 11:00:20');
INSERT INTO `system_hooker` VALUES ('8', 'Patricia', 'uploads/hooker/patricia.jpg', '4200', '360', '77', '0.0100', '0', '100', '2', '2010-04-21 11:01:04', '2010-04-21 11:01:04');
INSERT INTO `system_hooker` VALUES ('9', 'Claire', 'uploads/hooker/claire.jpg', '4500', '390', '86', '0.0100', '0', '100', '2', '2010-04-21 11:03:16', '2010-04-21 11:03:16');
INSERT INTO `system_hooker` VALUES ('10', 'Crystal', 'uploads/hooker/crystal.jpg', '5200', '450', '96', '0.0100', '0', '100', '2', '2010-04-21 11:03:55', '2010-04-21 11:03:55');
INSERT INTO `system_hooker` VALUES ('11', 'Valerie', 'uploads/hooker/valerie.jpg', '7000', '600', '116', '0.0100', '0', '100', '2', '2010-04-21 11:04:54', '2010-04-21 11:04:54');
INSERT INTO `system_hooker` VALUES ('12', 'Chessy', 'uploads/hooker/chessy.jpg', '8400', '720', '130', '0.0100', '0', '100', '2', '2010-04-21 11:06:38', '2010-04-21 11:06:38');
INSERT INTO `system_hooker` VALUES ('13', 'Denim Daisy', 'uploads/hooker/denim_daisy.jpg', '9500', '810', '142', '0.0100', '0', '100', '2', '2010-04-21 11:07:33', '2010-04-21 11:07:33');
INSERT INTO `system_hooker` VALUES ('14', 'Head Nurse', 'uploads/hooker/head_nurse.jpg', '12500', '1050', '168', '0.0100', '0', '100', '2', '2010-04-21 11:08:24', '2010-04-21 11:08:24');
INSERT INTO `system_hooker` VALUES ('15', 'Cindy', 'uploads/hooker/cindy.jpg', '14000', '1170', '182', '0.0100', '0', '100', '2', '2010-04-21 11:09:09', '2010-04-21 11:09:09');
INSERT INTO `system_hooker` VALUES ('16', 'George', 'uploads/hooker/george.jpg', '14500', '1200', '195', '0.0100', '0', '100', '2', '2010-04-21 11:09:53', '2010-04-21 11:09:53');
INSERT INTO `system_hooker` VALUES ('17', 'Gothic Goddess', 'uploads/hooker/gothic_goddess.jpg', '15000', '1350', '208', '0.0100', '0', '100', '2', '2010-04-21 11:10:31', '2010-04-21 11:10:31');
INSERT INTO `system_hooker` VALUES ('18', 'Pearl', 'uploads/hooker/pearl.jpg', '16500', '1500', '223', '0.0100', '0', '100', '2', '2010-04-21 11:11:10', '2010-04-21 11:11:10');
INSERT INTO `system_hooker` VALUES ('19', 'Miss FBI', 'uploads/hooker/miss_fbi.jpg', '24500', '2100', '270', '0.0100', '0', '100', '2', '2010-04-21 11:12:15', '2010-04-21 11:12:15');
INSERT INTO `system_hooker` VALUES ('20', 'French Maid Fifi', 'uploads/hooker/fifi_french_maid.jpg', '27500', '2400', '295', '0.0100', '0', '100', '2', '2010-04-21 11:13:31', '2010-04-21 11:13:31');
INSERT INTO `system_hooker` VALUES ('21', 'Darling Devil', 'uploads/hooker/darling_devil.jpg', '30000', '3000', '315', '0.0100', '0', '100', '2', '2010-04-21 11:14:12', '2010-04-21 11:14:12');
INSERT INTO `system_hooker` VALUES ('22', 'Sergeant Sexy', 'uploads/hooker/sergeant_sexy.jpg', '55000', '4500', '447', '0.0100', '0', '100', '2', '2010-04-21 11:14:51', '2010-04-21 11:14:51');
INSERT INTO `system_hooker` VALUES ('23', 'Jessica', 'uploads/hooker/jessica.jpg', '63000', '5400', '497', '0.0100', '0', '100', '2', '2010-04-21 11:15:22', '2010-04-21 11:15:22');
INSERT INTO `system_hooker` VALUES ('24', 'Leonard', 'uploads/hooker/leonard.jpg', '70000', '6600', '543', '0.0100', '0', '100', '2', '2010-04-21 11:15:53', '2010-04-21 11:15:53');
INSERT INTO `system_hooker` VALUES ('25', 'Bunnie', 'uploads/hooker/bunnie.jpg', '80000', '7500', '603', '0.0100', '0', '100', '2', '2010-04-21 11:16:25', '2010-04-21 11:16:25');
INSERT INTO `system_hooker` VALUES ('26', 'Mrs. Robinson', 'uploads/hooker/mrs_robinson.jpg', '100000', '9000', '710', '0.0100', '0', '100', '2', '2010-04-21 11:17:22', '2010-04-21 11:17:22');
INSERT INTO `system_hooker` VALUES ('27', 'Mr Love', 'uploads/hooker/mr_love.jpg', '150000', '12000', '967', '0.0100', '0', '100', '2', '2010-04-21 11:18:21', '2010-04-21 11:18:21');
INSERT INTO `system_hooker` VALUES ('28', 'Lill &  Jill', 'uploads/hooker/lill_jill.jpg', '240000', '21000', '1428', '0.0100', '0', '100', '2', '2010-04-21 11:19:25', '2010-04-21 11:19:25');
INSERT INTO `system_hooker` VALUES ('29', 'The Twins', 'uploads/hooker/the_twins.jpg', '430000', '30000', '2395', '0.0100', '0', '100', '2', '2010-04-21 11:20:05', '2010-04-21 11:20:05');
INSERT INTO `system_hooker` VALUES ('30', 'Slim Susy', 'uploads/hooker/slimsusy.jpg', '650000', '42000', '3513', '0.0100', '0', '100', '2', '2010-04-21 11:20:43', '2010-04-21 11:20:43');
INSERT INTO `system_hooker` VALUES ('31', 'SM Babe', 'uploads/hooker/smbabe.jpg', '800000', '54000', '4308', '0.0100', '0', '100', '2', '2010-04-21 11:21:15', '2010-04-21 11:21:15');
INSERT INTO `system_hooker` VALUES ('32', 'Miss Blonde', 'uploads/hooker/missblonde.jpg', '1200000', '72000', '6350', '0.0100', '0', '100', '2', '2010-04-21 11:22:23', '2010-04-21 11:22:23');
INSERT INTO `system_hooker` VALUES ('33', 'Bobbi', 'uploads/hooker/bobbi.jpg', '0', '450', '96', '0.0100', '1', '100', '2', '2010-04-21 11:23:13', '2010-04-21 11:23:13');
INSERT INTO `system_hooker` VALUES ('34', 'Woman of Wonder', 'uploads/hooker/woman_of_wonder.jpg', '9000', '650', '120', '0.0100', '1', '100', '2', '2010-04-21 11:24:31', '2010-04-21 11:24:31');
INSERT INTO `system_hooker` VALUES ('35', 'Rhinogirl', 'uploads/hooker/rhinogirl.jpg', '10000', '720', '150', '0.0100', '1', '100', '20', '2010-04-21 11:25:23', '2010-04-21 11:25:23');

-- ----------------------------
-- Table structure for `system_hospital`
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
-- Records of system_hospital
-- ----------------------------
INSERT INTO `system_hospital` VALUES ('1', '脑激素', 'intelligence', '23', '2010-04-21 14:48:55');
INSERT INTO `system_hospital` VALUES ('2', '性激素', 'charisma', '23', '2010-04-21 14:49:11');
INSERT INTO `system_hospital` VALUES ('3', '肌肉生长素', 'tolerance', '23', '2010-04-21 14:49:26');
INSERT INTO `system_hospital` VALUES ('4', '类固醇', 'strength', '23', '2010-04-21 14:49:38');
INSERT INTO `system_hospital` VALUES ('5', '美沙酮', 'addiction', '500', '2010-04-21 14:49:56');

-- ----------------------------
-- Table structure for `system_province`
-- ----------------------------
DROP TABLE IF EXISTS `system_province`;
CREATE TABLE `system_province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `icon` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_province
-- ----------------------------
INSERT INTO `system_province` VALUES ('1', '北京', '京');
INSERT INTO `system_province` VALUES ('2', '天津', '津');
INSERT INTO `system_province` VALUES ('3', '河北', '冀');
INSERT INTO `system_province` VALUES ('4', '山西', '晋');
INSERT INTO `system_province` VALUES ('5', '内蒙古自治区', '蒙');
INSERT INTO `system_province` VALUES ('6', '辽宁', '辽');
INSERT INTO `system_province` VALUES ('7', '吉林', '吉');
INSERT INTO `system_province` VALUES ('8', '黑龙江', '黑');
INSERT INTO `system_province` VALUES ('9', '上海', '沪');
INSERT INTO `system_province` VALUES ('10', '江苏', '苏');
INSERT INTO `system_province` VALUES ('11', '浙江', '浙');
INSERT INTO `system_province` VALUES ('12', '安徽', '皖');
INSERT INTO `system_province` VALUES ('13', '福建', '闽');
INSERT INTO `system_province` VALUES ('14', '江西', '赣');
INSERT INTO `system_province` VALUES ('15', '山东', '鲁');
INSERT INTO `system_province` VALUES ('16', '河南', '豫');
INSERT INTO `system_province` VALUES ('17', '湖北', '鄂');
INSERT INTO `system_province` VALUES ('18', '湖南', '湘');
INSERT INTO `system_province` VALUES ('19', '广东', '粤');
INSERT INTO `system_province` VALUES ('20', '广西', '桂');
INSERT INTO `system_province` VALUES ('21', '海南', '琼');
INSERT INTO `system_province` VALUES ('22', '重庆', '渝');
INSERT INTO `system_province` VALUES ('23', '四川', '川');
INSERT INTO `system_province` VALUES ('24', '贵州', '贵');
INSERT INTO `system_province` VALUES ('25', '云南', '滇');
INSERT INTO `system_province` VALUES ('26', '西藏', '藏');
INSERT INTO `system_province` VALUES ('27', '陕西', '陕');
INSERT INTO `system_province` VALUES ('28', '甘肃', '甘');
INSERT INTO `system_province` VALUES ('29', '青海', '青');
INSERT INTO `system_province` VALUES ('30', '宁夏', '宁');
INSERT INTO `system_province` VALUES ('31', '新疆', '新');
INSERT INTO `system_province` VALUES ('32', '香港', '港');
INSERT INTO `system_province` VALUES ('33', '澳门', '澳');
INSERT INTO `system_province` VALUES ('34', '台湾', '台');
INSERT INTO `system_province` VALUES ('35', '其它', '?');

-- ----------------------------
-- Table structure for `system_randomevent`
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
-- Records of system_randomevent
-- ----------------------------
INSERT INTO `system_randomevent` VALUES ('1', '一个警察不怀好心的档着你的路', 'uploads/random/title/1.jpg', '2010-04-21 18:51:59');
INSERT INTO `system_randomevent` VALUES ('2', '一个醉汉高举酒瓶档着你的路。', 'uploads/random/title/2.jpg', '2010-04-21 18:52:19');
INSERT INTO `system_randomevent` VALUES ('3', '一只毒虫拿着针头挡住你的路', 'uploads/random/title/3.jpg', '2010-04-21 18:52:34');
INSERT INTO `system_randomevent` VALUES ('4', '一个老伯步履蹒跚拦下你。', 'uploads/random/title/4.jpg', '2010-04-21 18:52:53');

-- ----------------------------
-- Table structure for `system_randomeventchoice`
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
-- Records of system_randomeventchoice
-- ----------------------------
INSERT INTO `system_randomeventchoice` VALUES ('6', '1', '非洲', 'uploads/random/choice/africa.gif');
INSERT INTO `system_randomeventchoice` VALUES ('7', '1', '欧洲', 'uploads/random/choice/europe.gif');
INSERT INTO `system_randomeventchoice` VALUES ('8', '1', '大洋洲', 'uploads/random/choice/oceania.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('9', '1', '南美洲', 'uploads/random/choice/south_america.gif');
INSERT INTO `system_randomeventchoice` VALUES ('10', '1', '亚洲', 'uploads/random/choice/asia.gif');
INSERT INTO `system_randomeventchoice` VALUES ('11', '2', '3:10', 'uploads/random/choice/03-10.gif');
INSERT INTO `system_randomeventchoice` VALUES ('12', '2', '5:00 ', 'uploads/random/choice/05-00.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('13', '2', '7:53', 'uploads/random/choice/07-53.gif');
INSERT INTO `system_randomeventchoice` VALUES ('14', '2', '10:30', 'uploads/random/choice/10-30.png');
INSERT INTO `system_randomeventchoice` VALUES ('15', '2', '11:55', 'uploads/random/choice/11-55.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('16', '3', '猴子', 'uploads/random/choice/monkey.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('17', '3', '鸟', 'uploads/random/choice/bird.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('18', '3', '狗', 'uploads/random/choice/dog.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('19', '3', '老虎', 'uploads/random/choice/tiger.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('20', '3', '耗子', 'uploads/random/choice/hamster.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('21', '4', '沙漠', 'uploads/random/choice/desert.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('22', '4', '海洋', 'uploads/random/choice/ocean.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('23', '4', '森林', 'uploads/random/choice/forest.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('24', '4', '城市', 'uploads/random/choice/city.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('25', '4', '北极', 'uploads/random/choice/arctic.gif');
INSERT INTO `system_randomeventchoice` VALUES ('26', '5', '渡船', 'uploads/random/choice/ferry.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('27', '5', '火车', 'uploads/random/choice/train.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('28', '5', '公共汽车', 'uploads/random/choice/bus.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('29', '5', '飞机', 'uploads/random/choice/airplane.png');
INSERT INTO `system_randomeventchoice` VALUES ('30', '6', '尺', 'uploads/random/choice/ruler.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('31', '6', '笔', 'uploads/random/choice/pen.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('32', '6', '计算器', 'uploads/random/choice/calculator.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('33', '6', '橡皮', 'uploads/random/choice/eraser.jpg');
INSERT INTO `system_randomeventchoice` VALUES ('34', '6', '笔记本计算机', 'uploads/random/choice/notebook.jpg');

-- ----------------------------
-- Table structure for `system_randomeventquestion`
-- ----------------------------
DROP TABLE IF EXISTS `system_randomeventquestion`;
CREATE TABLE `system_randomeventquestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_randomeventquestion
-- ----------------------------
INSERT INTO `system_randomeventquestion` VALUES ('1', '我想我认得样子，你说呢？', '2010-04-21 18:54:07');
INSERT INTO `system_randomeventquestion` VALUES ('2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '2010-04-21 18:54:17');
INSERT INTO `system_randomeventquestion` VALUES ('3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '2010-04-21 18:54:28');
INSERT INTO `system_randomeventquestion` VALUES ('4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '2010-04-21 18:54:37');
INSERT INTO `system_randomeventquestion` VALUES ('5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '2010-04-21 18:54:46');
INSERT INTO `system_randomeventquestion` VALUES ('6', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '2010-04-21 18:54:57');

-- ----------------------------
-- Table structure for `system_robbery`
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
-- Records of system_robbery
-- ----------------------------
INSERT INTO `system_robbery` VALUES ('1', '偷窃', '5', '3', 'single', '0.2000', '0.5000', '0.2000', '0.5000', '0.2000', '0.5000', '0.2000', '0.5000', '1', '10', '1,10', '0.2,0.5', '0', '2010-04-28 19:06:21');
INSERT INTO `system_robbery` VALUES ('2', '抢老妇人', '10', '10', 'single', '0.5000', '1.5000', '0.5000', '1.5000', '0.5000', '1.5000', '0.5000', '1.5000', '10', '65', '10,65', '0.5,1.5', '0', '2010-04-28 19:09:36');
INSERT INTO `system_robbery` VALUES ('3', '偷汽車', '10', '15', 'single', '0.5000', '2.0000', '0.5000', '2.0000', '0.5000', '2.0000', '0.5000', '2.0000', '40', '110', '40,110', '0.5,2', '0', '2010-04-28 19:10:33');
INSERT INTO `system_robbery` VALUES ('4', '抢出租车', '10', '25', 'single', '1.0000', '4.0000', '1.0000', '4.0000', '1.0000', '4.0000', '1.0000', '4.0000', '90', '200', '90,200', '1,4', '0', '2010-04-28 19:11:17');
INSERT INTO `system_robbery` VALUES ('5', '提款机', '10', '40', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '50', '250', '50,250', '1.5,5.5', '0', '2010-04-28 19:12:11');
INSERT INTO `system_robbery` VALUES ('6', '民宅', '12', '45', 'single', '1.2000', '5.0000', '1.2000', '5.0000', '1.2000', '5.0000', '1.2000', '5.0000', '140', '360', '140,360', '1.2,5', '0', '2010-04-28 19:13:02');
INSERT INTO `system_robbery` VALUES ('7', '加油站', '14', '55', 'single', '1.4000', '4.5000', '1.4000', '4.5000', '1.4000', '4.5000', '1.4000', '4.5000', '150', '550', '150,550', '1.4,4.5', '0', '2010-04-28 19:13:45');
INSERT INTO `system_robbery` VALUES ('8', '戏院', '15', '65', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '300', '700', '300,700', '1.5,5.5', '0', '2010-04-28 19:14:25');
INSERT INTO `system_robbery` VALUES ('9', '杂货店', '16', '70', 'single', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '1.5000', '5.5000', '300', '900', '300,900', '1.5,5.5', '0', '2010-04-28 19:15:15');
INSERT INTO `system_robbery` VALUES ('10', '24小时便利商店', '18', '100', 'single', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '400', '1400', '400,1400', '1.5,6', '0', '2010-04-28 19:15:53');
INSERT INTO `system_robbery` VALUES ('11', '绑架', '20', '170', 'single', '0.4000', '1.5000', '0.4000', '1.5000', '0.4000', '1.5000', '0.4000', '1.5000', '1000', '2500', '1000,2500', '0.4,1.5', '0', '2010-04-28 19:16:41');
INSERT INTO `system_robbery` VALUES ('12', '珠宝店', '25', '250', 'single', '0.7000', '5.0000', '0.7000', '5.0000', '0.7000', '5.0000', '0.7000', '5.0000', '1200', '4500', '1200,4500', '0.7,5', '0', '2010-04-28 19:17:24');
INSERT INTO `system_robbery` VALUES ('13', '保险箱', '27', '300', 'single', '0.8000', '3.2000', '0.8000', '3.2000', '0.8000', '3.2000', '0.8000', '3.2000', '2800', '5800', '2800,5800', '0.8,3.2', '0', '2010-04-28 19:18:07');
INSERT INTO `system_robbery` VALUES ('14', '小银行', '30', '370', 'single', '1.0000', '2.1000', '1.0000', '2.1000', '1.0000', '2.1000', '1.0000', '2.1000', '2400', '6500', '2400,6500', '1,2.1', '0', '2010-04-28 19:18:51');
INSERT INTO `system_robbery` VALUES ('15', '帮派小头目', '35', '480', 'single', '1.5000', '3.0000', '1.5000', '3.0000', '1.5000', '3.0000', '1.5000', '3.0000', '5000', '12000', '5000,12000', '1.5,3', '0', '2010-04-28 19:19:29');
INSERT INTO `system_robbery` VALUES ('16', '汽车沙龙', '40', '570', 'single', '1.5000', '5.7000', '1.5000', '5.7000', '1.5000', '5.7000', '1.5000', '5.7000', '5000', '17000', '5000,17000', '1.5,5.7', '0', '2010-04-28 19:20:12');
INSERT INTO `system_robbery` VALUES ('17', 'PayPal', '45', '640', 'single', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '1.5000', '6.0000', '5500', '15000', '5500,15000', '1.5,6', '0', '2010-04-28 19:21:00');
INSERT INTO `system_robbery` VALUES ('18', '地痞', '50', '770', 'single', '4.0000', '6.0000', '4.0000', '6.0000', '4.0000', '6.0000', '4.0000', '6.0000', '8000', '10000', '8000,10000', '4,6', '0', '2010-04-28 19:21:41');
INSERT INTO `system_robbery` VALUES ('19', '小药头', '60', '880', 'single', '5.0000', '6.0000', '5.0000', '6.0000', '5.0000', '6.0000', '5.0000', '6.0000', '10000', '15000', '10000,15000', '5,6', '0', '2010-04-28 19:22:21');
INSERT INTO `system_robbery` VALUES ('20', '赌场', '65', '980', 'single', '5.0000', '7.0000', '5.0000', '7.0000', '5.0000', '7.0000', '5.0000', '7.0000', '15000', '30000', '15000,30000', '5,7', '0', '2010-04-28 19:22:50');
INSERT INTO `system_robbery` VALUES ('21', '狂欢会', '70', '1150', 'single', '6.0000', '7.0000', '6.0000', '7.0000', '6.0000', '7.0000', '6.0000', '7.0000', '30000', '50000', '30000,50000', '6,7', '0', '2010-04-28 19:23:18');
INSERT INTO `system_robbery` VALUES ('22', '超级市场', '75', '1430', 'single', '1.2000', '6.0000', '1.2000', '6.0000', '1.2000', '6.0000', '1.2000', '6.0000', '35000', '80000', '35000,80000', '1.2,6', '0', '2010-04-28 19:23:56');
INSERT INTO `system_robbery` VALUES ('23', '博物馆', '80', '2700', 'single', '8.0000', '15.0000', '8.0000', '15.0000', '8.0000', '15.0000', '8.0000', '15.0000', '80000', '150000', '80000,150000', '8,15', '0', '2010-04-28 19:24:41');
INSERT INTO `system_robbery` VALUES ('24', '俄国药王', '80', '3200', 'single', '10.0000', '20.0000', '10.0000', '20.0000', '10.0000', '20.0000', '10.0000', '20.0000', '150000', '300000', '150000,300000', '10,20', '0', '2010-04-28 19:25:18');
INSERT INTO `system_robbery` VALUES ('25', '外币', '30', '70', 'gang', '0.5000', '3.5000', '0.5000', '3.5000', '0.5000', '3.5000', '0.5000', '3.5000', '4000', '14000', '4000,14000', '0.5,3.5', '2', '2010-04-28 19:26:13');
INSERT INTO `system_robbery` VALUES ('26', '银行', '30', '160', 'gang', '5.0000', '10.0000', '5.0000', '10.0000', '5.0000', '10.0000', '5.0000', '10.0000', '8000', '11000', '8000,11000', '5,10', '4', '2010-04-28 19:27:04');
INSERT INTO `system_robbery` VALUES ('27', '运钞车', '30', '300', 'gang', '6.0000', '12.0000', '6.0000', '12.0000', '6.0000', '12.0000', '6.0000', '12.0000', '12000', '15000', '12000,15000', '6,12', '3', '2010-04-28 19:27:44');
INSERT INTO `system_robbery` VALUES ('28', '中央储备银行', '30', '900', 'gang', '25.0000', '40.0000', '25.0000', '40.0000', '25.0000', '40.0000', '25.0000', '40.0000', '100000', '150000', '100000,150000', '25,40', '6', '2010-04-28 19:28:43');
INSERT INTO `system_robbery` VALUES ('29', '杜月笙', '30', '2000', 'gang', '40.0000', '65.0000', '40.0000', '65.0000', '40.0000', '65.0000', '40.0000', '65.0000', '30000', '50000', '30000,50000', '40,65', '7', '2010-04-28 19:29:31');
INSERT INTO `system_robbery` VALUES ('30', '操纵股市', '30', '2500', 'gang', '50.0000', '80.0000', '50.0000', '80.0000', '50.0000', '80.0000', '50.0000', '80.0000', '50000', '80000', '50000,80000', '50,80', '9', '2010-04-28 19:30:11');
INSERT INTO `system_robbery` VALUES ('31', '黄金荣', '80', '4500', 'gang', '80.0000', '150.0000', '80.0000', '150.0000', '80.0000', '150.0000', '80.0000', '150.0000', '80000', '150000', '80000,150000', '80,150', '10', '2010-04-28 19:30:54');
INSERT INTO `system_robbery` VALUES ('32', '他妈的烂地方', '80', '8000', 'gang', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '150000', '300000', '150000,300000', '100,200', '14', '2010-04-28 19:31:37');
INSERT INTO `system_robbery` VALUES ('33', '黄埔军校', '80', '15000', 'gang', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '100.0000', '200.0000', '150000', '300000', '150000,300000', '100,200', '17', '2010-04-28 19:32:20');

-- ----------------------------
-- Table structure for `system_sabotage`
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
-- Records of system_sabotage
-- ----------------------------
INSERT INTO `system_sabotage` VALUES ('1', '炸了他的工厂', '15', '1000', '500', '2010-05-04 10:28:37');
INSERT INTO `system_sabotage` VALUES ('2', '抢走他的小姐', '10', '200', '100', '2010-05-04 10:28:58');
INSERT INTO `system_sabotage` VALUES ('3', '砸了他的场子', '20', '2500', '1250', '2010-05-04 10:29:24');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_weapon
-- ----------------------------
INSERT INTO `system_weapon` VALUES ('1', '球棒', 'uploads/weapon/monkeybat.jpg', '120', '8', '10', '1', '1', 'melee', '12', '2010-04-21 11:31:34');
INSERT INTO `system_weapon` VALUES ('2', '匕首', 'uploads/weapon/knife.jpg', '300', '8', '20', '1', '10', 'melee', '30', '2010-04-21 11:35:34');
INSERT INTO `system_weapon` VALUES ('3', '剑', 'uploads/weapon/sword.jpg', '600', '15', '25', '1', '30', 'melee', '60', '2010-04-21 11:36:29');
INSERT INTO `system_weapon` VALUES ('4', '链锯', 'uploads/weapon/chainsaw.jpg', '660', '12', '30', '10', '50', 'melee', '66', '2010-04-21 11:37:20');
INSERT INTO `system_weapon` VALUES ('5', '格洛克', 'uploads/weapon/glock.jpg', '1350', '20', '40', '20', '40', 'handgun', '135', '2010-04-21 11:38:16');
INSERT INTO `system_weapon` VALUES ('6', '散弹枪', 'uploads/weapon/shotgun.jpg', '3100', '26', '65', '100', '50', 'rifle', '310', '2010-04-21 11:39:13');
INSERT INTO `system_weapon` VALUES ('7', 'MP5冲锋枪', 'uploads/weapon/mp5.jpg', '4700', '42', '70', '150', '100', 'handgun', '470', '2010-04-21 11:40:44');
INSERT INTO `system_weapon` VALUES ('8', 'AK 47', 'uploads/weapon/ak47.jpg', '5400', '45', '75', '200', '130', 'rifle', '540', '2010-04-21 11:41:45');
INSERT INTO `system_weapon` VALUES ('9', '乌兹冲锋枪', 'uploads/weapon/uzi.jpg', '6300', '30', '100', '250', '200', 'handgun', '630', '2010-04-21 11:42:30');
INSERT INTO `system_weapon` VALUES ('10', 'M4A1', 'uploads/weapon/coltm4a1.jpg', '6800', '45', '90', '300', '220', 'rifle', '680', '2010-04-21 11:43:30');
INSERT INTO `system_weapon` VALUES ('11', '沙漠之鹰', 'uploads/weapon/deagle.jpg', '8800', '68', '85', '400', '300', 'handgun', '880', '2010-04-21 11:44:48');
INSERT INTO `system_weapon` VALUES ('12', '重型狙击枪', 'uploads/weapon/sniper.jpg', '18000', '110', '110', '700', '500', 'rifle', '180', '2010-04-21 11:45:50');
INSERT INTO `system_weapon` VALUES ('13', '激光枪', 'uploads/weapon/raygun.jpg', '85000', '180', '300', '1000', '800', 'handgun', '850', '2010-04-21 11:47:14');
INSERT INTO `system_weapon` VALUES ('14', '重机枪', 'uploads/weapon/machine_gun.jpg', '210000', '250', '500', '1500', '800', 'heavy', '210', '2010-04-21 11:48:07');
INSERT INTO `system_weapon` VALUES ('15', '火箭筒', 'uploads/weapon/bazooka.jpg', '695000', '560', '800', '2000', '1500', 'heavy', '695', '2010-04-21 11:49:27');
INSERT INTO `system_weapon` VALUES ('16', '盖利步枪', 'uploads/weapon/gail.jpg', '1560000', '840', '1200', '2800', '1500', 'rifle', '1560', '2010-04-21 11:50:31');
INSERT INTO `system_weapon` VALUES ('17', '多管连发手枪', 'uploads/weapon/bfg.jpg', '2400000', '720', '1800', '3000', '1750', 'handgun', '2400', '2010-04-21 11:51:24');
INSERT INTO `system_weapon` VALUES ('18', '地狱火神炮', 'uploads/weapon/extreme.jpg', '4400000', '1400', '2000', '4500', '2200', 'heavy', '4400', '2010-04-21 11:52:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_business
-- ----------------------------

-- ----------------------------
-- Table structure for `user_business_user_drug`
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
-- Records of user_business_user_drug
-- ----------------------------

-- ----------------------------
-- Table structure for `user_data`
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
-- Records of user_data
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_hooker
-- ----------------------------

-- ----------------------------
-- Table structure for `user_profile`
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
-- Records of user_profile
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
