/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50139
 Source Host           : localhost
 Source Database       : 1930sh

 Target Server Type    : MySQL
 Target Server Version : 50139
 File Encoding         : utf-8

 Date: 04/18/2010 21:04:13 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `acos`
-- ----------------------------
DROP TABLE IF EXISTS `acos`;
CREATE TABLE `acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) DEFAULT '',
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=248 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `acos`
-- ----------------------------
INSERT INTO `acos` VALUES ('1', null, '', null, 'controllers', '1', '1264'), ('2', '1', null, null, 'Pages', '772', '775'), ('3', '2', null, null, 'display', '773', '774'), ('4', '1', null, null, 'Arms', '776', '803'), ('5', '4', null, null, 'index', '777', '778'), ('6', '4', null, null, 'armors', '779', '780'), ('7', '4', null, null, 'guards', '781', '782'), ('8', '4', null, null, 'repair', '783', '784'), ('9', '4', null, null, 'buy', '785', '786'), ('10', '4', null, null, 'sell', '787', '788'), ('11', '4', null, null, 'disarm', '789', '790'), ('12', '4', null, null, 'active', '791', '792'), ('13', '4', null, null, 'getactivearms', '793', '794'), ('14', '4', null, null, 'admin_index', '795', '796'), ('15', '4', null, null, 'admin_add', '797', '798'), ('16', '4', null, null, 'admin_edit', '799', '800'), ('17', '4', null, null, 'admin_delete', '801', '802'), ('18', '1', null, null, 'Assault', '804', '819'), ('19', '18', null, null, 'index', '805', '806'), ('20', '18', null, null, 'assault', '807', '808'), ('21', '18', null, null, 'plan', '809', '810'), ('22', '18', null, null, 'abort', '811', '812'), ('23', '18', null, null, 'perform', '813', '814'), ('24', '18', null, null, 'accept', '815', '816'), ('25', '18', null, null, 'decline', '817', '818'), ('26', '1', null, null, 'Bank', '820', '825'), ('27', '26', null, null, 'index', '821', '822'), ('28', '26', null, null, 'transfer', '823', '824'), ('29', '1', null, null, 'Benefits', '826', '829'), ('30', '29', null, null, 'buy', '827', '828'), ('31', '1', null, null, 'Bounty', '830', '837'), ('32', '31', null, null, 'index', '831', '832'), ('33', '31', null, null, 'bounty', '833', '834'), ('34', '31', null, null, 'check', '835', '836'), ('35', '1', null, null, 'Buildings', '838', '855'), ('36', '35', null, null, 'index', '839', '840'), ('37', '35', null, null, 'buy', '841', '842'), ('38', '35', null, null, 'sell', '843', '844'), ('39', '35', null, null, 'pickup', '845', '846'), ('40', '35', null, null, 'admin_index', '847', '848'), ('41', '35', null, null, 'admin_add', '849', '850'), ('42', '35', null, null, 'admin_edit', '851', '852'), ('43', '35', null, null, 'admin_delete', '853', '854'), ('44', '1', null, null, 'Businesses', '856', '885'), ('45', '44', null, null, 'index', '857', '858'), ('46', '44', null, null, 'buy', '859', '860'), ('47', '44', null, null, 'sell', '861', '862'), ('48', '44', null, null, 'collect', '863', '864'), ('49', '44', null, null, 'adddrug', '865', '866'), ('50', '44', null, null, 'deldrug', '867', '868'), ('51', '44', null, null, 'addhooker', '869', '870'), ('52', '44', null, null, 'delhooker', '871', '872'), ('53', '44', null, null, 'edit', '873', '874'), ('54', '44', null, null, 'visitors', '875', '876'), ('55', '44', null, null, 'admin_index', '877', '878'), ('56', '44', null, null, 'admin_add', '879', '880'), ('57', '44', null, null, 'admin_edit', '881', '882'), ('58', '44', null, null, 'admin_delete', '883', '884'), ('59', '1', null, null, 'Casino', '886', '893'), ('60', '59', null, null, 'index', '887', '888'), ('61', '59', null, null, 'blackjack', '889', '890'), ('62', '59', null, null, 'lottery', '891', '892'), ('63', '1', null, null, 'Character', '894', '899'), ('64', '63', null, null, 'index', '895', '896'), ('65', '63', null, null, 'choose', '897', '898'), ('66', '1', null, null, 'Credits', '900', '905'), ('67', '66', null, null, 'index', '901', '902'), ('68', '66', null, null, 'buy', '903', '904'), ('69', '1', null, null, 'Docks', '906', '913'), ('70', '69', null, null, 'index', '907', '908'), ('71', '69', null, null, 'tell', '909', '910'), ('72', '69', null, null, 'sell', '911', '912'), ('73', '1', null, null, 'Drugs', '914', '931'), ('74', '73', null, null, 'index', '915', '916'), ('75', '73', null, null, 'buy', '917', '918'), ('76', '73', null, null, 'sell', '919', '920'), ('77', '73', null, null, 'getstock', '921', '922'), ('78', '73', null, null, 'admin_index', '923', '924'), ('79', '73', null, null, 'admin_add', '925', '926'), ('80', '73', null, null, 'admin_edit', '927', '928'), ('81', '73', null, null, 'admin_delete', '929', '930'), ('82', '1', null, null, 'Fellas', '932', '947'), ('83', '82', null, null, 'index', '933', '934'), ('84', '82', null, null, 'check', '935', '936'), ('85', '82', null, null, 'delete', '937', '938'), ('86', '82', null, null, 'add', '939', '940'), ('87', '82', null, null, 'abort', '941', '942'), ('88', '82', null, null, 'accept', '943', '944'), ('89', '82', null, null, 'decline', '945', '946'), ('90', '1', null, null, 'Fight', '948', '957'), ('91', '90', null, null, 'index', '949', '950'), ('92', '90', null, null, 'challenge', '951', '952'), ('93', '90', null, null, 'chat', '953', '954'), ('94', '90', null, null, 'refresh', '955', '956'), ('95', '1', null, null, 'Gangs', '958', '999'), ('96', '95', null, null, 'index', '959', '960'), ('97', '95', null, null, 'create', '961', '962'), ('98', '95', null, null, 'edit', '963', '964'), ('99', '95', null, null, 'delete', '965', '966'), ('100', '95', null, null, 'leave', '967', '968'), ('101', '95', null, null, 'upload', '969', '970'), ('102', '95', null, null, 'delpic', '971', '972'), ('103', '95', null, null, 'members', '973', '974'), ('104', '95', null, null, 'accept', '975', '976'), ('105', '95', null, null, 'decline', '977', '978'), ('106', '95', null, null, 'kick', '979', '980'), ('107', '95', null, null, 'invite', '981', '982'), ('108', '95', null, null, 'sendmsg', '983', '984'), ('109', '95', null, null, 'noteboard', '985', '986'), ('110', '95', null, null, 'news', '987', '988'), ('111', '95', null, null, 'addnews', '989', '990'), ('112', '95', null, null, 'delnews', '991', '992'), ('113', '95', null, null, 'check', '993', '994'), ('114', '95', null, null, 'chat', '995', '996'), ('115', '95', null, null, 'refresh', '997', '998'), ('116', '1', null, null, 'Groups', '1000', '1011'), ('117', '116', null, null, 'admin_index', '1001', '1002'), ('118', '116', null, null, 'admin_view', '1003', '1004'), ('119', '116', null, null, 'admin_add', '1005', '1006'), ('120', '116', null, null, 'admin_edit', '1007', '1008'), ('121', '116', null, null, 'admin_delete', '1009', '1010'), ('122', '1', null, null, 'Hookers', '1012', '1029'), ('123', '122', null, null, 'index', '1013', '1014'), ('124', '122', null, null, 'buy', '1015', '1016'), ('125', '122', null, null, 'sell', '1017', '1018'), ('126', '122', null, null, 'collect', '1019', '1020'), ('127', '122', null, null, 'admin_index', '1021', '1022'), ('128', '122', null, null, 'admin_add', '1023', '1024'), ('129', '122', null, null, 'admin_edit', '1025', '1026'), ('130', '122', null, null, 'admin_delete', '1027', '1028'), ('131', '1', null, null, 'Hospital', '1030', '1041'), ('132', '131', null, null, 'index', '1031', '1032'), ('133', '131', null, null, 'buy', '1033', '1034'), ('134', '131', null, null, 'surgery', '1035', '1036'), ('135', '131', null, null, 'detox', '1037', '1038'), ('136', '131', null, null, 'upload', '1039', '1040'), ('137', '1', null, null, 'Messages', '1042', '1051'), ('138', '137', null, null, 'index', '1043', '1044'), ('139', '137', null, null, 'send', '1045', '1046'), ('140', '137', null, null, 'delete', '1047', '1048'), ('141', '137', null, null, 'getnew', '1049', '1050'), ('142', '1', null, null, 'Nightlife', '1052', '1075'), ('143', '142', null, null, 'index', '1053', '1054'), ('144', '142', null, null, 'whorehouses', '1055', '1056'), ('145', '142', null, null, 'search', '1057', '1058'), ('146', '142', null, null, 'fav', '1059', '1060'), ('147', '142', null, null, 'delfav', '1061', '1062'), ('148', '142', null, null, 'enter', '1063', '1064'), ('149', '142', null, null, 'leave', '1065', '1066'), ('150', '142', null, null, 'buy', '1067', '1068'), ('151', '142', null, null, 'visit', '1069', '1070'), ('152', '142', null, null, 'chat', '1071', '1072'), ('153', '142', null, null, 'refreshchat', '1073', '1074'), ('154', '1', null, null, 'Prison', '1076', '1087'), ('155', '154', null, null, 'index', '1077', '1078'), ('156', '154', null, null, 'escape', '1079', '1080'), ('157', '154', null, null, 'bribe', '1081', '1082'), ('158', '154', null, null, 'chat', '1083', '1084'), ('159', '154', null, null, 'refresh', '1085', '1086'), ('160', '1', null, null, 'Profile', '1088', '1125'), ('161', '160', null, null, 'index', '1089', '1090'), ('162', '160', null, null, 'stats', '1091', '1092'), ('163', '160', null, null, 'guestbook', '1093', '1094'), ('164', '160', null, null, 'addgb', '1095', '1096'), ('165', '160', null, null, 'delgb', '1097', '1098'), ('166', '160', null, null, 'ignorelist', '1099', '1100'), ('167', '160', null, null, 'addignore', '1101', '1102'), ('168', '160', null, null, 'delignore', '1103', '1104'), ('169', '160', null, null, 'trustedaccounts', '1105', '1106'), ('170', '160', null, null, 'addtrustedaccount', '1107', '1108'), ('171', '160', null, null, 'deltrustedaccount', '1109', '1110'), ('172', '160', null, null, 'edit', '1111', '1112'), ('173', '160', null, null, 'upload', '1113', '1114'), ('174', '160', null, null, 'delpic', '1115', '1116'), ('175', '160', null, null, 'changepassword', '1117', '1118'), ('176', '160', null, null, 'rabbit', '1119', '1120'), ('177', '160', null, null, 'userspy', '1121', '1122'), ('178', '160', null, null, 'report', '1123', '1124'), ('179', '1', null, null, 'Recruit', '1126', '1131'), ('180', '179', null, null, 'index', '1127', '1128'), ('181', '179', null, null, 'send', '1129', '1130'), ('182', '1', null, null, 'Register', '1132', '1149'), ('183', '182', null, null, 'index', '1133', '1134'), ('184', '182', null, null, 'aj', '1135', '1136'), ('185', '182', null, null, 'captcha', '1137', '1138'), ('186', '182', null, null, 'checkcap', '1139', '1140'), ('188', '1', null, null, 'Revents', '1150', '1163'), ('189', '188', null, null, 'index', '1151', '1152'), ('190', '188', null, null, 'answer', '1153', '1154'), ('191', '188', null, null, 'admin_index', '1155', '1156'), ('192', '188', null, null, 'admin_add', '1157', '1158'), ('193', '188', null, null, 'admin_edit', '1159', '1160'), ('194', '188', null, null, 'admin_delete', '1161', '1162'), ('195', '1', null, null, 'Rip', '1164', '1173'), ('196', '195', null, null, 'index', '1165', '1166'), ('197', '195', null, null, 'escape', '1167', '1168'), ('198', '195', null, null, 'chat', '1169', '1170'), ('199', '195', null, null, 'refresh', '1171', '1172'), ('200', '1', null, null, 'Robbery', '1174', '1189'), ('201', '200', null, null, 'index', '1175', '1176'), ('202', '200', null, null, 'single', '1177', '1178'), ('203', '200', null, null, 'gang', '1179', '1180'), ('204', '200', null, null, 'abort', '1181', '1182'), ('205', '200', null, null, 'accept', '1183', '1184'), ('206', '200', null, null, 'decline', '1185', '1186'), ('207', '200', null, null, 'perform', '1187', '1188'), ('208', '1', null, null, 'Sabotage', '1190', '1207'), ('209', '208', null, null, 'index', '1191', '1192'), ('210', '208', null, null, 'plan', '1193', '1194'), ('211', '208', null, null, 'perform', '1195', '1196'), ('212', '208', null, null, 'abort', '1197', '1198'), ('213', '208', null, null, 'admin_index', '1199', '1200'), ('214', '208', null, null, 'admin_add', '1201', '1202'), ('215', '208', null, null, 'admin_edit', '1203', '1204'), ('216', '208', null, null, 'admin_delete', '1205', '1206'), ('217', '1', null, null, 'Square', '1208', '1215'), ('218', '217', null, null, 'index', '1209', '1210'), ('219', '217', null, null, 'chat', '1211', '1212'), ('220', '217', null, null, 'refresh', '1213', '1214'), ('221', '1', null, null, 'Start', '1216', '1225'), ('222', '221', null, null, 'index', '1217', '1218'), ('223', '221', null, null, 'newspaper', '1219', '1220'), ('224', '221', null, null, 'benefits', '1221', '1222'), ('225', '221', null, null, 'choicechar', '1223', '1224'), ('226', '1', null, null, 'Stats', '1226', '1233'), ('227', '226', null, null, 'index', '1227', '1228'), ('228', '226', null, null, 'gangs', '1229', '1230'), ('229', '226', null, null, 'killers', '1231', '1232'), ('230', '1', null, null, 'Users', '1234', '1251'), ('231', '230', null, null, 'login', '1235', '1236'), ('232', '230', null, null, 'logout', '1237', '1238'), ('233', '230', null, null, 'admin_index', '1239', '1240'), ('234', '230', null, null, 'admin_view', '1241', '1242'), ('235', '230', null, null, 'admin_add', '1243', '1244'), ('236', '230', null, null, 'admin_edit', '1245', '1246'), ('237', '230', null, null, 'admin_delete', '1247', '1248'), ('238', '230', null, null, 'initDB', '1249', '1250'), ('242', '1', null, null, 'Confirm', '1258', '1261'), ('243', '242', null, null, 'index', '1259', '1260'), ('245', '182', null, null, 'deleteuser', '1143', '1144'), ('246', '182', null, null, 'resendregister', '1145', '1146'), ('247', '182', null, null, 'lostpasswd', '1147', '1148');

-- ----------------------------
--  Table structure for `armors`
-- ----------------------------
DROP TABLE IF EXISTS `armors`;
CREATE TABLE `armors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tolerance` int(11) NOT NULL,
  `photo` varchar(99) NOT NULL,
  `price` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `armors`
-- ----------------------------
INSERT INTO `armors` VALUES ('1', '8', 'diper.jpg', '150'), ('2', '32', 'leather_jacket.jpg', '1250'), ('3', '120', 'shining_body_armor.jpg', '15000'), ('4', '400', 'body_armour.jpg', '2100000'), ('5', '1200', 'nano_fiber_combat_jacket.jpg', '6200000'), ('6', '2000', 'nomex_plated_armour.jpg', '10000000');

-- ----------------------------
--  Table structure for `aros`
-- ----------------------------
DROP TABLE IF EXISTS `aros`;
CREATE TABLE `aros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) DEFAULT '',
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `aros`
-- ----------------------------
INSERT INTO `aros` VALUES ('1', null, 'Group', '1', '', '1', '4'), ('2', null, 'Group', '2', '', '5', '8'), ('3', null, 'Group', '3', '', '9', '12'), ('4', '1', 'User', '1', '', '2', '3'), ('5', '3', 'User', '2', '', '10', '11');

