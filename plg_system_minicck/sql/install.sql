DROP TABLE IF EXISTS `#__minicck`;
CREATE TABLE IF NOT EXISTS `#__minicck` (
  `id` int(11) NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `content_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
