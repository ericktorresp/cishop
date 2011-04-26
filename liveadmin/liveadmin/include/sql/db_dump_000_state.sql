-- 
-- Table structure for table `<%value table_prefix%>state` 
-- 

<%sec-start drop%>
DROP TABLE IF EXISTS `<%value table_prefix%>state`;
<%sec-end drop%>

<%sec-start table%>
CREATE TABLE IF NOT EXISTS `<%value table_prefix%>state` (
`name` varchar(100) NOT NULL,
`code` varchar(5) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM;
<%sec-end table%>
-- --------------------------------------------------------

-- 
-- Dumping data for table `<%value table_prefix%>state` 
-- 
<%sec-start data%>

INSERT INTO `<%value table_prefix%>state` (`name`, `code`) VALUES ('Outside US and Canada','IT'),
 ('Alabama','AL'),
 ('Alaska','AK'),
 ('Alberta','AB'),
 ('American Samoa','AS'),
 ('Arizona','AZ'),
 ('Arkansas','AR'),
 ('Armed Forces Americas','AA'),
 ('Armed Forces Europe','AE'),
 ('Armed Forces Pacific','AP'),
 ('British Columbia','BC'),
 ('California','CA'),
 ('Colorado','CO'),
 ('Connecticut','CT'),
 ('Delaware','DE'),
 ('District Of Columbia','DC'),
 ('Florida','FL'),
 ('Georgia','GA'),
 ('Guam','GU'),
 ('Hawaii','HI'),
 ('Idaho','ID'),
 ('Illinois','IL'),
 ('Indiana','IN'),
 ('Iowa','IA'),
 ('Kansas','KS'),
 ('Kentucky','KY'),
 ('Louisiana','LA'),
 ('Maine','ME'),
 ('Manitoba','MB'),
 ('Maryland','MD'),
 ('Massachusetts','MA'),
 ('Michigan','MI'),
 ('Minnesota','MN'),
 ('Mississippi','MS'),
 ('Missouri','MO'),
 ('Montana','MT'),
 ('Nebraska','NE'),
 ('Nevada','NV'),
 ('New Brunswick','NB'),
 ('New Hampshire','NH'),
 ('New Jersey','NJ'),
 ('New Mexico','NM'),
 ('New York','NY'),
 ('Newfoundland','NF'),
 ('North Carolina','NC'),
 ('North Dakota','ND'),
 ('Northern Mariana Is','MP'),
 ('Northwest Territories','NW'),
 ('Nova Scotia','NS'),
 ('Nunavut','NT'),
 ('Ohio','OH'),
 ('Oklahoma','OK'),
 ('Ontario','ON'),
 ('Oregon','OR'),
 ('Palau','PW'),
 ('Pennsylvania','PA'),
 ('Prince Edward Island','PE'),
 ('Province du Quebec','QC'),
 ('Puerto Rico','PR'),
 ('Rhode Island','RI'),
 ('Saskatchewan','SK'),
 ('South Carolina','SC'),
 ('South Dakota','SD'),
 ('Tennessee','TN'),
 ('Texas','TX'),
 ('Utah','UT'),
 ('Vermont','VT'),
 ('Virgin Islands','VI'),
 ('Virginia','VA'),
 ('Washington','WA'),
 ('West Virginia','WV'),
 ('Wisconsin','WI'),
 ('Wyoming','WY'),
 ('Yukon Territory','YT'),
 ('Washington DC','DC');
<%sec-end data%>
-- --------------------------------------------------------

