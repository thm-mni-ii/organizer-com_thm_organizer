CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
`eid` int(11) unsigned NOT NULL auto_increment,
`title` varchar(100) NOT NULL default '',
`ealias` varchar(100) NOT NULL default '',
`edescription` mediumtext NOT NULL,
`eimage` varchar(100) NOT NULL default '',
`created_by` int(11) unsigned NOT NULL default '0',
`created` datetime NOT NULL,
`modified_by` int(11) unsigned NOT NULL default '0',
`modified` datetime NOT NULL,
`ecatid` int(11) unsigned NOT NULL default '0',
`startdate` date NOT NULL default '0000-00-00',
`enddate` date NULL default NULL,
`starttime` time NULL default NULL,
`endtime` time NULL default NULL,
`recurrence_number` int(2) NOT NULL default '0',
`recurrence_type` int(2) NOT NULL default '0',
`recurrence_counter` date NOT NULL default '0000-00-00',
`register` tinyint(1) NOT NULL default '0',
`unregister` tinyint(1) NOT NULL default '0',
`checked_out` datetime default NULL,
PRIMARY KEY  (`eid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_eventobjects` (
`eventid` int(11) unsigned NOT NULL,
`objectid` varchar(20) NOT NULL,
`sid` int(11) unsigned NOT NULL
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
`sid` int(11) unsigned NOT NULL auto_increment,
`author` varchar(20) NOT NULL,
`orgunit` varchar(10) NOT NULL,
`semester` varchar(10) NOT NULL,
UNIQUE (`orgunit`, `semester`),
PRIMARY KEY  (`sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_objects` (
`oid` varchar(20) NOT NULL,
`oname` varchar(50) NOT NULL default '',
`oalias` varchar(50) NOT NULL default '',
`otype` varchar(50) NOT NULL default '',
`odescription` mediumtext NOT NULL,
`oimage` varchar(100) NOT NULL default '',
`manager` varchar(50) NOT NULL default '',
`created_by` int(11) unsigned NOT NULL default '0',
`created` datetime NOT NULL,
`modified_by` int(11) unsigned NOT NULL default '0',
`modified` datetime NOT NULL,
`checked_out` datetime default NULL,
`sid` int(11) unsigned default NULL,
UNIQUE KEY (`oid`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_classes` (
`cid` varchar(20) NOT NULL,
`semester` varchar(50) NOT NULL,
`orgunit` varchar(20) NOT NULL default '',
UNIQUE KEY `cid` (`cid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
`rid` varchar(20) NOT NULL,
`capacity` int(4) NOT NULL default '0',
`rtype` varchar(20) NOT NULL default '',
`orgunit` varchar(20) NOT NULL default '',
UNIQUE KEY `rid` (`rid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
`lid` varchar(20) NOT NULL,
`day` int(1) unsigned NOT NULL,
`period` int(1) unsigned NOT NULL,
`room` varchar(20) NOT NULL,
`sid` int(11) unsigned NOT NULL,
PRIMARY KEY (`lid`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_modules` (
`mid` int(11) unsigned NOT NULL auto_increment,
`modid` varchar(50)  NOT NULL,
`mtitle` varchar(100)  NOT NULL default '',
`shortname` varchar(30)  NOT NULL default '',
`objective` text  NOT NULL default '',
`content` text  NOT NULL default '',
`lit` text  NOT NULL default '',
`lp` int(1) unsigned NOT NULL,
`required` varchar(30)  NOT NULL default '',
`test` varchar(30)  NOT NULL default '',
`tstamp` datetime  NOT NULL,
UNIQUE (`modid`,`tstamp`),
PRIMARY KEY `mid` (`mid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_moduleclasses` (
`modid` varchar(50) NOT NULL,
`cid` varchar(20) NOT NULL,
UNIQUE (`modid`, `cid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessonteacher` (
`lid` varchar(20) NOT NULL,
`tid` varchar(20) NOT NULL,
`sid` int(11) unsigned NOT NULL
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';


CREATE TABLE IF NOT EXISTS `#__thm_organizer_modulesemester` (
`mid` int(11) unsigned NOT NULL,
`sid` int(11) unsigned NOT NULL,
UNIQUE (`mid`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prereq` (
`pid` varchar(10)  NOT NULL,
`cid` varchar(10)  NOT NULL,
UNIQUE (`pid`, `cid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors`(
`monitorID` int(11) unsigned NOT NULL auto_increment,
`room` varchar(4) NOT NULL,
`ip` varchar (15) NOT NULL,
`sid` varchar (11) NOT NULL default '',
PRIMARY KEY (`monitorID`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

INSERT IGNORE INTO `#__thm_organizer_monitors` (room, ip) VALUES
('I007', '10.48.0.87'),
('I008', '10.48.0.47'),
('I009', '10.48.0.48'),
('I107', '10.48.0.49'),
('I108', '10.48.0.50'),
('I109', '10.48.0.51'),
('I136', '10.48.0.86'),
('I207', '10.48.0.55'),
('I208', '10.48.0.53'),
('I209', '10.48.0.54'),
('I210', '10.48.0.88');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
`ecid` int(11) unsigned NOT NULL auto_increment,
`parent_id` int(11) unsigned NOT NULL default '0',
`ecname` varchar(100) NOT NULL default '',
`ecalias` varchar(100) NOT NULL default '',
`ecdescription` mediumtext NOT NULL,
`ecimage` varchar(100) NOT NULL default '',
`access` int(2) unsigned NOT NULL default '0',
`globalp` boolean NOT NULL default false,
`reservingp` boolean NOT NULL default false,
`checked_out` datetime default NULL,
PRIMARY KEY  (`ecid`)
) TYPE=MyISAM;

INSERT IGNORE INTO `#__thm_organizer_categories` (`ecid`, `parent_id`, `ecname`, `ecalias`, `ecdescription`, `ecimage`) VALUES
(1, 0, 'FH Termine', 'fh-termine', '<p>\r\nHier finden Sie Termine aus anderen Fachbereichen oder Organisationen aus unserer Hochschule, z.B.\r\n</p>\r\n<ul>\r\n<li>Sporadische Lehrveranstaltungen in unseren R&auml;umen</li>\r\n<li>Veranstaltungen der Fachhochschule in unseren R&auml;umen</li>\r\n<li>Termine f&uuml;r Gremien - z.B. Senat</li>\r\n</ul>', 'fhlogo.jpg'),
(3, 0, 'MNI Termine', 'mni-termine', '<p>Hier sind alle wichtigen Termine aus unserem Fachbereich zusammengefasst, z.B.</p>\r\n<ul>\r\n<li>Veranstaltungen in unserem Fachbereich</li>\r\n<li>Zeiten der Semester, der vorlesungsfreien Zeit, der Klausurwochen</li>\r\n<li>Termine f&uuml;r Gremien</li>\r\n</ul>', 'mnilogo.png'),
(5, 0, 'externe Termine', 'externe-termine', 'Hier sind Termine von Veranstaltungen externer Veranstalter aufgef&uuml;hrt, die inunseren R&auml;umen stattfinden.', 'clock.jpg'),
(7, 0, 'Nicht an der FH', 'nicht-an-der-fh', 'Interressante Termine ausserhalb der FH', 'world.jpg'),
(6, 0, 'interne Termine', 'interne-termine', '', ''),
(10, 0, 'vorlesungsfrei', 'vorlesungsfrei', '<p>Hier werden alle Zeiten eingetragen, an denen keine Veranstaltungen nach dem regul&auml;ren Stundenplan stattfinden</p>\r\n<p>(Also z.B. Feiertrage, Semesterferien, ... aber auch Projektwochen)</p>', ''),
(9, 0, 'FS Termine', 'fs-termine', '', ''),
(11, 0, 'Ausfall', 'ausfall', 'in diese Kategorie kommen alle Termine von ausfallenden Veranstaltungen', '');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_settings` (
`id` int(11) NOT NULL,
`oldevent` tinyint(4) NOT NULL,
`minus` tinyint(4) NOT NULL,
`contentsect` varchar(50) NOT NULL,
`contentcat` varchar(50) NOT NULL,
`imagesizelimit` varchar(20) NOT NULL,
`imageheight` varchar(20) NOT NULL,
`imagewidth` varchar(20) NOT NULL,
`showregistrer` tinyint(4) NOT NULL,
`showunregister` tinyint(4) NOT NULL,
`checked_out` datetime default NULL,
UNIQUE KEY `id` (`id`)
) TYPE = MYISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules`(
`id` INT NOT NULL AUTO_INCREMENT,
`filename` varchar(100) NOT NULL,
`file` MEDIUMBLOB NOT NULL,
`includedate` datetime NOT NULL,
`description` text NOT NULL default '',
`active`  datetime default NULL,
`sid` int(11) unsigned NOT NULL,
PRIMARY KEY (`id`, `sid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

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
UNIQUE KEY `vid` (`vid`)
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';

CREATE TABLE IF NOT EXISTS `#__thm_organizer_virtual_schedules_elements` (
`vid` varchar(20) NOT NULL,
`eid` varchar(20) NOT NULL,
`sid` varchar (11) NOT NULL default ''
) TYPE=MyISAM DEFAULT CHARACTER SET 'utf8';
