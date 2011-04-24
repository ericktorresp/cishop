/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50510
 Source Host           : localhost
 Source Database       : e88

 Target Server Type    : MySQL
 Target Server Version : 50510
 File Encoding         : utf-8

 Date: 04/25/2011 00:13:41 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `announcement`
-- ----------------------------
DROP TABLE IF EXISTS `announcement`;
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  `verify_time` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `sticked` tinyint(1) NOT NULL,
  `channel_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `announcement_337b96ff` (`author_id`),
  KEY `announcement_584122da` (`verifier_id`),
  KEY `announcement_668d8aa` (`channel_id`),
  CONSTRAINT `author_id_refs_id_126a9b25` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_6520b93c` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`),
  CONSTRAINT `verifier_id_refs_id_126a9b25` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `auth_group_permissions_425ae3c4` (`group_id`),
  KEY `auth_group_permissions_1e014c8f` (`permission_id`),
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `permission_id_refs_id_5886d21f` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
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
  KEY `auth_message_403f60f` (`user_id`),
  CONSTRAINT `user_id_refs_id_650f49a6` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  KEY `auth_permission_1bb8f392` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_728de91f` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `auth_permission`
-- ----------------------------
INSERT INTO `auth_permission` VALUES ('1', 'Can add permission', '1', 'add_permission'), ('2', 'Can change permission', '1', 'change_permission'), ('3', 'Can delete permission', '1', 'delete_permission'), ('4', 'Can add group', '2', 'add_group'), ('5', 'Can change group', '2', 'change_group'), ('6', 'Can delete group', '2', 'delete_group'), ('7', 'Can add user', '3', 'add_user'), ('8', 'Can change user', '3', 'change_user'), ('9', 'Can delete user', '3', 'delete_user'), ('10', 'Can add message', '4', 'add_message'), ('11', 'Can change message', '4', 'change_message'), ('12', 'Can delete message', '4', 'delete_message'), ('13', 'Can add content type', '5', 'add_contenttype'), ('14', 'Can change content type', '5', 'change_contenttype'), ('15', 'Can delete content type', '5', 'delete_contenttype'), ('16', 'Can add session', '6', 'add_session'), ('17', 'Can change session', '6', 'change_session'), ('18', 'Can delete session', '6', 'delete_session'), ('19', 'Can add site', '7', 'add_site'), ('20', 'Can change site', '7', 'change_site'), ('21', 'Can delete site', '7', 'delete_site'), ('22', 'Can add access attempt', '8', 'add_accessattempt'), ('23', 'Can change access attempt', '8', 'change_accessattempt'), ('24', 'Can delete access attempt', '8', 'delete_accessattempt'), ('25', 'Can add Country', '9', 'add_country'), ('26', 'Can change Country', '9', 'change_country'), ('27', 'Can delete Country', '9', 'delete_country'), ('28', 'Can add Province', '10', 'add_province'), ('29', 'Can change Province', '10', 'change_province'), ('30', 'Can delete Province', '10', 'delete_province'), ('31', 'Can add City', '11', 'add_city'), ('32', 'Can change City', '11', 'change_city'), ('33', 'Can delete City', '11', 'delete_city'), ('34', 'Can add Domain', '12', 'add_domain'), ('35', 'Can change Domain', '12', 'change_domain'), ('36', 'Can delete Domain', '12', 'delete_domain'), ('37', 'Can add Channel', '13', 'add_channel'), ('38', 'Can change Channel', '13', 'change_channel'), ('39', 'Can delete Channel', '13', 'delete_channel'), ('40', 'Can add Announcement', '14', 'add_announcement'), ('41', 'Can change Announcement', '14', 'change_announcement'), ('42', 'Can delete Announcement', '14', 'delete_announcement'), ('43', 'Can verify', '14', 'can_verify'), ('44', 'Can stick', '14', 'can_stick'), ('45', 'Can add profile', '15', 'add_userprofile'), ('46', 'Can change profile', '15', 'change_userprofile'), ('47', 'Can delete profile', '15', 'delete_userprofile'), ('48', 'Can add User\'s card', '16', 'add_usercard'), ('49', 'Can change User\'s card', '16', 'change_usercard'), ('50', 'Can delete User\'s card', '16', 'delete_usercard'), ('51', 'Can add Game', '17', 'add_game'), ('52', 'Can change Game', '17', 'change_game'), ('53', 'Can delete Game', '17', 'delete_game'), ('54', 'Can add Bank', '18', 'add_bank'), ('55', 'Can change Bank', '18', 'change_bank'), ('56', 'Can delete Bank', '18', 'delete_bank'), ('57', 'Can add Card', '19', 'add_card'), ('58', 'Can change Card', '19', 'change_card'), ('59', 'Can delete Card', '19', 'delete_card'), ('60', 'Can verify', '19', 'can_verify'), ('68', 'Can add log entry', '22', 'add_logentry'), ('69', 'Can change log entry', '22', 'change_logentry'), ('70', 'Can delete log entry', '22', 'delete_logentry'), ('71', 'Can add deposit method', '23', 'add_depositmethod'), ('72', 'Can change deposit method', '23', 'change_depositmethod'), ('73', 'Can delete deposit method', '23', 'delete_depositmethod'), ('74', 'Can add deposit method account', '24', 'add_depositmethodaccount'), ('75', 'Can change deposit method account', '24', 'change_depositmethodaccount'), ('76', 'Can delete deposit method account', '24', 'delete_depositmethodaccount'), ('77', 'Can verify', '24', 'can_verify'), ('78', 'Can add deposit log', '25', 'add_depositlog'), ('79', 'Can change deposit log', '25', 'change_depositlog'), ('80', 'Can delete deposit log', '25', 'delete_depositlog'), ('81', 'Can add cellphone', '26', 'add_cellphone'), ('82', 'Can change cellphone', '26', 'change_cellphone'), ('83', 'Can delete cellphone', '26', 'delete_cellphone'), ('84', 'Can verify', '26', 'can_verify'), ('85', 'Can add SMS log', '27', 'add_smslog'), ('86', 'Can change SMS log', '27', 'change_smslog'), ('87', 'Can delete SMS log', '27', 'delete_smslog'), ('88', 'Can add user account detail type', '28', 'add_useraccountdetailtype'), ('89', 'Can change user account detail type', '28', 'change_useraccountdetailtype'), ('90', 'Can delete user account detail type', '28', 'delete_useraccountdetailtype'), ('91', 'Can add user account detail', '29', 'add_useraccountdetail'), ('92', 'Can change user account detail', '29', 'change_useraccountdetail'), ('93', 'Can delete user account detail', '29', 'delete_useraccountdetail');

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
INSERT INTO `auth_user` VALUES ('1', 'root', 'Floyd', 'Joe', 'kirinse@gmail.com', 'sha1$228d8$dc78d2daa8f7b9c7cb8bd7ae3d3d7b3526d0f34a', '1', '1', '1', '2011-04-19 11:48:08', '2011-04-07 14:11:55');

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
  KEY `auth_user_groups_403f60f` (`user_id`),
  KEY `auth_user_groups_425ae3c4` (`group_id`),
  CONSTRAINT `group_id_refs_id_f116770` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `user_id_refs_id_7ceef80f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
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
  KEY `auth_user_user_permissions_403f60f` (`user_id`),
  KEY `auth_user_user_permissions_1e014c8f` (`permission_id`),
  CONSTRAINT `permission_id_refs_id_67e79cb` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `user_id_refs_id_dfbab7d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `axes_accessattempt`
-- ----------------------------
DROP TABLE IF EXISTS `axes_accessattempt`;
CREATE TABLE `axes_accessattempt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` char(15) NOT NULL,
  `get_data` longtext NOT NULL,
  `post_data` longtext NOT NULL,
  `http_accept` varchar(255) NOT NULL,
  `path_info` varchar(255) NOT NULL,
  `failures_since_start` int(10) unsigned NOT NULL,
  `attempt_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `bank`
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `logo` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `bank`
-- ----------------------------
INSERT INTO `bank` VALUES ('1', 'ICBC', '中国工商银行', 'images/bank/6.jpg'), ('2', 'CCB', '建设银行', 'images/bank/7.jpg');

-- ----------------------------
--  Table structure for `bank_cellphone`
-- ----------------------------
DROP TABLE IF EXISTS `bank_cellphone`;
CREATE TABLE `bank_cellphone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(11) NOT NULL,
  `sms_key` varchar(32) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  `verify_time` datetime DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `bank_cellphone_1e4ad39d` (`adder_id`),
  KEY `bank_cellphone_584122da` (`verifier_id`),
  CONSTRAINT `adder_id_refs_id_761d1e7b` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `verifier_id_refs_id_761d1e7b` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `bank_cellphone`
-- ----------------------------
INSERT INTO `bank_cellphone` VALUES ('1', '13800000000', 'suJVD5ncyoEMZuilzNVJrSXTnNtpBqq', '1', '2011-04-10 23:58:35', null, null, '1'), ('2', '13500000000', '', '1', '2011-04-11 10:32:45', null, null, '1'), ('3', '13000000000', '', '1', '2011-04-11 10:32:52', null, null, '1'), ('4', '13100000000', '', '1', '2011-04-11 10:33:05', null, null, '1');

-- ----------------------------
--  Table structure for `card`
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_no` varchar(87) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `alias` varchar(30) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `account_name` varchar(20) NOT NULL,
  `init_balance` decimal(14,4) NOT NULL,
  `login_pwd` varchar(30) NOT NULL,
  `transaction_pwd` varchar(30) NOT NULL,
  `country_id` varchar(6) NOT NULL,
  `province_id` int(11) NOT NULL,
  `discriminator` varchar(10) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verify_time` datetime DEFAULT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `card_1862eb86` (`bank_id`),
  KEY `card_534dd89` (`country_id`),
  KEY `card_37751324` (`province_id`),
  KEY `card_1e4ad39d` (`adder_id`),
  KEY `card_584122da` (`verifier_id`),
  CONSTRAINT `adder_id_refs_id_ea293a` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `bank_id_refs_id_7b7bf309` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`),
  CONSTRAINT `country_id_refs_iso_baadb0a` FOREIGN KEY (`country_id`) REFERENCES `country` (`iso`),
  CONSTRAINT `province_id_refs_id_3fa775db` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`),
  CONSTRAINT `verifier_id_refs_id_ea293a` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `channel`
