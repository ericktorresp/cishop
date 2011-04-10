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

 Date: 04/11/2011 03:28:52 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `auth_permission`
-- ----------------------------
INSERT INTO `auth_permission` VALUES ('1', 'Can add permission', '1', 'add_permission'), ('2', 'Can change permission', '1', 'change_permission'), ('3', 'Can delete permission', '1', 'delete_permission'), ('4', 'Can add group', '2', 'add_group'), ('5', 'Can change group', '2', 'change_group'), ('6', 'Can delete group', '2', 'delete_group'), ('7', 'Can add user', '3', 'add_user'), ('8', 'Can change user', '3', 'change_user'), ('9', 'Can delete user', '3', 'delete_user'), ('10', 'Can add message', '4', 'add_message'), ('11', 'Can change message', '4', 'change_message'), ('12', 'Can delete message', '4', 'delete_message'), ('13', 'Can add content type', '5', 'add_contenttype'), ('14', 'Can change content type', '5', 'change_contenttype'), ('15', 'Can delete content type', '5', 'delete_contenttype'), ('16', 'Can add session', '6', 'add_session'), ('17', 'Can change session', '6', 'change_session'), ('18', 'Can delete session', '6', 'delete_session'), ('19', 'Can add site', '7', 'add_site'), ('20', 'Can change site', '7', 'change_site'), ('21', 'Can delete site', '7', 'delete_site'), ('22', 'Can add access attempt', '8', 'add_accessattempt'), ('23', 'Can change access attempt', '8', 'change_accessattempt'), ('24', 'Can delete access attempt', '8', 'delete_accessattempt'), ('25', 'Can add Country', '9', 'add_country'), ('26', 'Can change Country', '9', 'change_country'), ('27', 'Can delete Country', '9', 'delete_country'), ('28', 'Can add Province', '10', 'add_province'), ('29', 'Can change Province', '10', 'change_province'), ('30', 'Can delete Province', '10', 'delete_province'), ('31', 'Can add City', '11', 'add_city'), ('32', 'Can change City', '11', 'change_city'), ('33', 'Can delete City', '11', 'delete_city'), ('34', 'Can add Domain', '12', 'add_domain'), ('35', 'Can change Domain', '12', 'change_domain'), ('36', 'Can delete Domain', '12', 'delete_domain'), ('37', 'Can add Channel', '13', 'add_channel'), ('38', 'Can change Channel', '13', 'change_channel'), ('39', 'Can delete Channel', '13', 'delete_channel'), ('40', 'Can add Announcement', '14', 'add_announcement'), ('41', 'Can change Announcement', '14', 'change_announcement'), ('42', 'Can delete Announcement', '14', 'delete_announcement'), ('43', 'Can verify', '14', 'can_verify'), ('44', 'Can stick', '14', 'can_stick'), ('45', 'Can add profile', '15', 'add_userprofile'), ('46', 'Can change profile', '15', 'change_userprofile'), ('47', 'Can delete profile', '15', 'delete_userprofile'), ('48', 'Can add User\'s card', '16', 'add_usercard'), ('49', 'Can change User\'s card', '16', 'change_usercard'), ('50', 'Can delete User\'s card', '16', 'delete_usercard'), ('51', 'Can add Game', '17', 'add_game'), ('52', 'Can change Game', '17', 'change_game'), ('53', 'Can delete Game', '17', 'delete_game'), ('54', 'Can add Bank', '18', 'add_bank'), ('55', 'Can change Bank', '18', 'change_bank'), ('56', 'Can delete Bank', '18', 'delete_bank'), ('57', 'Can add Card', '19', 'add_card'), ('58', 'Can change Card', '19', 'change_card'), ('59', 'Can delete Card', '19', 'delete_card'), ('60', 'Can verify', '19', 'can_verify'), ('68', 'Can add log entry', '22', 'add_logentry'), ('69', 'Can change log entry', '22', 'change_logentry'), ('70', 'Can delete log entry', '22', 'delete_logentry'), ('71', 'Can add deposit method', '23', 'add_depositmethod'), ('72', 'Can change deposit method', '23', 'change_depositmethod'), ('73', 'Can delete deposit method', '23', 'delete_depositmethod'), ('74', 'Can add deposit method account', '24', 'add_depositmethodaccount'), ('75', 'Can change deposit method account', '24', 'change_depositmethodaccount'), ('76', 'Can delete deposit method account', '24', 'delete_depositmethodaccount'), ('77', 'Can verify', '24', 'can_verify'), ('78', 'Can add deposit log', '25', 'add_depositlog'), ('79', 'Can change deposit log', '25', 'change_depositlog'), ('80', 'Can delete deposit log', '25', 'delete_depositlog'), ('81', 'Can add cellphone', '26', 'add_cellphone'), ('82', 'Can change cellphone', '26', 'change_cellphone'), ('83', 'Can delete cellphone', '26', 'delete_cellphone'), ('84', 'Can verify', '26', 'can_verify'), ('85', 'Can add SMS log', '27', 'add_smslog'), ('86', 'Can change SMS log', '27', 'change_smslog'), ('87', 'Can delete SMS log', '27', 'delete_smslog');

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
INSERT INTO `auth_user` VALUES ('1', 'root', 'Floyd', 'Joe', 'kirinse@gmail.com', 'sha1$228d8$dc78d2daa8f7b9c7cb8bd7ae3d3d7b3526d0f34a', '1', '1', '1', '2011-04-10 14:22:00', '2011-04-07 14:11:55');

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
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) DEFAULT NULL,
  `verify_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_cellphone_1e4ad39d` (`adder_id`),
  KEY `bank_cellphone_584122da` (`verifier_id`),
  CONSTRAINT `adder_id_refs_id_761d1e7b` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `verifier_id_refs_id_761d1e7b` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `bank_cellphone`
