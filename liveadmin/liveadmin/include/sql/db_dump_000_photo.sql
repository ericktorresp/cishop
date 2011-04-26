-- 
-- Table structure for table `<%value table_prefix%>visitors` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>photo`;
<%sec-end drop%>

<%sec-start table%>


CREATE TABLE `<%value table_prefix%>photo` (
`userid` bigint(16) NOT NULL,
`siteid` bigint(16) NOT NULL,
`photo_small` longblob,
`photo_large` longblob,
PRIMARY KEY  (`userid`,`siteid`)
) ENGINE=MyISAM;


<%sec-end table%>


-- --------------------------------------------------------