-- ----------------------------
DROP TABLE IF EXISTS `channel`;
CREATE TABLE `channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `path` varchar(90) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `city`
-- ----------------------------
DROP TABLE IF EXISTS `city`;
CREATE TABLE `city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `province_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `city_37751324` (`province_id`),
  CONSTRAINT `province_id_refs_id_23f2453a` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `city`
-- ----------------------------
INSERT INTO `city` VALUES ('1', '石家庄市', '70'), ('2', '唐山市', '70'), ('3', '秦皇岛市', '70'), ('4', '邯郸市', '70'), ('5', '邢台市', '70'), ('6', '保定市', '70'), ('7', '张家口市', '70'), ('8', '承德市', '70'), ('9', '沧州市', '70'), ('10', '廊坊市', '70'), ('11', '衡水市', '70'), ('12', '太原市', '71'), ('13', '大同市', '71'), ('14', '阳泉市', '71'), ('15', '长治市', '71'), ('16', '晋城市', '71'), ('17', '朔州市', '71'), ('18', '晋中市', '71'), ('19', '运城市', '71'), ('20', '忻州市', '71'), ('21', '临汾市', '71'), ('22', '吕梁市', '71'), ('23', '沈阳市', '73'), ('24', '大连市', '73'), ('25', '鞍山市', '73'), ('26', '抚顺市', '73'), ('27', '本溪市', '73'), ('28', '丹东市', '73'), ('29', '锦州市', '73'), ('30', '营口市', '73'), ('31', '阜新市', '73'), ('32', '辽阳市', '73'), ('33', '盘锦市', '73'), ('34', '铁岭市', '73'), ('35', '朝阳市', '73'), ('36', '葫芦岛市', '73'), ('37', '长春市', '74'), ('38', '吉林市', '74'), ('39', '四平市', '74'), ('40', '辽源市', '74'), ('41', '通化市', '74'), ('42', '白山市', '74'), ('43', '松原市', '74'), ('44', '白城市', '74'), ('45', '延边朝鲜族自治州', '74'), ('46', '哈尔滨市', '75'), ('47', '齐齐哈尔市', '75'), ('48', '鹤岗市', '75'), ('49', '双鸭山市', '75'), ('50', '鸡市', '75'), ('51', '大庆市', '75'), ('52', '伊春市', '75'), ('53', '牡丹江市', '75'), ('54', '佳木斯市', '75'), ('55', '七台河市', '75'), ('56', '黑河市', '75'), ('57', '绥化市', '75'), ('58', '大兴安岭地区', '75'), ('59', '南京市', '77'), ('60', '无锡市', '77'), ('61', '徐州市', '77'), ('62', '常州市', '77'), ('63', '苏州市', '77'), ('64', '南通市', '77'), ('65', '连云港市', '77'), ('66', '淮安市', '77'), ('67', '盐城市', '77'), ('68', '扬州市', '77'), ('69', '镇江市', '77'), ('70', '泰州市', '77'), ('71', '宿迁市', '77'), ('72', '杭州市', '78'), ('73', '宁波市', '78'), ('74', '温州市', '78'), ('75', '嘉兴市', '78'), ('76', '湖州市', '78'), ('77', '绍兴市', '78'), ('78', '金华市', '78'), ('79', '衢州市', '78'), ('80', '舟山市', '78'), ('81', '台州市', '78'), ('82', '丽水市', '78'), ('83', '合肥市', '79'), ('84', '芜湖市', '79'), ('85', '蚌埠市', '79'), ('86', '淮南市', '79'), ('87', '马鞍山市', '79'), ('88', '淮北市', '79'), ('89', '铜陵市', '79'), ('90', '安庆市', '79'), ('91', '黄山市', '79'), ('92', '滁州市', '79'), ('93', '阜阳市', '79'), ('94', '宿州市', '79'), ('95', '巢湖市', '79'), ('96', '六安市', '79'), ('97', '亳州市', '79'), ('98', '池州市', '79'), ('99', '宣城市', '79'), ('100', '福州市', '80'), ('101', '厦门市', '80'), ('102', '莆田市', '80'), ('103', '三明市', '80'), ('104', '泉州市', '80'), ('105', '漳州市', '80'), ('106', '南平市', '80'), ('107', '龙岩市', '80'), ('108', '宁德市', '80'), ('109', '南昌市', '81'), ('110', '景德镇市', '81'), ('111', '萍乡市', '81'), ('112', '九江市', '81'), ('113', '新余市', '81'), ('114', '鹰潭市', '81'), ('115', '赣州市', '81'), ('116', '吉安市', '81'), ('117', '宜春市', '81'), ('118', '抚州市', '81'), ('119', '上饶市', '81'), ('120', '济南市', '82'), ('121', '青岛市', '82'), ('122', '淄博市', '82'), ('123', '枣庄市', '82'), ('124', '东营市', '82'), ('125', '烟台市', '82'), ('126', '潍坊市', '82'), ('127', '济宁市', '82'), ('128', '泰安市', '82'), ('129', '威海市', '82'), ('130', '日照市', '82'), ('131', '莱芜市', '82'), ('132', '临沂市', '82'), ('133', '德州市', '82'), ('134', '聊城市', '82'), ('135', '滨州市', '82'), ('136', '菏泽市', '82'), ('137', '郑州市', '83'), ('138', '开封市', '83'), ('139', '洛阳市', '83'), ('140', '平顶山市', '83'), ('141', '安阳市', '83'), ('142', '鹤壁市', '83'), ('143', '新乡市', '83'), ('144', '焦作市', '83'), ('145', '濮阳市', '83'), ('146', '许昌市', '83'), ('147', '漯河市', '83'), ('148', '三门峡市', '83'), ('149', '南阳市', '83'), ('150', '商丘市', '83'), ('151', '信阳市', '83'), ('152', '周口市', '83'), ('153', '驻马店市', '83'), ('154', '济源市', '83'), ('155', '武汉市', '84'), ('156', '黄石市', '84'), ('157', '十堰市', '84'), ('158', '荆州市', '84'), ('159', '宜昌市', '84'), ('160', '襄樊市', '84'), ('161', '鄂州市', '84'), ('162', '荆门市', '84'), ('163', '孝感市', '84'), ('164', '黄冈市', '84'), ('165', '咸宁市', '84'), ('166', '随州市', '84'), ('167', '仙桃市', '84'), ('168', '天门市', '84'), ('169', '潜江市', '84'), ('170', '神农架林区', '84'), ('171', '恩施土家族苗族自治州', '84'), ('172', '长沙市', '85'), ('173', '株洲市', '85'), ('174', '湘潭市', '85'), ('175', '衡阳市', '85'), ('176', '邵阳市', '85'), ('177', '岳阳市', '85'), ('178', '常德市', '85'), ('179', '张家界市', '85'), ('180', '益阳市', '85'), ('181', '郴州市', '85'), ('182', '永州市', '85'), ('183', '怀化市', '85'), ('184', '娄底市', '85'), ('185', '湘西土家族苗族自治州', '85'), ('186', '广州市', '86'), ('187', '深圳市', '86'), ('188', '珠海市', '86'), ('189', '汕头市', '86'), ('190', '韶关市', '86'), ('191', '佛山市', '86'), ('192', '江门市', '86'), ('193', '湛江市', '86'), ('194', '茂名市', '86'), ('195', '肇庆市', '86'), ('196', '惠州市', '86'), ('197', '梅州市', '86'), ('198', '汕尾市', '86'), ('199', '河源市', '86'), ('200', '阳江市', '86'), ('201', '清远市', '86'), ('202', '东莞市', '86'), ('203', '中山市', '86'), ('204', '潮州市', '86'), ('205', '揭阳市', '86'), ('206', '云浮市', '86'), ('207', '兰州市', '95'), ('208', '金昌市', '95'), ('209', '白银市', '95'), ('210', '天水市', '95'), ('211', '嘉峪关市', '95'), ('212', '武威市', '95'), ('213', '张掖市', '95'), ('214', '平凉市', '95'), ('215', '酒泉市', '95'), ('216', '庆阳市', '95'), ('217', '定西市', '95'), ('218', '陇南市', '95'), ('219', '临夏回族自治州', '95'), ('220', '甘南藏族自治州', '95'), ('221', '成都市', '90'), ('222', '自贡市', '90'), ('223', '攀枝花市', '90'), ('224', '泸州市', '90'), ('225', '德阳市', '90'), ('226', '绵阳市', '90'), ('227', '广元市', '90'), ('228', '遂宁市', '90'), ('229', '内江市', '90'), ('230', '乐山市', '90'), ('231', '南充市', '90'), ('232', '眉山市', '90'), ('233', '宜宾市', '90'), ('234', '广安市', '90'), ('235', '达州市', '90'), ('236', '雅安市', '90'), ('237', '巴中市', '90'), ('238', '资阳市', '90'), ('239', '阿坝藏族羌族自治州', '90'), ('240', '甘孜藏族自治州', '90'), ('241', '凉山彝族自治州', '90'), ('242', '贵阳市', '91'), ('243', '六盘水市', '91'), ('244', '遵义市', '91'), ('245', '安顺市', '91'), ('246', '铜仁地区', '91'), ('247', '毕节地区', '91'), ('248', '黔西南布依族苗族自治州', '91'), ('249', '黔东南苗族侗族自治州', '91'), ('250', '黔南布依族苗族自治州', '91'), ('251', '海口市', '88'), ('252', '三亚市', '88'), ('253', '五指山市', '88'), ('254', '琼海市', '88'), ('255', '儋州市', '88'), ('256', '文昌市', '88'), ('257', '万宁市', '88'), ('258', '东方市', '88'), ('259', '澄迈县', '88'), ('260', '定安县', '88'), ('261', '屯昌县', '88'), ('262', '临高县', '88'), ('263', '白沙黎族自治县', '88'), ('264', '昌江黎族自治县', '88'), ('265', '乐东黎族自治县', '88'), ('266', '陵水黎族自治县', '88'), ('267', '保亭黎族苗族自治县', '88'), ('268', '琼中黎族苗族自治县', '88'), ('269', '昆明市', '92'), ('270', '曲靖市', '92'), ('271', '玉溪市', '92'), ('272', '保山市', '92'), ('273', '昭通市', '92'), ('274', '丽江市', '92'), ('275', '思茅市', '92'), ('276', '临沧市', '92'), ('277', '文山壮族苗族自治州', '92'), ('278', '红河哈尼族彝族自治州', '92'), ('279', '西双版纳傣族自治州', '92'), ('280', '楚雄彝族自治州', '92'), ('281', '大理白族自治州', '92'), ('282', '德宏傣族景颇族自治州', '92'), ('283', '怒江傈傈族自治州', '92'), ('284', '迪庆藏族自治州', '92'), ('285', '西宁市', '96'), ('286', '海东地区', '96'), ('287', '海北藏族自治州', '96'), ('288', '黄南藏族自治州', '96'), ('289', '海南藏族自治州', '96'), ('290', '果洛藏族自治州', '96'), ('291', '玉树藏族自治州', '96'), ('292', '海西蒙古族藏族自治州', '96'), ('293', '西安市', '94'), ('294', '铜川市', '94'), ('295', '宝鸡市', '94'), ('296', '咸阳市', '94'), ('297', '渭南市', '94'), ('298', '延安市', '94'), ('299', '汉中市', '94'), ('300', '榆林市', '94'), ('301', '安康市', '94'), ('302', '商洛市', '94'), ('303', '南宁市', '87'), ('304', '柳州市', '87'), ('305', '桂林市', '87'), ('306', '梧州市', '87'), ('307', '北海市', '87'), ('308', '防城港市', '87'), ('309', '钦州市', '87'), ('310', '贵港市', '87'), ('311', '玉林市', '87'), ('312', '百色市', '87'), ('313', '贺州市', '87'), ('314', '河池市', '87'), ('315', '来宾市', '87'), ('316', '崇左市', '87'), ('317', '拉萨市', '93'), ('318', '那曲地区', '93'), ('319', '昌都地区', '93'), ('320', '山南地区', '93'), ('321', '日喀则地区', '93'), ('322', '阿里地区', '93'), ('323', '林芝地区', '93'), ('324', '银川市', '97'), ('325', '石嘴山市', '97'), ('326', '吴忠市', '97'), ('327', '固原市', '97'), ('328', '中卫市', '97'), ('329', '乌鲁木齐市', '98'), ('330', '克拉玛依市', '98'), ('331', '石河子市　', '98'), ('332', '阿拉尔市', '98'), ('333', '图木舒克市', '98'), ('334', '五家渠市', '98'), ('335', '吐鲁番市', '98'), ('336', '阿克苏市', '98'), ('337', '喀什市', '98'), ('338', '哈密市', '98'), ('339', '和田市', '98'), ('340', '阿图什市', '98'), ('341', '库尔勒市', '98'), ('342', '昌吉市　', '98'), ('343', '阜康市', '98'), ('344', '米泉市', '98'), ('345', '博乐市', '98'), ('346', '伊宁市', '98'), ('347', '奎屯市', '98'), ('348', '塔城市', '98'), ('349', '乌苏市', '98'), ('350', '阿勒泰市', '98'), ('351', '呼和浩特市', '72'), ('352', '包头市', '72'), ('353', '乌海市', '72'), ('354', '赤峰市', '72'), ('355', '通辽市', '72'), ('356', '鄂尔多斯市', '72'), ('357', '呼伦贝尔市', '72'), ('358', '巴彦淖尔市', '72'), ('359', '乌兰察布市', '72'), ('360', '锡林郭勒盟', '72'), ('361', '兴安盟', '72'), ('362', '阿拉善盟', '72'), ('363', '中西', '99'), ('364', '东区', '99'), ('365', '九龙城', '99'), ('366', '观塘', '99'), ('367', '南区', '99'), ('368', '深水埗', '99'), ('369', '黄大仙', '99'), ('370', '湾仔', '99'), ('371', '油尖旺', '99'), ('372', '离岛', '99'), ('373', '葵青', '99'), ('374', '北区', '99'), ('375', '西贡', '99'), ('376', '沙田', '99'), ('377', '屯门', '99'), ('378', '大埔', '99'), ('379', '荃湾', '99'), ('380', '元朗', '99'), ('381', '花地玛堂区', '100'), ('382', '圣安多尼堂区', '100'), ('383', '大堂区', '100'), ('384', '望德堂区', '100'), ('385', '风顺堂区', '100'), ('386', '黄浦区', '76'), ('387', '卢湾区', '76'), ('388', '徐汇区', '76'), ('389', '长宁区', '76'), ('390', '静安区', '76'), ('391', '普陀区', '76'), ('392', '闸北区', '76'), ('393', '虹口区', '76'), ('394', '杨浦区', '76'), ('395', '宝山区', '76'), ('396', '闵行区', '76'), ('397', '嘉定区', '76'), ('398', '浦东新区', '76'), ('399', '松江区', '76'), ('400', '金山区', '76'), ('401', '青浦区', '76'), ('402', '奉贤区', '76'), ('403', '崇明县', '76'), ('404', '东城区', '3'), ('405', '西城区', '3'), ('406', '崇文区', '3'), ('407', '宣武区', '3'), ('408', '朝阳区', '3'), ('409', '丰台区', '3'), ('410', '石景山区', '3'), ('411', '海淀区', '3'), ('412', '门头沟区', '3'), ('413', '房山区', '3'), ('414', '通州区', '3'), ('415', '顺义区', '3'), ('416', '昌平区', '3'), ('417', '大兴区', '3'), ('418', '怀柔区', '3'), ('419', '平谷区', '3'), ('420', '延庆县', '3'), ('421', '密云县 ', '3'), ('422', '和平区', '69'), ('423', '河东区', '69'), ('424', '河西区', '69'), ('425', '南开区', '69'), ('426', '河北区', '69'), ('427', '红桥区', '69'), ('428', '塘沽区', '69'), ('429', '汉沽区', '69'), ('430', '大港区', '69'), ('431', '东丽区', '69'), ('432', '西青区', '69'), ('433', '津南区', '69'), ('434', '北辰区', '69'), ('435', '武清区', '69'), ('436', '宝坻区', '69'), ('437', '蓟　县', '69'), ('438', '宁河县', '69'), ('439', '静海县', '69'), ('440', '渝中区', '89'), ('441', '大渡口区', '89'), ('442', '江北区', '89'), ('443', '沙坪坝区', '89'), ('444', '九龙坡区', '89'), ('445', '南岸区', '89'), ('446', '北碚区', '89'), ('447', '万盛区', '89'), ('448', '双桥区', '89'), ('449', '渝北区', '89'), ('450', '巴南区', '89'), ('451', '万州区', '89'), ('452', '涪陵区', '89'), ('453', '黔江区', '89'), ('454', '长寿区', '89'), ('455', '江津区', '89'), ('456', '永川区', '89'), ('457', '合川区', '89'), ('458', '南川区', '89');

-- ----------------------------
--  Table structure for `country`
-- ----------------------------
DROP TABLE IF EXISTS `country`;
CREATE TABLE `country` (
  `iso` varchar(6) NOT NULL,
  `name` varchar(240) NOT NULL,
  `printable_name` varchar(240) NOT NULL,
  `cn_name` varchar(240) NOT NULL,
  `iso3` varchar(9) DEFAULT NULL,
  `numcode` int(11) DEFAULT NULL,
  PRIMARY KEY (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `country`
-- ----------------------------
INSERT INTO `country` VALUES ('AD', 'ANDORRA', 'Andorra', '', 'AND', '20'), ('AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', '', 'ARE', '784'), ('AF', 'AFGHANISTAN', 'Afghanistan', '', 'AFG', '4'), ('AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', '', 'ATG', '28'), ('AI', 'ANGUILLA', 'Anguilla', '', 'AIA', '660'), ('AL', 'ALBANIA', 'Albania', '', 'ALB', '8'), ('AM', 'ARMENIA', 'Armenia', '', 'ARM', '51'), ('AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', '', 'ANT', '530'), ('AO', 'ANGOLA', 'Angola', '', 'AGO', '24'), ('AQ', 'ANTARCTICA', 'Antarctica', '', null, null), ('AR', 'ARGENTINA', 'Argentina', '', 'ARG', '32'), ('AS', 'AMERICAN SAMOA', 'American Samoa', '', 'ASM', '16'), ('AT', 'AUSTRIA', 'Austria', '', 'AUT', '40'), ('AU', 'AUSTRALIA', 'Australia', '', 'AUS', '36'), ('AW', 'ARUBA', 'Aruba', '', 'ABW', '533'), ('AZ', 'AZERBAIJAN', 'Azerbaijan', '', 'AZE', '31'), ('BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', '', 'BIH', '70'), ('BB', 'BARBADOS', 'Barbados', '', 'BRB', '52'), ('BD', 'BANGLADESH', 'Bangladesh', '', 'BGD', '50'), ('BE', 'BELGIUM', 'Belgium', '', 'BEL', '56'), ('BF', 'BURKINA FASO', 'Burkina Faso', '', 'BFA', '854'), ('BG', 'BULGARIA', 'Bulgaria', '', 'BGR', '100'), ('BH', 'BAHRAIN', 'Bahrain', '', 'BHR', '48'), ('BI', 'BURUNDI', 'Burundi', '', 'BDI', '108'), ('BJ', 'BENIN', 'Benin', '', 'BEN', '204'), ('BM', 'BERMUDA', 'Bermuda', '', 'BMU', '60'), ('BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', '', 'BRN', '96'), ('BO', 'BOLIVIA', 'Bolivia', '', 'BOL', '68'), ('BR', 'BRAZIL', 'Brazil', '', 'BRA', '76'), ('BS', 'BAHAMAS', 'Bahamas', '', 'BHS', '44'), ('BT', 'BHUTAN', 'Bhutan', '', 'BTN', '64'), ('BV', 'BOUVET ISLAND', 'Bouvet Island', '', null, null), ('BW', 'BOTSWANA', 'Botswana', '', 'BWA', '72'), ('BY', 'BELARUS', 'Belarus', '', 'BLR', '112'), ('BZ', 'BELIZE', 'Belize', '', 'BLZ', '84'), ('CA', 'CANADA', 'Canada', '', 'CAN', '124'), ('CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', '', null, null), ('CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', '', 'COD', '180'), ('CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', '', 'CAF', '140'), ('CG', 'CONGO', 'Congo', '', 'COG', '178'), ('CH', 'SWITZERLAND', 'Switzerland', '', 'CHE', '756'), ('CI', 'COTE D\'IVOIRE', 'Cote D\'Ivoire', '', 'CIV', '384'), ('CK', 'COOK ISLANDS', 'Cook Islands', '', 'COK', '184'), ('CL', 'CHILE', 'Chile', '', 'CHL', '152'), ('CM', 'CAMEROON', 'Cameroon', '', 'CMR', '120'), ('CN', 'CHINA', 'China', '', 'CHN', '156'), ('CO', 'COLOMBIA', 'Colombia', '', 'COL', '170'), ('CR', 'COSTA RICA', 'Costa Rica', '', 'CRI', '188'), ('CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', '', null, null), ('CU', 'CUBA', 'Cuba', '', 'CUB', '192'), ('CV', 'CAPE VERDE', 'Cape Verde', '', 'CPV', '132'), ('CX', 'CHRISTMAS ISLAND', 'Christmas Island', '', null, null), ('CY', 'CYPRUS', 'Cyprus', '', 'CYP', '196'), ('CZ', 'CZECH REPUBLIC', 'Czech Republic', '', 'CZE', '203'), ('DE', 'GERMANY', 'Germany', '', 'DEU', '276'), ('DJ', 'DJIBOUTI', 'Djibouti', '', 'DJI', '262'), ('DK', 'DENMARK', 'Denmark', '', 'DNK', '208'), ('DM', 'DOMINICA', 'Dominica', '', 'DMA', '212'), ('DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', '', 'DOM', '214'), ('DZ', 'ALGERIA', 'Algeria', '', 'DZA', '12'), ('EC', 'ECUADOR', 'Ecuador', '', 'ECU', '218'), ('EE', 'ESTONIA', 'Estonia', '', 'EST', '233'), ('EG', 'EGYPT', 'Egypt', '', 'EGY', '818'), ('EH', 'WESTERN SAHARA', 'Western Sahara', '', 'ESH', '732'), ('ER', 'ERITREA', 'Eritrea', '', 'ERI', '232'), ('ES', 'SPAIN', 'Spain', '', 'ESP', '724'), ('ET', 'ETHIOPIA', 'Ethiopia', '', 'ETH', '231'), ('FI', 'FINLAND', 'Finland', '', 'FIN', '246'), ('FJ', 'FIJI', 'Fiji', '', 'FJI', '242'), ('FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', '', 'FLK', '238'), ('FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', '', 'FSM', '583'), ('FO', 'FAROE ISLANDS', 'Faroe Islands', '', 'FRO', '234'), ('FR', 'FRANCE', 'France', '', 'FRA', '250'), ('GA', 'GABON', 'Gabon', '', 'GAB', '266'), ('GB', 'UNITED KINGDOM', 'United Kingdom', '', 'GBR', '826'), ('GD', 'GRENADA', 'Grenada', '', 'GRD', '308'), ('GE', 'GEORGIA', 'Georgia', '', 'GEO', '268'), ('GF', 'FRENCH GUIANA', 'French Guiana', '', 'GUF', '254'), ('GH', 'GHANA', 'Ghana', '', 'GHA', '288'), ('GI', 'GIBRALTAR', 'Gibraltar', '', 'GIB', '292'), ('GL', 'GREENLAND', 'Greenland', '', 'GRL', '304'), ('GM', 'GAMBIA', 'Gambia', '', 'GMB', '270'), ('GN', 'GUINEA', 'Guinea', '', 'GIN', '324'), ('GP', 'GUADELOUPE', 'Guadeloupe', '', 'GLP', '312'), ('GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', '', 'GNQ', '226'), ('GR', 'GREECE', 'Greece', '', 'GRC', '300'), ('GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', '', null, null), ('GT', 'GUATEMALA', 'Guatemala', '', 'GTM', '320'), ('GU', 'GUAM', 'Guam', '', 'GUM', '316'), ('GW', 'GUINEA-BISSAU', 'Guinea-Bissau', '', 'GNB', '624'), ('GY', 'GUYANA', 'Guyana', '', 'GUY', '328'), ('HK', 'HONG KONG', 'Hong Kong', '', 'HKG', '344'), ('HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', '', null, null), ('HN', 'HONDURAS', 'Honduras', '', 'HND', '340'), ('HR', 'CROATIA', 'Croatia', '', 'HRV', '191'), ('HT', 'HAITI', 'Haiti', '', 'HTI', '332'), ('HU', 'HUNGARY', 'Hungary', '', 'HUN', '348'), ('ID', 'INDONESIA', 'Indonesia', '', 'IDN', '360'), ('IE', 'IRELAND', 'Ireland', '', 'IRL', '372'), ('IL', 'ISRAEL', 'Israel', '', 'ISR', '376'), ('IN', 'INDIA', 'India', '', 'IND', '356'), ('IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', '', null, null), ('IQ', 'IRAQ', 'Iraq', '', 'IRQ', '368'), ('IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', '', 'IRN', '364'), ('IS', 'ICELAND', 'Iceland', '', 'ISL', '352'), ('IT', 'ITALY', 'Italy', '', 'ITA', '380'), ('JM', 'JAMAICA', 'Jamaica', '', 'JAM', '388'), ('JO', 'JORDAN', 'Jordan', '', 'JOR', '400'), ('JP', 'JAPAN', 'Japan', '', 'JPN', '392'), ('KE', 'KENYA', 'Kenya', '', 'KEN', '404'), ('KG', 'KYRGYZSTAN', 'Kyrgyzstan', '', 'KGZ', '417'), ('KH', 'CAMBODIA', 'Cambodia', '', 'KHM', '116'), ('KI', 'KIRIBATI', 'Kiribati', '', 'KIR', '296'), ('KM', 'COMOROS', 'Comoros', '', 'COM', '174'), ('KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', '', 'KNA', '659'), ('KP', 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF', 'Korea, Democratic People\'s Republic of', '', 'PRK', '408'), ('KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', '', 'KOR', '410'), ('KW', 'KUWAIT', 'Kuwait', '', 'KWT', '414'), ('KY', 'CAYMAN ISLANDS', 'Cayman Islands', '', 'CYM', '136'), ('KZ', 'KAZAKHSTAN', 'Kazakhstan', '', 'KAZ', '398'), ('LA', 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'Lao People\'s Democratic Republic', '', 'LAO', '418'), ('LB', 'LEBANON', 'Lebanon', '', 'LBN', '422'), ('LC', 'SAINT LUCIA', 'Saint Lucia', '', 'LCA', '662'), ('LI', 'LIECHTENSTEIN', 'Liechtenstein', '', 'LIE', '438'), ('LK', 'SRI LANKA', 'Sri Lanka', '', 'LKA', '144'), ('LR', 'LIBERIA', 'Liberia', '', 'LBR', '430'), ('LS', 'LESOTHO', 'Lesotho', '', 'LSO', '426'), ('LT', 'LITHUANIA', 'Lithuania', '', 'LTU', '440'), ('LU', 'LUXEMBOURG', 'Luxembourg', '', 'LUX', '442'), ('LV', 'LATVIA', 'Latvia', '', 'LVA', '428'), ('LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', '', 'LBY', '434'), ('MA', 'MOROCCO', 'Morocco', '', 'MAR', '504'), ('MC', 'MONACO', 'Monaco', '', 'MCO', '492'), ('MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', '', 'MDA', '498'), ('MG', 'MADAGASCAR', 'Madagascar', '', 'MDG', '450'), ('MH', 'MARSHALL ISLANDS', 'Marshall Islands', '', 'MHL', '584'), ('MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', '', 'MKD', '807'), ('ML', 'MALI', 'Mali', '', 'MLI', '466'), ('MM', 'MYANMAR', 'Myanmar', '', 'MMR', '104'), ('MN', 'MONGOLIA', 'Mongolia', '', 'MNG', '496'), ('MO', 'MACAO', 'Macao', '', 'MAC', '446'), ('MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', '', 'MNP', '580'), ('MQ', 'MARTINIQUE', 'Martinique', '', 'MTQ', '474'), ('MR', 'MAURITANIA', 'Mauritania', '', 'MRT', '478'), ('MS', 'MONTSERRAT', 'Montserrat', '', 'MSR', '500'), ('MT', 'MALTA', 'Malta', '', 'MLT', '470'), ('MU', 'MAURITIUS', 'Mauritius', '', 'MUS', '480'), ('MV', 'MALDIVES', 'Maldives', '', 'MDV', '462'), ('MW', 'MALAWI', 'Malawi', '', 'MWI', '454'), ('MX', 'MEXICO', 'Mexico', '', 'MEX', '484'), ('MY', 'MALAYSIA', 'Malaysia', '', 'MYS', '458'), ('MZ', 'MOZAMBIQUE', 'Mozambique', '', 'MOZ', '508'), ('NA', 'NAMIBIA', 'Namibia', '', 'NAM', '516'), ('NC', 'NEW CALEDONIA', 'New Caledonia', '', 'NCL', '540'), ('NE', 'NIGER', 'Niger', '', 'NER', '562'), ('NF', 'NORFOLK ISLAND', 'Norfolk Island', '', 'NFK', '574'), ('NG', 'NIGERIA', 'Nigeria', '', 'NGA', '566'), ('NI', 'NICARAGUA', 'Nicaragua', '', 'NIC', '558'), ('NL', 'NETHERLANDS', 'Netherlands', '', 'NLD', '528'), ('NO', 'NORWAY', 'Norway', '', 'NOR', '578'), ('NP', 'NEPAL', 'Nepal', '', 'NPL', '524'), ('NR', 'NAURU', 'Nauru', '', 'NRU', '520'), ('NU', 'NIUE', 'Niue', '', 'NIU', '570'), ('NZ', 'NEW ZEALAND', 'New Zealand', '', 'NZL', '554'), ('OM', 'OMAN', 'Oman', '', 'OMN', '512'), ('PA', 'PANAMA', 'Panama', '', 'PAN', '591'), ('PE', 'PERU', 'Peru', '', 'PER', '604'), ('PF', 'FRENCH POLYNESIA', 'French Polynesia', '', 'PYF', '258'), ('PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', '', 'PNG', '598'), ('PH', 'PHILIPPINES', 'Philippines', '', 'PHL', '608'), ('PK', 'PAKISTAN', 'Pakistan', '', 'PAK', '586'), ('PL', 'POLAND', 'Poland', '', 'POL', '616'), ('PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', '', 'SPM', '666'), ('PN', 'PITCAIRN', 'Pitcairn', '', 'PCN', '612'), ('PR', 'PUERTO RICO', 'Puerto Rico', '', 'PRI', '630'), ('PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', '', null, null), ('PT', 'PORTUGAL', 'Portugal', '', 'PRT', '620'), ('PW', 'PALAU', 'Palau', '', 'PLW', '585'), ('PY', 'PARAGUAY', 'Paraguay', '', 'PRY', '600'), ('QA', 'QATAR', 'Qatar', '', 'QAT', '634'), ('RE', 'REUNION', 'Reunion', '', 'REU', '638'), ('RO', 'ROMANIA', 'Romania', '', 'ROM', '642'), ('RU', 'RUSSIAN FEDERATION', 'Russian Federation', '', 'RUS', '643'), ('RW', 'RWANDA', 'Rwanda', '', 'RWA', '646'), ('SA', 'SAUDI ARABIA', 'Saudi Arabia', '', 'SAU', '682'), ('SB', 'SOLOMON ISLANDS', 'Solomon Islands', '', 'SLB', '90'), ('SC', 'SEYCHELLES', 'Seychelles', '', 'SYC', '690'), ('SD', 'SUDAN', 'Sudan', '', 'SDN', '736'), ('SE', 'SWEDEN', 'Sweden', '', 'SWE', '752'), ('SG', 'SINGAPORE', 'Singapore', '', 'SGP', '702'), ('SH', 'SAINT HELENA', 'Saint Helena', '', 'SHN', '654'), ('SI', 'SLOVENIA', 'Slovenia', '', 'SVN', '705'), ('SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', '', 'SJM', '744'), ('SK', 'SLOVAKIA', 'Slovakia', '', 'SVK', '703'), ('SL', 'SIERRA LEONE', 'Sierra Leone', '', 'SLE', '694'), ('SM', 'SAN MARINO', 'San Marino', '', 'SMR', '674'), ('SN', 'SENEGAL', 'Senegal', '', 'SEN', '686'), ('SO', 'SOMALIA', 'Somalia', '', 'SOM', '706'), ('SR', 'SURINAME', 'Suriname', '', 'SUR', '740'), ('ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', '', 'STP', '678'), ('SV', 'EL SALVADOR', 'El Salvador', '', 'SLV', '222'), ('SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', '', 'SYR', '760'), ('SZ', 'SWAZILAND', 'Swaziland', '', 'SWZ', '748'), ('TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', '', 'TCA', '796'), ('TD', 'CHAD', 'Chad', '', 'TCD', '148'), ('TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', '', null, null), ('TG', 'TOGO', 'Togo', '', 'TGO', '768'), ('TH', 'THAILAND', 'Thailand', '', 'THA', '764'), ('TJ', 'TAJIKISTAN', 'Tajikistan', '', 'TJK', '762'), ('TK', 'TOKELAU', 'Tokelau', '', 'TKL', '772'), ('TL', 'TIMOR-LESTE', 'Timor-Leste', '', null, null), ('TM', 'TURKMENISTAN', 'Turkmenistan', '', 'TKM', '795'), ('TN', 'TUNISIA', 'Tunisia', '', 'TUN', '788'), ('TO', 'TONGA', 'Tonga', '', 'TON', '776'), ('TR', 'TURKEY', 'Turkey', '', 'TUR', '792'), ('TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', '', 'TTO', '780'), ('TV', 'TUVALU', 'Tuvalu', '', 'TUV', '798'), ('TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', '', 'TWN', '158'), ('TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', '', 'TZA', '834'), ('UA', 'UKRAINE', 'Ukraine', '', 'UKR', '804'), ('UG', 'UGANDA', 'Uganda', '', 'UGA', '800'), ('UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', '', null, null), ('US', 'UNITED STATES', 'United States', '', 'USA', '840'), ('UY', 'URUGUAY', 'Uruguay', '', 'URY', '858'), ('UZ', 'UZBEKISTAN', 'Uzbekistan', '', 'UZB', '860'), ('VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', '', 'VAT', '336'), ('VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', '', 'VCT', '670'), ('VE', 'VENEZUELA', 'Venezuela', '', 'VEN', '862'), ('VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', '', 'VGB', '92'), ('VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', '', 'VIR', '850'), ('VN', 'VIET NAM', 'Viet Nam', '', 'VNM', '704'), ('VU', 'VANUATU', 'Vanuatu', '', 'VUT', '548'), ('WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', '', 'WLF', '876'), ('WS', 'SAMOA', 'Samoa', '', 'WSM', '882'), ('YE', 'YEMEN', 'Yemen', '', 'YEM', '887'), ('YT', 'MAYOTTE', 'Mayotte', '', null, null), ('ZA', 'SOUTH AFRICA', 'South Africa', '', 'ZAF', '710'), ('ZM', 'ZAMBIA', 'Zambia', '', 'ZMB', '894'), ('ZW', 'ZIMBABWE', 'Zimbabwe', '', 'ZWE', '716');

-- ----------------------------
--  Table structure for `deposit_log`
-- ----------------------------
DROP TABLE IF EXISTS `deposit_log`;
CREATE TABLE `deposit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(15) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deposit_method_id` int(11) NOT NULL,
  `deposit_method_account_id` int(11) NOT NULL,
  `deposit_method_account_login_name` varchar(100) NOT NULL,
  `deposit_method_account_account_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `amount` decimal(14,4) NOT NULL,
  `status` smallint(6) NOT NULL,
  `cellphone` varchar(11) NOT NULL,
  `deposit_time` datetime NOT NULL,
  `receive_log_id` int(11) DEFAULT NULL,
  `receive_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `deposit_log_403f60f` (`user_id`),
  KEY `deposit_log_4069c848` (`deposit_method_id`),
  KEY `deposit_log_2092e3b0` (`deposit_method_account_id`),
  KEY `deposit_log_2bbf61fa` (`cellphone`),
  KEY `deposit_log_48439761` (`receive_log_id`),
  CONSTRAINT `cellphone_refs_number_b011b42` FOREIGN KEY (`cellphone`) REFERENCES `bank_cellphone` (`number`),
  CONSTRAINT `deposit_method_account_id_refs_id_7f5b94e9` FOREIGN KEY (`deposit_method_account_id`) REFERENCES `deposit_method_account` (`id`),
  CONSTRAINT `deposit_method_id_refs_id_17f3dfdf` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method` (`id`),
  CONSTRAINT `receive_log_id_refs_id_4c9606df` FOREIGN KEY (`receive_log_id`) REFERENCES `deposit_sms_log` (`id`),
  CONSTRAINT `user_id_refs_id_5900f35a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_log`
