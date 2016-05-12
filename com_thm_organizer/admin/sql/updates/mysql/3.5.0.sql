ALTER TABLE `#__thm_organizer_departments`
  ADD `short_name_en` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  ADD `name_en` VARCHAR ( 255 ) NOT NULL DEFAULT '';

UPDATE `#__thm_organizer_departments`
  SET `short_name_en` = `short_name`, `name_en` = `name`;

ALTER TABLE `#__thm_organizer_departments`
  CHANGE `short_name` `short_name_de` VARCHAR ( 50 ) NOT NULL,
  CHANGE `name` `name_de`VARCHAR ( 255 ) NOT NULL,
  MODIFY `short_name_en` VARCHAR ( 50 ) NOT NULL,
  MODIFY `name_en` VARCHAR ( 255 ) NOT NULL,
  ADD CONSTRAINT UNIQUE (`short_name_en`),
  ADD CONSTRAINT UNIQUE (`name_en`);

ALTER TABLE `#__thm_organizer_schedules` ADD `plan_name` VARCHAR ( 50 ) NOT NULL DEFAULT '';

UPDATE `#__thm_organizer_schedules`
  SET `plan_name` = CONCAT( `departmentname`, '-', `semestername`, '-',  SUBSTRING(`term_enddate`, 3, 2));

ALTER TABLE `#__thm_organizer_schedules` ADD INDEX `plan_name` (`plan_name`);

ALTER TABLE `#__thm_organizer_colors` ADD `name_en` VARCHAR ( 255 ) NOT NULL DEFAULT '';

UPDATE `#__thm_organizer_colors` SET `name_en` = `name`;

ALTER TABLE `#__thm_organizer_colors`
  CHANGE `name` `name_de` VARCHAR ( 60 ) NOT NULL,
  MODIFY `name_en` VARCHAR ( 60 ) NOT NULL;

ALTER TABLE `#__thm_organizer_fields`
  MODIFY `gpuntisID` VARCHAR ( 60 ) NOT NULL DEFAULT '',
  CHANGE `field` `field_de` VARCHAR ( 60 ) NOT NULL DEFAULT '',
  ADD `field_en` VARCHAR ( 60 ) NOT NULL DEFAULT '';

UPDATE `#__thm_organizer_fields` SET `field_en` = `field_de`;

ALTER TABLE `#__thm_organizer_degrees` CHANGE `lsfDegree` `code` varchar ( 10 ) DEFAULT '';

ALTER TABLE `#__thm_organizer_programs`
  CHANGE `subject_de` `name_de` varchar ( 60 ) NOT NULL,
  CHANGE `subject_en` `name_en` varchar ( 60 ) NOT NULL,
  CHANGE `lsfFieldID` `code` varchar ( 20 ) DEFAULT '';

ALTER TABLE `#__thm_organizer_teachers` MODIFY `gpuntisID` VARCHAR ( 60 ) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_rooms` ADD `capacity` INT(4) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_room_types`
  MODIFY `type` VARCHAR ( 150 ) NOT NULL,
  MODIFY `gpuntisID` VARCHAR ( 60 ) NOT NULL DEFAULT '';

UPDATE `#__thm_organizer_room_types` SET `type` = CONCAT(`type`, ', ', `subtype`);

