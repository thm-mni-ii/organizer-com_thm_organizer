ALTER TABLE `#__thm_organizer_calendar`
  ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `#__thm_organizer_lessons`
  ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `#__thm_organizer_lesson_pools`
  ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `#__thm_organizer_lesson_subjects`
  ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `#__thm_organizer_lesson_teachers`
  ADD `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"creationdate"', '"creationDate"');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"creationtime"', '"creationTime"');

ALTER TABLE `#__thm_organizer_schedules`
  CHANGE `creationdate` `creationDate` DATE DEFAULT NULL,
  CHANGE `creationtime` `creationTime` TIME DEFAULT NULL;

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"starttime"', '"startTime"');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"endtime"', '"endTime"');