DROP TABLE IF EXISTS `#__thm_organizer_users`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT ( 11 ) NOT NULL,
  `short_name` VARCHAR ( 50 ) NOT NULL,
  `name` VARCHAR ( 255 ) NOT NULL,
  KEY ( `id` ),
  UNIQUE ( `short_name` ),
  UNIQUE ( `name` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__thm_organizer_schedules`
ADD `asset_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
ADD `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `asset_id`;

ALTER TABLE `#__thm_organizer_programs`
ADD `asset_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
ADD `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `asset_id`;

ALTER TABLE `#__thm_organizer_pools`
ADD `asset_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
ADD `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `asset_id`;

ALTER TABLE `#__thm_organizer_subjects`
ADD `asset_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
ADD `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `asset_id`;

ALTER TABLE `#__thm_organizer_schedules`
ADD CONSTRAINT `schedules_departmentid_fk` FOREIGN KEY (`departmentID`)
REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_departmentid_fk` FOREIGN KEY (`departmentID`)
REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
ADD CONSTRAINT `pools_departmentid_fk` FOREIGN KEY (`departmentID`)
REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_departmentid_fk` FOREIGN KEY (`departmentID`)
REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;