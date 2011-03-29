/*
MySQL Data Transfer
Source Host: 192.168.1.253
Source Database: passport
Target Host: 192.168.1.253
Target Database: passport
Date: 2011/3/29 18:06:19
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for payport_set
-- ----------------------------
CREATE TABLE `payport_set` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `payport_name` varchar(10) NOT NULL COMMENT '接口名称',
  `payport_nickname` varchar(10) NOT NULL COMMENT '匿名名称',
  `currency` char(3) NOT NULL COMMENT '币种',
  `load_time_note` varchar(20) NOT NULL COMMENT '充值时间说明',
  `draw_time_note` varchar(20) NOT NULL COMMENT '提现时间说明',
  `load_limit_min_per` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '单次最低充值额',
  `load_limit_max_per` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '单次最高充值额',
  `load_fee_per_down` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '充值手续费(每次)',
  `load_fee_percent_down` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '充值手续费(百分比)',
  `load_fee_step` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '充值手续费计算界定金额',
  `load_fee_per_up` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '充值手续费每笔(超过界定值)',
  `load_fee_percent_up` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '充值手续费百分比(超过界定值)',
  `draw_limit_min_per` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '单次最低提现额',
  `draw_limit_max_per` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '单次最高提现额',
  `draw_fee_per_down` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '提现按次手续费',
  `draw_fee_percent_down` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '提现按金额手续费',
  `draw_fee_min` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '提现最低手续费',
  `draw_fee_max` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '提现最高手续费',
  `draw_fee_step` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '提现手续费计算界定金额',
  `draw_fee_per_up` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '提现按次手续费(高于界定)',
  `draw_fee_percent_up` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '提现按金额手续费(高于界定)',
  `plat_load_percent` decimal(5,2) NOT NULL COMMENT '支付接口充值按金额百分比手续费',
  `plat_load_min` decimal(14,4) NOT NULL COMMENT '支付接口充值手续费最低值',
  `plat_load_max` decimal(14,4) NOT NULL COMMENT '支付接口充值手续费最高值',
  `plat_draw_percent` decimal(5,2) NOT NULL COMMENT '支付接口提现按金额百分比手续费',
  `plat_draw_min` decimal(14,4) NOT NULL COMMENT '支付接口提现手续费最低值',
  `plat_draw_max` decimal(14,4) NOT NULL COMMENT '支付接口提现手续费最高值',
  `total_balance` decimal(14,4) NOT NULL DEFAULT '0.0000' COMMENT '各账户累计余额',
  `opt_limit_times` int(4) NOT NULL DEFAULT '0' COMMENT '操作次数限制',
  `payport_host` varchar(100) NOT NULL COMMENT '接口域名',
  `payport_url_load` varchar(100) NOT NULL COMMENT '接口充值URL',
  `payport_url_draw` varchar(100) NOT NULL COMMENT '接口提现URL',
  `payport_url_ques` varchar(100) NOT NULL COMMENT '接口查询URL',
  `receive_host` varchar(100) NOT NULL COMMENT '接收返回域名',
  `receive_url` varchar(100) NOT NULL COMMENT '接收返回URL',
  `receive_url_keep` varchar(100) DEFAULT NULL COMMENT '持续等待接收返回URL',
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '是否启用(0:未启用，1:启用 2:逻辑删除)',
  `payport_intro` text,
  `lang_code` varchar(10) DEFAULT NULL COMMENT '接口所需的语言编码',
  `payport_attr` int(3) DEFAULT '0' COMMENT '接口使用属性配置(标记使用该接口的提现，充值，查询功能)',
  `utime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'timeMark',
  PRIMARY KEY (`id`),
  KEY `invalids` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='支付接口配置表';

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `payport_set` VALUES ('1', '1topay', 'E支付', 'CNY', '即时', '即时', '2.0000', '60000.0000', '0.0000', '0.03', '50.0000', '0.0000', '0.02', '2.0000', '50000.0000', '0.2000', '0.01', '0.5000', '500.0000', '200.0000', '0.5000', '0.01', '0.01', '0.1000', '99999999.0000', '0.01', '1.0000', '1.0000', '4875.6500', '-1', '', '', '', '', 'http://bank.7v92.net/', 'receive/receive_1topay.php', 'receive/callback_1topay.php', '0', '<p>1TOPAY</p>\r\n<p>1TOPAY</p>\r\n<p>1TOPA1</p>', 'gb2312', '1', '2010-09-20 16:53:37');
INSERT INTO `payport_set` VALUES ('4', 'myself', 'T+1提现到卡-人工', 'CNY', '24小时', '24-48小时', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.00', '10.0000', '1000.0000', '1.1000', '0.10', '0.0000', '50.0000', '200.0000', '2.0000', '0.10', '0.00', '0.0000', '0.0000', '0.02', '1.0000', '100.0000', '0.0000', '3', '', '', '', '', 'mmmm', 'mmmm', '', '1', '<p>sss</p>', '', '16', '2010-09-20 16:54:05');
INSERT INTO `payport_set` VALUES ('5', '12', '12', 'RMB', '12', '12', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.0000', '0.00', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '-10', '', '', '', '', '6park.com', '6park.com', '6park.com', '1', '<p>123</p>\r\n<p>123</p>\r\n<p>123</p>', '12', '1', '2010-09-29 10:36:59');
INSERT INTO `payport_set` VALUES ('6', 'test2', '编辑测试', 'DD', '即时', '24小时', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.0000', '0.00', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '922.0000', '-1', '', '', '', '', 'dddas', '22', '', '1', '<p>sss</p>', '', '15', '2010-09-29 10:36:39');
INSERT INTO `payport_set` VALUES ('7', 'mdeposit', 'Email充值', '人民币', '3到5分钟', '3小时', '1000.0000', '5555.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.0000', '0.00', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '102990.8200', '-1', '1', '1', '1', '1', '1', '1', '1', '1', '', 'Email', '3', '2010-10-22 09:45:20');
INSERT INTO `payport_set` VALUES ('8', '1', 'Email充提', '1', '1', '1', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '0.0000', '0.00', '0.00', '0.0000', '0.0000', '0.00', '0.0000', '0.0000', '0.0000', '-1', '1', '1', '1', '1', '1', '1', '1', '2', '', '1', '3', '2010-08-19 15:38:39');
