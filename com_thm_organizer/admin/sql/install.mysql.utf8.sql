SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_users` (
  `userID` INT ( 11 ) NOT NULL,
  `program_manager` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `planner` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  KEY ( `userID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `departmentname` VARCHAR ( 50 ) NOT NULL,
  `semestername` VARCHAR ( 50 ) NOT NULL,
  `creationdate` date DEFAULT NULL,
  `creationtime` time DEFAULT NULL,
  `description` TEXT NOT NULL DEFAULT '',
  `schedule` mediumblob NOT NULL,
  `active` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `term_startdate` date DEFAULT NULL,
  `term_enddate` date DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `semestername` ( `semestername` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 50 ) NOT NULL,
  `type` VARCHAR ( 50 ) NOT NULL,
  `responsible` VARCHAR ( 50 ) NOT NULL,
  `department` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `semestername` VARCHAR ( 50 ) NOT NULL,
  PRIMARY KEY `id` ( `id` ),
  KEY `semestername` ( `semestername` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vid` INT ( 11 ) UNSIGNED NOT NULL,
  `eid` VARCHAR ( 20 ) NOT NULL,
  PRIMARY KEY `id` ( `id` ),
  KEY `vid` ( `vid` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` VARCHAR ( 100 ) NOT NULL,
  `created` INT ( 11 ) UNSIGNED NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY ( `username` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `color` VARCHAR ( 6 ) NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `field` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `colorID` INT(11) unsigned DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `gpuntisID` ( `gpuntisID` ),
  KEY `colorID` (`colorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `abbreviation` VARCHAR ( 45 ) NOT NULL DEFAULT '',
  `lsfDegree` varchar ( 10 ) DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_programs` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_de` varchar(255) NOT NULL,
  `subject_en` varchar(255) NOT NULL,
  `version` year (4) DEFAULT NULL,
  `lsfFieldID` varchar(20) DEFAULT '',
  `degreeID` INT(11) unsigned DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  `description_de` text DEFAULT '',
  `description_en` text DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lsfData` (`version`, `lsfFieldID`, `degreeID`),
  KEY `degreeID` (`degreeID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lsfID` INT(11) UNSIGNED DEFAULT NULL,
  `hisID` INT(11) UNSIGNED DEFAULT NULL,
  `externalID` varchar(45) DEFAULT '',
  `description_de` text DEFAULT '',
  `description_en` text DEFAULT '',
  `abbreviation_de` varchar(45) DEFAULT '',
  `abbreviation_en` varchar(45) DEFAULT '',
  `short_name_de` varchar(45) DEFAULT '',
  `short_name_en` varchar(45) DEFAULT '',
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `minCrP` INT(3) UNSIGNED DEFAULT 0,
  `maxCrP` INT(3) UNSIGNED DEFAULT 0,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  `distance` INT(2) UNSIGNED DEFAULT 10,
  `display_type` boolean DEFAULT TRUE,
  `enable_desc` boolean DEFAULT TRUE,
  PRIMARY KEY (id),
  KEY `lsfID` ( `lsfID` ),
  KEY `externalID` ( `externalID` ),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id` INT (1) UNSIGNED NOT NULL,
  `frequency_de` varchar (45) NOT NULL,
  `frequency_en` varchar (45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lsfID` INT(11) UNSIGNED DEFAULT NULL,
  `hisID` INT(11) UNSIGNED DEFAULT NULL,
  `externalID` varchar(45) NOT NULL DEFAULT '',
  `abbreviation_de` varchar(45) NOT NULL DEFAULT '',
  `abbreviation_en` varchar(45) NOT NULL DEFAULT '',
  `short_name_de` varchar(45) NOT NULL DEFAULT '',
  `short_name_en` varchar(45) NOT NULL DEFAULT '',
  `name_de` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `description_de` text NOT NULL DEFAULT '',
  `description_en` text NOT NULL DEFAULT '',
  `objective_de` text NOT NULL DEFAULT '',
  `objective_en` text NOT NULL DEFAULT '',
  `content_de` text NOT NULL DEFAULT '',
  `content_en` text NOT NULL DEFAULT '',
  `prerequisites_de` text NOT NULL DEFAULT '',
  `prerequisites_en` text NOT NULL DEFAULT '',
  `preliminary_work_de` varchar(255) NOT NULL DEFAULT '',
  `preliminary_work_en` varchar(255) NOT NULL DEFAULT '',
  `instructionLanguage` varchar(2)  NOT NULL DEFAULT 'D',
  `literature` text NOT NULL DEFAULT '',
  `creditpoints` INT(4) UNSIGNED NOT NULL DEFAULT 0,
  `expenditure` INT(4) UNSIGNED NOT NULL DEFAULT 0,
  `present` INT(4) UNSIGNED NOT NULL DEFAULT 0,
  `independent` INT(4) UNSIGNED NOT NULL DEFAULT 0,
  `proof_de` varchar(255) NOT NULL DEFAULT '',
  `proof_en` varchar(255) NOT NULL DEFAULT '',
  `frequencyID` INT(1) UNSIGNED DEFAULT NULL,
  `method_de` varchar(255) NOT NULL DEFAULT '',
  `method_en` varchar(255) NOT NULL DEFAULT '',
  `fieldID` INT(11) unsigned DEFAULT NULL,
  `sws` INT( 2 ) UNSIGNED NOT NULL DEFAULT 0,
  `aids_de` TEXT NOT NULL DEFAULT '',
  `aids_en` TEXT NOT NULL DEFAULT '',
  `evaluation_de` TEXT NOT NULL DEFAULT '',
  `evaluation_en` TEXT NOT NULL DEFAULT '',
  `expertise` INT( 1 ) UNSIGNED NULL DEFAULT NULL,
  `self_competence` INT( 1 ) UNSIGNED NULL DEFAULT NULL,
  `method_competence` INT( 1 ) UNSIGNED NULL DEFAULT NULL,
  `social_competence` INT( 1 ) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `frequencyID` (`frequencyID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `prerequisite` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`,`prerequisite`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_mappings` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `programID` INT(11) unsigned DEFAULT NULL,
  `parentID` INT(11) unsigned DEFAULT NULL,
  `poolID` INT(11) unsigned DEFAULT NULL,
  `subjectID` INT(11) unsigned DEFAULT NULL,
  `lft` INT(11) UNSIGNED DEFAULT NULL,
  `rgt` INT(11) UNSIGNED DEFAULT NULL,
  `level` INT(11) UNSIGNED DEFAULT NULL,
  `ordering` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `surname` VARCHAR ( 255 ) DEFAULT NULL,
  `forename` varchar ( 255 ) NOT NULL DEFAULT '',
  `username` VARCHAR ( 150 ) NOT NULL DEFAULT '',
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `title` varchar ( 45 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` ),
  KEY `username` ( `username` ),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `teacherID` INT(11) UNSIGNED NOT NULL,
  `teacherResp` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`subjectID`, `teacherID`, `teacherResp`),
  UNIQUE  KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `type` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `subtype` VARCHAR ( 100 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `name` VARCHAR ( 10 ) NOT NULL,
  `longname` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `typeID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `typeID` ( `typeID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `ip` VARCHAR ( 15 ) NOT NULL,
  `useDefaults` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `display` INT ( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of seconds before the schedule refreshes',
  `content_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of time in seconds before the content refreshes',
  `interval` INT ( 1 ) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
  `content` VARCHAR ( 256 ) DEFAULT '' COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY ( `id` ),
  UNIQUE KEY ( `ip` ),
  KEY `roomID` ( `roomID` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR ( 50 ) NOT NULL,
  `description` TEXT NOT NULL DEFAULT '',
  `global` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `reserves` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `contentCatID` INT ( 11 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `contentCatID` ( `contentCatID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
  `id` INT ( 11 ) UNSIGNED NOT NULL,
  `categoryID` INT ( 11 ) UNSIGNED NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date DEFAULT NULL,
  `starttime`  time NOT NULL DEFAULT '00:00:00',
  `endtime`  time NOT NULL DEFAULT '00:00:00',
  `start` INT ( 15 ) DEFAULT '0',
  `end` INT ( 15 ) DEFAULT '0',
  `recurrence_type` INT ( 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `recurrence_counter` INT ( 2 ) UNSIGNED  NOT NULL DEFAULT '0',
  `recurrence_enddate` date NOT NULL DEFAULT '0000-00-00',
  `recurrence_interval` INT ( 2 ) UNSIGNED  NOT NULL DEFAULT '0',
  `recurrence_days` VARCHAR ( 7 ) NOT NULL DEFAULT '0000000',
  `recurrence_date` INT ( 2 ) UNSIGNED NOT NULL DEFAULT '0',
  KEY `id` ( `id` ),
  KEY `categoryID` ( `categoryID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_exclude_dates` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY ( `id` ),
  KEY `eventID` ( `eventID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_teachers` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`eventID`, `teacherID`),
  KEY `eventID` ( `eventID` ),
  KEY `teacherID` ( `teacherID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_rooms` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `roomID` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`eventID`, `roomID`),
  KEY `eventID` ( `eventID` ),
  KEY `roomID` ( `roomID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_groups` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `groupID` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`eventID`, `groupID`),
  KEY `eventID` ( `eventID` ),
  KEY `groupID` ( `groupID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__thm_organizer_users`
ADD CONSTRAINT `users_userid_fk` FOREIGN KEY (`userID`)
REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_virtual_schedules`
ADD CONSTRAINT `virtual_schedules_semestername_fk` FOREIGN KEY (`semestername`)
REFERENCES `#__thm_organizer_schedules` (`semestername`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_virtual_schedules_elements`
ADD CONSTRAINT `virtual_schedules_elements_vid_fk` FOREIGN KEY (`vid`)
REFERENCES `#__thm_organizer_virtual_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_fields`
ADD CONSTRAINT `fields_colorid_fk` FOREIGN KEY (`colorID`)
REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_degreeid_fk` FOREIGN KEY (`degreeID`)
REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
ADD CONSTRAINT `pools_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_frequencyid_fk` FOREIGN KEY (`frequencyID`)
REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_parentid_fk` FOREIGN KEY (`parentID`)
REFERENCES `#__thm_organizer_mappings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_programid_fk` FOREIGN KEY (`programID`)
REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_teachers`
ADD CONSTRAINT `teachers_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
ADD CONSTRAINT `rooms_typeid_fk` FOREIGN KEY (`typeID`)
REFERENCES `#__thm_organizer_room_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_monitors`
ADD CONSTRAINT `monitors_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_categories`
ADD CONSTRAINT `categories_categoryid_fk` FOREIGN KEY (`contentCatID`)
REFERENCES `#__categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_events`
ADD CONSTRAINT `events_contentid_fk` FOREIGN KEY (`id`)
REFERENCES `#__content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_events`
ADD CONSTRAINT `events_categoryid_fk` FOREIGN KEY (`categoryID`)
REFERENCES `#__thm_organizer_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_exclude_dates`
ADD CONSTRAINT `event_exclude_dates_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_teachers`
ADD CONSTRAINT `event_teachers_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_teachers`
ADD CONSTRAINT `event_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_rooms`
ADD CONSTRAINT `event_rooms_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_rooms`
ADD CONSTRAINT `event_rooms_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_groups`
ADD CONSTRAINT `event_groups_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_groups`
ADD CONSTRAINT `event_groups_groupid_fk` FOREIGN KEY (`groupID`)
REFERENCES `#__usergroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
