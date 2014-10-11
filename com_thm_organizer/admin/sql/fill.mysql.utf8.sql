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
(25, 'THM dunkelrot', '810e2f');

INSERT INTO `#__thm_organizer_degrees` (`id`, `name`, `abbreviation`, `lsfDegree`) VALUES
(2, 'Bachelor of Engineering', 'B.Eng.', 'BE'),
(3, 'Bachelor of Science', 'B.Sc.', 'BS'),
(4, 'Bachelor of Arts', 'B.A.', 'BA'),
(5, 'Master of Engineering', 'M.Eng.', 'ME'),
(6, 'Master of Science', 'M.Sc.', 'MS'),
(7, 'Master of Arts', 'M.A.', 'MA'),
(8, 'Master of Business Administration and Engineering', 'M.B.A.', 'MB');


INSERT INTO `#__thm_organizer_teacher_responsibilities` (`id`, `name`) VALUES
(1, 'COM_THM_ORGANIZER_SUM_RESPONSIBLE'),
(2, 'COM_THM_ORGANIZER_SUM_TEACHER');

INSERT INTO `#__thm_organizer_frequencies` (`id`, `frequency_de`, `frequency_en`) VALUES
(0, 'Nach Termin', 'By Appointment'),
(1, 'Nur im Sommersemester', 'Only Spring/Summer Term'),
(2, 'Nur im Wintersemester', 'Only Fall/Winter Term'),
(3, 'Jedes Semester', 'Semesterly'),
(4, 'Nach Bedarf', 'As Needed'),
(5, 'Einmal im Jahr', 'Yearly');
