ALTER TABLE `#__thm_organizer_fields`
  DROP INDEX `gpuntisID`,
  ADD UNIQUE `gpuntisID` (`gpuntisID`);


ALTER TABLE `v7ocf_thm_organizer_teachers`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);