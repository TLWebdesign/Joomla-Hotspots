-- Hotspots 7.0: own tables for custom fields and images (were the shared #__compojoom_* tables).
-- script.php copies the Hotspots rows from the old tables.

CREATE TABLE IF NOT EXISTS `#__hotspots_customfields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `component` varchar(255) NOT NULL DEFAULT '',
  `show` enum('all', 'category') NOT NULL DEFAULT 'all',
  `type` varchar(100) NOT NULL DEFAULT 'text',
  `options` mediumtext,
  `default` varchar(255) DEFAULT '',
  `allow_empty` tinyint(3) NOT NULL DEFAULT 0,
  `params` mediumtext,
  `enabled` tinyint(3) NOT NULL DEFAULT 1,
  `filter` tinyint(3) NOT NULL DEFAULT 0,
  `ordering` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `created_on` datetime NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT 0,
  `modified_on` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hotspots_customfields_cats` (
  `customfield_id` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  PRIMARY KEY (`customfield_id`, `catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hotspots_multimedia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `type_alias` varchar(255) DEFAULT '',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `mangled_filename` varchar(1024) NOT NULL,
  `mime_type` varchar(255) NOT NULL DEFAULT 'application/octet-stream',
  `origin` varchar(40) DEFAULT 'web',
  `created_on` datetime NULL,
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `enabled` tinyint(4) NOT NULL DEFAULT 1,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
