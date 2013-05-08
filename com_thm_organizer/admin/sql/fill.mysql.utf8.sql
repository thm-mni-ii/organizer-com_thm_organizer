INSERT INTO `#__thm_organizer_asset_types` (`id`, `name`) VALUES
(1, 'Course'),
(2, 'Coursepool'),
(3, 'Dummy');

INSERT INTO `#__thm_organizer_colors` (`id`, `name`, `color`) VALUES
(1, 'THM Hintergrundgruen', 'cce3a7'),
(2, 'THM Hintergrundgrau', 'b7bec2'),
(3, 'THM Hintergrundrot', 'e199ad'),
(4, 'THM Hintergrundgelb', 'fde499'),
(5, 'THM Hintergrundcyan', '99e1f1'),
(6, 'THM Hintergrundblau', '99b4d0'),
(7, 'THM hellgruen', '9bd641'),
(8, 'THM hellgrau', '6b7e88'),
(9, 'THM hellrot', 'd32154'),
(10, 'THM hellgelb', 'ffca30'),
(11, 'THM hellcyan', '1dd1f9'),
(12, 'THM hellblau', '2568ae'),
(13, 'THM gruen', '80ba24'),
(14, 'THM rot', 'b30033'),
(15, 'THM gelb', 'fbbb00'),
(16, 'THM cyanm', '00b5dd'),
(17, 'THM mittelgruen', '71a126'),
(18, 'THM mittelgrau', '44535b'),
(19, 'THM mittelrot', '990831'),
(20, 'THM mittelgelb', 'd7a30b'),
(21, 'THM mittelcyan', '099cbd'),
(22, 'THM mittelblau', '063d76'),
(23, 'THM dunkelgruen', '638929'),
(24, 'THM dunkelgrau', '3d494f'),
(25, 'THM dunkelrot', '810e2f'),
(26, 'THM dunkelgelb', 'vb58b14');

INSERT INTO `#__thm_organizer_semesters` (`id`, `name`) VALUES
(1, '1. Semester'),
(2, '2. Semester'),
(3, '3. Semester'),
(4, '4. Semester'),
(5, '5. Semester'),
(6, '6. Semester'),
(7, '4./5. Semester');

INSERT INTO `#__thm_organizer_degrees` (`id`, `name`, `abbreviation`) VALUES
(1, 'Diplom', 'Dipl.'),
(2, 'Bachelor of Engineering', 'B.Eng.'),
(3, 'Bachelor of Science', 'B.Sc.'),
(4, 'Bachelor of Arts', 'B.A.'),
(5, 'Master of Engineering', 'M.Eng.'),
(6, 'Master of Science', 'M.Sc.'),
(7, 'Master of Arts', 'M.A.'),
(8, 'Master of Business Administration and Engineering', 'MBA');

INSERT INTO `#__thm_organizer_lecturers_types` (`id`, `name`) VALUES
(1, 'Modulverantwortlicher'),
(2, 'Dozent');

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