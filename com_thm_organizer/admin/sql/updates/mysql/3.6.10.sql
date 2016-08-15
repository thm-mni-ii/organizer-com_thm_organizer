ALTER TABLE `#__thm_organizer_lessons`
  ADD `comment` VARCHAR(200) DEFAULT NULL,
  CHANGE `plan_name` `planName` VARCHAR(10) NOT NULL
COMMENT 'A nomenclature for the source plan in the form XX-PP-YY, where XX is the organization key, PP the planning period and YY the short form for the year.';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_mappings` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`      INT(11) UNSIGNED NOT NULL,
  `plan_subjectID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`, `plan_subjectID`),
  CONSTRAINT `subject_mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `subject_mappings_plan_subjectID_fk` FOREIGN KEY (`plan_subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;