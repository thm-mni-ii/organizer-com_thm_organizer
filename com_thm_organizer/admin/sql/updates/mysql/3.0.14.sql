ALTER TABLE `#__thm_organizer_colors`
CHANGE `color` `color` VARCHAR ( 7 ) NOT NULL;

UPDATE `#__thm_organizer_colors` SET `color` = CONCAT('#', `color`);