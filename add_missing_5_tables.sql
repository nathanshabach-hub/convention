CREATE TABLE IF NOT EXISTS `divisions_19may2025` LIKE `divisions`;
INSERT INTO `divisions_19may2025` SELECT * FROM `divisions`;

CREATE TABLE IF NOT EXISTS `emailtemplates_19may2025` LIKE `emailtemplates`;
INSERT INTO `emailtemplates_19may2025` SELECT * FROM `emailtemplates`;

CREATE TABLE IF NOT EXISTS `events_19may2025` LIKE `events`;
INSERT INTO `events_19may2025` SELECT * FROM `events`;

CREATE TABLE IF NOT EXISTS `events_backup_30May2024` LIKE `events`;
INSERT INTO `events_backup_30May2024` SELECT * FROM `events`;

CREATE TABLE IF NOT EXISTS `schedulingtimings_backup_20260601` LIKE `schedulingtimings`;
INSERT INTO `schedulingtimings_backup_20260601` SELECT * FROM `schedulingtimings`;
