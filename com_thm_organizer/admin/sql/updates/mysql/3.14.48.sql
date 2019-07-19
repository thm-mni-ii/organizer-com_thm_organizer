ALTER TABLE `#__thm_organizer_rooms`
    DROP COLUMN `longname`;

UPDATE `#__menu`
SET `link` = 'index.php?option=com_thm_organizer&view=schedule_grid'
WHERE `link` = 'index.php?option=com_thm_organizer&view=schedule';

ALTER TABLE `#__thm_organizer_subjects`
    MODIFY `creditpoints` DOUBLE(4, 1) UNSIGNED NOT NULL DEFAULT '0';