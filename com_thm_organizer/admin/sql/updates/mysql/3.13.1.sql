ALTER TABLE `#__thm_organizer_subjects`
  ADD COLUMN `registration_type` INT(1) UNSIGNED DEFAULT NULL
COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.';