-- ----------------------------
--  Table structure for `aros_acos`
-- ----------------------------
DROP TABLE IF EXISTS `aros_acos`;
CREATE TABLE `aros_acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) unsigned NOT NULL,
  `aco_id` int(10) unsigned NOT NULL,
  `_create` char(2) NOT NULL DEFAULT '0',
  `_read` char(2) NOT NULL DEFAULT '0',
  `_update` char(2) NOT NULL DEFAULT '0',
  `_delete` char(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `aros_acos`
-- ----------------------------
INSERT INTO `aros_acos` VALUES ('1', '1', '1', '1', '1', '1', '1'), ('2', '3', '1', '-1', '-1', '-1', '-1'), ('55', '3', '195', '1', '1', '1', '1'), ('54', '3', '95', '1', '1', '1', '1'), ('53', '3', '18', '1', '1', '1', '1'), ('52', '3', '69', '1', '1', '1', '1'), ('51', '3', '66', '1', '1', '1', '1'), ('50', '3', '179', '1', '1', '1', '1'), ('49', '3', '63', '1', '1', '1', '1'), ('48', '3', '26', '1', '1', '1', '1'), ('47', '3', '59', '1', '1', '1', '1'), ('46', '3', '90', '1', '1', '1', '1'), ('45', '3', '217', '1', '1', '1', '1'), ('44', '3', '154', '1', '1', '1', '1'), ('43', '3', '131', '1', '1', '1', '1'), ('42', '3', '44', '1', '1', '1', '1'), ('41', '3', '35', '1', '1', '1', '1'), ('40', '3', '73', '1', '1', '1', '1'), ('39', '3', '122', '1', '1', '1', '1'), ('38', '3', '208', '1', '1', '1', '1'), ('37', '3', '200', '1', '1', '1', '1'), ('36', '3', '4', '1', '1', '1', '1'), ('35', '3', '142', '1', '1', '1', '1'), ('34', '3', '232', '1', '1', '1', '1'), ('33', '3', '160', '1', '1', '1', '1'), ('32', '3', '82', '1', '1', '1', '1'), ('31', '3', '137', '1', '1', '1', '1'), ('30', '3', '221', '1', '1', '1', '1'), ('56', '3', '31', '1', '1', '1', '1');

-- ----------------------------
--  Table structure for `avatars`
-- ----------------------------
DROP TABLE IF EXISTS `avatars`;
CREATE TABLE `avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(99) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `avatars`
-- ----------------------------
INSERT INTO `avatars` VALUES ('1', 'avatar_1.jpg', '1236874965'), ('2', 'avatar_2.jpg', '1236874976'), ('18', 'avatar_18.jpg', '0'), ('4', 'avatar_4.jpg', '0'), ('5', 'avatar_5.jpg', '0'), ('6', 'avatar_6.jpg', '0'), ('7', 'avatar_7.jpg', '0'), ('8', 'avatar_8.jpg', '0'), ('9', 'avatar_9.jpg', '0'), ('10', 'avatar_10.jpg', '0'), ('11', 'avatar_11.jpg', '0'), ('12', 'avatar_12.jpg', '0'), ('13', 'avatar_13.jpg', '0'), ('14', 'avatar_14.jpg', '0'), ('15', 'avatar_15.jpg', '0'), ('16', 'avatar_16.jpg', '0'), ('17', 'avatar_17.jpg', '0'), ('19', 'avatar_19.jpg', '0'), ('26', 'avatar_26.jpg', '0'), ('27', 'avatar_27.jpg', '0'), ('28', 'avatar_28.jpg', '0'), ('29', 'avatar_29.jpg', '0'), ('30', 'avatar_30.jpg', '0'), ('31', 'avatar_31.jpg', '0');

-- ----------------------------
--  Table structure for `banks`
-- ----------------------------
DROP TABLE IF EXISTS `banks`;
CREATE TABLE `banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `banks`
-- ----------------------------
INSERT INTO `banks` VALUES ('1', '1', '0', '1239960265', '1245520481'), ('2', '1', '0', '1246122363', '1246122363'), ('3', '2', '0', '1246128584', '1246128584');

-- ----------------------------
--  Table structure for `benefits`
-- ----------------------------
DROP TABLE IF EXISTS `benefits`;
CREATE TABLE `benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('hooker','weapon','building','condom') NOT NULL,
  `credits` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `benefits`
-- ----------------------------
INSERT INTO `benefits` VALUES ('1', 'hooker', '10'), ('2', 'weapon', '10'), ('3', 'building', '10'), ('4', 'condom', '10');

-- ----------------------------
--  Table structure for `bounties`
-- ----------------------------
DROP TABLE IF EXISTS `bounties`;
CREATE TABLE `bounties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `sender_id` bigint(20) NOT NULL,
  `credits` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `killed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `buildings`
-- ----------------------------
DROP TABLE IF EXISTS `buildings`;
CREATE TABLE `buildings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unitperday` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `payoutperday` int(11) NOT NULL,
  `photo` varchar(99) NOT NULL,
  `output_drug_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `buildings`
-- ----------------------------
INSERT INTO `buildings` VALUES ('1', '15', '4500', '12', 'weedplant.jpg', '2', '1239862798'), ('2', '10', '5000', '10', 'moonshiner.jpg', '3', '1239863007'), ('3', '45', '6500', '20', 'hashplant.jpg', '5', '1239863007'), ('4', '1100', '40000', '150', 'brewery.jpg', '3', '1239863140'), ('5', '1300', '50000', '250', 'pharmacy.jpg', '1', '1239863140'), ('6', '800', '60000', '320', 'mushrooms.jpg', '4', '1239863277'), ('7', '1600', '75000', '350', 'weedfield.jpg', '2', '1239863277'), ('8', '60', '80000', '900', 'morphinelab.jpg', '13', '1239863802'), ('9', '590', '100000', '500', 'lsd_lab.jpg', '6', '1239863802'), ('10', '340', '110000', '700', 'ecstacy_lab.jpg', '8', '1239863968'), ('11', '240', '150000', '1020', 'opiumfield.jpg', '10', '1239863968'), ('12', '650', '150000', '800', 'ghblab.jpg', '7', '1239864196'), ('13', '165', '180000', '650', 'specialklab.jpg', '12', '1239864196'), ('14', '120', '250000', '1500', 'cocaine.jpg', '11', '1239864282'), ('15', '160', '300000', '2000', 'amphetamine.jpg', '9', '1239864282'), ('16', '60', '500000', '5000', 'heroine_lab.jpg', '14', '1239864332');

-- ----------------------------
--  Table structure for `businesses`
-- ----------------------------
DROP TABLE IF EXISTS `businesses`;
CREATE TABLE `businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo` varchar(99) NOT NULL,
  `max_visitors` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `payoutperday` int(11) NOT NULL,
  `type` enum('club','hooker') NOT NULL,
  `created` int(11) NOT NULL,
  `limit` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `businesses`
-- ----------------------------
INSERT INTO `businesses` VALUES ('1', 'nightclub.jpg', '25', '10000', '50', 'club', '1240887232', '3'), ('2', 'rave.jpg', '100', '25000', '100', 'club', '1240887253', '10'), ('3', 'whorehouse.jpg', '10', '40000', '80', 'hooker', '1240887265', '5'), ('4', 'hooker_manison.jpg', '30', '110000', '230', 'hooker', '1240887278', '15');

-- ----------------------------
--  Table structure for `challenges`
-- ----------------------------
DROP TABLE IF EXISTS `challenges`;
CREATE TABLE `challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `victim_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  `expired` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `characters`
-- ----------------------------
DROP TABLE IF EXISTS `characters`;
CREATE TABLE `characters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avatar` varchar(99) NOT NULL,
  `photo` varchar(99) NOT NULL,
  `intelligence` int(11) NOT NULL,
  `strength` int(11) NOT NULL,
  `charisma` int(11) NOT NULL,
  `tolerance` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `characters`
-- ----------------------------
INSERT INTO `characters` VALUES ('1', 'pimp_head.png', 'pimp_300.gif', '4', '4', '6', '6'), ('3', 'biz_head.png', 'biz_300.gif', '6', '4', '4', '6'), ('2', 'hitman_head.png', 'hitman_300.gif', '4', '6', '4', '6'), ('4', 'robber_head.png', 'robber_300.gif', '6', '6', '4', '4'), ('5', 'gangster_head.png', 'gangster_300.gif', '5', '5', '5', '5');

-- ----------------------------
--  Table structure for `chats`
-- ----------------------------
DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `users_business_id` int(11) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL,
  `created` int(11) NOT NULL DEFAULT '0',
  `type` enum('square','fight','prison','rip','nightlife','gang') NOT NULL DEFAULT 'square',
  `gang_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `chats`
-- ----------------------------
INSERT INTO `chats` VALUES ('1', '1', '0', 'df', '1271507424', 'square', '0');

-- ----------------------------
--  Table structure for `confirms`
-- ----------------------------
DROP TABLE IF EXISTS `confirms`;
CREATE TABLE `confirms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `code` varchar(32) NOT NULL,
  `created` int(11) NOT NULL,
  `passwd` varchar(99) DEFAULT NULL,
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `issent` tinyint(1) NOT NULL DEFAULT '0',
  `action` enum('register','resetpwd','deluser','rabbit','trustaccount','changepwd') NOT NULL DEFAULT 'register',
  PRIMARY KEY (`id`),
  KEY `FK_actives` (`user_id`),
  KEY `code` (`code`),
  CONSTRAINT `FK_actives` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `confirms`
-- ----------------------------
INSERT INTO `confirms` VALUES ('26', '1', '1918df2c75a3fce9a98d96a4d665b07e', '1245522352', null, '1', '0', 'resetpwd'), ('27', '1', '39fb7b0f6ab247d4230fc4f594c0702a', '1245522489', '2908262', '1', '0', 'changepwd'), ('29', '2', '577b671b4bed7da17f5cc40b485e3f9d', '1246128252', 'c48s23', '1', '0', 'register');

-- ----------------------------
--  Table structure for `credits`
-- ----------------------------
DROP TABLE IF EXISTS `credits`;
CREATE TABLE `credits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `credits` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `created` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `daily_business`
-- ----------------------------
DROP TABLE IF EXISTS `daily_business`;
CREATE TABLE `daily_business` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_user_id` int(11) NOT NULL,
  `visitors` int(11) NOT NULL DEFAULT '0',
  `days` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `daily_respect`
-- ----------------------------
DROP TABLE IF EXISTS `daily_respect`;
CREATE TABLE `daily_respect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `respect` int(11) NOT NULL,
  `days` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `daily_respect`
-- ----------------------------
INSERT INTO `daily_respect` VALUES ('1', '1', '5', '1', '1246122363'), ('2', '2', '5', '1', '1246128639');

