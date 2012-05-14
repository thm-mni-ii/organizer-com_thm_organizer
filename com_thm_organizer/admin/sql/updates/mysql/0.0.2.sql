ALTER TABLE `#__thm_organizer_rooms` DROP `capacity`;
ALTER TABLE `#__thm_organizer_rooms` DROP INDEX `manager`;
ALTER TABLE `#__thm_organizer_rooms` DROP `manager`;
ALTER TABLE `#__thm_organizer_rooms` DROP `floor`;

RENAME TABLE `#__thm_organizer_application_settings` TO  `#__thm_organizer_settings` ;

ALTER TABLE `#__thm_organizer_monitors`
ADD `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
ADD `interval` INT(1) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
ADD `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
ADD`content_meta` TEXT DEFAULT NULL COMMENT'a json string containing optional file extension specific parameters',
CHANGE `roomID` `roomID` INT(11) UNSIGNED NOT NULL COMMENT 'references id of rooms table',
ADD INDEX (`display`);

ALTER TABLE `#__thm_organizer_events`
CHANGE `recurrence_counter` `recurrence_enddate` DATE NOT NULL DEFAULT '0000-00-00',
CHANGE `recurrence_number` `recurrence_counter` INT(3) UNSIGNED NOT NULL DEFAULT 0,
ADD `recurrence_interval` INT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `recurrence_enddate`,
ADD `recurrence_weekly_days` INT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `recurrence_interval`
;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_exclude_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eventID` int(10) unsigned NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `eventID` (`eventID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS #__thm_organizer_settings;

ALTER TABLE `#__thm_organizer_virtual_schedules` ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `#__thm_organizer_virtual_schedules_elements` DROP `vid`;
ALTER TABLE `#__thm_organizer_virtual_schedules_elements` DROP `sid`;
ALTER TABLE `#__thm_organizer_virtual_schedules_elements` ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST
ALTER TABLE `#__thm_organizer_virtual_schedules_elements` ADD `vid` INT NOT NULL AFTER `id`;

ALTER TABLE `#__thm_organizer_virtual_schedules` CHANGE `vname` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__thm_organizer_virtual_schedules` CHANGE `vtype` `type` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__thm_organizer_virtual_schedules` CHANGE `vresponsible` `responsible` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `#__thm_organizer_virtual_schedules` DROP `unittype`;
ALTER TABLE `#__thm_organizer_virtual_schedules` CHANGE `sid` `semesterID` INT( 11 ) UNSIGNED NOT NULL;

INSERT INTO `#__thm_organizer_display_behaviours` (`id`, `behaviour`) VALUES ('4', 'COM_THM_ORGANIZER_MON_EVENT');
