UPDATE `#__thm_organizer_grids`
SET `grid` = REPLACE(`grid`, 'start_time', 'startTime');

UPDATE `#__thm_organizer_grids`
SET `grid` = REPLACE(`grid`, 'end_time', 'endTime');

UPDATE `#__thm_organizer_grids`
SET `grid` = REPLACE(`grid`, 'start_day', 'startDay');

UPDATE `#__thm_organizer_grids`
SET `grid` = REPLACE(`grid`, 'end_day', 'endDay');

ALTER TABLE `#__thm_organizer_grids`
  CHANGE `default` `defaultGrid` INT(1) NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_calendar`
  CHANGE `start_time` `startTime` TIME DEFAULT NULL,
  CHANGE `end_time` `endTime` TIME DEFAULT NULL;