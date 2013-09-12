SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `departmentname` VARCHAR ( 50 ) NOT NULL,
  `semestername` VARCHAR ( 50 ) NOT NULL,
  `creationdate` date DEFAULT NULL,
  `description` TEXT NOT NULL,
  `schedule` mediumblob NOT NULL,
  `active` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
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
  FOREIGN KEY ( `semestername` ) REFERENCES #__thm_organizer_schedules( `semestername` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vid` INT ( 11 ) UNSIGNED NOT NULL,
  `eid` VARCHAR ( 20 ) NOT NULL,
  PRIMARY KEY `id` ( `id` ),
  FOREIGN KEY ( `vid` ) REFERENCES `#__thm_organizer_virtual_schedules` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
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
  UNIQUE KEY `gpuntisID` ( `gpuntisID` ),
  FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `abbreviation` VARCHAR ( 45 ) NOT NULL DEFAULT '',
  `lsfDegree` varchar ( 10 ) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_programs` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `version` year (4) DEFAULT NULL,
  `lsfFieldID` varchar(255) DEFAULT NULL,
  `degreeID` INT(11) unsigned DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  `description_de` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lsfID` INT(11) UNSIGNED DEFAULT NULL,
  `hisID` INT(11) UNSIGNED DEFAULT NULL,
  `externalID` varchar(45) DEFAULT NULL,
  `description_de` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `abbreviation_de` varchar(45) DEFAULT NULL,
  `abbreviation_en` varchar(45) DEFAULT NULL,
  `short_name_de` varchar(45) DEFAULT NULL,
  `short_name_en` varchar(45) DEFAULT NULL,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `minCrP` INT(2) UNSIGNED DEFAULT NULL,
  `maxCrP` INT(2) UNSIGNED DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  `distance` INT(2) UNSIGNED DEFAULT NULL,
  `display_type` boolean DEFAULT TRUE,
  `enable_desc` boolean DEFAULT TRUE,
  PRIMARY KEY (id),
  KEY `lsfID` ( `lsfID` ),
  KEY `externalID` ( `externalID` ),
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id` INT (1) UNSIGNED NOT NULL,
  `frequency_de` varchar (45) DEFAULT NULL,
  `frequency_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_proof` (
  `id` varchar (2) NOT NULL,
  `proof_de` varchar (45) DEFAULT NULL,
  `proof_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pforms` (
  `id` varchar (2) NOT NULL,
  `pform_de` varchar (45) DEFAULT NULL,
  `pform_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
  `id` varchar (2) NOT NULL,
  `method_de` varchar (45) DEFAULT NULL,
  `method_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lsfID` INT(11) UNSIGNED DEFAULT NULL,
  `hisID` INT(11) UNSIGNED DEFAULT NULL,
  `externalID` varchar(45) DEFAULT NULL,
  `abbreviation_de` varchar(45) DEFAULT NULL,
  `abbreviation_en` varchar(45) DEFAULT NULL,
  `short_name_de` varchar(45) DEFAULT NULL,
  `short_name_en` varchar(45) DEFAULT NULL,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description_de` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `objective_de` text DEFAULT NULL,
  `objective_en` text DEFAULT NULL,
  `content_de` text DEFAULT NULL,
  `content_en` text DEFAULT NULL,
  `preliminary_work_de` varchar(255) DEFAULT NULL,
  `preliminary_work_en` varchar(255) DEFAULT NULL,
  `instructionLanguage` varchar (2) DEFAULT NULL,
  `literature` text DEFAULT NULL,
  `creditpoints` INT(4) UNSIGNED DEFAULT NULL,
  `expenditure` INT(4) UNSIGNED DEFAULT NULL,
  `present` INT(4) UNSIGNED DEFAULT NULL,
  `independent` INT(4) UNSIGNED DEFAULT NULL,
  `proofID` varchar(2) DEFAULT NULL,
  `pformID` varchar(2) DEFAULT NULL,
  `frequencyID` INT(1) UNSIGNED DEFAULT NULL,
  `methodID` varchar(2) DEFAULT NULL,
  `fieldID` INT(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`proofID`) REFERENCES `#__thm_organizer_proof` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`pformID`) REFERENCES `#__thm_organizer_pforms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `prerequisite` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY ( `subjectID` ) REFERENCES #__thm_organizer_subjects( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `prerequisite` ) REFERENCES #__thm_organizer_subjects( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
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
  FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_mappings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 10 ) DEFAULT NULL,
  `surname` VARCHAR ( 255 ) DEFAULT NULL,
  `forename` VARCHAR ( 255 ) DEFAULT NULL,
  `username` VARCHAR ( 150 ) NOT NULL DEFAULT '',
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `title` varchar ( 45 ) DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `username` ( `username` ),
  FOREIGN KEY ( `fieldID` ) REFERENCES #__thm_organizer_fields( `id` ) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_responsibilities` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 50 ) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `teacherID` INT(11) UNSIGNED NOT NULL,
  `teacherResp` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`subjectID`, `teacherID`, `teacherResp`),
  UNIQUE  KEY (`id`),
  FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`teacherResp`) REFERENCES `#__thm_organizer_teacher_responsibilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `type` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `subtype` VARCHAR ( 100 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` ),
  UNIQUE KEY `gpuntisID` ( `gpuntisID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 20 ) NOT NULL,
  `name` VARCHAR ( 10 ) NOT NULL DEFAULT '',
  `longname` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `typeID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `typeID` ) REFERENCES #__thm_organizer_room_types( `id` ) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID` INT ( 11 ) UNSIGNED NOT NULL,
  `ip` VARCHAR ( 15 ) NOT NULL,
  `display` INT ( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of seconds before the schedule refreshes',
  `content_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of time in seconds before the content refreshes',
  `interval` INT ( 1 ) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
  `content` VARCHAR ( 256 ) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `roomID` ) REFERENCES `#__thm_organizer_rooms` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR ( 50 ) NOT NULL,
  `description` TEXT NOT NULL default '',
  `global` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `reserves` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `contentCatID` INT ( 11 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  KEY `contentCatID` ( `contentCatID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
  `id` INT ( 11 ) UNSIGNED NOT NULL,
  `categoryID` INT ( 11 ) UNSIGNED NOT NULL,
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `start` INT ( 15 ) DEFAULT '0',
  `end` INT ( 15 ) DEFAULT '0',
  `recurrence_type` INT ( 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `recurrence_counter` INT ( 2 ) UNSIGNED  NOT NULL DEFAULT '0',
  `recurrence_enddate` date NOT NULL DEFAULT '0000-00-00',
  `recurrence_INTerval` INT ( 2 ) UNSIGNED  NOT NULL DEFAULT '0',
  `recurrence_days` VARCHAR ( 7 ) NOT NULL DEFAULT '0000000',
  `recurrence_date` INT ( 2 ) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `categoryID` ) REFERENCES `#__thm_organizer_categories` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_exclude_dates` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `eventID` ) REFERENCES `#__thm_organizer_events` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_teachers` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
  FOREIGN KEY ( `eventID` ) REFERENCES `#__thm_organizer_events` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `teacherID` ) REFERENCES `#__thm_organizer_teachers` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_rooms` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `roomID` INT ( 11 ) UNSIGNED NOT NULL,
  FOREIGN KEY ( `eventID` ) REFERENCES `#__thm_organizer_events` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `roomID` ) REFERENCES `#__thm_organizer_rooms` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_groups` (
  `eventID` INT ( 11 ) UNSIGNED NOT NULL,
  `groupID` INT ( 11 ) UNSIGNED NOT NULL,
  FOREIGN KEY ( `eventID` ) REFERENCES `#__thm_organizer_events` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY `groupID` ( `groupID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;