-- Former curriculum tables with FK constraints have to be removed to alter the
-- signed int key to the more secure unsigned int key standard.
DROP TABLE IF EXISTS `#__thm_organizer_lecturers_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_assets_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_assets_tree`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters_majors`;
DROP TABLE IF EXISTS `#__thm_organizer_assets`;
DROP TABLE IF EXISTS `#__thm_organizer_semesters`;
DROP TABLE IF EXISTS `#__thm_organizer_curriculum_semesters`;

-- Uses MyISAM needs to get dropped for InnoDB
DROP TABLE IF EXISTS `#__thm_organizer_soap_queries`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_soap_queries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lsf_object` varchar(255) NOT NULL,
  `lsf_study_path` varchar(255) NOT NULL,
  `lsf_degree` varchar(255) NOT NULL,
  `lsf_pversion` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_soap_queries` (`id`, `name`, `lsf_object`, `lsf_study_path`, `lsf_degree`, `lsf_pversion`) VALUES
(1, 'Bachelor Medizin-Informatik (2010)', 'studiengang', 'I', 'MI', '2010'),
(2, 'Bachelor Ingenieur Informatik (2010)', 'studiengang', 'INI', 'BS', '2010'),
(3, 'Bachelor BWL (2009)', 'studiengang', 'W', 'BA', '2009'),
(4, 'Bachelor Informatik (2010)', 'studiengang', 'I', 'BS', '2010'),
(5, 'Master of Arts International Marketing (2010)', 'studiengang', 'INM', 'MA', '2010'),
(6, 'Master Informatik (2010)', 'studiengang', 'I', 'MS', '2010'),
(7, 'Master of Arts Unternehmensführung (PO 2011)', 'studiengang', 'UF', 'MA', '2010'),
(8, 'Master of Science WirtschaftsInformatik (2010)', 'studiengang', 'WIN', 'MS', '2010'),
(9, 'Bachelor Bio-Informatik (2010)', 'studiengang', 'I', 'BI', '2010'),
(10, 'Master of Business Administration (PO 2010)', 'studiengang', 'W', 'MB', '2010');

DROP TABLE IF EXISTS `#__thm_organizer_majors`;

-- Easier to destroy and rebuild than alter and add. Abbreviation later will
-- provide the link to data from the degrees/majors modeled in Untis departments.
DROP TABLE IF EXISTS `#__thm_organizer_degrees`;

CREATE TABLE`#__thm_organizer_degrees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_majors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `degree_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `po` year(4) NOT NULL,
  `note` text,
  `lsf_object` varchar(255),
  `lsf_study_path` varchar(255),
  `lsf_degree` varchar(255),
  `organizer_major` varchar(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`degree_id`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_degrees` (`id`, `name`, `abbreviation`) VALUES
(1, 'Diplom', 'Dipl.'),
(2, 'Bachelor of Engineering', 'B.Eng.'),
(3, 'Bachelor of Science', 'B.Sc.'),
(4, 'Bachelor of Arts', 'B.A.'),
(5, 'Master of Engineering', 'M.Eng.'),
(6, 'Master of Science', 'M.Sc.'),
(7, 'Master of Arts', 'M.A.'),
(8, 'Master of Business Administration and Engineering', 'MBA');

-- Alter keys of tables with superficial changes to unsigned int 11
ALTER TABLE  `#__thm_organizer_event_exclude_dates`
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `eventID`  `eventID` INT( 11 ) UNSIGNED NOT NULL;

ALTER TABLE  `#__thm_organizer_lecturers_types`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE  `#__thm_organizer_majors`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
ADD FOREIGN KEY (`degree_id`) REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE  `#__thm_organizer_colors`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE  `#__thm_organizer_asset_types`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Table has wrong column listed as PK, signed int id, and userid's length is
-- incompatible with the corresponding field in the users table.
DROP TABLE IF EXISTS `#__thm_organizer_lecturers`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lecturers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` varchar(150) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `forename` varchar(255) NOT NULL,
  `academic_title` varchar(45) DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Recreate tables with dependencries in reverse order.
CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `color_id` int(11) unsigned DEFAULT NULL,
  `short_title_de` varchar(45),
  `short_title_en` varchar(45), 
  `note` text,
  PRIMARY KEY (id),
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  FOREIGN KEY (`asset_type_id`) REFERENCES `#__thm_organizer_asset_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_semesters_majors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `major_id` int(11) unsigned NOT NULL,
  `semester_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`major_id`, `semester_id`),
  FOREIGN KEY (`major_id`) REFERENCES `#__thm_organizer_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`semester_id`) REFERENCES `#__thm_organizer_semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_tree` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color_id` int(11) unsigned DEFAULT NULL,
  `asset` int(11) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
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
  FOREIGN KEY (`color_id`) REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  FOREIGN KEY (`asset`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_assets_semesters` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assets_tree_id` int(11) unsigned NOT NULL,
  `semesters_majors_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`assets_tree_id`, `semesters_majors_id`),
  FOREIGN KEY (`assets_tree_id`) REFERENCES `#__thm_organizer_assets_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`semesters_majors_id`) REFERENCES `#__thm_organizer_semesters_majors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lecturers_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `modul_id` int(11) unsigned NOT NULL,
  `lecturer_id` int(11) unsigned NOT NULL,
  `lecturer_type` int(11) unsigned NOT NULL,
  PRIMARY KEY (`modul_id`, `lecturer_id`, `lecturer_type`),
  FOREIGN KEY (`modul_id`) REFERENCES `#__thm_organizer_assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_id`) REFERENCES `#__thm_organizer_lecturers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_type`) REFERENCES `#__thm_organizer_lecturers_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
