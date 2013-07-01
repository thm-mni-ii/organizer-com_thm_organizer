ALTER TABLE `#__thm_organizer_subjects`
ADD `literature` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `preliminary_work_en`,
ADD `instructionLanguage` VARCHAR ( 2 ) DEFAULT NULL,
ADD `pform` VARCHAR ( 2 ) DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects` CHANGE `description_de` `description_de` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `description_en` `description_en` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `objective_de` `objective_de` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `objective_en` `objective_en` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `content_de` `content_de` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `content_en` `content_en` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `literature` `literature` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;