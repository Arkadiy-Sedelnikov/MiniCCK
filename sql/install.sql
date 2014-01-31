DROP TABLE IF EXISTS `#__minicck`;
CREATE TABLE IF NOT EXISTS `#__minicck` (
  `id` int(11) NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `field` VARCHAR( 50 ) NOT NULL,
  `field_values` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `field` (`field`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;