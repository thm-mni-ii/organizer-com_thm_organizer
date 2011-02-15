CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
`id` int(11) unsigned NOT NULL auto_increment,
`title` varchar(100) NOT NULL default '',
`alias` varchar(100) NOT NULL default '',
`description` mediumtext NOT NULL,
`image` varchar(100) NOT NULL default '',
`created_by` int(11) unsigned NOT NULL default '0',
`created` datetime NOT NULL,
`modified_by` int(11) unsigned NOT NULL default '0',
`modified` datetime NOT NULL,
`categoryid` int(11) unsigned NOT NULL default '0',
`startdate` date NOT NULL default '0000-00-00',
`enddate` date NULL default NULL,
`starttime` time NULL default NULL,
`endtime` time NULL default NULL,
`recurrence_number` int(2) NOT NULL default '0',
`recurrence_type` int(2) NOT NULL default '0',
`recurrence_counter` date NOT NULL default '0000-00-00',
`register` tinyint(1) NOT NULL default '0',
`unregister` tinyint(1) NOT NULL default '0',
PRIMARY KEY  (`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_resources` (
`eventid` int(11) unsigned NOT NULL,
`resourceid` varchar(10) NOT NULL,
FOREIGN KEY (`eventid`) REFERENCES `#__thm_organizer_events`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
`id` varchar(10) NOT NULL,
`name` varchar(50) NOT NULL default '',
PRIMARY KEY (`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
`id` int(11) unsigned NOT NULL auto_increment,
`manager` int(11) NOT NULL,
`organization` varchar(20) NOT NULL,
`semesterDesc` varchar(20) NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE (`organization`, `semesterDesc`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_classes` (
`id` varchar(10) NOT NULL,
`name` varchar(50) NOT NULL default '',
`alias` varchar(50) NOT NULL default '',
`image` varchar(100) NOT NULL default '',
`manager` varchar(50) NOT NULL default '',
`semester` varchar(50) NOT NULL,
`dptID` varchar(10) NOT NULL default '',
PRIMARY KEY (`id`),
FOREIGN KEY (`dptID`) REFERENCES `#__thm_organizer_departments`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
`id` varchar(10) NOT NULL,
`name` varchar(50) NOT NULL default '',
`alias` varchar(50) NOT NULL default '',
`manager` varchar(50) NOT NULL default '',
`dptID` varchar(10) NOT NULL default '',
PRIMARY KEY (`id`),
FOREIGN KEY (`dptID`) REFERENCES `#__thm_organizer_departments`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
`id` varchar(10) NOT NULL,
`name` varchar(50) NOT NULL default '',
`alias` varchar(50) NOT NULL default '',
`image` varchar(100) NOT NULL default '',
`manager` varchar(50) NOT NULL default '',
`capacity` int(4) NOT NULL default '0',
`type` varchar(20) NOT NULL default '',
`dptID` varchar(10) NOT NULL default '',
PRIMARY KEY (`id`),
FOREIGN KEY (`dptID`) REFERENCES `#__thm_organizer_departments`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
`id` int(11) NOT NULL,
`gpuntisid` varchar(10) NOT NULL,
`sid` int(11) unsigned NOT NULL,
`name` varchar(50) NOT NULL default '',
`alias` varchar(50) NOT NULL default '',
`manager` varchar(50) NOT NULL default '',
PRIMARY KEY (`id`),
UNIQUE (`gpuntisid`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons_times` (
`lessonID` int(11) NOT NULL,
`roomID` varchar(10) NOT NULL,
`day` int(1) unsigned NOT NULL,
`period` int(1) unsigned NOT NULL,
FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons`(`id`),
FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
`lessonID` varchar(10) NOT NULL,
`teacherID` varchar(10) NOT NULL,
FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons`(`id`),
FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors`(
`monitorID` int(11) unsigned NOT NULL auto_increment,
`roomID` varchar(4) NOT NULL,
`ip` varchar (15) NOT NULL,
`semesterID` varchar (11) NOT NULL default '',
PRIMARY KEY (`monitorID`),
FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms`(`id`),
FOREIGN KEY (`semesterID`) REFERENCES `#__thm_organizer_semesters`(`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

INSERT IGNORE INTO `#__thm_organizer_monitors` (roomID, ip) VALUES
('RM_I007', '10.48.0.87'),
('RM_I008', '10.48.0.47'),
('RM_I009', '10.48.0.48'),
('RM_I107', '10.48.0.49'),
('RM_I108', '10.48.0.50'),
('RM_I109', '10.48.0.51'),
('RM_I136', '10.48.0.86'),
('RM_I207', '10.48.0.55'),
('RM_I208', '10.48.0.53'),
('RM_I209', '10.48.0.54'),
('RM_I210', '10.48.0.88');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
`id` int(11) unsigned NOT NULL auto_increment,
`globaldisplay` boolean NOT NULL default false,
`reservesobjects` boolean NOT NULL default false,
PRIMARY KEY (`id`),
FOREIGN KEY (`id`) REFERENCES `#__categories` (`id`)
) TYPE=MyISAM;

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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
`id` int(11) NOT NULL auto_increment,
`filename` varchar(100) NOT NULL,
`file` mediumblob NOT NULL,
`includedate` date NOT NULL,
`description` text NOT NULL,
`active` date default NULL,
`creationdate` date default NULL,
`startdate` date default NULL,
`enddate` date default NULL,
`sid` int(11) unsigned NOT NULL,
PRIMARY KEY  (`id`,`sid`),
FOREIGN KEY (`sid`) REFERENCES `#__thm_organizer_semesters` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules`(
`username` VARCHAR( 100 ) NOT NULL,
`data` TEXT NOT NULL ,
`created` INT NOT NULL,
`sid` int(11) unsigned NOT NULL,
`checked_out` datetime default NULL,
PRIMARY KEY (`username`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules` (
`vid` varchar(20) NOT NULL,
`vname` varchar(50) NOT NULL,
`vtype` varchar(50) NOT NULL,
`vresponsible` varchar(50) NOT NULL,
`unittype` varchar(50) NOT NULL,
`department` varchar(50) NOT NULL default '',
`sid` int(11) unsigned NOT NULL,
UNIQUE KEY `vid` (`vid`),
FOREIGN KEY (`sid`) REFERENCES `#__thm_organizer_semesters` (`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
`vid` varchar(20) NOT NULL,
`eid` varchar(20) NOT NULL,
`sid` varchar (11) NOT NULL default '',
FOREIGN KEY (`vid`) REFERENCES `#__thm_organizer_virtual_schedules` (`vid`),
FOREIGN KEY (`sid`) REFERENCES `#__thm_organizer_semesters` (`id`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';