-- ----------------------------
INSERT INTO `bank_cellphone` VALUES ('1', '13800000000', '1', '2011-04-10 23:58:35', null, null);

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
  `status` smallint(6) NOT NULL,
  `deposit_time` datetime NOT NULL,
  `receive_log_id` int(11) DEFAULT NULL,
  `receive_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `deposit_log_fbfc09f1` (`user_id`),
  KEY `deposit_log_4069c848` (`deposit_method_id`),
  KEY `deposit_log_2092e3b0` (`deposit_method_account_id`),
  KEY `deposit_log_b7bc689f` (`receive_log_id`),
  CONSTRAINT `deposit_method_account_id_refs_id_80a46b17` FOREIGN KEY (`deposit_method_account_id`) REFERENCES `deposit_method_account` (`id`),
  CONSTRAINT `deposit_method_id_refs_id_e80c2021` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method` (`id`),
  CONSTRAINT `receive_log_id_refs_id_4c9606df` FOREIGN KEY (`receive_log_id`) REFERENCES `deposit_sms_log` (`id`),
  CONSTRAINT `user_id_refs_id_5900f35a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_method_1e4ad39d` (`adder_id`),
  CONSTRAINT `adder_id_refs_id_d058860` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_method`
-- ----------------------------
INSERT INTO `deposit_method` VALUES ('1', '中国工商银行', 'icbc', 'CNY', 'netbank', 'careful', ', editable=False', '1', 'https://mybank.icbc.com.cn/icbc/perbank/index.jsp', 'images/payment/6.jpg', '10.0000', '10000.0000', '1', '2011-04-07 14:20:40'), ('2', '建设银行', 'ccb', 'CNY', 'netbank', 'careful', 'img_logo.allow_tags=True', '1', 'https://ibsbjstar.ccb.com.cn/app/V5/CN/STY1/login.jsp', 'images/payment/7.jpg', '10.0000', '10000.0000', '1', '2011-04-07 14:31:02'), ('3', '支付宝', 'alipay', 'CNY', 'thirdpart', 'careful', 'discriminator', '1', 'http://www.alipay.com/', 'images/payment/alipay.jpg', '10.0000', '5000.0000', '1', '2011-04-07 15:26:39');

-- ----------------------------
--  Table structure for `deposit_method_account`
-- ----------------------------
DROP TABLE IF EXISTS `deposit_method_account`;
CREATE TABLE `deposit_method_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_name` varchar(100) NOT NULL,
  `deposit_method_id` int(11) NOT NULL,
  `login_password` varchar(40) NOT NULL,
  `transaction_password` varchar(40) NOT NULL,
  `account_name` varchar(40) NOT NULL,
  `init_balance` decimal(14,4) NOT NULL,
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
  CONSTRAINT `adder_id_refs_id_2a2aee58` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `deposit_method_account_ibfk_1` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method` (`id`),
  CONSTRAINT `verifier_id_refs_id_2a2aee58` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `deposit_method_account`
-- ----------------------------
INSERT INTO `deposit_method_account` VALUES ('1', '9558801000000000000', '1', '123123', '123123', 'Floyd', '0.0000', '1', '1', '2011-04-07 14:27:03', null, null, '', ''), ('2', '9558801000000000001', '2', '123123', '123123', 'floyd', '0.0000', '1', '1', '2011-04-07 14:32:12', null, null, '', ''), ('3', '562838@qq.com', '3', '123123', '123123', 'Floyd', '0.0000', '1', '1', '2011-04-07 15:27:39', null, null, '', '');

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
  PRIMARY KEY (`id`)
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
  KEY `django_admin_log_403f60f` (`user_id`),
  KEY `django_admin_log_1bb8f392` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_288599e6` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `user_id_refs_id_c8665aa` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_admin_log`
