


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
  PRIMARY KEY ( `id` ),
  KEY `configurationID` (`configurationID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_programs` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `name` VARCHAR ( 100 ) NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pools` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `poolID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `untisID` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `short_name` VARCHAR ( 60 ) NOT NULL,
  `long_name` VARCHAR ( 100 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `poolID` (`poolID`),
  KEY `programID` (`programID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_subjects` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR ( 60 ) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `subjectNo` VARCHAR ( 45 ) NOT NULL DEFAULT '',
  `short_name` VARCHAR ( 60 ) NOT NULL,
  `long_name` VARCHAR ( 100 ) NOT NULL,
  `counter` INT ( 11 ) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY ( `id` ),
  KEY `untisID` (`untisID`),
  KEY `subjectID` (`subjectID`)
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
  `lesson_subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `poolID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `programID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY ( `id` ),
  KEY `lesson_subjectID` (`lesson_subjectID`),
  KEY `poolID` (`poolID`),
  KEY `programID` (`programID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lesson_subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
  `delta` VARCHAR( 10 ) NOT NULL DEFAULT '' COMMENT 'The lesson\'s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY ( `id` ),
  KEY `lesson_subjectID` (`lesson_subjectID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



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




ALTER TABLE `#__thm_organizer_lesson_subjects`
ADD CONSTRAINT `lesson_subjects_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_subjects`
ADD CONSTRAINT `lesson_subjects_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
ADD CONSTRAINT `lesson_pools_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_lesson_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
ADD CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

