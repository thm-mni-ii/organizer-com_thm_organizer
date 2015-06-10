--
-- com_thm_organizer Unit Test DDL
--

-- --------------------------------------------------------

--
-- Table structure for table `jos_extensions`
--

CREATE TABLE `jos_extensions` (
  `extension_id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL DEFAULT '',
  `type` TEXT NOT NULL DEFAULT '',
  `element` TEXT NOT NULL DEFAULT '',
  `folder` TEXT NOT NULL DEFAULT '',
  `client_id` INTEGER NOT NULL,
  `enabled` INTEGER NOT NULL DEFAULT '1',
  `access` INTEGER NOT NULL DEFAULT '1',
  `protected` INTEGER NOT NULL DEFAULT '0',
  `manifest_cache` TEXT NOT NULL DEFAULT '',
  `params` TEXT NOT NULL DEFAULT '',
  `custom_data` TEXT NOT NULL DEFAULT '',
  `system_data` TEXT NOT NULL DEFAULT '',
  `checked_out` INTEGER NOT NULL DEFAULT '0',
  `checked_out_time` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` INTEGER DEFAULT '0',
  `state` INTEGER DEFAULT '0'
);

CREATE INDEX `idx_extensions_client_id` ON `jos_extensions` (`element`,`client_id`);
CREATE INDEX `idx_extensions_folder_client_id` ON `jos_extensions` (`element`,`folder`,`client_id`);
CREATE INDEX `idx_extensions_lookup` ON `jos_extensions` (`type`,`element`,`folder`,`client_id`);

-- --------------------------------------------------------

--
-- Table structure for table `jos_thm_organizer_schedules`
--

CREATE TABLE IF NOT EXISTS `jos_thm_organizer_schedules` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `departmentname` VARCHAR ( 50 ) NOT NULL,
  `semestername` VARCHAR ( 50 ) NOT NULL,
  `creationdate` date DEFAULT NULL,
  `creationtime` time DEFAULT NULL,
  `description` TEXT NOT NULL,
  `schedule` mediumblob NOT NULL,
  `active` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `term_startdate` date DEFAULT NULL,
  `term_enddate` date DEFAULT NULL
);

CREATE TABLE jos_thm_organizer_colors
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL DEFAULT '',
    color TEXT NOT NULL DEFAULT ''
);

--
-- Table structure for table `jos_users`
--

CREATE TABLE `jos_users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` TEXT NOT NULL DEFAULT '',
  `username` TEXT NOT NULL DEFAULT '',
  `email` TEXT NOT NULL DEFAULT '',
  `password` TEXT NOT NULL DEFAULT '',
  `block` INTEGER NOT NULL DEFAULT '0',
  `sendEmail` INTEGER DEFAULT '0',
  `registerDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` TEXT NOT NULL DEFAULT '',
  `params` TEXT NOT NULL DEFAULT '',
	`lastResetTime` TEXT NOT NULL DEFAULT '0000-00-00 00:00:00',
	`resetCount` INTEGER DEFAULT '0'
);

CREATE TABLE `jos_thm_organizer_rooms` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `gpuntisID` varchar(20) NOT NULL,
  `name` varchar(10) NOT NULL DEFAULT '',
  `longname` varchar(50) NOT NULL DEFAULT '',
  `typeID` int unsigned DEFAULT NULL
);

CREATE TABLE `jos_thm_organizer_mappings` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `programID` int unsigned DEFAULT NULL,
    `parentID` int unsigned DEFAULT NULL,
    `poolID` int unsigned DEFAULT NULL,
    `subjectID` int unsigned DEFAULT NULL,
    `lft` int unsigned DEFAULT NULL, 
    `rgt` int unsigned DEFAULT NULL,
    `level` int unsigned DEFAULT NULL,
    `ordering` int unsigned DEFAULT NULL
);

CREATE TABLE `jos_thm_organizer_subject_teachers`(
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `subjectID` int unsigned DEFAULT NULL,
    `teacherID` int unsigned DEFAULT NULL,
    `teacherResp` int unsigned DEFAULT NULL
);

CREATE TABLE `jos_thm_organizer_teachers` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `gpuntisID` varchar(10) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `forename` varchar(255) DEFAULT NULL,
  `username` varchar(150) NOT NULL DEFAULT '',
  `fieldID` int unsigned DEFAULT NULL,
  `title` varchar(45) DEFAULT NULL
);

--
-- Table structure for table `jos_viewlevels`
--

CREATE TABLE `jos_viewlevels` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL DEFAULT '',
  `ordering` INTEGER NOT NULL DEFAULT '0',
  `rules` TEXT NOT NULL DEFAULT '',
  CONSTRAINT `idx_viewlevels_title` UNIQUE (`title`)
);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `jos_thm_organizer_subjects`
--

CREATE TABLE `jos_thm_organizer_subjects` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `lsfID` int(11),
  `hisID` int(11),
  `externalID` varchar(45) DEFAULT NULL,
  `abbreviation_de` varchar(45) DEFAULT NULL,
  `abbreviation_en` varchar(45) DEFAULT NULL,
  `short_name_de` varchar(45) DEFAULT NULL,
  `short_name_en` varchar(45) DEFAULT NULL,
  `name_de` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description_de` text,
  `description_en` text,
  `objective_de` text,
  `objective_en` text,
  `content_de` text,
  `content_en` text,
  `preliminary_work_de` varchar(255) DEFAULT NULL,
  `preliminary_work_en` varchar(255) DEFAULT NULL,
  `literature` text,
  `creditpoints` int(4),
  `expenditure` int(4),
  `present` int(4),
  `independent` int(4),
  `proofID` varchar(2) DEFAULT NULL,
  `frequencyID` int(1),
  `methodID` varchar(2) DEFAULT NULL,
  `fieldID` int(11),
  `instructionLanguage` varchar(2) DEFAULT NULL,
  `pformID` varchar(2) DEFAULT NULL,
  `prerequisites_de` text,
  `prerequisites_en` text
);

--
-- Tabellenstruktur für Tabelle `jos_thm_organizer_user_schedules`
--
CREATE TABLE `jos_thm_organizer_user_schedules` (
  `username` VARCHAR ( 100 ) NOT NULL,
  `created` INT ( 11 ) NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY ( `username` )
);

CREATE TABLE `jos_thm_organizer_fields` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `gpuntisID` varchar( 50 ) NOT NULL DEFAULT '',
  `field` varchar( 50 ) NOT NULL DEFAULT '',
  `colorID` int unsigned DEFAULT NULL
);

--
-- Table structure for table `jos_assets`
--

CREATE TABLE `jos_assets` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `parent_id` INTEGER NOT NULL DEFAULT '0',
  `lft` INTEGER NOT NULL DEFAULT '0',
  `rgt` INTEGER NOT NULL DEFAULT '0',
  `level` INTEGER NOT NULL,
  `name` TEXT NOT NULL DEFAULT '',
  `title` TEXT NOT NULL DEFAULT '',
  `rules` TEXT NOT NULL DEFAULT '',
  CONSTRAINT `idx_assets_name` UNIQUE (`name`)
);

CREATE INDEX `idx_assets_left_right` ON `jos_assets` (`lft`,`rgt`);
CREATE INDEX `idx_assets_parent_id` ON `jos_assets` (`parent_id`);