ALTER TABLE `#__thm_organizer_room_types` DROP COLUMN `subtype`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 1 ) NOT NULL COMMENT 'The Untis internal ID',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features_map` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID` INT ( 11 ) UNSIGNED NOT NULL,
  `featureID` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `roomID` (`roomID`),
  KEY `featureID` (`featureID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `abbreviation_de` varchar(45) DEFAULT '',
  `abbreviation_en` varchar(45) DEFAULT '',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `plan_name` VARCHAR( 8 ) NOT NULL COMMENT 'A nomenclature for the source plan in the form XX-PP-YY, where XX is the organization key, PP the planning period and YY the short form for the year.',
  `methodID` INT( 3 ) UNSIGNED DEFAULT NULL COMMENT 'The method of instruction for this lesson unit.',
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  `registration_type` INT( 1 ) UNSIGNED DEFAULT '0' COMMENT 'The method of registration for the lesson. Possible values: 0 - FIFO, 1 - Manual.',
  `max_participants` INT( 4 ) UNSIGNED DEFAULT NULL COMMENT 'The maximum number of participants. NULL is without limit.',
  PRIMARY KEY ( `id` ),
  KEY `plan_name` (`plan_name`),
  KEY `planID` (`untisID`, `plan_name`),
  KEY `methodID` (`methodID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_lessons` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID` INT ( 11 ) UNSIGNED NOT NULL,
  `userID` INT ( 11 ) NOT NULL,
  `status` INT( 1 ) UNSIGNED DEFAULT '0' COMMENT 'The user\'s registration status. Possible values: 0 - pending, 1 - registered, 2 - denied.',
  `user_date` DATETIME COMMENT 'The last date of user action.',
  `status_date` DATETIME COMMENT 'The last date of status action.',
  `order` INT( 4 ) UNSIGNED DEFAULT '0' COMMENT 'The order for automatic user registration actions.',
  `configuration` TEXT NOT NULL DEFAULT '' COMMENT 'A configuration of the lessons visited should the added lessons be a subset of those offered.',
  PRIMARY KEY ( `id` ),
  KEY `lessonID` (`lessonID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_configurations` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID` INT ( 11 ) UNSIGNED NOT NULL,
  `configuration` TEXT NOT NULL DEFAULT '' COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.',
  PRIMARY KEY ( `id` ),
  KEY `lessonID` (`lessonID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `configurationID` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `configurationID` (`configurationID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_grids` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `grid` TEXT NOT NULL DEFAULT '' COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
  `default` INT ( 1 ) NOT NULL DEFAULT '0' COMMENT 'True if the grid is displayed by default.',
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_programs` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL,
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `name` VARCHAR ( 100 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pools` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL,
  `poolID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `name` VARCHAR ( 100 ) NOT NULL,
  `full_name` VARCHAR ( 100 ) NOT NULL COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.',
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`),
  KEY `poolID` (`poolID`),
  KEY `programID` (`programID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_subjects` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL,
  `subjectID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `subjectNo` VARCHAR ( 45 ) NOT NULL DEFAULT '',
  `name` VARCHAR ( 100 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`),
  KEY `subjectID` (`subjectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `surname` VARCHAR ( 100 ) NOT NULL,
  `forename` VARCHAR ( 150 ) NOT NULL DEFAULT '',
  `title` VARCHAR ( 20 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_rooms` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL,
  `roomID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `typeID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `name` VARCHAR ( 100 ) NOT NULL,
  `capacity` INT ( 6 ) UNSIGNED DEFAULT NULL,
  `comment` VARCHAR ( 100 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_department_resources` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `departmentID` INT ( 11 ) UNSIGNED NOT NULL,
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `poolID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `subjectID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `teacherID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `roomID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `departmentID` (`departmentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`),
  KEY `roomID` (`roomID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_subjects` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID` INT ( 11 ) UNSIGNED NOT NULL,
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY ( `id` ),
  KEY `lessonID` (`lessonID`),
  KEY `subjectID` (`subjectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_pools` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `poolID` INT ( 11 ) UNSIGNED NOT NULL,
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY ( `id` ),
  KEY `subjectID` (`subjectID`),
  KEY `poolID` (`poolID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY ( `id` ),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `#__thm_organizer_room_features_map`
ADD CONSTRAINT `room_features_map_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_room_features_map`
ADD CONSTRAINT `room_features_map_featureid_fk` FOREIGN KEY (`featureID`)
REFERENCES `#__thm_organizer_room_features` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lessons`
ADD CONSTRAINT `lessons_methodid_fk` FOREIGN KEY (`methodID`)
REFERENCES `#__thm_organizer_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
ADD CONSTRAINT `user_lessons_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
ADD CONSTRAINT `user_lessons_userid_fk` FOREIGN KEY (`userID`)
REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_configurations`
ADD CONSTRAINT `lesson_configurations_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_calendar`
ADD CONSTRAINT `calendar_configurationid_fk` FOREIGN KEY (`configurationID`)
REFERENCES `#__thm_organizer_lesson_configurations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_programs`
ADD CONSTRAINT `plan_programs_programid_fk` FOREIGN KEY (`programID`)
REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
ADD CONSTRAINT `plan_pools_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
ADD CONSTRAINT `plan_pools_programid_fk` FOREIGN KEY (`programID`)
REFERENCES `#__thm_organizer_plan_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
ADD CONSTRAINT `plan_pools_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_subjects`
ADD CONSTRAINT `plan_subjects_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_subjects`
ADD CONSTRAINT `plan_subjects_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_teachers`
ADD CONSTRAINT `plan_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_teachers`
ADD CONSTRAINT `plan_teachers_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_rooms`
ADD CONSTRAINT `plan_rooms_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_rooms`
ADD CONSTRAINT `plan_rooms_typeid_fk` FOREIGN KEY (`typeID`)
REFERENCES `#__thm_organizer_room_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_departmentid_fk` FOREIGN KEY (`departmentID`)
REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_programid_fk` FOREIGN KEY (`programID`)
REFERENCES `#__thm_organizer_plan_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_plan_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_plan_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_plan_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_subjects`
ADD CONSTRAINT `lesson_subjects_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_subjects`
ADD CONSTRAINT `lesson_subjects_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_plan_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
ADD CONSTRAINT `lesson_pools_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_lesson_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
ADD CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
ADD CONSTRAINT `lesson_teachers_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_lesson_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
ADD CONSTRAINT `lesson_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_plan_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

