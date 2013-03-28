--------------------------------------------------------------------------------
-- Drop Dependent Table                                                       --
--------------------------------------------------------------------------------

DROP TABLE IF EXISTS `#__thm_organizer_assets_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_curriculum_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_assets_tree`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_teacher_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_lecturers`;

--------------------------------------------------------------------------------
-- Teachers (Lecturers)                                                       --
--------------------------------------------------------------------------------

-- Alter teachers table to accept lecturers data. MNI has no overlapping IDs.
-- W needs to be emptied first. KMUB de-/reinstalled.
ALTER TABLE `#__thm_organizer_teachers`
CHANGE  `gpuntisID`  `gpuntisID` varchar( 10 ) NOT NULL DEFAULT '',
CHANGE  `surname`  `surname` varchar( 255 ) NOT NULL DEFAULT '',
CHANGE  `firstname`  `forename` varchar( 255 ) NOT NULL DEFAULT '',
ADD `title` varchar ( 45 ) DEFAULT NULL;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_teachers` (`id`, `surname`, `forename`, `username`, `title`)
SELECT `id`, `surname`, `forename`, `userid`, `academic_title`
FROM `#__thm_curriculum_lecturers`;

--------------------------------------------------------------------------------
-- Teacher Responsibilites (Lecturers Types)                                  --
--------------------------------------------------------------------------------

-- Rename table to make contents clearer as regards subject and context.
RENAME TABLE `#__thm_organizer_lecturers_types` TO `#__thm_organizer_teacher_responsibilities`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_teacher_responsibilities`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_teacher_responsibilities`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_teacher_responsibilities`
SELECT *
FROM `#__thm_curriculum_lecturers_types`;

--------------------------------------------------------------------------------
-- Degrees                                                                    --
--------------------------------------------------------------------------------

-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_degrees`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_degrees` (`id`, `name`)
SELECT *
FROM `#__thm_curriculum_degrees`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_degrees`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Add abbreviations for the connection to Untis data
ALTER TABLE `#__thm_organizer_degrees`
ADD `abbreviation` varchar ( 45 ) NOT NULL DEFAULT '';

-- Fill abbreviation data.
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'B.Sc.' WHERE `name` = 'Bachelor of Science';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'M.Sc.' WHERE `name` = 'Master of Science';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'B.A.' WHERE `name` = 'Bachelor of Arts';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'M.A.' WHERE `name` = 'Master of Arts';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'B.Eng.' WHERE `name` = 'Bachelor of Engineering';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'M.Eng.' WHERE `name` = 'Master of Engineering';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'M.B.A.' WHERE `name` = 'Master of Business Administration and Engineering';
UPDATE `#__thm_organizer_degrees` SET `abbreviation` = 'Dipl.' WHERE `name` = 'Diplom';

--------------------------------------------------------------------------------
-- Colors                                                                     --
--------------------------------------------------------------------------------

-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_colors`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_colors`
SELECT *
FROM `#__thm_curriculum_colors`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_colors`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

--------------------------------------------------------------------------------
-- Asset types                                                                --
--------------------------------------------------------------------------------

-- Empty the yet unused table.
TRUNCATE TABLE `#__thm_organizer_asset_types`;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_asset_types`
SELECT *
FROM `#__thm_curriculum_asset_types`;

-- Standardize the index column
ALTER TABLE  `#__thm_organizer_asset_types`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

--------------------------------------------------------------------------------
-- Assets                                                                     --
--------------------------------------------------------------------------------

-- Recreate the assets table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets` (
  `id` int (11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `beschreibung` varchar(255) DEFAULT NULL,
  `min_creditpoints` tinyint(4) DEFAULT NULL,
  `max_creditpoints` tinyint(4) DEFAULT NULL,
  `lsf_course_id` int(11) NOT NULL,
  `lsf_course_code` varchar(45) DEFAULT NULL,
  `his_course_code` int(11) DEFAULT NULL,
  `title_de` varchar(255) DEFAULT NULL,
  `title_en` varchar(45) DEFAULT NULL,
  `short_title_de` varchar(45) DEFAULT NULL,
  `short_title_en` varchar(45) NOT NULL,
  `abbreviation` varchar(45) DEFAULT NULL,
  `asset_type_id` int(11) unsigned DEFAULT NULL,
  `prerequisite` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `note` text NOT NULL,
  `pool_type` tinyint(4) NOT NULL,
  `color_id` int(11) unsigned DEFAULT NULL,
  `ecollaboration_link` varchar(255) NOT NULL,
  `menu_link` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Copy data from curriculum to temp table
INSERT INTO `#__thm_organizer_assets`
SELECT *
FROM `#__thm_curriculum_assets`;

-- Standardize the index column
ALTER TABLE  `jos_thm_organizer_assets`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Replace the color_id 0 with 39 to prevent problems with FK checks
UPDATE `#__thm_organizer_assets`
SET `color_id` = '39'
WHERE `color_id`= '0';

