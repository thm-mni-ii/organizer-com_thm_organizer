CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id` INT (1) UNSIGNED NOT NULL,
  `frequency_de` varchar (45) DEFAULT NULL,
  `frequency_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_frequencies` (`id`, `frequency_de`, `frequency_en`) VALUES
(0, 'Nach Termin', 'By Appointment'),
(1, 'Nur im Sommersemester', 'Only Spring/Summer Term'),
(2, 'Nur im Wintersemester', 'Only Fall/Winter Term'),
(3, 'Jedes Semester', 'Semesterly'),
(4, 'Nach Bedarf', 'As Needed'),
(5, 'Einmal im Jahr', 'Yearly');

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `frequency` `frequencyID` INT( 1 ) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects`
ADD FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_proof` (
  `id` varchar (2) NOT NULL,
  `proof_de` varchar (45) DEFAULT NULL,
  `proof_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_proof` (`id`, `proof_de`, `proof_en`) VALUES
('BL', 'Belegung', 'Participation'),
('ST', 'Studienleistung', 'Scholastic Performance'),
('VL', 'Vorleistung', 'Work Performed'),
('LN', 'Leistungsnachweis', 'Coursework'),
('FP', 'Fachprüfung', 'Test'),
('TL', 'Teilleistung', 'Partial Completion'),
('P1', 'Praktikum', 'Project'),
('P', 'Klausur', 'Final'),
('DA', 'Diplom', 'Diploma Examination'),
('HD', 'Abschlussarbeit', 'Thesis Paper');

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `proof` `proofID` varchar (2) DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects`
ADD FOREIGN KEY (`proofID`) REFERENCES `#__thm_organizer_proof` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pforms` (
  `id` varchar (2) NOT NULL,
  `pform_de` varchar (45) DEFAULT NULL,
  `pform_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_pforms` (`id`, `pform_de`, `pform_en`) VALUES
('S', 'Schriftlich', 'Written'),
('E', 'Schriftlich, eventuell mündlich', 'Written, possibly Oral'),
('U', 'Schriftlich und mündlich', 'Written and Oral'),
('O', 'Schriftlich oder mündlich', 'Written or Oral'),
('M', 'Mündlich', 'Oral'),
('L', 'Leistungsschein', 'Certificate');

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `pform` `pformID` varchar (2) DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects`
ADD FOREIGN KEY (`pformID`) REFERENCES `#__thm_organizer_pforms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
  `id` varchar (2) NOT NULL,
  `method_de` varchar (45) DEFAULT NULL,
  `method_en` varchar (45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_methods` (`id`, `method_de`, `method_en`) VALUES
('V', 'Vorlesung', 'Lecture'),
('S', 'Seminar', 'Seminar'),
('P', 'Praktikum', 'Project'),
('VU', 'Vorlesung / Übung', 'Lecture / Practice'),
('VG', 'Vorlesung / Praktikum', 'Lecture / Group Work'),
('SV', 'Vorlesung / Seminar', 'Lecture / Seminar');

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `method` `methodID` varchar (2) DEFAULT NULL;

UPDATE `#__thm_organizer_subjects`
SET `methodID` = NULL
WHERE `methodID` = 0;

ALTER TABLE `#__thm_organizer_subjects`
ADD FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `subjectID` INT ( 11 ) UNSIGNED NOT NULL,
  `prerequisite` INT ( 11 ) UNSIGNED NOT NULL,
  FOREIGN KEY ( `subjectID` ) REFERENCES #__thm_organizer_subjects( `id` ) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY ( `prerequisite` ) REFERENCES #__thm_organizer_subjects( `id` ) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;