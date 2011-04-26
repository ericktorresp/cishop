-- 
-- Table structure for table `<%value table_prefix%>chat_arch_proto` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>chat_arch_proto`;
<%sec-end drop%>




<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>chat_arch_proto` (
`siteid` bigint(16) NOT NULL,
`userid` bigint(16) NOT NULL,
`chatid` bigint(16) NOT NULL auto_increment,
`start_date` bigint(16),
`end_date` bigint(16),
`msg_count` bigint(16),
`client_nickname` varchar(50),
`client_ip` varchar(20),
`client_info` text,
`message` text,
  PRIMARY KEY  (`siteid`, `userid`, `chatid`),
  KEY `cs1` (`siteid`, `userid`, `start_date`, `end_date`),
  KEY `cs2` (`siteid`, `chatid`)
) ENGINE=MyISAM AUTO_INCREMENT=1;


<%sec-end table%>


-- --------------------------------------------------------

