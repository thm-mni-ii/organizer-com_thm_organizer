SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `departmentname` varchar(50) NOT NULL,
  `semestername` varchar(50) NOT NULL,
  `creationdate` date DEFAULT NULL,
  `description` text NOT NULL,
  `schedule` mediumblob NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `semestername` (`semestername`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `responsible` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL DEFAULT '',
  `semestername` varchar(50) NOT NULL,
  PRIMARY KEY `id` (`id`),
  FOREIGN KEY (`semestername`) REFERENCES #__thm_organizer_schedules(`semestername`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(11) unsigned NOT NULL,
  `eid` varchar(20) NOT NULL,
  PRIMARY KEY `id` (`id`),
  FOREIGN KEY (`vid`) REFERENCES `#__thm_organizer_virtual_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` varchar(100) NOT NULL,
  `created` int(11) UNSIGNED NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `type` varchar(50) NOT NULL DEFAULT '',
  `subtype` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(20) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `longname` varchar(50) NOT NULL DEFAULT '',
  `typeID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`typeID`) REFERENCES #__thm_organizer_room_types(`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `field` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `surname` varchar(50) NOT NULL DEFAULT '',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `fieldID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  FOREIGN KEY (`fieldID`) REFERENCES #__thm_organizer_teacher_fields(`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL default '',
  `global` tinyint(1) NOT NULL DEFAULT '0',
  `reserves` tinyint(1) NOT NULL DEFAULT '0',
  `contentCatID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contentCatID` (`contentCatID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
  `id` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `start` int(15) DEFAULT '0',
  `end` int(15) DEFAULT '0',
  `recurrence_type` int(2) unsigned NOT NULL DEFAULT '0',
  `recurrence_counter` int(2) unsigned  NOT NULL DEFAULT '0',
  `recurrence_enddate` date NOT NULL DEFAULT '0000-00-00',
  `recurrence_interval` int(2) unsigned  NOT NULL DEFAULT '0',
  `recurrence_days` varchar(7) NOT NULL DEFAULT '0000000',
  `recurrence_date` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_exclude_dates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eventID` int(11) unsigned NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_teachers` (
  `eventID` int(11) unsigned NOT NULL,
  `teacherID` int(11) unsigned NOT NULL,
  FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_rooms` (
  `eventID` int(11) unsigned NOT NULL,
  `roomID` int(11) unsigned NOT NULL,
  FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_groups` (
  `eventID` int(11) unsigned NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  KEY `groupID` (`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `roomID` int(11) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` INT( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of seconds before the schedule refreshes',
  `content_refresh` INT( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of time in seconds before the content refreshes',
  `interval` INT(1) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
  `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_soap_queries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lsf_object` varchar(255) NOT NULL,
  `lsf_study_path` varchar(255) NOT NULL,
  `lsf_degree` varchar(255) NOT NULL,
  `lsf_pversion` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE`#__thm_organizer_degrees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_asset_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` varchar(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jos_thm_organizer_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `beschreibung` varchar(255) DEFAULT NULL,
  `min_creditpoints` tinyint(4) DEFAULT NULL,
  `max_creditpoints` tinyint(4) DEFAULT NULL,
  `lsf_course_id` int(11) NOT NULL,
  `lsf_course_code` varchar(45) DEFAULT NULL,
  `his_course_code` int(11) DEFAULT NULL,
  `title_de` varchar(255) DEFAULT NULL,
  `title_en` varchar(45) DEFAULT NULL,
  `short_title_de` varchar(45) DEFAULT NULL,
  `short_title_en` varchar(45) NOT NULL,
  `abbreviation` varchar(45) DEFAULT NULL,
  `asset_type_id` int(11) unsigned DEFAULT NULL,
  `prerequisite` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `note` text NOT NULL,
  `pool_type` tinyint(4) NOT NULL,
  `color_id` int(11) unsigned DEFAULT NULL,
  `ecollaboration_link` varchar(255) NOT NULL,
  `menu_link` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`asset_type_id`) REFERENCES `jos_thm_organizer_asset_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY (`color_id`) REFERENCES `jos_thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_majors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `po` year(4) NOT NULL,
  `note` text,
  `lsf_object` varchar(255),
  `lsf_study_path` varchar(255),
  `lsf_degree` varchar(255),
  `organizer_major` varchar(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`degree_id`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `color_id` int(11) unsigned DEFAULT NULL,
  `short_title_de` varchar(45),
  `short_title_en` varchar(45), 
  `note` text,
  PRIMARY KEY (id),
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters_majors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `major_id` int(11) unsigned NOT NULL,
  `semester_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`major_id`, `semester_id`),
  FOREIGN KEY (`major_id`) REFERENCES `#__thm_organizer_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`semester_id`) REFERENCES `#__thm_organizer_semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_tree` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color_id` int(11) unsigned DEFAULT NULL,
  `asset` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `proportion_crp` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `depth` int(11) DEFAULT NULL,
  `lineage` varchar(255) NOT NULL DEFAULT 'none',
  `published` tinyint(4) NOT NULL DEFAULT 1,
  `note` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `ecollaboration_link` varchar(255) NOT NULL,
  `menu_link` int(11) NOT NULL,
  `color_id_flag` tinyint(4) NOT NULL DEFAULT 1,
  `menu_link_flag` tinyint(4) NOT NULL DEFAULT 1,
  `ecollaboration_link_flag` int(1) NOT NULL DEFAULT 1,
  `note_flag` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  FOREIGN KEY (`asset`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assets_tree_id` int(11) unsigned NOT NULL,
  `semesters_majors_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`assets_tree_id`, `semesters_majors_id`),
  FOREIGN KEY (`assets_tree_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`semesters_majors_id`) REFERENCES `#__thm_organizer_semesters_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lecturers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` varchar(150) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `forename` varchar(255) NOT NULL,
  `academic_title` varchar(45) DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lecturers_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lecturers_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modul_id` int(11) unsigned NOT NULL,
  `lecturer_id` int(11) unsigned NOT NULL,
  `lecturer_type` int(11) unsigned NOT NULL,
  PRIMARY KEY (`modul_id`, `lecturer_id`, `lecturer_type`),
  FOREIGN KEY (`modul_id`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_id`) REFERENCES `#__thm_organizer_lecturers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_type`) REFERENCES `#__thm_organizer_lecturers_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;