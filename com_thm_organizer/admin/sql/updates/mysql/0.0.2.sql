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

