CREATE TABLE IF NOT EXISTS `prefix_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relationship_id` int(11) NOT NULL,
  `folder_guid` bigint(20) unsigned NOT NULL,
  `parent_guid` bigint(20) unsigned NOT NULL,
  `resource_guid` bigint(20) unsigned NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  `title` text NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `relationship_id` (`relationship_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;