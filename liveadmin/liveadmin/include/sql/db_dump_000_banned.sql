-- 
-- Table structure for table `<%value table_prefix%>banned` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>banned`;
<%sec-end drop%>


<%sec-start table%>

CREATE TABLE `<%value table_prefix%>banned` (
`siteid` bigint(16) NOT NULL,
`bid` bigint(16) NOT NULL auto_increment,
`active` smallint(5) NOT NULL default '1',
`client_ip` varchar(20) NOT NULL default '',
`client_cookie` varchar(34) NOT NULL default '',
`expiry_date` bigint(16) NOT NULL default '0',
`userid` bigint(16) NOT NULL default '0',
PRIMARY KEY  (`siteid`,`bid`),
KEY `siteid` (`siteid`,`active`,`client_ip`,`client_cookie`,`expiry_date`),
KEY `siteid_2` (`siteid`,`client_ip`,`client_cookie`),
KEY `expiry_date` (`expiry_date`)
) ENGINE=MyISAM AUTO_INCREMENT=1000;

<%sec-end table%>

-- --------------------------------------------------------

