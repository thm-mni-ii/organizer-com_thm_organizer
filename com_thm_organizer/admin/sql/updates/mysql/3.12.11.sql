ALTER TABLE `#__thm_organizer_user_lessons` DROP COLUMN `order`;

UPDATE `#__thm_organizer_user_lessons` SET `status` = 1;