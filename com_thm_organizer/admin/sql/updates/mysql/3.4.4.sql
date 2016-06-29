DROP TABLE `#__thm_organizer_virtual_schedules_elements`;

DROP TABLE `#__thm_organizer_virtual_schedules`;

ALTER TABLE `#__thm_organizer_programs`
  ADD `frequencyID` INT(1) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_programs`
  ADD CONSTRAINT `programs_frequencyid_fk` FOREIGN KEY (`frequencyID`)
REFERENCES `#__thm_organizer_frequencies` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

UPDATE `#__thm_organizer_programs`
SET `frequencyID` = '3'
WHERE `frequencyID` IS NULL;