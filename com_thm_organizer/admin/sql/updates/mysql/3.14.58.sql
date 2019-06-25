ALTER TABLE `#__thm_organizer_room_types`
    ADD COLUMN `public` TINYINT(1) NOT NULL DEFAULT '1';

UPDATE TABLE `#__thm_organizer_room_types`
SET public = 0
WHERE `untisID` = 'BR';