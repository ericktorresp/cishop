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

 Date: 04/07/2011 23:42:11 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

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

