UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'DP_', '');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'CL_', '');

UPDATE IGNORE `#__thm_organizer_plan_programs`
SET `gpuntisID` = REPLACE(`gpuntisID`, 'DP_', '');

DELETE FROM `#__thm_organizer_plan_programs` WHERE `gpuntisID` LIKE 'DP_%';