-- ----------------------------
INSERT INTO `django_admin_log` VALUES ('1', '2011-04-07 14:16:50', '1', '18', '1', '中国工商银行', '1', ''), ('2', '2011-04-07 14:17:03', '1', '18', '2', '建设银行', '1', ''), ('3', '2011-04-10 23:58:35', '1', '26', '1', '13800000000', '1', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `django_content_type`
-- ----------------------------
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission'), ('2', 'group', 'auth', 'group'), ('3', 'user', 'auth', 'user'), ('4', 'message', 'auth', 'message'), ('5', 'content type', 'contenttypes', 'contenttype'), ('6', 'session', 'sessions', 'session'), ('7', 'site', 'sites', 'site'), ('8', 'access attempt', 'axes', 'accessattempt'), ('9', 'Country', 'home', 'country'), ('10', 'Province', 'home', 'province'), ('11', 'City', 'home', 'city'), ('12', 'Domain', 'home', 'domain'), ('13', 'Channel', 'home', 'channel'), ('14', 'Announcement', 'home', 'announcement'), ('15', 'profile', 'account', 'userprofile'), ('16', 'User\'s card', 'account', 'usercard'), ('17', 'Game', 'games', 'game'), ('18', 'Bank', 'bank', 'bank'), ('19', 'Card', 'bank', 'card'), ('22', 'log entry', 'admin', 'logentry'), ('23', 'deposit method', 'bank', 'depositmethod'), ('24', 'deposit method account', 'bank', 'depositmethodaccount'), ('25', 'deposit log', 'bank', 'depositlog'), ('26', 'cellphone', 'bank', 'cellphone'), ('27', 'SMS log', 'bank', 'smslog');

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
INSERT INTO `django_session` VALUES ('1f004407339089efa193cc187c804869', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('21ccdcd6a3bac6b6327359cf4595a6de', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-24 14:20:52'), ('25c934b226f050d3937fc6d410e2c9b3', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:33:31'), ('295ee0b6f685e7a1d45d2058c82df117', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('358ce4cba83ceb5375e40ea003d5422d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:43:13'), ('4dc7892e96442921a6ca797e9fda993d', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 22:54:23'), ('51b4c1ceed652d0a62e95bead6fede0d', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:17:49'), ('540a042f253b7d3625f08d6e5f5d61a9', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 17:03:15'), ('55e6dfca20be2f283b8078df4eafcd8f', 'NWJjYTkzOTE5ZDJiYTdlNTIwYTA0YzQ4MTVhYjY4ZDE4OWQyM2I3OTqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWSKAQFVEl9hdXRoX3VzZXJfYmFja2VuZFUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmR1Lg==\n', '2011-04-22 17:16:09'), ('5d3535ff9ef94da6cb232cdba350104e', 'ZmNhMWM2ODk0YTQzNDNhYWUyODdlMzg2NDgwYzlkOTRkY2NlZjY4NTqAAn1xAVUKdGVzdGNvb2tp\nZXECVQZ3b3JrZWRxA3Mu\n', '2011-04-23 20:33:39'), ('660817ebdf5a30fd8cdf377cb1195fef', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 17:33:07'), ('6f461b5da9dace55a71082c2cc445586', 'NTI4MmVkMjU4MWQ0OTQxN2E5ZDZjODI0NTM4ZmE3NmJhZGVmM2ZkMzqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWRxAooBAVUSX2F1dGhfdXNlcl9iYWNrZW5kcQNVKWRqYW5nby5jb250cmliLmF1dGguYmFj\na2VuZHMuTW9kZWxCYWNrZW5kcQR1Lg==\n', '2011-04-23 20:28:54'), ('a4da2f70c6a7d79a2cadee5c6946b3bb', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-24 14:20:52'), ('c80813a9e2dd6460e982653cd6cb59f9', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36'), ('d8afd138e8c0dc3ee117975554b907b5', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 15:27:42'), ('df05faf89b23003f8dab3006c4d4ef1e', 'M2MzZTNiNzFkZWMzMzE0MTY5ODBlMjY4YzZiMTJhYTA0ZmJlM2QwODqAAn1xAShVEl9hdXRoX3Vz\nZXJfYmFja2VuZHECVSlkamFuZ28uY29udHJpYi5hdXRoLmJhY2tlbmRzLk1vZGVsQmFja2VuZHED\nVQ1fYXV0aF91c2VyX2lkcQSKAQF1Lg==\n', '2011-04-25 00:08:07'), ('eb85163557b033ccb570036a229e179c', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-23 15:27:42');

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
INSERT INTO `user_profile` VALUES ('1', '1', '1932-02-06', 'M', '9876543', '', 'paseo parkview', '18L', '399', '123454', null, '76', '127.0.0.1', '127.0.0.1', 'CN', '0.0000', '0.0000', '0.0000', '0.0000', null, '0', null);

