-- 
-- Table structure for table `<%value table_prefix%>cache` 
-- 
<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>cache`;
<%sec-end drop%>


<%sec-start table%>

CREATE TABLE `<%value table_prefix%>cache` (
`cache_id` varchar(30) NOT NULL,
`update_date` bigint(16) NOT NULL,
`cache_text` text,
PRIMARY KEY  (`cache_id`)
) ENGINE=MyISAM;

<%sec-end table%>

-- --------------------------------------------------------

