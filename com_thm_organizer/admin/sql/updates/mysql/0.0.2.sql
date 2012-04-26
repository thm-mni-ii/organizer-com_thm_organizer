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