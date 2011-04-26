-- 
-- Table structure for table `<%value table_prefix%>draft` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>draft`;
<%sec-end drop%>


<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>draft` (
`userid` bigint(16) NOT NULL,
`message_md5` varchar(32) default NULL,
`edate` bigint(16) default NULL,
`message_tag` varchar(20) default NULL,
`message` text,
PRIMARY KEY  (`userid`,`message_md5`),
KEY `userid_msgtag` (`userid`,`message_tag`)
) ENGINE=MyISAM;

<%sec-end table%>

-- --------------------------------------------------------

