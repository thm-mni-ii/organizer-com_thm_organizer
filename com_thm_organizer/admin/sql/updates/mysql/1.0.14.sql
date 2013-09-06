
ALTER TABLE `jos_thm_organizer_pools`
ADD `description_de` text DEFAULT NULL,
ADD `description_en` text DEFAULT NULL,
ADD `distance` INT(2) UNSIGNED DEFAULT NULL,
ADD `display_type` boolean DEFAULT TRUE,
ADD `enable_desc` boolean DEFAULT TRUE;