-- ----------------------------
--  Table structure for `drugs`
-- ----------------------------
DROP TABLE IF EXISTS `drugs`;
CREATE TABLE `drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `stamina` smallint(2) NOT NULL DEFAULT '0',
  `spirit` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `drugs`
-- ----------------------------
INSERT INTO `drugs` VALUES ('1', '6', '901', '2009-04-15 15:58:20', '2009-04-23 08:25:56', '99', '1'), ('2', '5', '900', '2009-04-15 15:58:20', '2009-04-20 07:48:07', '99', '1'), ('3', '7', '900', '2009-04-15 15:58:56', '2009-04-20 07:34:03', '50', '1'), ('4', '10', '740', '2009-04-15 15:58:56', '2009-04-15 15:58:56', '50', '1'), ('5', '12', '0', '2009-04-15 15:59:34', '2009-04-15 15:59:34', '35', '2'), ('6', '13', '0', '2009-04-15 15:59:34', '2009-04-15 15:59:34', '35', '2'), ('7', '10', '0', '2009-04-15 16:00:09', '2009-04-15 16:00:09', '25', '3'), ('8', '29', '0', '2009-04-15 16:00:09', '2009-04-15 16:00:09', '25', '3'), ('9', '60', '0', '2009-04-15 16:00:45', '2009-04-15 16:00:45', '20', '4'), ('10', '37', '0', '2009-04-15 16:00:45', '2009-04-15 16:00:45', '20', '4'), ('11', '72', '0', '2009-04-15 16:01:26', '2009-04-15 16:01:26', '15', '4'), ('12', '99', '0', '2009-04-15 16:01:26', '2009-04-15 16:01:26', '15', '5'), ('13', '117', '0', '2009-04-15 16:02:04', '2009-04-15 16:02:04', '13', '5'), ('14', '238', '0', '2009-04-15 16:02:04', '2009-04-15 16:02:04', '13', '5');

-- ----------------------------
--  Table structure for `events`
-- ----------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `section` enum('','robbery','drug','dock') NOT NULL DEFAULT '',
  `change` smallint(2) NOT NULL,
  `day` smallint(3) NOT NULL DEFAULT '0',
  `photo` varchar(99) DEFAULT NULL,
  `drug_id` smallint(3) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `events`
-- ----------------------------
INSERT INTO `events` VALUES ('1', 'robbery', '10', '36', '/images/section/start/newspaper_raid.jpg', '0', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.'), ('2', 'drug', '-20', '11', '/images/sections/home/newspaper_drugs.jpg', '0', 'DRUG PRICES DOWN', ''), ('3', 'robbery', '10', '14', '/images/section/start/newspaper_raid.jpg', '0', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.'), ('4', 'drug', '-20', '20', '/images/section/start/newspaper_drugs.jpg', '0', '', ''), ('5', '', '0', '35', '/images/section/start/warren_buffet.jpg', '0', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.'), ('6', 'robbery', '10', '39', '/images/section/start/newspaper_raid.jpg', '0', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.'), ('7', 'drug', '20', '41', '/images/section/start/newspaper_drugs.jpg', '14', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.'), ('8', 'drug', '-20', '44', '/images/section/start/carnival.jpg', '0', 'CARNIVAL!', 'Today it is carnival day in The Bund!'), ('9', '', '0', '45', '/images/section/start/oil_sheikh.jpg', null, 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!'), ('10', 'drug', '-10', '48', '/images/section/start/newspaper_drugs.jpg', '7', 'DRUG PRICES DOWN', 'A large delivery of %s has arrived, prices are going down.'), ('11', 'robbery', '10', '49', '/images/section/start/newspaper_raid.jpg', null, 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.'), ('12', 'robbery', '10', '60', '/images/section/start/newspaper_raid.jpg', '0', 'POLICE RAID', 'The police are using all available resources to fight crime in TheBund. They have massively increased their numbers on the streets in a desperate attempt to reduce the number of robberies committed in the city. Criminals better beware as they might get caught on their way to a robbery. When asked if resources have been shifted from other areas a high police official stated that this is an effort directed to prevent crime that hurt innocent citizens and that resources have been taken from clearing up killings in the criminal world. \"We expect to only get convictions in half as many murder cases as we used to, for the period of this raid.\" Said the same police official to the TheBund Times.'), ('13', '', '0', '62', '/images/section/start/oil_sheikh.jpg', null, 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!'), ('14', 'drug', '-10', '70', '/images/sections/home/newspaper_drugs.jpg', '7', 'DRUG PRICES DOWN', 'A large delivery of %s has arrived, prices are going down.'), ('15', 'drug', '8', '75', '/images/section/start/newspaper_drugs.jpg', '9', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.'), ('16', '', '0', '0', '/images/section/start/bill_gates.jpg', null, 'BILL GATES IN TOWN!', 'Bill Gates is visiting the city today to promote his new software and try some new bugs.'), ('17', 'dock', '20', '38', '/images/section/start/newspaper_ship.jpg', '11', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('18', 'dock', '70', '47', '/images/section/start/newspaper_ship.jpg', '4', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('19', 'dock', '30', '67', '/images/section/start/newspaper_ship.jpg', '14', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('20', 'dock', '34', '69', '/images/section/start/newspaper_ship.jpg', '1', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('21', 'dock', '10', '72', '/images/section/start/newspaper_ship.jpg', '7', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('22', 'dock', '20', '80', '/images/section/start/newspaper_ship.jpg', '4', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('23', 'dock', '25', '87', '/images/section/start/newspaper_ship.jpg', '4', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('24', '', '0', '93', '/images/section/start/oil_sheikh.jpg', null, 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!'), ('25', 'drug', '20', '94', '/images/section/start/newspaper_drugs.jpg', '2', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.'), ('26', '', '0', '95', '/images/section/start/bill_gates.jpg', null, 'BILL GATES IN TOWN!', 'Bill Gates is visiting the city today to promote his new software and try some new bugs.'), ('27', '', '0', '99', '/images/section/start/warren_buffet.jpg', '0', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.'), ('28', 'drug', '20', '96', '/images/section/start/newspaper_drugs.jpg', '6', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.'), ('29', 'drug', '10', '101', '/images/section/start/newspaper_drugs.jpg', '4', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.'), ('30', '', '0', '137', '/images/section/start/oil_sheikh.jpg', null, 'OIL TYCOON OF DUBAI IS IN TOWN!', 'The oil tycoon is visiting Crim City to settle some business, tell your gang to watch this guy, cause he is loaded!'), ('31', '', '0', '138', '/images/section/start/warren_buffet.jpg', '0', 'WARREN BUFFET ON QUICK VISIT!', 'Warren Buffet is in town for the day to look for some new investments.'), ('32', 'dock', '20', '149', '/images/section/start/newspaper_ship.jpg', '4', 'HARBOUR ACTIVITY', 'A big ship has arrived in the harbour, it will stay for the day.'), ('33', 'drug', '25', '151', '/images/section/start/newspaper_drugs.jpg', '12', 'DRUG PRICES UP', 'The cops made a big bust and has confiscated a large quantity of %s, prices are going up.');

-- ----------------------------
--  Table structure for `gangnews`
-- ----------------------------
DROP TABLE IF EXISTS `gangnews`;
CREATE TABLE `gangnews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `text` mediumtext NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `gangs`
-- ----------------------------
DROP TABLE IF EXISTS `gangs`;
CREATE TABLE `gangs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(99) NOT NULL,
  `leader_id` bigint(20) NOT NULL,
  `co_leader_id` bigint(20) DEFAULT NULL,
  `presentation` text,
  `text` tinytext NOT NULL,
  `picture` tinyint(1) NOT NULL,
  `created` int(11) NOT NULL,
  `province_id` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `gangs_assaults`
-- ----------------------------
DROP TABLE IF EXISTS `gangs_assaults`;
CREATE TABLE `gangs_assaults` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_id` int(11) NOT NULL,
  `victim_id` bigint(20) NOT NULL,
  `status` enum('planning','done','aborted') NOT NULL DEFAULT 'planning',
  `created` int(11) NOT NULL,
  `accepted` tinytext NOT NULL,
  `declined` tinytext NOT NULL,
  `members` tinytext NOT NULL,
  `summoner_id` bigint(20) NOT NULL COMMENT '发起人id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `gangs_invites`
-- ----------------------------
DROP TABLE IF EXISTS `gangs_invites`;
CREATE TABLE `gangs_invites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `gang_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `gangs_robberies`
-- ----------------------------
DROP TABLE IF EXISTS `gangs_robberies`;
CREATE TABLE `gangs_robberies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_id` int(11) NOT NULL,
  `robbery_id` int(11) NOT NULL,
  `status` enum('planning','done','aborted') NOT NULL DEFAULT 'planning',
  `created` int(11) NOT NULL,
  `accepted` tinytext NOT NULL,
  `declined` tinytext NOT NULL,
  `members` tinytext NOT NULL,
  `summoner_id` bigint(20) NOT NULL COMMENT '发起人id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `gangs_users`
-- ----------------------------
DROP TABLE IF EXISTS `gangs_users`;
CREATE TABLE `gangs_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `gang_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Table structure for `groups`
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `groups`
-- ----------------------------
INSERT INTO `groups` VALUES ('1', 'administrators', '2009-03-18 10:12:51', '2009-03-18 10:12:51'), ('2', 'managers', '2009-03-18 10:12:59', '2009-03-18 10:12:59'), ('3', 'users', '2009-03-18 10:13:07', '2009-03-18 10:13:07');

-- ----------------------------
--  Table structure for `guards`
-- ----------------------------
DROP TABLE IF EXISTS `guards`;
CREATE TABLE `guards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `strength` int(11) NOT NULL,
  `photo` varchar(99) NOT NULL,
  `price` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `guards`
-- ----------------------------
INSERT INTO `guards` VALUES ('1', '20', 'evil_dog.jpg', '3000'), ('2', '80', 'guard5.jpg', '15000'), ('3', '200', 'guard4.jpg', '45000'), ('4', '350', 'guard6.jpg', '90000'), ('5', '500', 'guard3.jpg', '200000'), ('6', '800', 'guard.jpg', '600000');

-- ----------------------------
--  Table structure for `guestbooks`
-- ----------------------------
DROP TABLE IF EXISTS `guestbooks`;
CREATE TABLE `guestbooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `auth_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `guestbooks`
-- ----------------------------
INSERT INTO `guestbooks` VALUES ('1', '1', '2', '哇哈哈，第一个留言。\n\n欢迎来测试。', '1246132561');

-- ----------------------------
--  Table structure for `hookers`
-- ----------------------------
DROP TABLE IF EXISTS `hookers`;
CREATE TABLE `hookers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(99) NOT NULL,
  `photo` varchar(99) NOT NULL,
  `payoutperday` int(11) NOT NULL,
  `visitprice` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `sickprobability` float NOT NULL,
  `is_random` tinyint(1) NOT NULL DEFAULT '0',
  `stamina` smallint(3) NOT NULL DEFAULT '0',
  `spirit` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `hookers`
-- ----------------------------
INSERT INTO `hookers` VALUES ('1', 'Dolly', 'dolly.jpg', '30', '6', '400', '2009-03-12 16:48:49', '0.01', '0', '10', '1'), ('2', 'Heinrich', 'heinrich.jpg', '50', '10', '600', '2009-04-14 19:34:46', '0.01', '0', '20', '1'), ('3', 'Britney', 'hooker2.jpg', '60', '15', '800', '2009-04-14 19:34:46', '0.01', '0', '30', '1'), ('4', 'Mount Tse Tung', 'hooker4.jpg', '90', '24', '1200', '2009-04-14 19:36:22', '0.01', '0', '50', '1'), ('5', 'Marilyn', 'hooker3.jpg', '270', '46', '3500', '2009-04-14 19:36:22', '0.01', '0', '80', '1'), ('6', 'Candy', 'hooker1.jpg', '300', '55', '3900', '2009-04-14 19:37:48', '0.01', '0', '100', '2'), ('7', 'Bell', 'bell.jpg', '330', '66', '4000', '2009-04-14 19:37:48', '0.01', '0', '100', '2'), ('8', 'Patricia', 'patricia.jpg', '360', '77', '4200', '2009-04-14 19:39:12', '0.01', '0', '100', '2'), ('9', 'Claire', 'claire.jpg', '390', '86', '4500', '2009-04-14 19:39:12', '0.01', '0', '100', '2'), ('10', 'Crystal', 'crystal.jpg', '450', '96', '5200', '2009-04-14 19:40:27', '0.01', '0', '100', '2'), ('11', 'Valerie', 'valerie.jpg', '600', '116', '7000', '2009-04-14 19:40:27', '0.01', '0', '100', '2'), ('12', 'Chessy', 'chessy.jpg', '720', '130', '8400', '2009-04-14 19:41:44', '0.01', '0', '100', '2'), ('13', 'Denim Daisy', 'denim_daisy.jpg', '810', '142', '9500', '2009-04-14 19:41:44', '0.01', '0', '100', '2'), ('14', 'Head Nurse', 'head_nurse.jpg', '1050', '168', '12500', '2009-04-14 19:43:02', '0.01', '0', '100', '2'), ('15', 'Cindy', 'cindy.jpg', '1170', '182', '14000', '2009-04-14 19:43:02', '0.01', '0', '100', '2'), ('16', 'George', 'george.jpg', '1200', '195', '14500', '2009-04-14 19:44:30', '0.01', '0', '100', '2'), ('17', 'Gothic Goddess', 'gothic_goddess.jpg', '1350', '208', '15000', '2009-04-14 19:44:30', '0.01', '0', '100', '2'), ('18', 'Pearl', 'pearl.jpg', '1500', '223', '16500', '2009-04-14 20:22:12', '0.01', '0', '100', '2'), ('19', 'Miss FBI', 'miss_fbi.jpg', '2100', '270', '24500', '2009-04-14 20:22:12', '0.01', '0', '100', '2'), ('20', 'French Maid Fifi', 'fifi_french_maid.jpg', '2400', '295', '27500', '2009-04-14 20:23:44', '0.01', '0', '100', '2'), ('21', 'Darling Devil', 'darling_devil.jpg', '3000', '315', '30000', '2009-04-14 20:23:44', '0.01', '0', '100', '2'), ('22', 'Sergeant Sexy', 'sergeant_sexy.jpg', '4500', '447', '55000', '2009-04-14 20:25:02', '0.01', '0', '100', '2'), ('23', 'Jessica', 'jessica.jpg', '5400', '497', '63000', '2009-04-14 20:25:02', '0.01', '0', '100', '2'), ('24', 'Leonard', 'leonard.jpg', '6600', '543', '70000', '2009-04-14 20:26:09', '0.01', '0', '100', '2'), ('25', 'Bunnie', 'bunnie.jpg', '7500', '603', '80000', '2009-04-14 20:26:09', '0.01', '0', '100', '2'), ('26', 'Mrs. Robinson', 'mrs_robinson.jpg', '9000', '710', '100000', '2009-04-14 20:27:25', '0.01', '0', '100', '2'), ('27', 'Mr Love', 'mr_love.jpg', '12000', '967', '150000', '2009-04-14 20:27:25', '0.01', '0', '100', '2'), ('28', 'Lill &  Jill', 'lill_jill.jpg', '21000', '1428', '240000', '2009-04-14 20:28:58', '0.01', '0', '100', '2'), ('29', 'The Twins', 'the_twins.jpg', '30000', '2395', '430000', '2009-04-14 20:28:58', '0.01', '0', '100', '2'), ('30', 'Slim Susy', 'slimsusy.jpg', '42000', '3513', '650000', '2009-04-14 20:30:11', '0.01', '0', '100', '2'), ('31', 'SM Babe', 'smbabe.jpg', '54000', '4308', '800000', '2009-04-14 20:30:11', '0.01', '0', '100', '2'), ('32', 'Miss Blonde', 'missblonde.jpg', '72000', '6350', '1200000', '2009-04-14 20:31:23', '0.01', '0', '100', '2'), ('33', 'Bobbi', 'bobbi.jpg', '450', '96', '6400', '2009-05-07 13:44:44', '0', '1', '100', '2'), ('34', 'Woman of Wonder', 'woman_of_wonder.jpg', '650', '120', '9000', '2009-05-07 14:48:00', '0', '1', '100', '2'), ('35', 'Rhinogirl', 'rhinogirl.jpg', '720', '150', '10000', '2009-05-07 16:20:26', '0', '1', '100', '20');

