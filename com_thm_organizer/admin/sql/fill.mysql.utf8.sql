INSERT INTO `#__thm_organizer_asset_types` (`id`, `name`) VALUES
(1, 'Course'),
(2, 'Coursepool'),
(3, 'Dummy');

INSERT INTO `#__thm_organizer_colors` (`id`, `name`, `color`) VALUES
(39, 'THM Hintergrundgruen', 'cce3a7'),
(40, 'THM Hintergrundgrau', 'b7bec2'),
(41, 'THM Hintergrundrot', 'e199ad'),
(42, 'THM Hintergrundgelb', 'fde499'),
(43, 'THM Hintergrundcyan', '99e1f1'),
(44, 'THM Hintergrundblau', '99b4d0'),
(45, 'THM hellgruen', '9bd641'),
(46, 'THM hellgrau', '6b7e88'),
(47, 'THM hellrot', 'd32154'),
(48, 'THM hellgelb', 'ffca30'),
(49, 'THM hellcyan', '1dd1f9'),
(50, 'THM hellblau', '2568ae'),
(51, 'THM gruen', '80ba24'),
(53, 'THM rot', 'b30033'),
(54, 'THM gelb', 'fbbb00'),
(55, 'THM cyanm', '00b5dd'),
(57, 'THM mittelgruen', '71a126'),
(58, 'THM mittelgrau', '44535b'),
(59, 'THM mittelrot', '990831'),
(60, 'THM mittelgelb', 'd7a30b'),
(61, 'THM mittelcyan', '099cbd'),
(62, 'THM mittelblau', '063d76'),
(63, 'THM dunkelgruen', '638929'),
(64, 'THM dunkelgrau', '3d494f'),
(65, 'THM dunkelrot', '810e2f'),
(66, 'THM dunkelgelb', 'vb58b14');

INSERT INTO #__thm_organizer_semesters (id, name) VALUES
(1, '1. Semester'),
(2, '2. Semester'),
(3, '3. Semester'),
(4, '4. Semester'),
(5, '5. Semester'),
(6, '6. Semester'),
(7, '4./5. Semester');

INSERT INTO `#__thm_organizer_degrees` (`id`, `name`) VALUES
(13, 'Bachelor of Engineering'),
(15, 'Bachelor of Science'),
(16, 'Master of Science'),
(17, 'Bachelor of Arts'),
(18, 'Master of Arts'),
(20, 'Master of Business Administration and Engineering');

INSERT INTO #__thm_organizer_lecturers_types (id, name) VALUES
(1, 'Modulverantwortlicher'),
(2, 'Dozent');

INSERT INTO `#__thm_organizer_soap_queries` (`id`, `name`, `lsf_object`, `lsf_study_path`, `lsf_degree`, `lsf_pversion`, `description`) VALUES
(172, 'Bachelor Medizin-Informatik (2010)', 'studiengang', 'I', 'MI', '2010', ''),
(170, 'Bachelor Ingenieur Informatik (2010)', 'studiengang', 'INI', 'BS', '2010', ''),
(173, 'Bachelor BWL (2009)', 'studiengang', 'W', 'BA', '2009', ''),
(154, 'Bachelor Informatik (2010)', 'studiengang', 'I', 'BS', '2010', ''),
(175, 'Master of Arts International Marketing (2010)', 'studiengang', 'INM', 'MA', '2010', ''),
(167, 'Master Informatik (2010)', 'studiengang', 'I', 'MS', '2010', ''),
(174, 'Master of Arts Unternehmensführung (PO 2011)', 'studiengang', 'UF', 'MA', '2010', ''),
(164, 'Master of Science WirtschaftsInformatik (2010)', 'studiengang', 'WIN', 'MS', '2010', ''),
(169, 'Bachelor Bio-Informatik (2010)', 'studiengang', 'I', 'BI', '2010', ''),
(176, 'Master of Business Administration (PO 2010)', 'studiengang', 'W', 'MB', '2010', '');