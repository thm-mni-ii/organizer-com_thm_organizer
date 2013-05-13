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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_fields` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  `field` VARCHAR ( 50 ) NOT NULL DEFAULT '',
  PRIMARY KEY ( `id` ),
  UNIQUE KEY `gpuntisID` ( `gpuntisID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR ( 10 ) NOT NULL DEFAULT '',
  `surname` VARCHAR ( 255 ) NOT NULL DEFAULT '',
  `forename` VARCHAR ( 255 ) NOT NULL DEFAULT '',
  `username` VARCHAR ( 150 ) NOT NULL DEFAULT '',
  `fieldID` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `title` varchar ( 45 ) DEFAULT NULL,
  PRIMARY KEY ( `id` ),
  KEY `username` ( `username` ),
  FOREIGN KEY ( `fieldID` ) REFERENCES #__thm_organizer_teacher_fields( `id` ) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID` INT ( 11 ) UNSIGNED NOT NULL,
  `ip` VARCHAR ( 15 ) NOT NULL,
  `display` INT ( 1 ) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of seconds before the schedule refreshes',
  `content_refresh` INT ( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of time in seconds before the content refreshes',
  `INTerval` INT ( 1 ) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time INTerval in minutes between context switches',
  `content` VARCHAR ( 256 ) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `roomID` ) REFERENCES `#__thm_organizer_rooms` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_soap_queries` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `lsf_object` VARCHAR ( 255 ) NOT NULL,
  `lsf_study_path` VARCHAR ( 255 ) NOT NULL,
  `lsf_degree` VARCHAR ( 255 ) NOT NULL,
  `lsf_pversion` VARCHAR ( 255 ) NOT NULL,
  `description` VARCHAR ( 255 ) DEFAULT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_responsibilities` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 50 ) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `abbreviation` VARCHAR ( 45 ) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) NOT NULL,
  `color` VARCHAR ( 6 ) NOT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_asset_types` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 45 ) DEFAULT NULL,
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 255 ) DEFAULT NULL,
  `beschreibung` VARCHAR ( 255 ) DEFAULT NULL,
  `min_creditpoints` TINYINT ( 4 ) DEFAULT NULL,
  `max_creditpoints` TINYINT ( 4 ) DEFAULT NULL,
  `lsf_course_id` INT ( 11 ) NOT NULL,
  `lsf_course_code` VARCHAR ( 45 ) DEFAULT NULL,
  `his_course_code` INT ( 11 ) DEFAULT NULL,
  `title_de` VARCHAR ( 255 ) DEFAULT NULL,
  `title_en` VARCHAR ( 45 ) DEFAULT NULL,
  `short_title_de` VARCHAR ( 45 ) DEFAULT NULL,
  `short_title_en` VARCHAR ( 45 ) NOT NULL,
  `abbreviation` VARCHAR ( 45 ) DEFAULT NULL,
  `asset_type_id` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `prerequisite` VARCHAR ( 255 ) DEFAULT NULL,
  `description` VARCHAR ( 255 ) DEFAULT NULL,
  `note` TEXT NOT NULL,
  `pool_type` TINYINT ( 4 ) NOT NULL,
  `color_id` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `ecollaboration_link` VARCHAR ( 255 ) NOT NULL,
  `menu_link` INT ( 11 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `asset_type_id` ) REFERENCES `#__thm_organizer_asset_types` ( `id` ) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY ( `color_id` ) REFERENCES `#__thm_organizer_colors` ( `id` ) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_majors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `degree_id` INT ( 11 ) DEFAULT NULL,
  `subject` VARCHAR ( 255 ) NOT NULL,
  `po` year( 4 ) NOT NULL,
  `note` TEXT,
  `lsf_object` VARCHAR ( 255 ),
  `lsf_study_path` VARCHAR ( 255 ),
  `lsf_degree` VARCHAR ( 255 ),
  `organizer_major` VARCHAR ( 255 ),
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `degree_id` ) REFERENCES `#__thm_organizer_degrees` ( `id` ) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR ( 45 ) DEFAULT NULL,
  `color_id` INT ( 11 ) UNSIGNED DEFAULT NULL;
  `short_title_de` VARCHAR ( 45 ),
  `short_title_en` VARCHAR ( 45 ), 
  `note` TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY ( `color_id` ) REFERENCES `#__thm_organizer_colors` ( `id` ) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters_majors` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `major_id` INT ( 11 ) UNSIGNED NOT NULL,
  `semester_id` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY ( `major_id`, `semester_id` ),
  FOREIGN KEY ( `major_id` ) REFERENCES `#__thm_organizer_majors` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `semester_id` ) REFERENCES `#__thm_organizer_semesters` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_assets` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `moduleID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
  `teacherResp` INT ( 11 ) UNSIGNED NOT NULL;
  PRIMARY KEY ( `moduleID`, `teacherID`, `teacherResp` ),
  FOREIGN KEY ( `moduleID` ) REFERENCES `#__thm_organizer_assets` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `teacherID` ) REFERENCES `#__thm_organizer_teachers` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `teacherResp` ) REFERENCES `#__thm_organizer_teacher_responsibilities` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_tree` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `color_id` INT ( 11 ) UNSIGNED DEFAULT NULL,
  `asset` INT ( 11 ) UNSIGNED NOT NULL,
  `parent_id` INT ( 11 ) UNSIGNED NOT NULL,
  `proportion_crp` VARCHAR ( 45 ) CHARACTER SET utf8 DEFAULT NULL,
  `depth` INT ( 11 ) DEFAULT NULL,
  `lineage` VARCHAR ( 255 ) NOT NULL DEFAULT 'none',
  `published` TINYINT ( 4 ) NOT NULL DEFAULT 1,
  `note` TEXT NOT NULL,
  `ordering` INT ( 11 ) NOT NULL,
  `ecollaboration_link` VARCHAR ( 255 ) NOT NULL,
  `menu_link` INT ( 11 ) NOT NULL,
  `color_id_flag` TINYINT ( 4 ) NOT NULL DEFAULT 1,
  `menu_link_flag` TINYINT ( 4 ) NOT NULL DEFAULT 1,
  `ecollaboration_link_flag` INT (1) NOT NULL DEFAULT 1,
  `note_flag` TINYINT ( 4 ) NOT NULL,
  PRIMARY KEY ( `id` ),
  FOREIGN KEY ( `color_id` ) REFERENCES `#__thm_organizer_colors` ( `id` ) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY ( `asset` ) REFERENCES `#__thm_organizer_assets` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `parent_id` ) REFERENCES `#__thm_organizer_assets_tree` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_semesters` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `assets_tree_id` INT ( 11 ) UNSIGNED NOT NULL,
  `semesters_majors_id` INT ( 11 ) UNSIGNED NOT NULL,
  PRIMARY KEY ( `assets_tree_id`, `semesters_majors_id` ),
  FOREIGN KEY ( `assets_tree_id` ) REFERENCES `#__thm_organizer_assets_tree` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `semesters_majors_id` ) REFERENCES `#__thm_organizer_semesters_majors` ( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY ( `id` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;