-- 
-- Table structure for table `<%value table_prefix%>news` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>news`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>news` (
`siteid` bigint(16) NOT NULL,
`newsid` bigint(16) NOT NULL auto_increment,
`n_date` bigint(16) NOT NULL,
`n_active` smallint(5) NOT NULL,
`n_sticky` smallint(5) NOT NULL,
`n_link` varchar(255) default NULL,
`n_title` varchar(255) default NULL,
`n_text` longtext,
PRIMARY KEY  (`siteid`,`newsid`),
KEY `n_date` (`n_active`,`n_date`)
) ENGINE=MyISAM;




<%sec-end table%>

-- --------------------------------------------------------

