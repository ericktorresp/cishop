/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50139
 Source Host           : localhost
 Source Database       : hxxpscom_video

 Target Server Type    : MySQL
 Target Server Version : 50139
 File Encoding         : utf-8

 Date: 11/07/2010 23:29:28 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `categories`
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `ctitle` varchar(99) CHARACTER SET latin1 NOT NULL,
  `ctime` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  UNIQUE KEY `title` (`ctitle`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `categories`
-- ----------------------------
INSERT INTO `categories` VALUES ('1', 'Asian', '0', '0', '0'), ('2', 'Bbbbb', '0', '0', '0');

-- ----------------------------
--  Table structure for `ci_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `ci_sessions`
-- ----------------------------
INSERT INTO `ci_sessions` VALUES ('1d140055b0edf8dc6607ae7ec4609d3a', '127.0.0.1', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; zh', '1289143613', 'a:6:{s:3:\"uid\";s:1:\"1\";s:8:\"username\";s:4:\"root\";s:8:\"password\";s:32:\"c5769e3620c3ee2bd879b16c905d948e\";s:14:\"validate_start\";s:10:\"0000-00-00\";s:13:\"validate_ends\";s:10:\"0000-00-00\";s:8:\"is_admin\";s:1:\"1\";}');

-- ----------------------------
--  Table structure for `members`
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(99) NOT NULL,
  `password` varchar(99) NOT NULL,
  `validate_start` date NOT NULL,
  `validate_ends` date NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `members`
-- ----------------------------
INSERT INTO `members` VALUES ('1', 'root', 'c5769e3620c3ee2bd879b16c905d948e', '0000-00-00', '0000-00-00', '1'), ('2', 'darkmoon', '88b5fdd9c49c4657ecaa4deede0f106f', '0000-00-00', '0000-00-00', '0');

-- ----------------------------
--  Table structure for `servers`
-- ----------------------------
DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(99) NOT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `actived` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
--  Records of `servers`
-- ----------------------------
INSERT INTO `servers` VALUES ('1', 'ph918.com', null, '1'), ('5', 'v.hxxps.us', '', '1');

-- ----------------------------
--  Table structure for `video_files`
-- ----------------------------
DROP TABLE IF EXISTS `video_files`;
CREATE TABLE `video_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` int(2) NOT NULL,
  `file_conversion_log` text CHARACTER SET latin1 NOT NULL,
  `encoder` char(16) CHARACTER SET latin1 NOT NULL,
  `command_used` text CHARACTER SET latin1 NOT NULL,
  `src_path` text CHARACTER SET latin1 NOT NULL,
  `src_name` char(64) CHARACTER SET latin1 NOT NULL,
  `src_ext` char(8) CHARACTER SET latin1 NOT NULL,
  `src_format` char(32) CHARACTER SET latin1 NOT NULL,
  `src_duration` char(10) CHARACTER SET latin1 NOT NULL,
  `src_size` char(10) CHARACTER SET latin1 NOT NULL,
  `src_bitrate` char(6) CHARACTER SET latin1 NOT NULL,
  `src_video_width` char(5) CHARACTER SET latin1 NOT NULL,
  `src_video_height` char(5) CHARACTER SET latin1 NOT NULL,
  `src_video_wh_ratio` char(10) CHARACTER SET latin1 NOT NULL,
  `src_video_codec` char(16) CHARACTER SET latin1 NOT NULL,
  `src_video_rate` char(10) CHARACTER SET latin1 NOT NULL,
  `src_video_bitrate` char(10) CHARACTER SET latin1 NOT NULL,
  `src_video_color` char(16) CHARACTER SET latin1 NOT NULL,
  `src_audio_codec` char(16) CHARACTER SET latin1 NOT NULL,
  `src_audio_bitrate` char(10) CHARACTER SET latin1 NOT NULL,
  `src_audio_rate` char(10) CHARACTER SET latin1 NOT NULL,
  `src_audio_channels` char(16) CHARACTER SET latin1 NOT NULL,
  `output_path` text CHARACTER SET latin1 NOT NULL,
  `output_format` char(32) CHARACTER SET latin1 NOT NULL,
  `output_duration` char(10) CHARACTER SET latin1 NOT NULL,
  `output_size` char(10) CHARACTER SET latin1 NOT NULL,
  `output_bitrate` char(6) CHARACTER SET latin1 NOT NULL,
  `output_video_width` char(5) CHARACTER SET latin1 NOT NULL,
  `output_video_height` char(5) CHARACTER SET latin1 NOT NULL,
  `output_video_wh_ratio` char(10) CHARACTER SET latin1 NOT NULL,
  `output_video_codec` char(16) CHARACTER SET latin1 NOT NULL,
  `output_video_rate` char(10) CHARACTER SET latin1 NOT NULL,
  `output_video_bitrate` char(10) CHARACTER SET latin1 NOT NULL,
  `output_video_color` char(16) CHARACTER SET latin1 NOT NULL,
  `output_audio_codec` char(16) CHARACTER SET latin1 NOT NULL,
  `output_audio_bitrate` char(10) CHARACTER SET latin1 NOT NULL,
  `output_audio_rate` char(10) CHARACTER SET latin1 NOT NULL,
  `output_audio_channels` char(16) CHARACTER SET latin1 NOT NULL,
  `hd` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  `hq` enum('yes','no') CHARACTER SET latin1 NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `src_bitrate` (`src_bitrate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `video_rates`
-- ----------------------------
DROP TABLE IF EXISTS `video_rates`;
CREATE TABLE `video_rates` (
  `vrid` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `vtime` datetime NOT NULL,
  `score` tinyint(1) NOT NULL,
  PRIMARY KEY (`vrid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `video_views`
-- ----------------------------
DROP TABLE IF EXISTS `video_views`;
CREATE TABLE `video_views` (
  `vvid` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `vtime` datetime NOT NULL,
  PRIMARY KEY (`vvid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `videos`
-- ----------------------------
DROP TABLE IF EXISTS `videos`;
CREATE TABLE `videos` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '分类id',
  `title` varchar(99) CHARACTER SET latin1 NOT NULL,
  `key` varchar(16) CHARACTER SET latin1 NOT NULL,
  `description` mediumtext CHARACTER SET latin1,
  `file_name` varchar(99) CHARACTER SET latin1 NOT NULL,
  `width` smallint(5) NOT NULL,
  `height` smallint(5) NOT NULL,
  `duration` varchar(8) NOT NULL,
  `ctime` int(10) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `is_fetured` tinyint(1) NOT NULL DEFAULT '0',
  `rate` smallint(5) NOT NULL DEFAULT '0',
  `server` varchar(100) NOT NULL DEFAULT '1' COMMENT '服务器域名或ip',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `mime` char(3) NOT NULL DEFAULT 'flv',
  PRIMARY KEY (`vid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `videos`
-- ----------------------------
INSERT INTO `videos` VALUES ('1', '1', 'testing', 'O5BgkhRUmJOL', null, '', '1366', '768', '00:34:23', '1288688091', '0', '0', '0', 'v.hxxps.us', '0', 'f4v'), ('2', '1', 'testing', 'cETmn2n0LdO9', null, '', '1366', '768', '0', '1288690284', '0', '0', '0', 'ph918.com', '0', 'flv'), ('3', '2', 'testing 2', 'GCi5wot5xfsT', null, '', '1366', '768', '0', '1288756716', '2', '0', '0', 'ph918.com', '0', 'flv'), ('5', '2', 'testing 2', 'WSqcXxONUMw0', null, '', '1366', '768', '0', '1288763883', '2', '0', '0', 'ph918.com', '0', 'flv'), ('6', '1', 'testing 22132', 'fZTz2oHftrhz', null, '', '1366', '768', '', '1288768684', '2', '1', '0', 'ph918.com', '1', 'flv'), ('10', '2', 'testing 22132sdf', 'XYs9ug3fSlC0', null, '', '320', '240', '00:34:23', '1288863445', '232', '0', '0', 'v.hxxps.us', '1', 'f4v');

