CREATE TABLE IF NOT EXISTS `#__thm_organizer_planning_periods` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(10)      NOT NULL,
  `startDate` DATE                      DEFAULT NULL,
  `endDate`   DATE                      DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `pp_long`(`name`, `startDate`, `endDate`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"startdate"', '"syStartDate"');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"enddate"', '"syEndDate"');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"termStartDate"', '"startDate"');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, '"termEndDate"', '"endDate"');

ALTER TABLE `#__thm_organizer_schedules`
  DROP COLUMN `startdate`,
  DROP COLUMN `enddate`;

ALTER TABLE `#__thm_organizer_schedules`
  CHANGE `term_startdate` `startDate` DATE DEFAULT NULL,
  CHANGE `term_enddate` `endDate` DATE DEFAULT NULL;

UPDATE `#__thm_organizer_schedules`
SET `endDate` = '2015-10-04'
WHERE `endDate` = '2015-10-03';

UPDATE `#__thm_organizer_schedules`
SET `endDate` = '2016-04-03'
WHERE `endDate` = '2016-04-05';

INSERT INTO `#__thm_organizer_planning_periods` (`name`, `startDate`, `endDate`)
  SELECT DISTINCT
    CONCAT(semestername, SUBSTR(endDate, 3, 2)) AS name,
    startDate,
    endDate
  FROM `#__thm_organizer_schedules`
  WHERE semestername IN ('WS', 'SS');

ALTER TABLE `#__thm_organizer_schedules`
  ADD `planningPeriodID` INT(11) UNSIGNED DEFAULT NULL,
  ADD CONSTRAINT `schedules_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`) REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

UPDATE `#__thm_organizer_schedules` AS s
  INNER JOIN (SELECT *
              FROM `#__thm_organizer_planning_periods`) AS pp
    ON s.startDate = pp.startDate AND s.endDate = pp.endDate
SET s.planningPeriodID = pp.id;

ALTER TABLE `#__thm_organizer_schedules`
  DROP COLUMN `plan_name`;

ALTER TABLE `#__thm_organizer_schedules`
  DROP COLUMN `description`;

ALTER TABLE `#__thm_organizer_schedules`
  ADD `newSchedule` MEDIUMTEXT NOT NULL;

ALTER TABLE `#__thm_organizer_lessons`
  DROP COLUMN `planName`;

ALTER TABLE `#__thm_organizer_lessons`
  ADD `departmentID` INT(11) UNSIGNED DEFAULT NULL,
  ADD CONSTRAINT `lessons_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD `planningPeriodID` INT(11) UNSIGNED DEFAULT NULL,
  ADD CONSTRAINT `lessons_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`) REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_frequencies`
  MODIFY `id` INT(11) UNSIGNED NOT NULL;

