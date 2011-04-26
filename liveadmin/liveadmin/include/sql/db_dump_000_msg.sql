-- 
-- Table structure for table `<%value table_prefix%>msg` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>msg`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>msg` (
`dtm` double(40,10) NOT NULL,
`client_uniq` varchar(40),
`server_uniq` varchar(40),
`direction` smallint(5),
`message_1` varchar(255),
`message_2` varchar(255),
  PRIMARY KEY  (`dtm`),
  KEY `cs1` (`client_uniq`, `server_uniq`, `dtm`),
  KEY `cs2` (`client_uniq`, `server_uniq`, `direction`)
) ENGINE=MyISAM;


ALTER TABLE `<%value table_prefix%>msg` CHANGE `dtm` `dtm` DECIMAL( 40, 10 ) NOT NULL  ;
ALTER TABLE `<%value table_prefix%>msg` ADD `msg_userid` BIGINT( 16 ) DEFAULT '0' AFTER `direction` ;


<%sec-end table%>

-- --------------------------------------------------------

