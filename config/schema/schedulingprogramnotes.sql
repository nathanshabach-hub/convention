-- Schedulingprogramnotes table for storing Small Program notes
CREATE TABLE IF NOT EXISTS `schedulingprogramnotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conventionseasons_id` int(11) NOT NULL,
  `notes_json` longtext COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_convention_season` (`conventionseasons_id`),
  KEY `conventionseasons_id` (`conventionseasons_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
