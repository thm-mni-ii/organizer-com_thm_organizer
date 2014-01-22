ALTER TABLE  `#__thm_organizer_programs`
CHANGE `subject` `subject_de` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `jos_thm_organizer_programs`
ADD `subject_en` VARCHAR( 255 ) NOT NULL AFTER `subject_de`;