-- ----------------------------
INSERT INTO `deposit_log` VALUES ('1', '110420191040989', '1', '1', '1', '9558801000000000000', 'Floyd', 'c-mtv@163.com', '99.0000', '0', '13800000000', '2011-04-20 20:21:53', null, null);

-- ----------------------------
--  Table structure for `deposit_method`
-- ----------------------------
DROP TABLE IF EXISTS `deposit_method`;
CREATE TABLE `deposit_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `alias` varchar(10) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `discriminator` varchar(10) NOT NULL,
  `note` varchar(255) NOT NULL,
  `instruction` longtext NOT NULL,
  `status` smallint(6) NOT NULL,
  `url` varchar(200) NOT NULL,
  `logo` varchar(100) NOT NULL,
  `min_deposit` decimal(14,4) NOT NULL,
  `max_deposit` decimal(14,4) NOT NULL,
  `regex` tinytext NOT NULL,
  `notice_number` varchar(15) NOT NULL,
  `support_ps` tinyint(1) NOT NULL DEFAULT '1',
  `api_key` char(32) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_method_1e4ad39d` (`adder_id`),
  CONSTRAINT `adder_id_refs_id_d058860` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_method`
-- ----------------------------
INSERT INTO `deposit_method` VALUES ('1', '中国工商银行', 'icbc', 'CNY', 'netbank', 'careful', '    <table>\r\n	<caption>\r\n		<p class=\"fn-right bank-tip\">工商银行客服热线：95588</p>\r\n		<p class=\"bank-tip\">请关注您的充值金额是否超限</p>\r\n	</caption>\r\n	<thead>\r\n		<tr>\r\n			<th>银行卡种类</th>\r\n			<th>单笔限额(元)</th>\r\n			<th>每日限额(元)	</th>\r\n			<th>需要满足的条件	</th>\r\n			<th width=\"100px\">备注</th>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td rowspan=\"3\">储蓄卡</td>\r\n			<td>500</td>\r\n			<td>1000</td>\r\n			<td>办理电子银行口令卡(无需开通短信认证)    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=212183#2\">如何办理？</a></td>\r\n			<td rowspan=\"6\" width=\"100px\">1.如果您在银行设置的网上支付额度低于左表限额，以您的设置为准。 <br />2.存量静态密码客户的总累计限额为300元</td>\r\n		</tr>\r\n		<tr>\r\n			\r\n			<td>2000</td>\r\n			<td>5000</td>\r\n			<td>办理电子银行口令卡，开通短信认证    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=212183#2\">如何办理？</a></td>\r\n		</tr>\r\n		<tr>\r\n			\r\n			<td>100万</td>\r\n			<td>100万</td>\r\n			<td>办理U盾    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=211542#3\">如何办理？</a></td>\r\n		</tr>\r\n		<tr>\r\n			<td rowspan=\"3\">信用卡</td>\r\n			<td>500</td>\r\n			<td>1000</td>\r\n			<td>办理电子银行口令卡(无需开通短信认证)    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=212183#2\">如何办理？</a></td>\r\n		</tr>\r\n		<tr>\r\n			\r\n			<td>1000</td>\r\n			<td>5000</td>\r\n			<td>办理电子银行口令卡，开通短信认证    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=212183#2\">如何办理？</a></td>\r\n		</tr>\r\n		<tr>\r\n			\r\n			<td>1000</td>\r\n			<td>信用卡本身透支额度</td>\r\n			<td>办理U盾    <a target=\"_blank\" href=\"http://help.alipay.com/lab/help_detail.htm?help_id=211542#3\">如何办理？</a></td>\r\n		</tr>\r\n	</tbody>\r\n</table>', '1', 'https://mybank.icbc.com.cn/icbc/perbank/index.jsp', 'images/payment/6.jpg', '10.0000', '10000.0000', '(?P<deposit_name>\\D+)\\D{2}\\d{1,2}\\D{1}\\d{1,2}\\D{5}(?P<card_tail>\\d{4})\\D{7}(?P<amount>.*)\\D{2}<\\D+(?P<order_number>\\d*)>\\S+', '95588', '1', 'fn42nuQo9BmWBgseIQrEO5BoQuPch276', '1', '2011-04-07 14:20:40'), ('2', '建设银行', 'ccb', 'CNY', 'netbank', 'careful', 'img_logo.allow_tags=True', '1', 'https://ibsbjstar.ccb.com.cn/app/V5/CN/STY1/login.jsp', 'images/payment/7.jpg', '100.0000', '40000.0000', '^\\D{3}(?P<account_name>\\D+)\\D{2}\'+u\'\\uff1a\'+\'\\D{3}(?P<deposit_name>\\D+)\\D{8}(?P<card_tail>\\d{4})\\D{8}(?P<amount>\\S+)\\D{12}\\:(?P<order_number>\\d+)\\[\\D+\\]\\D+$', '95533', '1', 'NcElTeV1W5g7KCx3BMSIp2htNE9sjk1R', '1', '2011-04-07 14:31:02'), ('3', '支付宝', 'alipay', 'CNY', 'thirdpart', 'careful', 'discriminator', '1', 'http://www.alipay.com/', 'images/payment/alipay.jpg', '10.0000', '5000.0000', '', '', '1', '', '1', '2011-04-07 15:26:39');

-- ----------------------------
--  Table structure for `deposit_method_account`
-- ----------------------------
DROP TABLE IF EXISTS `deposit_method_account`;
CREATE TABLE `deposit_method_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_name` varchar(100) NOT NULL,
  `deposit_method_id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `login_password` varchar(40) NOT NULL,
  `transaction_password` varchar(40) NOT NULL,
  `account_name` varchar(40) NOT NULL,
  `init_balance` decimal(14,4) NOT NULL,
  `cellphone` varchar(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  `verify_time` datetime DEFAULT NULL,
  `pid` varchar(30) DEFAULT NULL,
  `key` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_method_account_1123be70` (`deposit_method_id`),
  KEY `payment_method_account_1e4ad39d` (`adder_id`),
  KEY `payment_method_account_584122da` (`verifier_id`),
  KEY `cellphone` (`cellphone`),
  CONSTRAINT `adder_id_refs_id_2a2aee58` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `deposit_method_account_ibfk_1` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method` (`id`),
  CONSTRAINT `deposit_method_account_ibfk_2` FOREIGN KEY (`cellphone`) REFERENCES `bank_cellphone` (`number`),
  CONSTRAINT `verifier_id_refs_id_2a2aee58` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_method_account`
-- ----------------------------
INSERT INTO `deposit_method_account` VALUES ('1', '9558801000000000000', '1', 'c-mtv@163.com', '123123', '123123', 'Floyd', '0.0000', '13800000000', '1', '1', '2011-04-07 14:27:03', null, null, '', ''), ('2', '9558801000000000001', '2', '', '123123', '123123', 'floyd', '0.0000', '13000000000', '1', '1', '2011-04-07 14:32:12', null, null, '', ''), ('3', '562838@qq.com', '3', '', '123123', '123123', 'Floyd', '0.0000', '13500000000', '1', '1', '2011-04-07 15:27:39', null, null, '', '');

-- ----------------------------
--  Table structure for `deposit_sms_log`
-- ----------------------------
DROP TABLE IF EXISTS `deposit_sms_log`;
CREATE TABLE `deposit_sms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(20) NOT NULL,
  `receive_number` varchar(11) NOT NULL,
  `content` varchar(500) NOT NULL,
  `receive_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deposit_sms_log_732f0dee` (`sender`),
  KEY `deposit_sms_log_2afbfa10` (`receive_number`),
  CONSTRAINT `receive_number_refs_number_68f6b0e0` FOREIGN KEY (`receive_number`) REFERENCES `bank_cellphone` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_sms_log`
-- ----------------------------
INSERT INTO `deposit_sms_log` VALUES ('1', '95588', '13800000000', 'sdhkjfskjfkjkdsfkjsdjkfljksd', '2011-04-22 00:31:13');

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
  KEY `django_admin_log_403f60f` (`user_id`),
  KEY `django_admin_log_1bb8f392` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_288599e6` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `user_id_refs_id_c8665aa` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_admin_log`
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2011-04-07 14:16:50', '1', '18', '1', '中国工商银行', '1', ''), ('2', '2011-04-07 14:17:03', '1', '18', '2', '建设银行', '1', ''), ('3', '2011-04-10 23:58:35', '1', '26', '1', '13800000000', '1', ''), ('4', '2011-04-11 10:28:31', '1', '25', '1', '110411102759888', '1', ''), ('5', '2011-04-11 10:32:32', '1', '26', '1', '13800000000', '2', 'Changed enabled.'), ('6', '2011-04-11 10:32:45', '1', '26', '2', '13500000000', '1', ''), ('7', '2011-04-11 10:32:52', '1', '26', '3', '13000000000', '1', ''), ('8', '2011-04-11 10:33:05', '1', '26', '4', '13100000000', '1', ''), ('9', '2011-04-11 10:33:14', '1', '26', '4', '13100000000', '2', 'Changed enabled.'), ('10', '2011-04-11 10:33:18', '1', '26', '3', '13000000000', '2', 'Changed enabled.'), ('11', '2011-04-11 10:33:22', '1', '26', '2', '13500000000', '2', 'Changed enabled.'), ('12', '2011-04-11 17:17:35', '1', '24', '1', '中国工商银行 : 9558801000000000000', '2', 'Changed email.'), ('13', '2011-04-12 09:49:37', '1', '25', '1', '110411102759888', '2', 'No fields changed.'), ('14', '2011-04-12 09:50:06', '1', '25', '2', '110412092444555', '1', ''), ('15', '2011-04-12 09:59:04', '1', '25', '2', 'order no. 110412092444555', '2', 'No fields changed.'), ('16', '2011-04-12 11:07:52', '1', '23', '1', '中国工商银行', '2', 'Changed instruction.'), ('17', '2011-04-13 10:06:36', '1', '23', '1', '中国工商银行', '2', 'Changed notice_number.'), ('18', '2011-04-13 10:10:57', '1', '23', '1', '中国工商银行', '2', 'Changed regex.'), ('19', '2011-04-13 16:49:17', '1', '23', '1', '中国工商银行', '2', 'Changed regex.'), ('20', '2011-04-15 14:12:14', '1', '24', '3', '支付宝 : 562838@qq.com', '2', 'Changed cellphone.'), ('21', '2011-04-15 14:12:48', '1', '24', '1', '中国工商银行 : 9558801000000000000', '2', 'Changed cellphone.'), ('22', '2011-04-15 14:13:26', '1', '24', '2', '建设银行 : 9558801000000000001', '2', 'Changed cellphone.'), ('23', '2011-04-15 14:41:28', '1', '23', '2', '建设银行', '2', 'Changed notice_number and api_key.'), ('24', '2011-04-15 14:41:45', '1', '23', '1', '中国工商银行', '2', 'Changed api_key.'), ('25', '2011-04-15 17:03:19', '1', '23', '2', '建设银行', '2', 'Changed regex.'), ('26', '2011-04-19 11:48:29', '1', '26', '1', '13800000000', '2', 'Changed sms_key.'), ('27', '2011-04-19 16:23:53', '1', '28', '1', '上级充值', '1', ''), ('28', '2011-04-19 16:24:05', '1', '28', '2', '跨级充值', '1', ''), ('29', '2011-04-19 16:24:15', '1', '28', '3', '信用充值', '1', ''), ('30', '2011-04-19 16:24:26', '1', '28', '4', '充值扣费', '1', ''), ('31', '2011-04-19 16:24:36', '1', '28', '5', '本人提现', '1', ''), ('32', '2011-04-19 16:24:43', '1', '28', '6', '跨级提现', '1', ''), ('33', '2011-04-19 16:24:53', '1', '28', '7', '下级提现', '1', ''), ('34', '2011-04-19 16:25:10', '1', '28', '8', '本人发起提现', '1', ''), ('35', '2011-04-19 16:25:19', '1', '28', '9', '下级发起提现', '1', ''), ('36', '2011-04-19 16:25:37', '1', '28', '10', '商务提现申请', '1', ''), ('37', '2011-04-19 16:25:46', '1', '28', '11', '商务提现失败', '1', ''), ('38', '2011-04-19 16:25:56', '1', '28', '12', '信用扣减', '1', ''), ('39', '2011-04-19 16:26:07', '1', '28', '13', '商务提现成功', '1', ''), ('40', '2011-04-19 16:26:16', '1', '28', '14', '银行转出', '1', ''), ('41', '2011-04-19 16:26:25', '1', '28', '15', '转入银行', '1', ''), ('42', '2011-04-19 16:26:32', '1', '28', '16', '转账转出', '1', ''), ('43', '2011-04-19 16:26:40', '1', '28', '17', '转账转入', '1', ''), ('44', '2011-04-19 16:26:49', '1', '28', '18', '频道小额转入', '1', ''), ('45', '2011-04-19 16:26:56', '1', '28', '19', '小额扣除', '1', ''), ('46', '2011-04-19 16:27:06', '1', '28', '20', '小额接收', '1', ''), ('47', '2011-04-19 16:27:17', '1', '28', '21', '特殊金额清理', '1', ''), ('48', '2011-04-19 16:27:28', '1', '28', '22', '特殊金额整理', '1', ''), ('49', '2011-04-19 16:27:43', '1', '28', '23', '理赔充值', '1', ''), ('50', '2011-04-19 16:27:52', '1', '28', '24', '管理员扣减', '1', ''), ('51', '2011-04-19 16:28:00', '1', '28', '25', '转账理赔', '1', ''), ('52', '2011-04-19 16:28:14', '1', '28', '26', '平台提现申请', '1', ''), ('53', '2011-04-19 16:28:22', '1', '28', '27', '平台提现失败', '1', ''), ('54', '2011-04-19 16:28:35', '1', '28', '28', '平台提现成功', '1', ''), ('55', '2011-04-19 16:30:21', '1', '28', '29', '平台提现成功', '1', ''), ('56', '2011-04-19 16:54:32', '1', '28', '30', '加入游戏', '1', ''), ('57', '2011-04-19 16:54:57', '1', '28', '31', '销售返点', '1', ''), ('58', '2011-04-19 16:55:06', '1', '28', '32', '奖金派送', '1', ''), ('59', '2011-04-19 16:55:15', '1', '28', '33', '追号扣款', '1', ''), ('60', '2011-04-19 16:55:37', '1', '28', '34', '撤单返款', '1', ''), ('61', '2011-04-19 16:55:50', '1', '28', '35', '撤单手续费', '1', ''), ('62', '2011-04-19 16:56:29', '1', '28', '36', '平台充值', '1', ''), ('63', '2011-04-20 15:49:10', '1', '23', '2', '建设银行', '2', 'Changed min_deposit and max_deposit.'), ('64', '2011-04-23 02:33:37', '1', '23', '1', '中国工商银行', '2', '没有字段被修改。'), ('65', '2011-04-23 02:33:44', '1', '23', '2', '建设银行', '2', '没有字段被修改。');

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_content_type`
-- ----------------------------
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission'), ('2', 'group', 'auth', 'group'), ('3', 'user', 'auth', 'user'), ('4', 'message', 'auth', 'message'), ('5', 'content type', 'contenttypes', 'contenttype'), ('6', 'session', 'sessions', 'session'), ('7', 'site', 'sites', 'site'), ('8', 'access attempt', 'axes', 'accessattempt'), ('9', 'Country', 'home', 'country'), ('10', 'Province', 'home', 'province'), ('11', 'City', 'home', 'city'), ('12', 'Domain', 'home', 'domain'), ('13', 'Channel', 'home', 'channel'), ('14', 'Announcement', 'home', 'announcement'), ('15', 'profile', 'account', 'userprofile'), ('16', 'User\'s card', 'account', 'usercard'), ('17', 'Game', 'games', 'game'), ('18', 'Bank', 'bank', 'bank'), ('19', 'Card', 'bank', 'card'), ('22', 'log entry', 'admin', 'logentry'), ('23', 'deposit method', 'bank', 'depositmethod'), ('24', 'deposit method account', 'bank', 'depositmethodaccount'), ('25', 'deposit log', 'bank', 'depositlog'), ('26', 'cellphone', 'bank', 'cellphone'), ('27', 'SMS log', 'bank', 'smslog'), ('28', 'user account detail type', 'account', 'useraccountdetailtype'), ('29', 'user account detail', 'account', 'useraccountdetail');

