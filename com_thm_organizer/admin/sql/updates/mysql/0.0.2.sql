ALTER TABLE `#__thm_organizer_rooms`
DROP `capacity`,
DROP INDEX `manager`,
DROP `manager`,
ADD `institutionID` int(11) unsigned NOT NULL DEFAULT '1' AFTER `alias`,
ADD `campusID` int(11) unsigned NOT NULL DEFAULT '1' AFTER `institution`,
ADD `buildingID` int( 11 ) UNSIGNED NOT NULL DEFAULT '1', AFTER `campus`,
ADD INDEX `institutionID` ( `institutionID` ),
ADD INDEX `campusID` ( `campusID` ),
ADD INDEX `buildingID` ( `buildingID`);

RENAME TABLE  `#__thm_organizer_application_settings` TO  `#__thm_organizer_settings` ;

ALTER TABLE`jos_thm_organizer_monitors`
ADD `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
ADD `interval` INT(1) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
ADD `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
ADD`content_meta` TEXT DEFAULT NULL COMMENT'a json string containing optional file extension specific parameters',
CHANGE `roomID` `roomID` INT(11) UNSIGNED NOT NULL COMMENT 'references id of rooms table',
ADD INDEX (`display`);