-- ----------------------------
--  Table structure for `hospitals`
-- ----------------------------
DROP TABLE IF EXISTS `hospitals`;
CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('Intelligence','Charisma','Tolerance','Strength','Cure addiction') NOT NULL,
  `price` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `hospitals`
-- ----------------------------
INSERT INTO `hospitals` VALUES ('1', 'Intelligence', '23', '1242380898'), ('2', 'Charisma', '23', '1242381021'), ('3', 'Tolerance', '23', '1242381021'), ('4', 'Strength', '23', '1242381021'), ('5', 'Cure addiction', '500', '1242381021');

-- ----------------------------
--  Table structure for `i18n`
-- ----------------------------
DROP TABLE IF EXISTS `i18n`;
CREATE TABLE `i18n` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `locale` varchar(6) NOT NULL,
  `model` varchar(255) NOT NULL,
  `foreign_key` int(10) NOT NULL,
  `field` varchar(255) NOT NULL,
  `content` mediumtext,
  PRIMARY KEY (`id`),
  KEY `locale` (`locale`),
  KEY `model` (`model`),
  KEY `row_id` (`foreign_key`),
  KEY `field` (`field`)
) ENGINE=MyISAM AUTO_INCREMENT=411 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `i18n`
-- ----------------------------
INSERT INTO `i18n` VALUES ('1', 'chi', 'Benefit', '1', 'name', '妓女赚钱加速'), ('2', 'chi', 'Benefit', '1', 'text', '看看我的这些超级药丸啊。他们的混合了荷尔蒙，避孕药跟春药，你的小姐每个都会变成名模身材海咪咪。这些药片将会增加你10% 的收入，只需要花你 10 个点数。这些药片将会在你的小姐体内持续 28 个游戏日。'), ('3', 'chi', 'Benefit', '2', 'name', '武器增强器'), ('4', 'chi', 'Benefit', '2', 'text', '想不想要来点大家伙?我这边有些烫手的，可以增加你30%的武器威力.像我手上这件增强器可以让你 28天内威力增强不少,心动了吗?我只需要少少的 10点游戏点数.如果愿意买的话我另外免费给你个杀必速,这个润滑剂将可以让你的武器增加 30% 的威力.'), ('5', 'chi', 'Benefit', '3', 'name', '产量加速器'), ('6', 'chi', 'Benefit', '3', 'text', '如果你想的话我可以提供一些廉价劳力给你，这些孤儿只要提供给他们住所，他们将会为你工作。他们工作差些，但他们会增加你的10%的毒品产量 。你只需要 10 游戏点数就可以租用它们 28天。不要担心，如果不小心他们死掉的话，安家费已经算在里面了。'), ('7', 'chi', 'Benefit', '4', 'name', '避孕套'), ('8', 'chi', 'Benefit', '4', 'text', '过来！您这肮脏，低俗的一名男子。你能相信吗？我这边有提供卫生套，想想看得到了性病不仅会让你的生活受到影响，连日常打里事务都没办法了，如果为了你的身体健康与名声着想，多保护一下自己吧。我这边有贩卖无敌防护卫生套，它的效用远胜于市面上所有的牌子，可以完全隔绝性病对你的影响，在，你每次使用一个套子，可以让你28个游戏天内不会得到性病，每次你可以跟我买 100个，而这些只需要花你 10点的点数。'), ('9', 'chi', 'Building', '1', 'name', '烟草'), ('10', 'chi', 'Building', '2', 'name', '私酒'), ('11', 'chi', 'Building', '3', 'name', '大麻种植地'), ('12', 'chi', 'Building', '4', 'name', '酿酒厂'), ('13', 'chi', 'Building', '5', 'name', '药房'), ('14', 'chi', 'Building', '6', 'name', '蘑菇种植基地'), ('15', 'chi', 'Building', '7', 'name', '烟草种植园'), ('16', 'chi', 'Building', '8', 'name', '吗啡实验室'), ('17', 'chi', 'Building', '9', 'name', '迷幻药实验室'), ('18', 'chi', 'Building', '10', 'name', '摇头丸实验室'), ('19', 'chi', 'Building', '11', 'name', '罂粟田'), ('20', 'chi', 'Building', '12', 'name', '迷奸药实验室'), ('21', 'chi', 'Building', '13', 'name', 'K他命实验室'), ('22', 'chi', 'Building', '14', 'name', '可卡因工厂'), ('23', 'chi', 'Building', '15', 'name', '安非他命实验室'), ('24', 'chi', 'Building', '16', 'name', '海洛英工厂'), ('25', 'chi', 'Weapon', '2', 'name', '球棒'), ('26', 'chi', 'Weapon', '3', 'name', '匕首'), ('27', 'chi', 'Weapon', '4', 'name', '剑'), ('28', 'chi', 'Weapon', '5', 'name', '链锯'), ('29', 'chi', 'Weapon', '6', 'name', '格洛克'), ('30', 'chi', 'Weapon', '7', 'name', '散弹枪'), ('31', 'chi', 'Weapon', '8', 'name', 'MP5冲锋枪'), ('32', 'chi', 'Weapon', '9', 'name', 'AK 47'), ('33', 'chi', 'Weapon', '10', 'name', '乌兹冲锋枪'), ('34', 'chi', 'Weapon', '11', 'name', 'M4A1'), ('35', 'chi', 'Weapon', '12', 'name', '沙漠之鹰'), ('36', 'chi', 'Weapon', '13', 'name', '重型狙击枪'), ('37', 'chi', 'Weapon', '14', 'name', '激光枪'), ('38', 'chi', 'Weapon', '15', 'name', '重机枪'), ('39', 'chi', 'Weapon', '16', 'name', '火箭筒'), ('40', 'chi', 'Weapon', '17', 'name', '盖利步枪'), ('41', 'chi', 'Weapon', '18', 'name', '多管连发手枪'), ('42', 'chi', 'Weapon', '19', 'name', '地狱火神炮'), ('43', 'chi', 'Armor', '1', 'name', '尿布'), ('44', 'chi', 'Armor', '2', 'name', '皮夹克'), ('45', 'chi', 'Armor', '3', 'name', '合金盔甲'), ('46', 'chi', 'Armor', '4', 'name', '防弹背心'), ('47', 'chi', 'Armor', '5', 'name', '纳米战斗衣'), ('48', 'chi', 'Armor', '6', 'name', '反恐特勤装'), ('49', 'chi', 'Guard', '1', 'name', '恶狗'), ('50', 'chi', 'Guard', '2', 'name', '杀手'), ('51', 'chi', 'Guard', '3', 'name', '疯狂守卫'), ('52', 'chi', 'Guard', '4', 'name', '俄罗斯前特种兵'), ('53', 'chi', 'Guard', '5', 'name', '专业保镖'), ('54', 'chi', 'Guard', '6', 'name', '终极保镖'), ('55', 'chi', 'Business', '1', 'name', '夜总会'), ('56', 'chi', 'Business', '2', 'name', '狂欢派对'), ('57', 'chi', 'Business', '3', 'name', '青楼'), ('58', 'chi', 'Business', '4', 'name', '怡红院'), ('59', 'chi', 'Drug', '1', 'name', '止痛药'), ('60', 'chi', 'Drug', '2', 'name', '香烟'), ('61', 'chi', 'Drug', '3', 'name', '酒'), ('62', 'chi', 'Drug', '4', 'name', '迷幻蘑菇'), ('63', 'chi', 'Drug', '5', 'name', '大麻'), ('64', 'chi', 'Drug', '6', 'name', '迷幻药'), ('65', 'chi', 'Drug', '7', 'name', '迷奸药'), ('66', 'chi', 'Drug', '8', 'name', '摇头丸'), ('67', 'chi', 'Drug', '9', 'name', '安非他命'), ('68', 'chi', 'Drug', '10', 'name', '鸦片'), ('69', 'chi', 'Drug', '11', 'name', '可卡因'), ('70', 'chi', 'Drug', '12', 'name', 'K他命'), ('71', 'chi', 'Drug', '13', 'name', '吗啡'), ('72', 'chi', 'Drug', '14', 'name', '海洛英'), ('73', 'eng', 'Armor', '1', 'name', 'Diaper'), ('74', 'eng', 'Armor', '2', 'name', 'Leather Jacket'), ('75', 'eng', 'Armor', '3', 'name', 'Shining body armor'), ('76', 'eng', 'Armor', '4', 'name', 'Body armor'), ('77', 'eng', 'Armor', '5', 'name', 'Nano Fiber Combat Jacket'), ('78', 'eng', 'Armor', '6', 'name', 'Nomex plated armor'), ('79', 'eng', 'Weapon', '2', 'name', 'Baseball bat'), ('80', 'eng', 'Weapon', '3', 'name', 'Knife'), ('81', 'eng', 'Weapon', '4', 'name', 'Sword'), ('82', 'eng', 'Weapon', '5', 'name', 'Chainsaw'), ('83', 'eng', 'Weapon', '6', 'name', 'Glock'), ('84', 'eng', 'Weapon', '7', 'name', 'Shotgun'), ('85', 'eng', 'Weapon', '8', 'name', 'MP5'), ('86', 'eng', 'Weapon', '9', 'name', 'AK 47'), ('87', 'eng', 'Weapon', '10', 'name', 'Uzi'), ('88', 'eng', 'Weapon', '11', 'name', 'Colt M4A1'), ('89', 'eng', 'Weapon', '12', 'name', 'Deagle'), ('90', 'eng', 'Weapon', '13', 'name', 'Sniper rifle'), ('91', 'eng', 'Weapon', '14', 'name', 'Raygun'), ('92', 'eng', 'Weapon', '15', 'name', 'Machine Gun'), ('93', 'eng', 'Weapon', '16', 'name', 'Bazooka'), ('94', 'eng', 'Weapon', '17', 'name', 'Galil'), ('95', 'eng', 'Weapon', '18', 'name', 'BFG 9000'), ('96', 'eng', 'Weapon', '19', 'name', 'Mean Machine'), ('97', 'eng', 'Guard', '1', 'name', 'Evil dog'), ('98', 'eng', 'Guard', '2', 'name', 'The Grunt'), ('99', 'eng', 'Guard', '3', 'name', 'Mr. Mad'), ('100', 'eng', 'Guard', '4', 'name', 'Former Russian Elite Soldier'), ('101', 'eng', 'Guard', '5', 'name', 'The Professional'), ('102', 'eng', 'Guard', '6', 'name', 'Ultimate guard'), ('103', 'eng', 'Benefit', '1', 'name', 'hooker benefit'), ('104', 'eng', 'Benefit', '2', 'name', 'arms benefit'), ('105', 'eng', 'Benefit', '3', 'name', 'building benefit'), ('106', 'eng', 'Benefit', '4', 'name', 'condom benefit'), ('107', 'eng', 'Benefit', '1', 'text', 'Check out these new super pills I\'ve got for ya. They\'re a mixture of birth control, speed, and aphrodisiac and they\'ll make your hookers hump like hell. These pills will increase the income of your hookers by 10% and they only cost 10 credits. One stack of pills will last you 28 TC days.'), ('108', 'eng', 'Benefit', '2', 'text', 'Wanna buy some heavy rounds to go with that weapon? I\'ve got these armour penetrating rounds that will increase your weapons power with 30%. A case of these baddies will last you 28 TC days. How about it mate? All I\'m asking for is 10 credits in return. As a complimentary treat you\'ll get this special lubricant that will help to increase the power of your melee weapons 30% too.'), ('109', 'eng', 'Benefit', '3', 'text', 'I can offer you some real cheap labour if you wanna? These outcasts will work just for the shelter you offer them. They won\'t work hard, but they\'ll increase your production of any drug you choose by 10%. You can rent them from me for 28 TC days for the mere price of 10 credits. Don\'t worry if one ot two of them die from an inhumane working environment, that\'s already added in the price.'), ('110', 'eng', 'Benefit', '4', 'text', 'Come here you filthy, sleazy excuse for a man. Can you believe it? I\'m being paid a lousy government wage to stand here and try to sell condoms to vermin like you. I heard something about an epidemic of sexual transmittable diseases, if it were up to me I\'d leave you all to rot in your shame.\r\nGive me 10 credits and I will give you a stack of condoms. Your risk of catching a disease when you wear one of these condoms is lowered a 100 times. One stack will last you 28 TC days.'), ('111', 'eng', 'Building', '1', 'name', 'Weed plant'), ('112', 'eng', 'Building', '2', 'name', 'Moonshiner'), ('113', 'eng', 'Building', '3', 'name', 'Hash plant'), ('114', 'eng', 'Building', '4', 'name', 'Brewery'), ('115', 'eng', 'Building', '5', 'name', 'Pharmacy'), ('116', 'eng', 'Building', '6', 'name', 'Mushroom field'), ('117', 'eng', 'Building', '7', 'name', 'Weed field'), ('118', 'eng', 'Building', '8', 'name', 'Morphine Lab'), ('119', 'eng', 'Building', '9', 'name', 'LSD Lab'), ('120', 'eng', 'Building', '10', 'name', 'Ecstacy Lab'), ('121', 'eng', 'Building', '11', 'name', 'Opium field'), ('122', 'eng', 'Building', '12', 'name', 'GHB Lab'), ('123', 'eng', 'Building', '13', 'name', 'Special K Lab'), ('124', 'eng', 'Building', '14', 'name', 'Cocaine Facility'), ('125', 'eng', 'Building', '15', 'name', 'Amphetamine Lab'), ('126', 'eng', 'Building', '16', 'name', 'Heroine Facility'), ('127', 'eng', 'Business', '1', 'name', 'Nightclub'), ('128', 'eng', 'Business', '2', 'name', 'Rave party'), ('129', 'eng', 'Business', '3', 'name', 'Whorehouse'), ('130', 'eng', 'Business', '4', 'name', 'Hooker mansion'), ('131', 'chi', 'Robbery', '1', 'name', '偷窃'), ('132', 'chi', 'Robbery', '2', 'name', '抢老妇人'), ('133', 'chi', 'Robbery', '3', 'name', '偷汽車'), ('134', 'chi', 'Robbery', '4', 'name', '抢出租车'), ('135', 'chi', 'Robbery', '5', 'name', '提款机'), ('136', 'chi', 'Robbery', '6', 'name', '民宅'), ('137', 'chi', 'Robbery', '7', 'name', '加油站'), ('138', 'chi', 'Robbery', '8', 'name', '戏院'), ('139', 'chi', 'Robbery', '9', 'name', '杂货店'), ('140', 'chi', 'Robbery', '10', 'name', '24小时便利商店'), ('141', 'chi', 'Robbery', '11', 'name', '绑架'), ('142', 'chi', 'Robbery', '12', 'name', '珠宝店'), ('143', 'chi', 'Robbery', '13', 'name', '保险箱'), ('144', 'chi', 'Robbery', '14', 'name', '小银行'), ('145', 'chi', 'Robbery', '15', 'name', '帮派小头目'), ('146', 'chi', 'Robbery', '16', 'name', '汽车沙龙'), ('147', 'chi', 'Robbery', '17', 'name', 'PayPal'), ('148', 'chi', 'Robbery', '18', 'name', '地痞'), ('149', 'chi', 'Robbery', '19', 'name', '小药头'), ('150', 'chi', 'Robbery', '20', 'name', '赌场'), ('151', 'chi', 'Robbery', '21', 'name', '狂欢会'), ('152', 'chi', 'Robbery', '22', 'name', '超级市场'), ('153', 'chi', 'Robbery', '23', 'name', '博物馆'), ('154', 'chi', 'Robbery', '24', 'name', '俄国药王'), ('155', 'eng', 'Character', '1', 'name', 'Pimp'), ('156', 'eng', 'Character', '3', 'name', 'Businessman'), ('157', 'eng', 'Character', '2', 'name', 'Hitman'), ('158', 'eng', 'Character', '4', 'name', 'Robber'), ('159', 'eng', 'Character', '5', 'name', 'Gangster'), ('160', 'eng', 'Character', '1', 'desc', 'Take control of your hookers and pimp your way to wealth! The Pimp has advantages in Charisma and Tolerance.'), ('161', 'eng', 'Character', '2', 'desc', 'A vicious killer. Also a prefered gangleader because of his advantages in Strength and Tolerance.'), ('162', 'eng', 'Character', '3', 'desc', 'A Businessman is a man with courage and a sharp brain. Advantages in Intelligence and Tolerance gives him a lead in business.'), ('163', 'eng', 'Character', '4', 'desc', 'A partner in crime with advantages in Strength and Intelligence. '), ('164', 'eng', 'Character', '5', 'desc', 'The Gangster is a neutral criminal with evenly divided characteristics. '), ('165', 'chi', 'Character', '1', 'name', '领班'), ('166', 'chi', 'Character', '2', 'name', '杀手'), ('167', 'chi', 'Character', '3', 'name', '商人'), ('168', 'chi', 'Character', '4', 'name', '强盗'), ('169', 'chi', 'Character', '5', 'name', '黑社会'), ('170', 'chi', 'Character', '1', 'desc', '依靠特种行业维生是让你发财的选择! 领班拥有魅力和耐力的加值成长。'), ('171', 'chi', 'Character', '2', 'desc', '职业杀手。也最适合帮派老大，因为力量和耐力的成长快速。'), ('172', 'chi', 'Character', '3', 'desc', '生意人是一个有勇气和精明的人。拥有智力和耐力的优势使他对于事业可以快速掌握。'), ('173', 'chi', 'Character', '4', 'desc', '一个拥有力量和智慧的犯罪伙伴。'), ('174', 'chi', 'Character', '5', 'desc', '黑社会是一名平均成长的罪犯角色。'), ('175', 'eng', 'Hospital', '1', 'name', 'Brainstim'), ('176', 'eng', 'Hospital', '2', 'name', 'Love potion'), ('177', 'eng', 'Hospital', '3', 'name', 'Creatine fuel'), ('178', 'eng', 'Hospital', '4', 'name', 'Anabola'), ('179', 'eng', 'Hospital', '5', 'name', 'Methadone'), ('180', 'chi', 'Hospital', '1', 'name', '脑激素'), ('181', 'chi', 'Hospital', '2', 'name', '性激素'), ('182', 'chi', 'Hospital', '3', 'name', '肌肉生长素'), ('183', 'chi', 'Hospital', '4', 'name', '类固醇'), ('184', 'chi', 'Hospital', '5', 'name', '美沙酮'), ('185', 'eng', 'Drug', '1', 'name', 'Painkillers'), ('186', 'eng', 'Drug', '2', 'name', 'Weed'), ('187', 'eng', 'Drug', '3', 'name', 'Booze'), ('188', 'eng', 'Drug', '4', 'name', 'Magic Mushrooms'), ('189', 'eng', 'Drug', '5', 'name', 'Hash'), ('190', 'eng', 'Drug', '9', 'name', 'Amphetamine'), ('191', 'eng', 'Drug', '10', 'name', 'Opium'), ('192', 'eng', 'Drug', '11', 'name', 'Cocaine'), ('193', 'eng', 'Drug', '12', 'name', 'Special K'), ('194', 'eng', 'Drug', '13', 'name', 'Morphine'), ('195', 'eng', 'Drug', '14', 'name', 'Heroin'), ('196', 'eng', 'Drug', '6', 'name', 'LSD'), ('197', 'eng', 'Drug', '7', 'name', 'GHB'), ('198', 'eng', 'Drug', '8', 'name', 'Ecstacy'), ('199', 'chi', 'Sabotage', '1', 'name', '炸了他的工厂'), ('200', 'chi', 'Sabotage', '2', 'name', '抢走他的小姐'), ('201', 'chi', 'Sabotage', '3', 'name', '砸了他的场子'), ('202', 'eng', 'Sabotage', '1', 'name', 'Blow up buildings'), ('203', 'eng', 'Sabotage', '2', 'name', 'Hooker drive-by'), ('204', 'eng', 'Sabotage', '3', 'name', 'Rave or nightclub drive-by'), ('408', 'eng', 'Province', '33', 'name', 'Macau'), ('407', 'eng', 'Province', '32', 'name', 'HongKong'), ('406', 'eng', 'Province', '31', 'name', 'Sinkiang'), ('405', 'eng', 'Province', '30', 'name', 'Ningxia'), ('404', 'eng', 'Province', '29', 'name', 'Qinghai'), ('403', 'eng', 'Province', '28', 'name', 'Gansu'), ('402', 'eng', 'Province', '27', 'name', 'Shanxi'), ('401', 'eng', 'Province', '26', 'name', 'Tibet'), ('400', 'eng', 'Province', '25', 'name', 'Yunnan'), ('399', 'eng', 'Province', '24', 'name', 'Guizhou'), ('398', 'eng', 'Province', '23', 'name', 'Sichuan'), ('397', 'eng', 'Province', '22', 'name', 'Chongqing'), ('396', 'eng', 'Province', '21', 'name', 'Hainan'), ('395', 'eng', 'Province', '20', 'name', 'Guangxi'), ('394', 'eng', 'Province', '19', 'name', 'Guangdong'), ('393', 'eng', 'Province', '18', 'name', 'Hunan'), ('392', 'eng', 'Province', '17', 'name', 'Hubei'), ('391', 'eng', 'Province', '16', 'name', 'Henan'), ('390', 'eng', 'Province', '15', 'name', 'Shandong'), ('389', 'eng', 'Province', '14', 'name', 'Jiangxi'), ('388', 'eng', 'Province', '13', 'name', 'Fujian'), ('387', 'eng', 'Province', '12', 'name', 'Anhui'), ('386', 'eng', 'Province', '11', 'name', 'Zhejiang'), ('209', 'eng', 'Robbery', '1', 'name', 'Shoplift'), ('210', 'eng', 'Robbery', '2', 'name', 'Old lady'), ('211', 'eng', 'Robbery', '3', 'name', 'Car break-in'), ('212', 'eng', 'Robbery', '4', 'name', 'Taxi'), ('213', 'eng', 'Robbery', '5', 'name', 'Carding'), ('214', 'eng', 'Robbery', '6', 'name', 'House'), ('215', 'eng', 'Robbery', '7', 'name', 'Gas station'), ('216', 'eng', 'Robbery', '8', 'name', 'The Cinema'), ('217', 'eng', 'Robbery', '9', 'name', 'Grocery store'), ('218', 'eng', 'Robbery', '10', 'name', '7-11'), ('219', 'eng', 'Robbery', '11', 'name', 'Kidnapping'), ('220', 'eng', 'Robbery', '12', 'name', 'Jewellery'), ('221', 'eng', 'Robbery', '13', 'name', 'Safety deposit'), ('222', 'eng', 'Robbery', '14', 'name', 'Little City Bank'), ('223', 'eng', 'Robbery', '15', 'name', 'Maffia Boss'), ('224', 'eng', 'Robbery', '16', 'name', 'Car Saloon'), ('225', 'eng', 'Robbery', '17', 'name', 'Paypal'), ('226', 'eng', 'Robbery', '18', 'name', 'Local bastards'), ('227', 'eng', 'Robbery', '19', 'name', 'Local dealer'), ('228', 'eng', 'Robbery', '20', 'name', 'Casino'), ('229', 'eng', 'Robbery', '21', 'name', 'Rave party'), ('230', 'eng', 'Robbery', '22', 'name', 'Hypermarket'), ('231', 'eng', 'Robbery', '23', 'name', 'National museum'), ('232', 'eng', 'Robbery', '24', 'name', 'Russian drug king'), ('233', 'eng', 'Robbery', '25', 'name', 'Forex'), ('234', 'eng', 'Robbery', '27', 'name', 'Bank'), ('235', 'eng', 'Robbery', '28', 'name', 'Value transport'), ('236', 'eng', 'Robbery', '29', 'name', 'Federal reserve'), ('237', 'eng', 'Robbery', '30', 'name', 'Steven Seagull'), ('238', 'eng', 'Robbery', '31', 'name', 'Manipulate stock market'), ('239', 'eng', 'Robbery', '32', 'name', 'Al Capone'), ('240', 'eng', 'Robbery', '33', 'name', 'Fuckingham Palace'), ('241', 'eng', 'Robbery', '34', 'name', 'Fort Knox'), ('242', 'chi', 'Robbery', '25', 'name', '外币'), ('243', 'chi', 'Robbery', '27', 'name', '银行'), ('244', 'chi', 'Robbery', '28', 'name', '运钞车'), ('245', 'chi', 'Robbery', '29', 'name', '中央储备银行'), ('246', 'chi', 'Robbery', '30', 'name', '杜月笙'), ('247', 'chi', 'Robbery', '31', 'name', '操纵股市'), ('248', 'chi', 'Robbery', '32', 'name', '黄金荣'), ('249', 'chi', 'Robbery', '33', 'name', '他妈的烂地方'), ('250', 'chi', 'Robbery', '34', 'name', '黄埔军校'), ('251', 'eng', 'Revent', '1', 'name', 'A cop, obviously corrupted, stops you.'), ('252', 'chi', 'Revent', '1', 'name', '一个警察不怀好心的档着你的路'), ('253', 'eng', 'Revent', '2', 'name', 'A booze smelling drunk hinders your path.'), ('254', 'chi', 'Revent', '2', 'name', '一个醉汉高举酒瓶档着你的路。'), ('255', 'eng', 'Revent', '3', 'name', 'A junkie gets in your way.'), ('256', 'chi', 'Revent', '3', 'name', '一只毒虫拿着针头挡住你的路'), ('257', 'eng', 'Reventq', '1', 'name', 'I think I recognize this shape. Do you?'), ('258', 'chi', 'Reventq', '1', 'name', '我想我认得样子，你说呢？'), ('259', 'eng', 'Reventq', '2', 'name', 'I just stole this watch and I don\'t know if it works, but I\'m so wasted that I can\'t even see which is the small hand and which is the large one. Could you please tell me what time it shows?'), ('260', 'chi', 'Reventq', '2', 'name', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？'), ('261', 'eng', 'Reventq', '3', 'name', 'Oh, I forgot what they are called. Could you please tell what kind of animal that\'s inside of the cage on this picture?'), ('262', 'chi', 'Reventq', '3', 'name', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？'), ('263', 'eng', 'Reventq', '4', 'name', 'My aunt, the evil witch, is somewhat of a globetrotter. She sent me this postcard, could you please tell me what kind of area this is supposed to resemble?'), ('264', 'chi', 'Reventq', '4', 'name', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？'), ('265', 'eng', 'Reventq', '5', 'name', 'I only steal fast cars and shiny objects but I sometimes hear poeple talking about transporting themselves together with other people. For you and me that sounds pathetic but I\'m still a bit curious. What kind of so called public transportation is this?'), ('266', 'chi', 'Reventq', '5', 'name', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？'), ('267', 'eng', 'Reventq', '6', 'name', 'I just beat up a small school kid and snatched this off him, but I wasn\'t the brightest of students the few times I went to school. Could you please tell me what this is?'), ('268', 'chi', 'Reventq', '6', 'name', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？'), ('269', 'eng', 'Revent', '4', 'name', 'An old man stops you.'), ('270', 'chi', 'Revent', '4', 'name', '一个老伯步履蹒跚拦下你。'), ('271', 'eng', 'Reventa', '1', 'name', 'Africa'), ('272', 'chi', 'Reventa', '1', 'name', '非洲'), ('273', 'eng', 'Reventa', '2', 'name', 'Europe'), ('274', 'chi', 'Reventa', '2', 'name', '欧洲'), ('275', 'eng', 'Reventa', '3', 'name', 'Oceania'), ('276', 'chi', 'Reventa', '3', 'name', '大洋洲'), ('277', 'eng', 'Reventa', '4', 'name', 'South America'), ('278', 'chi', 'Reventa', '4', 'name', '南美洲'), ('279', 'eng', 'Reventa', '5', 'name', 'Asia'), ('280', 'chi', 'Reventa', '5', 'name', '亚洲'), ('281', 'eng', 'Reventa', '6', 'name', '3:10'), ('282', 'chi', 'Reventa', '6', 'name', '3:10'), ('283', 'eng', 'Reventa', '7', 'name', '5:00 '), ('284', 'chi', 'Reventa', '7', 'name', '5:00 '), ('285', 'eng', 'Reventa', '8', 'name', '8:55'), ('286', 'chi', 'Reventa', '8', 'name', '8:55'), ('287', 'eng', 'Reventa', '9', 'name', '10:30'), ('288', 'chi', 'Reventa', '9', 'name', '10:30'), ('289', 'eng', 'Reventa', '10', 'name', '11:55'), ('290', 'chi', 'Reventa', '10', 'name', '11:55'), ('291', 'eng', 'Reventa', '11', 'name', 'Monkey'), ('292', 'chi', 'Reventa', '11', 'name', '猴子'), ('293', 'eng', 'Reventa', '12', 'name', 'Bird'), ('294', 'chi', 'Reventa', '12', 'name', '鸟'), ('295', 'eng', 'Reventa', '13', 'name', 'Dog'), ('296', 'chi', 'Reventa', '13', 'name', '狗'), ('297', 'eng', 'Reventa', '14', 'name', 'Tiger'), ('298', 'chi', 'Reventa', '14', 'name', '老虎'), ('299', 'eng', 'Reventa', '15', 'name', 'Hamster'), ('300', 'chi', 'Reventa', '15', 'name', '耗子'), ('301', 'eng', 'Reventa', '16', 'name', 'Desert'), ('302', 'chi', 'Reventa', '16', 'name', '沙漠'), ('303', 'eng', 'Reventa', '17', 'name', 'Ocean'), ('304', 'chi', 'Reventa', '17', 'name', '海洋'), ('305', 'eng', 'Reventa', '18', 'name', 'Forest'), ('306', 'chi', 'Reventa', '18', 'name', '森林'), ('307', 'eng', 'Reventa', '19', 'name', 'City'), ('308', 'chi', 'Reventa', '19', 'name', '城市'), ('309', 'eng', 'Reventa', '20', 'name', 'Arctic'), ('310', 'chi', 'Reventa', '20', 'name', '北极'), ('311', 'eng', 'Reventa', '21', 'name', 'Ferry'), ('312', 'chi', 'Reventa', '21', 'name', '渡船'), ('313', 'eng', 'Reventa', '22', 'name', 'Train'), ('314', 'chi', 'Reventa', '22', 'name', '火车'), ('315', 'eng', 'Reventa', '23', 'name', 'Bus'), ('316', 'chi', 'Reventa', '23', 'name', '公共汽车'), ('317', 'eng', 'Reventa', '24', 'name', 'Airplane'), ('318', 'chi', 'Reventa', '24', 'name', '飞机'), ('319', 'eng', 'Reventa', '26', 'name', 'Ruler'), ('320', 'chi', 'Reventa', '26', 'name', '尺'), ('321', 'eng', 'Reventa', '27', 'name', 'Pen'), ('322', 'chi', 'Reventa', '27', 'name', '笔'), ('323', 'eng', 'Reventa', '28', 'name', 'Calculator'), ('324', 'chi', 'Reventa', '28', 'name', '计算器'), ('325', 'eng', 'Reventa', '29', 'name', 'Eraser'), ('326', 'chi', 'Reventa', '29', 'name', '橡皮'), ('327', 'eng', 'Reventa', '30', 'name', 'Note book'), ('328', 'chi', 'Reventa', '30', 'name', '笔记本计算机'), ('385', 'eng', 'Province', '10', 'name', 'Jiangsu'), ('384', 'eng', 'Province', '9', 'name', 'Shanghai'), ('383', 'eng', 'Province', '8', 'name', 'Heilongjiang'), ('380', 'eng', 'Province', '5', 'name', 'Inner Mongolia'), ('381', 'eng', 'Province', '6', 'name', 'Liaoning'), ('382', 'eng', 'Province', '7', 'name', 'Jilin'), ('344', 'chi', 'Province', '4', 'name', '山西'), ('342', 'chi', 'Province', '2', 'name', '天津'), ('341', 'chi', 'Province', '1', 'name', '北京'), ('379', 'eng', 'Province', '4', 'name', 'Shanxi'), ('347', 'chi', 'Province', '7', 'name', '吉林'), ('343', 'chi', 'Province', '3', 'name', '河北'), ('346', 'chi', 'Province', '6', 'name', '辽宁'), ('345', 'chi', 'Province', '5', 'name', '内蒙古'), ('350', 'chi', 'Province', '10', 'name', '江苏'), ('349', 'chi', 'Province', '9', 'name', '上海'), ('348', 'chi', 'Province', '8', 'name', '黑龙江'), ('351', 'chi', 'Province', '11', 'name', '浙江'), ('352', 'chi', 'Province', '12', 'name', '安徽'), ('353', 'chi', 'Province', '13', 'name', '福建'), ('354', 'chi', 'Province', '14', 'name', '江西'), ('363', 'chi', 'Province', '23', 'name', '四川'), ('362', 'chi', 'Province', '22', 'name', '重庆'), ('361', 'chi', 'Province', '21', 'name', '海南'), ('360', 'chi', 'Province', '20', 'name', '广西'), ('359', 'chi', 'Province', '19', 'name', '广东'), ('358', 'chi', 'Province', '18', 'name', '湖南'), ('357', 'chi', 'Province', '17', 'name', '湖北'), ('356', 'chi', 'Province', '16', 'name', '河南'), ('355', 'chi', 'Province', '15', 'name', '山东'), ('364', 'chi', 'Province', '24', 'name', '贵州'), ('377', 'eng', 'Province', '2', 'name', 'Tianjin'), ('376', 'eng', 'Province', '1', 'name', 'Peking'), ('375', 'chi', 'Province', '35', 'name', '其它'), ('374', 'chi', 'Province', '34', 'name', '台湾'), ('373', 'chi', 'Province', '33', 'name', '澳门'), ('372', 'chi', 'Province', '32', 'name', '香港'), ('371', 'chi', 'Province', '31', 'name', '新疆'), ('370', 'chi', 'Province', '30', 'name', '宁夏'), ('369', 'chi', 'Province', '29', 'name', '青海'), ('368', 'chi', 'Province', '28', 'name', '甘肃'), ('367', 'chi', 'Province', '27', 'name', '陕西'), ('366', 'chi', 'Province', '26', 'name', '西藏'), ('365', 'chi', 'Province', '25', 'name', '云南'), ('378', 'eng', 'Province', '3', 'name', 'Hebei'), ('409', 'eng', 'Province', '34', 'name', 'TaiWan'), ('410', 'eng', 'Province', '35', 'name', 'Other');

-- ----------------------------
--  Table structure for `logs`
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action` varchar(99) NOT NULL,
  `model` varchar(99) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `lotteries`
-- ----------------------------
DROP TABLE IF EXISTS `lotteries`;
CREATE TABLE `lotteries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(99) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `lotteries`
-- ----------------------------
INSERT INTO `lotteries` VALUES ('1', '1', 'root', '2010-04-17');

-- ----------------------------
--  Table structure for `messages`
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `from_id` bigint(20) NOT NULL,
  `to_id` bigint(20) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `created` int(11) NOT NULL,
  `readed` tinyint(1) NOT NULL DEFAULT '0',
  `need_i18n` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `messages`
-- ----------------------------
INSERT INTO `messages` VALUES ('1', '1', '2', '%s has accepted your relation request!', '', '1246132307', '0', '1'), ('2', '1', '2', null, '该死的 ACL 模块。\n还有，cake 的效率果然不怎么样。。。\n后悔。', '1246135057', '0', '0'), ('3', '1', '2', '%s deleted relation with you', '', '1271507413', '0', '1');

-- ----------------------------
--  Table structure for `news`
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `newspapers`
-- ----------------------------
DROP TABLE IF EXISTS `newspapers`;
CREATE TABLE `newspapers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` smallint(3) NOT NULL,
  `event` mediumtext,
  `wanted` mediumtext NOT NULL,
  `popular_club` mediumtext NOT NULL,
  `lovely_club` mediumtext NOT NULL,
  `hunt` mediumtext NOT NULL,
  `lottery` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='每日新闻';

-- ----------------------------
--  Table structure for `pictures`
-- ----------------------------
DROP TABLE IF EXISTS `pictures`;
CREATE TABLE `pictures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `prisons`
-- ----------------------------
DROP TABLE IF EXISTS `prisons`;
CREATE TABLE `prisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  `expired` int(11) NOT NULL,
  `escaped` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `profiles`
-- ----------------------------
DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `birthday` date DEFAULT NULL,
  `msn` varchar(99) DEFAULT NULL,
  `qq` varchar(20) DEFAULT NULL,
  `gender` tinyint(1) DEFAULT '2' COMMENT '0=female 1=male 2=unknown',
  `presentation` mediumtext,
  `guestbook` tinyint(1) NOT NULL DEFAULT '1',
  `lang` char(5) NOT NULL DEFAULT 'zh-cn',
  `vistors` int(11) NOT NULL DEFAULT '0',
  `province_id` smallint(2) NOT NULL DEFAULT '35',
  PRIMARY KEY (`id`),
  KEY `FK_profiles` (`user_id`),
  CONSTRAINT `FK_profiles` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `profiles`
-- ----------------------------
INSERT INTO `profiles` VALUES ('1', '1', '1975-02-07', 'floyd_joy@msn.com', '142620', '1', '[b][size=8]先把这个改掉试试看。[/size][/b]\n\n[i]看起来好像没问题。[/i]\n\n[u]BBcode 编辑器啊。[/u]\n\n[img]http://static.1930sh.com/upload/pictures/2.jpg[/img]\n[img]http://static.1930sh.com/upload/pictures/7.jpg[/img]', '1', 'zh-cn', '0', '10'), ('3', '2', '1940-01-01', '', '', '1', '', '1', 'zh-cn', '0', '9');

-- ----------------------------
--  Table structure for `provinces`
-- ----------------------------
DROP TABLE IF EXISTS `provinces`;
CREATE TABLE `provinces` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `icon` varchar(99) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `provinces`
-- ----------------------------
INSERT INTO `provinces` VALUES ('1', '京', '北京'), ('2', '津', '天津'), ('3', '冀', '河北'), ('4', '晋', '山西'), ('5', '蒙', '内蒙古自治区'), ('6', '辽', '辽宁'), ('7', '吉', '吉林'), ('8', '黑', '黑龙江'), ('9', '沪', '上海'), ('10', '苏', '江苏'), ('11', '浙', '浙江'), ('12', '皖', '安徽'), ('13', '闽', '福建'), ('14', '赣', '江西'), ('15', '鲁', '山东'), ('16', '豫', '河南'), ('17', '鄂', '湖北'), ('18', '湘', '湖南'), ('19', '粤', '广东'), ('20', '桂', '广西'), ('21', '琼', '海南'), ('22', '渝', '重庆'), ('23', '川', '四川'), ('24', '贵', '贵州'), ('25', '滇', '云南'), ('26', '藏', '西藏'), ('27', '陕', '陕西'), ('28', '甘', '甘肃'), ('29', '青', '青海'), ('30', '宁', '宁夏'), ('31', '新', '新疆'), ('32', '港', '香港'), ('33', '澳', '澳门'), ('34', '台', '台湾'), ('35', '?', '其它');

-- ----------------------------
--  Table structure for `randomevents`
-- ----------------------------
DROP TABLE IF EXISTS `randomevents`;
CREATE TABLE `randomevents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text-chi` varchar(255) NOT NULL,
  `photo_question` varchar(99) NOT NULL,
  `photo_answer` varchar(99) NOT NULL,
  `created` int(11) NOT NULL,
  `name` varchar(99) NOT NULL,
  `text` varchar(255) NOT NULL,
  `name-chi` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `randomevents`
-- ----------------------------
INSERT INTO `randomevents` VALUES ('1', '', '/images/randomevents/generalevent/cop/1.jpg', '/images/randomevents/generalevent/cop/randomeventimage.jpg', '1243017611', 'A cop, obviously corrupted, stops you.', 'I think I recognize this shape. Do you?', ''), ('2', '刚才我顺手借来了一只表，不过我这表是好的还是坏的，我实在分不出长短针，可以告诉我表上时间是几点几分吗？', '/images/randomevents/generalevent/drunk/1.jpg', '/images/randomevents/generalevent/drunk/randomeventimage.jpg', '1243022269', 'A booze smelling drunk hinders your path.', 'I just stole this watch and I don\'t know if it works, but I\'m so wasted that I can\'t even see which is the small hand and which is the large one. Could you please tell me what time it shows?', '一个醉汉高举酒瓶档着你的路。'), ('3', '欧！我忘记相片里面那关在笼子里的动物叫甚么名子了，你可以告诉我吗？', '/images/randomevents/generalevent/junkie/1.jpg', '/images/randomevents/generalevent/junkie/randomeventimage.php.jpeg', '1243024655', 'A junkie gets in your way.', 'Oh, I forgot what they are called. Could you please tell what kind of animal that\'s inside of the cage on this picture?', '一只毒虫拿着针头挡住你的路'), ('4', '我阿姨那个老巫婆，正在环游世界。她突然寄给我张明信片，拜托告诉我这里是哪里好吗？', '/images/randomevents/generalevent/cop/1.jpg', '/images/randomevents/generalevent/cop/desire.jpg', '1243079083', 'A cop, obviously corrupted, stops you.', 'My aunt, the evil witch, is somewhat of a globetrotter. She sent me this postcard, could you please tell me what kind of area this is supposed to resemble?', '一个警察不怀好心的档着你的路'), ('5', '我只会偷好车和炫丽的物体，但我有时会听到人们谈论运送自己与别人。我有点好奇，私下偷偷问你。这些人所谓的大众交通是长怎样？', '/images/randomevents/generalevent/drunk/1.jpg', '', '1243235058', 'A booze smelling drunk hinders your path.', 'I only steal fast cars and shiny objects but I sometimes hear poeple talking about transporting themselves together with other people. For you and me that sounds pathetic but I\'m still a bit curious. What kind of so called public transportation is this?', '一个醉汉高举酒瓶档着你的路。'), ('6', '我想我认得样子，你说呢？', '/images/randomevents/generalevent/junkie/1.jpg', '', '1243237709', 'A junkie gets in your way.', 'I think I recognize this shape. Do you?', '一只毒虫拿着针头挡住你的路'), ('7', '我只是一个喜欢玩闹嬉戏的小鬼头，但是以前念书不太用功。可以告诉我这是什么吗？', '', '', '1243347030', 'An old man stops you.', 'I just beat up a small school kid and snatched this off him, but I wasn\'t the brightest of students the few times I went to school. Could you please tell me what this is?', '一个老伯步履蹒跚拦下你。');

-- ----------------------------
--  Table structure for `randomevents_answers`
-- ----------------------------
DROP TABLE IF EXISTS `randomevents_answers`;
CREATE TABLE `randomevents_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `randomevent_id` int(11) NOT NULL,
  `name` varchar(99) NOT NULL,
  `name-chi` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `randomevents_answers`
-- ----------------------------
INSERT INTO `randomevents_answers` VALUES ('1', '1', 'Africa', ''), ('2', '1', 'Europe', ''), ('3', '1', 'Oceania', ''), ('4', '1', 'South America', ''), ('5', '1', 'Asia', ''), ('6', '2', '3:10', ''), ('7', '2', '5:00 ', ''), ('8', '2', '8:55', ''), ('9', '2', '10:30', ''), ('10', '2', '11:55', ''), ('11', '3', 'Monkey', '猴子'), ('12', '3', 'Bird', '鸟 '), ('13', '3', 'Dog', '狗 '), ('14', '3', 'Tiger', '老虎'), ('15', '3', 'Hamster', '耗子'), ('16', '4', 'Desert', '沙漠'), ('17', '4', 'Ocean', '海洋'), ('18', '4', 'Forest', '森林'), ('19', '4', 'City', '城市'), ('20', '4', 'Arctic', '北极'), ('21', '5', 'Ferry', '码头'), ('22', '5', 'Train', '火车'), ('23', '5', 'Bus', '公交车'), ('24', '5', 'Airplane', '飞机'), ('25', '7', 'Ruler', '尺 '), ('26', '7', 'Pen', '笔'), ('27', '7', 'Calculator', '计算器'), ('28', '7', 'Eraser', '擦子'), ('29', '7', 'Note book', '笔记本计算机');

-- ----------------------------
--  Table structure for `recruits`
-- ----------------------------
DROP TABLE IF EXISTS `recruits`;
CREATE TABLE `recruits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `recruits`
-- ----------------------------
INSERT INTO `recruits` VALUES ('1', 'admin', '2', 'kirinse@gmail.com', '喂！臭小子！来看看吧！\n\nhttp://1930sh.com/?rid=2\n\n11', '1246131303');

-- ----------------------------
--  Table structure for `reventas`
-- ----------------------------
DROP TABLE IF EXISTS `reventas`;
CREATE TABLE `reventas` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `reventq_id` smallint(3) NOT NULL,
  `photo` varchar(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `reventas`
-- ----------------------------
INSERT INTO `reventas` VALUES ('1', '1', '/images/randomevents/generalevent/africa.gif'), ('2', '1', '/images/randomevents/generalevent/europe.gif'), ('3', '1', '/images/randomevents/generalevent/oceania.jpg'), ('4', '1', '/images/randomevents/generalevent/south_america.gif'), ('5', '1', '/images/randomevents/generalevent/asia.gif'), ('6', '2', '/images/randomevents/generalevent/03-10.gif'), ('7', '2', '/images/randomevents/generalevent/05-00.jpg'), ('8', '2', '/images/randomevents/generalevent/08-55.gif'), ('9', '2', '/images/randomevents/generalevent/10-30.png'), ('10', '2', '/images/randomevents/generalevent/11-55.jpg'), ('11', '3', '/images/randomevents/generalevent/monkey.jpg'), ('12', '3', '/images/randomevents/generalevent/bird.jpg'), ('13', '3', '/images/randomevents/generalevent/dog.jpg'), ('14', '3', '/images/randomevents/generalevent/tiger.jpg'), ('15', '3', '/images/randomevents/generalevent/hamster.jpg'), ('16', '4', '/images/randomevents/generalevent/desert.jpg'), ('17', '4', '/images/randomevents/generalevent/ocean.jpg'), ('18', '4', '/images/randomevents/generalevent/forest.jpg'), ('19', '4', '/images/randomevents/generalevent/city.jpg'), ('20', '4', '/images/randomevents/generalevent/arctic.gif'), ('21', '5', '/images/randomevents/generalevent/ferry.jpg'), ('22', '5', '/images/randomevents/generalevent/train.jpg'), ('23', '5', '/images/randomevents/generalevent/bus.jpg'), ('24', '5', '/images/randomevents/generalevent/airplane.png'), ('26', '6', '/images/randomevents/generalevent/ruler.jpg'), ('27', '6', '/images/randomevents/generalevent/pen.jpg'), ('28', '6', '/images/randomevents/generalevent/calculator.jpg'), ('29', '6', '/images/randomevents/generalevent/eraser.jpg'), ('30', '6', '/images/randomevents/generalevent/notebook.jpg');

-- ----------------------------
--  Table structure for `reventqs`
-- ----------------------------
DROP TABLE IF EXISTS `reventqs`;
CREATE TABLE `reventqs` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `reventqs`
-- ----------------------------
INSERT INTO `reventqs` VALUES ('1', '1243933799'), ('2', '1243933799'), ('3', '1243933860'), ('4', '1243933860'), ('5', '1243933860'), ('6', '1243933907');

-- ----------------------------
--  Table structure for `revents`
-- ----------------------------
DROP TABLE IF EXISTS `revents`;
CREATE TABLE `revents` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `photo` varchar(99) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `revents`
-- ----------------------------
INSERT INTO `revents` VALUES ('1', '/images/randomevents/generalevent/cop/1.jpg'), ('2', '/images/randomevents/generalevent/drunk/1.jpg'), ('3', '/images/randomevents/generalevent/junkie/1.jpg'), ('4', '/images/randomevents/generalevent/oldman/1.jpg');

-- ----------------------------
--  Table structure for `rips`
-- ----------------------------
DROP TABLE IF EXISTS `rips`;
CREATE TABLE `rips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  `expired` int(11) NOT NULL,
  `escaped` tinyint(1) NOT NULL DEFAULT '0',
  `reason` enum('drug','assault','sick') NOT NULL DEFAULT 'drug',
  `victim_id` bigint(20) NOT NULL DEFAULT '0',
  `victim` varchar(161) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `robberies`
-- ----------------------------
DROP TABLE IF EXISTS `robberies`;
CREATE TABLE `robberies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stamina` int(11) NOT NULL,
  `difficulty` int(4) NOT NULL COMMENT '4-3200',
  `intelligence_min` float NOT NULL,
  `intelligence_max` float NOT NULL,
  `strength_min` float NOT NULL,
  `strength_max` float NOT NULL,
  `charisma_min` float NOT NULL,
  `charisma_max` float NOT NULL,
  `tolerance_min` float NOT NULL,
  `tolerance_max` float NOT NULL,
  `cash_min` float NOT NULL,
  `cash_max` float NOT NULL,
  `type` enum('single','gang') DEFAULT 'single',
  `require_members` smallint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `robberies`
-- ----------------------------
INSERT INTO `robberies` VALUES ('1', '5', '3', '0.2', '0.5', '0.2', '0.5', '0.2', '0.5', '0.2', '0.5', '1', '10', 'single', '0'), ('2', '10', '10', '0.5', '1.5', '0.5', '1.5', '0.5', '1.5', '0.5', '1.5', '10', '65', 'single', '0'), ('3', '10', '15', '0.5', '2', '0.5', '2', '0.5', '2', '0.5', '2', '40', '110', 'single', '0'), ('4', '10', '25', '1', '4', '1', '4', '1', '4', '1', '4', '90', '200', 'single', '0'), ('5', '10', '40', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '50', '250', 'single', '0'), ('6', '12', '45', '1.2', '5', '1.2', '5', '1.2', '5', '1.2', '5', '140', '360', 'single', '0'), ('7', '14', '55', '1.4', '4.5', '1.4', '4.5', '1.4', '4.5', '1.4', '4.5', '150', '550', 'single', '0'), ('8', '15', '65', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '300', '700', 'single', '0'), ('9', '16', '70', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '1.5', '5.5', '300', '900', 'single', '0'), ('10', '18', '100', '1.5', '6', '1.5', '6', '1.5', '6', '1.5', '6', '400', '1400', 'single', '0'), ('11', '20', '170', '0.4', '1.5', '0.4', '1.5', '0.4', '1.5', '0.4', '1.5', '1000', '2500', 'single', '0'), ('12', '25', '250', '0.7', '5', '0.7', '5', '0.7', '5', '0.7', '5', '1200', '4500', 'single', '0'), ('13', '27', '300', '0.8', '3.2', '0.8', '3.2', '0.8', '3.2', '0.8', '3.2', '2800', '5800', 'single', '0'), ('14', '30', '370', '1', '2.1', '1.2', '4.2', '1.1', '2.5', '1.4', '3.1', '2400', '6500', 'single', '0'), ('15', '35', '480', '1.5', '3', '1.8', '4', '1.2', '2.7', '1.8', '4.8', '5000', '12000', 'single', '0'), ('16', '40', '570', '1.5', '5.7', '1.5', '5.7', '1.5', '5.7', '1.5', '5.7', '5000', '17000', 'single', '0'), ('17', '45', '640', '1.5', '6', '1.5', '6', '1.5', '6', '1.5', '6', '5500', '15000', 'single', '0'), ('18', '50', '770', '4', '6', '4', '6', '4', '6', '4', '6', '8000', '10000', 'single', '0'), ('19', '60', '880', '5', '6', '5', '6', '5', '6', '5', '6', '10000', '15000', 'single', '0'), ('20', '65', '980', '5', '7', '5', '7', '5', '7', '5', '7', '15000', '30000', 'single', '0'), ('21', '70', '1150', '6', '7', '6', '7', '6', '7', '6', '7', '30000', '50000', 'single', '0'), ('22', '75', '1430', '1.2', '6', '1.2', '6', '1.2', '6', '1.2', '6', '35000', '80000', 'single', '0'), ('23', '80', '2700', '80', '150', '80', '150', '80', '150', '80', '150', '80000', '150000', 'single', '0'), ('24', '80', '3200', '100', '200', '100', '200', '100', '200', '100', '200', '150000', '300000', 'single', '0'), ('25', '30', '70', '0.5', '3.5', '0.5', '3.5', '0.5', '3.5', '0.5', '3.5', '4000', '14000', 'gang', '2'), ('27', '30', '160', '5', '10', '5', '10', '5', '10', '5', '10', '800', '1100', 'gang', '4'), ('28', '30', '300', '6', '12', '6', '12', '6', '12', '6', '12', '1200', '1500', 'gang', '3'), ('29', '30', '900', '25', '40', '25', '40', '25', '40', '25', '40', '10000', '15000', 'gang', '6'), ('30', '30', '2000', '40', '65', '40', '65', '40', '65', '40', '65', '30000', '50000', 'gang', '7'), ('31', '30', '2500', '50', '80', '50', '80', '50', '80', '50', '80', '50000', '80000', 'gang', '9'), ('32', '80', '4500', '80', '150', '80', '150', '80', '150', '80', '150', '80000', '150000', 'gang', '10'), ('33', '80', '8000', '100', '200', '100', '200', '100', '200', '100', '200', '150000', '300000', 'gang', '14'), ('34', '80', '15000', '100', '200', '100', '200', '100', '200', '100', '200', '150000', '300000', 'gang', '17');

-- ----------------------------
--  Table structure for `sabotages`
-- ----------------------------
DROP TABLE IF EXISTS `sabotages`;
CREATE TABLE `sabotages` (
  `id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `stamina` smallint(3) NOT NULL,
  `difficulty` smallint(4) NOT NULL,
  `pay` smallint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `sabotages`
-- ----------------------------
INSERT INTO `sabotages` VALUES ('1', '15', '1000', '500'), ('2', '10', '200', '100'), ('3', '20', '2500', '1250');

-- ----------------------------
--  Table structure for `sabotages_plans`
-- ----------------------------
DROP TABLE IF EXISTS `sabotages_plans`;
CREATE TABLE `sabotages_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sabotage_id` tinyint(1) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `victim_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  `status` enum('planning','performed','aborted') NOT NULL DEFAULT 'planning',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `sabotages_plans`
-- ----------------------------
INSERT INTO `sabotages_plans` VALUES ('1', '1', '1', '3', '1243015444', 'aborted'), ('2', '1', '1', '3', '1243016618', 'aborted');

-- ----------------------------
--  Table structure for `sessions`
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL DEFAULT '',
  `data` text,
  `expires` int(11) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT '0',
  `useragent` varchar(255) NOT NULL,
  `nightlife_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `useragent` (`useragent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `sessions`
-- ----------------------------
INSERT INTO `sessions` VALUES ('02top3eckj7ged5b2gv11lnec1', 'Config|a:4:{s:9:\"userAgent\";s:32:\"59ea6cb459a27e0c947d534e0cfabc6b\";s:4:\"time\";i:1271590311;s:4:\"rand\";i:76055462;s:7:\"timeout\";i:10;}gifcaptcha|s:5:\"kd0vh\";Message|a:0:{}Auth|a:1:{s:8:\"redirect\";s:9:\"/register\";}', '1271590311', '0', '59ea6cb459a27e0c947d534e0cfabc6b', '0'), ('c1pbcidb0mdp5url66fohl3s86', 'Config|a:4:{s:9:\"userAgent\";s:32:\"921f3560ab9334c8eee4b91c01230625\";s:4:\"time\";i:1271590165;s:4:\"rand\";i:1787037311;s:7:\"timeout\";i:10;}', '1271590165', '0', '921f3560ab9334c8eee4b91c01230625', '0'), ('camstbo2b8sbsijkb6k1rfg5b5', 'Config|a:4:{s:9:\"userAgent\";s:32:\"b331e7150b6019e32aaa7ca62ff8573d\";s:4:\"time\";i:1271591819;s:4:\"rand\";i:1875316974;s:7:\"timeout\";i:10;}', '1271591819', '0', 'b331e7150b6019e32aaa7ca62ff8573d', '0'), ('lr1ar2ncao637lli1sbtjidal4', 'Config|a:4:{s:9:\"userAgent\";s:32:\"22843748afcb04dd2e486255603bd274\";s:4:\"time\";i:1271592037;s:4:\"rand\";i:1228035848;s:7:\"timeout\";i:10;}', '1271592038', '0', '22843748afcb04dd2e486255603bd274', '0'), ('r0sv0m8nla2e5h7h0almfvmi11', 'Config|a:5:{s:9:\"userAgent\";s:32:\"f01c9c140acaa0bab690c60426b9e600\";s:4:\"time\";i:1271509049;s:4:\"rand\";i:232149719;s:7:\"timeout\";i:10;s:2:\"ip\";s:9:\"127.0.0.1\";}Message|a:0:{}gifcaptcha|s:5:\"hf1cd\";Auth|a:1:{s:8:\"redirect\";s:7:\"/prison\";}', '1271509049', '0', 'f01c9c140acaa0bab690c60426b9e600', '0');

-- ----------------------------
--  Table structure for `startbenefits`
-- ----------------------------
DROP TABLE IF EXISTS `startbenefits`;
CREATE TABLE `startbenefits` (
  `id` smallint(1) NOT NULL AUTO_INCREMENT,
  `credits` smallint(2) NOT NULL,
  `cash` mediumint(6) NOT NULL,
  `weapon_id` smallint(2) NOT NULL,
  `hookers` varchar(99) NOT NULL,
  `created` int(11) NOT NULL,
  `name` varchar(99) NOT NULL,
  `text` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `startbenefits`
-- ----------------------------
INSERT INTO `startbenefits` VALUES ('1', '20', '19500', '7', '1,3', '1243554166', 'Mafioso Package', 'The Mafioso Package is the start package for elite and serious players. This package will give you a lot of stats and money at once and you will also get a carefully selected weapon that will blow away your enemies.'), ('2', '10', '7800', '6', '', '1243554166', 'Gangster Package', 'The Gangster Package will give you extra stats and money. This will help you to get a really good start.');

-- ----------------------------
--  Table structure for `stats`
-- ----------------------------
DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `kills` int(11) NOT NULL DEFAULT '0',
  `respect` float(10,3) NOT NULL DEFAULT '5.000',
  `spirit` smallint(1) NOT NULL DEFAULT '5',
  `intelligence` float(10,3) NOT NULL DEFAULT '5.000',
  `strength` float(10,3) NOT NULL DEFAULT '5.000',
  `charisma` float(10,3) NOT NULL DEFAULT '5.000',
  `tolerance` float(10,3) NOT NULL DEFAULT '5.000',
  `cash` int(11) NOT NULL DEFAULT '150',
  `credits` int(11) NOT NULL DEFAULT '0',
  `stamina` int(3) NOT NULL DEFAULT '100',
  `skill` float(8,3) NOT NULL DEFAULT '5.000',
  `proficiency_melee` int(11) NOT NULL DEFAULT '1',
  `proficiency_handgun` int(11) NOT NULL DEFAULT '1',
  `proficiency_rifle` int(11) NOT NULL DEFAULT '1',
  `proficiency_heavy` int(11) NOT NULL DEFAULT '1',
  `tickets` mediumint(6) NOT NULL DEFAULT '100',
  `yen` smallint(4) NOT NULL DEFAULT '0',
  `prison_released` int(11) NOT NULL DEFAULT '0',
  `rip_released` int(11) NOT NULL DEFAULT '0',
  `rip_reason` varchar(255) DEFAULT NULL,
  `in_prison` tinyint(1) NOT NULL DEFAULT '0',
  `in_rip` tinyint(1) NOT NULL DEFAULT '0',
  `gangs_new` tinyint(1) NOT NULL DEFAULT '0',
  `messages_new` tinyint(1) NOT NULL DEFAULT '0',
  `fellas_new` tinyint(1) NOT NULL DEFAULT '0',
  `gang_id` int(11) NOT NULL DEFAULT '0',
  `stamina_start` int(11) NOT NULL DEFAULT '0',
  `next_character_id` int(11) NOT NULL DEFAULT '0',
  `next_character_time` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `drug_deals` smallint(2) NOT NULL DEFAULT '15',
  PRIMARY KEY (`id`),
  KEY `FK_stats` (`user_id`),
  KEY `respect` (`respect`),
  CONSTRAINT `FK_stats` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `stats`
-- ----------------------------
INSERT INTO `stats` VALUES ('1', '1', '0', '14.436', '9', '14.455', '13.715', '14.471', '15.056', '280', '9969', '82', '14.406', '1', '1', '13', '1', '100', '0', '0', '0', null, '0', '0', '0', '0', '0', '0', '1271508430', '2', '1271550730', '0', '15'), ('2', '2', '0', '13.161', '5', '9.813', '14.529', '13.798', '14.447', '305', '0', '1', '12.596', '1', '1', '1', '1', '100', '0', '0', '0', null, '0', '0', '0', '1', '1', '0', '1246135075', '0', '0', '0', '15');

-- ----------------------------
--  Table structure for `temprelations`
-- ----------------------------
DROP TABLE IF EXISTS `temprelations`;
CREATE TABLE `temprelations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `victim_id` bigint(20) NOT NULL,
  `created` int(11) NOT NULL,
  `expired` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(99) NOT NULL,
  `email` varchar(99) NOT NULL,
  `password` char(40) NOT NULL,
  `group_id` int(11) NOT NULL,
  `file_avatar` varchar(99) NOT NULL,
  `character_id` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  `actived` tinyint(1) DEFAULT '0',
  `info_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `benefited` tinyint(1) DEFAULT NULL,
  `rabbit_mode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `users`
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'root', 'c-mtv@163.com', 'ad19b3cc684381328508a90bd86704849e357fe8', '1', '/upload/avatars/1.jpg', '5', '1237371317', '1245522509', '1', '1', '0', '0'), ('2', 'admin', 'info@1930sh.com', '143da444b9a0c45948192bc034cd7b2e288dfc30', '3', '/images/avatar/avatar_2.jpg', '2', '1246128252', '1246128653', '1', '1', '0', '0');

-- ----------------------------
--  Table structure for `users_armors`
-- ----------------------------
DROP TABLE IF EXISTS `users_armors`;
CREATE TABLE `users_armors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `armor_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_benefits`
-- ----------------------------
DROP TABLE IF EXISTS `users_benefits`;
CREATE TABLE `users_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `benefit_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `expired` int(11) NOT NULL,
  `drug_id` smallint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_buildings`
-- ----------------------------
DROP TABLE IF EXISTS `users_buildings`;
CREATE TABLE `users_buildings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(99) DEFAULT NULL,
  `building_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  `units` int(11) NOT NULL,
  `output_drug_id` smallint(3) NOT NULL COMMENT '产出的毒品id',
  `outputs` int(11) NOT NULL COMMENT '产出量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_businesses`
-- ----------------------------
DROP TABLE IF EXISTS `users_businesses`;
CREATE TABLE `users_businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `business_id` int(11) NOT NULL,
  `income` int(11) NOT NULL DEFAULT '0',
  `created` int(10) NOT NULL,
  `name` varchar(99) DEFAULT NULL,
  `max_respect` int(11) NOT NULL DEFAULT '0',
  `entrance_fee` int(11) NOT NULL DEFAULT '20',
  `description` varchar(255) NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_businesses_log`
-- ----------------------------
DROP TABLE IF EXISTS `users_businesses_log`;
CREATE TABLE `users_businesses_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT '0',
  `users_business_id` int(11) NOT NULL,
  `income` smallint(3) NOT NULL,
  `created` int(11) NOT NULL,
  `exited` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_business_id` (`users_business_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_businesses_users_drugs`
-- ----------------------------
DROP TABLE IF EXISTS `users_businesses_users_drugs`;
CREATE TABLE `users_businesses_users_drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_business_id` int(11) NOT NULL,
  `users_drug_id` int(11) NOT NULL,
  `price` smallint(3) NOT NULL DEFAULT '0',
  `sold` mediumint(6) NOT NULL DEFAULT '0',
  `removed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_drugs`
-- ----------------------------
DROP TABLE IF EXISTS `users_drugs`;
CREATE TABLE `users_drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `units` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_fav_businesses`
-- ----------------------------
DROP TABLE IF EXISTS `users_fav_businesses`;
CREATE TABLE `users_fav_businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `user_business_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_guards`
-- ----------------------------
DROP TABLE IF EXISTS `users_guards`;
CREATE TABLE `users_guards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `guard_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_hookers`
-- ----------------------------
DROP TABLE IF EXISTS `users_hookers`;
CREATE TABLE `users_hookers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `hooker_id` int(11) NOT NULL,
  `users_business_id` int(11) NOT NULL DEFAULT '0',
  `visitprice` smallint(4) NOT NULL DEFAULT '0',
  `payoutperday` smallint(5) NOT NULL DEFAULT '0',
  `income` int(11) NOT NULL DEFAULT '0',
  `freetime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hooker_id` (`hooker_id`),
  KEY `users_business_id` (`users_business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_users`
-- ----------------------------
DROP TABLE IF EXISTS `users_users`;
CREATE TABLE `users_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `friend_id` bigint(20) NOT NULL,
  `ignored` tinyint(1) NOT NULL DEFAULT '0',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `users_weapons`
-- ----------------------------
DROP TABLE IF EXISTS `users_weapons`;
CREATE TABLE `users_weapons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `weapon_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  `used` int(5) NOT NULL DEFAULT '0' COMMENT '使用次数',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `weapon_id` (`weapon_id`),
  CONSTRAINT `users_weapons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_weapons_ibfk_2` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `weapons`
-- ----------------------------
DROP TABLE IF EXISTS `weapons`;
CREATE TABLE `weapons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo` varchar(99) NOT NULL,
  `min_damage` int(11) NOT NULL,
  `max_damage` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `repair_price` int(11) NOT NULL COMMENT '暂时保留，维修价格＝价格*损失百分比',
  `skill` int(5) NOT NULL COMMENT '技能建议值',
  `proficiency` int(5) NOT NULL COMMENT '精通建议值',
  `type` enum('Melee','Rifle','Handgun','Heavy') NOT NULL,
  `durability` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `weapons`
-- ----------------------------
INSERT INTO `weapons` VALUES ('2', 'monkeybat.jpg', '8', '10', '120', '10', '1', '1', 'Melee', '12'), ('3', 'knife.jpg', '8', '20', '300', '30', '1', '10', 'Melee', '30'), ('4', 'sword.jpg', '15', '25', '600', '60', '1', '30', 'Melee', '60'), ('5', 'chainsaw.jpg', '12', '30', '660', '66', '10', '50', 'Melee', '66'), ('6', 'glock.jpg', '20', '40', '1350', '135', '20', '40', 'Handgun', '135'), ('7', 'shotgun.jpg', '26', '65', '3100', '310', '100', '50', 'Rifle', '310'), ('8', 'mp5.jpg', '42', '70', '4700', '470', '150', '100', 'Handgun', '470'), ('9', 'ak47.jpg', '45', '75', '5400', '540', '200', '130', 'Rifle', '540'), ('10', 'uzi.jpg', '30', '100', '6300', '630', '250', '200', 'Handgun', '630'), ('11', 'coltm4a1.jpg', '45', '90', '6800', '680', '300', '220', 'Rifle', '680'), ('12', 'deagle.jpg', '68', '85', '8800', '880', '400', '300', 'Handgun', '880'), ('13', 'sniper.jpg', '110', '110', '18000', '1800', '700', '500', 'Rifle', '180'), ('14', 'raygun.jpg', '180', '300', '85000', '8500', '1000', '800', 'Handgun', '850'), ('15', 'machine_gun.jpg', '250', '500', '210000', '21000', '1500', '800', 'Heavy', '210'), ('16', 'bazooka.jpg', '560', '800', '695000', '69500', '2000', '1500', 'Heavy', '695'), ('17', 'gail.jpg', '840', '1200', '1560000', '156000', '2800', '1500', 'Rifle', '780'), ('18', 'bfg.jpg', '720', '1800', '2400000', '240000', '3000', '1750', 'Handgun', '1200'), ('19', 'extreme.jpg', '1400', '2000', '4400000', '440000', '4500', '2200', 'Heavy', '2200');

