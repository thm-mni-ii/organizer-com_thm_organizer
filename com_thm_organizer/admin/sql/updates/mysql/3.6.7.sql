ALTER TABLE `#__thm_organizer_grids`
  ADD `gpuntisID` VARCHAR(60) NOT NULL;

ALTER TABLE `#__thm_organizer_grids`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);