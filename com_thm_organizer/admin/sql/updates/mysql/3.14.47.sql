ALTER TABLE `#__thm_organizer_subjects`
    ADD COLUMN `bonus_points_de` TEXT NOT NULL,
    ADD COLUMN `bonus_points_en` TEXT NOT NULL;

ALTER TABLE `#__thm_organizer_subjects`
    MODIFY `creditpoints` DOUBLE(4, 1) UNSIGNED NOT NULL DEFAULT '0';