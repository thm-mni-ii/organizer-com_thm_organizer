SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_application_settings` (
  `id` int(11) unsigned NOT NULL,
  `downFolder` varchar(100) NOT NULL,
  `vacationcat` tinyint(4) NOT NULL,
  `eStudyPath` varchar(100) NOT NULL,
  `eStudywsapiPath` varchar(100) NOT NULL,
  `eStudyCreateCoursePath` varchar(100) NOT NULL,
  `eStudySoapSchema` varchar(100) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `manager` varchar(20) DEFAULT '',
  `semester` varchar(50) NOT NULL,
  `major` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `manager` (`manager`)
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
  `semesterID` int(11) unsigned NOT NULL,
  `plantypeID` int(1) NOT NULL,
  `type` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `semesterID` (`semesterID`),
  KEY `plantypeID` (`plantypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plantype` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `plantype` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__thm_organizer_plantype` (`id`, `plantype`) VALUES
(1, 'Stundenplan'),
(2, 'Lehrplan');

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
  `semesterID` int(11) unsigned NOT NULL,
  `day` int(1) unsigned NOT NULL,
  `period` int(1) unsigned NOT NULL,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `semesterID` (`semesterID`)
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `manager` int(11) unsigned DEFAULT NULL,
  `organization` varchar(50) NOT NULL DEFAULT '',
  `semesterDesc` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization` (`organization`,`semesterDesc`),
  KEY `manager` (`manager`)
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

