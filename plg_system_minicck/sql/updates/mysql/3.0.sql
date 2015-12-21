CREATE TABLE IF NOT EXISTS `#__minicck_category_fields` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `content_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;