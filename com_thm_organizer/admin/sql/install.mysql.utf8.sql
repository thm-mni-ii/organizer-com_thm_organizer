SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL default '',
  `globaldisplay` tinyint(1) NOT NULL DEFAULT '0',
  `reservesobjects` tinyint(1) NOT NULL DEFAULT '0',
  `contentCatID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contentCatID` ( `contentCatID` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_classes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `teacherID` int(11) NOT NULL DEFAULT '0',
  `semester` varchar(50) NOT NULL,
  `major` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `teacherID` (`teacherID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `institution` varchar(50) NOT NULL DEFAULT '',
  `campus` varchar(50) NOT NULL DEFAULT '',
  `department` varchar(50) NOT NULL DEFAULT '',
  `subdepartment` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__thm_organizer_descriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `category` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) unsigned NOT NULL,
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `start` int(15) DEFAULT '0',
  `end` int(15) DEFAULT '0',
  `recurrence_number` int(2) unsigned  NOT NULL DEFAULT '0',
  `recurrence_type` int(2) unsigned NOT NULL DEFAULT '0',
  `recurrence_counter` date NOT NULL DEFAULT '0000-00-00',
  `image` varchar(100) NOT NULL DEFAULT '',
  `register` tinyint(1) NOT NULL DEFAULT '0',
  `unregister` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `categoryID` (`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_teachers` (
  `eventID` int(11) unsigned NOT NULL,
  `teacherID` int(11) unsigned NOT NULL,
  KEY `eventID` (`eventID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_rooms` (
  `eventID` int(11) unsigned NOT NULL,
  `roomID` int(11) unsigned NOT NULL,
  KEY `eventID` (`eventID`),
  KEY `roomID` (`roomID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_groups` (
  `eventID` int(11) unsigned NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  KEY `eventID` (`eventID`),
  KEY `groupID` (`groupID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `moduleID` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE `gpuntisID` (`gpuntisID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `subjectID` int(11) unsigned NOT NULL,
  `periods` int(3) NOT NULL DEFAULT '0',
  `semesterID` int(11) unsigned NOT NULL,
  `plantypeID` int(1) NOT NULL,
  `type` varchar(32) NOT NULL DEFAULT '',
  `comment` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `semesterID` (`semesterID`),
  KEY `plantypeID` (`plantypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plantypes` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `plantype` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__thm_organizer_plantypes` (`id`, `plantype`) VALUES
(1, 'COM_THM_ORGANIZER_SCHEDULE'),
(2, 'COM_THM_ORGANIZER_CURRICULUM');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_times` (
  `lessonID` int(11) NOT NULL,
  `roomID` int(11) NOT NULL,
  `periodID` int(11) NOT NULL,
  UNIQUE `lt` (`lessonID`, `roomID`, `periodID`),
  KEY `lessonID` (`lessonID`),
  KEY `roomID` (`roomID`),
  KEY `periodID` (`periodID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_classes` (
  `lessonID` varchar(10) NOT NULL,
  `classID` varchar(10) NOT NULL,
  UNIQUE `lc` (`lessonID`, `classID`),
  KEY `lessonID` (`lessonID`),
  KEY `classID` (`classID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `lessonID` varchar(10) NOT NULL,
  `teacherID` varchar(10) NOT NULL,
  UNIQUE `lt` (`lessonID`, `teacherID`),
  KEY `lessonID` (`lessonID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `monitorID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `roomID` varchar(4) NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`monitorID`),
  KEY `roomID` (`roomID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

INSERT IGNORE INTO `#__thm_organizer_monitors` (`monitorID`, `roomID`, `ip`) VALUES
(1, 'I007', '10.48.0.87'),
(2, 'I008', '10.48.0.47'),
(3, 'I009', '10.48.0.48'),
(4, 'I107', '10.48.0.49'),
(5, 'I108', '10.48.0.50'),
(6, 'I109', '10.48.0.51'),
(7, 'I136', '10.48.0.86'),
(8, 'I207', '10.48.0.55'),
(9, 'I208', '10.48.0.53'),
(10, 'I209', '10.48.0.54'),
(11, 'I210', '10.48.0.88');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_periods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `day` int(1) unsigned NOT NULL,
  `period` int(1) unsigned NOT NULL,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL,
  `manager` int(11) unsigned DEFAULT NULL,
  `capacity` int(4) unsigned DEFAULT NULL,
  `descriptionID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `descriptionID` (`descriptionID`),
  KEY `manager` (`manager`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `file` mediumblob NOT NULL,
  `includedate` date NOT NULL,
  `description` text NOT NULL,
  `active` date DEFAULT NULL,
  `creationdate` date DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `sid` int(11) unsigned NOT NULL,
  `plantypeID` int(1) NOT NULL,
  PRIMARY KEY (`id`,`sid`),
  KEY `sid` (`sid`),
  KEY `plantypeID` (`plantypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_deltas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `semesterID` int(11) unsigned NOT NULL,
  `plantypeID` int(11) unsigned NOT NULL,
  `delta` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `semesterID` (`semesterID`),
  KEY `plantypeID` (`plantypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `organization` varchar(50) NOT NULL DEFAULT '',
  `semesterDesc` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization` (`organization`,`semesterDesc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `username` varchar(50) NOT NULL DEFAULT '',
  `departmentID` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `departmentID` (`departmentID`),
  KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `sid` int(11) unsigned NOT NULL,
  `checked_out` datetime DEFAULT NULL,
  PRIMARY KEY (`username`,`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules` (
  `vid` varchar(50) NOT NULL,
  `vname` varchar(50) NOT NULL,
  `vtype` varchar(50) NOT NULL,
  `vresponsible` varchar(50) NOT NULL,
  `unittype` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL DEFAULT '',
  `sid` int(11) unsigned NOT NULL,
  UNIQUE KEY `vid` (`vid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
  `vid` varchar(50) NOT NULL,
  `eid` varchar(20) NOT NULL,
  `sid` varchar(11) NOT NULL DEFAULT '',
  KEY `vid` (`vid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_display_behaviours` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `behaviour` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__thm_organizer_display_behaviours` (`id`, `behaviour`) VALUES
(1, 'COM_THM_ORGANIZER_MON_SCHEDULE'),
(2, 'COM_THM_ORGANIZER_MON_MIXED'),
(3, 'COM_THM_ORGANIZER_MON_CONTENT');


--
-- Curriculum tables
--


--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_assets
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_assets (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) DEFAULT NULL,
  beschreibung varchar(255) DEFAULT NULL,
  min_creditpoints tinyint(4) DEFAULT NULL,
  max_creditpoints tinyint(4) DEFAULT NULL,
  lsf_course_id int(11) NOT NULL,
  lsf_course_code varchar(45) DEFAULT NULL,
  his_course_code int(11) DEFAULT NULL,
  title_de varchar(255) DEFAULT NULL,
  title_en varchar(45) DEFAULT NULL,
  short_title_de varchar(45) DEFAULT NULL,
  short_title_en varchar(45) NOT NULL,
  abbreviation varchar(45) DEFAULT NULL,
  asset_type_id int(11) DEFAULT NULL,
  prerequisite varchar(255) DEFAULT NULL,
  description varchar(255) DEFAULT NULL,
  note text NOT NULL,
  pool_type tinyint(4) NOT NULL,
  color_id tinyint(4) NOT NULL,
  ecollaboration_link varchar(255) NOT NULL,
  menu_link int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY asset_type_id (asset_type_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=914 ;





--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_assets_semesters
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_assets_semesters (
  id int(11) NOT NULL AUTO_INCREMENT,
  assets_tree_id int(11) NOT NULL,
  semesters_majors_id int(11) NOT NULL,
  PRIMARY KEY (assets_tree_id,semesters_majors_id),
  UNIQUE KEY (id),
  KEY semesters_majors_id (semesters_majors_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=817 ;





--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_assets_tree
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_assets_tree (
  id int(11) NOT NULL AUTO_INCREMENT,
  color_id int(11) NOT NULL,
  asset int(11) NOT NULL,
  parent_id int(11) DEFAULT NULL,
  proportion_crp varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  depth int(11) DEFAULT NULL,
  lineage varchar(255) NOT NULL DEFAULT 'none',
  published tinyint(4) NOT NULL DEFAULT 1,
  note text NOT NULL,
  ordering int(11) NOT NULL,
  ecollaboration_link varchar(255) NOT NULL,
  menu_link int(11) NOT NULL,
  color_id_flag tinyint(4) NOT NULL DEFAULT 1,
  menu_link_flag tinyint(4) NOT NULL DEFAULT 1,
  ecollaboration_link_flag int(1) NOT NULL DEFAULT 1,
  note_flag tinyint(4) NOT NULL,
  
  PRIMARY KEY (id),
  KEY color_id (color_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=171 ;

--
-- Daten f�r Tabelle #__thm_organizer_assets_tree
--



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_asset_types
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_asset_types (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(45) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Daten f�r Tabelle #__thm_organizer_asset_types
--

INSERT INTO #__thm_organizer_asset_types (id, name) VALUES
(1, 'Course'),
(2, 'Coursepool'),
(3, 'Dummy');



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_colors
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_colors (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  color varchar(6) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=70 ;

--
-- Daten f�r Tabelle #__thm_organizer_colors
--

INSERT INTO #__thm_organizer_colors (id, name, color) VALUES
(39, 'THM Hintergrundgruen', 'cce3a7'),
(40, 'THM Hintergrundgrau', 'b7bec2'),
(41, 'THM Hintergrundrot', 'e199ad'),
(42, 'THM Hintergrundgelb', 'fde499'),
(43, 'THM Hintergrundcyan', '99e1f1'),
(44, 'THM Hintergrundblau', '99b4d0'),
(45, 'THM hellgruen', '9bd641'),
(46, 'THM hellgrau', '6b7e88'),
(47, 'THM hellrot', 'd32154'),
(48, 'THM hellgelb', 'ffca30'),
(49, 'THM hellcyan', '1dd1f9'),
(50, 'THM hellblau', '2568ae'),
(51, 'THM gruen', '80ba24'),
(53, 'THM rot', 'b30033'),
(54, 'THM gelb', 'fbbb00'),
(55, 'THM cyanm', '00b5dd'),
(57, 'THM mittelgruen', '71a126'),
(58, 'THM mittelgrau', '44535b'),
(59, 'THM mittelrot', '990831'),
(60, 'THM mittelgelb', 'd7a30b'),
(61, 'THM mittelcyan', '099cbd'),
(62, 'THM mittelblau', '063d76'),
(63, 'THM dunkelgruen', '638929'),
(64, 'THM dunkelgrau', '3d494f'),
(65, 'THM dunkelrot', '810e2f'),
(66, 'THM dunkelgelb', 'vb58b14');



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_degrees
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_degrees (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Daten f�r Tabelle #__thm_organizer_degrees
--

INSERT INTO #__thm_organizer_degrees (id, name) VALUES
(13, 'Bachelor of Engineering'),
(15, 'Bachelor of Science'),
(16, 'Master of Science'),
(17, 'Bachelor of Arts'),
(18, 'Master of Arts'),
(20, 'Master of Business Administration and Engineering');

--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_lecturers
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_lecturers (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid varchar(25) NOT NULL,
  surname varchar(255) NOT NULL,
  forename varchar(255) NOT NULL,
  academic_title varchar(45) DEFAULT NULL,
  note text NOT NULL,
  PRIMARY KEY (userid),
  UNIQUE KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5449 ;




--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_lecturers_assets
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_lecturers_assets (
  id int(11) NOT NULL AUTO_INCREMENT,
  modul_id int(11) NOT NULL,
  lecturer_id int(25) NOT NULL,
  lecturer_type int(11) NOT NULL,
  PRIMARY KEY (modul_id,lecturer_id,lecturer_type),
  UNIQUE KEY (id),
  KEY modul_id (modul_id),
  KEY lecturer_type (lecturer_type),
  KEY lecturer_id (lecturer_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8928 ;



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_lecturers_types
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_lecturers_types (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten f�r Tabelle #__thm_organizer_lecturers_types
--

INSERT INTO #__thm_organizer_lecturers_types (id, name) VALUES
(1, 'Modulverantwortlicher'),
(2, 'Dozent');



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_majors
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_majors (
  id int(11) NOT NULL AUTO_INCREMENT,
  degree_id int(11) NOT NULL,
  subject varchar(255) NOT NULL,
  po year(4) NOT NULL,
  note text,
  lsf_object varchar(255),
  lsf_study_path varchar(255),
  lsf_degree varchar(255),
  organizer_major varchar(255),
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;




--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_semesters
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_curriculum_semesters (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(45) DEFAULT NULL,
  color_id int(11),
  short_title_de varchar(45),
  short_title_en varchar(45), 
  note text,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Daten f�r Tabelle #__thm_organizer_curriculum_semesters
--

INSERT INTO #__thm_organizer_curriculum_semesters (id, name) VALUES
(1, '1. Semester'),
(2, '2. Semester'),
(3, '3. Semester'),
(4, '4. Semester'),
(5, '5. Semester'),
(6, '6. Semester'),
(7, '4./5. Semester');



--
-- Tabellenstruktur f�r Tabelle #__thm_organizer_semesters_majors
--

CREATE TABLE IF NOT EXISTS #__thm_organizer_semesters_majors (
  id int(11) NOT NULL AUTO_INCREMENT,
  major_id int(11) NOT NULL,
  semester_id int(11) NOT NULL,
  PRIMARY KEY (major_id,semester_id),
  UNIQUE KEY (id),
  KEY semester_id (semester_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=303 ;



--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle #__thm_organizer_assets
--
ALTER TABLE #__thm_organizer_assets
  ADD FOREIGN KEY (asset_type_id) REFERENCES #__thm_organizer_asset_types (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle #__thm_organizer_assets_semesters
--
ALTER TABLE #__thm_organizer_assets_semesters
  ADD FOREIGN KEY (assets_tree_id) REFERENCES #__thm_organizer_assets_tree (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (semesters_majors_id) REFERENCES #__thm_organizer_semesters_majors (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle #__thm_organizer_lecturers_assets
--
ALTER TABLE #__thm_organizer_lecturers_assets
  ADD FOREIGN KEY (modul_id) REFERENCES #__thm_organizer_assets (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (lecturer_type) REFERENCES #__thm_organizer_lecturers_types (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (lecturer_id) REFERENCES #__thm_organizer_lecturers (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle #__thm_organizer_semesters_majors
--
ALTER TABLE #__thm_organizer_semesters_majors
  ADD FOREIGN KEY (major_id) REFERENCES #__thm_organizer_majors (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD FOREIGN KEY (semester_id) REFERENCES #__thm_organizer_curriculum_semesters (id) ON DELETE CASCADE ON UPDATE CASCADE;
