
RENAME TABLE `#__thm_organizer_degree_programs` TO `#__thm_organizer_programs`;

ALTER TABLE `#__thm_organizer_pools`
ADD  KEY `lsfID` ( `lsfID` ),
ADD  KEY `externalID` ( `externalID` );

ALTER TABLE `#__thm_organizer_subjects`
ADD `references` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `preliminary_work_en`,
ADD `language` VARCHAR ( 2 ) DEFAULT NULL,
ADD `pform` VARCHAR ( 2 ) DEFAULT NULL;