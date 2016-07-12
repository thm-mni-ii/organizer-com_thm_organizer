ALTER TABLE `#__thm_organizer_plan_pools`
  DROP INDEX `untisID`;

ALTER TABLE `#__thm_organizer_plan_pools`
  CHANGE `untisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_plan_pools`
  ADD UNIQUE `gpuntisID` (`gpuntisID`),
  ADD UNIQUE `dbID`(`gpuntisID`, `programID`);


ALTER TABLE `#__thm_organizer_plan_programs`
  DROP INDEX `untisID`;

ALTER TABLE `#__thm_organizer_plan_programs`
  CHANGE `untisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_plan_programs`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);


ALTER TABLE `#__thm_organizer_plan_subjects`
  DROP INDEX `untisID`;

ALTER TABLE `#__thm_organizer_plan_subjects`
  CHANGE `untisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_plan_subjects`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);


ALTER TABLE `#__thm_organizer_lessons`
  DROP INDEX `planID`;

ALTER TABLE `#__thm_organizer_lessons`
  CHANGE `untisID`  `gpuntisID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_lessons`
  ADD UNIQUE `planID` (`gpuntisID`, `plan_name`);


ALTER TABLE `#__thm_organizer_departments`
  ADD `plan_key` VARCHAR(10) NOT NULL;