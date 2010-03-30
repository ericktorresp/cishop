/*
Navicat MySQL Data Transfer

Source Server         : localhost:3306
Source Server Version : 50142
Source Host           : localhost:3306
Source Database       : blog

Target Server Type    : MYSQL
Target Server Version : 50142
File Encoding         : 65001

Date: 2010-03-30 17:33:04
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
  `mode_id` int(11) DEFAULT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `pre_balance` decimal(14,4) NOT NULL,
  `pre_hold` decimal(14,4) NOT NULL,
  `pre_available` decimal(14,4) NOT NULL,
  `suf_balance` decimal(14,4) NOT NULL,
  `suf_hold` decimal(14,4) NOT NULL,
  `suf_available` decimal(14,4) NOT NULL,
  `client_ip` char(15) DEFAULT NULL,
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
  KEY `accounts_account_hashvar` (`hashvar`),
  CONSTRAINT `dest_channel_id_refs_id_29d6901f` FOREIGN KEY (`dest_channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `from_user_id_refs_id_712b888` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `lottery_id_refs_id_63647a3` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `method_id_refs_id_621c9720` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `mode_id_refs_id_7ef13db8` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`),
  CONSTRAINT `operator_id_refs_id_712b888` FOREIGN KEY (`operator_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `order_id_refs_id_2e3190e7` FOREIGN KEY (`order_id`) REFERENCES `records_order` (`id`),
  CONSTRAINT `project_id_refs_id_7c25465a` FOREIGN KEY (`project_id`) REFERENCES `records_project` (`id`),
  CONSTRAINT `source_channel_id_refs_id_29d6901f` FOREIGN KEY (`source_channel_id`) REFERENCES `channels_channel` (`id`),
  CONSTRAINT `task_id_refs_id_7c33757f` FOREIGN KEY (`task_id`) REFERENCES `records_task` (`id`),
  CONSTRAINT `to_user_id_refs_id_712b888` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `type_id_refs_id_6d19ccbf` FOREIGN KEY (`type_id`) REFERENCES `accounts_accounttype` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of accounts_account
-- ----------------------------
INSERT INTO `accounts_account` VALUES ('1', '加入游戏', '加入游戏', '250.0000', '1', '2', '1', null, '2', '1', '1', null, '3', '35898.9200', '11918.0000', '23980.9200', '35898.9200', '12168.0000', '23730.9200', '127.0.0.1', '', '2010-03-30 10:53:19', '2010-03-30 10:53:19', '3', null, null, '0', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='帐变类型表';

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
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `permission_id_refs_id_5886d21f` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8;

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
INSERT INTO `auth_permission` VALUES ('106', 'Can add issue error', '36', 'add_issueerror');
INSERT INTO `auth_permission` VALUES ('107', 'Can change issue error', '36', 'change_issueerror');
INSERT INTO `auth_permission` VALUES ('108', 'Can delete issue error', '36', 'delete_issueerror');
INSERT INTO `auth_permission` VALUES ('109', 'Can add registration profile', '37', 'add_registrationprofile');
INSERT INTO `auth_permission` VALUES ('110', 'Can change registration profile', '37', 'change_registrationprofile');
INSERT INTO `auth_permission` VALUES ('111', 'Can delete registration profile', '37', 'delete_registrationprofile');
INSERT INTO `auth_permission` VALUES ('112', 'Can add lock', '38', 'add_lock');
INSERT INTO `auth_permission` VALUES ('113', 'Can change lock', '38', 'change_lock');
INSERT INTO `auth_permission` VALUES ('114', 'Can delete lock', '38', 'delete_lock');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of auth_user
-- ----------------------------
INSERT INTO `auth_user` VALUES ('1', 'root', '', '', 'kirinse@gmail.com', 'sha1$8ae46$39e846d47d4b08e3ca664356a74d87a3bca14a3b', '1', '1', '1', '2010-03-30 10:46:49', '2010-03-25 16:56:01');
INSERT INTO `auth_user` VALUES ('3', 'floyd', '', '', '', 'sha1$3b259$a780f347dee2c43488ac5dbd18698fd8d59df60e', '0', '0', '0', '2010-03-29 17:12:32', '2010-03-29 17:11:55');
INSERT INTO `auth_user` VALUES ('8', 'darkmoon', '', '', '142620@qq.com', 'sha1$6f2c4$5ba68913a87284448bf9ae6b7a3e31daea3f683c', '0', '1', '0', '2010-03-29 18:06:40', '2010-03-29 18:04:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='频道信息表';

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COMMENT='频道变量配置表';

-- ----------------------------
-- Records of channels_configure
-- ----------------------------
INSERT INTO `channels_configure` VALUES ('1', null, 'operation', '', '', '', '', '3', '运营参数', '运营参数', '0');
INSERT INTO `channels_configure` VALUES ('2', '1', 'limitbonus', '100000', '100000', 'num', 'text', '3', '奖金限额', '奖金限额', '0');
INSERT INTO `channels_configure` VALUES ('3', '1', 'bigordercancel', '200000', '10000', 'num', 'text', '3', '大额撤单起始金额', '大额撤单起始金额', '0');
INSERT INTO `channels_configure` VALUES ('4', '1', 'bigordercancelpre', '0.01', '0.01', 'num', 'text', '3', '手续费用比例', '手续费用比例', '0');
INSERT INTO `channels_configure` VALUES ('5', '1', 'specialmaxtimes', '5', '5', 'num', 'text', '3', '靓号区购买倍数最大倍数', '靓号区购买倍数最大倍数', '0');
INSERT INTO `channels_configure` VALUES ('6', '1', 'admincancellimit', '3000', '30', 'num', 'text', '3', '管理员单笔撤单最多时间（以录入号码时间为准，单位:分钟）', '管理员单笔撤单最多时间（以录入号码时间为准，单位:分钟）', '0');
INSERT INTO `channels_configure` VALUES ('7', '1', 'issueexceptiontime', '720', '120', 'num', 'text', '3', '撤消派奖最大允许时间范围（以号码录入时间为准，单位：分钟）', '撤消派奖最大允许时间范围（以号码录入时间为准，单位：分钟）', '0');
INSERT INTO `channels_configure` VALUES ('8', '1', 'sysoneonline', 'no', 'yes', 'string', 'select', '3', '限制同一帐号一人在线', '限制同一帐号一人在线', '0');
INSERT INTO `channels_configure` VALUES ('9', null, 'clearset', '', '', '', '', '3', '清理参数', '清理参数', '0');
INSERT INTO `channels_configure` VALUES ('10', '9', 'logcleardate', '10', '5', 'num', 'text', '3', '日志清理天数', '日志清理天数', '0');
INSERT INTO `channels_configure` VALUES ('11', '9', 'logclearstarttime', '04:00:00', '02:00:00', 'string', 'text', '3', '日志清理最早时间', '日志清理最早时间', '0');
INSERT INTO `channels_configure` VALUES ('12', '9', 'logclearendtime', '04:30:00', '03:00:00', 'string', 'text', '3', '日志清理最晚时间', '日志清理最晚时间', '0');
INSERT INTO `channels_configure` VALUES ('13', '9', 'orderscleardate', '3', '14', 'num', 'text', '3', '帐变清理日期', '帐变清理几天前的数据', '0');
INSERT INTO `channels_configure` VALUES ('14', '9', 'ordersclearstarttime', '03:00:01', '02:00:00', 'string', 'text', '3', '帐变清理的最早时间', '帐变清理的最早时间', '0');
INSERT INTO `channels_configure` VALUES ('15', '9', 'ordersclearendtime', '04:00:01', '03:00:00', 'string', 'text', '3', '帐变清理最晚时间', '帐变清理最晚时间', '0');
INSERT INTO `channels_configure` VALUES ('16', '9', 'logclearrun', '1', '1', 'num', 'checkbox', '3', '日志是否清理', '日志是否清理', '0');
INSERT INTO `channels_configure` VALUES ('17', '9', 'ordersclearrun', '1', '1', 'num', 'checkbox', '2', '是否运行帐变清理', '是否运行帐变清理', '0');
INSERT INTO `channels_configure` VALUES ('18', '9', 'issuecleardate', '3', '5', 'num', 'text', '3', '奖期清理天数', '奖期清理几天前的数据', '0');
INSERT INTO `channels_configure` VALUES ('19', '9', 'issueclearstarttime', '03:00:01', '02:00:00', 'string', 'text', '3', '奖期清理最早时间', '奖期清理最早时间', '0');
INSERT INTO `channels_configure` VALUES ('20', '9', 'issueclearendtime', '04:00:00', '03:00:00', 'string', 'text', '3', '奖期清理最晚时间', '奖期清理最晚时间', '0');
INSERT INTO `channels_configure` VALUES ('21', '9', 'issueclearrun', '1', '1', 'num', 'checkbox', '3', '奖期是否清理', '奖期是否清理', '0');
INSERT INTO `channels_configure` VALUES ('22', '9', 'projectcleardate', '3', '5', 'num', 'text', '3', '方案清理天数', '方案清理几天前的数据', '0');
INSERT INTO `channels_configure` VALUES ('23', '9', 'projectclearstarttime', '03:00:01', '02:00:00', 'string', 'text', '3', '方案清理最早时间', '方案清理最早时间', '0');
INSERT INTO `channels_configure` VALUES ('24', '9', 'projectclearendtime', '04:00:01', '03:00:00', 'string', 'text', '3', '方案清理最晚时间', '方案清理最晚时间', '0');
INSERT INTO `channels_configure` VALUES ('25', '9', 'projectclearrun', '1', '1', 'num', 'checkbox', '3', '方案是否清理', '', '0');
INSERT INTO `channels_configure` VALUES ('26', '9', 'historylockcleardate', '2', '5', 'num', 'text', '3', '历史封锁表清理天数', '历史封锁表清理天数', '0');
INSERT INTO `channels_configure` VALUES ('27', '9', 'historylockclearstarttime', '04:30:00', '02:00:00', 'string', 'text', '3', '历史封锁表清理最早时间', '历史封锁表清理最早时间', '0');
INSERT INTO `channels_configure` VALUES ('28', '9', 'historylockclearendtime', '05:00:00', '03:00:00', 'string', 'text', '3', '历史封锁表清理最晚时间', '历史封锁表清理最晚时间', '0');
INSERT INTO `channels_configure` VALUES ('29', '9', 'historylockclearrun', '1', '1', 'num', 'checkbox', '3', '历史封锁表是否清理', '历史封锁表是否清理', '0');
INSERT INTO `channels_configure` VALUES ('30', null, 'tradeset', '', '', '', '', '3', '交易设置', '', '0');
INSERT INTO `channels_configure` VALUES ('31', '30', 'zz_forbid_time', '2:00 - 4:30', '0:00', 'string', 'select', '3', '禁止转账时间', '', '0');
INSERT INTO `channels_configure` VALUES ('32', '30', 'kz_allow_time', '2:20 - 4:00', '0', 'string', 'select', '3', '快照允许启动时间', '', '0');
INSERT INTO `channels_configure` VALUES ('33', '30', 'cd_3dp5_repealtimerange', '20:30 - 23:59', '20:30 - 23:59', 'string', 'select', '3', '系统撤单的时间范围', '', '0');
INSERT INTO `channels_configure` VALUES ('34', null, '', '', '', '', '', '3', '计划任务设置', '', '0');
INSERT INTO `channels_configure` VALUES ('35', '34', 'task_bankss_money1', '3000', '3000', 'num', 'text', '3', '[快照] 自动对账报警最小差额 (单笔金额)', '', '0');
INSERT INTO `channels_configure` VALUES ('36', '34', 'task_bankss_money2', '5000', '5000', 'num', 'text', '3', '[快照] 自动对账报警最小差额 (总计金额)', '', '0');
INSERT INTO `channels_configure` VALUES ('37', null, 'adminuser', '', '', '', '', '3', '管理员参数', '', '0');
INSERT INTO `channels_configure` VALUES ('38', '37', 'islimitip', 'no', 'no', 'string', 'select', '3', '是否启用信任IP控制登录', '', '0');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户频道设置表';

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
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_admin_log` VALUES ('23', '2010-03-29 10:15:57', '1', '22', '2', '100315001: 27631', '1', '');
INSERT INTO `django_admin_log` VALUES ('24', '2010-03-29 10:40:42', '1', '36', '1', 'CQSSC', '1', '');
INSERT INTO `django_admin_log` VALUES ('25', '2010-03-29 14:21:45', '1', '16', '1', 'CQSSC1700-6.8', '1', '');
INSERT INTO `django_admin_log` VALUES ('26', '2010-03-29 15:38:54', '1', '24', '4', 'prize', '1', '');
INSERT INTO `django_admin_log` VALUES ('27', '2010-03-29 15:39:24', '1', '24', '4', 'CQSSC 1700 - 6.8 Level 1', '2', '已修改 description 。');
INSERT INTO `django_admin_log` VALUES ('28', '2010-03-29 17:11:55', '1', '3', '3', 'floyd', '1', '');
INSERT INTO `django_admin_log` VALUES ('29', '2010-03-30 10:46:12', '1', '3', '3', 'floyd', '2', '已修改 is_active 。');
INSERT INTO `django_admin_log` VALUES ('30', '2010-03-30 10:53:19', '1', '34', '1', '加入游戏', '1', '');
INSERT INTO `django_admin_log` VALUES ('31', '2010-03-30 11:49:31', '1', '38', '1', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('32', '2010-03-30 12:01:03', '1', '38', '2', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('33', '2010-03-30 12:02:18', '1', '38', '2', 'lock_cqssc_qszhixuan', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('34', '2010-03-30 12:02:54', '1', '38', '3', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('35', '2010-03-30 13:19:20', '1', '38', '3', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('36', '2010-03-30 13:19:20', '1', '38', '2', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('37', '2010-03-30 13:46:32', '1', '38', '4', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('38', '2010-03-30 13:51:37', '1', '38', '5', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('39', '2010-03-30 14:06:26', '1', '38', '6', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('40', '2010-03-30 14:08:26', '1', '38', '7', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('41', '2010-03-30 14:21:50', '1', '38', '8', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('42', '2010-03-30 14:22:35', '1', '38', '8', 'lock_cqssc_qszhixuan', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('43', '2010-03-30 14:23:14', '1', '38', '9', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('44', '2010-03-30 14:31:17', '1', '38', '10', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('45', '2010-03-30 14:38:20', '1', '38', '10', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('46', '2010-03-30 14:38:20', '1', '38', '9', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('47', '2010-03-30 14:38:20', '1', '38', '8', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('48', '2010-03-30 14:38:34', '1', '38', '11', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('49', '2010-03-30 14:40:24', '1', '38', '11', 'lock_cqssc_qszhixuan', '3', '');
INSERT INTO `django_admin_log` VALUES ('50', '2010-03-30 14:57:38', '1', '38', '12', 'lock_cqssc_qszhixuan', '1', '');
INSERT INTO `django_admin_log` VALUES ('51', '2010-03-30 15:31:19', '1', '13', '2', '奖金限额', '1', '');
INSERT INTO `django_admin_log` VALUES ('52', '2010-03-30 15:32:30', '1', '13', '3', '大额撤单起始金额', '1', '');
INSERT INTO `django_admin_log` VALUES ('53', '2010-03-30 15:33:08', '1', '13', '4', '手续费用比例', '1', '');
INSERT INTO `django_admin_log` VALUES ('54', '2010-03-30 15:33:35', '1', '13', '5', '靓号区购买倍数最大倍数', '1', '');
INSERT INTO `django_admin_log` VALUES ('55', '2010-03-30 15:34:08', '1', '13', '6', '管理员单笔撤单最多时间（以录入号码时间为准，单位:分钟）', '1', '');
INSERT INTO `django_admin_log` VALUES ('56', '2010-03-30 15:34:38', '1', '13', '7', '撤消派奖最大允许时间范围（以号码录入时间为准，单位：分钟）', '1', '');
INSERT INTO `django_admin_log` VALUES ('57', '2010-03-30 15:35:14', '1', '13', '8', '限制同一帐号一人在线', '1', '');
INSERT INTO `django_admin_log` VALUES ('58', '2010-03-30 15:36:02', '1', '13', '9', '清理参数', '1', '');
INSERT INTO `django_admin_log` VALUES ('59', '2010-03-30 15:37:00', '1', '13', '10', '日志清理天数', '1', '');
INSERT INTO `django_admin_log` VALUES ('60', '2010-03-30 15:37:41', '1', '13', '11', '日志清理最早时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('61', '2010-03-30 15:38:09', '1', '13', '12', '日志清理最晚时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('62', '2010-03-30 15:38:44', '1', '13', '13', '帐变清理日期', '1', '');
INSERT INTO `django_admin_log` VALUES ('63', '2010-03-30 15:39:26', '1', '13', '14', '帐变清理的最早时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('64', '2010-03-30 15:39:58', '1', '13', '15', '帐变清理最晚时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('65', '2010-03-30 15:40:50', '1', '13', '16', '日志是否清理', '1', '');
INSERT INTO `django_admin_log` VALUES ('66', '2010-03-30 15:41:18', '1', '13', '17', '是否运行帐变清理', '1', '');
INSERT INTO `django_admin_log` VALUES ('67', '2010-03-30 15:41:54', '1', '13', '18', '奖期清理天数', '1', '');
INSERT INTO `django_admin_log` VALUES ('68', '2010-03-30 15:42:05', '1', '13', '18', '奖期清理天数', '2', '已修改 description 。');
INSERT INTO `django_admin_log` VALUES ('69', '2010-03-30 15:42:42', '1', '13', '19', '奖期清理最早时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('70', '2010-03-30 15:43:15', '1', '13', '20', '奖期清理最晚时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('71', '2010-03-30 15:43:53', '1', '13', '21', '奖期是否清理', '1', '');
INSERT INTO `django_admin_log` VALUES ('72', '2010-03-30 15:44:30', '1', '13', '22', '方案清理天数', '1', '');
INSERT INTO `django_admin_log` VALUES ('73', '2010-03-30 15:44:57', '1', '13', '23', '方案清理最早时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('74', '2010-03-30 15:45:30', '1', '13', '24', '方案清理最晚时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('75', '2010-03-30 15:45:58', '1', '13', '25', '方案是否清理', '1', '');
INSERT INTO `django_admin_log` VALUES ('76', '2010-03-30 15:46:28', '1', '13', '26', '历史封锁表清理天数', '1', '');
INSERT INTO `django_admin_log` VALUES ('77', '2010-03-30 15:46:59', '1', '13', '27', '历史封锁表清理最早时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('78', '2010-03-30 15:47:29', '1', '13', '28', '历史封锁表清理最晚时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('79', '2010-03-30 15:47:57', '1', '13', '29', '历史封锁表是否清理', '1', '');
INSERT INTO `django_admin_log` VALUES ('80', '2010-03-30 15:48:30', '1', '13', '30', '交易设置', '1', '');
INSERT INTO `django_admin_log` VALUES ('81', '2010-03-30 15:49:52', '1', '13', '31', '禁止转账时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('82', '2010-03-30 15:50:44', '1', '13', '32', '快照允许启动时间', '1', '');
INSERT INTO `django_admin_log` VALUES ('83', '2010-03-30 15:51:31', '1', '13', '33', '系统撤单的时间范围', '1', '');
INSERT INTO `django_admin_log` VALUES ('84', '2010-03-30 15:51:53', '1', '13', '34', '计划任务设置', '1', '');
INSERT INTO `django_admin_log` VALUES ('85', '2010-03-30 15:52:27', '1', '13', '35', '[快照] 自动对账报警最小差额 (单笔金额)', '1', '');
INSERT INTO `django_admin_log` VALUES ('86', '2010-03-30 15:52:52', '1', '13', '36', '[快照] 自动对账报警最小差额 (总计金额)', '1', '');
INSERT INTO `django_admin_log` VALUES ('87', '2010-03-30 15:53:17', '1', '13', '37', '管理员参数', '1', '');
INSERT INTO `django_admin_log` VALUES ('88', '2010-03-30 15:54:08', '1', '13', '38', '是否启用信任IP控制登录', '1', '');
INSERT INTO `django_admin_log` VALUES ('89', '2010-03-30 15:57:34', '1', '29', '1', '2010-03-25 17:01:21', '2', '没有字段被修改。');
INSERT INTO `django_admin_log` VALUES ('90', '2010-03-30 15:58:23', '1', '7', '1', 'localhost', '2', '已修改 domain 和 name 。');

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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

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
INSERT INTO `django_content_type` VALUES ('36', 'issue error', 'lotteries', 'issueerror');
INSERT INTO `django_content_type` VALUES ('37', 'registration profile', 'registration', 'registrationprofile');
INSERT INTO `django_content_type` VALUES ('38', 'lock', 'lotteries', 'lock');

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
INSERT INTO `django_session` VALUES ('28ee9853b0ceadab8dea314372035d6c', 'gAJ9cQEoVRJfYXV0aF91c2VyX2JhY2tlbmRxAlUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmRxA1UNX2F1dGhfdXNlcl9pZHEEigEBdS4zZDMyNGVhYWFkYTkwYWVhODNk\nZTI4NGY4MzIwNzgyZQ==\n', '2010-04-13 10:46:49');
INSERT INTO `django_session` VALUES ('bf2b70d55d555d853feb43406050fb87', 'gAJ9cQEuODZmNTc2NTBjZDBmZDYxNGU1OGFmMTNjZTg4ZGUyYzE=\n', '2010-04-12 17:12:03');

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
INSERT INTO `django_site` VALUES ('1', 'localhost', 'localhost');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='帮助信息表';

-- ----------------------------
-- Records of helps_help
-- ----------------------------
INSERT INTO `helps_help` VALUES ('1', '3', 'bank', '[转]上海世博至少落后广州亚运一百年！', '[转]上海世博至少落后广州亚运一百年！', '1', '2010-03-26 17:37:13', '0', '0');

-- ----------------------------
-- Table structure for `lock_cqssc_qszhixuan`
-- ----------------------------
DROP TABLE IF EXISTS `lock_cqssc_qszhixuan`;
CREATE TABLE `lock_cqssc_qszhixuan` (
  `issue` varchar(20) NOT NULL COMMENT '奖期',
  `method_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩法ID',
  `code` varchar(100) NOT NULL COMMENT '号码',
  `special_value` varchar(100) NOT NULL DEFAULT ' ' COMMENT '特征值',
  `stamp` tinyint(1) NOT NULL DEFAULT '0' COMMENT '组三组六特征(1组三，2组六)',
  `prizes` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '奖金值',
  `is_bonuscode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否对应当期开奖号码(0:否,1:是)',
  UNIQUE KEY `idx_index` (`issue`,`method_id`,`code`),
  KEY `idx_code` (`code`),
  KEY `idx_issue` (`issue`),
  KEY `lock_cqssc_qszhixuan_ibfk` (`method_id`),
  CONSTRAINT `lock_cqssc_qszhixuan_ibfk` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='lock_cqssc_qszhixuan';

-- ----------------------------
-- Records of lock_cqssc_qszhixuan
-- ----------------------------

-- ----------------------------
-- Table structure for `lock_example`
-- ----------------------------
DROP TABLE IF EXISTS `lock_example`;
CREATE TABLE `lock_example` (
  `issue` varchar(20) NOT NULL COMMENT '奖期',
  `method_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩法ID',
  `code` varchar(100) NOT NULL COMMENT '号码',
  `special_value` varchar(100) NOT NULL DEFAULT ' ' COMMENT '特征值',
  `stamp` tinyint(1) NOT NULL DEFAULT '0' COMMENT '组三组六特征(1组三，2组六)',
  `prizes` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '奖金值',
  `is_bonuscode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否对应当期开奖号码(0:否,1:是)',
  UNIQUE KEY `idx_index` (`issue`,`method_id`,`code`),
  KEY `idx_code` (`code`),
  KEY `idx_issue` (`issue`),
  KEY `method_id` (`method_id`),
  CONSTRAINT `lock_example_ibfk_1` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='封锁表样例';

-- ----------------------------
-- Records of lock_example
-- ----------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='奖期表';

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
  `lottery_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `error_type` smallint(6) NOT NULL,
  `ahead_open_time` datetime DEFAULT NULL,
  `note_time` datetime NOT NULL,
  `noter_id` int(11) NOT NULL,
  `old_code` varchar(30) NOT NULL,
  `old_status_code` smallint(6) NOT NULL,
  `old_status_deduct` smallint(6) NOT NULL,
  `old_status_userpoint` smallint(6) NOT NULL,
  `old_status_checkbonus` smallint(6) NOT NULL,
  `old_status_bonus` smallint(6) NOT NULL,
  `old_status_tasktoproject` smallint(6) NOT NULL,
  `new_code` varchar(30) NOT NULL,
  `new_status_code` smallint(6) NOT NULL,
  `new_status_deduct` smallint(6) NOT NULL,
  `new_status_userpoint` smallint(6) NOT NULL,
  `new_status_checkbonus` smallint(6) NOT NULL,
  `new_status_bonus` smallint(6) NOT NULL,
  `new_status_tasktoproject` tinyint(1) NOT NULL,
  `new_status_cancelbonus` smallint(6) NOT NULL,
  `new_status_repeal` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_issueerror_lottery_id` (`lottery_id`),
  KEY `lotteries_issueerror_issue_id` (`issue_id`),
  KEY `lotteries_issueerror_noter_id` (`noter_id`),
  CONSTRAINT `lotteries_issueerror_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `lotteries_issue` (`id`),
  CONSTRAINT `lotteries_issueerror_ibfk_2` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `lotteries_issueerror_ibfk_3` FOREIGN KEY (`noter_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_issueerror
-- ----------------------------
INSERT INTO `lotteries_issueerror` VALUES ('1', '1', '1', '2', null, '2010-03-29 10:40:42', '1', '123', '1', '0', '0', '0', '0', '0', '456', '0', '0', '0', '0', '0', '0', '0', '0');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='奖期历史表';

-- ----------------------------
-- Records of lotteries_issuehistory
-- ----------------------------
INSERT INTO `lotteries_issuehistory` VALUES ('2', '1', '27631', '1', 'sdf', 'sdf', 'sdf', 'sdf');

-- ----------------------------
-- Table structure for `lotteries_lock`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries_lock`;
CREATE TABLE `lotteries_lock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `lottery_id` int(11) NOT NULL,
  `max_lost` decimal(14,4) NOT NULL,
  `code_function` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lotteries_lock_title` (`title`),
  KEY `lotteries_lock_lottery_id` (`lottery_id`),
  CONSTRAINT `lottery_id_refs_id_606795c2` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lotteries_lock
-- ----------------------------
INSERT INTO `lotteries_lock` VALUES ('12', 'lock_cqssc_qszhixuan', '1', '100000.0000', 'ssc_qszhixuan');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='彩种表';

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='彩种类型表';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='玩法表';

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
  CONSTRAINT `method_id_refs_id_3e073758` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `mode_id_refs_id_7d53b800` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='玩法/模式关联表';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='模式表';

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='奖金组表';

-- ----------------------------
-- Records of lotteries_prizegroup
-- ----------------------------
INSERT INTO `lotteries_prizegroup` VALUES ('1', 'CQSSC1700-6.8', '1', '0');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='奖级表';

-- ----------------------------
-- Records of lotteries_prizelevel
-- ----------------------------
INSERT INTO `lotteries_prizelevel` VALUES ('4', 'CQSSC 1700 - 6.8 Level 1', '1', '2', '1', '1700.00', '0.1000', '0');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户/玩法关联表';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户/奖金组关联表';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户/奖级关联表';

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='公告信息表';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='号码扩展表';

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='订单表';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='注单表';

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
  CONSTRAINT `lottery_id_refs_id_62693bdd` FOREIGN KEY (`lottery_id`) REFERENCES `lotteries_lottery` (`id`),
  CONSTRAINT `method_id_refs_id_7d9c7b1a` FOREIGN KEY (`method_id`) REFERENCES `lotteries_method` (`id`),
  CONSTRAINT `order_id_refs_id_6f921121` FOREIGN KEY (`order_id`) REFERENCES `records_order` (`id`),
  CONSTRAINT `records_task_ibfk_1` FOREIGN KEY (`mode_id`) REFERENCES `lotteries_mode` (`id`),
  CONSTRAINT `supperior_id_refs_id_362c1a4e` FOREIGN KEY (`supperior_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `user_id_refs_id_362c1a4e` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='追号表';

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='追号详情表';

-- ----------------------------
-- Records of records_taskdetail
-- ----------------------------
INSERT INTO `records_taskdetail` VALUES ('2', '1', '2', '1', '1', '0');

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
  CONSTRAINT `user_id_refs_id_313280c4` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of registration_registrationprofile
-- ----------------------------
INSERT INTO `registration_registrationprofile` VALUES ('6', '8', 'ALREADY_ACTIVATED');
