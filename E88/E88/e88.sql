/*
MySQL Data Transfer
Source Host: localhost
Source Database: e88
Target Host: localhost
Target Database: e88
Date: 2011/4/8 18:01:49
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for announcement
-- ----------------------------
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) default NULL,
  `verify_time` datetime default NULL,
  `deleted` tinyint(1) NOT NULL,
  `sticked` tinyint(1) NOT NULL,
  `channel_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `announcement_337b96ff` (`author_id`),
  KEY `announcement_584122da` (`verifier_id`),
  KEY `announcement_668d8aa` (`channel_id`),
  CONSTRAINT `author_id_refs_id_126a9b25` FOREIGN KEY (`author_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `channel_id_refs_id_6520b93c` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`),
  CONSTRAINT `verifier_id_refs_id_126a9b25` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_group
-- ----------------------------
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_group_permissions
-- ----------------------------
CREATE TABLE `auth_group_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `group_id` (`group_id`,`permission_id`),
  KEY `auth_group_permissions_425ae3c4` (`group_id`),
  KEY `auth_group_permissions_1e014c8f` (`permission_id`),
  CONSTRAINT `group_id_refs_id_3cea63fe` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `permission_id_refs_id_5886d21f` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_message
-- ----------------------------
CREATE TABLE `auth_message` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `auth_message_403f60f` (`user_id`),
  CONSTRAINT `user_id_refs_id_650f49a6` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_permission
-- ----------------------------
CREATE TABLE `auth_permission` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `content_type_id` (`content_type_id`,`codename`),
  KEY `auth_permission_1bb8f392` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_728de91f` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_user
-- ----------------------------
CREATE TABLE `auth_user` (
  `id` int(11) NOT NULL auto_increment,
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
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_user_groups
-- ----------------------------
CREATE TABLE `auth_user_groups` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`,`group_id`),
  KEY `auth_user_groups_403f60f` (`user_id`),
  KEY `auth_user_groups_425ae3c4` (`group_id`),
  CONSTRAINT `group_id_refs_id_f116770` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `user_id_refs_id_7ceef80f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for auth_user_user_permissions
-- ----------------------------
CREATE TABLE `auth_user_user_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`,`permission_id`),
  KEY `auth_user_user_permissions_403f60f` (`user_id`),
  KEY `auth_user_user_permissions_1e014c8f` (`permission_id`),
  CONSTRAINT `permission_id_refs_id_67e79cb` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `user_id_refs_id_dfbab7d` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for axes_accessattempt
-- ----------------------------
CREATE TABLE `axes_accessattempt` (
  `id` int(11) NOT NULL auto_increment,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` char(15) NOT NULL,
  `get_data` longtext NOT NULL,
  `post_data` longtext NOT NULL,
  `http_accept` varchar(255) NOT NULL,
  `path_info` varchar(255) NOT NULL,
  `failures_since_start` int(10) unsigned NOT NULL,
  `attempt_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bank
-- ----------------------------
CREATE TABLE `bank` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `logo` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bank_cellphone
-- ----------------------------
CREATE TABLE `bank_cellphone` (
  `id` int(11) NOT NULL auto_increment,
  `number` varchar(11) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) default NULL,
  `verify_time` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `bank_cellphone_1e4ad39d` (`adder_id`),
  KEY `bank_cellphone_584122da` (`verifier_id`),
  CONSTRAINT `adder_id_refs_id_761d1e7b` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `verifier_id_refs_id_761d1e7b` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for card
-- ----------------------------
CREATE TABLE `card` (
  `id` int(11) NOT NULL auto_increment,
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
  `verify_time` datetime default NULL,
  `verifier_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
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
-- Table structure for channel
-- ----------------------------
CREATE TABLE `channel` (
  `id` int(11) NOT NULL auto_increment,
  `enabled` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `path` varchar(90) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for city
-- ----------------------------
CREATE TABLE `city` (
  `id` int(11) NOT NULL auto_increment,
  `city` varchar(100) NOT NULL,
  `province_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `city_37751324` (`province_id`),
  CONSTRAINT `province_id_refs_id_23f2453a` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for country
-- ----------------------------
CREATE TABLE `country` (
  `iso` varchar(6) NOT NULL,
  `name` varchar(240) NOT NULL,
  `printable_name` varchar(240) NOT NULL,
  `cn_name` varchar(240) NOT NULL,
  `iso3` varchar(9) default NULL,
  `numcode` int(11) default NULL,
  PRIMARY KEY  (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for deposit_log
-- ----------------------------
CREATE TABLE `deposit_log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `deposit_method_id` int(11) NOT NULL,
  `deposit_time` datetime NOT NULL,
  `received` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `deposit_log_403f60f` (`user_id`),
  KEY `deposit_log_4069c848` (`deposit_method_id`),
  CONSTRAINT `deposit_method_id_refs_id_7f5b94e9` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method_account` (`id`),
  CONSTRAINT `user_id_refs_id_5900f35a` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for deposit_method
-- ----------------------------
CREATE TABLE `deposit_method` (
  `id` int(11) NOT NULL auto_increment,
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
  PRIMARY KEY  (`id`),
  KEY `payment_method_1e4ad39d` (`adder_id`),
  CONSTRAINT `adder_id_refs_id_d058860` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for deposit_method_account
-- ----------------------------
CREATE TABLE `deposit_method_account` (
  `id` int(11) NOT NULL auto_increment,
  `login_name` varchar(100) NOT NULL,
  `deposit_method_id` int(11) NOT NULL,
  `login_password` varchar(40) NOT NULL,
  `transaction_password` varchar(40) NOT NULL,
  `account_name` varchar(40) NOT NULL,
  `init_balance` decimal(14,4) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `adder_id` int(11) NOT NULL,
  `add_time` datetime NOT NULL,
  `verifier_id` int(11) default NULL,
  `verify_time` datetime default NULL,
  `pid` varchar(30) default NULL,
  `key` varchar(40) default NULL,
  PRIMARY KEY  (`id`),
  KEY `payment_method_account_1123be70` (`deposit_method_id`),
  KEY `payment_method_account_1e4ad39d` (`adder_id`),
  KEY `payment_method_account_584122da` (`verifier_id`),
  CONSTRAINT `deposit_method_account_ibfk_1` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_method` (`id`),
  CONSTRAINT `adder_id_refs_id_2a2aee58` FOREIGN KEY (`adder_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `verifier_id_refs_id_2a2aee58` FOREIGN KEY (`verifier_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for django_admin_log
-- ----------------------------
CREATE TABLE `django_admin_log` (
  `id` int(11) NOT NULL auto_increment,
  `action_time` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_type_id` int(11) default NULL,
  `object_id` longtext,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint(5) unsigned NOT NULL,
  `change_message` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `django_admin_log_403f60f` (`user_id`),
  KEY `django_admin_log_1bb8f392` (`content_type_id`),
  CONSTRAINT `content_type_id_refs_id_288599e6` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `user_id_refs_id_c8665aa` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for django_content_type
-- ----------------------------
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `app_label` (`app_label`,`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for django_session
-- ----------------------------
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime NOT NULL,
  PRIMARY KEY  (`session_key`),
  KEY `django_session_3da3d3d8` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for django_site
-- ----------------------------
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL auto_increment,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for domain
-- ----------------------------
CREATE TABLE `domain` (
  `id` int(11) NOT NULL auto_increment,
  `enabled` tinyint(1) NOT NULL,
  `domain` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for game
-- ----------------------------
CREATE TABLE `game` (
  `id` int(11) NOT NULL auto_increment,
  `display_name` varchar(100) NOT NULL,
  `url_name` varchar(100) NOT NULL,
  `url` varchar(255) default NULL,
  `photo` varchar(100) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for province
-- ----------------------------
CREATE TABLE `province` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `country_id` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `province_534dd89` (`country_id`),
  CONSTRAINT `country_id_refs_iso_7b15e9a8` FOREIGN KEY (`country_id`) REFERENCES `country` (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_card
-- ----------------------------
CREATE TABLE `user_card` (
  `id` int(11) NOT NULL auto_increment,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alias` varchar(20) NOT NULL,
  `account_name` varchar(30) NOT NULL,
  `branch` varchar(50) NOT NULL,
  `card_no` varchar(30) NOT NULL,
  `add_time` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_card_1862eb86` (`bank_id`),
  KEY `user_card_403f60f` (`user_id`),
  CONSTRAINT `bank_id_refs_id_5cfaf0e` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`),
  CONSTRAINT `user_id_refs_id_993816f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_channel
-- ----------------------------
CREATE TABLE `user_channel` (
  `id` int(11) NOT NULL auto_increment,
  `channel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `channel_id` (`channel_id`,`user_id`),
  KEY `user_channel_668d8aa` (`channel_id`),
  KEY `user_channel_403f60f` (`user_id`),
  CONSTRAINT `channel_id_refs_id_24173c8c` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`),
  CONSTRAINT `user_id_refs_id_1ce5ecf5` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_domain
-- ----------------------------
CREATE TABLE `user_domain` (
  `id` int(11) NOT NULL auto_increment,
  `domain_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `domain_id` (`domain_id`,`user_id`),
  KEY `user_domain_a2431ea` (`domain_id`),
  KEY `user_domain_403f60f` (`user_id`),
  CONSTRAINT `domain_id_refs_id_735fd586` FOREIGN KEY (`domain_id`) REFERENCES `domain` (`id`),
  CONSTRAINT `user_id_refs_id_7e9bfbeb` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_profile
-- ----------------------------
CREATE TABLE `user_profile` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `birthday` date default NULL,
  `gender` varchar(1) NOT NULL,
  `phone` varchar(11) default NULL,
  `mobile` varchar(15) NOT NULL,
  `address` varchar(255) default NULL,
  `address2` varchar(255) default NULL,
  `city_id` int(11) default NULL,
  `zip` varchar(8) default NULL,
  `language` varchar(10) default NULL,
  `province_id` int(11) default NULL,
  `lastip` char(15) default NULL,
  `registerip` char(15) default NULL,
  `country_id` varchar(6) default NULL,
  `available_balance` decimal(14,4) NOT NULL,
  `cash_balance` decimal(14,4) NOT NULL,
  `channel_balance` decimal(14,4) NOT NULL,
  `hold_balance` decimal(14,4) NOT NULL,
  `balance_update_time` datetime default NULL,
  `email_verified` tinyint(1) NOT NULL,
  `security_password` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `user_profile_586a73b5` (`city_id`),
  KEY `user_profile_37751324` (`province_id`),
  KEY `user_profile_534dd89` (`country_id`),
  CONSTRAINT `city_id_refs_id_3ada2c19` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `country_id_refs_iso_6a9d6c3f` FOREIGN KEY (`country_id`) REFERENCES `country` (`iso`),
  CONSTRAINT `province_id_refs_id_56dc919c` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`),
  CONSTRAINT `user_id_refs_id_5f4bba6f` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
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
INSERT INTO `auth_permission` VALUES ('22', 'Can add access attempt', '8', 'add_accessattempt');
INSERT INTO `auth_permission` VALUES ('23', 'Can change access attempt', '8', 'change_accessattempt');
INSERT INTO `auth_permission` VALUES ('24', 'Can delete access attempt', '8', 'delete_accessattempt');
INSERT INTO `auth_permission` VALUES ('25', 'Can add Country', '9', 'add_country');
INSERT INTO `auth_permission` VALUES ('26', 'Can change Country', '9', 'change_country');
INSERT INTO `auth_permission` VALUES ('27', 'Can delete Country', '9', 'delete_country');
INSERT INTO `auth_permission` VALUES ('28', 'Can add Province', '10', 'add_province');
INSERT INTO `auth_permission` VALUES ('29', 'Can change Province', '10', 'change_province');
INSERT INTO `auth_permission` VALUES ('30', 'Can delete Province', '10', 'delete_province');
INSERT INTO `auth_permission` VALUES ('31', 'Can add City', '11', 'add_city');
INSERT INTO `auth_permission` VALUES ('32', 'Can change City', '11', 'change_city');
INSERT INTO `auth_permission` VALUES ('33', 'Can delete City', '11', 'delete_city');
INSERT INTO `auth_permission` VALUES ('34', 'Can add Domain', '12', 'add_domain');
INSERT INTO `auth_permission` VALUES ('35', 'Can change Domain', '12', 'change_domain');
INSERT INTO `auth_permission` VALUES ('36', 'Can delete Domain', '12', 'delete_domain');
INSERT INTO `auth_permission` VALUES ('37', 'Can add Channel', '13', 'add_channel');
INSERT INTO `auth_permission` VALUES ('38', 'Can change Channel', '13', 'change_channel');
INSERT INTO `auth_permission` VALUES ('39', 'Can delete Channel', '13', 'delete_channel');
INSERT INTO `auth_permission` VALUES ('40', 'Can add Announcement', '14', 'add_announcement');
INSERT INTO `auth_permission` VALUES ('41', 'Can change Announcement', '14', 'change_announcement');
INSERT INTO `auth_permission` VALUES ('42', 'Can delete Announcement', '14', 'delete_announcement');
INSERT INTO `auth_permission` VALUES ('43', 'Can verify', '14', 'can_verify');
INSERT INTO `auth_permission` VALUES ('44', 'Can stick', '14', 'can_stick');
INSERT INTO `auth_permission` VALUES ('45', 'Can add profile', '15', 'add_userprofile');
INSERT INTO `auth_permission` VALUES ('46', 'Can change profile', '15', 'change_userprofile');
INSERT INTO `auth_permission` VALUES ('47', 'Can delete profile', '15', 'delete_userprofile');
INSERT INTO `auth_permission` VALUES ('48', 'Can add User\'s card', '16', 'add_usercard');
INSERT INTO `auth_permission` VALUES ('49', 'Can change User\'s card', '16', 'change_usercard');
INSERT INTO `auth_permission` VALUES ('50', 'Can delete User\'s card', '16', 'delete_usercard');
INSERT INTO `auth_permission` VALUES ('51', 'Can add Game', '17', 'add_game');
INSERT INTO `auth_permission` VALUES ('52', 'Can change Game', '17', 'change_game');
INSERT INTO `auth_permission` VALUES ('53', 'Can delete Game', '17', 'delete_game');
INSERT INTO `auth_permission` VALUES ('54', 'Can add Bank', '18', 'add_bank');
INSERT INTO `auth_permission` VALUES ('55', 'Can change Bank', '18', 'change_bank');
INSERT INTO `auth_permission` VALUES ('56', 'Can delete Bank', '18', 'delete_bank');
INSERT INTO `auth_permission` VALUES ('57', 'Can add Card', '19', 'add_card');
INSERT INTO `auth_permission` VALUES ('58', 'Can change Card', '19', 'change_card');
INSERT INTO `auth_permission` VALUES ('59', 'Can delete Card', '19', 'delete_card');
INSERT INTO `auth_permission` VALUES ('60', 'Can verify', '19', 'can_verify');
INSERT INTO `auth_permission` VALUES ('68', 'Can add log entry', '22', 'add_logentry');
INSERT INTO `auth_permission` VALUES ('69', 'Can change log entry', '22', 'change_logentry');
INSERT INTO `auth_permission` VALUES ('70', 'Can delete log entry', '22', 'delete_logentry');
INSERT INTO `auth_permission` VALUES ('71', 'Can add deposit method', '23', 'add_depositmethod');
INSERT INTO `auth_permission` VALUES ('72', 'Can change deposit method', '23', 'change_depositmethod');
INSERT INTO `auth_permission` VALUES ('73', 'Can delete deposit method', '23', 'delete_depositmethod');
INSERT INTO `auth_permission` VALUES ('74', 'Can add deposit method account', '24', 'add_depositmethodaccount');
INSERT INTO `auth_permission` VALUES ('75', 'Can change deposit method account', '24', 'change_depositmethodaccount');
INSERT INTO `auth_permission` VALUES ('76', 'Can delete deposit method account', '24', 'delete_depositmethodaccount');
INSERT INTO `auth_permission` VALUES ('77', 'Can verify', '24', 'can_verify');
INSERT INTO `auth_permission` VALUES ('78', 'Can add deposit log', '25', 'add_depositlog');
INSERT INTO `auth_permission` VALUES ('79', 'Can change deposit log', '25', 'change_depositlog');
INSERT INTO `auth_permission` VALUES ('80', 'Can delete deposit log', '25', 'delete_depositlog');
INSERT INTO `auth_permission` VALUES ('81', 'Can add cellphone', '26', 'add_cellphone');
INSERT INTO `auth_permission` VALUES ('82', 'Can change cellphone', '26', 'change_cellphone');
INSERT INTO `auth_permission` VALUES ('83', 'Can delete cellphone', '26', 'delete_cellphone');
INSERT INTO `auth_permission` VALUES ('84', 'Can verify', '26', 'can_verify');
INSERT INTO `auth_user` VALUES ('1', 'root', 'Floyd', 'Joe', 'kirinse@gmail.com', 'sha1$228d8$dc78d2daa8f7b9c7cb8bd7ae3d3d7b3526d0f34a', '1', '1', '1', '2011-04-07 14:13:47', '2011-04-07 14:11:55');
INSERT INTO `bank` VALUES ('1', 'ICBC', '中国工商银行', 'images/bank/6.jpg');
INSERT INTO `bank` VALUES ('2', 'CCB', '建设银行', 'images/bank/7.jpg');
INSERT INTO `city` VALUES ('1', '石家庄市', '70');
INSERT INTO `city` VALUES ('2', '唐山市', '70');
INSERT INTO `city` VALUES ('3', '秦皇岛市', '70');
INSERT INTO `city` VALUES ('4', '邯郸市', '70');
INSERT INTO `city` VALUES ('5', '邢台市', '70');
INSERT INTO `city` VALUES ('6', '保定市', '70');
INSERT INTO `city` VALUES ('7', '张家口市', '70');
INSERT INTO `city` VALUES ('8', '承德市', '70');
INSERT INTO `city` VALUES ('9', '沧州市', '70');
INSERT INTO `city` VALUES ('10', '廊坊市', '70');
INSERT INTO `city` VALUES ('11', '衡水市', '70');
INSERT INTO `city` VALUES ('12', '太原市', '71');
INSERT INTO `city` VALUES ('13', '大同市', '71');
INSERT INTO `city` VALUES ('14', '阳泉市', '71');
INSERT INTO `city` VALUES ('15', '长治市', '71');
INSERT INTO `city` VALUES ('16', '晋城市', '71');
INSERT INTO `city` VALUES ('17', '朔州市', '71');
INSERT INTO `city` VALUES ('18', '晋中市', '71');
INSERT INTO `city` VALUES ('19', '运城市', '71');
INSERT INTO `city` VALUES ('20', '忻州市', '71');
INSERT INTO `city` VALUES ('21', '临汾市', '71');
INSERT INTO `city` VALUES ('22', '吕梁市', '71');
INSERT INTO `city` VALUES ('23', '沈阳市', '73');
INSERT INTO `city` VALUES ('24', '大连市', '73');
INSERT INTO `city` VALUES ('25', '鞍山市', '73');
INSERT INTO `city` VALUES ('26', '抚顺市', '73');
INSERT INTO `city` VALUES ('27', '本溪市', '73');
INSERT INTO `city` VALUES ('28', '丹东市', '73');
INSERT INTO `city` VALUES ('29', '锦州市', '73');
INSERT INTO `city` VALUES ('30', '营口市', '73');
INSERT INTO `city` VALUES ('31', '阜新市', '73');
INSERT INTO `city` VALUES ('32', '辽阳市', '73');
INSERT INTO `city` VALUES ('33', '盘锦市', '73');
INSERT INTO `city` VALUES ('34', '铁岭市', '73');
INSERT INTO `city` VALUES ('35', '朝阳市', '73');
INSERT INTO `city` VALUES ('36', '葫芦岛市', '73');
INSERT INTO `city` VALUES ('37', '长春市', '74');
INSERT INTO `city` VALUES ('38', '吉林市', '74');
INSERT INTO `city` VALUES ('39', '四平市', '74');
INSERT INTO `city` VALUES ('40', '辽源市', '74');
INSERT INTO `city` VALUES ('41', '通化市', '74');
INSERT INTO `city` VALUES ('42', '白山市', '74');
INSERT INTO `city` VALUES ('43', '松原市', '74');
INSERT INTO `city` VALUES ('44', '白城市', '74');
INSERT INTO `city` VALUES ('45', '延边朝鲜族自治州', '74');
INSERT INTO `city` VALUES ('46', '哈尔滨市', '75');
INSERT INTO `city` VALUES ('47', '齐齐哈尔市', '75');
INSERT INTO `city` VALUES ('48', '鹤岗市', '75');
INSERT INTO `city` VALUES ('49', '双鸭山市', '75');
INSERT INTO `city` VALUES ('50', '鸡市', '75');
INSERT INTO `city` VALUES ('51', '大庆市', '75');
INSERT INTO `city` VALUES ('52', '伊春市', '75');
INSERT INTO `city` VALUES ('53', '牡丹江市', '75');
INSERT INTO `city` VALUES ('54', '佳木斯市', '75');
INSERT INTO `city` VALUES ('55', '七台河市', '75');
INSERT INTO `city` VALUES ('56', '黑河市', '75');
INSERT INTO `city` VALUES ('57', '绥化市', '75');
INSERT INTO `city` VALUES ('58', '大兴安岭地区', '75');
INSERT INTO `city` VALUES ('59', '南京市', '77');
INSERT INTO `city` VALUES ('60', '无锡市', '77');
INSERT INTO `city` VALUES ('61', '徐州市', '77');
INSERT INTO `city` VALUES ('62', '常州市', '77');
INSERT INTO `city` VALUES ('63', '苏州市', '77');
INSERT INTO `city` VALUES ('64', '南通市', '77');
INSERT INTO `city` VALUES ('65', '连云港市', '77');
INSERT INTO `city` VALUES ('66', '淮安市', '77');
INSERT INTO `city` VALUES ('67', '盐城市', '77');
INSERT INTO `city` VALUES ('68', '扬州市', '77');
INSERT INTO `city` VALUES ('69', '镇江市', '77');
INSERT INTO `city` VALUES ('70', '泰州市', '77');
INSERT INTO `city` VALUES ('71', '宿迁市', '77');
INSERT INTO `city` VALUES ('72', '杭州市', '78');
INSERT INTO `city` VALUES ('73', '宁波市', '78');
INSERT INTO `city` VALUES ('74', '温州市', '78');
INSERT INTO `city` VALUES ('75', '嘉兴市', '78');
INSERT INTO `city` VALUES ('76', '湖州市', '78');
INSERT INTO `city` VALUES ('77', '绍兴市', '78');
INSERT INTO `city` VALUES ('78', '金华市', '78');
INSERT INTO `city` VALUES ('79', '衢州市', '78');
INSERT INTO `city` VALUES ('80', '舟山市', '78');
INSERT INTO `city` VALUES ('81', '台州市', '78');
INSERT INTO `city` VALUES ('82', '丽水市', '78');
INSERT INTO `city` VALUES ('83', '合肥市', '79');
INSERT INTO `city` VALUES ('84', '芜湖市', '79');
INSERT INTO `city` VALUES ('85', '蚌埠市', '79');
INSERT INTO `city` VALUES ('86', '淮南市', '79');
INSERT INTO `city` VALUES ('87', '马鞍山市', '79');
INSERT INTO `city` VALUES ('88', '淮北市', '79');
INSERT INTO `city` VALUES ('89', '铜陵市', '79');
INSERT INTO `city` VALUES ('90', '安庆市', '79');
INSERT INTO `city` VALUES ('91', '黄山市', '79');
INSERT INTO `city` VALUES ('92', '滁州市', '79');
INSERT INTO `city` VALUES ('93', '阜阳市', '79');
INSERT INTO `city` VALUES ('94', '宿州市', '79');
INSERT INTO `city` VALUES ('95', '巢湖市', '79');
INSERT INTO `city` VALUES ('96', '六安市', '79');
INSERT INTO `city` VALUES ('97', '亳州市', '79');
INSERT INTO `city` VALUES ('98', '池州市', '79');
INSERT INTO `city` VALUES ('99', '宣城市', '79');
INSERT INTO `city` VALUES ('100', '福州市', '80');
INSERT INTO `city` VALUES ('101', '厦门市', '80');
INSERT INTO `city` VALUES ('102', '莆田市', '80');
INSERT INTO `city` VALUES ('103', '三明市', '80');
INSERT INTO `city` VALUES ('104', '泉州市', '80');
INSERT INTO `city` VALUES ('105', '漳州市', '80');
INSERT INTO `city` VALUES ('106', '南平市', '80');
INSERT INTO `city` VALUES ('107', '龙岩市', '80');
INSERT INTO `city` VALUES ('108', '宁德市', '80');
INSERT INTO `city` VALUES ('109', '南昌市', '81');
INSERT INTO `city` VALUES ('110', '景德镇市', '81');
INSERT INTO `city` VALUES ('111', '萍乡市', '81');
INSERT INTO `city` VALUES ('112', '九江市', '81');
INSERT INTO `city` VALUES ('113', '新余市', '81');
INSERT INTO `city` VALUES ('114', '鹰潭市', '81');
INSERT INTO `city` VALUES ('115', '赣州市', '81');
INSERT INTO `city` VALUES ('116', '吉安市', '81');
INSERT INTO `city` VALUES ('117', '宜春市', '81');
INSERT INTO `city` VALUES ('118', '抚州市', '81');
INSERT INTO `city` VALUES ('119', '上饶市', '81');
INSERT INTO `city` VALUES ('120', '济南市', '82');
INSERT INTO `city` VALUES ('121', '青岛市', '82');
INSERT INTO `city` VALUES ('122', '淄博市', '82');
INSERT INTO `city` VALUES ('123', '枣庄市', '82');
INSERT INTO `city` VALUES ('124', '东营市', '82');
INSERT INTO `city` VALUES ('125', '烟台市', '82');
INSERT INTO `city` VALUES ('126', '潍坊市', '82');
INSERT INTO `city` VALUES ('127', '济宁市', '82');
INSERT INTO `city` VALUES ('128', '泰安市', '82');
INSERT INTO `city` VALUES ('129', '威海市', '82');
INSERT INTO `city` VALUES ('130', '日照市', '82');
INSERT INTO `city` VALUES ('131', '莱芜市', '82');
INSERT INTO `city` VALUES ('132', '临沂市', '82');
INSERT INTO `city` VALUES ('133', '德州市', '82');
INSERT INTO `city` VALUES ('134', '聊城市', '82');
INSERT INTO `city` VALUES ('135', '滨州市', '82');
INSERT INTO `city` VALUES ('136', '菏泽市', '82');
INSERT INTO `city` VALUES ('137', '郑州市', '83');
INSERT INTO `city` VALUES ('138', '开封市', '83');
INSERT INTO `city` VALUES ('139', '洛阳市', '83');
INSERT INTO `city` VALUES ('140', '平顶山市', '83');
INSERT INTO `city` VALUES ('141', '安阳市', '83');
INSERT INTO `city` VALUES ('142', '鹤壁市', '83');
INSERT INTO `city` VALUES ('143', '新乡市', '83');
INSERT INTO `city` VALUES ('144', '焦作市', '83');
INSERT INTO `city` VALUES ('145', '濮阳市', '83');
INSERT INTO `city` VALUES ('146', '许昌市', '83');
INSERT INTO `city` VALUES ('147', '漯河市', '83');
INSERT INTO `city` VALUES ('148', '三门峡市', '83');
INSERT INTO `city` VALUES ('149', '南阳市', '83');
INSERT INTO `city` VALUES ('150', '商丘市', '83');
INSERT INTO `city` VALUES ('151', '信阳市', '83');
INSERT INTO `city` VALUES ('152', '周口市', '83');
INSERT INTO `city` VALUES ('153', '驻马店市', '83');
INSERT INTO `city` VALUES ('154', '济源市', '83');
INSERT INTO `city` VALUES ('155', '武汉市', '84');
INSERT INTO `city` VALUES ('156', '黄石市', '84');
INSERT INTO `city` VALUES ('157', '十堰市', '84');
INSERT INTO `city` VALUES ('158', '荆州市', '84');
INSERT INTO `city` VALUES ('159', '宜昌市', '84');
INSERT INTO `city` VALUES ('160', '襄樊市', '84');
INSERT INTO `city` VALUES ('161', '鄂州市', '84');
INSERT INTO `city` VALUES ('162', '荆门市', '84');
INSERT INTO `city` VALUES ('163', '孝感市', '84');
INSERT INTO `city` VALUES ('164', '黄冈市', '84');
INSERT INTO `city` VALUES ('165', '咸宁市', '84');
INSERT INTO `city` VALUES ('166', '随州市', '84');
INSERT INTO `city` VALUES ('167', '仙桃市', '84');
INSERT INTO `city` VALUES ('168', '天门市', '84');
INSERT INTO `city` VALUES ('169', '潜江市', '84');
INSERT INTO `city` VALUES ('170', '神农架林区', '84');
INSERT INTO `city` VALUES ('171', '恩施土家族苗族自治州', '84');
INSERT INTO `city` VALUES ('172', '长沙市', '85');
INSERT INTO `city` VALUES ('173', '株洲市', '85');
INSERT INTO `city` VALUES ('174', '湘潭市', '85');
INSERT INTO `city` VALUES ('175', '衡阳市', '85');
INSERT INTO `city` VALUES ('176', '邵阳市', '85');
INSERT INTO `city` VALUES ('177', '岳阳市', '85');
INSERT INTO `city` VALUES ('178', '常德市', '85');
INSERT INTO `city` VALUES ('179', '张家界市', '85');
INSERT INTO `city` VALUES ('180', '益阳市', '85');
INSERT INTO `city` VALUES ('181', '郴州市', '85');
INSERT INTO `city` VALUES ('182', '永州市', '85');
INSERT INTO `city` VALUES ('183', '怀化市', '85');
INSERT INTO `city` VALUES ('184', '娄底市', '85');
INSERT INTO `city` VALUES ('185', '湘西土家族苗族自治州', '85');
INSERT INTO `city` VALUES ('186', '广州市', '86');
INSERT INTO `city` VALUES ('187', '深圳市', '86');
INSERT INTO `city` VALUES ('188', '珠海市', '86');
INSERT INTO `city` VALUES ('189', '汕头市', '86');
INSERT INTO `city` VALUES ('190', '韶关市', '86');
INSERT INTO `city` VALUES ('191', '佛山市', '86');
INSERT INTO `city` VALUES ('192', '江门市', '86');
INSERT INTO `city` VALUES ('193', '湛江市', '86');
INSERT INTO `city` VALUES ('194', '茂名市', '86');
INSERT INTO `city` VALUES ('195', '肇庆市', '86');
INSERT INTO `city` VALUES ('196', '惠州市', '86');
INSERT INTO `city` VALUES ('197', '梅州市', '86');
INSERT INTO `city` VALUES ('198', '汕尾市', '86');
INSERT INTO `city` VALUES ('199', '河源市', '86');
INSERT INTO `city` VALUES ('200', '阳江市', '86');
INSERT INTO `city` VALUES ('201', '清远市', '86');
INSERT INTO `city` VALUES ('202', '东莞市', '86');
INSERT INTO `city` VALUES ('203', '中山市', '86');
INSERT INTO `city` VALUES ('204', '潮州市', '86');
INSERT INTO `city` VALUES ('205', '揭阳市', '86');
INSERT INTO `city` VALUES ('206', '云浮市', '86');
INSERT INTO `city` VALUES ('207', '兰州市', '95');
INSERT INTO `city` VALUES ('208', '金昌市', '95');
INSERT INTO `city` VALUES ('209', '白银市', '95');
INSERT INTO `city` VALUES ('210', '天水市', '95');
INSERT INTO `city` VALUES ('211', '嘉峪关市', '95');
INSERT INTO `city` VALUES ('212', '武威市', '95');
INSERT INTO `city` VALUES ('213', '张掖市', '95');
INSERT INTO `city` VALUES ('214', '平凉市', '95');
INSERT INTO `city` VALUES ('215', '酒泉市', '95');
INSERT INTO `city` VALUES ('216', '庆阳市', '95');
INSERT INTO `city` VALUES ('217', '定西市', '95');
INSERT INTO `city` VALUES ('218', '陇南市', '95');
INSERT INTO `city` VALUES ('219', '临夏回族自治州', '95');
INSERT INTO `city` VALUES ('220', '甘南藏族自治州', '95');
INSERT INTO `city` VALUES ('221', '成都市', '90');
INSERT INTO `city` VALUES ('222', '自贡市', '90');
INSERT INTO `city` VALUES ('223', '攀枝花市', '90');
INSERT INTO `city` VALUES ('224', '泸州市', '90');
INSERT INTO `city` VALUES ('225', '德阳市', '90');
INSERT INTO `city` VALUES ('226', '绵阳市', '90');
INSERT INTO `city` VALUES ('227', '广元市', '90');
INSERT INTO `city` VALUES ('228', '遂宁市', '90');
INSERT INTO `city` VALUES ('229', '内江市', '90');
INSERT INTO `city` VALUES ('230', '乐山市', '90');
INSERT INTO `city` VALUES ('231', '南充市', '90');
INSERT INTO `city` VALUES ('232', '眉山市', '90');
INSERT INTO `city` VALUES ('233', '宜宾市', '90');
INSERT INTO `city` VALUES ('234', '广安市', '90');
INSERT INTO `city` VALUES ('235', '达州市', '90');
INSERT INTO `city` VALUES ('236', '雅安市', '90');
INSERT INTO `city` VALUES ('237', '巴中市', '90');
INSERT INTO `city` VALUES ('238', '资阳市', '90');
INSERT INTO `city` VALUES ('239', '阿坝藏族羌族自治州', '90');
INSERT INTO `city` VALUES ('240', '甘孜藏族自治州', '90');
INSERT INTO `city` VALUES ('241', '凉山彝族自治州', '90');
INSERT INTO `city` VALUES ('242', '贵阳市', '91');
INSERT INTO `city` VALUES ('243', '六盘水市', '91');
INSERT INTO `city` VALUES ('244', '遵义市', '91');
INSERT INTO `city` VALUES ('245', '安顺市', '91');
INSERT INTO `city` VALUES ('246', '铜仁地区', '91');
INSERT INTO `city` VALUES ('247', '毕节地区', '91');
INSERT INTO `city` VALUES ('248', '黔西南布依族苗族自治州', '91');
INSERT INTO `city` VALUES ('249', '黔东南苗族侗族自治州', '91');
INSERT INTO `city` VALUES ('250', '黔南布依族苗族自治州', '91');
INSERT INTO `city` VALUES ('251', '海口市', '88');
INSERT INTO `city` VALUES ('252', '三亚市', '88');
INSERT INTO `city` VALUES ('253', '五指山市', '88');
INSERT INTO `city` VALUES ('254', '琼海市', '88');
INSERT INTO `city` VALUES ('255', '儋州市', '88');
INSERT INTO `city` VALUES ('256', '文昌市', '88');
INSERT INTO `city` VALUES ('257', '万宁市', '88');
INSERT INTO `city` VALUES ('258', '东方市', '88');
INSERT INTO `city` VALUES ('259', '澄迈县', '88');
INSERT INTO `city` VALUES ('260', '定安县', '88');
INSERT INTO `city` VALUES ('261', '屯昌县', '88');
INSERT INTO `city` VALUES ('262', '临高县', '88');
INSERT INTO `city` VALUES ('263', '白沙黎族自治县', '88');
INSERT INTO `city` VALUES ('264', '昌江黎族自治县', '88');
INSERT INTO `city` VALUES ('265', '乐东黎族自治县', '88');
INSERT INTO `city` VALUES ('266', '陵水黎族自治县', '88');
INSERT INTO `city` VALUES ('267', '保亭黎族苗族自治县', '88');
INSERT INTO `city` VALUES ('268', '琼中黎族苗族自治县', '88');
INSERT INTO `city` VALUES ('269', '昆明市', '92');
INSERT INTO `city` VALUES ('270', '曲靖市', '92');
INSERT INTO `city` VALUES ('271', '玉溪市', '92');
INSERT INTO `city` VALUES ('272', '保山市', '92');
INSERT INTO `city` VALUES ('273', '昭通市', '92');
INSERT INTO `city` VALUES ('274', '丽江市', '92');
INSERT INTO `city` VALUES ('275', '思茅市', '92');
INSERT INTO `city` VALUES ('276', '临沧市', '92');
INSERT INTO `city` VALUES ('277', '文山壮族苗族自治州', '92');
INSERT INTO `city` VALUES ('278', '红河哈尼族彝族自治州', '92');
INSERT INTO `city` VALUES ('279', '西双版纳傣族自治州', '92');
INSERT INTO `city` VALUES ('280', '楚雄彝族自治州', '92');
INSERT INTO `city` VALUES ('281', '大理白族自治州', '92');
INSERT INTO `city` VALUES ('282', '德宏傣族景颇族自治州', '92');
INSERT INTO `city` VALUES ('283', '怒江傈傈族自治州', '92');
INSERT INTO `city` VALUES ('284', '迪庆藏族自治州', '92');
INSERT INTO `city` VALUES ('285', '西宁市', '96');
INSERT INTO `city` VALUES ('286', '海东地区', '96');
INSERT INTO `city` VALUES ('287', '海北藏族自治州', '96');
INSERT INTO `city` VALUES ('288', '黄南藏族自治州', '96');
INSERT INTO `city` VALUES ('289', '海南藏族自治州', '96');
INSERT INTO `city` VALUES ('290', '果洛藏族自治州', '96');
INSERT INTO `city` VALUES ('291', '玉树藏族自治州', '96');
INSERT INTO `city` VALUES ('292', '海西蒙古族藏族自治州', '96');
INSERT INTO `city` VALUES ('293', '西安市', '94');
INSERT INTO `city` VALUES ('294', '铜川市', '94');
INSERT INTO `city` VALUES ('295', '宝鸡市', '94');
INSERT INTO `city` VALUES ('296', '咸阳市', '94');
INSERT INTO `city` VALUES ('297', '渭南市', '94');
INSERT INTO `city` VALUES ('298', '延安市', '94');
INSERT INTO `city` VALUES ('299', '汉中市', '94');
INSERT INTO `city` VALUES ('300', '榆林市', '94');
INSERT INTO `city` VALUES ('301', '安康市', '94');
INSERT INTO `city` VALUES ('302', '商洛市', '94');
INSERT INTO `city` VALUES ('303', '南宁市', '87');
INSERT INTO `city` VALUES ('304', '柳州市', '87');
INSERT INTO `city` VALUES ('305', '桂林市', '87');
INSERT INTO `city` VALUES ('306', '梧州市', '87');
INSERT INTO `city` VALUES ('307', '北海市', '87');
INSERT INTO `city` VALUES ('308', '防城港市', '87');
INSERT INTO `city` VALUES ('309', '钦州市', '87');
INSERT INTO `city` VALUES ('310', '贵港市', '87');
INSERT INTO `city` VALUES ('311', '玉林市', '87');
INSERT INTO `city` VALUES ('312', '百色市', '87');
INSERT INTO `city` VALUES ('313', '贺州市', '87');
INSERT INTO `city` VALUES ('314', '河池市', '87');
INSERT INTO `city` VALUES ('315', '来宾市', '87');
INSERT INTO `city` VALUES ('316', '崇左市', '87');
INSERT INTO `city` VALUES ('317', '拉萨市', '93');
INSERT INTO `city` VALUES ('318', '那曲地区', '93');
INSERT INTO `city` VALUES ('319', '昌都地区', '93');
INSERT INTO `city` VALUES ('320', '山南地区', '93');
INSERT INTO `city` VALUES ('321', '日喀则地区', '93');
INSERT INTO `city` VALUES ('322', '阿里地区', '93');
INSERT INTO `city` VALUES ('323', '林芝地区', '93');
INSERT INTO `city` VALUES ('324', '银川市', '97');
INSERT INTO `city` VALUES ('325', '石嘴山市', '97');
INSERT INTO `city` VALUES ('326', '吴忠市', '97');
INSERT INTO `city` VALUES ('327', '固原市', '97');
INSERT INTO `city` VALUES ('328', '中卫市', '97');
INSERT INTO `city` VALUES ('329', '乌鲁木齐市', '98');
INSERT INTO `city` VALUES ('330', '克拉玛依市', '98');
INSERT INTO `city` VALUES ('331', '石河子市　', '98');
INSERT INTO `city` VALUES ('332', '阿拉尔市', '98');
INSERT INTO `city` VALUES ('333', '图木舒克市', '98');
INSERT INTO `city` VALUES ('334', '五家渠市', '98');
INSERT INTO `city` VALUES ('335', '吐鲁番市', '98');
INSERT INTO `city` VALUES ('336', '阿克苏市', '98');
INSERT INTO `city` VALUES ('337', '喀什市', '98');
INSERT INTO `city` VALUES ('338', '哈密市', '98');
INSERT INTO `city` VALUES ('339', '和田市', '98');
INSERT INTO `city` VALUES ('340', '阿图什市', '98');
INSERT INTO `city` VALUES ('341', '库尔勒市', '98');
INSERT INTO `city` VALUES ('342', '昌吉市　', '98');
INSERT INTO `city` VALUES ('343', '阜康市', '98');
INSERT INTO `city` VALUES ('344', '米泉市', '98');
INSERT INTO `city` VALUES ('345', '博乐市', '98');
INSERT INTO `city` VALUES ('346', '伊宁市', '98');
INSERT INTO `city` VALUES ('347', '奎屯市', '98');
INSERT INTO `city` VALUES ('348', '塔城市', '98');
INSERT INTO `city` VALUES ('349', '乌苏市', '98');
INSERT INTO `city` VALUES ('350', '阿勒泰市', '98');
INSERT INTO `city` VALUES ('351', '呼和浩特市', '72');
INSERT INTO `city` VALUES ('352', '包头市', '72');
INSERT INTO `city` VALUES ('353', '乌海市', '72');
INSERT INTO `city` VALUES ('354', '赤峰市', '72');
INSERT INTO `city` VALUES ('355', '通辽市', '72');
INSERT INTO `city` VALUES ('356', '鄂尔多斯市', '72');
INSERT INTO `city` VALUES ('357', '呼伦贝尔市', '72');
INSERT INTO `city` VALUES ('358', '巴彦淖尔市', '72');
INSERT INTO `city` VALUES ('359', '乌兰察布市', '72');
INSERT INTO `city` VALUES ('360', '锡林郭勒盟', '72');
INSERT INTO `city` VALUES ('361', '兴安盟', '72');
INSERT INTO `city` VALUES ('362', '阿拉善盟', '72');
INSERT INTO `city` VALUES ('363', '中西', '99');
INSERT INTO `city` VALUES ('364', '东区', '99');
INSERT INTO `city` VALUES ('365', '九龙城', '99');
INSERT INTO `city` VALUES ('366', '观塘', '99');
INSERT INTO `city` VALUES ('367', '南区', '99');
INSERT INTO `city` VALUES ('368', '深水埗', '99');
INSERT INTO `city` VALUES ('369', '黄大仙', '99');
INSERT INTO `city` VALUES ('370', '湾仔', '99');
INSERT INTO `city` VALUES ('371', '油尖旺', '99');
INSERT INTO `city` VALUES ('372', '离岛', '99');
INSERT INTO `city` VALUES ('373', '葵青', '99');
INSERT INTO `city` VALUES ('374', '北区', '99');
INSERT INTO `city` VALUES ('375', '西贡', '99');
INSERT INTO `city` VALUES ('376', '沙田', '99');
INSERT INTO `city` VALUES ('377', '屯门', '99');
INSERT INTO `city` VALUES ('378', '大埔', '99');
INSERT INTO `city` VALUES ('379', '荃湾', '99');
INSERT INTO `city` VALUES ('380', '元朗', '99');
INSERT INTO `city` VALUES ('381', '花地玛堂区', '100');
INSERT INTO `city` VALUES ('382', '圣安多尼堂区', '100');
INSERT INTO `city` VALUES ('383', '大堂区', '100');
INSERT INTO `city` VALUES ('384', '望德堂区', '100');
INSERT INTO `city` VALUES ('385', '风顺堂区', '100');
INSERT INTO `city` VALUES ('386', '黄浦区', '76');
INSERT INTO `city` VALUES ('387', '卢湾区', '76');
INSERT INTO `city` VALUES ('388', '徐汇区', '76');
INSERT INTO `city` VALUES ('389', '长宁区', '76');
INSERT INTO `city` VALUES ('390', '静安区', '76');
INSERT INTO `city` VALUES ('391', '普陀区', '76');
INSERT INTO `city` VALUES ('392', '闸北区', '76');
INSERT INTO `city` VALUES ('393', '虹口区', '76');
INSERT INTO `city` VALUES ('394', '杨浦区', '76');
INSERT INTO `city` VALUES ('395', '宝山区', '76');
INSERT INTO `city` VALUES ('396', '闵行区', '76');
INSERT INTO `city` VALUES ('397', '嘉定区', '76');
INSERT INTO `city` VALUES ('398', '浦东新区', '76');
INSERT INTO `city` VALUES ('399', '松江区', '76');
INSERT INTO `city` VALUES ('400', '金山区', '76');
INSERT INTO `city` VALUES ('401', '青浦区', '76');
INSERT INTO `city` VALUES ('402', '奉贤区', '76');
INSERT INTO `city` VALUES ('403', '崇明县', '76');
INSERT INTO `city` VALUES ('404', '东城区', '3');
INSERT INTO `city` VALUES ('405', '西城区', '3');
INSERT INTO `city` VALUES ('406', '崇文区', '3');
INSERT INTO `city` VALUES ('407', '宣武区', '3');
INSERT INTO `city` VALUES ('408', '朝阳区', '3');
INSERT INTO `city` VALUES ('409', '丰台区', '3');
INSERT INTO `city` VALUES ('410', '石景山区', '3');
INSERT INTO `city` VALUES ('411', '海淀区', '3');
INSERT INTO `city` VALUES ('412', '门头沟区', '3');
INSERT INTO `city` VALUES ('413', '房山区', '3');
INSERT INTO `city` VALUES ('414', '通州区', '3');
INSERT INTO `city` VALUES ('415', '顺义区', '3');
INSERT INTO `city` VALUES ('416', '昌平区', '3');
INSERT INTO `city` VALUES ('417', '大兴区', '3');
INSERT INTO `city` VALUES ('418', '怀柔区', '3');
INSERT INTO `city` VALUES ('419', '平谷区', '3');
INSERT INTO `city` VALUES ('420', '延庆县', '3');
INSERT INTO `city` VALUES ('421', '密云县 ', '3');
INSERT INTO `city` VALUES ('422', '和平区', '69');
INSERT INTO `city` VALUES ('423', '河东区', '69');
INSERT INTO `city` VALUES ('424', '河西区', '69');
INSERT INTO `city` VALUES ('425', '南开区', '69');
INSERT INTO `city` VALUES ('426', '河北区', '69');
INSERT INTO `city` VALUES ('427', '红桥区', '69');
INSERT INTO `city` VALUES ('428', '塘沽区', '69');
INSERT INTO `city` VALUES ('429', '汉沽区', '69');
INSERT INTO `city` VALUES ('430', '大港区', '69');
INSERT INTO `city` VALUES ('431', '东丽区', '69');
INSERT INTO `city` VALUES ('432', '西青区', '69');
INSERT INTO `city` VALUES ('433', '津南区', '69');
INSERT INTO `city` VALUES ('434', '北辰区', '69');
INSERT INTO `city` VALUES ('435', '武清区', '69');
INSERT INTO `city` VALUES ('436', '宝坻区', '69');
INSERT INTO `city` VALUES ('437', '蓟　县', '69');
INSERT INTO `city` VALUES ('438', '宁河县', '69');
INSERT INTO `city` VALUES ('439', '静海县', '69');
INSERT INTO `city` VALUES ('440', '渝中区', '89');
INSERT INTO `city` VALUES ('441', '大渡口区', '89');
INSERT INTO `city` VALUES ('442', '江北区', '89');
INSERT INTO `city` VALUES ('443', '沙坪坝区', '89');
INSERT INTO `city` VALUES ('444', '九龙坡区', '89');
INSERT INTO `city` VALUES ('445', '南岸区', '89');
INSERT INTO `city` VALUES ('446', '北碚区', '89');
INSERT INTO `city` VALUES ('447', '万盛区', '89');
INSERT INTO `city` VALUES ('448', '双桥区', '89');
INSERT INTO `city` VALUES ('449', '渝北区', '89');
INSERT INTO `city` VALUES ('450', '巴南区', '89');
INSERT INTO `city` VALUES ('451', '万州区', '89');
INSERT INTO `city` VALUES ('452', '涪陵区', '89');
INSERT INTO `city` VALUES ('453', '黔江区', '89');
INSERT INTO `city` VALUES ('454', '长寿区', '89');
INSERT INTO `city` VALUES ('455', '江津区', '89');
INSERT INTO `city` VALUES ('456', '永川区', '89');
INSERT INTO `city` VALUES ('457', '合川区', '89');
INSERT INTO `city` VALUES ('458', '南川区', '89');
INSERT INTO `country` VALUES ('AD', 'ANDORRA', 'Andorra', '', 'AND', '20');
INSERT INTO `country` VALUES ('AE', 'UNITED ARAB EMIRATES', 'United Arab Emirates', '', 'ARE', '784');
INSERT INTO `country` VALUES ('AF', 'AFGHANISTAN', 'Afghanistan', '', 'AFG', '4');
INSERT INTO `country` VALUES ('AG', 'ANTIGUA AND BARBUDA', 'Antigua and Barbuda', '', 'ATG', '28');
INSERT INTO `country` VALUES ('AI', 'ANGUILLA', 'Anguilla', '', 'AIA', '660');
INSERT INTO `country` VALUES ('AL', 'ALBANIA', 'Albania', '', 'ALB', '8');
INSERT INTO `country` VALUES ('AM', 'ARMENIA', 'Armenia', '', 'ARM', '51');
INSERT INTO `country` VALUES ('AN', 'NETHERLANDS ANTILLES', 'Netherlands Antilles', '', 'ANT', '530');
INSERT INTO `country` VALUES ('AO', 'ANGOLA', 'Angola', '', 'AGO', '24');
INSERT INTO `country` VALUES ('AQ', 'ANTARCTICA', 'Antarctica', '', null, null);
INSERT INTO `country` VALUES ('AR', 'ARGENTINA', 'Argentina', '', 'ARG', '32');
INSERT INTO `country` VALUES ('AS', 'AMERICAN SAMOA', 'American Samoa', '', 'ASM', '16');
INSERT INTO `country` VALUES ('AT', 'AUSTRIA', 'Austria', '', 'AUT', '40');
INSERT INTO `country` VALUES ('AU', 'AUSTRALIA', 'Australia', '', 'AUS', '36');
INSERT INTO `country` VALUES ('AW', 'ARUBA', 'Aruba', '', 'ABW', '533');
INSERT INTO `country` VALUES ('AZ', 'AZERBAIJAN', 'Azerbaijan', '', 'AZE', '31');
INSERT INTO `country` VALUES ('BA', 'BOSNIA AND HERZEGOVINA', 'Bosnia and Herzegovina', '', 'BIH', '70');
INSERT INTO `country` VALUES ('BB', 'BARBADOS', 'Barbados', '', 'BRB', '52');
INSERT INTO `country` VALUES ('BD', 'BANGLADESH', 'Bangladesh', '', 'BGD', '50');
INSERT INTO `country` VALUES ('BE', 'BELGIUM', 'Belgium', '', 'BEL', '56');
INSERT INTO `country` VALUES ('BF', 'BURKINA FASO', 'Burkina Faso', '', 'BFA', '854');
INSERT INTO `country` VALUES ('BG', 'BULGARIA', 'Bulgaria', '', 'BGR', '100');
INSERT INTO `country` VALUES ('BH', 'BAHRAIN', 'Bahrain', '', 'BHR', '48');
INSERT INTO `country` VALUES ('BI', 'BURUNDI', 'Burundi', '', 'BDI', '108');
INSERT INTO `country` VALUES ('BJ', 'BENIN', 'Benin', '', 'BEN', '204');
INSERT INTO `country` VALUES ('BM', 'BERMUDA', 'Bermuda', '', 'BMU', '60');
INSERT INTO `country` VALUES ('BN', 'BRUNEI DARUSSALAM', 'Brunei Darussalam', '', 'BRN', '96');
INSERT INTO `country` VALUES ('BO', 'BOLIVIA', 'Bolivia', '', 'BOL', '68');
INSERT INTO `country` VALUES ('BR', 'BRAZIL', 'Brazil', '', 'BRA', '76');
INSERT INTO `country` VALUES ('BS', 'BAHAMAS', 'Bahamas', '', 'BHS', '44');
INSERT INTO `country` VALUES ('BT', 'BHUTAN', 'Bhutan', '', 'BTN', '64');
INSERT INTO `country` VALUES ('BV', 'BOUVET ISLAND', 'Bouvet Island', '', null, null);
INSERT INTO `country` VALUES ('BW', 'BOTSWANA', 'Botswana', '', 'BWA', '72');
INSERT INTO `country` VALUES ('BY', 'BELARUS', 'Belarus', '', 'BLR', '112');
INSERT INTO `country` VALUES ('BZ', 'BELIZE', 'Belize', '', 'BLZ', '84');
INSERT INTO `country` VALUES ('CA', 'CANADA', 'Canada', '', 'CAN', '124');
INSERT INTO `country` VALUES ('CC', 'COCOS (KEELING) ISLANDS', 'Cocos (Keeling) Islands', '', null, null);
INSERT INTO `country` VALUES ('CD', 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'Congo, the Democratic Republic of the', '', 'COD', '180');
INSERT INTO `country` VALUES ('CF', 'CENTRAL AFRICAN REPUBLIC', 'Central African Republic', '', 'CAF', '140');
INSERT INTO `country` VALUES ('CG', 'CONGO', 'Congo', '', 'COG', '178');
INSERT INTO `country` VALUES ('CH', 'SWITZERLAND', 'Switzerland', '', 'CHE', '756');
INSERT INTO `country` VALUES ('CI', 'COTE D\'IVOIRE', 'Cote D\'Ivoire', '', 'CIV', '384');
INSERT INTO `country` VALUES ('CK', 'COOK ISLANDS', 'Cook Islands', '', 'COK', '184');
INSERT INTO `country` VALUES ('CL', 'CHILE', 'Chile', '', 'CHL', '152');
INSERT INTO `country` VALUES ('CM', 'CAMEROON', 'Cameroon', '', 'CMR', '120');
INSERT INTO `country` VALUES ('CN', 'CHINA', 'China', '', 'CHN', '156');
INSERT INTO `country` VALUES ('CO', 'COLOMBIA', 'Colombia', '', 'COL', '170');
INSERT INTO `country` VALUES ('CR', 'COSTA RICA', 'Costa Rica', '', 'CRI', '188');
INSERT INTO `country` VALUES ('CS', 'SERBIA AND MONTENEGRO', 'Serbia and Montenegro', '', null, null);
INSERT INTO `country` VALUES ('CU', 'CUBA', 'Cuba', '', 'CUB', '192');
INSERT INTO `country` VALUES ('CV', 'CAPE VERDE', 'Cape Verde', '', 'CPV', '132');
INSERT INTO `country` VALUES ('CX', 'CHRISTMAS ISLAND', 'Christmas Island', '', null, null);
INSERT INTO `country` VALUES ('CY', 'CYPRUS', 'Cyprus', '', 'CYP', '196');
INSERT INTO `country` VALUES ('CZ', 'CZECH REPUBLIC', 'Czech Republic', '', 'CZE', '203');
INSERT INTO `country` VALUES ('DE', 'GERMANY', 'Germany', '', 'DEU', '276');
INSERT INTO `country` VALUES ('DJ', 'DJIBOUTI', 'Djibouti', '', 'DJI', '262');
INSERT INTO `country` VALUES ('DK', 'DENMARK', 'Denmark', '', 'DNK', '208');
INSERT INTO `country` VALUES ('DM', 'DOMINICA', 'Dominica', '', 'DMA', '212');
INSERT INTO `country` VALUES ('DO', 'DOMINICAN REPUBLIC', 'Dominican Republic', '', 'DOM', '214');
INSERT INTO `country` VALUES ('DZ', 'ALGERIA', 'Algeria', '', 'DZA', '12');
INSERT INTO `country` VALUES ('EC', 'ECUADOR', 'Ecuador', '', 'ECU', '218');
INSERT INTO `country` VALUES ('EE', 'ESTONIA', 'Estonia', '', 'EST', '233');
INSERT INTO `country` VALUES ('EG', 'EGYPT', 'Egypt', '', 'EGY', '818');
INSERT INTO `country` VALUES ('EH', 'WESTERN SAHARA', 'Western Sahara', '', 'ESH', '732');
INSERT INTO `country` VALUES ('ER', 'ERITREA', 'Eritrea', '', 'ERI', '232');
INSERT INTO `country` VALUES ('ES', 'SPAIN', 'Spain', '', 'ESP', '724');
INSERT INTO `country` VALUES ('ET', 'ETHIOPIA', 'Ethiopia', '', 'ETH', '231');
INSERT INTO `country` VALUES ('FI', 'FINLAND', 'Finland', '', 'FIN', '246');
INSERT INTO `country` VALUES ('FJ', 'FIJI', 'Fiji', '', 'FJI', '242');
INSERT INTO `country` VALUES ('FK', 'FALKLAND ISLANDS (MALVINAS)', 'Falkland Islands (Malvinas)', '', 'FLK', '238');
INSERT INTO `country` VALUES ('FM', 'MICRONESIA, FEDERATED STATES OF', 'Micronesia, Federated States of', '', 'FSM', '583');
INSERT INTO `country` VALUES ('FO', 'FAROE ISLANDS', 'Faroe Islands', '', 'FRO', '234');
INSERT INTO `country` VALUES ('FR', 'FRANCE', 'France', '', 'FRA', '250');
INSERT INTO `country` VALUES ('GA', 'GABON', 'Gabon', '', 'GAB', '266');
INSERT INTO `country` VALUES ('GB', 'UNITED KINGDOM', 'United Kingdom', '', 'GBR', '826');
INSERT INTO `country` VALUES ('GD', 'GRENADA', 'Grenada', '', 'GRD', '308');
INSERT INTO `country` VALUES ('GE', 'GEORGIA', 'Georgia', '', 'GEO', '268');
INSERT INTO `country` VALUES ('GF', 'FRENCH GUIANA', 'French Guiana', '', 'GUF', '254');
INSERT INTO `country` VALUES ('GH', 'GHANA', 'Ghana', '', 'GHA', '288');
INSERT INTO `country` VALUES ('GI', 'GIBRALTAR', 'Gibraltar', '', 'GIB', '292');
INSERT INTO `country` VALUES ('GL', 'GREENLAND', 'Greenland', '', 'GRL', '304');
INSERT INTO `country` VALUES ('GM', 'GAMBIA', 'Gambia', '', 'GMB', '270');
INSERT INTO `country` VALUES ('GN', 'GUINEA', 'Guinea', '', 'GIN', '324');
INSERT INTO `country` VALUES ('GP', 'GUADELOUPE', 'Guadeloupe', '', 'GLP', '312');
INSERT INTO `country` VALUES ('GQ', 'EQUATORIAL GUINEA', 'Equatorial Guinea', '', 'GNQ', '226');
INSERT INTO `country` VALUES ('GR', 'GREECE', 'Greece', '', 'GRC', '300');
INSERT INTO `country` VALUES ('GS', 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'South Georgia and the South Sandwich Islands', '', null, null);
INSERT INTO `country` VALUES ('GT', 'GUATEMALA', 'Guatemala', '', 'GTM', '320');
INSERT INTO `country` VALUES ('GU', 'GUAM', 'Guam', '', 'GUM', '316');
INSERT INTO `country` VALUES ('GW', 'GUINEA-BISSAU', 'Guinea-Bissau', '', 'GNB', '624');
INSERT INTO `country` VALUES ('GY', 'GUYANA', 'Guyana', '', 'GUY', '328');
INSERT INTO `country` VALUES ('HK', 'HONG KONG', 'Hong Kong', '', 'HKG', '344');
INSERT INTO `country` VALUES ('HM', 'HEARD ISLAND AND MCDONALD ISLANDS', 'Heard Island and Mcdonald Islands', '', null, null);
INSERT INTO `country` VALUES ('HN', 'HONDURAS', 'Honduras', '', 'HND', '340');
INSERT INTO `country` VALUES ('HR', 'CROATIA', 'Croatia', '', 'HRV', '191');
INSERT INTO `country` VALUES ('HT', 'HAITI', 'Haiti', '', 'HTI', '332');
INSERT INTO `country` VALUES ('HU', 'HUNGARY', 'Hungary', '', 'HUN', '348');
INSERT INTO `country` VALUES ('ID', 'INDONESIA', 'Indonesia', '', 'IDN', '360');
INSERT INTO `country` VALUES ('IE', 'IRELAND', 'Ireland', '', 'IRL', '372');
INSERT INTO `country` VALUES ('IL', 'ISRAEL', 'Israel', '', 'ISR', '376');
INSERT INTO `country` VALUES ('IN', 'INDIA', 'India', '', 'IND', '356');
INSERT INTO `country` VALUES ('IO', 'BRITISH INDIAN OCEAN TERRITORY', 'British Indian Ocean Territory', '', null, null);
INSERT INTO `country` VALUES ('IQ', 'IRAQ', 'Iraq', '', 'IRQ', '368');
INSERT INTO `country` VALUES ('IR', 'IRAN, ISLAMIC REPUBLIC OF', 'Iran, Islamic Republic of', '', 'IRN', '364');
INSERT INTO `country` VALUES ('IS', 'ICELAND', 'Iceland', '', 'ISL', '352');
INSERT INTO `country` VALUES ('IT', 'ITALY', 'Italy', '', 'ITA', '380');
INSERT INTO `country` VALUES ('JM', 'JAMAICA', 'Jamaica', '', 'JAM', '388');
INSERT INTO `country` VALUES ('JO', 'JORDAN', 'Jordan', '', 'JOR', '400');
INSERT INTO `country` VALUES ('JP', 'JAPAN', 'Japan', '', 'JPN', '392');
INSERT INTO `country` VALUES ('KE', 'KENYA', 'Kenya', '', 'KEN', '404');
INSERT INTO `country` VALUES ('KG', 'KYRGYZSTAN', 'Kyrgyzstan', '', 'KGZ', '417');
INSERT INTO `country` VALUES ('KH', 'CAMBODIA', 'Cambodia', '', 'KHM', '116');
INSERT INTO `country` VALUES ('KI', 'KIRIBATI', 'Kiribati', '', 'KIR', '296');
INSERT INTO `country` VALUES ('KM', 'COMOROS', 'Comoros', '', 'COM', '174');
INSERT INTO `country` VALUES ('KN', 'SAINT KITTS AND NEVIS', 'Saint Kitts and Nevis', '', 'KNA', '659');
INSERT INTO `country` VALUES ('KP', 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF', 'Korea, Democratic People\'s Republic of', '', 'PRK', '408');
INSERT INTO `country` VALUES ('KR', 'KOREA, REPUBLIC OF', 'Korea, Republic of', '', 'KOR', '410');
INSERT INTO `country` VALUES ('KW', 'KUWAIT', 'Kuwait', '', 'KWT', '414');
INSERT INTO `country` VALUES ('KY', 'CAYMAN ISLANDS', 'Cayman Islands', '', 'CYM', '136');
INSERT INTO `country` VALUES ('KZ', 'KAZAKHSTAN', 'Kazakhstan', '', 'KAZ', '398');
INSERT INTO `country` VALUES ('LA', 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC', 'Lao People\'s Democratic Republic', '', 'LAO', '418');
INSERT INTO `country` VALUES ('LB', 'LEBANON', 'Lebanon', '', 'LBN', '422');
INSERT INTO `country` VALUES ('LC', 'SAINT LUCIA', 'Saint Lucia', '', 'LCA', '662');
INSERT INTO `country` VALUES ('LI', 'LIECHTENSTEIN', 'Liechtenstein', '', 'LIE', '438');
INSERT INTO `country` VALUES ('LK', 'SRI LANKA', 'Sri Lanka', '', 'LKA', '144');
INSERT INTO `country` VALUES ('LR', 'LIBERIA', 'Liberia', '', 'LBR', '430');
INSERT INTO `country` VALUES ('LS', 'LESOTHO', 'Lesotho', '', 'LSO', '426');
INSERT INTO `country` VALUES ('LT', 'LITHUANIA', 'Lithuania', '', 'LTU', '440');
INSERT INTO `country` VALUES ('LU', 'LUXEMBOURG', 'Luxembourg', '', 'LUX', '442');
INSERT INTO `country` VALUES ('LV', 'LATVIA', 'Latvia', '', 'LVA', '428');
INSERT INTO `country` VALUES ('LY', 'LIBYAN ARAB JAMAHIRIYA', 'Libyan Arab Jamahiriya', '', 'LBY', '434');
INSERT INTO `country` VALUES ('MA', 'MOROCCO', 'Morocco', '', 'MAR', '504');
INSERT INTO `country` VALUES ('MC', 'MONACO', 'Monaco', '', 'MCO', '492');
INSERT INTO `country` VALUES ('MD', 'MOLDOVA, REPUBLIC OF', 'Moldova, Republic of', '', 'MDA', '498');
INSERT INTO `country` VALUES ('MG', 'MADAGASCAR', 'Madagascar', '', 'MDG', '450');
INSERT INTO `country` VALUES ('MH', 'MARSHALL ISLANDS', 'Marshall Islands', '', 'MHL', '584');
INSERT INTO `country` VALUES ('MK', 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'Macedonia, the Former Yugoslav Republic of', '', 'MKD', '807');
INSERT INTO `country` VALUES ('ML', 'MALI', 'Mali', '', 'MLI', '466');
INSERT INTO `country` VALUES ('MM', 'MYANMAR', 'Myanmar', '', 'MMR', '104');
INSERT INTO `country` VALUES ('MN', 'MONGOLIA', 'Mongolia', '', 'MNG', '496');
INSERT INTO `country` VALUES ('MO', 'MACAO', 'Macao', '', 'MAC', '446');
INSERT INTO `country` VALUES ('MP', 'NORTHERN MARIANA ISLANDS', 'Northern Mariana Islands', '', 'MNP', '580');
INSERT INTO `country` VALUES ('MQ', 'MARTINIQUE', 'Martinique', '', 'MTQ', '474');
INSERT INTO `country` VALUES ('MR', 'MAURITANIA', 'Mauritania', '', 'MRT', '478');
INSERT INTO `country` VALUES ('MS', 'MONTSERRAT', 'Montserrat', '', 'MSR', '500');
INSERT INTO `country` VALUES ('MT', 'MALTA', 'Malta', '', 'MLT', '470');
INSERT INTO `country` VALUES ('MU', 'MAURITIUS', 'Mauritius', '', 'MUS', '480');
INSERT INTO `country` VALUES ('MV', 'MALDIVES', 'Maldives', '', 'MDV', '462');
INSERT INTO `country` VALUES ('MW', 'MALAWI', 'Malawi', '', 'MWI', '454');
INSERT INTO `country` VALUES ('MX', 'MEXICO', 'Mexico', '', 'MEX', '484');
INSERT INTO `country` VALUES ('MY', 'MALAYSIA', 'Malaysia', '', 'MYS', '458');
INSERT INTO `country` VALUES ('MZ', 'MOZAMBIQUE', 'Mozambique', '', 'MOZ', '508');
INSERT INTO `country` VALUES ('NA', 'NAMIBIA', 'Namibia', '', 'NAM', '516');
INSERT INTO `country` VALUES ('NC', 'NEW CALEDONIA', 'New Caledonia', '', 'NCL', '540');
INSERT INTO `country` VALUES ('NE', 'NIGER', 'Niger', '', 'NER', '562');
INSERT INTO `country` VALUES ('NF', 'NORFOLK ISLAND', 'Norfolk Island', '', 'NFK', '574');
INSERT INTO `country` VALUES ('NG', 'NIGERIA', 'Nigeria', '', 'NGA', '566');
INSERT INTO `country` VALUES ('NI', 'NICARAGUA', 'Nicaragua', '', 'NIC', '558');
INSERT INTO `country` VALUES ('NL', 'NETHERLANDS', 'Netherlands', '', 'NLD', '528');
INSERT INTO `country` VALUES ('NO', 'NORWAY', 'Norway', '', 'NOR', '578');
INSERT INTO `country` VALUES ('NP', 'NEPAL', 'Nepal', '', 'NPL', '524');
INSERT INTO `country` VALUES ('NR', 'NAURU', 'Nauru', '', 'NRU', '520');
INSERT INTO `country` VALUES ('NU', 'NIUE', 'Niue', '', 'NIU', '570');
INSERT INTO `country` VALUES ('NZ', 'NEW ZEALAND', 'New Zealand', '', 'NZL', '554');
INSERT INTO `country` VALUES ('OM', 'OMAN', 'Oman', '', 'OMN', '512');
INSERT INTO `country` VALUES ('PA', 'PANAMA', 'Panama', '', 'PAN', '591');
INSERT INTO `country` VALUES ('PE', 'PERU', 'Peru', '', 'PER', '604');
INSERT INTO `country` VALUES ('PF', 'FRENCH POLYNESIA', 'French Polynesia', '', 'PYF', '258');
INSERT INTO `country` VALUES ('PG', 'PAPUA NEW GUINEA', 'Papua New Guinea', '', 'PNG', '598');
INSERT INTO `country` VALUES ('PH', 'PHILIPPINES', 'Philippines', '', 'PHL', '608');
INSERT INTO `country` VALUES ('PK', 'PAKISTAN', 'Pakistan', '', 'PAK', '586');
INSERT INTO `country` VALUES ('PL', 'POLAND', 'Poland', '', 'POL', '616');
INSERT INTO `country` VALUES ('PM', 'SAINT PIERRE AND MIQUELON', 'Saint Pierre and Miquelon', '', 'SPM', '666');
INSERT INTO `country` VALUES ('PN', 'PITCAIRN', 'Pitcairn', '', 'PCN', '612');
INSERT INTO `country` VALUES ('PR', 'PUERTO RICO', 'Puerto Rico', '', 'PRI', '630');
INSERT INTO `country` VALUES ('PS', 'PALESTINIAN TERRITORY, OCCUPIED', 'Palestinian Territory, Occupied', '', null, null);
INSERT INTO `country` VALUES ('PT', 'PORTUGAL', 'Portugal', '', 'PRT', '620');
INSERT INTO `country` VALUES ('PW', 'PALAU', 'Palau', '', 'PLW', '585');
INSERT INTO `country` VALUES ('PY', 'PARAGUAY', 'Paraguay', '', 'PRY', '600');
INSERT INTO `country` VALUES ('QA', 'QATAR', 'Qatar', '', 'QAT', '634');
INSERT INTO `country` VALUES ('RE', 'REUNION', 'Reunion', '', 'REU', '638');
INSERT INTO `country` VALUES ('RO', 'ROMANIA', 'Romania', '', 'ROM', '642');
INSERT INTO `country` VALUES ('RU', 'RUSSIAN FEDERATION', 'Russian Federation', '', 'RUS', '643');
INSERT INTO `country` VALUES ('RW', 'RWANDA', 'Rwanda', '', 'RWA', '646');
INSERT INTO `country` VALUES ('SA', 'SAUDI ARABIA', 'Saudi Arabia', '', 'SAU', '682');
INSERT INTO `country` VALUES ('SB', 'SOLOMON ISLANDS', 'Solomon Islands', '', 'SLB', '90');
INSERT INTO `country` VALUES ('SC', 'SEYCHELLES', 'Seychelles', '', 'SYC', '690');
INSERT INTO `country` VALUES ('SD', 'SUDAN', 'Sudan', '', 'SDN', '736');
INSERT INTO `country` VALUES ('SE', 'SWEDEN', 'Sweden', '', 'SWE', '752');
INSERT INTO `country` VALUES ('SG', 'SINGAPORE', 'Singapore', '', 'SGP', '702');
INSERT INTO `country` VALUES ('SH', 'SAINT HELENA', 'Saint Helena', '', 'SHN', '654');
INSERT INTO `country` VALUES ('SI', 'SLOVENIA', 'Slovenia', '', 'SVN', '705');
INSERT INTO `country` VALUES ('SJ', 'SVALBARD AND JAN MAYEN', 'Svalbard and Jan Mayen', '', 'SJM', '744');
INSERT INTO `country` VALUES ('SK', 'SLOVAKIA', 'Slovakia', '', 'SVK', '703');
INSERT INTO `country` VALUES ('SL', 'SIERRA LEONE', 'Sierra Leone', '', 'SLE', '694');
INSERT INTO `country` VALUES ('SM', 'SAN MARINO', 'San Marino', '', 'SMR', '674');
INSERT INTO `country` VALUES ('SN', 'SENEGAL', 'Senegal', '', 'SEN', '686');
INSERT INTO `country` VALUES ('SO', 'SOMALIA', 'Somalia', '', 'SOM', '706');
INSERT INTO `country` VALUES ('SR', 'SURINAME', 'Suriname', '', 'SUR', '740');
INSERT INTO `country` VALUES ('ST', 'SAO TOME AND PRINCIPE', 'Sao Tome and Principe', '', 'STP', '678');
INSERT INTO `country` VALUES ('SV', 'EL SALVADOR', 'El Salvador', '', 'SLV', '222');
INSERT INTO `country` VALUES ('SY', 'SYRIAN ARAB REPUBLIC', 'Syrian Arab Republic', '', 'SYR', '760');
INSERT INTO `country` VALUES ('SZ', 'SWAZILAND', 'Swaziland', '', 'SWZ', '748');
INSERT INTO `country` VALUES ('TC', 'TURKS AND CAICOS ISLANDS', 'Turks and Caicos Islands', '', 'TCA', '796');
INSERT INTO `country` VALUES ('TD', 'CHAD', 'Chad', '', 'TCD', '148');
INSERT INTO `country` VALUES ('TF', 'FRENCH SOUTHERN TERRITORIES', 'French Southern Territories', '', null, null);
INSERT INTO `country` VALUES ('TG', 'TOGO', 'Togo', '', 'TGO', '768');
INSERT INTO `country` VALUES ('TH', 'THAILAND', 'Thailand', '', 'THA', '764');
INSERT INTO `country` VALUES ('TJ', 'TAJIKISTAN', 'Tajikistan', '', 'TJK', '762');
INSERT INTO `country` VALUES ('TK', 'TOKELAU', 'Tokelau', '', 'TKL', '772');
INSERT INTO `country` VALUES ('TL', 'TIMOR-LESTE', 'Timor-Leste', '', null, null);
INSERT INTO `country` VALUES ('TM', 'TURKMENISTAN', 'Turkmenistan', '', 'TKM', '795');
INSERT INTO `country` VALUES ('TN', 'TUNISIA', 'Tunisia', '', 'TUN', '788');
INSERT INTO `country` VALUES ('TO', 'TONGA', 'Tonga', '', 'TON', '776');
INSERT INTO `country` VALUES ('TR', 'TURKEY', 'Turkey', '', 'TUR', '792');
INSERT INTO `country` VALUES ('TT', 'TRINIDAD AND TOBAGO', 'Trinidad and Tobago', '', 'TTO', '780');
INSERT INTO `country` VALUES ('TV', 'TUVALU', 'Tuvalu', '', 'TUV', '798');
INSERT INTO `country` VALUES ('TW', 'TAIWAN, PROVINCE OF CHINA', 'Taiwan, Province of China', '', 'TWN', '158');
INSERT INTO `country` VALUES ('TZ', 'TANZANIA, UNITED REPUBLIC OF', 'Tanzania, United Republic of', '', 'TZA', '834');
INSERT INTO `country` VALUES ('UA', 'UKRAINE', 'Ukraine', '', 'UKR', '804');
INSERT INTO `country` VALUES ('UG', 'UGANDA', 'Uganda', '', 'UGA', '800');
INSERT INTO `country` VALUES ('UM', 'UNITED STATES MINOR OUTLYING ISLANDS', 'United States Minor Outlying Islands', '', null, null);
INSERT INTO `country` VALUES ('US', 'UNITED STATES', 'United States', '', 'USA', '840');
INSERT INTO `country` VALUES ('UY', 'URUGUAY', 'Uruguay', '', 'URY', '858');
INSERT INTO `country` VALUES ('UZ', 'UZBEKISTAN', 'Uzbekistan', '', 'UZB', '860');
INSERT INTO `country` VALUES ('VA', 'HOLY SEE (VATICAN CITY STATE)', 'Holy See (Vatican City State)', '', 'VAT', '336');
INSERT INTO `country` VALUES ('VC', 'SAINT VINCENT AND THE GRENADINES', 'Saint Vincent and the Grenadines', '', 'VCT', '670');
INSERT INTO `country` VALUES ('VE', 'VENEZUELA', 'Venezuela', '', 'VEN', '862');
INSERT INTO `country` VALUES ('VG', 'VIRGIN ISLANDS, BRITISH', 'Virgin Islands, British', '', 'VGB', '92');
INSERT INTO `country` VALUES ('VI', 'VIRGIN ISLANDS, U.S.', 'Virgin Islands, U.s.', '', 'VIR', '850');
INSERT INTO `country` VALUES ('VN', 'VIET NAM', 'Viet Nam', '', 'VNM', '704');
INSERT INTO `country` VALUES ('VU', 'VANUATU', 'Vanuatu', '', 'VUT', '548');
INSERT INTO `country` VALUES ('WF', 'WALLIS AND FUTUNA', 'Wallis and Futuna', '', 'WLF', '876');
INSERT INTO `country` VALUES ('WS', 'SAMOA', 'Samoa', '', 'WSM', '882');
INSERT INTO `country` VALUES ('YE', 'YEMEN', 'Yemen', '', 'YEM', '887');
INSERT INTO `country` VALUES ('YT', 'MAYOTTE', 'Mayotte', '', null, null);
INSERT INTO `country` VALUES ('ZA', 'SOUTH AFRICA', 'South Africa', '', 'ZAF', '710');
INSERT INTO `country` VALUES ('ZM', 'ZAMBIA', 'Zambia', '', 'ZMB', '894');
INSERT INTO `country` VALUES ('ZW', 'ZIMBABWE', 'Zimbabwe', '', 'ZWE', '716');
INSERT INTO `deposit_method` VALUES ('1', '中国工商银行', 'icbc', 'CNY', 'netbank', 'careful', ', editable=False', '1', 'https://mybank.icbc.com.cn/icbc/perbank/index.jsp', 'images/payment/6.jpg', '10.0000', '10000.0000', '1', '2011-04-07 14:20:40');
INSERT INTO `deposit_method` VALUES ('2', '建设银行', 'ccb', 'CNY', 'netbank', 'careful', 'img_logo.allow_tags=True', '1', 'https://ibsbjstar.ccb.com.cn/app/V5/CN/STY1/login.jsp', 'images/payment/7.jpg', '10.0000', '10000.0000', '1', '2011-04-07 14:31:02');
INSERT INTO `deposit_method` VALUES ('3', '支付宝', 'alipay', 'CNY', 'thirdpart', 'careful', 'discriminator', '1', 'http://www.alipay.com/', 'images/payment/alipay.jpg', '10.0000', '5000.0000', '1', '2011-04-07 15:26:39');
INSERT INTO `deposit_method_account` VALUES ('1', '9558801000000000000', '1', '123123', '123123', 'Floyd', '0.0000', '1', '1', '2011-04-07 14:27:03', null, null, '', '');
INSERT INTO `deposit_method_account` VALUES ('2', '9558801000000000001', '2', '123123', '123123', 'floyd', '0.0000', '1', '1', '2011-04-07 14:32:12', null, null, '', '');
INSERT INTO `deposit_method_account` VALUES ('3', '562838@qq.com', '3', '123123', '123123', 'Floyd', '0.0000', '1', '1', '2011-04-07 15:27:39', null, null, '', '');
INSERT INTO `django_admin_log` VALUES ('1', '2011-04-07 14:16:50', '1', '18', '1', '中国工商银行', '1', '');
INSERT INTO `django_admin_log` VALUES ('2', '2011-04-07 14:17:03', '1', '18', '2', '建设银行', '1', '');
INSERT INTO `django_content_type` VALUES ('1', 'permission', 'auth', 'permission');
INSERT INTO `django_content_type` VALUES ('2', 'group', 'auth', 'group');
INSERT INTO `django_content_type` VALUES ('3', 'user', 'auth', 'user');
INSERT INTO `django_content_type` VALUES ('4', 'message', 'auth', 'message');
INSERT INTO `django_content_type` VALUES ('5', 'content type', 'contenttypes', 'contenttype');
INSERT INTO `django_content_type` VALUES ('6', 'session', 'sessions', 'session');
INSERT INTO `django_content_type` VALUES ('7', 'site', 'sites', 'site');
INSERT INTO `django_content_type` VALUES ('8', 'access attempt', 'axes', 'accessattempt');
INSERT INTO `django_content_type` VALUES ('9', 'Country', 'home', 'country');
INSERT INTO `django_content_type` VALUES ('10', 'Province', 'home', 'province');
INSERT INTO `django_content_type` VALUES ('11', 'City', 'home', 'city');
INSERT INTO `django_content_type` VALUES ('12', 'Domain', 'home', 'domain');
INSERT INTO `django_content_type` VALUES ('13', 'Channel', 'home', 'channel');
INSERT INTO `django_content_type` VALUES ('14', 'Announcement', 'home', 'announcement');
INSERT INTO `django_content_type` VALUES ('15', 'profile', 'account', 'userprofile');
INSERT INTO `django_content_type` VALUES ('16', 'User\'s card', 'account', 'usercard');
INSERT INTO `django_content_type` VALUES ('17', 'Game', 'games', 'game');
INSERT INTO `django_content_type` VALUES ('18', 'Bank', 'bank', 'bank');
INSERT INTO `django_content_type` VALUES ('19', 'Card', 'bank', 'card');
INSERT INTO `django_content_type` VALUES ('22', 'log entry', 'admin', 'logentry');
INSERT INTO `django_content_type` VALUES ('23', 'deposit method', 'bank', 'depositmethod');
INSERT INTO `django_content_type` VALUES ('24', 'deposit method account', 'bank', 'depositmethodaccount');
INSERT INTO `django_content_type` VALUES ('25', 'deposit log', 'bank', 'depositlog');
INSERT INTO `django_content_type` VALUES ('26', 'cellphone', 'bank', 'cellphone');
INSERT INTO `django_session` VALUES ('1f004407339089efa193cc187c804869', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36');
INSERT INTO `django_session` VALUES ('295ee0b6f685e7a1d45d2058c82df117', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36');
INSERT INTO `django_session` VALUES ('55e6dfca20be2f283b8078df4eafcd8f', 'NWJjYTkzOTE5ZDJiYTdlNTIwYTA0YzQ4MTVhYjY4ZDE4OWQyM2I3OTqAAn1xAShVDV9hdXRoX3Vz\nZXJfaWSKAQFVEl9hdXRoX3VzZXJfYmFja2VuZFUpZGphbmdvLmNvbnRyaWIuYXV0aC5iYWNrZW5k\ncy5Nb2RlbEJhY2tlbmR1Lg==\n', '2011-04-22 17:16:09');
INSERT INTO `django_session` VALUES ('c80813a9e2dd6460e982653cd6cb59f9', 'YWE1MWViZDI5ZGM3Y2FkN2E1YzkxMzVmZGI0Y2Y1MjVjNzljMTA1MzqAAn1xAS4=\n', '2011-04-22 10:38:36');
INSERT INTO `django_site` VALUES ('1', 'example.com', 'example.com');
INSERT INTO `province` VALUES ('3', '北京市', 'CN');
INSERT INTO `province` VALUES ('4', 'Alaska', 'US');
INSERT INTO `province` VALUES ('5', 'Alabama', 'US');
INSERT INTO `province` VALUES ('6', 'American Samoa', 'US');
INSERT INTO `province` VALUES ('7', 'Arizona', 'US');
INSERT INTO `province` VALUES ('8', 'Arkansas', 'US');
INSERT INTO `province` VALUES ('9', 'California', 'US');
INSERT INTO `province` VALUES ('10', 'Colorado', 'US');
INSERT INTO `province` VALUES ('11', 'Connecticut', 'US');
INSERT INTO `province` VALUES ('12', 'Delaware', 'US');
INSERT INTO `province` VALUES ('13', 'District of Columbia', 'US');
INSERT INTO `province` VALUES ('14', 'Federated province of Micronesia', 'US');
INSERT INTO `province` VALUES ('15', 'Florida', 'US');
INSERT INTO `province` VALUES ('16', 'Georgia', 'US');
INSERT INTO `province` VALUES ('17', 'Guam', 'US');
INSERT INTO `province` VALUES ('18', 'Hawaii', 'US');
INSERT INTO `province` VALUES ('19', 'Idaho', 'US');
INSERT INTO `province` VALUES ('20', 'Illinois', 'US');
INSERT INTO `province` VALUES ('21', 'Indiana', 'US');
INSERT INTO `province` VALUES ('22', 'Iowa', 'US');
INSERT INTO `province` VALUES ('23', 'Kansas', 'US');
INSERT INTO `province` VALUES ('24', 'Kentucky', 'US');
INSERT INTO `province` VALUES ('25', 'Louisiana', 'US');
INSERT INTO `province` VALUES ('26', 'Maine', 'US');
INSERT INTO `province` VALUES ('27', 'Marshall Islands', 'US');
INSERT INTO `province` VALUES ('28', 'Maryland', 'US');
INSERT INTO `province` VALUES ('29', 'Massachusetts', 'US');
INSERT INTO `province` VALUES ('30', 'Michigan', 'US');
INSERT INTO `province` VALUES ('31', 'Minnesota', 'US');
INSERT INTO `province` VALUES ('32', 'Mississippi', 'US');
INSERT INTO `province` VALUES ('33', 'Missouri', 'US');
INSERT INTO `province` VALUES ('34', 'Montana', 'US');
INSERT INTO `province` VALUES ('35', 'Nebraska', 'US');
INSERT INTO `province` VALUES ('36', 'Nevada', 'US');
INSERT INTO `province` VALUES ('37', 'New Hampshire', 'US');
INSERT INTO `province` VALUES ('38', 'New Jersey', 'US');
INSERT INTO `province` VALUES ('39', 'New Mexico', 'US');
INSERT INTO `province` VALUES ('40', 'New York', 'US');
INSERT INTO `province` VALUES ('41', 'North Carolina', 'US');
INSERT INTO `province` VALUES ('42', 'North Dakota', 'US');
INSERT INTO `province` VALUES ('43', 'Northern Mariana Islands', 'US');
INSERT INTO `province` VALUES ('44', 'Ohio', 'US');
INSERT INTO `province` VALUES ('45', 'Oklahoma', 'US');
INSERT INTO `province` VALUES ('46', 'Oregon', 'US');
INSERT INTO `province` VALUES ('47', 'Palau', 'US');
INSERT INTO `province` VALUES ('48', 'Pennsylvania', 'US');
INSERT INTO `province` VALUES ('49', 'Puerto Rico', 'US');
INSERT INTO `province` VALUES ('50', 'Rhode Island', 'US');
INSERT INTO `province` VALUES ('51', 'South Carolina', 'US');
INSERT INTO `province` VALUES ('52', 'South Dakota', 'US');
INSERT INTO `province` VALUES ('53', 'Tennessee', 'US');
INSERT INTO `province` VALUES ('54', 'Texas', 'US');
INSERT INTO `province` VALUES ('55', 'Utah', 'US');
INSERT INTO `province` VALUES ('56', 'Vermont', 'US');
INSERT INTO `province` VALUES ('57', 'Virgin Islands', 'US');
INSERT INTO `province` VALUES ('58', 'Virginia', 'US');
INSERT INTO `province` VALUES ('59', 'Washington', 'US');
INSERT INTO `province` VALUES ('60', 'West Virginia', 'US');
INSERT INTO `province` VALUES ('61', 'Wisconsin', 'US');
INSERT INTO `province` VALUES ('62', 'Wyoming', 'US');
INSERT INTO `province` VALUES ('63', 'Armed Forces Africa', 'US');
INSERT INTO `province` VALUES ('64', 'Armed Forces Americas (except Canada)', 'US');
INSERT INTO `province` VALUES ('65', 'Armed Forces Canada', 'US');
INSERT INTO `province` VALUES ('66', 'Armed Forces Europe', 'US');
INSERT INTO `province` VALUES ('67', 'Armed Forces Middle East', 'US');
INSERT INTO `province` VALUES ('68', 'Armed Forces Pacific', 'US');
INSERT INTO `province` VALUES ('69', '天津市', 'CN');
INSERT INTO `province` VALUES ('70', '河北省', 'CN');
INSERT INTO `province` VALUES ('71', '山西省', 'CN');
INSERT INTO `province` VALUES ('72', '内蒙古自治区', 'CN');
INSERT INTO `province` VALUES ('73', '辽宁省', 'CN');
INSERT INTO `province` VALUES ('74', '吉林省', 'CN');
INSERT INTO `province` VALUES ('75', '黑龙江省', 'CN');
INSERT INTO `province` VALUES ('76', '上海市', 'CN');
INSERT INTO `province` VALUES ('77', '江苏省', 'CN');
INSERT INTO `province` VALUES ('78', '浙江省', 'CN');
INSERT INTO `province` VALUES ('79', '安徽省', 'CN');
INSERT INTO `province` VALUES ('80', '福建省', 'CN');
INSERT INTO `province` VALUES ('81', '江西省', 'CN');
INSERT INTO `province` VALUES ('82', '山东省', 'CN');
INSERT INTO `province` VALUES ('83', '河南省', 'CN');
INSERT INTO `province` VALUES ('84', '湖北省', 'CN');
INSERT INTO `province` VALUES ('85', '湖南省', 'CN');
INSERT INTO `province` VALUES ('86', '广东省', 'CN');
INSERT INTO `province` VALUES ('87', '广西壮族自治区', 'CN');
INSERT INTO `province` VALUES ('88', '海南省', 'CN');
INSERT INTO `province` VALUES ('89', '重庆市', 'CN');
INSERT INTO `province` VALUES ('90', '四川省', 'CN');
INSERT INTO `province` VALUES ('91', '贵州省', 'CN');
INSERT INTO `province` VALUES ('92', '云南省', 'CN');
INSERT INTO `province` VALUES ('93', '西藏自治区', 'CN');
INSERT INTO `province` VALUES ('94', '陕西省', 'CN');
INSERT INTO `province` VALUES ('95', '甘肃省', 'CN');
INSERT INTO `province` VALUES ('96', '青海省', 'CN');
INSERT INTO `province` VALUES ('97', '宁夏回族自治区', 'CN');
INSERT INTO `province` VALUES ('98', '新疆维吾尔自治区', 'CN');
INSERT INTO `province` VALUES ('99', '香港特别行政区', 'CN');
INSERT INTO `province` VALUES ('100', '澳门特别行政区', 'CN');
INSERT INTO `user_profile` VALUES ('1', '1', '1932-02-06', 'M', '9876543', '', 'paseo parkview', '18L', '399', '123454', null, '76', '127.0.0.1', '127.0.0.1', 'CN', '0.0000', '0.0000', '0.0000', '0.0000', null, '0', null);
