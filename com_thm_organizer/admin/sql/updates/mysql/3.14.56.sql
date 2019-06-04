ALTER TABLE `#__thm_organizer_departments` ADD COLUMN contact_type    TINYINT(1),
    ADD COLUMN contactID  INT(11),
    ADD COLUMN contact_email   VARCHAR(100),
    ADD INDEX `contactID` (`contactID`);

ALTER TABLE `#__thm_organizer_departments`
    ADD CONSTRAINT `departments_contactID_fk` FOREIGN KEY (`contactID`) REFERENCES `#__users` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

