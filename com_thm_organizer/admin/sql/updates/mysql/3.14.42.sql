ALTER TABLE `#__thm_organizer_schedules`
    ADD COLUMN `userID` INT(11) DEFAULT NULL AFTER `departmentID`,
    ADD KEY `userID` (`userID`);

ALTER TABLE `#__thm_organizer_schedules`
    ADD CONSTRAINT `schedules_userid_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;