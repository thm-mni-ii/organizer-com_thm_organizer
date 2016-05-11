SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `configurationID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `configurationID` (`configurationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name_de` varchar(60) NOT NULL,
  `color` varchar(7) NOT NULL,
  `name_en` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `#__thm_organizer_colors` (`id`, `name_de`, `color`, `name_en`) VALUES
  (1, 'THM Hintergrundgruen', '#cce3a7', 'THM Hintergrundgruen'),
  (2, 'THM Hintergrundgrau', '#b7bec2', 'THM Hintergrundgrau'),
  (3, 'THM Hintergrundrot', '#e199ad', 'THM Hintergrundrot'),
  (4, 'THM Hintergrundgelb', '#fde499', 'THM Hintergrundgelb'),
  (5, 'THM Hintergrundcyan', '#99e1f1', 'THM Hintergrundcyan'),
  (6, 'THM Hintergrundblau', '#99b4d0', 'THM Hintergrundblau'),
  (7, 'THM hellgruen', '#9bd641', 'THM hellgruen'),
  (8, 'THM hellgrau', '#6b7e88', 'THM hellgrau'),
  (9, 'THM hellrot', '#d32154', 'THM hellrot'),
  (10, 'THM hellgelb', '#ffca30', 'THM hellgelb'),
  (11, 'THM hellcyan', '#1dd1f9', 'THM hellcyan'),
  (12, 'THM hellblau', '#2568ae', 'THM hellblau'),
  (13, 'THM gruen', '#80ba24', 'THM gruen'),
  (14, 'THM rot', '#b30033', 'THM rot'),
  (15, 'THM gelb', '#fbbb00', 'THM gelb'),
  (16, 'THM cyanm', '#00b5dd', 'THM cyanm'),
  (17, 'THM mittelgruen', '#71a126', 'THM mittelgruen'),
  (18, 'THM mittelgrau', '#44535b', 'THM mittelgrau'),
  (19, 'THM mittelrot', '#990831', 'THM mittelrot'),
  (20, 'THM mittelgelb', '#d7a30b', 'THM mittelgelb'),
  (21, 'THM mittelcyan', '#099cbd', 'THM mittelcyan'),
  (22, 'THM mittelblau', '#063d76', 'THM mittelblau'),
  (23, 'THM dunkelgruen', '#638929', 'THM dunkelgruen'),
  (24, 'THM dunkelgrau', '#3d494f', 'THM dunkelgrau'),
  (25, 'THM dunkelrot', '#810e2f', 'THM dunkelrot');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(45) NOT NULL DEFAULT '',
  `code` varchar(10) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `#__thm_organizer_degrees` (`id`, `name`, `abbreviation`, `code`) VALUES
  (2, 'Bachelor of Engineering', 'B.Eng.', 'BE'),
  (3, 'Bachelor of Science', 'B.Sc.', 'BS'),
  (4, 'Bachelor of Arts', 'B.A.', 'BA'),
  (5, 'Master of Engineering', 'M.Eng.', 'ME'),
  (6, 'Master of Science', 'M.Sc.', 'MS'),
  (7, 'Master of Arts', 'M.A.', 'MA'),
  (8, 'Master of Business Administration and Engineering', 'M.B.A.', 'MB');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `short_name_de` varchar(50) NOT NULL,
  `name_de` varchar(255) NOT NULL,
  `short_name_en` varchar(50) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  UNIQUE KEY `short_name` (`short_name_de`),
  UNIQUE KEY `name` (`name_de`),
  UNIQUE KEY `short_name_en` (`short_name_en`),
  UNIQUE KEY `name_en` (`name_en`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_department_resources` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `departmentID` int(11) unsigned NOT NULL,
  `programID` int(11) unsigned DEFAULT NULL,
  `poolID` int(11) unsigned DEFAULT NULL,
  `subjectID` int(11) unsigned DEFAULT NULL,
  `teacherID` int(11) unsigned DEFAULT NULL,
  `roomID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departmentID` (`departmentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`),
  KEY `roomID` (`roomID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(60) NOT NULL DEFAULT '',
  `field_de` varchar(60) NOT NULL DEFAULT '',
  `colorID` int(11) unsigned DEFAULT NULL,
  `field_en` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `gpuntisID` (`gpuntisID`),
  KEY `colorID` (`colorID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id` int(1) unsigned NOT NULL,
  `frequency_de` varchar(45) NOT NULL,
  `frequency_en` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_frequencies` (`id`, `frequency_de`, `frequency_en`) VALUES
  (0, 'Nach Termin', 'By Appointment'),
  (1, 'Nur im Sommersemester', 'Only Spring/Summer Term'),
  (2, 'Nur im Wintersemester', 'Only Fall/Winter Term'),
  (3, 'Jedes Semester', 'Semesterly'),
  (4, 'Nach Bedarf', 'As Needed'),
  (5, 'Einmal im Jahr', 'Yearly');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_grids` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `grid` text NOT NULL COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
  `default` int(1) NOT NULL DEFAULT '0' COMMENT 'True if the grid is displayed by default.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `plan_name` varchar(8) NOT NULL COMMENT 'A nomenclature for the source plan in the form XX-PP-YY, where XX is the organization key, PP the planning period and YY the short form for the year.',
  `methodID` int(3) unsigned DEFAULT NULL COMMENT 'The method of instruction for this lesson unit.',
  `delta` varchar(10) NOT NULL DEFAULT '' COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `registration_type` int(1) unsigned DEFAULT '0' COMMENT 'The method of registration for the lesson. Possible values: 0 - FIFO, 1 - Manual.',
  `max_participants` int(4) unsigned DEFAULT NULL COMMENT 'The maximum number of participants. NULL is without limit.',
  PRIMARY KEY (`id`),
  KEY `plan_name` (`plan_name`),
  KEY `planID` (`untisID`,`plan_name`),
  KEY `methodID` (`methodID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_configurations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lessonID` int(11) unsigned NOT NULL,
  `configuration` text NOT NULL COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_pools` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subjectID` int(11) unsigned NOT NULL,
  `poolID` int(11) unsigned NOT NULL,
  `delta` varchar(10) NOT NULL DEFAULT '' COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `poolID` (`poolID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_subjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lessonID` int(11) unsigned NOT NULL,
  `subjectID` int(11) unsigned NOT NULL,
  `delta` varchar(10) NOT NULL DEFAULT '' COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  KEY `subjectID` (`subjectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subjectID` int(11) unsigned NOT NULL,
  `teacherID` int(11) unsigned NOT NULL,
  `delta` varchar(10) NOT NULL DEFAULT '' COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_mappings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `programID` int(11) unsigned DEFAULT NULL,
  `parentID` int(11) unsigned DEFAULT NULL,
  `poolID` int(11) unsigned DEFAULT NULL,
  `subjectID` int(11) unsigned DEFAULT NULL,
  `lft` int(11) unsigned DEFAULT NULL,
  `rgt` int(11) unsigned DEFAULT NULL,
  `level` int(11) unsigned DEFAULT NULL,
  `ordering` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL DEFAULT '' COMMENT 'The Untis internal ID',
  `abbreviation_de` varchar(45) DEFAULT '',
  `abbreviation_en` varchar(45) DEFAULT '',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `roomID` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `useDefaults` tinyint(1) NOT NULL DEFAULT '0',
  `display` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` int(3) unsigned NOT NULL DEFAULT '60' COMMENT 'the amount of seconds before the schedule refreshes',
  `content_refresh` int(3) unsigned NOT NULL DEFAULT '60' COMMENT 'the amount of time in seconds before the content refreshes',
  `interval` int(1) unsigned NOT NULL DEFAULT '1' COMMENT 'the time interval in minutes between context switches',
  `content` varchar(256) DEFAULT '' COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `roomID` (`roomID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pools` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL,
  `poolID` int(11) unsigned DEFAULT NULL,
  `programID` int(11) unsigned DEFAULT NULL,
  `fieldID` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.',
  PRIMARY KEY (`id`),
  KEY `untisID` (`untisID`),
  KEY `poolID` (`poolID`),
  KEY `programID` (`programID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_programs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL,
  `programID` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `untisID` (`untisID`),
  KEY `plan_programs_programid_fk` (`programID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_rooms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL,
  `roomID` int(11) unsigned DEFAULT NULL,
  `typeID` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(6) unsigned DEFAULT NULL,
  `comment` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `untisID` (`untisID`),
  KEY `plan_rooms_roomid_fk` (`roomID`),
  KEY `plan_rooms_typeid_fk` (`typeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_subjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL,
  `subjectID` int(11) unsigned DEFAULT NULL,
  `fieldID` int(11) unsigned DEFAULT NULL,
  `subjectNo` varchar(45) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `untisID` (`untisID`),
  KEY `subjectID` (`subjectID`),
  KEY `plan_subjects_fieldid_fk` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(60) NOT NULL,
  `teacherID` int(11) unsigned DEFAULT NULL,
  `fieldID` int(11) unsigned DEFAULT NULL,
  `surname` varchar(100) NOT NULL,
  `forename` varchar(150) NOT NULL DEFAULT '',
  `title` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `untisID` (`untisID`),
  KEY `plan_teachers_teacherid_fk` (`teacherID`),
  KEY `plan_teachers_fieldid_fk` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `departmentID` int(11) unsigned DEFAULT NULL,
  `lsfID` int(11) unsigned DEFAULT NULL,
  `hisID` int(11) unsigned DEFAULT NULL,
  `externalID` varchar(45) DEFAULT '',
  `description_de` text,
  `description_en` text,
  `abbreviation_de` varchar(45) DEFAULT '',
  `abbreviation_en` varchar(45) DEFAULT '',
  `short_name_de` varchar(45) DEFAULT '',
  `short_name_en` varchar(45) DEFAULT '',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `minCrP` int(3) unsigned DEFAULT '0',
  `maxCrP` int(3) unsigned DEFAULT '0',
  `fieldID` int(11) unsigned DEFAULT NULL,
  `distance` int(2) unsigned DEFAULT '10',
  `display_type` tinyint(1) DEFAULT '1',
  `enable_desc` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `lsfID` (`lsfID`),
  KEY `externalID` (`externalID`),
  KEY `fieldID` (`fieldID`),
  KEY `pools_departmentid_fk` (`departmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subjectID` int(11) unsigned NOT NULL,
  `prerequisite` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`,`prerequisite`),
  KEY `prerequisites_prerequisites_fk` (`prerequisite`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_programs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `departmentID` int(11) unsigned DEFAULT NULL,
  `name_de` varchar(60) NOT NULL,
  `name_en` varchar(60) NOT NULL,
  `version` year(4) DEFAULT NULL,
  `code` varchar(20) DEFAULT '',
  `degreeID` int(11) unsigned DEFAULT NULL,
  `fieldID` int(11) unsigned DEFAULT NULL,
  `description_de` text,
  `description_en` text,
  `frequencyID` int(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lsfData` (`version`,`code`,`degreeID`),
  KEY `degreeID` (`degreeID`),
  KEY `fieldID` (`fieldID`),
  KEY `programs_departmentid_fk` (`departmentID`),
  KEY `programs_frequencyid_fk` (`frequencyID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(10) NOT NULL,
  `longname` varchar(50) NOT NULL DEFAULT '',
  `typeID` int(11) unsigned DEFAULT NULL,
  `capacity` int(4) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `typeID` (`typeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `untisID` varchar(1) NOT NULL COMMENT 'The Untis internal ID',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `roomID` int(11) unsigned NOT NULL,
  `featureID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roomID` (`roomID`),
  KEY `featureID` (`featureID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(60) NOT NULL DEFAULT '',
  `type` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `departmentID` int(11) unsigned DEFAULT NULL,
  `departmentname` varchar(50) NOT NULL,
  `semestername` varchar(50) NOT NULL,
  `creationdate` date DEFAULT NULL,
  `creationtime` time DEFAULT NULL,
  `description` text NOT NULL,
  `schedule` mediumblob NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `term_startdate` date DEFAULT NULL,
  `term_enddate` date DEFAULT NULL,
  `plan_name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `semestername` (`semestername`),
  KEY `schedules_departmentid_fk` (`departmentID`),
  KEY `plan_name` (`plan_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL DEFAULT '0',
  `departmentID` int(11) unsigned DEFAULT NULL,
  `lsfID` int(11) unsigned DEFAULT NULL,
  `hisID` int(11) unsigned DEFAULT NULL,
  `externalID` varchar(45) NOT NULL DEFAULT '',
  `abbreviation_de` varchar(45) NOT NULL DEFAULT '',
  `abbreviation_en` varchar(45) NOT NULL DEFAULT '',
  `short_name_de` varchar(45) NOT NULL DEFAULT '',
  `short_name_en` varchar(45) NOT NULL DEFAULT '',
  `name_de` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description_de` text NOT NULL,
  `description_en` text NOT NULL,
  `objective_de` text NOT NULL,
  `objective_en` text NOT NULL,
  `content_de` text NOT NULL,
  `content_en` text NOT NULL,
  `prerequisites_de` text NOT NULL,
  `prerequisites_en` text NOT NULL,
  `preliminary_work_de` text NOT NULL,
  `preliminary_work_en` text NOT NULL,
  `instructionLanguage` varchar(2) NOT NULL DEFAULT 'D',
  `literature` text NOT NULL,
  `creditpoints` int(4) unsigned NOT NULL DEFAULT '0',
  `expenditure` int(4) unsigned NOT NULL DEFAULT '0',
  `present` int(4) unsigned NOT NULL DEFAULT '0',
  `independent` int(4) unsigned NOT NULL DEFAULT '0',
  `proof_de` text NOT NULL,
  `proof_en` text NOT NULL,
  `frequencyID` int(1) unsigned DEFAULT NULL,
  `method_de` text NOT NULL,
  `method_en` text NOT NULL,
  `fieldID` int(11) unsigned DEFAULT NULL,
  `sws` int(2) unsigned NOT NULL DEFAULT '0',
  `aids_de` text NOT NULL,
  `aids_en` text NOT NULL,
  `evaluation_de` text NOT NULL,
  `evaluation_en` text NOT NULL,
  `expertise` int(1) unsigned DEFAULT NULL,
  `self_competence` int(1) unsigned DEFAULT NULL,
  `method_competence` int(1) unsigned DEFAULT NULL,
  `social_competence` int(1) unsigned DEFAULT NULL,
  `recommended_prerequisites_de` text NOT NULL,
  `recommended_prerequisites_en` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `frequencyID` (`frequencyID`),
  KEY `fieldID` (`fieldID`),
  KEY `subjects_departmentid_fk` (`departmentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subjectID` int(11) unsigned NOT NULL,
  `teacherID` int(11) unsigned NOT NULL,
  `teacherResp` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`subjectID`,`teacherID`,`teacherResp`),
  UNIQUE KEY `id` (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(60) NOT NULL DEFAULT '',
  `surname` varchar(255) NOT NULL,
  `forename` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `fieldID` int(11) unsigned DEFAULT NULL,
  `title` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_lessons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lessonID` int(11) unsigned NOT NULL,
  `userID` int(11) NOT NULL,
  `status` int(1) unsigned DEFAULT '0' COMMENT 'The user''s registration status. Possible values: 0 - pending, 1 - registered, 2 - denied.',
  `user_date` datetime DEFAULT NULL COMMENT 'The last date of user action.',
  `status_date` datetime DEFAULT NULL COMMENT 'The last date of status action.',
  `order` int(4) unsigned DEFAULT '0' COMMENT 'The order for automatic user registration actions.',
  `configuration` text NOT NULL COMMENT 'A configuration of the lessons visited should the added lessons be a subset of those offered.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` varchar(100) NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `#__thm_organizer_calendar`
ADD CONSTRAINT `calendar_configurationid_fk` FOREIGN KEY (`configurationID`) REFERENCES `#__thm_organizer_lesson_configurations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
ADD CONSTRAINT `department_resources_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_plan_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `department_resources_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `department_resources_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `department_resources_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `department_resources_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `department_resources_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_plan_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_fields`
ADD CONSTRAINT `fields_colorid_fk` FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lessons`
ADD CONSTRAINT `lessons_methodid_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_configurations`
ADD CONSTRAINT `lesson_configurations_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
ADD CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `lesson_pools_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_subjects`
ADD CONSTRAINT `lesson_subjects_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `lesson_subjects_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
ADD CONSTRAINT `lesson_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_plan_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `lesson_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_parentid_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_mappings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `mappings_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `mappings_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `mappings_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_monitors`
ADD CONSTRAINT `monitors_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
ADD CONSTRAINT `plan_pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `plan_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `plan_pools_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_programs`
ADD CONSTRAINT `plan_programs_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_rooms`
ADD CONSTRAINT `plan_rooms_typeid_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `plan_rooms_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_subjects`
ADD CONSTRAINT `plan_subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `plan_subjects_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_teachers`
ADD CONSTRAINT `plan_teachers_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `plan_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
ADD CONSTRAINT `pools_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_degreeid_fk` FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `programs_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `programs_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `programs_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
ADD CONSTRAINT `rooms_typeid_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_room_features_map`
ADD CONSTRAINT `room_features_map_featureid_fk` FOREIGN KEY (`featureID`) REFERENCES `#__thm_organizer_room_features` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `room_features_map_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_schedules`
ADD CONSTRAINT `schedules_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `subjects_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `subject_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_teachers`
ADD CONSTRAINT `teachers_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
ADD CONSTRAINT `user_lessons_userid_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `user_lessons_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
