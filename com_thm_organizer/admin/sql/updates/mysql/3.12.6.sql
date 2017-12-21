ALTER TABLE `#__thm_organizer_subjects`
  MODIFY COLUMN `max_participants` INT(4) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_lessons`
  ADD COLUMN `campusID` INT(11) UNSIGNED DEFAULT NULL,
  MODIFY COLUMN `registration_type` INT(1) UNSIGNED DEFAULT NULL
  COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.';

UPDATE `#__thm_organizer_lessons`
SET `registration_type` = NULL;

ALTER TABLE `#__thm_organizer_lessons`
  ADD CONSTRAINT `lessons_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
  MODIFY COLUMN `status` INT(1) UNSIGNED DEFAULT '0'
  COMMENT 'The user''s registration status. Possible values: 0 - pending, 1 - registered'