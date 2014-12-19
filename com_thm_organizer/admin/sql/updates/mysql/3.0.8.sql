
ALTER TABLE `#__thm_organizer_events`
DROP FOREIGN KEY `events_categoryid_fk`;

DROP TABLE IF EXISTS `#__thm_organizer_categories`;

ALTER TABLE `#__thm_organizer_events`
CHANGE `categoryID` `categoryID` INT(11) NOT NULL;

ALTER TABLE `#__thm_organizer_events`
ADD `global` TINYINT ( 1 ) NOT NULL DEFAULT '0',
ADD `reserves` TINYINT ( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_events`
ADD CONSTRAINT `events_categoryid_fk` FOREIGN KEY (`categoryID`)
REFERENCES `#__categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;