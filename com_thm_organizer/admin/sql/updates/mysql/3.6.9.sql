ALTER TABLE `#__thm_organizer_plan_subjects`
  DROP FOREIGN KEY `plan_subjects_subjectid_fk`;

ALTER TABLE `#__thm_organizer_plan_subjects`
  DROP INDEX `subjectID`;

ALTER TABLE `#__thm_organizer_plan_subjects`
  DROP `subjectID`;

ALTER TABLE `#__thm_organizer_plan_subjects`
  DROP INDEX `gpuntisID`;

CREATE INDEX `gpuntisID`
  ON `#__thm_organizer_plan_subjects` (`gpuntisID`);

ALTER TABLE `#__thm_organizer_plan_subjects`
  ADD `subjectIndex` VARCHAR(70) NOT NULL;

ALTER TABLE `#__thm_organizer_plan_subjects`
  ADD UNIQUE `subjectIndex` (`subjectIndex`);

ALTER TABLE `#__thm_organizer_plan_programs`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NOT NULL;

ALTER TABLE `#__thm_organizer_plan_pools`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NOT NULL;

ALTER TABLE `#__thm_organizer_plan_subjects`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NOT NULL;