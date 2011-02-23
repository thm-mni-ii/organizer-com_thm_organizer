SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_application_settings` (
  `id` int(11) NOT NULL,
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
  `globaldisplay` tinyint(1) NOT NULL DEFAULT '0',
  `reservesobjects` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_classes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `manager` int(11) DEFAULT NULL,
  `semester` varchar(50) NOT NULL,
  `major` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `dptID` (`dptID`),
  KEY `manager` (`manager`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `manager` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `institution` varchar(50) NOT NULL DEFAULT '',
  `campus` varchar(50) NOT NULL DEFAULT '',
  `department` varchar(50) NOT NULL DEFAULT '',
  `curriculum` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `manager` (`manager`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(100) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `image` varchar(100) NOT NULL DEFAULT '',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `categoryid` int(11) unsigned NOT NULL DEFAULT '0',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date DEFAULT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `recurrence_number` int(2) NOT NULL DEFAULT '0',
  `recurrence_type` int(2) NOT NULL DEFAULT '0',
  `recurrence_counter` date NOT NULL DEFAULT '0000-00-00',
  `register` tinyint(1) NOT NULL DEFAULT '0',
  `unregister` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_resources` (
  `eventid` int(11) unsigned NOT NULL,
  `resourceid` int(11) NOT NULL,
  `resource_table` varchar(40) NOT NULL,
  KEY `eventid` (`eventid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jos_thm_organizer_lessons` (
  `id` int(11) NOT NULL,
  `plantypeID` int(1) NOT NULL,
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `semesterID` int(11) unsigned NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `moduleID` varchar(10) NOT NULL DEFAULT '',
  `type` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`,`semesterID`),
  KEY `semesterID` (`semesterID`),
  KEY `plantypeID` (`plantypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plantype` (
  `id` int(1) NOT NULL,
  `plantype` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons_times` (
  `lessonID` int(11) NOT NULL,
  `roomID` int(11) NOT NULL,
  `periodID` int(11) NOT NULL,
  KEY `lessonID` (`lessonID`),
  KEY `roomID` (`roomID`),
  KEY `periodID` (`periodID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_classes` (
  `lessonID` varchar(10) NOT NULL,
  `classID` varchar(10) NOT NULL,
  KEY `lessonID` (`lessonID`),
  KEY `classID` (`classID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `lessonID` varchar(10) NOT NULL,
  `teacherID` varchar(10) NOT NULL,
  KEY `lessonID` (`lessonID`),
  KEY `teacherID` (`teacherID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `monitorID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `roomID` varchar(4) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `semesterID` varchar(11) NOT NULL DEFAULT '',
  PRIMARY KEY (`monitorID`),
  KEY `roomID` (`roomID`),
  KEY `semesterID` (`semesterID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

INSERT INTO `#__thm_organizer_monitors` (`monitorID`, `roomID`, `ip`, `semesterID`) VALUES
(1, 'I007', '10.48.0.87', ''),
(2, 'I008', '10.48.0.47', ''),
(3, 'I009', '10.48.0.48', ''),
(4, 'I107', '10.48.0.49', ''),
(5, 'I108', '10.48.0.50', ''),
(6, 'I109', '10.48.0.51', ''),
(7, 'I136', '10.48.0.86', ''),
(8, 'I207', '10.48.0.55', ''),
(9, 'I208', '10.48.0.53', ''),
(10, 'I209', '10.48.0.54', ''),
(11, 'I210', '10.48.0.88', '');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_periods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `semesterID` int(11) NOT NULL,
  `day` int(1) unsigned NOT NULL,
  `period` int(1) unsigned NOT NULL,
  `starttime` time NOT NULL,
  `endtime` time NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`,`semesterID`),
  KEY `semesterID` (`semesterID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(10) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL,
  `manager` int(11) DEFAULT NULL,
  `capacity` int(4) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT '',
  `dptID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dptID` (`dptID`),
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
  `gpuntisID` varchar(10) NOT NULL DEFAULT '',
  `manager` int(11) DEFAULT NULL,
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
  `manager` varchar(50) NOT NULL DEFAULT '',
  `dptID` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `dptID` (`dptID`),
  KEY `manager` (`manager`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` varchar(100) NOT NULL,
  `data` text NOT NULL,
  `created` int(11) NOT NULL,
  `sid` int(11) unsigned NOT NULL,
  `checked_out` datetime DEFAULT NULL,
  PRIMARY KEY (`username`,`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules` (
  `vid` varchar(20) NOT NULL,
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
  `vid` varchar(20) NOT NULL,
  `eid` varchar(20) NOT NULL,
  `sid` varchar(11) NOT NULL DEFAULT '',
  KEY `vid` (`vid`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

