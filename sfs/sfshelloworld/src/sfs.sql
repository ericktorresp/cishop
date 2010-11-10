/*
MySQL Data Transfer
Source Host: localhost
Source Database: sfs
Target Host: localhost
Target Database: sfs
Date: 2010/11/10 18:02:14
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE `users` (
  `uid` int(11) NOT NULL auto_increment,
  `username` varchar(99) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(99) NOT NULL,
  `ctime` datetime NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'root', 'c5769e3620c3ee2bd879b16c905d948e', 'c-mtv@163.com', '2010-11-10 16:27:57');
