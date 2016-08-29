UPDATE `#__thm_organizer_schedules` AS s
  INNER JOIN (SELECT *
              FROM `#__thm_organizer_planning_periods`) AS pp
    ON s.startDate = pp.startDate AND s.endDate = pp.endDate
SET s.planningPeriodID = pp.id;

ALTER TABLE `v7ocf_thm_organizer_lesson_configurations`
  DROP FOREIGN KEY `lesson_configurations_lessonid_fk`;

ALTER TABLE `v7ocf_thm_organizer_lesson_configurations`
  ADD CONSTRAINT `lesson_configurations_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `v7ocf_thm_organizer_lesson_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `v7ocf_thm_organizer_calendar`
  ADD `delta` VARCHAR(10) NOT NULL DEFAULT '';

ALTER TABLE `v7ocf_thm_organizer_calendar`
  DROP FOREIGN KEY `calendar_configurationid_fk`;

ALTER TABLE `v7ocf_thm_organizer_calendar`
    CHANGE `configurationID` `lessonID` INT (11) UNSIGNED NOT NULL;

ALTER TABLE `v7ocf_thm_organizer_calendar`
  ADD KEY `lessonID` (`lessonID`);

ALTER TABLE `v7ocf_thm_organizer_calendar`
  ADD CONSTRAINT `calendar_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `v7ocf_thm_organizer_lessons` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_calendar_configuration_map` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendarID`      INT(11) UNSIGNED NOT NULL,
  `configurationID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`calendarID`, `configurationID`),
  CONSTRAINT `calendar_configuration_map_calendarID_fk` FOREIGN KEY (`calendarID`) REFERENCES `v7ocf_thm_organizer_calendar` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `calendar_configuration_map_configurationID_fk` FOREIGN KEY (`configurationID`) REFERENCES `v7ocf_thm_organizer_lesson_configurations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;