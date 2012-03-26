ALTER TABLE `#__thm_organizer_rooms` ADD `campus`  varchar(128) NOT NULL DEFAULT '' AFTER `manager`;
ALTER TABLE `#__thm_organizer_rooms` ADD `building` varchar(64) NOT NULL DEFAULT '' AFTER `campus`;
ALTER TABLE `#__thm_organizer_rooms` ADD `floor` int(2) NOT NULL DEFAULT 0 AFTER `building`;

ALTER TABLE `#__thm_organizer_classes` DROP INDEX `teacherID`;
ALTER TABLE `#__thm_organizer_classes` DROP `teacherID`;
ALTER TABLE `#__thm_organizer_classes` ADD `manager` varchar(20) AFTER `alias`;

