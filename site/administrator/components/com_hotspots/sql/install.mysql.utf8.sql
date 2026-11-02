--
-- Hotspots database tables, as of Hotspots 6.0.13.
-- The #__compojoom_* tables used to come from lib_compojoom and can be shared with other
-- Compojoom extensions, so they are only created when missing and never dropped by Hotspots.
--

CREATE TABLE IF NOT EXISTS `#__hotspots_marker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT 0 COMMENT 'FK to #__assets',
  `catid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `street` varchar(255) NOT NULL DEFAULT '',
  `plz` varchar(10) NOT NULL DEFAULT '',
  `town` varchar(255) NOT NULL DEFAULT '',
  `administrative_area_level_1` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `gmlat` float(10,6) NOT NULL,
  `gmlng` float(10,6) NOT NULL,
  `description` mediumtext NOT NULL,
  `description_small` mediumtext NOT NULL,
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `created` datetime NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `publish_up` datetime NULL,
  `publish_down` datetime NULL,
  `modified` datetime NULL,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `customfields` text,
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '*',
  `access` int(10) unsigned NOT NULL DEFAULT 1,
  `import_table` varchar(255) COMMENT 'If we import data from 3rd party components we store the table_id here',
  `import_id` int(11) COMMENT 'Original id of the stored object',
  PRIMARY KEY (`id`),
  KEY `gmlat` (`gmlat`),
  KEY `gmlng` (`gmlng`),
  KEY `catid` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hotspots_kmls` (
  `hotspots_kml_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `catid` int(11) NOT NULL COMMENT 'FK to #__categories',
  `description` text NOT NULL,
  `original_filename` varchar(1024) NOT NULL,
  `mangled_filename` varchar(1024) NOT NULL,
  `mime_type` varchar(255) NOT NULL DEFAULT 'application/octet-stream',
  `created` datetime NULL,
  `created_by` bigint(20) NOT NULL DEFAULT 0,
  `state` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`hotspots_kml_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__hotspots_mappings` (
  `marker_id` int(11) NOT NULL,
  `foreign_id` int(11) NOT NULL,
  `component` varchar(191) DEFAULT '',
  PRIMARY KEY (`marker_id`, `foreign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__compojoom_customfields` (
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

CREATE TABLE IF NOT EXISTS `#__compojoom_customfields_cats` (
  `compojoom_customfields_id` int(11) NOT NULL,
  `catid` int(11) NOT NULL,
  PRIMARY KEY (`compojoom_customfields_id`, `catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__compojoom_multimedia` (
  `compojoom_multimedia_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`compojoom_multimedia_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
