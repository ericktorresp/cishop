/*
MySQL Data Transfer
Source Host: localhost
Source Database: hxxpscom_video
Target Host: localhost
Target Database: hxxpscom_video
Date: 2010/11/8 17:43:42
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for categories
-- ----------------------------
CREATE TABLE `categories` (
  `cid` int(11) NOT NULL auto_increment,
  `ctitle` varchar(99) character set latin1 NOT NULL,
  `ctime` int(11) NOT NULL,
  `count` int(11) NOT NULL default '0',
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cid`),
  UNIQUE KEY `title` (`ctitle`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ci_sessions
-- ----------------------------
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for members
-- ----------------------------
CREATE TABLE `members` (
  `uid` int(11) NOT NULL default '0',
  `username` varchar(99) NOT NULL,
  `password` varchar(99) NOT NULL,
  `validate_start` date NOT NULL,
  `validate_ends` date NOT NULL,
  `is_admin` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for servers
-- ----------------------------
CREATE TABLE `servers` (
  `sid` int(11) NOT NULL auto_increment,
  `domain` varchar(99) NOT NULL,
  `ip` varchar(15) default NULL,
  `actived` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for video_rates
-- ----------------------------
CREATE TABLE `video_rates` (
  `vrid` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `vtime` datetime NOT NULL,
  `score` tinyint(1) NOT NULL,
  PRIMARY KEY  (`vrid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for video_views
-- ----------------------------
CREATE TABLE `video_views` (
  `vvid` int(11) NOT NULL auto_increment,
  `vid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `vday` date NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY  (`vvid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for videos
-- ----------------------------
CREATE TABLE `videos` (
  `vid` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL COMMENT '分类id',
  `title` varchar(99) character set latin1 NOT NULL,
  `key` varchar(16) character set latin1 NOT NULL,
  `description` mediumtext character set latin1,
  `file_name` varchar(99) character set latin1 NOT NULL,
  `width` smallint(5) NOT NULL,
  `height` smallint(5) NOT NULL,
  `duration` varchar(8) NOT NULL,
  `ctime` int(10) NOT NULL,
  `is_fetured` tinyint(1) NOT NULL default '0',
  `rate` smallint(5) NOT NULL default '0',
  `server` varchar(100) NOT NULL default '1' COMMENT '服务器域名或ip',
  `published` tinyint(1) NOT NULL default '0',
  `mime` char(3) NOT NULL default 'flv',
  PRIMARY KEY  (`vid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `categories` VALUES ('1', 'Asian', '0', '2', '0');
INSERT INTO `categories` VALUES ('2', 'Bbbbb', '0', '5', '2');
INSERT INTO `categories` VALUES ('4', 'Hardcore', '1288841398', '1', '3');
INSERT INTO `ci_sessions` VALUES ('c70c593dd6caf62b8dba078b03317140', '127.0.0.1', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv', '1289201828', null);
INSERT INTO `members` VALUES ('1', 'root', 'c5769e3620c3ee2bd879b16c905d948e', '0000-00-00', '0000-00-00', '1');
INSERT INTO `members` VALUES ('2', 'darkmoon', '88b5fdd9c49c4657ecaa4deede0f106f', '0000-00-00', '0000-00-00', '0');
INSERT INTO `servers` VALUES ('1', 'ph918.com', null, '1');
INSERT INTO `servers` VALUES ('5', 'v.hxxps.us', '', '1');
INSERT INTO `video_views` VALUES ('1', '1', '1', '2010-11-01', '322');
INSERT INTO `video_views` VALUES ('2', '1', '1', '2010-11-02', '231');
INSERT INTO `video_views` VALUES ('3', '1', '1', '2010-11-03', '21');
INSERT INTO `video_views` VALUES ('4', '1', '1', '2010-11-04', '23');
INSERT INTO `video_views` VALUES ('5', '1', '1', '2010-11-05', '12');
INSERT INTO `video_views` VALUES ('6', '1', '1', '2010-11-06', '23223');
INSERT INTO `video_views` VALUES ('7', '1', '1', '2010-11-07', '23423');
INSERT INTO `videos` VALUES ('1', '1', 'testing', 'O5BgkhRUmJOL', null, '', '1366', '768', '00:34:23', '1288688091', '0', '0', 'v.hxxps.us', '0', 'f4v');
INSERT INTO `videos` VALUES ('2', '1', 'testing', 'cETmn2n0LdO9', null, '', '1366', '768', '0', '1288690284', '0', '0', 'ph918.com', '0', 'flv');
INSERT INTO `videos` VALUES ('3', '2', 'testing 2', 'GCi5wot5xfsT', null, '', '1366', '768', '0', '1288756716', '0', '0', 'ph918.com', '0', 'flv');
INSERT INTO `videos` VALUES ('5', '2', 'testing 2', 'WSqcXxONUMw0', null, '', '1366', '768', '0', '1288763883', '0', '0', 'ph918.com', '0', 'flv');
INSERT INTO `videos` VALUES ('6', '1', 'testing 22132', 'fZTz2oHftrhz', null, '', '1366', '768', '', '1288768684', '1', '0', 'ph918.com', '1', 'flv');
INSERT INTO `videos` VALUES ('10', '2', 'testing 22132sdf', 'XYs9ug3fSlC0', null, '', '320', '240', '00:34:23', '1288863445', '0', '0', 'v.hxxps.us', '1', 'f4v');
