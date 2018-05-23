ALTER TABLE `#__thm_organizer_lessons`
  ADD COLUMN `deadline` INT(2) UNSIGNED DEFAULT NULL COMMENT 'The deadline in days for registration before the course starts.';