-- ----------------------------
--  Table structure for `django_session`
-- ----------------------------
DROP TABLE IF EXISTS `django_session`;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY (`session_key`),
  KEY `django_session_3da3d3d8` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_session`
-- ----------------------------
INSERT INTO `django_session` VALUES ('0171fa0d3c9fb3d635ad4685e6827dae', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:51:17'), ('0207d328f5c9e520cace5bccb20d9def', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:38:50'), ('0561981355a92f539998d82e6903582b', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:51:48'), ('072c515cb855d4f718b67b1fb52f972e', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:00:32'), ('07f566193de0dcae2f58942cbaa2e1fd', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:56:26'), ('0b5f3722f5f60eb322ad7f4977941057', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-06 00:31:13'), ('0d7717ea5ee885a9c0aa18783c1d33ea', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:14:56'), ('0e1e7814fe926dde16b92d33045850be', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:54:30'), ('0ea61a686c73f815dfff9ad366a7fb3c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-29 11:11:23'), ('0fb01047f1fd6855c95e2669baafb687', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-05-03 14:39:52'), ('10040a4e78fadb2e7ed335130d478058', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:56:49'), ('141e374f3f809900b0fce868a4b5233f', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-29 17:34:53'), ('1ac9545fd5a3dc0feba985f505b4c3ec', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 10:04:48'), ('1b238d875f8af162b5fa28ea3f7ab437', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:51:53'), ('1c0cdab351d815dd12633564c6873bf0', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:50:00'), ('1c112746cd8b843039c3aca9a0f2ea54', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:21:15'), ('1d56636ce4682de2744634f5e2b3d710', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:57:02'), ('1f004407339089efa193cc187c804869', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('21ccdcd6a3bac6b6327359cf4595a6de', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-24 14:20:52'), ('229978ea8be7b8e1be09d024df1b958c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:27:52'), ('23895f71a20ce469b2132c099664a056', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-25 10:27:14'), ('24e13f882d8b3fe9a74417f2c2394393', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 16:41:05'), ('25c934b226f050d3937fc6d410e2c9b3', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:33:31'), ('284f35b84aad920f6e41a02b6fb51e81', 'NWJjYTkzOTE5ZDJiYTdlNTIwYTA0YzQ4MTVhYjY4ZDE4OWQyM2I3OTqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWSKAQFVEl9hdXRoX3VzZXJfYmFja2VuZFUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmR1Lg==\n', '2011-05-04 15:49:11'), ('295ee0b6f685e7a1d45d2058c82df117', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('2e3be82e92b8ae4340a31c0e297bd3d7', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:25:24'), ('301d5a2337dcc56e1ecf097a9aedb411', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:26:38'), ('358ce4cba83ceb5375e40ea003d5422d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:43:13'), ('35a7c681e68251a88d5487bf51e8b757', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:24:39'), ('388758765505768616d8ecbe62a64277', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:52:09'), ('3bd7dd366be2dff76187a6037d808b90', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 16:49:55'), ('3c52dc436f7613e9007487ca7927e576', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-29 17:02:58'), ('4b2e52e3b989d9407750250aece7c677', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:00:32'), ('4be3a440edec15759dbeef30adff4463', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-29 17:34:14'), ('4db3ede5a75ef4904bacdcbcc61adaad', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:57:13'), ('4dc7892e96442921a6ca797e9fda993d', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 22:54:23'), ('512e5205bcde76e73a42c3c3e97ac9fd', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:26:00'), ('51b4c1ceed652d0a62e95bead6fede0d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:17:49'), ('540a042f253b7d3625f08d6e5f5d61a9', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 17:03:15'), ('551d58d2267d0b69bd9b12abb005ce14', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:48:48'), ('55e6dfca20be2f283b8078df4eafcd8f', 'NWJjYTkzOTE5ZDJiYTdlNTIwYTA0YzQ4MTVhYjY4ZDE4OWQyM2I3OTqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWSKAQFVEl9hdXRoX3VzZXJfYmFja2VuZFUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmR1Lg==\n', '2011-05-04 15:49:19'), ('576d356873169acf62c4a2d5a1cb8074', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:33:23'), ('5847cc9273df056e744961210c2098b7', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:30:59'), ('59133887ff62b3e281e447cc317c9e64', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:36:52'), ('5d3535ff9ef94da6cb232cdba350104e', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 20:33:39'), ('660817ebdf5a30fd8cdf377cb1195fef', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:33:07'), ('69c9168a62344f2eb8fd12448233e741', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-04 19:09:46'), ('6ca47d062f00ec17021b7dad47d65549', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-26 17:31:54'), ('6ddf6ee113b781a9191ee09bf046b2c1', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-28 11:10:44'), ('6f461b5da9dace55a71082c2cc445586', 'NTI4MmVkMjU4MWQ0OTQxN2E5ZDZjODI0NTM4ZmE3NmJhZGVmM2ZkMzqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWRxAooBAVUSX2F1dGhfdXNlcl9iYWNrZW5kcQNVKWRqYW5nby5jb250cmliLmF1dGguYmFj\na2VuZHMuTW9kZWxCYWNrZW5kcQR1Lg==\n', '2011-04-23 20:28:54'), ('70bbc9837a25a9efd620b460a78c4bc8', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:29:22'), ('77d3c780905de161dde2b566a0e2f62b', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 15:02:50'), ('7cef216d32205b90955b354f1dce2581', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-07 17:27:19'), ('81c9b487fe38a3fa7ede1651d4691173', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-04 10:08:08'), ('8c39652fb7501baa8de5ed5a89526f6a', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:53:28'), ('8e75db7ee93540015ca33db0f9189514', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:55:30'), ('90898476bbe8c0adf43ee6bb161bd21c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:57:40'), ('91477d1ae53e2567f93d9cfb8b23d5ab', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 15:04:16'), ('9483f4605edb31efc8ac655c1e6ca6c1', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-07 17:27:19'), ('964c34dc2c51b1c488e08bdf8ddf1693', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-07 02:32:40'), ('9b0021df62d824d8f2f5e9fb03febd3d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:57:48'), ('9f05cd5dfb7a0e07c50be75f73a9f2e9', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:53:36'), ('a1ccf147ac6be001541c5d2232f61529', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:50:01'), ('a32868de6fb1142961994319c9730e6c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:00:32'), ('a48ffef63534b2ba758ea67e72ae802e', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:56:47'), ('a4da2f70c6a7d79a2cadee5c6946b3bb', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-24 14:20:52'), ('a58b0b04d8c6b6aa4b55674b4885e0fc', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 10:04:48'), ('a6bf221b09cb0f16c5638b91c70bc3fa', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:58:36'), ('a70632ad26634de2e026ce86fd682464', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:44:13'), ('a8b476689fb28ffc817487322096efa5', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 16:49:56'), ('aad830a91344c196b0446531bc38c43f', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:30:29'), ('ac6c9ac62f230b7af8572aa894a1b978', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:54:03'), ('aeebfcb2ecdf9453f3ef5959884a2c57', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 16:38:07'), ('b1b37f1468d3e07c1ff7e94a287f6a4c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-08 05:08:44'), ('b433ffaef35c533ebdc825094aa68812', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:52:16'), ('b5ff0249d9eba8cbdc0c521c3cd3f260', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:45:30'), ('bb57b285eb822f8f54f8e7d54c5d11d4', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:37:00'), ('c80813a9e2dd6460e982653cd6cb59f9', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('c9b96a05bb249c00b53ecac233631977', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 14:37:16'), ('ca87a689d2eb86c4ed20b2d707059c26', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-27 16:48:47'), ('cce52e43def8f9de49ea5dd3be008889', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:50:42'), ('cdb970ada4251e01e31a4d16b1cbe4de', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:20:32'), ('d45e6631dd772c6fdc724dda921bcd50', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 11:49:03'), ('d7b5734413d39087c559c9f7ea71bb48', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-07 02:32:41'), ('d8afd138e8c0dc3ee117975554b907b5', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 15:27:42'), ('df05faf89b23003f8dab3006c4d4ef1e', 'M2MzZTNiNzFkZWMzMzE0MTY5ODBlMjY4YzZiMTJhYTA0ZmJlM2QwODqAAn1xAShVEl9hdXRoX3Vz\nZXJfYmFja2VuZHECVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tlbmRzLk1vZGVsQmFja2VuZHED\nVQ1fYXV0aF91c2VyX2lkcQSKAQF1Lg==\n', '2011-05-07 17:27:18'), ('df724af82156e9b9d52b77a0fac3de8d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-04 19:09:46'), ('e0dd5de21f8833bcd8c7bc1859980ff2', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-28 10:18:56'), ('e81e3f235d556e6a0b9977db2a528874', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:52:56'), ('e903473d45e86b5d9ad451669df18704', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 16:44:14'), ('eb85163557b033ccb570036a229e179c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 15:27:42'), ('eb99790ce7608a3acc0cf50f1a944d87', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 16:49:55'), ('f229b221c63d9e16c305fa5f0775a411', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:39:50'), ('f2e9cf1a772069ae9f0f44370b401108', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:54:06'), ('f34996e90884bc71ff2633e569089c77', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:54:31'), ('f631959a7c8e20b729e30801a13e522a', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 10:04:48'), ('f843f73e83660339fb1e04383395d772', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:52:40'), ('f9647d3be431eb6a53f989fb17938940', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-02 14:40:26'), ('fd55044eac6cfe099bdf2f0683c72026', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:16:59'), ('fd94b3227f37d7ebca7dec181c782aeb', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-05-03 17:32:32');

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
--  Table structure for `domain`
-- ----------------------------
DROP TABLE IF EXISTS `domain`;
CREATE TABLE `domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL,
  `domain` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `game`
