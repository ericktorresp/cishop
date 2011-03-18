/*
MySQL Data Transfer
Source Host: localhost
Source Database: e8
Target Host: localhost
Target Database: e8
Date: 2011/3/18 18:12:01
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for announcement
-- ----------------------------
CREATE TABLE `announcement` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `author_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `deleted` bit(1) NOT NULL default '\0',
  `sticked` bit(1) NOT NULL default '\0',
  `subject` varchar(255) NOT NULL,
  `verifier_id` int(11) NOT NULL,
  `verify_time` datetime NOT NULL,
  `write_time` datetime NOT NULL,
  `channel_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `FK9584D47EF228F87` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统公告表';

-- ----------------------------
-- Table structure for bank
-- ----------------------------
CREATE TABLE `bank` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(9) NOT NULL,
  `logo` tinyblob,
  `name` varchar(99) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行信息表';

-- ----------------------------
-- Table structure for captcha
-- ----------------------------
CREATE TABLE `captcha` (
  `captcha_id` bigint(13) unsigned NOT NULL auto_increment,
  `captcha_time` int(10) unsigned NOT NULL,
  `ip_address` varchar(16) NOT NULL default '0',
  `word` varchar(20) NOT NULL,
  PRIMARY KEY  (`captcha_id`),
  KEY `word` (`word`)
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for card
-- ----------------------------
CREATE TABLE `card` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `account_alias` varchar(99) NOT NULL,
  `account_currency` varchar(9) NOT NULL,
  `account_name` varchar(19) NOT NULL,
  `account_no` varchar(29) NOT NULL,
  `add_time` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `enabled` bit(1) NOT NULL default '',
  `discriminator` enum('withdraw','deposit') NOT NULL default 'deposit',
  `email` varchar(99) NOT NULL,
  `init_balance` decimal(10,0) NOT NULL,
  `login_pwd` varchar(99) NOT NULL,
  `transaction_pwd` varchar(99) NOT NULL,
  `verify_time` timestamp NULL default NULL,
  `bank_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `FK2E7B10F04053ED` (`district_id`),
  KEY `FK2E7B10C8749EAD` (`bank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='银行卡表';

-- ----------------------------
-- Table structure for channel
-- ----------------------------
CREATE TABLE `channel` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `enabled` bit(1) NOT NULL default '',
  `name` varchar(60) NOT NULL,
  `path` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='频道表';

-- ----------------------------
-- Table structure for config
-- ----------------------------
CREATE TABLE `config` (
  `configid` int(4) unsigned NOT NULL auto_increment COMMENT '系统配置ID',
  `parentid` int(4) unsigned NOT NULL default '0' COMMENT '配置项父ID',
  `configkey` varchar(30) NOT NULL COMMENT '系统配置名称',
  `configvalue` varchar(500) NOT NULL COMMENT '系统配置值',
  `defaultvalue` varchar(255) NOT NULL COMMENT '系统配置默认值',
  `configvaluetype` varchar(10) NOT NULL COMMENT '系统配置数据类型',
  `forminputtype` varchar(10) NOT NULL default 'input' COMMENT '表单提交类型',
  `channelid` tinyint(2) unsigned NOT NULL default '0' COMMENT '栏目ID',
  `title` varchar(255) NOT NULL COMMENT '配置标题',
  `description` varchar(255) NOT NULL default '' COMMENT '配置描述',
  `isdisabled` tinyint(1) unsigned NOT NULL default '0' COMMENT '配置项是否禁用',
  PRIMARY KEY  (`configid`),
  KEY `idx_disable` (`isdisabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统配置';

-- ----------------------------
-- Table structure for district
-- ----------------------------
CREATE TABLE `district` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `areacode` varchar(9) NOT NULL,
  `code` varchar(19) NOT NULL,
  `name` varchar(99) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `zip` varchar(9) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='省市地区表';

-- ----------------------------
-- Table structure for domain
-- ----------------------------
CREATE TABLE `domain` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `enabled` bit(1) NOT NULL default '',
  `domain` varchar(99) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='统系域名表';

-- ----------------------------
-- Table structure for online_user
-- ----------------------------
CREATE TABLE `online_user` (
  `user_id` int(8) unsigned NOT NULL default '0' COMMENT '用户ID',
  `session_id` char(32) default '' COMMENT '用户的sessionkey',
  `lasttime` timestamp NULL default NULL on update CURRENT_TIMESTAMP COMMENT '最后更新时间',
  `is_admin` bit(1) NOT NULL,
  KEY `user_id` (`user_id`,`session_id`,`is_admin`),
  KEY `lasttime` (`lasttime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='当前在线用户表';

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
CREATE TABLE `sessions` (
  `session_id` varchar(40) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  `user_data` text NOT NULL,
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user
-- ----------------------------
CREATE TABLE `user` (
  `id` int(8) unsigned NOT NULL auto_increment COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户登陆帐号',
  `loginpwd` char(32) NOT NULL COMMENT '登陆密码',
  `securitypwd` char(32) NOT NULL COMMENT '安全密码',
  `usertype` tinyint(1) unsigned NOT NULL default '0' COMMENT '用户类型(0:用户;1:代理;2:总代管理员)',
  `nickname` varchar(50) NOT NULL default '' COMMENT '用户昵称',
  `language` varchar(10) NOT NULL default 'zh_CN' COMMENT '用户指定语言',
  `skin` varchar(10) NOT NULL default 'default' COMMENT '用户设置的模板文件',
  `email` varchar(60) NOT NULL default '' COMMENT '用户邮箱',
  `authtoparent` tinyint(1) NOT NULL default '0' COMMENT '用户授权上级',
  `addcount` int(8) NOT NULL default '0' COMMENT '允许开户数额',
  `authadd` tinyint(1) unsigned NOT NULL default '0' COMMENT '否是允许开户，0：不允许，1允许',
  `lastip` char(15) NOT NULL COMMENT '最后登陆IP',
  `lasttime` datetime NOT NULL COMMENT '最后登陆时间',
  `registerip` char(15) NOT NULL COMMENT '注册IP',
  `registertime` datetime NOT NULL COMMENT '用户注册时间',
  `userrank` int(2) unsigned NOT NULL default '0' COMMENT '用户星级',
  `rankcreatetime` datetime default NULL COMMENT '用户被评星时间',
  `rankupdate` datetime default NULL COMMENT '用户被评星更新时间',
  `question_id_1` smallint(5) unsigned NOT NULL default '0' COMMENT '安全问题ID',
  `define_question_1` varchar(100) NOT NULL default '' COMMENT '自定义安全问题',
  `answer_1` varchar(255) NOT NULL default '' COMMENT '安全问题答案',
  `question_id_2` smallint(5) unsigned NOT NULL default '0' COMMENT '第二个安全问题',
  `define_question_2` varchar(100) NOT NULL default '' COMMENT '第二个自定义问题',
  `answer_2` varchar(255) NOT NULL default '' COMMENT '第二个问题答案',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_uname` (`username`),
  KEY `idx_uid_uname` (`id`,`username`),
  KEY `idx_user_login` (`username`,`loginpwd`,`securitypwd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户表';

-- ----------------------------
-- Table structure for user_channel
-- ----------------------------
CREATE TABLE `user_channel` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT '用户频道ID',
  `user_id` int(8) unsigned NOT NULL COMMENT '用户ID',
  `channel_id` tinyint(2) unsigned NOT NULL COMMENT '频道ID',
  `enabled` bit(1) NOT NULL default '' COMMENT '用户频道关系状态(是否禁用1:正常;0:禁用)',
  `group_id` int(8) unsigned NOT NULL COMMENT '用户在频道中所属组ID(usergroup.id|proxygroup.id)',
  `extendmenustr` varchar(255) NOT NULL default '' COMMENT '扩展差异菜单权限',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `idx_userchannel` (`user_id`,`channel_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_userright` (`user_id`,`channel_id`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户频道关系表';

-- ----------------------------
-- Table structure for user_domain
-- ----------------------------
CREATE TABLE `user_domain` (
  `id` int(7) unsigned NOT NULL auto_increment COMMENT '用户域名ID',
  `user_id` int(8) unsigned NOT NULL COMMENT '总代ID',
  `domain_id` int(5) unsigned NOT NULL COMMENT '域名ID',
  PRIMARY KEY  (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_userdmain` (`user_id`,`domain_id`),
  KEY `idx_domain` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户域名表';

-- ----------------------------
-- Table structure for user_fund
-- ----------------------------
CREATE TABLE `user_fund` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `available_balance` decimal(14,4) NOT NULL,
  `cash_balance` decimal(14,4) NOT NULL,
  `channel_balance` decimal(14,4) NOT NULL,
  `hold_balance` decimal(14,4) NOT NULL,
  `lastactivetime` datetime NOT NULL,
  `lastupdatetime` datetime NOT NULL,
  `locked` bit(1) NOT NULL,
  `user` tinyblob,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='户用资金表';

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `captcha` VALUES ('1', '1300344798', '127.0.0.1', 'zPeV4HSz');
INSERT INTO `captcha` VALUES ('2', '1300344951', '127.0.0.1', 'nud7PHi4');
INSERT INTO `captcha` VALUES ('3', '1300345061', '127.0.0.1', 'IAFG1h7e');
INSERT INTO `captcha` VALUES ('4', '1300345080', '127.0.0.1', 'Y21lH4og');
INSERT INTO `captcha` VALUES ('5', '1300345293', '127.0.0.1', 'NXRgix8h');
INSERT INTO `captcha` VALUES ('6', '1300345432', '127.0.0.1', 'EBU13snN');
INSERT INTO `captcha` VALUES ('7', '1300345460', '127.0.0.1', 'nmyzTZGF');
INSERT INTO `captcha` VALUES ('8', '1300345620', '127.0.0.1', '8hnqAs0b');
INSERT INTO `captcha` VALUES ('9', '1300345622', '127.0.0.1', 'jiSdqUUe');
INSERT INTO `captcha` VALUES ('10', '1300345623', '127.0.0.1', 'zrnXeQxN');
INSERT INTO `captcha` VALUES ('11', '1300345895', '127.0.0.1', 'witljbz3');
INSERT INTO `captcha` VALUES ('12', '1300345896', '127.0.0.1', 'QGTY6OeD');
INSERT INTO `captcha` VALUES ('13', '1300345913', '127.0.0.1', 'XvgMK46H');
INSERT INTO `captcha` VALUES ('14', '1300345914', '127.0.0.1', 'nwsX7NqN');
INSERT INTO `captcha` VALUES ('15', '1300345918', '127.0.0.1', '9SglZbMv');
INSERT INTO `captcha` VALUES ('16', '1300345920', '127.0.0.1', '4JMwK4hO');
INSERT INTO `captcha` VALUES ('17', '1300346081', '127.0.0.1', 'fd4fcnZC');
INSERT INTO `captcha` VALUES ('18', '1300346084', '127.0.0.1', 'Q9VFIKeM');
INSERT INTO `captcha` VALUES ('19', '1300346086', '127.0.0.1', 'fNL3QzgD');
INSERT INTO `captcha` VALUES ('20', '1300346308', '127.0.0.1', 'VlNR5QPw');
INSERT INTO `captcha` VALUES ('21', '1300346309', '127.0.0.1', 'cmyQ1ftI');
INSERT INTO `captcha` VALUES ('22', '1300346310', '127.0.0.1', 'Ipv47aAy');
INSERT INTO `captcha` VALUES ('23', '1300346310', '127.0.0.1', 'bQmBKGaY');
INSERT INTO `captcha` VALUES ('24', '1300346310', '127.0.0.1', 'iwWLnMYR');
INSERT INTO `captcha` VALUES ('25', '1300346311', '127.0.0.1', 'bWBUlCg9');
INSERT INTO `captcha` VALUES ('26', '1300346311', '127.0.0.1', 'FvtZzWPN');
INSERT INTO `captcha` VALUES ('27', '1300346311', '127.0.0.1', 'S0f9ASAJ');
INSERT INTO `captcha` VALUES ('28', '1300346311', '127.0.0.1', 'sxQ94WHP');
INSERT INTO `captcha` VALUES ('29', '1300346311', '127.0.0.1', 'Cy6Mc5Nx');
INSERT INTO `captcha` VALUES ('30', '1300346479', '127.0.0.1', 'bJK7ltKU');
INSERT INTO `captcha` VALUES ('31', '1300346482', '127.0.0.1', '7bTWtiiE');
INSERT INTO `captcha` VALUES ('32', '1300346483', '127.0.0.1', '3w7nj4dE');
INSERT INTO `captcha` VALUES ('33', '1300346483', '127.0.0.1', 'fEU4gANF');
INSERT INTO `captcha` VALUES ('34', '1300346484', '127.0.0.1', 'v9IxgfRa');
INSERT INTO `captcha` VALUES ('35', '1300346484', '127.0.0.1', '9G6uz3Cz');
INSERT INTO `captcha` VALUES ('36', '1300346484', '127.0.0.1', 'Lyr0SKdn');
INSERT INTO `captcha` VALUES ('37', '1300346500', '127.0.0.1', '5HL7ILvR');
INSERT INTO `captcha` VALUES ('38', '1300346548', '127.0.0.1', 'XosuuyHh');
INSERT INTO `captcha` VALUES ('39', '1300346576', '127.0.0.1', 'hpRyzQ9A');
INSERT INTO `captcha` VALUES ('40', '1300346684', '127.0.0.1', 'u7Hvj43X');
INSERT INTO `captcha` VALUES ('41', '1300347691', '127.0.0.1', 'EbYjIrdF');
INSERT INTO `captcha` VALUES ('42', '1300347694', '127.0.0.1', '3hiz1ZrW');
INSERT INTO `captcha` VALUES ('43', '1300347695', '127.0.0.1', 'jzoFUIoF');
INSERT INTO `captcha` VALUES ('44', '1300347695', '127.0.0.1', '2ill19x6');
INSERT INTO `captcha` VALUES ('45', '1300347695', '127.0.0.1', 'ygCCNT19');
INSERT INTO `captcha` VALUES ('46', '1300347938', '127.0.0.1', 'yQONI8dj');
INSERT INTO `captcha` VALUES ('47', '1300347939', '127.0.0.1', '5KCK5sXI');
INSERT INTO `captcha` VALUES ('48', '1300347939', '127.0.0.1', '2gnMmTXo');
INSERT INTO `captcha` VALUES ('49', '1300347940', '127.0.0.1', 'RprcMv3G');
INSERT INTO `captcha` VALUES ('50', '1300347940', '127.0.0.1', 'McCiJ6Cf');
INSERT INTO `captcha` VALUES ('51', '1300347940', '127.0.0.1', 'Rg8QxUJP');
INSERT INTO `captcha` VALUES ('52', '1300347940', '127.0.0.1', 'MiGVqE4v');
INSERT INTO `captcha` VALUES ('53', '1300347941', '127.0.0.1', 'i3SkLH45');
INSERT INTO `captcha` VALUES ('54', '1300348172', '127.0.0.1', 'FuK24tkc');
INSERT INTO `captcha` VALUES ('55', '1300348173', '127.0.0.1', 'lVcckckb');
INSERT INTO `captcha` VALUES ('56', '1300348177', '127.0.0.1', 'oFSYEzqk');
INSERT INTO `captcha` VALUES ('57', '1300348244', '127.0.0.1', 'j7XCmvqM');
INSERT INTO `captcha` VALUES ('58', '1300348245', '127.0.0.1', 'XlZNcTp1');
INSERT INTO `captcha` VALUES ('59', '1300348245', '127.0.0.1', 'Tpan9FFh');
INSERT INTO `captcha` VALUES ('60', '1300348248', '127.0.0.1', 'udhO09tE');
INSERT INTO `captcha` VALUES ('61', '1300349852', '127.0.0.1', 'vtQLNuHw');
INSERT INTO `captcha` VALUES ('62', '1300356328', '127.0.0.1', 'BXhOdR9z');
INSERT INTO `captcha` VALUES ('63', '1300356328', '127.0.0.1', 'ZGQnu80t');
INSERT INTO `captcha` VALUES ('64', '1300356329', '127.0.0.1', 'gc2m66zL');
INSERT INTO `captcha` VALUES ('65', '1300413312', '127.0.0.1', 'aa7Fc789');
INSERT INTO `captcha` VALUES ('66', '1300413349', '127.0.0.1', '2TrFJC2W');
INSERT INTO `captcha` VALUES ('67', '1300413418', '127.0.0.1', 'VTtY28T5');
INSERT INTO `captcha` VALUES ('68', '1300416523', '127.0.0.1', '7lIGY72Z');
INSERT INTO `captcha` VALUES ('69', '1300416579', '127.0.0.1', 'bDIBdHtt');
INSERT INTO `captcha` VALUES ('70', '1300416582', '127.0.0.1', 'eQg58dGR');
INSERT INTO `captcha` VALUES ('71', '1300416583', '127.0.0.1', 'M6Fz6dvr');
INSERT INTO `captcha` VALUES ('72', '1300416613', '127.0.0.1', 'UMrNYuw5');
INSERT INTO `captcha` VALUES ('73', '1300416621', '127.0.0.1', 'F8nKjlVV');
INSERT INTO `captcha` VALUES ('74', '1300416737', '127.0.0.1', 'guncoLnT');
INSERT INTO `captcha` VALUES ('75', '1300417290', '127.0.0.1', 'BZv9EPUP');
INSERT INTO `captcha` VALUES ('76', '1300420639', '127.0.0.1', 'Wg4UFAjR');
INSERT INTO `captcha` VALUES ('77', '1300427620', '127.0.0.1', 'b31uyaQC');
INSERT INTO `captcha` VALUES ('78', '1300431432', '127.0.0.1', 'how1SDyt');
INSERT INTO `captcha` VALUES ('79', '1300431544', '127.0.0.1', 'AQ10ncOR');
INSERT INTO `captcha` VALUES ('80', '1300431907', '127.0.0.1', 'Zsb8Ckdj');
INSERT INTO `captcha` VALUES ('81', '1300432080', '127.0.0.1', 'Vkl1Wr2g');
INSERT INTO `sessions` VALUES ('2b218f7be9fcbfa9054803632625c1fe', '127.0.0.1', 'Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv', '1300442899', 'a:16:{s:8:\"username\";s:6:\"floyd1\";s:8:\"password\";s:6:\"123123\";s:5:\"email\";s:13:\"c-mtv@163.com\";s:4:\"step\";i:3;s:5:\"fname\";s:5:\"floyd\";s:5:\"lname\";s:3:\"Joe\";s:5:\"phone\";s:7:\"9876543\";s:11:\"birth_month\";s:1:\"1\";s:9:\"birth_day\";s:1:\"1\";s:10:\"birth_year\";s:4:\"1950\";s:11:\"street_addr\";s:4:\"na.a\";s:5:\"suite\";s:0:\"\";s:4:\"city\";s:8:\"Portland\";s:3:\"zip\";s:6:\"123456\";s:5:\"state\";s:0:\"\";s:7:\"country\";s:2:\"CN\";}');