-- Add FK constraints
ALTER TABLE `#__thm_organizer_assets`
ADD FOREIGN KEY (`asset_type_id`) REFERENCES `#__thm_organizer_asset_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--------------------------------------------------------------------------------
-- Majors                                                                     --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_majors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` int(11) unsigned DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `po` year(4) NOT NULL,
  `note` text,
  `lsf_object` varchar(255),
  `lsf_study_path` varchar(255),
  `lsf_degree` varchar(255),
  `organizer_major` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_majors`
SELECT *
FROM `#__thm_curriculum_majors`;

-- Add FK constraints
ALTER TABLE `#__thm_organizer_majors`
ADD FOREIGN KEY (`degree_id`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--------------------------------------------------------------------------------
-- Semesters                                                                  --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `color_id` int(11) DEFAULT NULL,
  `short_title_de` varchar(45),
  `short_title_en` varchar(45), 
  `note` text,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Copy data from curriculum to organizer
INSERT INTO `#__thm_organizer_semesters`
SELECT *
FROM `#__thm_curriculum_semesters`;

-- Standardize the index columns
ALTER TABLE `#__thm_organizer_semesters`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `color_id` `color_id` INT ( 11 ) UNSIGNED DEFAULT NULL;

-- Replace the color_id 0 with 39 to prevent problems with FK checks
UPDATE `#__thm_organizer_semesters`
SET `color_id` = '39'
WHERE `color_id`= '0';

-- Add FK constraints.
ALTER TABLE `#__thm_organizer_semesters`
ADD FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--------------------------------------------------------------------------------
-- Semesters Majors                                                           --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters_majors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `major_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  PRIMARY KEY (`major_id`, `semester_id`),
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Copy data from curriculum to temp table
INSERT INTO `#__thm_organizer_semesters_majors`
SELECT *
FROM `#__thm_curriculum_semesters_majors`;

-- Standardize the index columns
ALTER TABLE `#__thm_organizer_semesters_majors`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `major_id` `major_id` INT ( 11 ) UNSIGNED NOT NULL,
CHANGE `semester_id` `semester_id` INT ( 11 ) UNSIGNED NOT NULL;

-- Add FK constraints.
ALTER TABLE `#__thm_organizer_semesters_majors`
ADD FOREIGN KEY (`major_id`) REFERENCES `#__thm_organizer_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`semester_id`) REFERENCES `#__thm_organizer_semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--------------------------------------------------------------------------------
-- Teacher Assets (Lecturers Assets)                                          --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications, the id gets altered later to
-- avoid problems with fk type checks.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_teacher_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleID` int(11) NOT NULL,
  `teacherID` int(11) NOT NULL,
  `teacherResp` int(11) NOT NULL,
  PRIMARY KEY (`moduleID`, `teacherID`, `teacherResp`),
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Copy data from curriculum to temp table
INSERT INTO `#__thm_organizer_teacher_assets`
SELECT *
FROM `#__thm_curriculum_lecturers_assets`;

-- Standardize the index columns
ALTER TABLE `#__thm_organizer_teacher_assets`
CHANGE `id` `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `moduleID` `moduleID` INT ( 11 ) UNSIGNED NOT NULL,
CHANGE `teacherID` `teacherID` INT ( 11 ) UNSIGNED NOT NULL,
CHANGE `teacherResp` `teacherResp` INT ( 11 ) UNSIGNED NOT NULL;

-- Add FK constraints.
ALTER TABLE `#__thm_organizer_teacher_assets`
ADD FOREIGN KEY (`moduleID`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`teacherResp`) REFERENCES `#__thm_organizer_teacher_responsibilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--------------------------------------------------------------------------------
-- Assets Tree                                                                --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications. Data is not migrated due to the
-- extreme level of corrupt data (approx. 66%).
CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_tree` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `color_id` int(11) UNSIGNED DEFAULT NULL,
  `asset` int(11) UNSIGNED NOT NULL,
  `parent_id` int(11) UNSIGNED NOT NULL,
  `proportion_crp` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `depth` int(11) DEFAULT NULL,
  `lineage` varchar(255) NOT NULL DEFAULT 'none',
  `published` tinyint(4) NOT NULL DEFAULT 1,
  `note` text NOT NULL,
  `ordering` int(11) NOT NULL,
  `ecollaboration_link` varchar(255) NOT NULL,
  `menu_link` int(11) NOT NULL,
  `color_id_flag` tinyint(4) NOT NULL DEFAULT 1,
  `menu_link_flag` tinyint(4) NOT NULL DEFAULT 1,
  `ecollaboration_link_flag` int(1) NOT NULL DEFAULT 1,
  `note_flag` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`asset`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--------------------------------------------------------------------------------
-- Assets Semesters                                                           --
--------------------------------------------------------------------------------

-- Recreate the majors table with modifications. Data is not migrated due to the
-- extreme level of corrupt data in parent table assets tree.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assets_tree_id` int(11) unsigned NOT NULL,
  `semesters_majors_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`assets_tree_id`, `semesters_majors_id`),
  FOREIGN KEY (`assets_tree_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`semesters_majors_id`) REFERENCES `#__thm_organizer_semesters_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--------------------------------------------------------------------------------
-- SOAP Queries                                                               --
--------------------------------------------------------------------------------

TRUNCATE TABLE `#__thm_organizer_soap_queries`;

INSERT INTO `#__thm_organizer_soap_queries`
SELECT *
FROM `#__thm_curriculum_soap_queries`;