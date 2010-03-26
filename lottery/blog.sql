/*
Navicat MySQL Data Transfer

Source Server         : localhost:3306
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-03-26 18:07:26
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `accounts_account`
-- ----------------------------
DROP TABLE IF EXISTS `accounts_account`;
CREATE TABLE `accounts_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `description` varchar(100) NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `lottery_id` int(11) DEFAULT NULL,
  `method_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `mode_id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `pre_balance` decimal(14,4) NOT NULL,
  `pre_hold` decimal(14,4) NOT NULL,
  `pre_available` decimal(14,4) NOT NULL,
  `suf_balance` decimal(14,4) NOT NULL,
  `suf_hold` decimal(14,4) NOT NULL,
  `suf_available` decimal(14,4) NOT NULL,
  `client_ip` char(15) NOT NULL,
  `proxy_ip` char(15) NOT NULL,
  `db_time` datetime NOT NULL,
  `action_time` datetime NOT NULL,
  `source_channel_id` int(11) DEFAULT NULL,
  `dest_channel_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `status` smallint(6) NOT NULL,
  `hashvar` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_account_lottery_id` (`lottery_id`),
  KEY `accounts_account_method_id` (`method_id`),
  KEY `accounts_account_order_id` (`order_id`),
  KEY `accounts_account_task_id` (`task_id`),
  KEY `accounts_account_project_id` (`project_id`),
  KEY `accounts_account_mode_id` (`mode_id`),
  KEY `accounts_account_from_user_id` (`from_user_id`),
  KEY `accounts_account_to_user_id` (`to_user_id`),
  KEY `accounts_account_type_id` (`type_id`),
  KEY `accounts_account_client_ip` (`client_ip`),
  KEY `accounts_account_source_channel_id` (`source_channel_id`),
  KEY `accounts_account_dest_channel_id` (`dest_channel_id`),
  KEY `accounts_account_operator_id` (`operator_id`),
  KEY `accounts_account_hashvar` (`hashvar`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of accounts_account
-- ----------------------------
INSERT INTO `accounts_account` VALUES ('1', '加入游戏', '加入游戏', '250.0000', '1', '2', '1', null, '2', '1', '1', null, '3', '35898.9200', '11918.0000', '23980.9200', '35898.9200', '12168.0000', '23730.9200', 'localhost:8000', 'localhost:8000', '2010-03-26 16:31:09', '2010-03-26 16:31:09', '3', null, null, '0', '');
INSERT INTO `accounts_account` VALUES ('3', '加入游戏', '加入游戏', '250.0000', '1', '2', '1', null, '2', '1', '1', null, '3', '35898.9200', '11918.0000', '23980.9200', '35898.9200', '12168.0000', '23730.9200', '127.0.0.1', '', '2010-03-26 17:45:42', '2010-03-26 17:45:42', '3', null, null, '0', '');

-- ----------------------------
-- Table structure for `accounts_accounttype`
-- ----------------------------
DROP TABLE IF EXISTS `accounts_accounttype`;
CREATE TABLE `accounts_accounttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(6) NOT NULL,
  `description` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_foruser` tinyint(1) NOT NULL,
  `is_plus` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_accounttype_parent_id` (`parent_id`),
  CONSTRAINT `parent_id_refs_id_151bae0b` FOREIGN KEY (`parent_id`) REFERENCES `accounts_accounttype` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of accounts_accounttype
-- ----------------------------
INSERT INTO `accounts_accounttype` VALUES ('1', 'ZRPD', '转入频道', null, '1', '1', '2010-03-25 16:29:53');
INSERT INTO `accounts_accounttype` VALUES ('2', 'PDZC', '频道转出', null, '1', '0', '2010-03-25 16:30:06');
INSERT INTO `accounts_accounttype` VALUES ('3', 'JRYX', '加入游戏', null, '1', '0', '2010-03-25 16:30:17');
INSERT INTO `accounts_accounttype` VALUES ('4', 'XSFD', '销售返点', null, '1', '1', '2010-03-25 16:30:29');
INSERT INTO `accounts_accounttype` VALUES ('5', 'JJPS', '奖金派送', null, '1', '1', '2010-03-25 16:30:42');
INSERT INTO `accounts_accounttype` VALUES ('6', 'ZHKK', '追号扣款', null, '1', '0', '2010-03-25 16:30:52');
INSERT INTO `accounts_accounttype` VALUES ('7', 'DQZHFK', '当期追号返款', null, '1', '1', '2010-03-25 16:31:04');
INSERT INTO `accounts_accounttype` VALUES ('8', 'YXKK', '游戏扣款', null, '0', '0', '2010-03-25 16:31:17');
INSERT INTO `accounts_accounttype` VALUES ('9', 'CDFK', '撤单返款', null, '1', '1', '2010-03-25 16:31:31');
INSERT INTO `accounts_accounttype` VALUES ('10', 'CDSXF', '撤单手续费', null, '1', '0', '2010-03-25 16:31:44');
INSERT INTO `accounts_accounttype` VALUES ('11', 'CXFD', '撤销返点', null, '1', '0', '2010-03-25 16:31:56');
INSERT INTO `accounts_accounttype` VALUES ('12', 'CXPJ', '撤销派奖', null, '1', '0', '2010-03-25 16:32:06');
INSERT INTO `accounts_accounttype` VALUES ('13', 'PDXEZC', '频道小额转出', null, '1', '0', '2010-03-25 16:32:19');
INSERT INTO `accounts_accounttype` VALUES ('14', 'TSJEZL', '特殊金额整理', null, '1', '1', '2010-03-25 16:32:29');
INSERT INTO `accounts_accounttype` VALUES ('15', 'TSJEQL', '特殊金额清理', null, '1', '0', '2010-03-25 16:32:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;

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
INSERT INTO `auth_permission` VALUES ('28', 'Can add log entry', '10', 'add_logentry');
INSERT INTO `auth_permission` VALUES ('29', 'Can change log entry', '10', 'change_logentry');
INSERT INTO `auth_permission` VALUES ('30', 'Can delete log entry', '10', 'delete_logentry');
INSERT INTO `auth_permission` VALUES ('31', 'Can add channel', '11', 'add_channel');
INSERT INTO `auth_permission` VALUES ('32', 'Can change channel', '11', 'change_channel');
INSERT INTO `auth_permission` VALUES ('33', 'Can delete channel', '11', 'delete_channel');
INSERT INTO `auth_permission` VALUES ('34', 'Can add user channel set', '12', 'add_userchannelset');
INSERT INTO `auth_permission` VALUES ('35', 'Can change user channel set', '12', 'change_userchannelset');
INSERT INTO `auth_permission` VALUES ('36', 'Can delete user channel set', '12', 'delete_userchannelset');
INSERT INTO `auth_permission` VALUES ('37', 'Can add configure', '13', 'add_configure');
INSERT INTO `auth_permission` VALUES ('38', 'Can change configure', '13', 'change_configure');
INSERT INTO `auth_permission` VALUES ('39', 'Can delete configure', '13', 'delete_configure');
INSERT INTO `auth_permission` VALUES ('40', 'Can add lottery type', '14', 'add_lotterytype');
INSERT INTO `auth_permission` VALUES ('41', 'Can change lottery type', '14', 'change_lotterytype');
INSERT INTO `auth_permission` VALUES ('42', 'Can delete lottery type', '14', 'delete_lotterytype');
INSERT INTO `auth_permission` VALUES ('43', 'Can add lottery', '15', 'add_lottery');
INSERT INTO `auth_permission` VALUES ('44', 'Can change lottery', '15', 'change_lottery');
INSERT INTO `auth_permission` VALUES ('45', 'Can delete lottery', '15', 'delete_lottery');
INSERT INTO `auth_permission` VALUES ('46', 'Can add prize group', '16', 'add_prizegroup');
INSERT INTO `auth_permission` VALUES ('47', 'Can change prize group', '16', 'change_prizegroup');
INSERT INTO `auth_permission` VALUES ('48', 'Can delete prize group', '16', 'delete_prizegroup');
INSERT INTO `auth_permission` VALUES ('49', 'Can add user prize group', '17', 'add_userprizegroup');
INSERT INTO `auth_permission` VALUES ('50', 'Can change user prize group', '17', 'change_userprizegroup');
INSERT INTO `auth_permission` VALUES ('51', 'Can delete user prize group', '17', 'delete_userprizegroup');
INSERT INTO `auth_permission` VALUES ('52', 'Can add mode', '18', 'add_mode');
INSERT INTO `auth_permission` VALUES ('53', 'Can change mode', '18', 'change_mode');
INSERT INTO `auth_permission` VALUES ('54', 'Can delete mode', '18', 'delete_mode');
INSERT INTO `auth_permission` VALUES ('55', 'Can add method', '19', 'add_method');
INSERT INTO `auth_permission` VALUES ('56', 'Can change method', '19', 'change_method');
INSERT INTO `auth_permission` VALUES ('57', 'Can delete method', '19', 'delete_method');
INSERT INTO `auth_permission` VALUES ('58', 'Can add user method set', '20', 'add_usermethodset');
INSERT INTO `auth_permission` VALUES ('59', 'Can change user method set', '20', 'change_usermethodset');
INSERT INTO `auth_permission` VALUES ('60', 'Can delete user method set', '20', 'delete_usermethodset');
INSERT INTO `auth_permission` VALUES ('61', 'Can add issue', '21', 'add_issue');
INSERT INTO `auth_permission` VALUES ('62', 'Can change issue', '21', 'change_issue');
INSERT INTO `auth_permission` VALUES ('63', 'Can delete issue', '21', 'delete_issue');
INSERT INTO `auth_permission` VALUES ('64', 'Can add issue history', '22', 'add_issuehistory');
INSERT INTO `auth_permission` VALUES ('65', 'Can change issue history', '22', 'change_issuehistory');
INSERT INTO `auth_permission` VALUES ('66', 'Can delete issue history', '22', 'delete_issuehistory');
INSERT INTO `auth_permission` VALUES ('67', 'Can add issue error', '23', 'add_issueerror');
INSERT INTO `auth_permission` VALUES ('68', 'Can change issue error', '23', 'change_issueerror');
INSERT INTO `auth_permission` VALUES ('69', 'Can delete issue error', '23', 'delete_issueerror');
INSERT INTO `auth_permission` VALUES ('70', 'Can add prize level', '24', 'add_prizelevel');
INSERT INTO `auth_permission` VALUES ('71', 'Can change prize level', '24', 'change_prizelevel');
INSERT INTO `auth_permission` VALUES ('72', 'Can delete prize level', '24', 'delete_prizelevel');
INSERT INTO `auth_permission` VALUES ('73', 'Can add user prize level', '25', 'add_userprizelevel');
INSERT INTO `auth_permission` VALUES ('74', 'Can change user prize level', '25', 'change_userprizelevel');
INSERT INTO `auth_permission` VALUES ('75', 'Can delete user prize level', '25', 'delete_userprizelevel');
INSERT INTO `auth_permission` VALUES ('76', 'Can add help', '26', 'add_help');
INSERT INTO `auth_permission` VALUES ('77', 'Can change help', '26', 'change_help');
INSERT INTO `auth_permission` VALUES ('78', 'Can delete help', '26', 'delete_help');
INSERT INTO `auth_permission` VALUES ('79', 'Can add notice', '27', 'add_notice');
INSERT INTO `auth_permission` VALUES ('80', 'Can change notice', '27', 'change_notice');
INSERT INTO `auth_permission` VALUES ('81', 'Can delete notice', '27', 'delete_notice');
INSERT INTO `auth_permission` VALUES ('82', 'Can add account type', '28', 'add_accounttype');
INSERT INTO `auth_permission` VALUES ('83', 'Can change account type', '28', 'change_accounttype');
INSERT INTO `auth_permission` VALUES ('84', 'Can delete account type', '28', 'delete_accounttype');
INSERT INTO `auth_permission` VALUES ('85', 'Can add order', '29', 'add_order');
INSERT INTO `auth_permission` VALUES ('86', 'Can change order', '29', 'change_order');
INSERT INTO `auth_permission` VALUES ('87', 'Can delete order', '29', 'delete_order');
INSERT INTO `auth_permission` VALUES ('88', 'Can add task', '30', 'add_task');
INSERT INTO `auth_permission` VALUES ('89', 'Can change task', '30', 'change_task');
INSERT INTO `auth_permission` VALUES ('90', 'Can delete task', '30', 'delete_task');
INSERT INTO `auth_permission` VALUES ('91', 'Can add project', '31', 'add_project');
INSERT INTO `auth_permission` VALUES ('92', 'Can change project', '31', 'change_project');
INSERT INTO `auth_permission` VALUES ('93', 'Can delete project', '31', 'delete_project');
INSERT INTO `auth_permission` VALUES ('94', 'Can add task detail', '32', 'add_taskdetail');
INSERT INTO `auth_permission` VALUES ('95', 'Can change task detail', '32', 'change_taskdetail');
INSERT INTO `auth_permission` VALUES ('96', 'Can delete task detail', '32', 'delete_taskdetail');
INSERT INTO `auth_permission` VALUES ('97', 'Can add expand code', '33', 'add_expandcode');
INSERT INTO `auth_permission` VALUES ('98', 'Can change expand code', '33', 'change_expandcode');
INSERT INTO `auth_permission` VALUES ('99', 'Can delete expand code', '33', 'delete_expandcode');
INSERT INTO `auth_permission` VALUES ('100', 'Can add account', '34', 'add_account');
INSERT INTO `auth_permission` VALUES ('101', 'Can change account', '34', 'change_account');
INSERT INTO `auth_permission` VALUES ('102', 'Can delete account', '34', 'delete_account');

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
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'kirinse@gmail.com', 'sha1$8ae46$39e846d47d4b08e3ca664356a74d87a3bca14a3b', '1', '1', '1', '2010-03-25 16:57:28', '2010-03-25 16:56:01');

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
INSERT INTO `channels_channel` VALUES ('1', 'Passport', '/', '0', '2010-03-25 16:58:24', '2010-03-25 16:58:24');
INSERT INTO `channels_channel` VALUES ('2', 'Low', 'low', '0', '2010-03-25 16:58:30', '2010-03-25 16:58:30');
INSERT INTO `channels_channel` VALUES ('3', 'High', 'high', '0', '2010-03-25 16:58:35', '2010-03-26 14:29:01');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_configure
-- ----------------------------
INSERT INTO `channels_configure` VALUES ('1', null, 'operation', '', '', '', '', '3', '运营参数', '运营参数', '0');

-- ----------------------------
-- Table structure for `channels_userchannelset`
-- ----------------------------
DROP TABLE IF EXISTS `channels_userchannelset`;
CREATE TABLE `channels_userchannelset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `channels_userchannelset_user_id` (`user_id`),
  KEY `channels_userchannelset_channel_id` (`channel_id`),
  CONSTRAINT `channel_id_refs_id_5bcd73c9` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `user_id_refs_id_7f053b3e` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of channels_userchannelset
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of django_admin_log
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2010-03-25 16:58:24', '1', '11', '1', 'Passport', '1', '');
INSERT INTO `django_admin_log` VALUES ('2', '2010-03-25 16:58:30', '1', '11', '2', 'Low', '1', '');
INSERT INTO `django_admin_log` VALUES ('3', '2010-03-25 16:58:35', '1', '11', '3', 'High', '1', '');
INSERT INTO `django_admin_log` VALUES ('4', '2010-03-25 17:01:21', '1', '29', '1', '2010-03-25 17:01:21.093000', '1', '');
INSERT INTO `django_admin_log` VALUES ('5', '2010-03-25 17:03:21', '1', '27', '1', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('6', '2010-03-25 17:04:57', '1', '19', '1', '前三直选', '1', '');
INSERT INTO `django_admin_log` VALUES ('7', '2010-03-25 17:07:45', '1', '19', '2', '前三直选', '1', '');
INSERT INTO `django_admin_log` VALUES ('8', '2010-03-25 17:16:55', '1', '21', '1', '100315001', '1', '');
INSERT INTO `django_admin_log` VALUES ('9', '2010-03-25 17:17:47', '1', '21', '1', '100315001', '2', '已修改 code 。');
INSERT INTO `django_admin_log` VALUES ('10', '2010-03-25 17:21:05', '1', '31', '2', 'digital 13579|13579|13579', '1', '');
INSERT INTO `django_admin_log` VALUES ('11', '2010-03-25 17:41:40', '1', '31', '2', 'digital 01 02|01 02|01 02 03', '2', '已修改 code 。 已添加 expand code \"1 Level, Prize is 1500\".');
INSERT INTO `django_admin_log` VALUES ('12', '2010-03-25 17:46:27', '1', '30', '1', '前三直选_直选 追号2期', '1', '');
INSERT INTO `django_admin_log` VALUES ('13', '2010-03-25 17:53:00', '1', '30', '1', '前三直选_直选 追号2期', '2', '已修改 mode 。');
INSERT INTO `django_admin_log` VALUES ('14', '2010-03-25 17:57:45', '1', '30', '1', '前三直选_直选 追号2期', '2', '已添加 task detail \"前三直选_直选 追号2期 100315001\".');
INSERT INTO `django_admin_log` VALUES ('15', '2010-03-26 14:29:01', '1', '11', '3', 'High', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('16', '2010-03-26 15:33:09', '1', '13', '1', 'operation', '1', '');
INSERT INTO `django_admin_log` VALUES ('17', '2010-03-26 15:34:47', '1', '13', '1', '运营参数', '2', '已修改 config_key 和 title 。');
INSERT INTO `django_admin_log` VALUES ('18', '2010-03-26 16:31:09', '1', '34', '1', '加入游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('19', '2010-03-26 16:36:44', '1', '34', '2', '加入游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('20', '2010-03-26 17:28:13', '1', '34', '2', '加入游戏', '3', '');
INSERT INTO `django_admin_log` VALUES ('21', '2010-03-26 17:37:13', '1', '26', '1', '[转]上海世博至少落后广州亚运一百年！', '1', '');
INSERT INTO `django_admin_log` VALUES ('22', '2010-03-26 17:45:42', '1', '34', '3', '加入游戏', '1', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_content_type` VALUES ('10', 'log entry', 'admin', 'logentry');
INSERT INTO `django_content_type` VALUES ('11', 'channel', 'channels', 'channel');
INSERT INTO `django_content_type` VALUES ('12', 'user channel set', 'channels', 'userchannelset');
INSERT INTO `django_content_type` VALUES ('13', 'configure', 'channels', 'configure');
INSERT INTO `django_content_type` VALUES ('14', 'lottery type', 'lotteries', 'lotterytype');
INSERT INTO `django_content_type` VALUES ('15', 'lottery', 'lotteries', 'lottery');
INSERT INTO `django_content_type` VALUES ('16', 'prize group', 'lotteries', 'prizegroup');
INSERT INTO `django_content_type` VALUES ('17', 'user prize group', 'lotteries', 'userprizegroup');
INSERT INTO `django_content_type` VALUES ('18', 'mode', 'lotteries', 'mode');
INSERT INTO `django_content_type` VALUES ('19', 'method', 'lotteries', 'method');
INSERT INTO `django_content_type` VALUES ('20', 'user method set', 'lotteries', 'usermethodset');
INSERT INTO `django_content_type` VALUES ('21', 'issue', 'lotteries', 'issue');
INSERT INTO `django_content_type` VALUES ('22', 'issue history', 'lotteries', 'issuehistory');
INSERT INTO `django_content_type` VALUES ('23', 'issue error', 'lotteries', 'issueerror');
INSERT INTO `django_content_type` VALUES ('24', 'prize level', 'lotteries', 'prizelevel');
INSERT INTO `django_content_type` VALUES ('25', 'user prize level', 'lotteries', 'userprizelevel');
INSERT INTO `django_content_type` VALUES ('26', 'help', 'helps', 'help');
INSERT INTO `django_content_type` VALUES ('27', 'notice', 'notices', 'notice');
INSERT INTO `django_content_type` VALUES ('28', 'account type', 'accounts', 'accounttype');
INSERT INTO `django_content_type` VALUES ('29', 'order', 'records', 'order');
INSERT INTO `django_content_type` VALUES ('30', 'task', 'records', 'task');
INSERT INTO `django_content_type` VALUES ('31', 'project', 'records', 'project');
INSERT INTO `django_content_type` VALUES ('32', 'task detail', 'records', 'taskdetail');
INSERT INTO `django_content_type` VALUES ('33', 'expand code', 'records', 'expandcode');
INSERT INTO `django_content_type` VALUES ('34', 'account', 'accounts', 'account');

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
INSERT INTO `django_session` VALUES ('012878a800595abfbdf08f8cca7a128b', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS4zZDMyNGVhYWFkYTkwYWVhODNk\nZTI4NGY4MzIwNzgyZQ==\n', '2010-04-08 16:57:29');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of helps_help
-- ----------------------------
INSERT INTO `helps_help` VALUES ('1', '3', 'bank', '[转]上海世博至少落后广州亚运一百年！', '[转]上海世博至少落后广州亚运一百年！', '1', '2010-03-26 17:37:13', '0', '0');

-- ----------------------------
-- Table structure for `lotteries_issue`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_issue`;
CREATE TABLE `lotteries_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `lottery_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `sale_start` datetime NOT NULL,
  `sale_end` datetime NOT NULL,
  `cancel_deadline` datetime NOT NULL,
  `official_time` datetime NOT NULL,
  `write_time` datetime DEFAULT NULL,
  `write_user_id` int(11) DEFAULT NULL,
  `verify_time` datetime DEFAULT NULL,
  `verify_user_id` int(11) DEFAULT NULL,
  `status_code` smallint(6) NOT NULL DEFAULT '0',
  `status_deduct` smallint(6) NOT NULL DEFAULT '0',
  `status_point` smallint(6) NOT NULL DEFAULT '0',
  `status_check_prize` smallint(6) NOT NULL DEFAULT '0',
  `status_prize` smallint(6) NOT NULL DEFAULT '0',
  `status_task_to_project` smallint(6) NOT NULL DEFAULT '0',
  `status_is_synced` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lotteries_issue_lottery_id` (`lottery_id`),
  KEY `lotteries_issue_write_user_id` (`write_user_id`),
  KEY `lotteries_issue_verify_user_id` (`verify_user_id`),
  CONSTRAINT `lottery_id_refs_id_4437a32b` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `verify_user_id_refs_id_69c5cf90` FOREIGN KEY (`verify_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `write_user_id_refs_id_69c5cf90` FOREIGN KEY (`write_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_issue
-- ----------------------------
INSERT INTO `lotteries_issue` VALUES ('1', '100315001', '', '1', '2010-03-15', '2010-03-14 23:59:10', '2010-03-15 00:04:10', '2010-03-15 00:03:20', '2010-03-15 00:05:00', null, null, null, null, '0', '0', '0', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for `lotteries_issueerror`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_issueerror`;
CREATE TABLE `lotteries_issueerror` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_issueerror
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_issuehistory`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_issuehistory`;
CREATE TABLE `lotteries_issuehistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `missed` longtext NOT NULL,
  `total_missed` longtext NOT NULL,
  `series` longtext NOT NULL,
  `total_series` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_issuehistory_lottery_id` (`lottery_id`),
  KEY `lotteries_issuehistory_issue_id` (`issue_id`),
  CONSTRAINT `issue_id_refs_id_c5e12e0` FOREIGN KEY (`issue_id`) REFERENCES `lotteries_issue` (`id`),
  CONSTRAINT `lottery_id_refs_id_317a908` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_issuehistory
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
  `week_cycle` varchar(100) NOT NULL,
  `yearly_break_start` date NOT NULL,
  `yearly_break_end` date NOT NULL,
  `min_commission_gap` decimal(3,3) NOT NULL,
  `min_profit` decimal(3,3) NOT NULL,
  `issue_rule` varchar(200) NOT NULL,
  `description` longtext NOT NULL,
  `number_rule` longtext NOT NULL,
  `channel_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_lottery_lotterytype_id` (`lotterytype_id`),
  KEY `lotteries_lottery_channel_id` (`channel_id`),
  CONSTRAINT `channel_id_refs_id_34b3d5bf` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `lotterytype_id_refs_id_1c8112f1` FOREIGN KEY (`lotterytype_id`) REFERENCES `lotteries_lotterytype` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lottery
-- ----------------------------
INSERT INTO `lotteries_lottery` VALUES ('1', 'CQSSC', 'CQSSC', '0', '1', 'CQSSC', '1,2,3,4,5,6,7', '2010-02-13', '2010-02-19', '0.005', '0.040', '(y)(M)(D)(N3)', 'CQSSC', 'CQSSC', '3', '2010-03-25 16:41:20');

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
INSERT INTO `lotteries_lotterytype` VALUES ('1', '数字型', '3');
INSERT INTO `lotteries_lotterytype` VALUES ('2', '乐透分区型(蓝红球)', '2');
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
  `parent_id` int(11) DEFAULT NULL,
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
  KEY `lotteries_method_parent_id` (`parent_id`),
  CONSTRAINT `lottery_id_refs_id_2e416d46` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `parent_id_refs_id_73d4743` FOREIGN KEY (`parent_id`) REFERENCES `lotteries_method` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_method
-- ----------------------------
INSERT INTO `lotteries_method` VALUES ('1', '前三直选', '1', null, 'n/a', 'n/a', '1', '前三直选', '前三直选', '0', '0', 'n/a', '500000.00', '2000.0000');
INSERT INTO `lotteries_method` VALUES ('2', '前三直选', '1', '1', 'ssc_q3zhixuan', 'initNumberTypeThreeZhiXuanLock', '1', 'thinking about it', '前三直选_直选', '0', '1', 'lock_cqssc_qszhixuan', '500000.00', '2000.0000');

-- ----------------------------
-- Table structure for `lotteries_method_mode`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_method_mode`;
CREATE TABLE `lotteries_method_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method_id` int(11) NOT NULL,
  `mode_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_id` (`method_id`,`mode_id`),
  KEY `mode_id_refs_id_7d53b800` (`mode_id`),
  CONSTRAINT `mode_id_refs_id_7d53b800` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`),
  CONSTRAINT `method_id_refs_id_3e073758` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_method_mode
-- ----------------------------
INSERT INTO `lotteries_method_mode` VALUES ('1', '1', '1');
INSERT INTO `lotteries_method_mode` VALUES ('2', '1', '2');
INSERT INTO `lotteries_method_mode` VALUES ('3', '2', '1');
INSERT INTO `lotteries_method_mode` VALUES ('4', '2', '2');

-- ----------------------------
-- Table structure for `lotteries_mode`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_mode`;
CREATE TABLE `lotteries_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL,
  `rate` decimal(4,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_mode
-- ----------------------------
INSERT INTO `lotteries_mode` VALUES ('1', '元', '1.00');
INSERT INTO `lotteries_mode` VALUES ('2', '角', '0.10');

-- ----------------------------
-- Table structure for `lotteries_prizegroup`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_prizegroup`;
CREATE TABLE `lotteries_prizegroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `status` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_prizegroup_lottery_id` (`lottery_id`),
  CONSTRAINT `lottery_id_refs_id_117646a0` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_prizegroup
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_prizelevel`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_prizelevel`;
CREATE TABLE `lotteries_prizelevel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` longtext NOT NULL,
  `prizegroup_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `level` smallint(6) NOT NULL,
  `prize` decimal(10,2) NOT NULL,
  `point` decimal(4,4) NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_prizelevel_prizegroup_id` (`prizegroup_id`),
  KEY `lotteries_prizelevel_method_id` (`method_id`),
  CONSTRAINT `method_id_refs_id_5bbd120c` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `prizegroup_id_refs_id_6639642a` FOREIGN KEY (`prizegroup_id`) REFERENCES `lotteries_prizegroup` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_prizelevel
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_usermethodset`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_usermethodset`;
CREATE TABLE `lotteries_usermethodset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `prizegroup_id` int(11) NOT NULL,
  `point` decimal(4,3) NOT NULL,
  `limit_bonus` decimal(14,4) NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_usermethodset_user_id` (`user_id`),
  KEY `lotteries_usermethodset_method_id` (`method_id`),
  KEY `lotteries_usermethodset_prizegroup_id` (`prizegroup_id`),
  CONSTRAINT `method_id_refs_id_5579a0ab` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `prizegroup_id_refs_id_663e23d` FOREIGN KEY (`prizegroup_id`) REFERENCES `lotteries_prizegroup` (`id`),
  CONSTRAINT `user_id_refs_id_64e5f213` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_usermethodset
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_userprizegroup`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_userprizegroup`;
CREATE TABLE `lotteries_userprizegroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prizegroup_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `is_disabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_userprizegroup_user_id` (`user_id`),
  KEY `lotteries_userprizegroup_prizegroup_id` (`prizegroup_id`),
  KEY `lotteries_userprizegroup_lottery_id` (`lottery_id`),
  CONSTRAINT `lottery_id_refs_id_12c9fcc5` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `prizegroup_id_refs_id_70cc49e0` FOREIGN KEY (`prizegroup_id`) REFERENCES `lotteries_prizegroup` (`id`),
  CONSTRAINT `user_id_refs_id_2931c796` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_userprizegroup
-- ----------------------------

-- ----------------------------
-- Table structure for `lotteries_userprizelevel`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_userprizelevel`;
CREATE TABLE `lotteries_userprizelevel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prizelevel_id` int(11) NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_userprizelevel_user_id` (`user_id`),
  KEY `lotteries_userprizelevel_prizelevel_id` (`prizelevel_id`),
  CONSTRAINT `prizelevel_id_refs_id_474d2ce6` FOREIGN KEY (`prizelevel_id`) REFERENCES `lotteries_prizelevel` (`id`),
  CONSTRAINT `user_id_refs_id_230988e5` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_userprizelevel
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
  `author_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `checker_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `is_top` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notices_notice_author_id` (`author_id`),
  KEY `notices_notice_channel_id` (`channel_id`),
  KEY `notices_notice_checker_id` (`checker_id`),
  CONSTRAINT `author_id_refs_id_4123a64c` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_6b37f5f5` FOREIGN KEY (`channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `checker_id_refs_id_4123a64c` FOREIGN KEY (`checker_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of notices_notice
-- ----------------------------
INSERT INTO `notices_notice` VALUES ('1', '[转]上海世博至少落后广州亚运一百年！', '[转]上海世博至少落后广州亚运一百年！', '2010-03-25 17:03:21', '1', '3', '1', '0', '1');

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

-- ----------------------------
-- Table structure for `records_expandcode`
-- ----------------------------
DROP TABLE IF EXISTS `records_expandcode`;
CREATE TABLE `records_expandcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `level` smallint(6) NOT NULL,
  `multiple` smallint(6) NOT NULL,
  `prize` decimal(14,4) NOT NULL,
  `expanded_code` longtext NOT NULL,
  `updated` datetime NOT NULL,
  `hashvar` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `records_expandcode_project_id` (`project_id`),
  CONSTRAINT `project_id_refs_id_512340a6` FOREIGN KEY (`project_id`) REFERENCES `records_project` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of records_expandcode
-- ----------------------------
INSERT INTO `records_expandcode` VALUES ('2', '2', '1', '1', '1500.0000', '01 02|01 02|01 02 03', '2010-03-25 17:41:40', 'sdfsdf');

-- ----------------------------
-- Table structure for `records_order`
-- ----------------------------
DROP TABLE IF EXISTS `records_order`;
CREATE TABLE `records_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `records_order_user_id` (`user_id`),
  CONSTRAINT `user_id_refs_id_58e0baea` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of records_order
-- ----------------------------
INSERT INTO `records_order` VALUES ('1', '1', '2010-03-25 17:01:21');

-- ----------------------------
-- Table structure for `records_project`
-- ----------------------------
DROP TABLE IF EXISTS `records_project`;
CREATE TABLE `records_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `lottery_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `bonus` decimal(14,4) NOT NULL,
  `code` longtext NOT NULL,
  `code_type` varchar(30) NOT NULL,
  `price` decimal(14,4) NOT NULL,
  `mode_id` int(11) NOT NULL,
  `multiple` int(11) NOT NULL,
  `total_amount` decimal(14,4) NOT NULL,
  `supperior_id` int(11) DEFAULT NULL,
  `supperior_point` decimal(4,3) NOT NULL,
  `created` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `is_deducted` tinyint(1) NOT NULL,
  `is_canceled` smallint(6) NOT NULL,
  `is_get_prize` smallint(6) NOT NULL,
  `is_send_prize` tinyint(1) NOT NULL,
  `client_ip` char(15) NOT NULL,
  `proxy_ip` char(15) NOT NULL,
  `db_queries` smallint(6) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `records_project_user_id` (`user_id`),
  KEY `records_project_order_id` (`order_id`),
  KEY `records_project_task_id` (`task_id`),
  KEY `records_project_lottery_id` (`lottery_id`),
  KEY `records_project_method_id` (`method_id`),
  KEY `records_project_issue_id` (`issue_id`),
  KEY `records_project_mode_id` (`mode_id`),
  KEY `records_project_supperior_id` (`supperior_id`),
  KEY `records_project_is_canceled` (`is_canceled`),
  KEY `records_project_is_get_prize` (`is_get_prize`),
  KEY `records_project_client_ip` (`client_ip`),
  CONSTRAINT `issue_id_refs_id_2525d890` FOREIGN KEY (`issue_id`) REFERENCES `lotteries_issue` (`id`),
  CONSTRAINT `lottery_id_refs_id_436bc848` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `method_id_refs_id_1795890b` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `mode_id_refs_id_113483b3` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`),
  CONSTRAINT `order_id_refs_id_d0b9452` FOREIGN KEY (`order_id`) REFERENCES `records_order` (`id`),
  CONSTRAINT `supperior_id_refs_id_6974558d` FOREIGN KEY (`supperior_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `task_id_refs_id_3bcfafba` FOREIGN KEY (`task_id`) REFERENCES `records_task` (`id`),
  CONSTRAINT `user_id_refs_id_6974558d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of records_project
-- ----------------------------
INSERT INTO `records_project` VALUES ('2', '1', '1', null, '1', '1', '1', '1700.0000', '01 02|01 02|01 02 03', 'digital', '250.0000', '1', '1', '250.0000', null, '0.100', '2010-03-25 17:21:05', '2010-03-25 17:41:40', '0', '0', '0', '0', '127.0.0.1', '127.0.0.1', '1', 'sdfsdfsdfsdf');

-- ----------------------------
-- Table structure for `records_task`
-- ----------------------------
DROP TABLE IF EXISTS `records_task`;
CREATE TABLE `records_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `method_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `code` longtext NOT NULL,
  `code_type` varchar(30) NOT NULL,
  `total_issues` smallint(6) NOT NULL,
  `finished_issues` smallint(6) NOT NULL,
  `canceled_issues` smallint(6) NOT NULL,
  `mode_id` int(11) NOT NULL,
  `price` decimal(14,4) NOT NULL,
  `total_amount` decimal(14,4) NOT NULL,
  `finished_amount` decimal(14,4) NOT NULL,
  `canceled_amount` decimal(14,4) NOT NULL,
  `start_time` datetime NOT NULL,
  `start_issue` varchar(30) NOT NULL,
  `win_issues` smallint(6) NOT NULL,
  `update_time` datetime NOT NULL,
  `prize` longtext NOT NULL,
  `diffpoints` longtext NOT NULL,
  `supperior_id` int(11) DEFAULT NULL,
  `supperior_point` decimal(4,3) NOT NULL,
  `status` smallint(6) NOT NULL,
  `stop_on_win` tinyint(1) NOT NULL,
  `client_ip` char(15) NOT NULL,
  `proxy_ip` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `records_task_user_id` (`user_id`),
  KEY `records_task_lottery_id` (`lottery_id`),
  KEY `records_task_method_id` (`method_id`),
  KEY `records_task_order_id` (`order_id`),
  KEY `records_task_supperior_id` (`supperior_id`),
  KEY `records_task_client_ip` (`client_ip`),
  KEY `mode_id` (`mode_id`),
  CONSTRAINT `records_task_ibfk_1` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`),
  CONSTRAINT `lottery_id_refs_id_62693bdd` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `method_id_refs_id_7d9c7b1a` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `order_id_refs_id_6f921121` FOREIGN KEY (`order_id`) REFERENCES `records_order` (`id`),
  CONSTRAINT `supperior_id_refs_id_362c1a4e` FOREIGN KEY (`supperior_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `user_id_refs_id_362c1a4e` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of records_task
-- ----------------------------
INSERT INTO `records_task` VALUES ('1', '前三直选_直选 追号2期', '1', '1', '1', '1', '0|1|2', 'digital', '2', '1', '1', '1', '2.0000', '4.0000', '2.0000', '2.0000', '2010-03-25 17:46:27', '100316068', '0', '2010-03-25 17:57:45', 'a:1:{s:4:\"base\";a:1:{i:0;a:5:{s:9:\"projectid\";i:0;s:5:\"level\";i:1;s:9:\"codetimes\";i:1;s:5:\"prize\";s:7:\"1700.00\";s:10:\"expandcode\";s:5:\"0|1|2\";}}}', 'a:1:{s:4:\"base\";a:3:{i:0;a:5:{s:6:\"userid\";i:200319;s:9:\"diffpoint\";d:0.0899999999999999966693309261245303787291049957275390625;s:9:\"projectid\";i:0;s:9:\"diffmoney\";d:0.179999999999999993338661852249060757458209991455078125;s:6:\"status\";i:0;}i:1;a:5:{s:6:\"userid\";i:200320;s:9:\"diffpoint\";d:0.005000000000000000104083408558608425664715468883514404296875;s:9:\"projectid\";i:0;s:9:\"diffmoney\";d:0.01000000000000000020816681711721685132943093776702880859375;s:6:\"status\";i:0;}i:2;a:6:{s:6:\"userid\";i:200322;s:9:\"diffpoint\";s:5:\"0.005\";s:9:\"projectid\";i:0;s:9:\"diffmoney\";d:0.01000000000000000020816681711721685132943093776702880859375;s:6:\"status\";i:1;s:8:\"sendtime\";s:5:\"now()\";}}}', null, '0.100', '0', '1', '127.0.0.1', '127.0.0.1');

-- ----------------------------
-- Table structure for `records_taskdetail`
-- ----------------------------
DROP TABLE IF EXISTS `records_taskdetail`;
CREATE TABLE `records_taskdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `multiple` smallint(6) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `status` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `records_taskdetail_task_id` (`task_id`),
  KEY `records_taskdetail_project_id` (`project_id`),
  KEY `records_taskdetail_issue_id` (`issue_id`),
  CONSTRAINT `issue_id_refs_id_5d36676c` FOREIGN KEY (`issue_id`) REFERENCES `lotteries_issue` (`id`),
  CONSTRAINT `project_id_refs_id_2badb6af` FOREIGN KEY (`project_id`) REFERENCES `records_project` (`id`),
  CONSTRAINT `task_id_refs_id_d622b36` FOREIGN KEY (`task_id`) REFERENCES `records_task` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of records_taskdetail
-- ----------------------------
INSERT INTO `records_taskdetail` VALUES ('2', '1', '2', '1', '1', '0');
