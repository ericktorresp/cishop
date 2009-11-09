/*
SQLyog Enterprise - MySQL GUI v6.14
MySQL - 5.0.45-community-nt-log : Database - cishop
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

create database if not exists `cishop`;

USE `cishop`;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `be_acl_actions` */

DROP TABLE IF EXISTS `be_acl_actions`;

CREATE TABLE `be_acl_actions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(254) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `be_acl_actions` */

/*Table structure for table `be_acl_groups` */

DROP TABLE IF EXISTS `be_acl_groups`;

CREATE TABLE `be_acl_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(254) NOT NULL,
  `link` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `be_acl_groups` */

insert  into `be_acl_groups`(`id`,`lft`,`rgt`,`name`,`link`) values (1,1,4,'Member',NULL),(2,2,3,'Administrator',NULL);

/*Table structure for table `be_acl_permission_actions` */

DROP TABLE IF EXISTS `be_acl_permission_actions`;

CREATE TABLE `be_acl_permission_actions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `access_id` int(10) unsigned NOT NULL default '0',
  `axo_id` int(10) unsigned NOT NULL default '0',
  `allow` char(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `access_id` (`access_id`),
  KEY `axo_id` (`axo_id`),
  CONSTRAINT `be_acl_permission_actions_ibfk_1` FOREIGN KEY (`access_id`) REFERENCES `be_acl_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `be_acl_permission_actions_ibfk_2` FOREIGN KEY (`axo_id`) REFERENCES `be_acl_actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `be_acl_permission_actions` */

/*Table structure for table `be_acl_permissions` */

DROP TABLE IF EXISTS `be_acl_permissions`;

CREATE TABLE `be_acl_permissions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `aro_id` int(10) unsigned NOT NULL default '0',
  `aco_id` int(10) unsigned NOT NULL default '0',
  `allow` char(1) default NULL,
  PRIMARY KEY  (`id`),
  KEY `aro_id` (`aro_id`),
  KEY `aco_id` (`aco_id`),
  CONSTRAINT `be_acl_permissions_ibfk_1` FOREIGN KEY (`aro_id`) REFERENCES `be_acl_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `be_acl_permissions_ibfk_2` FOREIGN KEY (`aco_id`) REFERENCES `be_acl_resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `be_acl_permissions` */

insert  into `be_acl_permissions`(`id`,`aro_id`,`aco_id`,`allow`) values (1,2,1,'Y');

/*Table structure for table `be_acl_resources` */

DROP TABLE IF EXISTS `be_acl_resources`;

CREATE TABLE `be_acl_resources` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `lft` int(10) unsigned NOT NULL default '0',
  `rgt` int(10) unsigned NOT NULL default '0',
  `name` varchar(254) NOT NULL,
  `link` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `be_acl_resources` */

insert  into `be_acl_resources`(`id`,`lft`,`rgt`,`name`,`link`) values (1,1,22,'Site',NULL),(2,2,21,'Control Panel',NULL),(3,3,20,'System',NULL),(4,14,15,'Members',NULL),(5,4,13,'Access Control',NULL),(6,16,17,'Settings',NULL),(7,18,19,'Utilities',NULL),(8,11,12,'Permissions',NULL),(9,9,10,'Groups',NULL),(10,7,8,'Resources',NULL),(11,5,6,'Actions',NULL);

/*Table structure for table `be_groups` */

DROP TABLE IF EXISTS `be_groups`;

CREATE TABLE `be_groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `locked` tinyint(1) unsigned NOT NULL default '0',
  `disabled` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  CONSTRAINT `be_groups_ibfk_1` FOREIGN KEY (`id`) REFERENCES `be_acl_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `be_groups` */

insert  into `be_groups`(`id`,`locked`,`disabled`) values (1,1,0),(2,1,0);

/*Table structure for table `be_preferences` */

DROP TABLE IF EXISTS `be_preferences`;

