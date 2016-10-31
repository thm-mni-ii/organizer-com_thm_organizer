ALTER TABLE `#__thm_organizer_calendar`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `delta` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_calendar_configuration_map`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_colors`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name_de` VARCHAR(60)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `color` VARCHAR(7)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_en` VARCHAR(60)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_degrees`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `abbreviation` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `code` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '';

ALTER TABLE `#__thm_organizer_departments`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `short_name_de` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_de` VARCHAR(150)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `short_name_en` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_en` VARCHAR(150)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_department_resources`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_fields`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `field_de` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `field_en` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_frequencies`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `frequency_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `frequency_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_grids`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name_de` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `name_en` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `grid` TEXT NOT NULL
  COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
  MODIFY `gpuntisID` VARCHAR(60)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_lessons`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `delta` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  MODIFY `comment` VARCHAR(200)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE `#__thm_organizer_lesson_configurations`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `configuration` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL
  COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.';

ALTER TABLE `#__thm_organizer_lesson_pools`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `delta` VARCHAR(10)
CHARSET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.';

ALTER TABLE `#__thm_organizer_lesson_subjects`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `delta` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.';

ALTER TABLE `#__thm_organizer_lesson_teachers`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `delta` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.';

ALTER TABLE `#__thm_organizer_mappings`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_methods`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `abbreviation_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `abbreviation_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `name_de` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `name_en` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE `#__thm_organizer_monitors`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `ip` VARCHAR(15)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `content` VARCHAR(256)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT ''
  COMMENT 'the filename of the resource to the optional resource to be displayed';

ALTER TABLE `#__thm_organizer_planning_periods`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_plan_pools`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `full_name` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL
  COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.';

ALTER TABLE `#__thm_organizer_plan_programs`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_plan_subjects`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `subjectNo` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `name` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `subjectIndex` VARCHAR(70)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_pools`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `externalID` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `description_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci,
  MODIFY `description_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci,
  MODIFY `abbreviation_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `abbreviation_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `short_name_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `short_name_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `name_de` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `name_en` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL;

DROP TABLE `#__thm_organizer_prerequisites`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`    INT(11) UNSIGNED NOT NULL,
  `prerequisite` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`, `prerequisite`),
  KEY `prerequisites_prerequisites_fk` (`prerequisite`)
)
  ENGINE InnoDB
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_prerequisites`
  ADD CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`) REFERENCES `#__thm_organizer_mappings` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_mappings` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name_de` VARCHAR(60)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_en` VARCHAR(60)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `code` VARCHAR(20)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT '',
  MODIFY `description_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci,
  MODIFY `description_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_rooms`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name` VARCHAR(10)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `longname` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_room_features`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `untisID` VARCHAR(1)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL
  COMMENT 'The Untis internal ID',
  MODIFY `name_de` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `name_en` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE `#__thm_organizer_room_features_map`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_room_types`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `name_de` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_en` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `description_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `description_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_schedules`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `departmentname` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `semestername` VARCHAR(50)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `schedule` MEDIUMTEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `newSchedule` MEDIUMTEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;

ALTER TABLE `#__thm_organizer_subjects`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `externalID` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `abbreviation_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `abbreviation_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `short_name_de` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `short_name_en` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `name_de` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `name_en` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `description_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `description_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `objective_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `objective_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `content_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `content_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `prerequisites_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `prerequisites_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `preliminary_work_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `preliminary_work_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `instructionLanguage` VARCHAR(2)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  MODIFY `literature` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `proof_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `proof_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `method_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `method_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `aids_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `aids_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `evaluation_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `evaluation_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `recommended_prerequisites_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `recommended_prerequisites_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  ADD `used_for_de` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  ADD `used_for_en` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subject_mappings`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_subject_teachers`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_teachers`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `surname` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `forename` VARCHAR(255)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  MODIFY `username` VARCHAR(150)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  MODIFY `title` VARCHAR(45)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_user_lessons`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `configuration` TEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL
  COMMENT 'A configuration of the lessons visited should the added lessons be a subset of those offered.';

ALTER TABLE `#__thm_organizer_user_schedules`
  DEFAULT CHARSET utf8mb4
  COLLATE utf8mb4_unicode_ci,
  MODIFY `username` VARCHAR(100)
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY `data` MEDIUMTEXT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci NOT NULL;