-- ----------------------------
DROP TABLE IF EXISTS `game`;
CREATE TABLE `game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(100) NOT NULL,
  `url_name` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `photo` varchar(100) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `province`
-- ----------------------------
DROP TABLE IF EXISTS `province`;
CREATE TABLE `province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country_id` varchar(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `province_534dd89` (`country_id`),
  CONSTRAINT `country_id_refs_iso_7b15e9a8` FOREIGN KEY (`country_id`) REFERENCES `country` (`iso`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `province`
-- ----------------------------
INSERT INTO `province` VALUES ('3', '北京市', 'CN'), ('4', 'Alaska', 'US'), ('5', 'Alabama', 'US'), ('6', 'American Samoa', 'US'), ('7', 'Arizona', 'US'), ('8', 'Arkansas', 'US'), ('9', 'California', 'US'), ('10', 'Colorado', 'US'), ('11', 'Connecticut', 'US'), ('12', 'Delaware', 'US'), ('13', 'District of Columbia', 'US'), ('14', 'Federated province of Micronesia', 'US'), ('15', 'Florida', 'US'), ('16', 'Georgia', 'US'), ('17', 'Guam', 'US'), ('18', 'Hawaii', 'US'), ('19', 'Idaho', 'US'), ('20', 'Illinois', 'US'), ('21', 'Indiana', 'US'), ('22', 'Iowa', 'US'), ('23', 'Kansas', 'US'), ('24', 'Kentucky', 'US'), ('25', 'Louisiana', 'US'), ('26', 'Maine', 'US'), ('27', 'Marshall Islands', 'US'), ('28', 'Maryland', 'US'), ('29', 'Massachusetts', 'US'), ('30', 'Michigan', 'US'), ('31', 'Minnesota', 'US'), ('32', 'Mississippi', 'US'), ('33', 'Missouri', 'US'), ('34', 'Montana', 'US'), ('35', 'Nebraska', 'US'), ('36', 'Nevada', 'US'), ('37', 'New Hampshire', 'US'), ('38', 'New Jersey', 'US'), ('39', 'New Mexico', 'US'), ('40', 'New York', 'US'), ('41', 'North Carolina', 'US'), ('42', 'North Dakota', 'US'), ('43', 'Northern Mariana Islands', 'US'), ('44', 'Ohio', 'US'), ('45', 'Oklahoma', 'US'), ('46', 'Oregon', 'US'), ('47', 'Palau', 'US'), ('48', 'Pennsylvania', 'US'), ('49', 'Puerto Rico', 'US'), ('50', 'Rhode Island', 'US'), ('51', 'South Carolina', 'US'), ('52', 'South Dakota', 'US'), ('53', 'Tennessee', 'US'), ('54', 'Texas', 'US'), ('55', 'Utah', 'US'), ('56', 'Vermont', 'US'), ('57', 'Virgin Islands', 'US'), ('58', 'Virginia', 'US'), ('59', 'Washington', 'US'), ('60', 'West Virginia', 'US'), ('61', 'Wisconsin', 'US'), ('62', 'Wyoming', 'US'), ('63', 'Armed Forces Africa', 'US'), ('64', 'Armed Forces Americas (except Canada)', 'US'), ('65', 'Armed Forces Canada', 'US'), ('66', 'Armed Forces Europe', 'US'), ('67', 'Armed Forces Middle East', 'US'), ('68', 'Armed Forces Pacific', 'US'), ('69', '天津市', 'CN'), ('70', '河北省', 'CN'), ('71', '山西省', 'CN'), ('72', '内蒙古自治区', 'CN'), ('73', '辽宁省', 'CN'), ('74', '吉林省', 'CN'), ('75', '黑龙江省', 'CN'), ('76', '上海市', 'CN'), ('77', '江苏省', 'CN'), ('78', '浙江省', 'CN'), ('79', '安徽省', 'CN'), ('80', '福建省', 'CN'), ('81', '江西省', 'CN'), ('82', '山东省', 'CN'), ('83', '河南省', 'CN'), ('84', '湖北省', 'CN'), ('85', '湖南省', 'CN'), ('86', '广东省', 'CN'), ('87', '广西壮族自治区', 'CN'), ('88', '海南省', 'CN'), ('89', '重庆市', 'CN'), ('90', '四川省', 'CN'), ('91', '贵州省', 'CN'), ('92', '云南省', 'CN'), ('93', '西藏自治区', 'CN'), ('94', '陕西省', 'CN'), ('95', '甘肃省', 'CN'), ('96', '青海省', 'CN'), ('97', '宁夏回族自治区', 'CN'), ('98', '新疆维吾尔自治区', 'CN'), ('99', '香港特别行政区', 'CN'), ('100', '澳门特别行政区', 'CN');

-- ----------------------------
--  Table structure for `user_account_detail`
-- ----------------------------
DROP TABLE IF EXISTS `user_account_detail`;
CREATE TABLE `user_account_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `detail_type_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(100) NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `pre_balance` decimal(14,4) NOT NULL,
  `post_balance` decimal(14,4) NOT NULL,
  `client_ip` char(15) NOT NULL,
  `proxy_ip` char(15) NOT NULL,
  `db_time` datetime NOT NULL,
  `action_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_account_detail_74b00be1` (`from_user_id`),
  KEY `user_account_detail_315477a4` (`to_user_id`),
  KEY `user_account_detail_47a36ae5` (`detail_type_id`),
  KEY `user_account_detail_e972820` (`admin_id`),
  CONSTRAINT `admin_id_refs_id_1bb11274` FOREIGN KEY (`admin_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `detail_type_id_refs_id_64798047` FOREIGN KEY (`detail_type_id`) REFERENCES `user_account_detail_type` (`id`),
  CONSTRAINT `from_user_id_refs_id_1bb11274` FOREIGN KEY (`from_user_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `to_user_id_refs_id_1bb11274` FOREIGN KEY (`to_user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `user_account_detail`
-- ----------------------------
INSERT INTO `user_account_detail` VALUES ('1', '1', null, '36', null, '平台充值', 'user deposit', '10000.0000', '10000.0000', '20000.0000', '127.0.0.1', '127.0.0.1', '2011-04-19 17:37:00', '2011-04-19 17:37:00'), ('2', '1', null, '36', null, '平台充值', 'user deposit', '10000.0000', '20000.0000', '30000.0000', '127.0.0.1', '127.0.0.1', '2011-04-20 10:08:08', '2011-04-20 10:08:08');

-- ----------------------------
--  Table structure for `user_account_detail_type`
-- ----------------------------
DROP TABLE IF EXISTS `user_account_detail_type`;
CREATE TABLE `user_account_detail_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `operation` varchar(1) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `user_account_detail_type`
-- ----------------------------
INSERT INTO `user_account_detail_type` VALUES ('1', '上级充值', '1', '上级充值'), ('2', '跨级充值', '1', '跨级充值'), ('3', '信用充值', '1', '信用充值'), ('4', '充值扣费', '0', '充值扣费'), ('5', '本人提现', '0', '本人提现'), ('6', '跨级提现', '0', '跨级提现'), ('7', '下级提现', '1', '下级提现'), ('8', '本人发起提现', '0', '本人发起提现'), ('9', '下级发起提现', '1', '下级发起提现'), ('10', '商务提现申请', '0', '商务提现申请'), ('11', '商务提现失败', '1', '商务提现失败'), ('12', '信用扣减', '0', '信用扣减'), ('13', '商务提现成功', '0', '商务提现成功'), ('14', '银行转出', '0', '银行转出'), ('15', '转入银行', '1', '转入银行'), ('16', '转账转出', '0', '转账转出'), ('17', '转账转入', '1', '转账转入'), ('18', '频道小额转入', '1', '频道小额转入'), ('19', '小额扣除', '0', '小额扣除'), ('20', '小额接收', '1', '小额接收'), ('21', '特殊金额清理', '0', '特殊金额清理'), ('22', '特殊金额整理', '1', '特殊金额整理'), ('23', '理赔充值', '1', '理赔充值'), ('24', '管理员扣减', '0', '管理员扣减'), ('25', '转账理赔', '1', '转账理赔'), ('26', '平台提现申请', '0', '平台提现申请'), ('27', '平台提现失败', '1', '平台提现失败'), ('28', '平台提现成功', '0', '平台提现成功'), ('29', '平台提现成功', '1', '大额提现'), ('30', '加入游戏', '0', '加入游戏'), ('31', '销售返点', '1', '销售返点'), ('32', '奖金派送', '1', '奖金派送'), ('33', '追号扣款', '0', '追号扣款'), ('34', '撤单返款', '1', '撤单返款'), ('35', '撤单手续费', '0', '撤单手续费'), ('36', '平台充值', '1', '平台充值');

-- ----------------------------
--  Table structure for `user_card`
-- ----------------------------
DROP TABLE IF EXISTS `user_card`;
CREATE TABLE `user_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alias` varchar(20) NOT NULL,
  `account_name` varchar(30) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `card_no` varchar(30) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_card_1862eb86` (`bank_id`),
  KEY `user_card_403f60f` (`user_id`),
  CONSTRAINT `bank_id_refs_id_5cfaf0e` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`),
  CONSTRAINT `user_id_refs_id_993816f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_channel`
-- ----------------------------
DROP TABLE IF EXISTS `user_channel`;
CREATE TABLE `user_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_id` (`channel_id`,`user_id`),
  KEY `user_channel_668d8aa` (`channel_id`),
  KEY `user_channel_403f60f` (`user_id`),
  CONSTRAINT `channel_id_refs_id_24173c8c` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`),
  CONSTRAINT `user_id_refs_id_1ce5ecf5` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_domain`
-- ----------------------------
DROP TABLE IF EXISTS `user_domain`;
CREATE TABLE `user_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_id` (`domain_id`,`user_id`),
  KEY `user_domain_a2431ea` (`domain_id`),
  KEY `user_domain_403f60f` (`user_id`),
  CONSTRAINT `domain_id_refs_id_735fd586` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`),
  CONSTRAINT `user_id_refs_id_7e9bfbeb` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user_profile`
-- ----------------------------
DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(1) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `zip` varchar(8) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `lastip` char(15) DEFAULT NULL,
  `registerip` char(15) DEFAULT NULL,
  `country_id` varchar(6) DEFAULT NULL,
  `available_balance` decimal(14,4) NOT NULL,
  `cash_balance` decimal(14,4) NOT NULL,
  `channel_balance` decimal(14,4) NOT NULL,
  `hold_balance` decimal(14,4) NOT NULL,
  `balance_update_time` datetime DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL,
  `security_password` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `user_profile_586a73b5` (`city_id`),
  KEY `user_profile_37751324` (`province_id`),
  KEY `user_profile_534dd89` (`country_id`),
  CONSTRAINT `city_id_refs_id_3ada2c19` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `country_id_refs_iso_6a9d6c3f` FOREIGN KEY (`country_id`) REFERENCES `country` (`iso`),
  CONSTRAINT `province_id_refs_id_56dc919c` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`),
  CONSTRAINT `user_id_refs_id_5f4bba6f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `user_profile`
-- ----------------------------
INSERT INTO `user_profile` VALUES ('1', '1', '1932-02-06', 'M', '9876543', '', 'paseo parkview', '18L', '399', '123454', null, '76', '127.0.0.1', '127.0.0.1', 'CN', '30000.0000', '0.0000', '0.0000', '0.0000', '2011-04-20 10:08:08', '0', null);