CREATE TABLE `be_preferences` (
  `name` varchar(254) character set latin1 NOT NULL,
  `value` text character set latin1 NOT NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `be_preferences` */

insert  into `be_preferences`(`name`,`value`) values ('default_user_group','1'),('smtp_host',''),('keep_error_logs_for','30'),('email_protocol','sendmail'),('use_registration_captcha','0'),('page_debug','1'),('automated_from_name','BackendPro'),('allow_user_registration','1'),('use_login_captcha','0'),('site_name','cishop'),('automated_from_email','noreply@backendpro.co.uk'),('account_activation_time','7'),('allow_user_profiles','1'),('activation_method','email'),('autologin_period','30'),('min_password_length','8'),('smtp_user',''),('smtp_pass',''),('email_mailpath','/usr/sbin/sendmail'),('smtp_port','25'),('smtp_timeout','5'),('email_wordwrap','1'),('email_wrapchars','76'),('email_mailtype','text'),('email_charset','utf-8'),('bcc_batch_mode','0'),('bcc_batch_size','200'),('login_field','email');

/*Table structure for table `be_resources` */

DROP TABLE IF EXISTS `be_resources`;

CREATE TABLE `be_resources` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `locked` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  CONSTRAINT `be_resources_ibfk_1` FOREIGN KEY (`id`) REFERENCES `be_acl_resources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `be_resources` */

insert  into `be_resources`(`id`,`locked`) values (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1);

/*Table structure for table `be_user_profiles` */

DROP TABLE IF EXISTS `be_user_profiles`;

CREATE TABLE `be_user_profiles` (
  `user_id` int(10) unsigned NOT NULL,
  `gender` char(1) default 'm',
  PRIMARY KEY  (`user_id`),
  CONSTRAINT `be_user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `be_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `be_user_profiles` */

insert  into `be_user_profiles`(`user_id`,`gender`) values (1,'m');

/*Table structure for table `be_users` */

DROP TABLE IF EXISTS `be_users`;

CREATE TABLE `be_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(32) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(254) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL default '0',
  `group` int(10) unsigned default NULL,
  `activation_key` varchar(32) default NULL,
  `last_visit` datetime default NULL,
  `created` datetime NOT NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `password` (`password`),
  KEY `group` (`group`),
  CONSTRAINT `be_users_ibfk_1` FOREIGN KEY (`group`) REFERENCES `be_acl_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `be_users` */

insert  into `be_users`(`id`,`username`,`password`,`email`,`active`,`group`,`activation_key`,`last_visit`,`created`,`modified`) values (1,'root','0ccaccc35c30d4a60e0c8738a9123534c8b37865','c-mtv@163.com',1,2,NULL,'2009-11-03 11:05:34','2009-11-03 10:31:35','2009-11-03 11:04:15');

/*Table structure for table `ci_sessions` */

DROP TABLE IF EXISTS `ci_sessions`;

CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) character set latin1 NOT NULL default '0',
  `ip_address` varchar(16) character set latin1 NOT NULL default '0',
  `user_agent` varchar(50) character set latin1 NOT NULL,
  `user_data` text NOT NULL,
  `last_activity` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `ci_sessions` */

insert  into `ci_sessions`(`session_id`,`ip_address`,`user_agent`,`user_data`,`last_activity`) values ('d814fea3669cc42db2ae71ca90cc23a8','127.0.0.1','Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv','a:11:{s:2:\"id\";s:1:\"1\";s:8:\"username\";s:4:\"root\";s:5:\"email\";s:13:\"c-mtv@163.com\";s:8:\"password\";s:40:\"0ccaccc35c30d4a60e0c8738a9123534c8b37865\";s:6:\"active\";s:1:\"1\";s:10:\"last_visit\";s:19:\"2009-11-03 09:50:57\";s:7:\"created\";s:19:\"2009-11-03 10:31:35\";s:8:\"modified\";s:19:\"2009-11-03 11:04:15\";s:5:\"group\";s:13:\"Administrator\";s:8:\"group_id\";s:1:\"2\";s:6:\"gender\";s:1:\"m\";}',1257247172);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;