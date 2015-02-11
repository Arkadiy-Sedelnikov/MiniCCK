DROP TABLE IF EXISTS `#__minicck`;
CREATE TABLE IF NOT EXISTS `#__minicck` (
  `id` int(11) NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `content_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__minicck_categories`;
CREATE TABLE IF NOT EXISTS `#__minicck_categories` (
  `id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`),
  KEY `article_id` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;