ALTER TABLE `#__thm_organizer_lessons`
  DROP INDEX `planID`;

ALTER TABLE `#__thm_organizer_lessons`
  ADD UNIQUE `planID` (`gpuntisID`, `departmentID`, `planningPeriodID`);