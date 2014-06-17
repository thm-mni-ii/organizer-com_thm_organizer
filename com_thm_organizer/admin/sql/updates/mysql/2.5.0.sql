ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `#__thm_organizer_subjects_ibfk_1`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `#__thm_organizer_subjects_ibfk_2`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `#__thm_organizer_subjects_ibfk_3`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `#__thm_organizer_subjects_ibfk_4`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `#__thm_organizer_subjects_ibfk_5`;

ALTER TABLE `#__thm_organizer_subjects` DROP INDEX `proofID`;

ALTER TABLE `#__thm_organizer_subjects` DROP INDEX `pformID`;

ALTER TABLE `#__thm_organizer_subjects` DROP `pformID`;

ALTER TABLE `#__thm_organizer_subjects` DROP INDEX `methodID`;

DROP TABLE IF EXISTS `#__thm_organizer_proof`;
DROP TABLE IF EXISTS `#__thm_organizer_pforms`;
DROP TABLE IF EXISTS `#__thm_organizer_methods`;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `frequencyID_fk`FOREIGN KEY (`frequencyID`)
REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `fieldID_fk`FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects` CHANGE `proofID` `proof_de` VARCHAR( 255 ) DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects` CHANGE `methodID` `method_de` VARCHAR( 255 ) DEFAULT NULL;

ALTER TABLE  `#__thm_organizer_subjects`
ADD `proof_en` VARCHAR( 255 ) DEFAULT NULL ,
ADD `method_en` VARCHAR( 255 ) DEFAULT NULL;