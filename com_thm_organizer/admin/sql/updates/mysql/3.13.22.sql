ALTER TABLE `#__thm_organizer_campuses`
  ADD COLUMN `gridID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_campuses`
  ADD CONSTRAINT `campus_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;