/* Former curriculum tables with FK constraints have to be removed to alter the
   signed int key to the more secure unsigned int key standard.*/

DROP TABLE IF EXISTS `#__thm_organizer_assets_semesters`;

DROP TABLE IF EXISTS `#__thm_organizer_lecturers_assets`;

DROP TABLE IF EXISTS `#__thm_organizer_lecturers`;

DROP TABLE IF EXISTS `#__thm_organizer_curriculum_semesters`;

DROP TABLE IF EXISTS `#__thm_organizer_soap_queries`;

DROP TABLE IF EXISTS `#__thm_organizer_degrees`;

DROP TABLE IF EXISTS `#__thm_organizer_semesters_majors`;

DROP TABLE IF EXISTS `#__thm_organizer_semesters`;

DROP TABLE IF EXISTS `#__thm_organizer_majors`;

DROP TABLE IF EXISTS `#__thm_organizer_assets_tree`;

DROP TABLE IF EXISTS `#__thm_organizer_assets`;

/* Easier to destroy and rebuild than alter and add. Abbreviation later will
   provide the link to data from the degrees/majors modeled in Untis departments.*/

CREATE TABLE `#__thm_organizer_degrees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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

/* Alter keys of tables with superficial changes to unsigned int 11 */
ALTER TABLE  `#__thm_organizer_event_exclude_dates`
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE  `eventID`  `eventID` INT( 11 ) UNSIGNED NOT NULL;



